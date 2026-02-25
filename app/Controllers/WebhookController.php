<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\AsaasService;
use App\Libraries\SMSFlowService;
use App\Models\AuditLogModel;

class WebhookController extends Controller
{
    protected $db;
    protected $auditLog;
    protected $sms;

    public function __construct()
    {
        $this->db       = \Config\Database::connect();
        $this->auditLog = new AuditLogModel();
        $this->sms      = new SMSFlowService();
    }

    /**
     * Sanitiza dados do webhook removendo informacoes sensiveis antes de salvar
     */
    private function sanitizeWebhookData(array $data): array
    {
        // Campos sensiveis a remover ou mascarar
        $sensitiveFields = ['cpfCnpj', 'cpf', 'cnpj', 'mobilePhone', 'phone', 'postalCode', 'addressNumber'];

        // Mascara CPF/CNPJ para manter apenas primeiros e ultimos digitos
        if (isset($data['customer']['cpfCnpj'])) {
            $cpf = $data['customer']['cpfCnpj'];
            $len = strlen($cpf);
            if ($len >= 6) {
                $data['customer']['cpfCnpj'] = substr($cpf, 0, 3) . str_repeat('*', $len - 6) . substr($cpf, -3);
            }
        }

        // Remove telefone do cliente
        if (isset($data['customer']['mobilePhone'])) {
            $data['customer']['mobilePhone'] = '***REMOVIDO***';
        }
        if (isset($data['customer']['phone'])) {
            $data['customer']['phone'] = '***REMOVIDO***';
        }

        // Remove dados de endereco sensiveis
        if (isset($data['customer']['postalCode'])) {
            $data['customer']['postalCode'] = '***REMOVIDO***';
        }
        if (isset($data['customer']['addressNumber'])) {
            $data['customer']['addressNumber'] = '***';
        }

        // Remove dados de cartao se existirem
        if (isset($data['creditCard'])) {
            $data['creditCard'] = ['masked' => true, 'lastDigits' => $data['creditCard']['creditCardNumber'] ?? '****'];
        }

        return $data;
    }

    /**
     * Verifica se webhook ja foi processado (idempotencia)
     */
    private function isWebhookProcessed(string $webhookId, string $source = 'asaas'): bool
    {
        return $this->db->table('webhook_processed')
            ->where('webhook_id', $webhookId)
            ->where('source', $source)
            ->countAllResults() > 0;
    }

    /**
     * Marca webhook como processado
     */
    private function markWebhookProcessed(string $webhookId, string $source = 'asaas', ?string $eventType = null): void
    {
        $this->db->table('webhook_processed')->insert([
            'webhook_id' => $webhookId,
            'source' => $source,
            'event_type' => $eventType,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Valida a assinatura HMAC do webhook Asaas
     */
    private function validateAsaasHmac(string $payload, string $signature, string $secret): bool
    {
        // Em producao, secret DEVE estar configurado
        if (ENVIRONMENT === 'production' && empty($secret)) {
            log_message('critical', 'Webhook ASAAS: Secret nao configurado em PRODUCAO - rejeitando webhook');
            return false;
        }

        // Em desenvolvimento sem secret, permitir com warning
        if (empty($secret)) {
            log_message('warning', 'Webhook ASAAS: Secret nao configurado - MODO DESENVOLVIMENTO');
            return ENVIRONMENT !== 'production';
        }

        if (empty($signature)) {
            log_message('error', 'Webhook ASAAS: Assinatura HMAC ausente no header');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Webhook do ASAAS
     * Recebe notificações de eventos de pagamento
     *
     * Eventos importantes:
     * - PAYMENT_CREATED: Cobrança criada
     * - PAYMENT_UPDATED: Cobrança atualizada
     * - PAYMENT_CONFIRMED: Pagamento confirmado (PIX/Cartão aprovado)
     * - PAYMENT_RECEIVED: Pagamento recebido (Boleto compensado)
     * - PAYMENT_OVERDUE: Cobrança vencida
     * - PAYMENT_DELETED: Cobrança removida
     * - PAYMENT_RESTORED: Cobrança restaurada
     * - PAYMENT_REFUNDED: Pagamento estornado
     * - PAYMENT_RECEIVED_IN_CASH: Pagamento recebido em dinheiro
     * - PAYMENT_CHARGEBACK_REQUESTED: Chargeback solicitado
     * - PAYMENT_CHARGEBACK_DISPUTE: Disputa de chargeback
     * - PAYMENT_AWAITING_CHARGEBACK_REVERSAL: Aguardando reversão de chargeback
     */
    public function asaas()
    {
        try {
            // Obter corpo bruto para validação HMAC
            $rawPayload = file_get_contents('php://input');

            // Validar assinatura HMAC
            $asaasConfig = config('Asaas');
            $webhookSecret = $asaasConfig->getWebhookSecret();
            $signature = $this->request->getHeaderLine('asaas-access-token')
                      ?: $this->request->getHeaderLine('X-Asaas-Signature')
                      ?: $this->request->getHeaderLine('Asaas-Signature');

            if (!$this->validateAsaasHmac($rawPayload, $signature, $webhookSecret)) {
                $this->auditLog->logSuspicious('Webhook ASAAS com assinatura HMAC inválida', null, [
                    'ip' => $this->request->getIPAddress(),
                    'signature_received' => substr($signature, 0, 20) . '...',
                ]);

                log_message('error', 'Webhook ASAAS: Assinatura HMAC inválida - IP: ' . $this->request->getIPAddress());

                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'error' => 'Assinatura inválida'
                ]);
            }

            // Ler dados do webhook
            $json = json_decode($rawPayload, true);

            if (empty($json)) {
                $json = $this->request->getPost();
            }

            // Log do webhook recebido (sem dados sensiveis)
            $eventType = $json['event'] ?? 'unknown';
            $paymentId = $json['payment']['id'] ?? 'unknown';
            log_message('info', "Webhook ASAAS recebido: evento={$eventType}, payment_id={$paymentId}");

            // Registrar no audit log
            $this->auditLog->log(
                AuditLogModel::ACTION_WEBHOOK_RECEIVED,
                null,
                'webhook_asaas',
                null,
                null,
                null,
                ['event' => $json['event'] ?? 'unknown', 'payment_id' => $json['payment']['id'] ?? null]
            );

            // Validar se tem dados
            if (empty($json) || !isset($json['event'])) {
                log_message('error', 'Webhook ASAAS inválido - sem dados ou evento');
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'error' => 'Dados inválidos'
                ]);
            }

            $event = $json['event'];
            $payment = $json['payment'] ?? null;

            if (!$payment || empty($payment['id'])) {
                log_message('error', 'Webhook ASAAS inválido - sem payment.id');
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'error' => 'Payment ID não encontrado'
                ]);
            }

            $asaasPaymentId = $payment['id'];

            // Verificar idempotencia - se webhook ja foi processado, ignorar
            $webhookKey = $asaasPaymentId . '_' . $event;
            if ($this->isWebhookProcessed($webhookKey, 'asaas')) {
                log_message('info', "Webhook ASAAS ja processado (idempotencia): {$webhookKey}");
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Webhook ja processado anteriormente'
                ]);
            }

            // Buscar transação no banco de dados
            $transaction = $this->db->table('asaas_transactions')
                ->where('asaas_payment_id', $asaasPaymentId)
                ->get()
                ->getRowArray();

            if (!$transaction) {
                log_message('warning', "Webhook ASAAS: transação {$asaasPaymentId} não encontrada no banco");
                // Não retornar erro, pois pode ser uma cobrança antiga ou de outro sistema
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Transação não encontrada (pode ser externa ao sistema)'
                ]);
            }

            // Processar evento
            $this->processWebhookEvent($event, $payment, $transaction);

            // Marcar webhook como processado (idempotencia)
            $this->markWebhookProcessed($webhookKey, 'asaas', $event);

            // Retornar sucesso
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Webhook processado com sucesso'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao processar webhook ASAAS: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Erro interno ao processar webhook'
            ]);
        }
    }

    /**
     * Processa eventos do webhook
     */
    private function processWebhookEvent(string $event, array $payment, array $transaction)
    {
        log_message('info', "Processando evento ASAAS: {$event} para transação {$transaction['id']}");

        // Atualizar dados da transação (sanitizando dados sensiveis)
        $updateData = [
            'status' => $this->mapAsaasStatus($payment['status'] ?? $transaction['status']),
            'webhook_data' => json_encode($this->sanitizeWebhookData($payment)),
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('asaas_transactions')
            ->where('id', $transaction['id'])
            ->update($updateData);

        // Processar eventos específicos
        switch ($event) {
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
            case 'PAYMENT_RECEIVED_IN_CASH':
                $this->handlePaymentReceived($transaction, $payment);
                break;

            case 'PAYMENT_REFUNDED':
                $this->handlePaymentRefunded($transaction, $payment);
                break;

            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($transaction, $payment);
                break;

            case 'PAYMENT_DELETED':
                $this->handlePaymentDeleted($transaction, $payment);
                break;
        }
    }

    /**
     * Mapeia status do Asaas para o sistema
     */
    private function mapAsaasStatus(string $asaasStatus): string
    {
        $statusMap = [
            'PENDING' => 'pending',
            'CONFIRMED' => 'confirmed',
            'RECEIVED' => 'received',
            'OVERDUE' => 'overdue',
            'REFUNDED' => 'refunded',
            'RECEIVED_IN_CASH' => 'received',
            'REFUND_REQUESTED' => 'refunded',
            'CHARGEBACK_REQUESTED' => 'cancelled',
            'CHARGEBACK_DISPUTE' => 'cancelled',
            'AWAITING_CHARGEBACK_REVERSAL' => 'cancelled',
            'DUNNING_REQUESTED' => 'overdue',
            'DUNNING_RECEIVED' => 'received',
            'AWAITING_RISK_ANALYSIS' => 'pending',
        ];

        return $statusMap[$asaasStatus] ?? 'pending';
    }

    /**
     * Processa pagamento recebido/confirmado
     */
    private function handlePaymentReceived(array $transaction, array $payment)
    {
        log_message('info', "Pagamento recebido: {$transaction['asaas_payment_id']}");

        // Validar montante - garantir que valor recebido corresponde ao esperado
        $expectedAmount = (float) ($transaction['amount'] ?? 0);
        $receivedAmount = (float) ($payment['value'] ?? $payment['netValue'] ?? 0);

        // Tolerancia de 2% para taxas e arredondamentos
        $tolerance = $expectedAmount * 0.02;

        if ($expectedAmount > 0 && abs($expectedAmount - $receivedAmount) > $tolerance) {
            log_message('error', "Webhook: VALOR DIVERGENTE! Esperado: R$ {$expectedAmount}, Recebido: R$ {$receivedAmount}, Payment ID: {$payment['id']}");

            // Registrar para auditoria
            $this->auditLog->logSuspicious(
                'Valor de pagamento divergente no webhook',
                null,
                [
                    'payment_id' => $payment['id'],
                    'expected_amount' => $expectedAmount,
                    'received_amount' => $receivedAmount,
                    'difference' => abs($expectedAmount - $receivedAmount),
                    'transaction_id' => $transaction['id']
                ]
            );

            // NAO processar pagamento com valor divergente significativo
            return;
        }

        // Buscar doação relacionada
        $donation = null;
        $donationId = null;

        if (!empty($transaction['donation_id'])) {
            $donation = $this->db->table('donations')
                ->where('id', $transaction['donation_id'])
                ->get()
                ->getRowArray();
            $donationId = $transaction['donation_id'];
        }

        // Buscar assinatura relacionada
        $subscription = null;
        $subscriptionId = null;

        if (!empty($transaction['subscription_id'])) {
            $subscription = $this->db->table('subscriptions')
                ->where('id', $transaction['subscription_id'])
                ->get()
                ->getRowArray();
            $subscriptionId = $transaction['subscription_id'];
        }

        if (!$donation && !$subscription) {
            log_message('warning', "Doação/Assinatura não encontrada para pagamento {$transaction['asaas_payment_id']}");
            return;
        }

        // Se é doação única
        if ($donation) {
            // Verificar se já foi processada
            if ($donation['status'] === 'received') {
                log_message('info', "Doação {$donation['id']} já foi processada anteriormente");
                return;
            }

            // Iniciar transação no banco
            $this->db->transStart();

            // Atualizar status da doação
            $this->db->table('donations')
                ->where('id', $donation['id'])
                ->update([
                    'status' => 'received',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Atualizar valor arrecadado na campanha
            $campaign = $this->db->table('campaigns')
                ->where('id', $donation['campaign_id'])
                ->get()
                ->getRowArray();

            if ($campaign) {
                $newRaisedAmount = floatval($campaign['current_amount']) + floatval($donation['amount']);

                $this->db->table('campaigns')
                    ->where('id', $campaign['id'])
                    ->update([
                        'current_amount' => $newRaisedAmount,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                log_message('info', "Campanha {$campaign['id']}: valor arrecadado atualizado para R$ {$newRaisedAmount}");
            }

            // Enviar notificação para o doador
            $this->sendDonorNotification($donation, $campaign);

            // Enviar notificação para o criador da campanha
            $this->sendCampaignOwnerNotification($donation, $campaign);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', "Erro ao processar doação recebida: {$transaction['asaas_payment_id']}");
            } else {
                log_message('info', "Doação {$donation['id']} processada com sucesso!");
            }
        }

        // Se é assinatura recorrente
        if ($subscription) {
            // Iniciar transação no banco
            $this->db->transStart();

            // Atualizar última cobrança e status
            $this->db->table('subscriptions')
                ->where('id', $subscription['id'])
                ->update([
                    'status' => 'active',
                    'last_charge_date' => date('Y-m-d H:i:s'),
                    'next_charge_date' => $this->calculateNextChargeDate($subscription['billing_cycle']),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Criar registro de doação para cada cobrança da assinatura
            $donationData = [
                'user_id' => $subscription['user_id'],
                'campaign_id' => $subscription['campaign_id'],
                'amount' => $subscription['amount'],
                'status' => 'received',
                'payment_method' => $transaction['payment_method'],
                'is_anonymous' => $subscription['is_anonymous'] ?? 0,
                'comment' => 'Doação recorrente - Assinatura #' . $subscription['id'],
                'paid_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('donations')->insert($donationData);
            $newDonationId = $this->db->insertID();

            // Vincular transação à nova doação
            $this->db->table('asaas_transactions')
                ->where('id', $transaction['id'])
                ->update(['donation_id' => $newDonationId]);

            // Atualizar valor arrecadado na campanha
            $campaign = $this->db->table('campaigns')
                ->where('id', $subscription['campaign_id'])
                ->get()
                ->getRowArray();

            if ($campaign) {
                $newRaisedAmount = floatval($campaign['current_amount']) + floatval($subscription['amount']);

                $this->db->table('campaigns')
                    ->where('id', $campaign['id'])
                    ->update([
                        'current_amount' => $newRaisedAmount,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                log_message('info', "Campanha {$campaign['id']}: valor arrecadado atualizado para R$ {$newRaisedAmount}");
            }

            // Enviar notificação
            $donation = $this->db->table('donations')->where('id', $newDonationId)->get()->getRowArray();
            $this->sendDonorNotification($donation, $campaign);
            $this->sendCampaignOwnerNotification($donation, $campaign);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', "Erro ao processar assinatura recorrente: {$transaction['asaas_payment_id']}");
            } else {
                log_message('info', "Assinatura {$subscription['id']} processada com sucesso! Nova doação: {$newDonationId}");
            }
        }
    }

    /**
     * Calcula próxima data de cobrança baseada no ciclo
     */
    private function calculateNextChargeDate(string $billingCycle): string
    {
        $now = new \DateTime();

        switch ($billingCycle) {
            case 'monthly':
                $now->modify('+1 month');
                break;
            case 'quarterly':
                $now->modify('+3 months');
                break;
            case 'yearly':
                $now->modify('+1 year');
                break;
            default:
                $now->modify('+1 month');
        }

        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Processa estorno de pagamento
     */
    private function handlePaymentRefunded(array $transaction, array $payment)
    {
        log_message('info', "Pagamento estornado: {$transaction['asaas_payment_id']}");

        // Buscar doação relacionada
        if (!empty($transaction['donation_id'])) {
            $donation = $this->db->table('donations')
                ->where('id', $transaction['donation_id'])
                ->get()
                ->getRowArray();

            if (!$donation || $donation['status'] !== 'received') {
                log_message('warning', "Doação não encontrada ou não completada para estorno");
                return;
            }

            // Iniciar transação no banco
            $this->db->transStart();

            // Atualizar status da doação
            $this->db->table('donations')
                ->where('id', $donation['id'])
                ->update([
                    'status' => 'refunded',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Reverter valor arrecadado na campanha
            $campaign = $this->db->table('campaigns')
                ->where('id', $donation['campaign_id'])
                ->get()
                ->getRowArray();

            if ($campaign) {
                $newRaisedAmount = max(0, floatval($campaign['current_amount']) - floatval($donation['amount']));

                $this->db->table('campaigns')
                    ->where('id', $campaign['id'])
                    ->update([
                        'current_amount' => $newRaisedAmount,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                log_message('info', "Campanha {$campaign['id']}: estorno processado, novo valor: R$ {$newRaisedAmount}");
            }

            // Enviar notificação de estorno
            $this->sendRefundNotification($donation, $campaign);

            $this->db->transComplete();
        }

        // Se for assinatura, cancelá-la
        if (!empty($transaction['subscription_id'])) {
            $this->db->table('subscriptions')
                ->where('id', $transaction['subscription_id'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
    }

    /**
     * Processa pagamento vencido
     */
    private function handlePaymentOverdue(array $transaction, array $payment)
    {
        log_message('info', "Pagamento vencido: {$transaction['asaas_payment_id']}");

        // Atualizar status da doação se ainda estiver pendente
        if (!empty($transaction['donation_id'])) {
            $this->db->table('donations')
                ->where('id', $transaction['donation_id'])
                ->where('status', 'pending')
                ->update([
                    'status' => 'expired',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        // Para assinaturas, marcar como problema
        if (!empty($transaction['subscription_id'])) {
            $this->db->table('subscriptions')
                ->where('id', $transaction['subscription_id'])
                ->update([
                    'status' => 'payment_failed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Enviar notificação ao doador sobre falha
            $this->sendPaymentFailedNotification($transaction);
        }
    }

    /**
     * Processa pagamento deletado/cancelado
     */
    private function handlePaymentDeleted(array $transaction, array $payment)
    {
        log_message('info', "Pagamento cancelado: {$transaction['asaas_payment_id']}");

        // Atualizar status da doação
        if (!empty($transaction['donation_id'])) {
            $this->db->table('donations')
                ->where('id', $transaction['donation_id'])
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        // Para assinaturas, cancelar
        if (!empty($transaction['subscription_id'])) {
            $this->db->table('subscriptions')
                ->where('id', $transaction['subscription_id'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
    }

    /**
     * Envia notificação para o doador (push + SMS se tiver telefone)
     */
    private function sendDonorNotification(array $donation, ?array $campaign)
    {
        if (!$campaign) return;

        // Push notification (banco)
        $notificationData = [
            'user_id'    => $donation['user_id'],
            'campaign_id'=> $campaign['id'],
            'donation_id'=> $donation['id'],
            'type'       => 'donation_confirmed',
            'title'      => 'Doação confirmada!',
            'body'       => "Sua doação de R$ " . number_format($donation['amount'], 2, ',', '.') . " para '{$campaign['title']}' foi confirmada. Obrigado por fazer o bem!",
            'icon'       => base_url('assets/icons/heart-success.png'),
            'url'        => base_url("donations/{$donation['id']}"),
            'data'       => json_encode([
                'donation_id' => $donation['id'],
                'campaign_id' => $campaign['id'],
                'amount'      => $donation['amount'],
            ]),
            'channel'    => 'push',
            'status'     => 'sent',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('notifications')->insert($notificationData);

        // SMS ao doador (apenas se tiver usuário e telefone cadastrado)
        if (!empty($donation['user_id']) && $this->sms->isConfigured()) {
            $user = $this->db->table('users')
                ->select('name, phone')
                ->where('id', $donation['user_id'])
                ->get()->getRowArray();

            if ($user && !empty($user['phone'])) {
                $slug = $campaign['slug'] ?? '';
                $result = $this->sms->sendDonationConfirmed(
                    $user['phone'],
                    $user['name'],
                    $campaign['title'],
                    (float) $donation['amount'],
                    $slug,
                    (int) $donation['id']
                );
                log_message('info', "[SMS] Doador notificado por SMS: " . ($result['success'] ? 'OK' : ($result['error'] ?? 'falha')));
            }
        }

        log_message('info', "Notificação enviada ao doador {$donation['user_id']}");
    }

    /**
     * Envia notificação para o criador da campanha (push + SMS)
     * Também verifica se atingiu marco (25/50/75%) ou meta (100%)
     */
    private function sendCampaignOwnerNotification(array $donation, ?array $campaign)
    {
        if (!$campaign) return;

        $donorName = ($donation['is_anonymous'] ?? 0) ? 'Doador Anônimo' : $this->getUserName($donation['user_id']);

        // Push notification (banco)
        $notificationData = [
            'user_id'    => $campaign['user_id'],
            'campaign_id'=> $campaign['id'],
            'donation_id'=> $donation['id'],
            'type'       => 'new_donation',
            'title'      => 'Nova doação recebida!',
            'body'       => "{$donorName} doou R$ " . number_format($donation['amount'], 2, ',', '.') . " para sua campanha '{$campaign['title']}'",
            'icon'       => base_url('assets/icons/donation-received.png'),
            'url'        => base_url("campaigns/{$campaign['id']}/donations"),
            'data'       => json_encode([
                'donation_id' => $donation['id'],
                'campaign_id' => $campaign['id'],
                'amount'      => $donation['amount'],
            ]),
            'channel'    => 'push',
            'status'     => 'sent',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('notifications')->insert($notificationData);

        // SMS ao criador da campanha
        if ($this->sms->isConfigured()) {
            $creator = $this->db->table('users')
                ->select('name, phone')
                ->where('id', $campaign['user_id'])
                ->get()->getRowArray();

            if ($creator && !empty($creator['phone'])) {
                // Valores atualizados após essa doação
                $totalRaised = (float) ($campaign['current_amount'] ?? 0);
                $goalAmount  = (float) ($campaign['goal_amount'] ?? 0);

                // Verifica se atingiu meta (100%)
                if ($goalAmount > 0 && $totalRaised >= $goalAmount) {
                    $donorsCount = (int) $this->db->table('donations')
                        ->where('campaign_id', $campaign['id'])
                        ->where('status', 'received')
                        ->countAllResults();

                    $result = $this->sms->sendCampaignGoalReached(
                        $creator['phone'],
                        $creator['name'],
                        $campaign['title'],
                        $totalRaised,
                        $donorsCount,
                        (int) $campaign['id']
                    );
                    log_message('info', "[SMS] Meta atingida — SMS ao criador: " . ($result['success'] ? 'OK' : ($result['error'] ?? 'falha')));
                } else {
                    // Verifica se atingiu marco 25/50/75% (e só envia uma vez por marco)
                    $progress   = $goalAmount > 0 ? ($totalRaised / $goalAmount) * 100 : 0;
                    $milestones = [75, 50, 25];

                    foreach ($milestones as $milestone) {
                        if ($progress >= $milestone) {
                            // Verifica se esse marco já foi notificado
                            $alreadySent = $this->db->table('notifications')
                                ->where('campaign_id', $campaign['id'])
                                ->where('type', "milestone_{$milestone}")
                                ->countAllResults() > 0;

                            if (!$alreadySent) {
                                $result = $this->sms->sendCampaignMilestone(
                                    $creator['phone'],
                                    $creator['name'],
                                    $campaign['title'],
                                    $milestone,
                                    $totalRaised,
                                    (int) $campaign['id']
                                );
                                log_message('info', "[SMS] Marco {$milestone}% — SMS ao criador: " . ($result['success'] ? 'OK' : ($result['error'] ?? 'falha')));

                                // Registra marco como notificado
                                $this->db->table('notifications')->insert([
                                    'user_id'    => $campaign['user_id'],
                                    'campaign_id'=> $campaign['id'],
                                    'type'       => "milestone_{$milestone}",
                                    'title'      => "Marco {$milestone}% atingido",
                                    'body'       => "Campanha '{$campaign['title']}' atingiu {$milestone}%",
                                    'channel'    => 'sms',
                                    'status'     => 'sent',
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                            break; // Só notifica o maior marco ainda não notificado
                        }
                    }

                    // Sempre envia SMS de nova doação (independente de marco)
                    $result = $this->sms->sendCreatorNewDonation(
                        $creator['phone'],
                        $creator['name'],
                        $campaign['title'],
                        (float) $donation['amount'],
                        $totalRaised,
                        $goalAmount,
                        (int) $campaign['id']
                    );
                    log_message('info', "[SMS] Criador notificado de nova doação: " . ($result['success'] ? 'OK' : ($result['error'] ?? 'falha')));
                }
            }
        }

        log_message('info', "Notificação enviada ao criador da campanha {$campaign['user_id']}");
    }

    /**
     * Envia notificação de estorno (push + SMS)
     */
    private function sendRefundNotification(array $donation, ?array $campaign)
    {
        if (!$campaign) return;

        // Push notification (banco)
        $notificationData = [
            'user_id'    => $donation['user_id'],
            'campaign_id'=> $campaign['id'],
            'donation_id'=> $donation['id'],
            'type'       => 'donation_refunded',
            'title'      => 'Doação estornada',
            'body'       => "Sua doação de R$ " . number_format($donation['amount'], 2, ',', '.') . " para '{$campaign['title']}' foi estornada.",
            'icon'       => base_url('assets/icons/refund.png'),
            'url'        => base_url("donations/{$donation['id']}"),
            'data'       => json_encode([
                'donation_id' => $donation['id'],
                'campaign_id' => $campaign['id'],
                'amount'      => $donation['amount'],
            ]),
            'channel'    => 'push',
            'status'     => 'sent',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('notifications')->insert($notificationData);

        // SMS ao doador informando o estorno
        if (!empty($donation['user_id']) && $this->sms->isConfigured()) {
            $user = $this->db->table('users')
                ->select('name, phone')
                ->where('id', $donation['user_id'])
                ->get()->getRowArray();

            if ($user && !empty($user['phone'])) {
                $this->sms->sendDonationRefunded(
                    $user['phone'],
                    $user['name'],
                    (float) $donation['amount'],
                    $campaign['title']
                );
            }
        }
    }

    /**
     * Envia notificação de falha no pagamento
     */
    private function sendPaymentFailedNotification(array $transaction)
    {
        if (empty($transaction['subscription_id'])) return;

        $subscription = $this->db->table('subscriptions')
            ->where('id', $transaction['subscription_id'])
            ->get()
            ->getRowArray();

        if (!$subscription) return;

        $notificationData = [
            'user_id' => $subscription['user_id'],
            'campaign_id' => $subscription['campaign_id'],
            'donation_id' => null,
            'type' => 'payment_failed',
            'title' => 'Falha no pagamento recorrente',
            'body' => "Não foi possível processar sua doação recorrente de R$ " . number_format($subscription['amount'], 2, ',', '.') . ". Por favor, atualize seus dados de pagamento.",
            'icon' => base_url('assets/icons/payment-error.png'),
            'url' => base_url("subscriptions/{$subscription['id']}/update-payment"),
            'data' => json_encode([
                'subscription_id' => $subscription['id'],
                'amount' => $subscription['amount']
            ]),
            'channel' => 'push',
            'status' => 'sent',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('notifications')->insert($notificationData);
    }

    /**
     * Obtém nome do usuário
     */
    private function getUserName(int $userId): string
    {
        $user = $this->db->table('users')
            ->select('name')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        return $user ? $user['name'] : 'Usuário';
    }
}

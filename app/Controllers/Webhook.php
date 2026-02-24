<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Donation as DonationModel;
use App\Models\CampaignModel;
use App\Models\TransactionModel;
use App\Libraries\AsaasLibrary;

/**
 * Webhook Controller
 *
 * Recebe webhooks do Asaas para atualizar status de pagamentos
 */
class Webhook extends BaseController
{
    protected $donationModel;
    protected $campaignModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->donationModel = new DonationModel();
        $this->campaignModel = new CampaignModel();
        $this->transactionModel = new TransactionModel();
    }

    /**
     * Webhook do Asaas
     * POST /webhook/asaas
     */
    public function asaas()
    {
        // Validar token de segurança do webhook
        $webhookToken = $this->request->getHeaderLine('asaas-access-token');
        $expectedToken = getenv('ASAAS_WEBHOOK_TOKEN');

        // Em producao, token DEVE estar configurado
        if (ENVIRONMENT === 'production' && empty($expectedToken)) {
            log_message('critical', 'ASAAS_WEBHOOK_TOKEN nao configurado em producao!');
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Webhook not configured']);
        }

        // Validar token se estiver configurado
        if (!empty($expectedToken)) {
            if (empty($webhookToken) || !hash_equals($expectedToken, $webhookToken)) {
                log_message('error', 'Webhook com token invalido');
                return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
            }
        } elseif (ENVIRONMENT !== 'production') {
            log_message('warning', 'Webhook sem validacao de token (ambiente de desenvolvimento)');
        }

        // Pegar payload bruto
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        // Log do webhook recebido (sem dados sensiveis)
        $eventType = $data['event'] ?? 'unknown';
        $paymentId = $data['payment']['id'] ?? 'unknown';
        log_message('info', "Webhook Asaas recebido: evento={$eventType}, payment_id={$paymentId}");

        if (!$data || !isset($data['event'])) {
            log_message('error', 'Webhook inválido: dados não encontrados');
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid webhook']);
        }

        $event = $data['event'];
        $payment = $data['payment'] ?? [];

        if (empty($payment['id'])) {
            log_message('error', 'Webhook sem ID do pagamento');
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Payment ID missing']);
        }

        $asaasPaymentId = $payment['id'];

        // Buscar doação pelo ID do pagamento Asaas
        $donation = $this->donationModel->getByAsaasPaymentId($asaasPaymentId);

        if (!$donation) {
            log_message('warning', 'Doação não encontrada para payment_id: ' . $asaasPaymentId);
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Donation not found']);
        }

        try {
            switch ($event) {
                case 'PAYMENT_CREATED':
                    // Pagamento criado (já está pending)
                    log_message('info', 'Pagamento criado: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_AWAITING_RISK_ANALYSIS':
                    // Aguardando análise de risco
                    log_message('info', 'Pagamento aguardando análise: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_APPROVED_BY_RISK_ANALYSIS':
                    // Aprovado pela análise de risco
                    log_message('info', 'Pagamento aprovado pela análise: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    // Pagamento confirmado/recebido
                    $this->donationModel->confirmPayment($donation['id']);
                    log_message('info', 'Pagamento confirmado: ' . $asaasPaymentId);

                    // Registrar transação
                    $campaign = $this->campaignModel->find($donation['campaign_id']);
                    $this->transactionModel->recordDonationReceived(
                        $campaign['user_id'],
                        $donation['id'],
                        $donation['net_amount'],
                        'Doação recebida: ' . $campaign['title']
                    );

                    // Registrar taxa (se houver)
                    if ($donation['platform_fee'] > 0) {
                        $this->transactionModel->recordPlatformFee(
                            $campaign['user_id'],
                            $donation['id'],
                            $donation['platform_fee']
                        );
                    }

                    // TODO: Enviar WhatsApp notification
                    // TODO: Enviar email de agradecimento

                    log_message('info', 'Pagamento recebido: ' . $asaasPaymentId . ' - R$ ' . $donation['amount']);
                    break;

                case 'PAYMENT_OVERDUE':
                    // Pagamento vencido (boleto não pago)
                    log_message('info', 'Pagamento vencido: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_DELETED':
                    // Pagamento deletado
                    log_message('info', 'Pagamento deletado: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_RESTORED':
                    // Pagamento restaurado
                    log_message('info', 'Pagamento restaurado: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_REFUNDED':
                    // Pagamento reembolsado
                    $this->donationModel->refundPayment($donation['id']);

                    // Atualizar estatísticas da campanha (subtrair)
                    $campaign = $this->campaignModel->find($donation['campaign_id']);
                    $newAmount = max(0, $campaign['current_amount'] - $donation['amount']);
                    $newDonorsCount = max(0, $campaign['donors_count'] - 1);

                    $this->campaignModel->update($donation['campaign_id'], [
                        'current_amount' => $newAmount,
                        'donors_count' => $newDonorsCount
                    ]);

                    // Registrar transação de reembolso
                    $this->transactionModel->recordRefund(
                        $campaign['user_id'],
                        $donation['id'],
                        $donation['amount']
                    );

                    log_message('info', 'Pagamento reembolsado: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_RECEIVED_IN_CASH':
                    // Pagamento recebido em dinheiro
                    $this->donationModel->markAsReceived($donation['id']);
                    $this->campaignModel->updateDonationStats($donation['campaign_id'], $donation['amount']);
                    log_message('info', 'Pagamento recebido em dinheiro: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_CHARGEBACK_REQUESTED':
                    // Chargeback solicitado
                    log_message('warning', 'Chargeback solicitado: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_CHARGEBACK_DISPUTE':
                    // Disputa de chargeback
                    log_message('warning', 'Disputa de chargeback: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_AWAITING_CHARGEBACK_REVERSAL':
                    // Aguardando reversão de chargeback
                    log_message('info', 'Aguardando reversão de chargeback: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_DUNNING_RECEIVED':
                    // Pagamento em atraso recebido
                    $this->donationModel->markAsReceived($donation['id']);
                    $this->campaignModel->updateDonationStats($donation['campaign_id'], $donation['amount']);
                    log_message('info', 'Pagamento em atraso recebido: ' . $asaasPaymentId);
                    break;

                case 'PAYMENT_DUNNING_REQUESTED':
                    // Cobrança em atraso solicitada
                    log_message('info', 'Cobrança em atraso: ' . $asaasPaymentId);
                    break;

                default:
                    log_message('info', 'Evento não tratado: ' . $event);
                    break;
            }

            return $this->response->setJSON(['success' => true]);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao processar webhook: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal error']);
        }
    }
}

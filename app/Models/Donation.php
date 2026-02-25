<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para gerenciar doações
 */
class Donation extends Model
{
    protected $table = 'donations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'campaign_id',
        'user_id',
        'donor_name',
        'donor_email',
        'donor_cpf',
        'amount',
        'gateway_fee',
        'platform_fee',
        'net_amount',
        'payment_method',
        'status',
        'asaas_payment_id',
        'asaas_customer_id',
        'pix_qr_code',
        'pix_copy_paste',
        'boleto_url',
        'is_anonymous',
        'is_recurring',
        'subscription_id',
        'message',
        'paid_at',
        'refunded_at',
        'api_response',
    ];

    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'donor_name' => 'required|string|max_length[255]',
        'donor_email' => 'required|valid_email',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_method' => 'required|in_list[pix,credit_card,boleto]',
    ];

    protected $validationMessages = [
        'campaign_id' => [
            'required' => 'ID da campanha é obrigatório',
        ],
        'donor_name' => [
            'required' => 'Nome do doador é obrigatório',
        ],
        'donor_email' => [
            'required' => 'Email do doador é obrigatório',
            'valid_email' => 'Email inválido',
        ],
        'amount' => [
            'required' => 'Valor da doação é obrigatório',
            'greater_than' => 'Valor deve ser maior que zero',
        ],
        'payment_method' => [
            'required' => 'Método de pagamento é obrigatório',
            'in_list' => 'Método de pagamento inválido',
        ],
    ];

    // Callbacks
    protected $beforeInsert = ['calculateFees'];
    protected $beforeUpdate = ['calculateFees'];
    protected $afterUpdate = ['notifyDonationConfirmed'];

    /**
     * Calcula taxas antes de inserir/atualizar
     */
    protected function calculateFees(array $data): array
    {
        if (!isset($data['data']['amount'])) {
            return $data;
        }

        $amount = $data['data']['amount'];
        $campaignId = $data['data']['campaign_id'] ?? null;

        if (!$campaignId) {
            return $data;
        }

        // Busca tipo da campanha
        $campaignModel = new \App\Models\Campaign();
        $campaign = $campaignModel->find($campaignId);

        if (!$campaign) {
            return $data;
        }

        // Determina tipo para cálculo de taxa
        $campaignType = 'other';
        if (!empty($campaign['category'])) {
            if (stripos($campaign['category'], 'médica') !== false ||
                stripos($campaign['category'], 'saúde') !== false) {
                $campaignType = 'medical';
            } elseif (stripos($campaign['category'], 'social') !== false) {
                $campaignType = 'social';
            }
        }

        // Calcula taxas
        $asaasConfig = config('Asaas');
        $paymentMethod = $data['data']['payment_method'] ?? 'pix';
        $fees = $asaasConfig->calculateFees($campaignType, $paymentMethod, $amount);

        $data['data']['gateway_fee'] = $fees['gateway_fee'];
        $data['data']['platform_fee'] = $fees['platform_fee'];
        $data['data']['net_amount'] = $fees['net_amount'];

        return $data;
    }

    /**
     * Dispara notificações quando doação é confirmada
     */
    protected function notifyDonationConfirmed(array $data): array
    {
        // Verificar se o status mudou para 'confirmed' ou 'received'
        if (isset($data['data']['status']) &&
            ($data['data']['status'] === 'confirmed' || $data['data']['status'] === 'received')) {

            // Buscar status anterior
            $oldDonation = $this->find($data['id']);

            // Só notificar se o status mudou (não estava confirmado antes)
            if ($oldDonation &&
                $oldDonation['status'] !== 'confirmed' &&
                $oldDonation['status'] !== 'received') {

                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyCreatorNewDonation($data['id']);
                } catch (\Exception $e) {
                    log_message('error', 'Erro ao disparar notificações de doação: ' . $e->getMessage());
                }
            }
        }

        return $data;
    }

    // Relacionamentos

    /**
     * Retorna a campanha desta doação
     */
    public function getCampaign(int $donationId)
    {
        $donation = $this->find($donationId);
        if (!$donation) {
            return null;
        }

        $campaignModel = new \App\Models\Campaign();
        return $campaignModel->find($donation['campaign_id']);
    }

    /**
     * Retorna o doador (se não for anônimo)
     */
    public function getDonor(int $donationId)
    {
        $donation = $this->find($donationId);
        if (!$donation || !$donation['user_id']) {
            return null;
        }

        $userModel = new \App\Models\User();
        return $userModel->find($donation['user_id']);
    }

    // Consultas específicas

    /**
     * Busca doações por campanha
     */
    public function getByCampaign(int $campaignId, bool $onlyConfirmed = false): array
    {
        $builder = $this->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC');

        if ($onlyConfirmed) {
            $builder->where('status', 'received');
        }

        return $builder->findAll();
    }

    /**
     * Busca doações por doador
     */
    public function getByDonor(int $userId, bool $onlyConfirmed = false): array
    {
        $builder = $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC');

        if ($onlyConfirmed) {
            $builder->where('status', 'received');
        }

        return $builder->findAll();
    }

    /**
     * Busca doações por email (para doadores não cadastrados)
     */
    public function getByEmail(string $email, bool $onlyConfirmed = false): array
    {
        $builder = $this->where('donor_email', $email)
            ->orderBy('created_at', 'DESC');

        if ($onlyConfirmed) {
            $builder->where('status', 'received');
        }

        return $builder->findAll();
    }

    /**
     * Busca doação pelo ID de pagamento do Asaas
     */
    public function getByAsaasPaymentId(string $paymentId): ?array
    {
        return $this->where('asaas_payment_id', $paymentId)->first();
    }

    /**
     * Busca doações pendentes
     */
    public function getPending(): array
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca doações confirmadas
     */
    public function getConfirmed(int $limit = null): array
    {
        $builder = $this->where('status', 'received')
            ->orderBy('paid_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Busca doações recorrentes ativas
     */
    public function getRecurring(): array
    {
        return $this->where('is_recurring', true)
            ->where('status', 'received')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    // Estatísticas

    /**
     * Total arrecadado por campanha
     */
    public function getTotalByCampaign(int $campaignId): float
    {
        $result = $this->selectSum('amount')
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->first();

        return $result['amount'] ?? 0.0;
    }

    /**
     * Número de doadores únicos por campanha
     */
    public function getUniqueDonorsByCampaign(int $campaignId): int
    {
        // Contar doadores únicos considerando user_id, email ou contando doações anônimas sem identificação
        $db = \Config\Database::connect();
        $builder = $db->table('donations');

        $result = $builder
            ->select("COUNT(DISTINCT
                CASE
                    WHEN user_id IS NOT NULL THEN CONCAT('user_', user_id)
                    WHEN donor_email IS NOT NULL AND donor_email != '' THEN CONCAT('email_', donor_email)
                    ELSE CONCAT('anon_', id)
                END
            ) as unique_donors", false)
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return (int)($result['unique_donors'] ?? 0);
    }

    /**
     * Total de doações de um usuário
     */
    public function getTotalByDonor(int $userId): float
    {
        $result = $this->selectSum('amount')
            ->where('user_id', $userId)
            ->where('status', 'received')
            ->first();

        return $result['amount'] ?? 0.0;
    }

    /**
     * Doações recentes da plataforma
     */
    public function getRecentDonations(int $limit = 10): array
    {
        return $this->where('status', 'received')
            ->where('is_anonymous', false)
            ->orderBy('paid_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Estatísticas gerais
     */
    public function getStats(): array
    {
        $totalConfirmed = $this->selectSum('amount')
            ->where('status', 'received')
            ->first();

        $totalPlatformFee = $this->selectSum('platform_fee')
            ->where('status', 'received')
            ->first();

        $totalGatewayFee = $this->selectSum('gateway_fee')
            ->where('status', 'received')
            ->first();

        $count = $this->where('status', 'received')->countAllResults();

        return [
            'total_donations' => $count,
            'total_amount' => $totalConfirmed['amount'] ?? 0.0,
            'platform_revenue' => $totalPlatformFee['platform_fee'] ?? 0.0,
            'gateway_fees' => $totalGatewayFee['gateway_fee'] ?? 0.0,
        ];
    }

    // Ações

    /**
     * Confirma pagamento
     */
    public function confirmPayment(int $donationId): bool
    {
        $donation = $this->find($donationId);
        if (!$donation) {
            return false;
        }

        // Atualiza doação
        $updated = $this->update($donationId, [
            'status' => 'received',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        if ($updated) {
            // Atualiza valor arrecadado da campanha
            $campaignModel = new \App\Models\CampaignModel();
            $campaign = $campaignModel->find($donation['campaign_id']);

            if ($campaign) {
                $currentRaised = $campaign['current_amount'] ?? 0;
                $campaignModel->update($campaign['id'], [
                    'current_amount' => $currentRaised + $donation['amount'],
                ]);
            }
        }

        return $updated;
    }

    /**
     * Cancela doação
     */
    public function cancelPayment(int $donationId): bool
    {
        return $this->update($donationId, [
            'status' => 'refunded',
        ]);
    }

    /**
     * Marca como vencido
     */
    public function markAsOverdue(int $donationId): bool
    {
        return $this->update($donationId, [
            'status' => 'pending',
        ]);
    }

    /**
     * Estorna doação
     */
    public function refundPayment(int $donationId): bool
    {
        $donation = $this->find($donationId);
        if (!$donation || $donation['status'] !== 'received') {
            return false;
        }

        // Atualiza doação
        $updated = $this->update($donationId, [
            'status' => 'refunded',
            'refunded_at' => date('Y-m-d H:i:s'),
        ]);

        if ($updated) {
            // Subtrai do valor arrecadado da campanha
            $campaignModel = new \App\Models\CampaignModel();
            $campaign = $campaignModel->find($donation['campaign_id']);

            if ($campaign) {
                $campaignModel->update($campaign['id'], [
                    'current_amount' => max(0, $campaign['current_amount'] - $donation['amount']),
                ]);
            }
        }

        return $updated;
    }

    /**
     * Marca pagamento como recebido (para dinheiro/PIX manual)
     */
    public function markAsReceived(int $donationId): bool
    {
        return $this->confirmPayment($donationId);
    }

    /**
     * Formata nome do doador (anônimo se necessário)
     */
    public function getDisplayName(array $donation): string
    {
        return $donation['is_anonymous'] ? 'Doador Anônimo' : $donation['donor_name'];
    }

    /**
     * Retorna método de pagamento formatado
     */
    public static function getPaymentMethodLabel(string $method): string
    {
        $labels = [
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'boleto' => 'Boleto Bancário',
        ];

        return $labels[$method] ?? $method;
    }

    /**
     * Retorna status formatado
     */
    public static function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'overdue' => 'Vencido',
            'refunded' => 'Estornado',
            'cancelled' => 'Cancelado',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Retorna classe CSS para badge de status
     */
    public static function getStatusBadgeClass(string $status): string
    {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }
}

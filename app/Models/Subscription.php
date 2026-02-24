<?php

namespace App\Models;

use CodeIgniter\Model;

class Subscription extends Model
{
    protected $table            = 'subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id', 'user_id', 'donor_name', 'donor_email', 'donor_cpf',
        'amount', 'payment_method', 'cycle', 'status',
        'asaas_subscription_id', 'asaas_customer_id',
        'next_due_date', 'started_at', 'cancelled_at', 'api_response'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'donor_name' => 'required|min_length[3]',
        'donor_email' => 'required|valid_email',
        'amount' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Busca assinaturas ativas de uma campanha
     */
    public function getActiveByCampaign(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
            ->where('status', 'active')
            ->findAll();
    }

    /**
     * Busca assinaturas de um usuário
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Cancela uma assinatura
     */
    public function cancel(int $id): bool
    {
        return $this->update($id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Calcula próxima data de cobrança
     */
    public function calculateNextDueDate(string $cycle): string
    {
        $intervals = [
            'monthly' => '+1 month',
            'quarterly' => '+3 months',
            'semiannual' => '+6 months',
            'yearly' => '+1 year'
        ];

        return date('Y-m-d', strtotime($intervals[$cycle] ?? '+1 month'));
    }

    public static function getCycleLabel(string $cycle): string
    {
        return [
            'monthly' => 'Mensal',
            'quarterly' => 'Trimestral',
            'semiannual' => 'Semestral',
            'yearly' => 'Anual'
        ][$cycle] ?? 'Mensal';
    }

    public static function getStatusLabel(string $status): string
    {
        return [
            'active' => 'Ativa',
            'cancelled' => 'Cancelada',
            'suspended' => 'Suspensa',
            'expired' => 'Expirada'
        ][$status] ?? $status;
    }
}

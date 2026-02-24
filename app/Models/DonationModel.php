<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DonationModel
 *
 * Model para gerenciar doações
 */
class DonationModel extends Model
{
    protected $table            = 'donations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'campaign_id',
        'user_id',
        'donor_name',
        'donor_email',
        'amount',
        'platform_fee',
        'payment_gateway_fee',
        'net_amount',
        'payment_method',
        'asaas_payment_id',
        'status',
        'is_anonymous',
        'message',
        'pix_qr_code',
        'pix_copy_paste',
        'boleto_url',
        'paid_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Regras de Validação
    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_method' => 'required|in_list[credit_card,boleto,pix]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Busca doações de uma campanha
     */
    public function getCampaignDonations($campaignId, $limit = 50)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->orderBy('paid_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Busca doações de um usuário
     * @param int $userId ID do usuario
     * @param int $limit Limite de resultados (padrao 100 para seguranca)
     */
    public function getUserDonations($userId, $limit = 100)
    {
        return $this->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('donations.user_id', $userId)
            ->orderBy('donations.created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Busca doação por ID do pagamento Asaas
     */
    public function getDonationByAsaasId($asaasPaymentId)
    {
        return $this->where('asaas_payment_id', $asaasPaymentId)->first();
    }

    /**
     * Calcula total arrecadado em uma campanha
     */
    public function getCampaignTotal($campaignId)
    {
        $result = $this->selectSum('amount')
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return $result['amount'] ?? 0;
    }

    /**
     * Conta doadores únicos de uma campanha
     */
    public function countUniqueDonors($campaignId)
    {
        return $this->distinct()
            ->select('user_id, donor_email')
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->groupBy('COALESCE(user_id, donor_email)')
            ->countAllResults();
    }

    /**
     * Busca últimas doações (feed público)
     */
    public function getRecentDonations($limit = 10)
    {
        return $this->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('donations.status', 'received')
            ->where('donations.is_anonymous', false)
            ->orderBy('donations.paid_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Atualiza status da doação para confirmada
     */
    public function markAsConfirmed($donationId)
    {
        return $this->update($donationId, [
            'status' => 'confirmed'
        ]);
    }

    /**
     * Atualiza status da doação para recebida
     */
    public function markAsReceived($donationId)
    {
        return $this->update($donationId, [
            'status' => 'received',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Processa reembolso
     */
    public function refund($donationId)
    {
        return $this->update($donationId, [
            'status' => 'refunded'
        ]);
    }

    /**
     * Estatísticas gerais de doações
     */
    public function getGlobalStats()
    {
        $builder = $this->db->table($this->table);

        return $builder->select('
            COUNT(*) as total_donations,
            SUM(amount) as total_amount,
            AVG(amount) as average_donation,
            COUNT(DISTINCT campaign_id) as campaigns_with_donations,
            SUM(CASE WHEN payment_method = "pix" THEN 1 ELSE 0 END) as pix_count,
            SUM(CASE WHEN payment_method = "credit_card" THEN 1 ELSE 0 END) as credit_card_count,
            SUM(CASE WHEN payment_method = "boleto" THEN 1 ELSE 0 END) as boleto_count
        ')
        ->where('status', 'received')
        ->get()
        ->getRowArray();
    }

    /**
     * Top doadores
     */
    public function getTopDonors($limit = 10)
    {
        return $this->select('
            COALESCE(users.name, donations.donor_name) as donor_name,
            SUM(donations.amount) as total_donated,
            COUNT(*) as donation_count
        ')
        ->join('users', 'users.id = donations.user_id', 'left')
        ->where('donations.status', 'received')
        ->where('donations.is_anonymous', false)
        ->groupBy('COALESCE(donations.user_id, donations.donor_email)')
        ->orderBy('total_donated', 'DESC')
        ->limit($limit)
        ->get()
        ->getResultArray();
    }
}

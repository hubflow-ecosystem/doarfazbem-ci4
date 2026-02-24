<?php

namespace App\Models;

use CodeIgniter\Model;

class RaffleCampaignDistributionModel extends Model
{
    protected $table = 'raffle_campaign_distributions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'purchase_id',
        'campaign_id',
        'percentage',
        'amount',
        'transferred',
        'transferred_at',
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';

    /**
     * Cria distribuições para campanhas escolhidas
     */
    public function createDistributions(int $purchaseId, array $campaignIds, float $totalAmount): bool
    {
        if (empty($campaignIds)) {
            return false;
        }

        // Máximo 5 campanhas, dividido igualmente
        $campaignIds = array_slice($campaignIds, 0, 5);
        $percentage = 100 / count($campaignIds);
        $amountPerCampaign = $totalAmount / count($campaignIds);

        foreach ($campaignIds as $campaignId) {
            $this->insert([
                'purchase_id' => $purchaseId,
                'campaign_id' => $campaignId,
                'percentage' => round($percentage, 2),
                'amount' => round($amountPerCampaign, 2),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    /**
     * Busca distribuições de uma compra
     */
    public function getPurchaseDistributions(int $purchaseId): array
    {
        return $this->select('raffle_campaign_distributions.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = raffle_campaign_distributions.campaign_id')
            ->where('purchase_id', $purchaseId)
            ->findAll();
    }

    /**
     * Busca total distribuído para uma campanha
     */
    public function getCampaignTotal(int $campaignId): float
    {
        $result = $this->selectSum('amount', 'total')
            ->join('raffle_purchases', 'raffle_purchases.id = raffle_campaign_distributions.purchase_id')
            ->where('campaign_id', $campaignId)
            ->where('raffle_purchases.payment_status', 'paid')
            ->first();

        return (float)($result['total'] ?? 0);
    }

    /**
     * Busca distribuições pendentes de transferência
     */
    public function getPendingTransfers(): array
    {
        return $this->select('raffle_campaign_distributions.*, campaigns.title as campaign_title, raffle_purchases.paid_at')
            ->join('campaigns', 'campaigns.id = raffle_campaign_distributions.campaign_id')
            ->join('raffle_purchases', 'raffle_purchases.id = raffle_campaign_distributions.purchase_id')
            ->where('raffle_campaign_distributions.transferred', 0)
            ->where('raffle_purchases.payment_status', 'paid')
            ->findAll();
    }

    /**
     * Marca distribuição como transferida
     */
    public function markAsTransferred(int $distributionId): bool
    {
        return $this->update($distributionId, [
            'transferred' => 1,
            'transferred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Estatísticas de distribuição por campanha
     */
    public function getCampaignStats(int $campaignId): array
    {
        $result = $this->selectSum('amount', 'total_amount')
            ->selectCount('id', 'total_contributions')
            ->join('raffle_purchases', 'raffle_purchases.id = raffle_campaign_distributions.purchase_id')
            ->where('campaign_id', $campaignId)
            ->where('raffle_purchases.payment_status', 'paid')
            ->first();

        return [
            'total_amount' => (float)($result['total_amount'] ?? 0),
            'total_contributions' => (int)($result['total_contributions'] ?? 0),
        ];
    }
}

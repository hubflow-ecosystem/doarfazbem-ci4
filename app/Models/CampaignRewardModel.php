<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignRewardModel extends Model
{
    protected $table            = 'campaign_rewards';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'campaign_id',
        'title',
        'description',
        'min_amount',
        'max_quantity',
        'claimed_quantity',
        'delivery_date',
        'image',
        'is_active',
        'sort_order'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'required|min_length[10]',
        'min_amount' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Busca recompensas de uma campanha ordenadas por valor
     */
    public function getRewardsByCampaign($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('is_active', 1)
            ->orderBy('min_amount', 'ASC')
            ->findAll();
    }

    /**
     * Busca recompensa disponível para um valor
     */
    public function getAvailableReward($campaignId, $amount)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('is_active', 1)
            ->where('min_amount <=', $amount)
            ->groupStart()
                ->where('max_quantity IS NULL')
                ->orWhere('claimed_quantity < max_quantity')
            ->groupEnd()
            ->orderBy('min_amount', 'DESC')
            ->first();
    }

    /**
     * Incrementa contador de recompensas reivindicadas
     */
    public function claimReward($rewardId)
    {
        return $this->set('claimed_quantity', 'claimed_quantity + 1', false)
            ->where('id', $rewardId)
            ->update();
    }

    /**
     * Verifica se recompensa está disponível
     */
    public function isAvailable($rewardId)
    {
        $reward = $this->find($rewardId);

        if (!$reward || !$reward['is_active']) {
            return false;
        }

        if ($reward['max_quantity'] === null) {
            return true;
        }

        return $reward['claimed_quantity'] < $reward['max_quantity'];
    }

    /**
     * Calcula quantidade restante
     */
    public function getRemainingQuantity($rewardId)
    {
        $reward = $this->find($rewardId);

        if (!$reward || $reward['max_quantity'] === null) {
            return null; // Ilimitado
        }

        return max(0, $reward['max_quantity'] - $reward['claimed_quantity']);
    }

    /**
     * Busca recompensas com estatísticas
     */
    public function getRewardsWithStats($campaignId)
    {
        $rewards = $this->getRewardsByCampaign($campaignId);

        foreach ($rewards as &$reward) {
            $remaining = $this->getRemainingQuantity($reward['id']);
            $reward['remaining'] = $remaining;
            $reward['is_sold_out'] = $remaining !== null && $remaining <= 0;
            $reward['percentage_claimed'] = $reward['max_quantity']
                ? round(($reward['claimed_quantity'] / $reward['max_quantity']) * 100)
                : 0;
        }

        return $rewards;
    }
}

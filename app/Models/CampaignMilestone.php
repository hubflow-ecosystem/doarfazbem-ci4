<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model: CampaignMilestone
 * Rastreia marcos de campanha já notificados (10%, 20%, 30%, etc)
 */
class CampaignMilestone extends Model
{
    protected $table            = 'campaign_milestones_notified';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id',
        'milestone_percentage',
        'notified_at',
    ];

    // Dates
    protected $useTimestamps = false; // Usamos notified_at manual

    /**
     * Verificar se um marco já foi notificado
     */
    public function wasNotified(int $campaignId, int $percentage): bool
    {
        $result = $this->where('campaign_id', $campaignId)
                       ->where('milestone_percentage', $percentage)
                       ->first();

        return !empty($result);
    }

    /**
     * Marcar um marco como notificado
     */
    public function markAsNotified(int $campaignId, int $percentage): bool
    {
        // Evitar duplicatas
        if ($this->wasNotified($campaignId, $percentage)) {
            return true;
        }

        return $this->insert([
            'campaign_id' => $campaignId,
            'milestone_percentage' => $percentage,
            'notified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Buscar marcos notificados de uma campanha
     */
    public function getCampaignMilestones(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
                    ->orderBy('milestone_percentage', 'ASC')
                    ->findAll();
    }

    /**
     * Calcular próximo marco a ser atingido
     */
    public function getNextMilestone(int $campaignId, float $currentPercentage): ?int
    {
        // Marcos: 10, 20, 30, 40, 50, 60, 70, 80, 90, 100
        $milestones = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

        foreach ($milestones as $milestone) {
            if ($currentPercentage >= $milestone && !$this->wasNotified($campaignId, $milestone)) {
                return $milestone;
            }
        }

        return null;
    }
}

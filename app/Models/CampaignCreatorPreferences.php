<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model: CampaignCreatorPreferences
 * Gerencia preferências de notificação dos criadores de campanhas
 */
class CampaignCreatorPreferences extends Model
{
    protected $table            = 'campaign_creator_preferences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'campaign_id',
        'notify_donation_email',
        'notify_donation_push',
        'notify_daily_summary',
        'notify_weekly_summary',
        'push_token',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'user_id'     => 'required|integer',
        'campaign_id' => 'permit_empty|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Buscar ou criar preferências para um usuário/campanha
     */
    public function getOrCreatePreferences(int $userId, ?int $campaignId = null): array
    {
        $prefs = $this->where('user_id', $userId)
                      ->where('campaign_id', $campaignId)
                      ->first();

        if (!$prefs) {
            // Criar preferências padrão
            $data = [
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'notify_donation_email' => 1,
                'notify_donation_push' => 1,
                'notify_daily_summary' => 0,
                'notify_weekly_summary' => 1,
            ];

            $this->insert($data);
            $prefs = $this->find($this->getInsertID());
        }

        return $prefs;
    }

    /**
     * Buscar preferências globais do usuário (campaign_id = null)
     */
    public function getGlobalPreferences(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('campaign_id', null)
                    ->first();
    }

    /**
     * Buscar preferências de uma campanha específica
     */
    public function getCampaignPreferences(int $userId, int $campaignId): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('campaign_id', $campaignId)
                    ->first();
    }

    /**
     * Buscar todas as preferências de um criador
     */
    public function getUserPreferences(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('campaign_id', 'ASC')
                    ->findAll();
    }

    /**
     * Atualizar token FCM do criador
     */
    public function updatePushToken(int $userId, string $token): bool
    {
        // Atualizar em todas as preferências do usuário
        return $this->where('user_id', $userId)
                    ->set(['push_token' => $token])
                    ->update();
    }

    /**
     * Verificar se criador deve receber email de doação
     */
    public function shouldNotifyDonationEmail(int $userId, int $campaignId): bool
    {
        $prefs = $this->getCampaignPreferences($userId, $campaignId);

        if (!$prefs) {
            // Se não tem preferências específicas, buscar global
            $prefs = $this->getGlobalPreferences($userId);
        }

        return $prefs ? (bool)$prefs['notify_donation_email'] : true; // Default: true
    }

    /**
     * Verificar se criador deve receber push de doação
     */
    public function shouldNotifyDonationPush(int $userId, int $campaignId): bool
    {
        $prefs = $this->getCampaignPreferences($userId, $campaignId);

        if (!$prefs) {
            $prefs = $this->getGlobalPreferences($userId);
        }

        return $prefs ? (bool)$prefs['notify_donation_push'] : true; // Default: true
    }

    /**
     * Buscar criadores que querem resumo diário
     */
    public function getUsersForDailySummary(): array
    {
        return $this->where('notify_daily_summary', 1)
                    ->groupBy('user_id')
                    ->findAll();
    }

    /**
     * Buscar criadores que querem resumo semanal
     */
    public function getUsersForWeeklySummary(): array
    {
        return $this->where('notify_weekly_summary', 1)
                    ->groupBy('user_id')
                    ->findAll();
    }
}

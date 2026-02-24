<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model: AdminNotificationPreferences
 * Gerencia preferências de notificação dos administradores da plataforma
 */
class AdminNotificationPreferences extends Model
{
    protected $table            = 'admin_notification_preferences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'admin_user_id',
        'notify_new_campaign_email',
        'notify_new_campaign_push',
        'notify_weekly_donations_report',
        'notify_campaign_milestones',
        'enable_realtime_dashboard',
        'push_token',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'admin_user_id' => 'required|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Buscar ou criar preferências para um admin
     */
    public function getOrCreatePreferences(int $adminUserId): array
    {
        $prefs = $this->where('admin_user_id', $adminUserId)->first();

        if (!$prefs) {
            // Criar preferências padrão
            $data = [
                'admin_user_id' => $adminUserId,
                'notify_new_campaign_email' => 1,
                'notify_new_campaign_push' => 1,
                'notify_weekly_donations_report' => 1,
                'notify_campaign_milestones' => 1,
                'enable_realtime_dashboard' => 1,
            ];

            $this->insert($data);
            $prefs = $this->find($this->getInsertID());
        }

        return $prefs;
    }

    /**
     * Buscar todos os admins que devem ser notificados sobre novas campanhas (email)
     */
    public function getAdminsForNewCampaignEmail(): array
    {
        return $this->where('notify_new_campaign_email', 1)->findAll();
    }

    /**
     * Buscar todos os admins que devem receber push sobre novas campanhas
     */
    public function getAdminsForNewCampaignPush(): array
    {
        return $this->where('notify_new_campaign_push', 1)
                    ->where('push_token IS NOT NULL')
                    ->findAll();
    }

    /**
     * Buscar admins que querem relatório semanal
     */
    public function getAdminsForWeeklyReport(): array
    {
        return $this->where('notify_weekly_donations_report', 1)->findAll();
    }

    /**
     * Buscar admins que querem notificações de marcos (10%, 20%, etc)
     */
    public function getAdminsForMilestones(): array
    {
        return $this->where('notify_campaign_milestones', 1)->findAll();
    }

    /**
     * Buscar admins com dashboard em tempo real habilitado
     */
    public function getAdminsWithRealtimeDashboard(): array
    {
        return $this->where('enable_realtime_dashboard', 1)
                    ->where('push_token IS NOT NULL')
                    ->findAll();
    }

    /**
     * Atualizar token FCM do admin
     */
    public function updatePushToken(int $adminUserId, string $token): bool
    {
        return $this->where('admin_user_id', $adminUserId)
                    ->set(['push_token' => $token])
                    ->update();
    }

    /**
     * Verificar se é admin (simplificado - pode melhorar com campo role na users)
     */
    public function isAdmin(int $userId): bool
    {
        // Por enquanto, verifica se tem entrada na tabela admin_notification_preferences
        $prefs = $this->where('admin_user_id', $userId)->first();
        return !empty($prefs);
    }
}

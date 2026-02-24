<?php

namespace App\Services;

use App\Models\NotificationQueue;
use App\Models\CampaignCreatorPreferences;
use App\Models\AdminNotificationPreferences;
use App\Models\NotificationPreference;
use App\Models\CampaignMilestone;
use App\Models\CampaignModel;
use App\Models\Donation;
use App\Models\UserModel;

/**
 * Notification Service - Centralizado
 * Orquestra TODOS os tipos de notificação da plataforma
 */
class NotificationService
{
    protected $queueModel;
    protected $creatorPrefsModel;
    protected $adminPrefsModel;
    protected $donorPrefsModel;
    protected $milestoneModel;
    protected $campaignModel;
    protected $donationModel;
    protected $userModel;

    public function __construct()
    {
        $this->queueModel = new NotificationQueue();
        $this->creatorPrefsModel = new CampaignCreatorPreferences();
        $this->adminPrefsModel = new AdminNotificationPreferences();
        $this->donorPrefsModel = new NotificationPreference();
        $this->milestoneModel = new CampaignMilestone();
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new Donation();
        $this->userModel = new UserModel();
    }

    // ========================================================================
    // NOTIFICAÇÕES DE DOAÇÃO (para criador)
    // ========================================================================

    /**
     * Notificar criador ao receber doação
     */
    public function notifyCreatorNewDonation(int $donationId): bool
    {
        try {
            $donation = $this->donationModel->find($donationId);
            if (!$donation || $donation['payment_status'] !== 'confirmed') {
                return false;
            }

            $campaign = $this->campaignModel->find($donation['campaign_id']);
            if (!$campaign) {
                return false;
            }

            $creatorId = $campaign['user_id'];

            // Verificar preferências
            $shouldEmail = $this->creatorPrefsModel->shouldNotifyDonationEmail($creatorId, $campaign['id']);
            $shouldPush = $this->creatorPrefsModel->shouldNotifyDonationPush($creatorId, $campaign['id']);

            if (!$shouldEmail && !$shouldPush) {
                return false; // Criador não quer ser notificado
            }

            // Buscar dados do criador
            $creator = $this->userModel->find($creatorId);

            // Enfileirar notificação de email
            if ($shouldEmail) {
                $this->queueModel->insert([
                    'type' => 'donation_received_email',
                    'recipient_email' => $creator['email'],
                    'recipient_name' => $creator['name'],
                    'data' => json_encode([
                        'donation_id' => $donationId,
                        'campaign_id' => $campaign['id'],
                        'campaign_title' => $campaign['title'],
                        'donor_name' => $donation['is_anonymous'] ? 'Anônimo' : $donation['donor_name'],
                        'amount' => $donation['amount'],
                        'message' => $donation['message'] ?? null,
                    ]),
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Enfileirar push
            if ($shouldPush) {
                $prefs = $this->creatorPrefsModel->getCampaignPreferences($creatorId, $campaign['id']);
                if ($prefs && !empty($prefs['push_token'])) {
                    $this->queueModel->insert([
                        'type' => 'donation_received_push',
                        'recipient_email' => $creator['email'],
                        'data' => json_encode([
                            'push_token' => $prefs['push_token'],
                            'title' => 'Nova Doação Recebida!',
                            'body' => "Você recebeu R$ {$donation['amount']} em {$campaign['title']}",
                            'campaign_id' => $campaign['id'],
                        ]),
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Verificar marcos atingidos
            $this->checkAndNotifyMilestones($campaign['id']);

            return true;

        } catch (\Exception $e) {
            log_message('error', 'NotificationService::notifyCreatorNewDonation - ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // NOTIFICAÇÕES DE MARCOS (10%, 20%, etc)
    // ========================================================================

    public function checkAndNotifyMilestones(int $campaignId): bool
    {
        try {
            $campaign = $this->campaignModel->find($campaignId);
            if (!$campaign || $campaign['goal'] == 0) {
                return false;
            }

            $percentage = ($campaign['raised'] / $campaign['goal']) * 100;
            $nextMilestone = $this->milestoneModel->getNextMilestone($campaignId, $percentage);

            if ($nextMilestone) {
                // Notificar ADMIN
                $this->notifyAdminCampaignMilestone($campaignId, $nextMilestone);

                // Marcar como notificado
                $this->milestoneModel->markAsNotified($campaignId, $nextMilestone);

                // Se atingiu 100%, notificar doadores
                if ($nextMilestone >= 100) {
                    $this->notifyDonorsGoalReached($campaignId);
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'NotificationService::checkAndNotifyMilestones - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyAdminCampaignMilestone(int $campaignId, int $percentage): void
    {
        $admins = $this->adminPrefsModel->getAdminsForMilestones();
        $campaign = $this->campaignModel->find($campaignId);

        foreach ($admins as $admin) {
            $adminUser = $this->userModel->find($admin['admin_user_id']);

            $this->queueModel->insert([
                'type' => 'campaign_milestone_email',
                'recipient_email' => $adminUser['email'],
                'recipient_name' => $adminUser['name'],
                'data' => json_encode([
                    'campaign_id' => $campaignId,
                    'campaign_title' => $campaign['title'],
                    'percentage' => $percentage,
                    'raised' => $campaign['raised'],
                    'goal' => $campaign['goal'],
                ]),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ========================================================================
    // NOTIFICAÇÕES PARA DOADORES
    // ========================================================================

    public function notifyDonorsGoalReached(int $campaignId): bool
    {
        try {
            $preferences = $this->donorPrefsModel
                ->where('campaign_id', $campaignId)
                ->where('notify_campaign_goal_reached', 1)
                ->findAll();

            $campaign = $this->campaignModel->find($campaignId);

            foreach ($preferences as $pref) {
                if ($pref['notify_email']) {
                    $this->queueModel->insert([
                        'type' => 'campaign_goal_reached_email',
                        'recipient_email' => $pref['donor_email'],
                        'recipient_name' => $pref['donor_name'],
                        'data' => json_encode([
                            'campaign_title' => $campaign['title'],
                            'campaign_slug' => $campaign['slug'],
                            'unsubscribe_token' => $pref['unsubscribe_token'],
                        ]),
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'NotificationService::notifyDonorsGoalReached - ' . $e->getMessage());
            return false;
        }
    }

    public function notifyDonorsCampaignEndingSoon(int $campaignId): bool
    {
        try {
            $campaign = $this->campaignModel->find($campaignId);
            if (!$campaign || empty($campaign['end_date'])) {
                return false;
            }

            $daysRemaining = (strtotime($campaign['end_date']) - time()) / 86400;
            if ($daysRemaining > 7 || $daysRemaining < 0) {
                return false; // Só notificar 7 dias antes
            }

            $preferences = $this->donorPrefsModel
                ->where('campaign_id', $campaignId)
                ->where('notify_campaign_ending_soon', 1)
                ->findAll();

            foreach ($preferences as $pref) {
                if ($pref['notify_email']) {
                    $this->queueModel->insert([
                        'type' => 'campaign_ending_soon_email',
                        'recipient_email' => $pref['donor_email'],
                        'recipient_name' => $pref['donor_name'],
                        'data' => json_encode([
                            'campaign_title' => $campaign['title'],
                            'campaign_slug' => $campaign['slug'],
                            'days_remaining' => round($daysRemaining),
                            'unsubscribe_token' => $pref['unsubscribe_token'],
                        ]),
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'NotificationService::notifyDonorsCampaignEndingSoon - ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // NOTIFICAÇÕES PARA ADMIN
    // ========================================================================

    public function notifyAdminNewCampaign(int $campaignId): bool
    {
        try {
            $admins = $this->adminPrefsModel->getAdminsForNewCampaignEmail();
            $campaign = $this->campaignModel->find($campaignId);
            $creator = $this->userModel->find($campaign['user_id']);

            foreach ($admins as $admin) {
                $adminUser = $this->userModel->find($admin['admin_user_id']);

                $this->queueModel->insert([
                    'type' => 'new_campaign_admin_email',
                    'recipient_email' => $adminUser['email'],
                    'recipient_name' => $adminUser['name'],
                    'data' => json_encode([
                        'campaign_id' => $campaignId,
                        'campaign_title' => $campaign['title'],
                        'creator_name' => $creator['name'],
                        'goal' => $campaign['goal'],
                    ]),
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'NotificationService::notifyAdminNewCampaign - ' . $e->getMessage());
            return false;
        }
    }
}

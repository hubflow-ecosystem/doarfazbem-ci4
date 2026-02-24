<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationPreference;

/**
 * Notification Controller
 * Gerencia preferências e unsubscribe de notificações
 */
class NotificationController extends BaseController
{
    protected $preferenceModel;
    protected $session;

    public function __construct()
    {
        $this->preferenceModel = new NotificationPreference();
        $this->session = \Config\Services::session();
    }

    /**
     * Página de gerenciamento de preferências (dashboard)
     * GET /dashboard/notifications
     */
    public function preferences()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Faça login para acessar suas preferências');
        }

        $userId = $this->session->get('id');
        $userEmail = $this->session->get('email');

        // Buscar todas as preferências do usuário
        $preferences = $this->preferenceModel
            ->join('campaigns', 'campaigns.id = notification_preferences.campaign_id')
            ->join('users', 'users.id = campaigns.user_id')
            ->where('notification_preferences.donor_email', $userEmail)
            ->select('notification_preferences.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug, users.name as campaign_creator')
            ->findAll();

        // Verificar se push está habilitado (se tem token salvo)
        $pushEnabled = false;
        foreach ($preferences as $pref) {
            if (!empty($pref['push_token'])) {
                $pushEnabled = true;
                break;
            }
        }

        $data = [
            'title' => 'Preferências de Notificações | DoarFazBem',
            'preferences' => $preferences,
            'push_enabled' => $pushEnabled,
        ];

        return view('dashboard/notifications', $data);
    }

    /**
     * Atualizar preferências (AJAX)
     * POST /dashboard/notifications/update
     */
    public function updatePreferences()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ]);
        }

        $json = $this->request->getJSON();
        $preferenceId = $json->preference_id ?? null;
        $type = $json->type ?? null; // 'email' ou 'push'
        $enabled = $json->enabled ?? false;

        if (!$preferenceId || !$type) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Parâmetros inválidos'
            ]);
        }

        try {
            $field = $type === 'email' ? 'notify_email' : 'notify_push';

            $this->preferenceModel->update($preferenceId, [
                $field => $enabled ? 1 : 0,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Preferência atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Erro ao atualizar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancelar assinatura de uma campanha (AJAX)
     * POST /dashboard/notifications/unsubscribe
     */
    public function unsubscribeCampaign()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ]);
        }

        $json = $this->request->getJSON();
        $preferenceId = $json->preference_id ?? null;

        if (!$preferenceId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'ID inválido'
            ]);
        }

        try {
            $this->preferenceModel->update($preferenceId, [
                'notify_email' => 0,
                'notify_push' => 0,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Assinatura cancelada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Erro ao cancelar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancelar todas as assinaturas (AJAX)
     * POST /dashboard/notifications/unsubscribe-all
     */
    public function unsubscribeAll()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ]);
        }

        $userEmail = $this->session->get('email');

        try {
            $this->preferenceModel
                ->where('donor_email', $userEmail)
                ->set(['notify_email' => 0, 'notify_push' => 0])
                ->update();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Todas as assinaturas foram canceladas!'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Erro ao cancelar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Unsubscribe via link no email
     * GET /notifications/unsubscribe/{token}
     */
    public function unsubscribe($token = null)
    {
        if (!$token) {
            return view('notifications/unsubscribe_error', [
                'title' => 'Link Inválido | DoarFazBem',
                'error_message' => 'Link de cancelamento inválido ou expirado.',
            ]);
        }

        $preference = $this->preferenceModel->getByUnsubscribeToken($token);

        if (!$preference) {
            return view('notifications/unsubscribe_error', [
                'title' => 'Link Inválido | DoarFazBem',
                'error_message' => 'Link de cancelamento inválido ou expirado.',
            ]);
        }

        // Buscar informações da campanha
        $campaignModel = new \App\Models\CampaignModel();
        $campaign = $campaignModel->find($preference['campaign_id']);

        $data = [
            'title' => 'Cancelar Inscrição | DoarFazBem',
            'preference' => $preference,
            'token' => $token,
            'campaign_title' => $campaign['title'] ?? 'Campanha',
            'campaign_slug' => $campaign['slug'] ?? '',
        ];

        return view('notifications/unsubscribe', $data);
    }

    /**
     * Confirmar unsubscribe
     * POST /notifications/unsubscribe
     */
    public function confirmUnsubscribe()
    {
        $token = $this->request->getPost('token');
        $action = $this->request->getPost('action'); // 'all' ou 'email_only'
        $reason = $this->request->getPost('reason');
        $comments = $this->request->getPost('comments');

        if (!$token) {
            return view('notifications/unsubscribe_error', [
                'title' => 'Erro | DoarFazBem',
                'error_message' => 'Token não fornecido.',
            ]);
        }

        $preference = $this->preferenceModel->getByUnsubscribeToken($token);

        if (!$preference) {
            return view('notifications/unsubscribe_error', [
                'title' => 'Erro | DoarFazBem',
                'error_message' => 'Link de cancelamento inválido ou expirado.',
            ]);
        }

        // Buscar informações da campanha
        $campaignModel = new \App\Models\CampaignModel();
        $campaign = $campaignModel->find($preference['campaign_id']);

        try {
            if ($action === 'all') {
                $this->preferenceModel->unsubscribeAll($token);
            } else {
                $this->preferenceModel->unsubscribeEmail($token);
            }

            // TODO: Salvar feedback (reason e comments) em uma tabela de feedback se desejar

            return view('notifications/unsubscribe_success', [
                'title' => 'Cancelamento Confirmado | DoarFazBem',
                'action' => $action,
                'campaign_title' => $campaign['title'] ?? null,
                'campaign_slug' => $campaign['slug'] ?? null,
                'feedback_received' => !empty($reason) || !empty($comments),
            ]);

        } catch (\Exception $e) {
            return view('notifications/unsubscribe_error', [
                'title' => 'Erro | DoarFazBem',
                'error_message' => 'Erro ao processar cancelamento: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Salvar token Firebase (AJAX)
     * POST /api/fcm/save-token
     */
    public function savePushToken()
    {
        $json = $this->request->getJSON();
        $token = $json->token ?? null;
        $deviceType = $json->device_type ?? 'unknown';

        $email = $this->session->get('email');

        if (!$email || !$token) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Email ou token não fornecido',
            ]);
        }

        try {
            // Atualizar todos os preferences do usuário com o token
            $preferences = $this->preferenceModel->where('donor_email', $email)->findAll();

            if (empty($preferences)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Nenhuma preferência encontrada. Faça uma doação primeiro.',
                ]);
            }

            foreach ($preferences as $pref) {
                $this->preferenceModel->update($pref['id'], [
                    'push_token' => $token,
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Token salvo com sucesso',
                'count' => count($preferences),
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

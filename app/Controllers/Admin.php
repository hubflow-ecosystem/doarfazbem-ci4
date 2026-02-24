<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;
use App\Models\TransactionModel;

/**
 * Admin Controller
 *
 * Painel administrativo da plataforma
 */
class Admin extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $userModel;
    protected $transactionModel;
    protected $session;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->userModel = new UserModel();
        $this->transactionModel = new TransactionModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url', 'number']);
    }

    /**
     * Verificar se usuário é admin
     */
    private function checkAdmin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar se o usuário tem role de admin
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user || $user['role'] !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
        }

        return null;
    }

    /**
     * Dashboard administrativo
     * GET /admin
     */
    public function index()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Estatísticas gerais
        $totalUsers = $this->userModel->countAll();
        $totalCampaigns = $this->campaignModel->countAll();
        $activeCampaigns = $this->campaignModel->where('status', 'active')->countAllResults();
        $pendingCampaigns = $this->campaignModel->where('status', 'pending')->countAllResults();

        $donationStats = $this->donationModel->getGlobalStats();

        $data = [
            'title' => 'Painel Administrativo | DoarFazBem',
            'total_users' => $totalUsers,
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'pending_campaigns' => $pendingCampaigns,
            'donation_stats' => $donationStats
        ];

        return view('admin/index', $data);
    }

    /**
     * Gerenciar campanhas
     * GET /admin/campaigns
     */
    public function campaigns()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $status = $this->request->getGet('status') ?? 'pending';
        $campaigns = $this->campaignModel->where('status', $status)->findAll();

        $data = [
            'title' => 'Gerenciar Campanhas | Admin',
            'campaigns' => $campaigns,
            'current_status' => $status
        ];

        return view('admin/campaigns', $data);
    }

    /**
     * Aprovar campanha
     * POST /admin/campaigns/approve/{id}
     */
    public function approveCampaign($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $this->campaignModel->update($id, ['status' => 'active']);

        // Enviar notificação por email ao criador
        $creator = $this->userModel->find($campaign['user_id']);
        if ($creator) {
            $this->sendCampaignApprovedEmail($creator['email'], $creator['name'], $campaign['title'], $campaign['id']);
        }

        $this->session->setFlashdata('success', 'Campanha aprovada com sucesso!');
        return redirect()->to('/admin/campaigns');
    }

    /**
     * Rejeitar campanha
     * POST /admin/campaigns/reject/{id}
     */
    public function rejectCampaign($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $reason = $this->request->getPost('reason') ?? 'Motivo não especificado';

        $this->campaignModel->update($id, ['status' => 'rejected']);

        // Enviar notificação com motivo da rejeição
        $creator = $this->userModel->find($campaign['user_id']);
        if ($creator) {
            $this->sendCampaignRejectedEmail($creator['email'], $creator['name'], $campaign['title'], $reason);
        }

        $this->session->setFlashdata('success', 'Campanha rejeitada.');
        return redirect()->to('/admin/campaigns');
    }

    /**
     * Gerenciar usuários
     * GET /admin/users
     */
    public function users()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $users = $this->userModel->findAll();

        $data = [
            'title' => 'Gerenciar Usuários | Admin',
            'users' => $users
        ];

        return view('admin/users', $data);
    }

    /**
     * Enviar email de campanha aprovada
     */
    private function sendCampaignApprovedEmail(string $email, string $name, string $campaignTitle, int $campaignId): bool
    {
        $emailService = \Config\Services::email();

        $emailService->setFrom(getenv('email.fromEmail') ?: 'contato@doarfazbem.com.br', getenv('email.fromName') ?: 'DoarFazBem');
        $emailService->setTo($email);
        $emailService->setSubject('Sua Campanha foi Aprovada! - DoarFazBem');

        $campaignUrl = base_url("campaigns/{$campaignId}");
        $message = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">Campanha Aprovada!</h1>
    </div>

    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #4b5563;">Olá <strong>{$name}</strong>,</p>

            <p style="font-size: 16px; color: #4b5563;">
                Temos uma ótima notícia! Sua campanha <strong>"{$campaignTitle}"</strong> foi aprovada e já está disponível para receber doações!
            </p>

            <div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-size: 14px; color: #065f46;">
                    <strong>Dica:</strong> Compartilhe sua campanha nas redes sociais e grupos de WhatsApp para alcançar mais pessoas e aumentar suas chances de sucesso!
                </p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{$campaignUrl}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Ver Minha Campanha
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">
                Boa sorte com sua campanha! Estamos torcendo por você.
            </p>

        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">DoarFazBem - Transformando vidas através da solidariedade</p>
    </div>

</body>
</html>
HTML;

        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Email de campanha aprovada enviado para: ' . $email);
            return true;
        } else {
            log_message('error', 'Erro ao enviar email de campanha aprovada: ' . $emailService->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Enviar email de campanha rejeitada
     */
    private function sendCampaignRejectedEmail(string $email, string $name, string $campaignTitle, string $reason): bool
    {
        $emailService = \Config\Services::email();

        $emailService->setFrom(getenv('email.fromEmail') ?: 'contato@doarfazbem.com.br', getenv('email.fromName') ?: 'DoarFazBem');
        $emailService->setTo($email);
        $emailService->setSubject('Sobre sua Campanha - DoarFazBem');

        $message = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">Campanha Não Aprovada</h1>
    </div>

    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #4b5563;">Olá <strong>{$name}</strong>,</p>

            <p style="font-size: 16px; color: #4b5563;">
                Infelizmente, sua campanha <strong>"{$campaignTitle}"</strong> não foi aprovada neste momento.
            </p>

            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-size: 14px; color: #92400e;">
                    <strong>Motivo:</strong>
                </p>
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    {$reason}
                </p>
            </div>

            <p style="font-size: 16px; color: #4b5563;">
                Você pode criar uma nova campanha corrigindo os pontos mencionados acima. Nossa equipe está aqui para ajudar!
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{base_url('campaigns/create')}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Criar Nova Campanha
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">
                Se tiver dúvidas, entre em contato conosco pelo email <a href="mailto:contato@doarfazbem.com.br" style="color: #667eea;">contato@doarfazbem.com.br</a>.
            </p>

        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">DoarFazBem - Transformando vidas através da solidariedade</p>
    </div>

</body>
</html>
HTML;

        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Email de campanha rejeitada enviado para: ' . $email);
            return true;
        } else {
            log_message('error', 'Erro ao enviar email de campanha rejeitada: ' . $emailService->printDebugger(['headers']));
            return false;
        }
    }
}

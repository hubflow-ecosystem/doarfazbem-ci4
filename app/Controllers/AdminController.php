<?php

namespace App\Controllers;

use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;
use App\Models\RaffleModel;
use App\Models\RafflePackageModel;
use App\Models\RaffleNumberModel;
use App\Models\RafflePurchaseModel;
use App\Models\RaffleRankingModel;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use App\Services\SettingsService;

class AdminController extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $userModel;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->userModel = new UserModel();
    }

    /**
     * Verificar se usuário é admin
     */
    private function checkAdmin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar se o usuário tem role de admin ou superadmin
        // Primeiro tenta pegar da sessão (mais rápido)
        $role = session()->get('role');
        if (in_array($role, ['admin', 'superadmin'])) {
            return null;
        }

        // Se não tiver role na sessão, busca do banco
        $userId = session()->get('id');
        $user = $this->userModel->find($userId);

        if (!$user || !in_array($user['role'] ?? '', ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
        }

        return null;
    }

    /**
     * Super Admin Dashboard
     */
    public function dashboard()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Dados para KPI Cards
        $currentMonth = date('Y-m');
        $previousMonth = date('Y-m', strtotime('-1 month'));

        $platformTotal = $this->getPlatformTotal();
        $prevPlatformTotal = $this->getPlatformTotal($previousMonth);

        $activeUsers = $this->userModel->countAll();
        $prevActiveUsers = $this->userModel
            ->where('DATE_FORMAT(created_at, "%Y-%m") <=', $previousMonth)
            ->countAllResults();

        $totalCampaigns = $this->campaignModel->countAll();
        $prevTotalCampaigns = $this->campaignModel
            ->where('DATE_FORMAT(created_at, "%Y-%m") <=', $previousMonth)
            ->countAllResults();

        // Taxa de sucesso (campanhas que atingiram meta)
        $successRate = $this->getSuccessRate();
        $prevSuccessRate = $this->getSuccessRate($previousMonth);

        // Dados para Gráfico de Crescimento (10 meses)
        $growthLabels = [];
        $growthData = [];

        for ($i = 9; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthLabel = $this->getMonthName(date('m', strtotime("-$i months")));

            $growthLabels[] = $monthLabel;
            $growthData[] = $this->getPlatformTotal($month);
        }

        // Campanhas Recentes para Tabela
        $recentCampaigns = $this->getRecentCampaignsForAdmin(20);

        $data = [
            'title' => 'Super Admin Dashboard',

            // KPI Cards
            'platform_total' => $platformTotal,
            'prev_platform_total' => $prevPlatformTotal,
            'active_users' => $activeUsers,
            'prev_active_users' => $prevActiveUsers,
            'total_campaigns' => $totalCampaigns,
            'prev_total_campaigns' => $prevTotalCampaigns,
            'success_rate' => $successRate,
            'prev_success_rate' => $prevSuccessRate,

            // Growth Chart
            'growth_labels' => $growthLabels,
            'growth_data' => $growthData,

            // Recent Campaigns Table
            'recent_campaigns' => $recentCampaigns
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Gerenciar Campanhas (com filtros avançados)
     */
    public function campaigns()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Obter filtros da URL
        $status = $this->request->getGet('status') ?? 'pending';
        $search = $this->request->getGet('search');
        $category = $this->request->getGet('category');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $sortBy = $this->request->getGet('sort_by') ?? 'created_at';
        $sortOrder = $this->request->getGet('sort_order') ?? 'DESC';
        // Validar sortOrder para prevenir SQL injection
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'DESC';

        $query = $this->campaignModel
            ->select('campaigns.*, users.name as creator_name, users.email as creator_email')
            ->join('users', 'users.id = campaigns.user_id');

        // Filtrar por status
        if ($status !== 'all') {
            $query->where('campaigns.status', $status);
        }

        // Busca por título ou nome do criador
        if ($search) {
            $query->groupStart()
                ->like('campaigns.title', $search)
                ->orLike('campaigns.description', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        // Filtrar por categoria
        if ($category) {
            $query->where('campaigns.category', $category);
        }

        // Filtrar por data de criação
        if ($dateFrom) {
            $query->where('campaigns.created_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('campaigns.created_at <=', $dateTo . ' 23:59:59');
        }

        // Ordenação
        $allowedSortFields = ['created_at', 'title', 'goal_amount', 'category'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy('campaigns.' . $sortBy, $sortOrder);
        } else {
            $query->orderBy('campaigns.created_at', 'DESC');
        }

        $campaigns = $query->findAll();

        // Adicionar estatísticas
        foreach ($campaigns as &$campaign) {
            $campaign['donors_count'] = $this->getDonorsCount($campaign['id']);
            $campaign['raised_amount'] = $this->getCampaignRaised($campaign['id']);
            $campaign['percentage'] = ($campaign['raised_amount'] / max(1, $campaign['goal_amount'])) * 100;
        }

        // Estatísticas por status para os badges
        $statusCounts = [
            'all' => $this->campaignModel->countAll(),
            'pending' => $this->campaignModel->where('status', 'pending')->countAllResults(),
            'active' => $this->campaignModel->where('status', 'active')->countAllResults(),
            'paused' => $this->campaignModel->where('status', 'paused')->countAllResults(),
            'completed' => $this->campaignModel->where('status', 'completed')->countAllResults(),
            'rejected' => $this->campaignModel->where('status', 'rejected')->countAllResults(),
        ];

        // Lista de categorias únicas
        $categories = $this->campaignModel->select('category')->distinct()->findAll();
        $categoryList = array_column($categories, 'category');

        $data = [
            'title' => 'Gerenciar Campanhas',
            'campaigns' => $campaigns,
            'current_status' => $status,
            'status_counts' => $statusCounts,
            'category_list' => $categoryList,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ];

        return view('admin/campaigns', $data);
    }

    /**
     * Aprovar campanha
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

        // Enviar email de aprovação para o criador
        $creator = $this->userModel->find($campaign['user_id']);
        if ($creator) {
            $this->sendApprovalEmail($creator, $campaign);
        }

        // Log de auditoria
        $this->logAudit('campaign_approved', 'campaigns', $id, [
            'campaign_title' => $campaign['title'],
            'creator_id' => $campaign['user_id']
        ]);

        return redirect()->to('/admin/campaigns?status=pending')
            ->with('success', 'Campanha "' . $campaign['title'] . '" aprovada com sucesso! O criador foi notificado por email.');
    }

    /**
     * Rejeitar campanha
     */
    public function rejectCampaign($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $reason = $this->request->getPost('reason');
        if (empty($reason)) {
            return redirect()->back()->with('error', 'Informe o motivo da rejeição.');
        }

        $this->campaignModel->update($id, [
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);

        // Enviar email para o criador com o motivo
        $creator = $this->userModel->find($campaign['user_id']);
        if ($creator) {
            $this->sendRejectionEmail($creator, $campaign, $reason);
        }

        // Log de auditoria
        $auditModel = new \App\Models\AuditLogModel();
        $auditModel->log(
            'campaign_rejected',
            'campaigns',
            $id,
            null,
            ['status' => 'rejected', 'reason' => $reason],
            session()->get('id')
        );

        return redirect()->to('/admin/campaigns?status=pending')
            ->with('success', 'Campanha "' . $campaign['title'] . '" foi rejeitada. O criador foi notificado por email.');
    }

    /**
     * Envia email de aprovação para o criador
     */
    private function sendApprovalEmail($creator, $campaign)
    {
        try {
            $email = \Config\Services::email();
            $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
            $email->setTo($creator['email']);
            $email->setSubject('Sua campanha foi aprovada! - DoarFazBem');

            $message = view('emails/campaign_approved', [
                'creator_name' => $creator['name'],
                'campaign_title' => $campaign['title'],
                'campaign_slug' => $campaign['slug'],
                'campaign_id' => $campaign['id']
            ]);

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de aprovação: ' . $e->getMessage());
        }
    }

    /**
     * Envia email de rejeição para o criador
     */
    private function sendRejectionEmail($creator, $campaign, $reason)
    {
        try {
            $email = \Config\Services::email();
            $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
            $email->setTo($creator['email']);
            $email->setSubject('Sua campanha foi rejeitada - DoarFazBem');

            $message = view('emails/campaign_rejected', [
                'creator_name' => $creator['name'],
                'campaign_title' => $campaign['title'],
                'rejection_reason' => $reason,
            ]);

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de rejeição: ' . $e->getMessage());
        }
    }

    /**
     * Gerenciar Usuários
     */
    public function users()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $status = $this->request->getGet('status') ?? 'all';

        $query = $this->userModel;

        if ($status !== 'all') {
            $query = $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'DESC')->findAll();

        // Adicionar estatísticas de cada usuário
        foreach ($users as &$user) {
            $user['campaigns_count'] = $this->campaignModel->where('user_id', $user['id'])->countAllResults();
            $user['total_raised'] = $this->getUserTotalRaised($user['id']);
        }

        // Estatísticas
        $stats = [
            'total' => $this->userModel->countAll(),
            'active' => $this->userModel->where('status', 'active')->orWhere('status IS NULL')->countAllResults(),
            'suspended' => $this->userModel->where('status', 'suspended')->countAllResults(),
            'banned' => $this->userModel->where('status', 'banned')->countAllResults(),
        ];

        $data = [
            'title' => 'Gerenciar Usuários',
            'users' => $users,
            'current_status' => $status,
            'stats' => $stats
        ];

        return view('admin/users', $data);
    }

    /**
     * Suspender usuário
     */
    public function suspendUser($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        // Não pode suspender admin/superadmin
        if (in_array($user['role'] ?? '', ['admin', 'superadmin'])) {
            return redirect()->back()->with('error', 'Não é possível suspender um administrador.');
        }

        $reason = $this->request->getPost('reason') ?? 'Não informado';

        if ($this->userModel->suspendUser($id, $reason, session()->get('id'))) {
            $this->logAudit('user_suspended', 'users', $id, [
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'reason' => $reason
            ]);

            // Enviar email de notificação
            $this->sendSuspensionEmail($user, 'suspended', $reason);

            return redirect()->back()->with('success', 'Usuário suspenso com sucesso.');
        }

        return redirect()->back()->with('error', 'Erro ao suspender usuário.');
    }

    /**
     * Banir usuário permanentemente
     */
    public function banUser($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        // Não pode banir admin/superadmin
        if (in_array($user['role'] ?? '', ['admin', 'superadmin'])) {
            return redirect()->back()->with('error', 'Não é possível banir um administrador.');
        }

        $reason = $this->request->getPost('reason') ?? 'Não informado';

        if ($this->userModel->banUser($id, $reason, session()->get('id'))) {
            $this->logAudit('user_banned', 'users', $id, [
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'reason' => $reason
            ]);

            // Enviar email de notificação
            $this->sendSuspensionEmail($user, 'banned', $reason);

            return redirect()->back()->with('success', 'Usuário banido permanentemente.');
        }

        return redirect()->back()->with('error', 'Erro ao banir usuário.');
    }

    /**
     * Reativar usuário
     */
    public function reactivateUser($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        if ($this->userModel->reactivateUser($id)) {
            $this->logAudit('user_reactivated', 'users', $id, [
                'user_name' => $user['name'],
                'user_email' => $user['email']
            ]);

            // Enviar email de notificação
            $this->sendReactivationEmail($user);

            return redirect()->back()->with('success', 'Usuário reativado com sucesso.');
        }

        return redirect()->back()->with('error', 'Erro ao reativar usuário.');
    }

    /**
     * Enviar email de suspensão/ban
     */
    private function sendSuspensionEmail($user, $type, $reason)
    {
        try {
            $email = \Config\Services::email();
            $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
            $email->setTo($user['email']);

            if ($type === 'suspended') {
                $email->setSubject('Sua conta foi suspensa - DoarFazBem');
                $message = view('emails/account_suspended', [
                    'user' => $user,
                    'reason' => $reason
                ]);
            } else {
                $email->setSubject('Sua conta foi banida - DoarFazBem');
                $message = view('emails/account_banned', [
                    'user' => $user,
                    'reason' => $reason
                ]);
            }

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de suspensão: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de reativação
     */
    private function sendReactivationEmail($user)
    {
        try {
            $email = \Config\Services::email();
            $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
            $email->setTo($user['email']);
            $email->setSubject('Sua conta foi reativada - DoarFazBem');

            $message = view('emails/account_reactivated', [
                'user' => $user
            ]);

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de reativação: ' . $e->getMessage());
        }
    }

    /**
     * Gerenciar Doações (com filtros avançados)
     */
    public function donations()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Obter filtros da URL
        $status = $this->request->getGet('status');
        $paymentMethod = $this->request->getGet('payment_method');
        $search = $this->request->getGet('search');
        $campaignId = $this->request->getGet('campaign_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $amountFrom = $this->request->getGet('amount_from');
        $amountTo = $this->request->getGet('amount_to');
        $sortBy = $this->request->getGet('sort_by') ?? 'created_at';
        $sortOrder = $this->request->getGet('sort_order') ?? 'DESC';
        // Validar sortOrder para prevenir SQL injection
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'DESC';

        $query = $this->donationModel
            ->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug, users.name as creator_name')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->join('users', 'users.id = campaigns.user_id');

        // Filtrar por status
        if ($status) {
            $query->where('donations.status', $status);
        }

        // Filtrar por método de pagamento
        if ($paymentMethod) {
            $query->where('donations.payment_method', $paymentMethod);
        }

        // Busca por nome do doador, email, ou campanha
        if ($search) {
            $query->groupStart()
                ->like('donations.donor_name', $search)
                ->orLike('donations.donor_email', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('users.name', $search)
                ->groupEnd();
        }

        // Filtrar por campanha específica
        if ($campaignId) {
            $query->where('donations.campaign_id', $campaignId);
        }

        // Filtrar por data
        if ($dateFrom) {
            $query->where('donations.created_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('donations.created_at <=', $dateTo . ' 23:59:59');
        }

        // Filtrar por valor
        if ($amountFrom) {
            $query->where('donations.amount >=', $amountFrom);
        }

        if ($amountTo) {
            $query->where('donations.amount <=', $amountTo);
        }

        // Ordenação
        $allowedSortFields = ['created_at', 'amount', 'status', 'payment_method'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy('donations.' . $sortBy, $sortOrder);
        } else {
            $query->orderBy('donations.created_at', 'DESC');
        }

        $donations = $query->findAll();

        // Estatísticas por status
        $statusCounts = [
            'all' => $this->donationModel->countAll(),
            'pending' => $this->donationModel->where('status', 'pending')->countAllResults(),
            'confirmed' => $this->donationModel->where('status', 'confirmed')->countAllResults(),
            'received' => $this->donationModel->where('status', 'received')->countAllResults(),
            'failed' => $this->donationModel->where('status', 'failed')->countAllResults(),
        ];

        // Campanhas para o filtro
        $campaignsList = $this->campaignModel
            ->select('id, title')
            ->where('status !=', 'draft')
            ->orderBy('title', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Gerenciar Doações',
            'donations' => $donations,
            'total_donated' => array_sum(array_column($donations, 'amount')),
            'status_counts' => $statusCounts,
            'campaigns_list' => $campaignsList,
            'filters' => [
                'status' => $status,
                'payment_method' => $paymentMethod,
                'search' => $search,
                'campaign_id' => $campaignId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'amount_from' => $amountFrom,
                'amount_to' => $amountTo,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ];

        return view('admin/donations', $data);
    }

    /**
     * Relatórios
     */
    public function reports()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Estatísticas Gerais
        $stats = [
            'total_users' => $this->userModel->countAll(),
            'total_campaigns' => $this->campaignModel->countAll(),
            'active_campaigns' => $this->campaignModel->where('status', 'active')->countAllResults(),
            'completed_campaigns' => $this->campaignModel->where('status', 'completed')->countAllResults(),
            'total_donations' => $this->donationModel->where('status', 'received')->countAllResults(),
            'total_raised' => $this->getPlatformTotal(),
            'total_amount' => $this->getPlatformTotal(),
            'total_withdrawals' => \Config\Database::connect()->table('withdrawals')->countAll(),
            'average_donation' => $this->getAverageDonation(),

            // Por Categoria
            'medica_total' => $this->getTotalByCategory('medica'),
            'social_total' => $this->getTotalByCategory('social'),
            'educacao_total' => $this->getTotalByCategory('educacao'),
            'negocio_total' => $this->getTotalByCategory('negocio'),
            'criativa_total' => $this->getTotalByCategory('criativa'),

            // Por Método de Pagamento
            'pix_total' => $this->getTotalByPaymentMethod('pix'),
            'credit_card_total' => $this->getTotalByPaymentMethod('credit_card'),
            'boleto_total' => $this->getTotalByPaymentMethod('boleto'),

            // Crescimento
            'new_users_month' => $this->getNewUsersThisMonth(),
            'new_campaigns_month' => $this->getNewCampaignsThisMonth(),
            'donations_month' => $this->getDonationsThisMonth()
        ];

        $data = [
            'title' => 'Relatórios',
            'stats' => $stats
        ];

        return view('admin/reports', $data);
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    private function getPlatformTotal($month = null)
    {
        $builder = $this->donationModel
            ->select('SUM(amount) as total')
            ->where('status', 'received');

        if ($month) {
            $builder->where('DATE_FORMAT(created_at, "%Y-%m")', $month);
        }

        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }

    private function getSuccessRate($month = null)
    {
        $builder = $this->campaignModel->select('
            COUNT(*) as total,
            SUM(CASE WHEN (
                SELECT SUM(amount)
                FROM donations
                WHERE donations.campaign_id = campaigns.id
                AND donations.status = "received"
            ) >= campaigns.goal_amount THEN 1 ELSE 0 END) as successful
        ');

        if ($month) {
            $builder->where('DATE_FORMAT(created_at, "%Y-%m") <=', $month);
        }

        $result = $builder->get()->getRowArray();

        if (!$result || $result['total'] == 0) {
            return 0;
        }

        return ($result['successful'] / $result['total']) * 100;
    }

    private function getRecentCampaignsForAdmin($limit = 20)
    {
        $campaigns = $this->campaignModel
            ->select('campaigns.*, users.name as creator')
            ->join('users', 'users.id = campaigns.user_id')
            ->orderBy('campaigns.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        // Formatar para JSON
        $formatted = [];
        foreach ($campaigns as $campaign) {
            $raised = $this->getCampaignRaised($campaign['id']);

            $formatted[] = [
                'id' => $campaign['id'],
                'title' => $campaign['title'],
                'creator' => $campaign['creator'],
                'category' => ucfirst($campaign['category']),
                'raised' => (float) $raised,
                'goal' => (float) $campaign['goal_amount'],
                'status' => $this->translateStatus($campaign['status'])
            ];
        }

        return $formatted;
    }

    private function getDonorsCount($campaignId)
    {
        return $this->donationModel
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->countAllResults();
    }

    private function getCampaignRaised($campaignId)
    {
        $result = $this->donationModel
            ->select('SUM(amount) as total')
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getUserTotalRaised($userId)
    {
        $result = $this->donationModel
            ->select('SUM(donations.amount) as total')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('donations.status', 'received')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getTotalByCategory($category)
    {
        $result = $this->donationModel
            ->select('SUM(donations.amount) as total')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.category', $category)
            ->where('donations.status', 'received')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getTotalByPaymentMethod($method)
    {
        $result = $this->donationModel
            ->select('SUM(amount) as total')
            ->where('payment_method', $method)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getAverageDonation()
    {
        $result = $this->donationModel
            ->select('AVG(amount) as average')
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return $result['average'] ?? 0;
    }

    private function getNewUsersThisMonth()
    {
        $currentMonth = date('Y-m');
        return $this->userModel
            ->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)
            ->countAllResults();
    }

    private function getNewCampaignsThisMonth()
    {
        $currentMonth = date('Y-m');
        return $this->campaignModel
            ->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)
            ->countAllResults();
    }

    private function getDonationsThisMonth()
    {
        $currentMonth = date('Y-m');
        $result = $this->donationModel
            ->select('SUM(amount) as total')
            ->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function translateStatus($status)
    {
        $map = [
            'active' => 'Ativa',
            'completed' => 'Concluída',
            'paused' => 'Pausada',
            'cancelled' => 'Cancelada'
        ];

        return $map[$status] ?? ucfirst($status);
    }

    private function getMonthName($month)
    {
        $months = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
            '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
        ];

        return $months[$month] ?? '';
    }

    // ========================================
    // GERENCIAMENTO DE RIFAS
    // ========================================

    /**
     * Lista de rifas
     */
    public function raffles()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffles = $raffleModel->orderBy('created_at', 'DESC')->findAll();

        // Adicionar estatísticas
        foreach ($raffles as &$raffle) {
            $stats = $raffleModel->getStats($raffle['id']);
            $raffle['stats'] = $stats;
        }

        return view('admin/raffles/index', [
            'title' => 'Gerenciar Rifas',
            'raffles' => $raffles
        ]);
    }

    /**
     * Criar nova rifa
     */
    public function createRaffle()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        if ($this->request->getMethod() === 'POST') {
            $raffleModel = new RaffleModel();
            $numberModel = new RaffleNumberModel();

            $data = [
                'title' => $this->request->getPost('title'),
                'slug' => url_title($this->request->getPost('title'), '-', true),
                'description' => $this->request->getPost('description'),
                'total_numbers' => (int) $this->request->getPost('total_numbers'),
                'number_price' => (float) $this->request->getPost('number_price'),
                'main_prize_percentage' => (float) $this->request->getPost('main_prize_percentage'),
                'campaign_percentage' => (float) $this->request->getPost('campaign_percentage'),
                'platform_percentage' => (float) $this->request->getPost('platform_percentage'),
                'federal_lottery_date' => $this->request->getPost('federal_lottery_date'),
                'status' => 'draft',
            ];

            // Validar percentuais
            $total = $data['main_prize_percentage'] + $data['campaign_percentage'] + $data['platform_percentage'];
            if ($total != 100) {
                return redirect()->back()->withInput()->with('error', 'Os percentuais devem somar 100%.');
            }

            $raffleId = $raffleModel->insert($data);

            if ($raffleId) {
                // Gerar números
                $numberModel->generateNumbers($raffleId, $data['total_numbers']);

                // Criar pacotes padrão
                $this->createDefaultPackages($raffleId, $data['number_price']);

                return redirect()->to('/admin/raffles/edit/' . $raffleId)
                    ->with('success', 'Rifa criada com sucesso! Configure os prêmios especiais.');
            }

            return redirect()->back()->withInput()->with('error', 'Erro ao criar rifa.');
        }

        return view('admin/raffles/create', [
            'title' => 'Nova Rifa'
        ]);
    }

    /**
     * Editar rifa
     */
    public function editRaffle($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $packageModel = new RafflePackageModel();

        $raffle = $raffleModel->find($id);
        if (!$raffle) {
            return redirect()->to('/admin/raffles')->with('error', 'Rifa não encontrada.');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'number_price' => (float) $this->request->getPost('number_price'),
                'main_prize_percentage' => (float) $this->request->getPost('main_prize_percentage'),
                'campaign_percentage' => (float) $this->request->getPost('campaign_percentage'),
                'platform_percentage' => (float) $this->request->getPost('platform_percentage'),
                'federal_lottery_date' => $this->request->getPost('federal_lottery_date'),
            ];

            // Validar percentuais
            $total = $data['main_prize_percentage'] + $data['campaign_percentage'] + $data['platform_percentage'];
            if ($total != 100) {
                return redirect()->back()->withInput()->with('error', 'Os percentuais devem somar 100%.');
            }

            $raffleModel->update($id, $data);
            return redirect()->back()->with('success', 'Rifa atualizada com sucesso!');
        }

        $packages = $packageModel->where('raffle_id', $id)->orderBy('quantity', 'ASC')->findAll();
        $stats = $raffleModel->getStats($id);

        // Buscar prêmios especiais
        $db = \Config\Database::connect();
        $specialPrizes = $db->table('raffle_special_prizes')
            ->where('raffle_id', $id)
            ->orderBy('prize_amount', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/raffles/edit', [
            'title' => 'Editar Rifa',
            'raffle' => $raffle,
            'packages' => $packages,
            'stats' => $stats,
            'specialPrizes' => $specialPrizes
        ]);
    }

    /**
     * Ativar rifa
     */
    public function activateRaffle($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();

        // Desativar todas as outras rifas
        $raffleModel->where('status', 'active')->set(['status' => 'completed'])->update();

        // Ativar esta
        $raffleModel->update($id, ['status' => 'active']);

        return redirect()->back()->with('success', 'Rifa ativada com sucesso!');
    }

    /**
     * Pausar rifa
     */
    public function pauseRaffle($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffleModel->update($id, ['status' => 'paused']);

        return redirect()->back()->with('success', 'Rifa pausada.');
    }

    /**
     * Finalizar rifa e realizar sorteio
     */
    public function completeRaffle($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffle = $raffleModel->find($id);

        if (!$raffle) {
            return redirect()->back()->with('error', 'Rifa não encontrada.');
        }

        if ($raffle['status'] === 'completed') {
            return redirect()->back()->with('error', 'Esta rifa já foi finalizada.');
        }

        // Verificar se tem números vendidos
        if ($raffle['numbers_sold'] < 1) {
            return redirect()->back()->with('error', 'Não é possível sortear uma rifa sem números vendidos.');
        }

        // Verificar se o resultado da loteria foi informado (opcional)
        $federalResult = $this->request->getPost('federal_result');

        // Realizar o sorteio
        $drawResult = $this->performDraw($raffle, $federalResult);

        if (!$drawResult['success']) {
            return redirect()->back()->with('error', $drawResult['error']);
        }

        return redirect()->to('/admin/raffles')
            ->with('success', 'Sorteio realizado com sucesso! Ganhador: ' . $drawResult['winner_name'] . ' - Número: ' . $drawResult['winning_number']);
    }

    /**
     * Página para realizar o sorteio
     */
    public function drawRaffle($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffle = $raffleModel->find($id);

        if (!$raffle) {
            return redirect()->to('/admin/raffles')->with('error', 'Rifa não encontrada.');
        }

        $numberModel = new RaffleNumberModel();
        $rankingModel = new RaffleRankingModel();
        $winnerModel = new RaffleWinnerModel();

        // Estatísticas
        $paidNumbers = $numberModel->where('raffle_id', $id)->where('status', 'paid')->countAllResults();
        $rankings = $rankingModel->getRankingByRaffle($id, 10);

        // Se já foi sorteado, mostrar os ganhadores
        $winners = [];
        if ($raffle['status'] === 'completed') {
            $winners = $winnerModel->getWinnersByRaffle($id);
        }

        $data = [
            'title' => 'Realizar Sorteio - ' . $raffle['title'],
            'raffle' => $raffle,
            'paid_numbers_count' => $paidNumbers,
            'rankings' => $rankings,
            'winners' => $winners,
        ];

        return view('admin/raffle_draw', $data);
    }

    /**
     * Executa o sorteio
     */
    private function performDraw($raffle, $federalResult = null)
    {
        $raffleModel = new RaffleModel();
        $numberModel = new RaffleNumberModel();
        $purchaseModel = new RafflePurchaseModel();
        $rankingModel = new RaffleRankingModel();
        $winnerModel = new RaffleWinnerModel();

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Sortear número vencedor
            // Se tiver resultado da loteria federal, usar como base
            if ($federalResult) {
                // Usar os últimos dígitos do resultado da loteria
                $digits = strlen((string)($raffle['total_numbers'] - 1));
                $winningNumber = str_pad(substr($federalResult, -$digits), $digits, '0', STR_PAD_LEFT);

                // Verificar se esse número foi vendido
                $winner = $numberModel->where('raffle_id', $raffle['id'])
                    ->where('number', $winningNumber)
                    ->where('status', 'paid')
                    ->first();

                // Se não foi vendido, sortear aleatoriamente entre os vendidos
                if (!$winner) {
                    $winner = $numberModel->where('raffle_id', $raffle['id'])
                        ->where('status', 'paid')
                        ->orderBy('RAND()')
                        ->first();
                    $winningNumber = $winner ? $winner['number'] : null;
                }
            } else {
                // Sortear aleatoriamente
                $winner = $numberModel->where('raffle_id', $raffle['id'])
                    ->where('status', 'paid')
                    ->orderBy('RAND()')
                    ->first();
                $winningNumber = $winner ? $winner['number'] : null;
            }

            if (!$winningNumber || !$winner) {
                $db->transRollback();
                return ['success' => false, 'error' => 'Não há números pagos para sortear.'];
            }

            // 2. Buscar dados do comprador
            $purchase = $purchaseModel->find($winner['purchase_id']);
            if (!$purchase) {
                $db->transRollback();
                return ['success' => false, 'error' => 'Compra não encontrada para o número vencedor.'];
            }

            // 3. Calcular prêmio principal
            $mainPrize = $raffle['total_revenue'] * ($raffle['main_prize_percentage'] / 100);

            // 4. Registrar ganhador principal
            $buyerData = [
                'user_id' => $purchase['user_id'],
                'name' => $purchase['buyer_name'],
                'email' => $purchase['buyer_email'],
                'phone' => $purchase['buyer_phone'],
            ];

            $mainWinnerId = $winnerModel->registerMainWinner($raffle['id'], $buyerData, $winningNumber, $mainPrize);

            // 5. Marcar número como vencedor
            $numberModel->setWinner($raffle['id'], $winningNumber);

            // 6. Registrar ganhadores do ranking (top compradores)
            $rankings = $rankingModel->getRankingByRaffle($raffle['id'], 5);
            if (!empty($rankings)) {
                $winnerModel->registerRankingWinners($raffle['id'], $rankings);
            }

            // 7. Atualizar rifa
            $raffleModel->update($raffle['id'], [
                'status' => 'completed',
                'winning_number' => $winningNumber,
                'federal_lottery_result' => $federalResult,
            ]);

            // 8. Enviar notificação ao ganhador
            $this->sendWinnerNotification($purchase, $winningNumber, $mainPrize, $raffle);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['success' => false, 'error' => 'Erro ao processar o sorteio.'];
            }

            // Log de auditoria
            $auditModel = new \App\Models\AuditLogModel();
            $auditModel->log(
                'raffle_draw_completed',
                'raffles',
                $raffle['id'],
                null,
                [
                    'winning_number' => $winningNumber,
                    'winner_name' => $purchase['buyer_name'],
                    'main_prize' => $mainPrize,
                ],
                session()->get('id')
            );

            return [
                'success' => true,
                'winning_number' => $winningNumber,
                'winner_name' => $purchase['buyer_name'],
                'prize_amount' => $mainPrize,
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro no sorteio: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Envia notificação ao ganhador
     */
    private function sendWinnerNotification($purchase, $winningNumber, $prizeAmount, $raffle)
    {
        try {
            $email = \Config\Services::email();
            $email->setTo($purchase['buyer_email']);
            $email->setSubject('PARABENS! Voce ganhou o sorteio - DoarFazBem');

            $message = view('emails/raffle_winner', [
                'winner_name' => $purchase['buyer_name'],
                'winning_number' => $winningNumber,
                'prize_amount' => $prizeAmount,
                'raffle_title' => $raffle['title'],
            ]);

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar notificação ao ganhador: ' . $e->getMessage());
        }
    }

    /**
     * Gerenciar pacotes de uma rifa
     */
    public function rafflePackages($raffleId)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $packageModel = new RafflePackageModel();
        $raffleModel = new RaffleModel();

        $raffle = $raffleModel->find($raffleId);
        if (!$raffle) {
            return redirect()->to('/admin/raffles')->with('error', 'Rifa não encontrada.');
        }

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            if ($action === 'add') {
                $quantity = (int) $this->request->getPost('quantity');
                $discountPercentage = (float) $this->request->getPost('discount_percentage');
                $originalPrice = $quantity * $raffle['number_price'];
                $discountPrice = $originalPrice * (1 - $discountPercentage / 100);

                // Calcular próximo sort_order
                $maxOrder = $packageModel->where('raffle_id', $raffleId)
                    ->selectMax('sort_order')
                    ->first()['sort_order'] ?? 0;

                $packageModel->insert([
                    'raffle_id' => $raffleId,
                    'name' => $this->request->getPost('name'),
                    'quantity' => $quantity,
                    'original_price' => $originalPrice,
                    'discount_price' => $discountPrice,
                    'discount_percentage' => $discountPercentage,
                    'is_popular' => $this->request->getPost('is_popular') ? 1 : 0,
                    'sort_order' => $maxOrder + 1,
                ]);
                return redirect()->back()->with('success', 'Pacote adicionado!');
            }

            if ($action === 'delete') {
                $packageModel->delete($this->request->getPost('package_id'));
                return redirect()->back()->with('success', 'Pacote removido!');
            }

            if ($action === 'update') {
                $quantity = (int) $this->request->getPost('quantity');
                $discountPercentage = (float) $this->request->getPost('discount_percentage');
                $originalPrice = $quantity * $raffle['number_price'];
                $discountPrice = $originalPrice * (1 - $discountPercentage / 100);

                $packageModel->update($this->request->getPost('package_id'), [
                    'name' => $this->request->getPost('name'),
                    'quantity' => $quantity,
                    'original_price' => $originalPrice,
                    'discount_price' => $discountPrice,
                    'discount_percentage' => $discountPercentage,
                    'is_popular' => $this->request->getPost('is_popular') ? 1 : 0,
                ]);
                return redirect()->back()->with('success', 'Pacote atualizado!');
            }
        }

        $packages = $packageModel->where('raffle_id', $raffleId)->orderBy('quantity', 'ASC')->findAll();

        return view('admin/raffles/packages', [
            'title' => 'Pacotes - ' . $raffle['title'],
            'raffle' => $raffle,
            'packages' => $packages
        ]);
    }

    /**
     * Gerenciar prêmios especiais
     */
    public function raffleSpecialPrizes($raffleId)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffle = $raffleModel->find($raffleId);

        if (!$raffle) {
            return redirect()->to('/admin/raffles')->with('error', 'Rifa não encontrada.');
        }

        $db = \Config\Database::connect();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            if ($action === 'add') {
                $numbers = explode(',', $this->request->getPost('numbers'));
                $prizeValue = (float) $this->request->getPost('prize_amount');
                $prizeName = $this->request->getPost('prize_name');

                // Calcular total atual de premios
                $currentSpecialPrizes = $db->table('raffle_special_prizes')
                    ->where('raffle_id', $raffleId)
                    ->selectSum('prize_amount')
                    ->get()
                    ->getRow()->prize_amount ?? 0;

                $topBuyersPrizes = 1500 + 1000 + 500; // Fixo: 1º + 2º + 3º lugar
                $validNumbers = array_filter($numbers, fn($n) => is_numeric(trim($n)));
                $newPrizesTotal = count($validNumbers) * $prizeValue;
                $totalAfterAdd = $currentSpecialPrizes + $topBuyersPrizes + $newPrizesTotal;

                $maxExtraPrizes = $raffle['max_extra_prizes'] ?? 10000;

                // Validar limite
                if ($totalAfterAdd > $maxExtraPrizes) {
                    $available = $maxExtraPrizes - $currentSpecialPrizes - $topBuyersPrizes;
                    return redirect()->back()->with('error',
                        "Limite de prêmios excedido! Total após adicionar: R$ " . number_format($totalAfterAdd, 2, ',', '.') .
                        ". Limite máximo: R$ " . number_format($maxExtraPrizes, 2, ',', '.') .
                        ". Disponível para cotas premiadas: R$ " . number_format(max(0, $available), 2, ',', '.')
                    );
                }

                foreach ($numbers as $number) {
                    $number = trim($number);
                    if (is_numeric($number)) {
                        $db->table('raffle_special_prizes')->insert([
                            'raffle_id' => $raffleId,
                            'number_pattern' => str_pad($number, 6, '0', STR_PAD_LEFT),
                            'prize_name' => $prizeName,
                            'prize_amount' => $prizeValue,
                        ]);
                    }
                }
                return redirect()->back()->with('success', 'Prêmios especiais adicionados!');
            }

            if ($action === 'delete') {
                $db->table('raffle_special_prizes')
                    ->where('id', $this->request->getPost('prize_id'))
                    ->delete();
                return redirect()->back()->with('success', 'Prêmio removido!');
            }

            if ($action === 'update') {
                $prizeId = $this->request->getPost('prize_id');
                $number = trim($this->request->getPost('number'));
                $prizeName = $this->request->getPost('prize_name');
                $prizeAmount = (float) $this->request->getPost('prize_amount');

                if (!is_numeric($number)) {
                    return redirect()->back()->with('error', 'Número inválido!');
                }

                $db->table('raffle_special_prizes')
                    ->where('id', $prizeId)
                    ->update([
                        'number_pattern' => $number,
                        'prize_name' => $prizeName,
                        'prize_amount' => $prizeAmount,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                return redirect()->back()->with('success', 'Prêmio atualizado com sucesso!');
            }
        }

        $specialPrizes = $db->table('raffle_special_prizes')
            ->where('raffle_id', $raffleId)
            ->orderBy('prize_amount', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/raffles/special_prizes', [
            'title' => 'Prêmios Especiais - ' . $raffle['title'],
            'raffle' => $raffle,
            'specialPrizes' => $specialPrizes
        ]);
    }

    /**
     * Ver compras de uma rifa
     */
    public function rafflePurchases($raffleId)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $purchaseModel = new RafflePurchaseModel();

        $raffle = $raffleModel->find($raffleId);
        if (!$raffle) {
            return redirect()->to('/admin/raffles')->with('error', 'Rifa não encontrada.');
        }

        $purchases = $purchaseModel
            ->select('raffle_purchases.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = raffle_purchases.user_id', 'left')
            ->where('raffle_id', $raffleId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Estatísticas
        $stats = [
            'total_purchases' => count($purchases),
            'confirmed' => 0,
            'pending' => 0,
            'expired' => 0,
            'total_revenue' => 0,
        ];

        foreach ($purchases as $purchase) {
            $status = $purchase['payment_status'] ?? 'pending';
            if ($status === 'paid') {
                $stats['confirmed']++;
                $stats['total_revenue'] += $purchase['total_amount'] ?? 0;
            } elseif ($status === 'pending') {
                $stats['pending']++;
            } elseif ($status === 'expired') {
                $stats['expired']++;
            }
        }

        return view('admin/raffles/purchases', [
            'title' => 'Compras - ' . $raffle['title'],
            'raffle' => $raffle,
            'purchases' => $purchases,
            'stats' => $stats
        ]);
    }

    /**
     * Simular pagamento (modo desenvolvimento)
     */
    public function simulateRafflePayment($purchaseId)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $purchaseModel = new RafflePurchaseModel();
        $numberModel = new RaffleNumberModel();
        $raffleModel = new RaffleModel();
        $rankingModel = new RaffleRankingModel();

        log_message('debug', '=== SIMULATE PAYMENT START ===');
        log_message('debug', 'Purchase ID: ' . $purchaseId);

        $purchase = $purchaseModel->find($purchaseId);
        log_message('debug', 'Purchase Data: ' . json_encode($purchase));

        if (!$purchase) {
            log_message('error', 'Compra não encontrada: ' . $purchaseId);
            return redirect()->back()->with('error', 'Compra não encontrada.');
        }

        log_message('debug', 'Payment Status: ' . ($purchase['payment_status'] ?? 'NULL'));

        if ($purchase['payment_status'] !== 'pending') {
            log_message('warning', 'Compra já processada. Status: ' . ($purchase['payment_status'] ?? 'NULL'));
            return redirect()->back()->with('error', 'Esta compra já foi processada. Status atual: ' . ($purchase['payment_status'] ?? 'desconhecido'));
        }

        // Confirmar números (muda status de 'reserved' para 'paid')
        log_message('debug', 'Confirmando números...');
        $result = $numberModel->confirmPayment($purchaseId);
        log_message('debug', 'Números confirmados: ' . json_encode($result));

        // Atualizar status da compra para 'paid'
        log_message('debug', 'Atualizando payment_status para paid...');
        $purchaseModel->updatePaymentStatus($purchaseId, 'paid', $result['instant_prize']);

        // Atualizar estatísticas da rifa
        log_message('debug', 'Atualizando estatísticas da rifa ' . $purchase['raffle_id']);
        $raffleModel->updateStatsAfterPurchase(
            $purchase['raffle_id'],
            $purchase['quantity'],
            $purchase['total_amount']
        );

        // Atualizar ranking se usuário estiver logado
        if ($purchase['user_id']) {
            log_message('debug', 'Atualizando ranking do usuário ' . $purchase['user_id']);
            $rankingModel->updateRanking(
                $purchase['raffle_id'],
                $purchase['user_id'],
                $purchase['buyer_name'],
                $purchase['buyer_email'],
                $purchase['quantity'],
                $purchase['total_amount']
            );
        } else {
            log_message('debug', 'Compra sem user_id, pulando ranking');
        }

        // Verificar se a rifa esgotou
        log_message('debug', 'Verificando se rifa esgotou...');
        $raffleModel->checkSoldOut($purchase['raffle_id']);

        log_message('info', '=== SIMULATE PAYMENT SUCCESS === Purchase ID: ' . $purchaseId);
        return redirect()->back()->with('success', 'Pagamento confirmado! Números liberados e estatísticas atualizadas.');
    }

    /**
     * Criar pacotes padrão
     */
    private function createDefaultPackages($raffleId, $basePrice)
    {
        $packageModel = new RafflePackageModel();

        $packages = [
            ['name' => '1 Cota', 'quantity' => 1, 'discount' => 0, 'popular' => 0, 'order' => 1],
            ['name' => '5 Cotas', 'quantity' => 5, 'discount' => 3, 'popular' => 0, 'order' => 2],
            ['name' => '10 Cotas', 'quantity' => 10, 'discount' => 5, 'popular' => 0, 'order' => 3],
            ['name' => '25 Cotas', 'quantity' => 25, 'discount' => 10, 'popular' => 1, 'order' => 4],
            ['name' => '50 Cotas', 'quantity' => 50, 'discount' => 15, 'popular' => 0, 'order' => 5],
            ['name' => '100 Cotas', 'quantity' => 100, 'discount' => 20, 'popular' => 0, 'order' => 6],
            ['name' => '500 Cotas', 'quantity' => 500, 'discount' => 20, 'popular' => 0, 'order' => 7],
            ['name' => '1000 Cotas', 'quantity' => 1000, 'discount' => 20, 'popular' => 0, 'order' => 8],
        ];

        foreach ($packages as $pkg) {
            $originalPrice = $pkg['quantity'] * $basePrice;
            $discountPrice = $originalPrice * (1 - $pkg['discount'] / 100);

            $packageModel->insert([
                'raffle_id' => $raffleId,
                'name' => $pkg['name'],
                'quantity' => $pkg['quantity'],
                'original_price' => $originalPrice,
                'discount_price' => $discountPrice,
                'discount_percentage' => $pkg['discount'],
                'is_popular' => $pkg['popular'],
                'sort_order' => $pkg['order'],
            ]);
        }
    }

    // ========================================
    // SISTEMA DE BACKUP
    // ========================================

    /**
     * Página de gerenciamento de backup
     */
    public function backup()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $backupService = new BackupService();
        $config = config('Backup');

        // Verificar conexão com Google Drive
        $googleDriveConnected = false;
        $authUrl = '';
        $remoteBackups = [];

        try {
            $googleDrive = new GoogleDriveService();
            $googleDriveConnected = $googleDrive->isAuthenticated();

            if ($googleDriveConnected) {
                $remoteBackups = $googleDrive->listBackupFiles(
                    $config->backupPrefix,
                    $config->googleDriveFolderId
                );
            } else {
                $authUrl = $googleDrive->getAuthUrl();
            }
        } catch (\Exception $e) {
            // Credenciais não configuradas
            log_message('warning', 'Google Drive não configurado: ' . $e->getMessage());
        }

        // Carregar configurações salvas (ou padrão do config)
        $settings = $this->loadBackupSettings();

        $data = [
            'title' => 'Sistema de Backup',
            'googleDriveConnected' => $googleDriveConnected,
            'authUrl' => $authUrl,
            'localBackups' => $backupService->listBackups(),
            'remoteBackups' => $remoteBackups,
            'settings' => $settings,
        ];

        return view('admin/backup', $data);
    }

    /**
     * Executar backup manual
     */
    public function runBackup()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $type = $this->request->getPost('type') ?? 'full';

        try {
            $backupService = new BackupService();
            $result = $backupService->createBackup($type);

            if ($result['success']) {
                $message = 'Backup criado com sucesso!';

                if (!empty($result['database'])) {
                    $message .= ' BD: ' . basename($result['database']);
                }
                if (!empty($result['files'])) {
                    $message .= ' Arquivos: ' . basename($result['files']);
                }

                return redirect()->to('/admin/backup')->with('success', $message);
            }

            return redirect()->to('/admin/backup')->with('error', 'Erro ao criar backup: ' . ($result['error'] ?? 'Erro desconhecido'));
        } catch (\Exception $e) {
            return redirect()->to('/admin/backup')->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Autorizar Google Drive com código
     */
    public function authCode()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $code = $this->request->getPost('code');

        if (empty($code)) {
            return redirect()->to('/admin/backup')->with('error', 'Código de autorização não fornecido');
        }

        try {
            $googleDrive = new GoogleDriveService();
            $result = $googleDrive->exchangeCodeForToken($code);

            if ($result['success']) {
                return redirect()->to('/admin/backup')->with('success', 'Google Drive conectado com sucesso!');
            }

            return redirect()->to('/admin/backup')->with('error', 'Erro na autorização: ' . ($result['error'] ?? 'Erro desconhecido'));
        } catch (\Exception $e) {
            return redirect()->to('/admin/backup')->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Desconectar Google Drive
     */
    public function disconnectDrive()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $config = config('Backup');

        if (file_exists($config->googleTokenPath)) {
            unlink($config->googleTokenPath);
        }

        return redirect()->to('/admin/backup')->with('success', 'Google Drive desconectado');
    }

    /**
     * Download de backup local
     */
    public function downloadBackup($filename)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $config = config('Backup');
        $filepath = $config->tempPath . $filename;

        if (!file_exists($filepath)) {
            return redirect()->to('/admin/backup')->with('error', 'Arquivo não encontrado');
        }

        return $this->response->download($filepath, null);
    }

    /**
     * Deletar backup local
     */
    public function deleteBackup()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $filename = $this->request->getPost('file');
        $config = config('Backup');
        $filepath = $config->tempPath . $filename;

        if (file_exists($filepath)) {
            unlink($filepath);
            return redirect()->to('/admin/backup')->with('success', 'Backup deletado');
        }

        return redirect()->to('/admin/backup')->with('error', 'Arquivo não encontrado');
    }

    /**
     * Upload para Google Drive
     */
    public function uploadToDrive()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $filename = $this->request->getPost('file');
        $config = config('Backup');
        $filepath = $config->tempPath . $filename;

        if (!file_exists($filepath)) {
            return redirect()->to('/admin/backup')->with('error', 'Arquivo não encontrado');
        }

        try {
            $googleDrive = new GoogleDriveService();

            if (!$googleDrive->isAuthenticated()) {
                return redirect()->to('/admin/backup')->with('error', 'Google Drive não conectado');
            }

            $fileId = $googleDrive->uploadFile($filepath, $filename, $config->googleDriveFolderId);

            return redirect()->to('/admin/backup')->with('success', 'Arquivo enviado para o Google Drive!');
        } catch (\Exception $e) {
            return redirect()->to('/admin/backup')->with('error', 'Erro no upload: ' . $e->getMessage());
        }
    }

    /**
     * Salvar configurações de backup
     */
    public function saveBackupSettings()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $settings = [
            'keepLocalBackups' => (int) ($this->request->getPost('keep_local') ?? 3),
            'keepRemoteBackups' => (int) ($this->request->getPost('keep_remote') ?? 7),
            'notificationEmail' => $this->request->getPost('notification_email') ?? '',
            'notifyOnErrorOnly' => $this->request->getPost('notify_error_only') ? true : false,
            'includeFolders' => $this->request->getPost('include_folders') ?? ['app', 'public/uploads'],
        ];

        // Salvar em arquivo JSON
        $settingsPath = WRITEPATH . 'backup_settings.json';
        file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));

        return redirect()->to('/admin/backup')->with('success', 'Configurações salvas!');
    }

    /**
     * Carregar configurações de backup
     */
    private function loadBackupSettings(): array
    {
        $settingsPath = WRITEPATH . 'backup_settings.json';

        if (file_exists($settingsPath)) {
            $saved = json_decode(file_get_contents($settingsPath), true);
            if ($saved) {
                return $saved;
            }
        }

        // Retornar valores padrão do config
        $config = config('Backup');

        return [
            'keepLocalBackups' => $config->keepLocalBackups ?? 3,
            'keepRemoteBackups' => $config->keepRemoteBackups ?? 7,
            'notificationEmail' => $config->notificationEmail ?? '',
            'notifyOnErrorOnly' => $config->notifyOnErrorOnly ?? false,
            'includeFolders' => $config->includeFolders ?? ['app', 'public/uploads'],
        ];
    }

    // ========================================
    // CONFIGURAÇÕES DA PLATAFORMA
    // ========================================

    /**
     * Página de configurações
     */
    public function settings()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $settingsService = new SettingsService();

        $data = [
            'title' => 'Configurações da Plataforma',
            'settings' => $settingsService->getAll(),
        ];

        return view('admin/settings', $data);
    }

    /**
     * Salvar configurações
     */
    public function saveSettings()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $settingsService = new SettingsService();

        // Campos booleanos (checkboxes)
        $boolFields = [
            'maintenance_mode',
            'enable_pix',
            'enable_credit_card',
            'enable_boleto',
            'require_approval',
            'allow_flexible_goal',
            'raffles_enabled',
            'email_notifications',
            'push_notifications',
            'notify_new_campaign',
            'notify_new_donation',
            'notify_new_user',
            'recaptcha_enabled',
        ];

        $postData = $this->request->getPost();

        // Salvar todas as configurações
        $settingsService->saveFromPost($postData, $boolFields);

        return redirect()->to('/admin/settings')->with('success', 'Configurações salvas com sucesso!');
    }

    // ===============================
    // MODERAÇÃO DE COMENTÁRIOS
    // ===============================

    /**
     * Listar comentários para moderação
     */
    public function comments()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $commentModel = new \App\Models\CampaignCommentModel();
        $status = $this->request->getGet('status') ?? 'pending';

        // Buscar comentários com filtro
        $query = $commentModel
            ->select('campaign_comments.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug, users.name as user_name')
            ->join('campaigns', 'campaigns.id = campaign_comments.campaign_id')
            ->join('users', 'users.id = campaign_comments.user_id', 'left');

        if ($status !== 'all') {
            $query->where('campaign_comments.status', $status);
        }

        $comments = $query->orderBy('campaign_comments.created_at', 'DESC')
            ->findAll(100);

        // Estatísticas
        $stats = [
            'pending' => $commentModel->where('status', 'pending')->countAllResults(),
            'approved' => $commentModel->where('status', 'approved')->countAllResults(),
            'rejected' => $commentModel->where('status', 'rejected')->countAllResults(),
        ];

        return view('admin/comments', [
            'title' => 'Moderação de Comentários',
            'comments' => $comments,
            'current_status' => $status,
            'stats' => $stats
        ]);
    }

    /**
     * Aprovar comentário
     */
    public function approveComment($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $commentModel = new \App\Models\CampaignCommentModel();
        $comment = $commentModel->find($id);

        if (!$comment) {
            return redirect()->back()->with('error', 'Comentário não encontrado.');
        }

        if ($commentModel->update($id, ['status' => 'approved'])) {
            // Log de auditoria
            $this->logAudit('comment_approved', 'campaign_comments', $id, [
                'comment' => substr($comment['comment'], 0, 100),
                'campaign_id' => $comment['campaign_id']
            ]);

            return redirect()->back()->with('success', 'Comentário aprovado com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao aprovar comentário.');
    }

    /**
     * Rejeitar comentário
     */
    public function rejectComment($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $commentModel = new \App\Models\CampaignCommentModel();
        $comment = $commentModel->find($id);

        if (!$comment) {
            return redirect()->back()->with('error', 'Comentário não encontrado.');
        }

        if ($commentModel->update($id, ['status' => 'rejected'])) {
            // Log de auditoria
            $this->logAudit('comment_rejected', 'campaign_comments', $id, [
                'comment' => substr($comment['comment'], 0, 100),
                'campaign_id' => $comment['campaign_id']
            ]);

            return redirect()->back()->with('success', 'Comentário rejeitado.');
        }

        return redirect()->back()->with('error', 'Erro ao rejeitar comentário.');
    }

    /**
     * Deletar comentário permanentemente
     */
    public function deleteCommentAdmin($id)
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $commentModel = new \App\Models\CampaignCommentModel();
        $comment = $commentModel->find($id);

        if (!$comment) {
            return redirect()->back()->with('error', 'Comentário não encontrado.');
        }

        // Log de auditoria antes de deletar
        $this->logAudit('comment_deleted', 'campaign_comments', $id, [
            'comment' => substr($comment['comment'], 0, 100),
            'campaign_id' => $comment['campaign_id'],
            'donor_name' => $comment['donor_name'] ?? 'N/A'
        ]);

        if ($commentModel->delete($id)) {
            return redirect()->back()->with('success', 'Comentário deletado permanentemente.');
        }

        return redirect()->back()->with('error', 'Erro ao deletar comentário.');
    }

    /**
     * Aprovar múltiplos comentários
     */
    public function bulkApproveComments()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $ids = $this->request->getPost('comment_ids');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Nenhum comentário selecionado.');
        }

        $commentModel = new \App\Models\CampaignCommentModel();
        $approved = 0;

        foreach ($ids as $id) {
            if ($commentModel->update($id, ['status' => 'approved'])) {
                $approved++;
            }
        }

        $this->logAudit('comments_bulk_approved', 'campaign_comments', 0, [
            'count' => $approved,
            'ids' => implode(',', $ids)
        ]);

        return redirect()->back()->with('success', "{$approved} comentário(s) aprovado(s) com sucesso!");
    }

    /**
     * Rejeitar múltiplos comentários
     */
    public function bulkRejectComments()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $ids = $this->request->getPost('comment_ids');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Nenhum comentário selecionado.');
        }

        $commentModel = new \App\Models\CampaignCommentModel();
        $rejected = 0;

        foreach ($ids as $id) {
            if ($commentModel->update($id, ['status' => 'rejected'])) {
                $rejected++;
            }
        }

        $this->logAudit('comments_bulk_rejected', 'campaign_comments', 0, [
            'count' => $rejected,
            'ids' => implode(',', $ids)
        ]);

        return redirect()->back()->with('success', "{$rejected} comentário(s) rejeitado(s).");
    }

    /**
     * Visualizar Logs de Auditoria (com filtros)
     */
    public function auditLogs()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        // Obter filtros da URL
        $action = $this->request->getGet('action');
        $userId = $this->request->getGet('user_id');
        $entityType = $this->request->getGet('entity_type');
        $search = $this->request->getGet('search');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $sortOrder = $this->request->getGet('sort_order') ?? 'DESC';
        // Validar sortOrder para prevenir SQL injection
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'DESC';
        $perPage = 50;

        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs')
            ->select('audit_logs.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left');

        // Filtrar por ação
        if ($action) {
            $builder->where('audit_logs.action', $action);
        }

        // Filtrar por usuário
        if ($userId) {
            $builder->where('audit_logs.user_id', $userId);
        }

        // Filtrar por tipo de entidade
        if ($entityType) {
            $builder->where('audit_logs.entity_type', $entityType);
        }

        // Busca por nome/email de usuário ou IP
        if ($search) {
            $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('audit_logs.ip_address', $search)
                ->orLike('audit_logs.details', $search)
                ->groupEnd();
        }

        // Filtrar por data
        if ($dateFrom) {
            $builder->where('audit_logs.created_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $builder->where('audit_logs.created_at <=', $dateTo . ' 23:59:59');
        }

        // Total de registros (antes da paginação)
        $totalLogs = $builder->countAllResults(false);

        // Ordenação e paginação
        $builder->orderBy('audit_logs.created_at', $sortOrder);

        // Paginação
        $page = (int)($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $logs = $builder->get()->getResultArray();

        // Listas para filtros
        $actionsQuery = $db->query("SELECT DISTINCT action FROM audit_logs WHERE action IS NOT NULL");
        $actionsList = array_column($actionsQuery->getResultArray(), 'action');

        $entityTypesQuery = $db->query("SELECT DISTINCT entity_type FROM audit_logs WHERE entity_type IS NOT NULL");
        $entityTypesList = array_column($entityTypesQuery->getResultArray(), 'entity_type');

        // Lista de usuários que geraram logs (admins)
        $usersQuery = $db->query("SELECT DISTINCT u.id, u.name FROM audit_logs al JOIN users u ON u.id = al.user_id WHERE al.user_id IS NOT NULL ORDER BY u.name ASC");
        $usersList = $usersQuery->getResultArray();

        // Estatísticas
        $stats = [
            'total_logs' => $totalLogs,
            'today' => $db->table('audit_logs')->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'this_week' => $db->table('audit_logs')->where('created_at >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            'this_month' => $db->table('audit_logs')->where('DATE_FORMAT(created_at, "%Y-%m")', date('Y-m'))->countAllResults(),
        ];

        $data = [
            'title' => 'Logs de Auditoria',
            'logs' => $logs,
            'actions_list' => $actionsList,
            'entity_types_list' => $entityTypesList,
            'users_list' => $usersList,
            'stats' => $stats,
            'filters' => [
                'action' => $action,
                'user_id' => $userId,
                'entity_type' => $entityType,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_order' => $sortOrder
            ],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalLogs,
                'total_pages' => ceil($totalLogs / $perPage)
            ]
        ];

        return view('admin/audit_logs', $data);
    }

    /**
     * Log de auditoria
     */
    private function logAudit($action, $entity, $entityId, $details = [])
    {
        try {
            $db = \Config\Database::connect();
            $db->table('audit_logs')->insert([
                'user_id' => session()->get('id'),
                'action' => $action,
                'entity_type' => $entity,
                'entity_id' => $entityId,
                'details' => json_encode($details),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao registrar audit log: ' . $e->getMessage());
        }
    }

    // ===============================
    // EXPORT DE RELATÓRIOS
    // ===============================


    /**
     * Exportar doações para CSV
     */
    public function exportDonations()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $status = $this->request->getGet('status') ?? 'all';

        $db = \Config\Database::connect();
        $builder = $db->table('donations d')
            ->select('d.*, c.title as campaign_title, u.name as user_name, u.email as user_email')
            ->join('campaigns c', 'c.id = d.campaign_id', 'left')
            ->join('users u', 'u.id = d.user_id', 'left')
            ->where('d.created_at >=', $startDate . ' 00:00:00')
            ->where('d.created_at <=', $endDate . ' 23:59:59');

        if ($status !== 'all') {
            $builder->where('d.status', $status);
        }

        $donations = $builder->orderBy('d.created_at', 'DESC')->get()->getResultArray();

        // Gerar CSV
        $filename = 'doacoes_' . date('Y-m-d_His') . '.csv';

        $this->logAudit('export_donations', 'donations', 0, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'count' => count($donations)
        ]);

        return $this->generateCSV($donations, $filename, [
            'ID', 'Campanha', 'Doador', 'Email', 'Valor', 'Status', 'Método', 'Anônimo', 'Data'
        ], function($row) {
            return [
                $row['id'],
                $row['campaign_title'] ?? 'N/A',
                $row['donor_name'] ?? $row['user_name'] ?? 'N/A',
                $row['donor_email'] ?? $row['user_email'] ?? 'N/A',
                number_format($row['amount'], 2, ',', '.'),
                $this->translateStatus($row['status']),
                strtoupper($row['payment_method'] ?? 'N/A'),
                $row['is_anonymous'] ? 'Sim' : 'Não',
                date('d/m/Y H:i', strtotime($row['created_at']))
            ];
        });
    }

    /**
     * Exportar campanhas para CSV
     */
    public function exportCampaigns()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $status = $this->request->getGet('status') ?? 'all';

        $db = \Config\Database::connect();
        $builder = $db->table('campaigns c')
            ->select('c.*, u.name as creator_name, u.email as creator_email')
            ->join('users u', 'u.id = c.user_id', 'left');

        if ($status !== 'all') {
            $builder->where('c.status', $status);
        }

        $campaigns = $builder->orderBy('c.created_at', 'DESC')->get()->getResultArray();

        $filename = 'campanhas_' . date('Y-m-d_His') . '.csv';

        $this->logAudit('export_campaigns', 'campaigns', 0, [
            'status' => $status,
            'count' => count($campaigns)
        ]);

        return $this->generateCSV($campaigns, $filename, [
            'ID', 'Título', 'Criador', 'Email', 'Categoria', 'Meta', 'Arrecadado', '% Atingido', 'Status', 'Doadores', 'Visualizações', 'Data Criação', 'Data Fim'
        ], function($row) {
            $percentage = $row['goal_amount'] > 0 ? ($row['current_amount'] / $row['goal_amount']) * 100 : 0;
            return [
                $row['id'],
                $row['title'],
                $row['creator_name'] ?? 'N/A',
                $row['creator_email'] ?? 'N/A',
                $row['category'],
                number_format($row['goal_amount'], 2, ',', '.'),
                number_format($row['current_amount'], 2, ',', '.'),
                number_format($percentage, 1) . '%',
                $this->translateCampaignStatus($row['status']),
                $row['donors_count'],
                $row['views_count'],
                date('d/m/Y', strtotime($row['created_at'])),
                date('d/m/Y', strtotime($row['end_date']))
            ];
        });
    }

    /**
     * Exportar usuários para CSV
     */
    public function exportUsers()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $db = \Config\Database::connect();

        // Buscar usuários com contagem de campanhas e doações
        $users = $db->query("
            SELECT u.*,
                   (SELECT COUNT(*) FROM campaigns WHERE user_id = u.id) as campaigns_count,
                   (SELECT COUNT(*) FROM donations WHERE user_id = u.id AND status = 'received') as donations_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = u.id AND status = 'received') as total_donated
            FROM users u
            ORDER BY u.created_at DESC
        ")->getResultArray();

        $filename = 'usuarios_' . date('Y-m-d_His') . '.csv';

        $this->logAudit('export_users', 'users', 0, [
            'count' => count($users)
        ]);

        return $this->generateCSV($users, $filename, [
            'ID', 'Nome', 'Email', 'Telefone', 'CPF', 'Role', 'Campanhas', 'Doações', 'Total Doado', 'Data Cadastro'
        ], function($row) {
            return [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'] ?? 'N/A',
                $row['cpf'] ?? 'N/A',
                $row['role'] ?? 'user',
                $row['campaigns_count'],
                $row['donations_count'],
                'R$ ' . number_format($row['total_donated'], 2, ',', '.'),
                date('d/m/Y H:i', strtotime($row['created_at']))
            ];
        });
    }

    /**
     * Exportar saques para CSV
     */
    public function exportWithdrawals()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $status = $this->request->getGet('status') ?? 'all';

        $db = \Config\Database::connect();
        $builder = $db->table('withdrawals w')
            ->select('w.*, c.title as campaign_title, u.name as user_name, u.email as user_email')
            ->join('campaigns c', 'c.id = w.campaign_id', 'left')
            ->join('users u', 'u.id = w.user_id', 'left');

        if ($status !== 'all') {
            $builder->where('w.status', $status);
        }

        $withdrawals = $builder->orderBy('w.created_at', 'DESC')->get()->getResultArray();

        $filename = 'saques_' . date('Y-m-d_His') . '.csv';

        $this->logAudit('export_withdrawals', 'withdrawals', 0, [
            'status' => $status,
            'count' => count($withdrawals)
        ]);

        return $this->generateCSV($withdrawals, $filename, [
            'ID', 'Usuário', 'Email', 'Campanha', 'Valor Bruto', 'Taxa', 'Valor Líquido', 'Status', 'Método', 'Data Solicitação'
        ], function($row) {
            return [
                $row['id'],
                $row['user_name'] ?? 'N/A',
                $row['user_email'] ?? 'N/A',
                $row['campaign_title'] ?? 'N/A',
                'R$ ' . number_format($row['amount'], 2, ',', '.'),
                'R$ ' . number_format($row['fee'] ?? 0, 2, ',', '.'),
                'R$ ' . number_format($row['net_amount'] ?? 0, 2, ',', '.'),
                $this->translateWithdrawalStatus($row['status']),
                strtoupper($row['payment_method'] ?? 'N/A'),
                date('d/m/Y H:i', strtotime($row['created_at']))
            ];
        });
    }

    /**
     * Exportar rifas para CSV
     */
    public function exportRaffles()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $raffleModel = new RaffleModel();
        $raffles = $raffleModel->orderBy('created_at', 'DESC')->findAll();

        $filename = 'rifas_' . date('Y-m-d_His') . '.csv';

        $this->logAudit('export_raffles', 'raffles', 0, [
            'count' => count($raffles)
        ]);

        return $this->generateCSV($raffles, $filename, [
            'ID', 'Título', 'Preço/Cota', 'Total Números', 'Vendidos', 'Receita', 'Prêmio Principal %', 'Status', 'Data Sorteio', 'Número Vencedor'
        ], function($row) {
            return [
                $row['id'],
                $row['title'],
                'R$ ' . number_format($row['price_per_number'], 2, ',', '.'),
                $row['total_numbers'],
                $row['sold_numbers'] ?? 0,
                'R$ ' . number_format($row['total_revenue'] ?? 0, 2, ',', '.'),
                $row['main_prize_percentage'] . '%',
                $this->translateRaffleStatus($row['status']),
                !empty($row['federal_lottery_date']) ? date('d/m/Y', strtotime($row['federal_lottery_date'])) : 'N/A',
                $row['winning_number'] ?? 'N/A'
            ];
        });
    }

    /**
     * Gerar arquivo CSV
     */
    private function generateCSV(array $data, string $filename, array $headers, callable $rowMapper)
    {
        $output = fopen('php://temp', 'r+');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalhos
        fputcsv($output, $headers, ';');

        // Dados
        foreach ($data as $row) {
            fputcsv($output, $rowMapper($row), ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Traduzir status de campanha
     */
    private function translateCampaignStatus($status)
    {
        return match($status) {
            'pending' => 'Pendente',
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'completed' => 'Finalizada',
            'rejected' => 'Rejeitada',
            default => $status
        };
    }

    /**
     * Traduzir status de saque
     */
    private function translateWithdrawalStatus($status)
    {
        return match($status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Concluído',
            'rejected' => 'Rejeitado',
            default => $status
        };
    }

    /**
     * Traduzir status de rifa
     */
    private function translateRaffleStatus($status)
    {
        return match($status) {
            'draft' => 'Rascunho',
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'completed' => 'Finalizada',
            default => $status
        };
    }
}

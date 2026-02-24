<?php

namespace App\Controllers;

use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;

class DashboardController extends BaseController
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
     * Dashboard Principal (Simples)
     */
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $user = $this->userModel->find($userId);

        // Buscar conta Asaas do usuário
        $asaasAccountModel = new \App\Models\AsaasAccount();
        $asaasAccount = $asaasAccountModel->getByUserId($userId);

        // Se não encontrou na tabela asaas_accounts, verifica nos campos do usuário
        if (!$asaasAccount && !empty($user['asaas_account_id'])) {
            $asaasAccount = [
                'asaas_account_id' => $user['asaas_account_id'],
                'asaas_wallet_id' => $user['asaas_wallet_id'] ?? null,
                'account_status' => 'active',
            ];
        }

        // Buscar estatísticas do usuário
        $data = [
            'title' => 'Meu Dashboard',
            'total_campaigns' => $this->campaignModel->where('user_id', $userId)->countAllResults(),
            'active_campaigns' => $this->campaignModel->where('user_id', $userId)->where('status', 'active')->countAllResults(),
            'total_raised' => $this->getTotalRaised($userId),
            'total_donations' => $this->getTotalDonations($userId),
            'recent_campaigns' => $this->getRecentCampaigns($userId, 5),
            'asaas_account' => $asaasAccount
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Dashboard Analytics (Avançado com Gráficos Tremor-Style)
     */
    public function analytics()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        // Dados para KPI Cards
        $currentMonth = date('Y-m');
        $previousMonth = date('Y-m', strtotime('-1 month'));

        $totalRaised = $this->getTotalRaised($userId);
        $previousTotalRaised = $this->getTotalRaised($userId, $previousMonth);

        $totalDonations = $this->getTotalDonations($userId);
        $previousDonations = $this->getTotalDonations($userId, $previousMonth);

        $activeCampaigns = $this->campaignModel->where('user_id', $userId)->where('status', 'active')->countAllResults();
        $previousActive = $this->campaignModel->where('user_id', $userId)->where('status', 'active')
            ->where('DATE_FORMAT(created_at, "%Y-%m") <=', $previousMonth)->countAllResults();

        // Dados para Gráficos
        $donationLabels = [];
        $donationData = [];

        // Últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthLabel = $this->getMonthName(date('m', strtotime("-$i months")));

            $donationLabels[] = $monthLabel;
            $donationData[] = $this->getTotalRaised($userId, $month);
        }

        // Doações por Categoria
        $categoryLabels = ['Médica', 'Social', 'Educação', 'Negócio', 'Criativa'];
        $categoryData = [];

        foreach (['medica', 'social', 'educacao', 'negocio', 'criativa'] as $category) {
            $categoryData[] = $this->getRaisedByCategory($userId, $category);
        }

        // Métodos de Pagamento
        $donutLabels = ['PIX', 'Cartão', 'Boleto'];
        $donutData = [
            $this->getDonationsByPaymentMethod($userId, 'pix'),
            $this->getDonationsByPaymentMethod($userId, 'credit_card'),
            $this->getDonationsByPaymentMethod($userId, 'boleto')
        ];

        // Últimas Doações para Tabela
        $recentDonations = $this->getRecentDonationsForTable($userId, 20);

        // Taxa de conversão (exemplo: visitas vs doações)
        $conversionRate = $totalDonations > 0 ? ($totalDonations / max(1, $totalRaised / 100)) * 100 : 0;
        $previousConversion = $previousDonations > 0 ? ($previousDonations / max(1, $previousTotalRaised / 100)) * 100 : 0;

        $data = [
            'title' => 'Analytics Dashboard',

            // KPI Cards
            'total_raised' => $totalRaised,
            'previous_total_raised' => $previousTotalRaised,
            'total_donations' => $totalDonations,
            'previous_donations' => $previousDonations,
            'active_campaigns' => $activeCampaigns,
            'previous_active' => $previousActive,
            'conversion_rate' => $conversionRate,
            'previous_conversion' => $previousConversion,

            // Charts
            'donation_labels' => $donationLabels,
            'donation_data' => $donationData,
            'category_labels' => $categoryLabels,
            'category_data' => $categoryData,
            'donut_labels' => $donutLabels,
            'donut_data' => $donutData,

            // Table
            'recent_donations' => $recentDonations
        ];

        return view('dashboard/analytics', $data);
    }

    /**
     * Minhas Campanhas
     */
    public function myCampaigns()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        log_message('error', 'MY_CAMPAIGNS DEBUG: user_id=' . $userId);

        $campaigns = $this->campaignModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        log_message('error', 'MY_CAMPAIGNS DEBUG: found ' . count($campaigns) . ' campaigns');

        // Adicionar estatísticas para cada campanha
        foreach ($campaigns as &$campaign) {
            $campaign['donors_count'] = $this->getDonorsCount($campaign['id']);
            $campaign['raised_amount'] = $this->getCampaignRaised($campaign['id']);
            $campaign['percentage'] = ($campaign['raised_amount'] / max(1, $campaign['goal_amount'])) * 100;
        }

        log_message('error', 'MY_CAMPAIGNS DEBUG: campaigns data = ' . json_encode($campaigns));

        $data = [
            'title' => 'Minhas Campanhas',
            'campaigns' => $campaigns
        ];

        return view('dashboard/my_campaigns', $data);
    }

    /**
     * Minhas Doações
     */
    public function myDonations()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $email = session()->get('email');

        // Buscar doações do usuário (por email de doador)
        $donations = $this->donationModel
            ->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('donations.donor_email', $email)
            ->orderBy('donations.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Minhas Doações',
            'donations' => $donations,
            'total_donated' => array_sum(array_column($donations, 'amount'))
        ];

        return view('dashboard/my_donations', $data);
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    private function getTotalRaised($userId, $month = null)
    {
        $builder = $this->donationModel
            ->select('SUM(donations.amount) as total')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('donations.status', 'paid');

        if ($month) {
            $builder->where('DATE_FORMAT(donations.created_at, "%Y-%m")', $month);
        }

        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }

    private function getTotalDonations($userId, $month = null)
    {
        $builder = $this->donationModel
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('donations.status', 'paid');

        if ($month) {
            $builder->where('DATE_FORMAT(donations.created_at, "%Y-%m")', $month);
        }

        return $builder->countAllResults();
    }

    private function getRaisedByCategory($userId, $category)
    {
        $result = $this->donationModel
            ->select('SUM(donations.amount) as total')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('campaigns.category', $category)
            ->where('donations.status', 'paid')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getDonationsByPaymentMethod($userId, $method)
    {
        $result = $this->donationModel
            ->select('SUM(donations.amount) as total')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('donations.payment_method', $method)
            ->where('donations.status', 'paid')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function getRecentCampaigns($userId, $limit = 5)
    {
        return $this->campaignModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    private function getRecentDonationsForTable($userId, $limit = 20)
    {
        $donations = $this->donationModel
            ->select('donations.*, campaigns.title as campaign')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('donations.status', 'paid')
            ->orderBy('donations.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        // Formatar para JSON
        $formatted = [];
        foreach ($donations as $donation) {
            $formatted[] = [
                'id' => $donation['id'],
                'donor' => $donation['is_anonymous'] ? 'Anônimo' : $donation['donor_name'],
                'amount' => (float) $donation['amount'],
                'campaign' => $donation['campaign'],
                'date' => date('d/m/Y', strtotime($donation['created_at'])),
                'method' => $this->formatPaymentMethod($donation['payment_method'])
            ];
        }

        return $formatted;
    }

    private function getDonorsCount($campaignId)
    {
        return $this->donationModel
            ->where('campaign_id', $campaignId)
            ->where('status', 'paid')
            ->countAllResults();
    }

    private function getCampaignRaised($campaignId)
    {
        $result = $this->donationModel
            ->select('SUM(amount) as total')
            ->where('campaign_id', $campaignId)
            ->where('status', 'paid')
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    private function formatPaymentMethod($method)
    {
        $map = [
            'pix' => 'PIX',
            'credit_card' => 'Cartão',
            'boleto' => 'Boleto'
        ];

        return $map[$method] ?? ucfirst($method);
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
}

<?php

namespace App\Controllers;

use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;

class Home extends BaseController
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
     * Homepage
     * GET /
     */
    public function index()
    {
        // Buscar estatísticas reais da plataforma
        $stats = [
            'total_raised' => $this->getTotalRaised(),
            'total_campaigns' => $this->campaignModel->countAll(),
            'total_users' => $this->userModel->countAll(),
            'total_donors' => $this->getTotalDonors(),
        ];

        // Buscar campanhas em destaque (últimas 6 ativas)
        $featuredCampaigns = $this->campaignModel
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->limit(6)
            ->find();

        // Calcular progresso de cada campanha
        foreach ($featuredCampaigns as &$campaign) {
            $raised = $this->getCampaignRaised($campaign['id']);
            $campaign['raised'] = $raised;
            $campaign['percentage'] = $campaign['goal_amount'] > 0
                ? min(($raised / $campaign['goal_amount']) * 100, 100)
                : 0;
            $campaign['donors_count'] = $this->getCampaignDonors($campaign['id']);
        }

        $data = [
            'title' => 'DoarFazBem - A plataforma de crowdfunding mais justa do Brasil',
            'description' => 'Campanhas médicas e sociais 100% gratuitas. Sistema transparente e seguro.',
            'stats' => $stats,
            'campaigns' => $featuredCampaigns
        ];

        return view('home/index', $data);
    }

    /**
     * Obter total arrecadado na plataforma
     */
    private function getTotalRaised()
    {
        $result = $this->donationModel
            ->selectSum('amount')
            ->whereIn('status', ['confirmed', 'received'])
            ->get()
            ->getRowArray();

        return $result['amount'] ?? 0;
    }

    /**
     * Obter total de doadores únicos
     */
    private function getTotalDonors()
    {
        $result = $this->donationModel
            ->select('COUNT(DISTINCT donor_email) as total')
            ->whereIn('status', ['confirmed', 'received'])
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }

    /**
     * Obter total arrecadado de uma campanha
     */
    private function getCampaignRaised($campaignId)
    {
        $result = $this->donationModel
            ->selectSum('amount')
            ->where('campaign_id', $campaignId)
            ->whereIn('status', ['confirmed', 'received'])
            ->get()
            ->getRowArray();

        return $result['amount'] ?? 0;
    }

    /**
     * Obter número de doadores de uma campanha
     */
    private function getCampaignDonors($campaignId)
    {
        $result = $this->donationModel
            ->select('COUNT(DISTINCT donor_email) as total')
            ->where('campaign_id', $campaignId)
            ->whereIn('status', ['confirmed', 'received'])
            ->get()
            ->getRowArray();

        return $result['total'] ?? 0;
    }
}

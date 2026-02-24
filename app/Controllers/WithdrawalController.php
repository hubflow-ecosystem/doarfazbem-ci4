<?php

namespace App\Controllers;

use App\Models\WithdrawalModel;
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;

/**
 * Controller para gerenciamento de saques de criadores
 */
class WithdrawalController extends BaseController
{
    protected $withdrawalModel;
    protected $campaignModel;
    protected $donationModel;

    public function __construct()
    {
        $this->withdrawalModel = new WithdrawalModel();
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
    }

    /**
     * Dashboard de saques do criador
     */
    public function index()
    {
        $userId = session()->get('id');

        // Buscar campanhas do usuário
        $campaigns = $this->campaignModel->where('user_id', $userId)->findAll();

        // Calcular saldo por campanha
        $campaignsWithBalance = [];
        $totalAvailable = 0;

        foreach ($campaigns as $campaign) {
            $balance = $this->withdrawalModel->getCampaignBalance($campaign['id']);
            $campaign['balance'] = $balance;
            $campaignsWithBalance[] = $campaign;
            $totalAvailable += $balance['available'];
        }

        // Histórico de saques
        $withdrawals = $this->withdrawalModel->getUserWithdrawals($userId);

        // Estatísticas
        $stats = [
            'total_available' => $totalAvailable,
            'total_withdrawn' => 0,
            'pending_withdrawals' => 0,
        ];

        foreach ($withdrawals as $w) {
            if ($w['status'] === 'completed') {
                $stats['total_withdrawn'] += $w['net_amount'];
            }
            if ($w['status'] === 'pending' || $w['status'] === 'processing') {
                $stats['pending_withdrawals'] += $w['amount'];
            }
        }

        $data = [
            'title' => 'Saques | Dashboard',
            'campaigns' => $campaignsWithBalance,
            'withdrawals' => $withdrawals,
            'stats' => $stats,
        ];

        return view('dashboard/withdrawals', $data);
    }

    /**
     * Formulário de solicitação de saque
     */
    public function request()
    {
        $userId = session()->get('id');

        // Buscar campanhas com saldo disponível
        $campaigns = $this->campaignModel->where('user_id', $userId)->findAll();

        $campaignsWithBalance = [];
        foreach ($campaigns as $campaign) {
            $balance = $this->withdrawalModel->getCampaignBalance($campaign['id']);
            if ($balance['available'] > 0) {
                $campaign['balance'] = $balance;
                $campaignsWithBalance[] = $campaign;
            }
        }

        // Buscar dados de pagamento do usuário
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $data = [
            'title' => 'Solicitar Saque | Dashboard',
            'campaigns' => $campaignsWithBalance,
            'user' => $user,
        ];

        return view('dashboard/withdrawal_request', $data);
    }

    /**
     * Processa solicitação de saque
     */
    public function store()
    {
        $userId = session()->get('id');

        $validation = \Config\Services::validation();

        $validation->setRules([
            'campaign_id' => 'required|integer',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[pix,bank_transfer]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $campaignId = (int) $this->request->getPost('campaign_id');
        $amount = (float) $this->request->getPost('amount');
        $paymentMethod = $this->request->getPost('payment_method');

        // Verificar se a campanha pertence ao usuário
        $campaign = $this->campaignModel->find($campaignId);
        if (!$campaign || $campaign['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        // Verificar saldo disponível
        $balance = $this->withdrawalModel->getCampaignBalance($campaignId);
        if ($amount > $balance['available']) {
            return redirect()->back()->withInput()
                ->with('error', 'Saldo insuficiente. Disponível: R$ ' . number_format($balance['available'], 2, ',', '.'));
        }

        // Valor mínimo de saque
        $minAmount = 20.00;
        if ($amount < $minAmount) {
            return redirect()->back()->withInput()
                ->with('error', 'Valor mínimo para saque: R$ ' . number_format($minAmount, 2, ',', '.'));
        }

        // Montar dados do saque
        $data = [
            'user_id' => $userId,
            'campaign_id' => $campaignId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ];

        // Dados de PIX
        if ($paymentMethod === 'pix') {
            $data['pix_key'] = $this->request->getPost('pix_key');
            $data['pix_key_type'] = $this->request->getPost('pix_key_type');

            if (empty($data['pix_key'])) {
                return redirect()->back()->withInput()->with('error', 'Informe a chave PIX.');
            }
        }
        // Dados bancários
        else {
            $data['bank_code'] = $this->request->getPost('bank_code');
            $data['bank_agency'] = $this->request->getPost('bank_agency');
            $data['bank_account'] = $this->request->getPost('bank_account');
            $data['bank_account_type'] = $this->request->getPost('bank_account_type');

            if (empty($data['bank_code']) || empty($data['bank_agency']) || empty($data['bank_account'])) {
                return redirect()->back()->withInput()->with('error', 'Preencha todos os dados bancários.');
            }
        }

        $data['notes'] = $this->request->getPost('notes');

        // Criar solicitação
        $withdrawalId = $this->withdrawalModel->createWithdrawal($data);

        if ($withdrawalId) {
            return redirect()->to('/dashboard/withdrawals')
                ->with('success', 'Solicitação de saque enviada! Processamento em até 3 dias úteis.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao processar solicitação.');
    }

    /**
     * Cancela solicitação de saque (se ainda pendente)
     */
    public function cancel(int $id)
    {
        $userId = session()->get('id');

        $withdrawal = $this->withdrawalModel->find($id);

        if (!$withdrawal || $withdrawal['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Solicitação não encontrada.');
        }

        if ($withdrawal['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Esta solicitação não pode mais ser cancelada.');
        }

        $this->withdrawalModel->update($id, ['status' => 'cancelled']);

        return redirect()->to('/dashboard/withdrawals')
            ->with('success', 'Solicitação cancelada com sucesso.');
    }

    // ========================================
    // MÉTODOS ADMINISTRATIVOS
    // ========================================

    /**
     * Lista saques para admin
     */
    public function adminIndex()
    {
        // Verificar se é admin
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $status = $this->request->getGet('status');

        $withdrawals = $this->withdrawalModel->getWithdrawalsForAdmin($status);
        $stats = $this->withdrawalModel->getWithdrawalStats();

        $data = [
            'title' => 'Gerenciar Saques',
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'currentStatus' => $status,
        ];

        return view('admin/withdrawals', $data);
    }

    /**
     * Detalhe de saque (admin)
     */
    public function adminDetail(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $withdrawal = $this->withdrawalModel
            ->select('withdrawals.*, users.name as user_name, users.email as user_email, users.cpf as user_cpf, campaigns.title as campaign_title')
            ->join('users', 'users.id = withdrawals.user_id')
            ->join('campaigns', 'campaigns.id = withdrawals.campaign_id', 'left')
            ->find($id);

        if (!$withdrawal) {
            return redirect()->to('/admin/withdrawals')->with('error', 'Saque não encontrado.');
        }

        $data = [
            'title' => 'Detalhe do Saque #' . $id,
            'withdrawal' => $withdrawal,
        ];

        return view('admin/withdrawal_detail', $data);
    }

    /**
     * Aprovar saque (admin)
     */
    public function approve(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $result = $this->withdrawalModel->processWithdrawal($id);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Saque processado com sucesso!');
        }

        return redirect()->back()->with('error', $result['error'] ?? 'Erro ao processar saque.');
    }

    /**
     * Rejeitar saque (admin)
     */
    public function reject(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $reason = $this->request->getPost('reason');

        $this->withdrawalModel->update($id, [
            'status' => 'failed',
            'admin_notes' => $reason,
        ]);

        return redirect()->back()->with('success', 'Saque rejeitado.');
    }
}

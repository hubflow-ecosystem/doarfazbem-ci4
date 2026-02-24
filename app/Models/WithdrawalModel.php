<?php

namespace App\Models;

use CodeIgniter\Model;

class WithdrawalModel extends Model
{
    protected $table = 'withdrawals';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'campaign_id',
        'amount',
        'fee_amount',
        'net_amount',
        'status',
        'payment_method',
        'pix_key',
        'pix_key_type',
        'bank_code',
        'bank_agency',
        'bank_account',
        'bank_account_type',
        'asaas_transfer_id',
        'processed_at',
        'notes',
        'admin_notes',
    ];

    /**
     * Busca saques de um usuário
     */
    public function getUserWithdrawals(int $userId, ?int $limit = null): array
    {
        $builder = $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Busca saques de uma campanha
     */
    public function getCampaignWithdrawals(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Calcula saldo disponível de um usuário
     */
    public function getAvailableBalance(int $userId): float
    {
        $donationModel = new DonationModel();
        $campaignModel = new CampaignModel();

        // Buscar todas as campanhas do usuário
        $campaigns = $campaignModel->where('user_id', $userId)->findAll();

        if (empty($campaigns)) {
            return 0;
        }

        $campaignIds = array_column($campaigns, 'id');

        // Total recebido em doações confirmadas
        $totalReceived = $donationModel
            ->selectSum('net_amount')
            ->whereIn('campaign_id', $campaignIds)
            ->whereIn('status', ['received', 'paid'])
            ->first();

        $totalReceived = (float) ($totalReceived['net_amount'] ?? 0);

        // Total já sacado (pendente ou processado)
        $totalWithdrawn = $this->selectSum('amount')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->first();

        $totalWithdrawn = (float) ($totalWithdrawn['amount'] ?? 0);

        return max(0, $totalReceived - $totalWithdrawn);
    }

    /**
     * Calcula saldo disponível por campanha
     */
    public function getCampaignBalance(int $campaignId): array
    {
        $donationModel = new DonationModel();

        // Total recebido
        $received = $donationModel
            ->selectSum('net_amount')
            ->where('campaign_id', $campaignId)
            ->whereIn('status', ['received', 'paid'])
            ->first();

        $totalReceived = (float) ($received['net_amount'] ?? 0);

        // Total sacado desta campanha
        $withdrawn = $this->selectSum('amount')
            ->where('campaign_id', $campaignId)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->first();

        $totalWithdrawn = (float) ($withdrawn['amount'] ?? 0);

        return [
            'total_received' => $totalReceived,
            'total_withdrawn' => $totalWithdrawn,
            'available' => max(0, $totalReceived - $totalWithdrawn),
        ];
    }

    /**
     * Cria solicitação de saque
     */
    public function createWithdrawal(array $data): int|false
    {
        // Calcular taxa (2.5% + R$2.00 por transferência)
        $feePercentage = 0.025; // 2.5%
        $feeFixed = 2.00; // R$2.00

        $amount = (float) $data['amount'];
        $feeAmount = ($amount * $feePercentage) + $feeFixed;
        $netAmount = $amount - $feeAmount;

        $data['fee_amount'] = round($feeAmount, 2);
        $data['net_amount'] = round($netAmount, 2);
        $data['status'] = 'pending';

        return $this->insert($data);
    }

    /**
     * Lista saques para admin
     */
    public function getWithdrawalsForAdmin(?string $status = null): array
    {
        $builder = $this->select('withdrawals.*, users.name as user_name, users.email as user_email, campaigns.title as campaign_title')
            ->join('users', 'users.id = withdrawals.user_id')
            ->join('campaigns', 'campaigns.id = withdrawals.campaign_id', 'left');

        if ($status) {
            $builder->where('withdrawals.status', $status);
        }

        return $builder->orderBy('withdrawals.created_at', 'DESC')->findAll();
    }

    /**
     * Processa saque (via Asaas)
     */
    public function processWithdrawal(int $withdrawalId): array
    {
        $withdrawal = $this->find($withdrawalId);

        if (!$withdrawal) {
            return ['success' => false, 'error' => 'Saque não encontrado'];
        }

        if ($withdrawal['status'] !== 'pending') {
            return ['success' => false, 'error' => 'Saque já processado'];
        }

        // Atualizar status para processando
        $this->update($withdrawalId, ['status' => 'processing']);

        // TODO: Integrar com Asaas para transferência real
        // Por enquanto, simular sucesso
        $this->update($withdrawalId, [
            'status' => 'completed',
            'processed_at' => date('Y-m-d H:i:s'),
            'asaas_transfer_id' => 'SIM_' . time(),
        ]);

        return ['success' => true];
    }

    /**
     * Estatísticas de saques para admin
     */
    public function getWithdrawalStats(): array
    {
        $db = \Config\Database::connect();

        // Pendentes
        $pending = $db->table('withdrawals')
            ->where('status', 'pending')
            ->selectCount('id', 'count')
            ->selectSum('amount', 'amount')
            ->get()->getRow();

        // Processando
        $processing = $db->table('withdrawals')
            ->where('status', 'processing')
            ->selectCount('id', 'count')
            ->selectSum('amount', 'amount')
            ->get()->getRow();

        // Concluídos este mês
        $completed = $db->table('withdrawals')
            ->where('status', 'completed')
            ->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))
            ->selectCount('id', 'count')
            ->selectSum('amount', 'amount')
            ->get()->getRow();

        // Total de taxas arrecadadas
        $fees = $db->table('withdrawals')
            ->where('status', 'completed')
            ->selectSum('fee_amount', 'total')
            ->get()->getRow();

        return [
            'pending_count' => (int) ($pending->count ?? 0),
            'pending_amount' => (float) ($pending->amount ?? 0),
            'processing_count' => (int) ($processing->count ?? 0),
            'processing_amount' => (float) ($processing->amount ?? 0),
            'completed_count' => (int) ($completed->count ?? 0),
            'completed_amount' => (float) ($completed->amount ?? 0),
            'total_fees' => (float) ($fees->total ?? 0),
        ];
    }
}

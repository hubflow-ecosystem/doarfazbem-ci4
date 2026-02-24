<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TransactionModel
 *
 * Model para gerenciar transações financeiras
 */
class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'donation_id',
        'type',
        'amount',
        'description',
        'status',
        'asaas_transfer_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Busca transações de um usuário
     */
    public function getUserTransactions($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Busca saldo disponível do usuário
     */
    public function getUserBalance($userId)
    {
        $result = $this->selectSum('amount')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->get()
            ->getRowArray();

        return $result['amount'] ?? 0;
    }

    /**
     * Registra recebimento de doação
     */
    public function recordDonationReceived($userId, $donationId, $amount, $description)
    {
        return $this->insert([
            'user_id' => $userId,
            'donation_id' => $donationId,
            'type' => 'donation',
            'amount' => $amount,
            'description' => $description,
            'status' => 'completed'
        ]);
    }

    /**
     * Registra saque
     */
    public function recordWithdrawal($userId, $amount, $asaasTransferId)
    {
        return $this->insert([
            'user_id' => $userId,
            'type' => 'withdrawal',
            'amount' => -$amount, // Negativo para saída
            'description' => 'Saque via Asaas',
            'status' => 'pending',
            'asaas_transfer_id' => $asaasTransferId
        ]);
    }

    /**
     * Registra taxa da plataforma
     */
    public function recordPlatformFee($userId, $donationId, $amount)
    {
        return $this->insert([
            'user_id' => $userId,
            'donation_id' => $donationId,
            'type' => 'fee',
            'amount' => -$amount, // Negativo
            'description' => 'Taxa da plataforma DoarFazBem',
            'status' => 'completed'
        ]);
    }

    /**
     * Registra reembolso
     */
    public function recordRefund($userId, $donationId, $amount)
    {
        return $this->insert([
            'user_id' => $userId,
            'donation_id' => $donationId,
            'type' => 'refund',
            'amount' => -$amount, // Negativo
            'description' => 'Reembolso de doação',
            'status' => 'completed'
        ]);
    }

    /**
     * Marca transação como concluída
     */
    public function markAsCompleted($transactionId)
    {
        return $this->update($transactionId, [
            'status' => 'completed'
        ]);
    }

    /**
     * Marca transação como falhada
     */
    public function markAsFailed($transactionId)
    {
        return $this->update($transactionId, [
            'status' => 'failed'
        ]);
    }
}

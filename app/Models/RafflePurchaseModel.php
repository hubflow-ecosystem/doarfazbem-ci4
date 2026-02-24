<?php

namespace App\Models;

use CodeIgniter\Model;

class RafflePurchaseModel extends Model
{
    protected $table = 'raffle_purchases';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'raffle_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'buyer_cpf',
        'quantity',
        'unit_price',
        'total_amount',
        'discount_applied',
        'payment_method',
        'payment_status',
        'payment_id',
        'pix_code',
        'pix_qrcode',
        'paid_at',
        'instant_prize_won',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Cria uma nova compra
     */
    public function createPurchase(array $data): int
    {
        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Busca compra por payment_id
     */
    public function findByPaymentId(string $paymentId): ?array
    {
        return $this->where('payment_id', $paymentId)->first();
    }

    /**
     * Atualiza status de pagamento
     */
    public function updatePaymentStatus(int $purchaseId, string $status, ?float $instantPrize = null): bool
    {
        $data = ['payment_status' => $status];

        if ($status === 'paid') {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }

        if ($instantPrize !== null) {
            $data['instant_prize_won'] = $instantPrize;
        }

        return $this->update($purchaseId, $data);
    }

    /**
     * Busca compras de um usuário
     */
    public function getUserPurchases(int $userId, ?int $raffleId = null): array
    {
        $builder = $this->where('user_id', $userId);

        if ($raffleId) {
            $builder->where('raffle_id', $raffleId);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Busca compras de uma rifa
     */
    public function getRafflePurchases(int $raffleId, string $status = null): array
    {
        $builder = $this->where('raffle_id', $raffleId);

        if ($status) {
            $builder->where('payment_status', $status);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Busca compras pendentes expiradas
     */
    public function getExpiredPurchases(): array
    {
        $expiryTime = date('Y-m-d H:i:s', strtotime('-30 minutes'));

        return $this->where('payment_status', 'pending')
            ->where('created_at <', $expiryTime)
            ->findAll();
    }

    /**
     * Cancela compras expiradas
     */
    public function cancelExpiredPurchases(): int
    {
        $expiryTime = date('Y-m-d H:i:s', strtotime('-30 minutes'));

        return $this->where('payment_status', 'pending')
            ->where('created_at <', $expiryTime)
            ->set(['payment_status' => 'expired'])
            ->update();
    }

    /**
     * Busca total gasto por usuário em uma rifa
     */
    public function getUserTotalInRaffle(int $raffleId, int $userId): array
    {
        $result = $this->selectSum('quantity', 'total_numbers')
            ->selectSum('total_amount', 'total_spent')
            ->where('raffle_id', $raffleId)
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->first();

        return [
            'total_numbers' => (int)($result['total_numbers'] ?? 0),
            'total_spent' => (float)($result['total_spent'] ?? 0),
        ];
    }

    /**
     * Busca compra com detalhes da rifa
     */
    public function getPurchaseWithRaffle(int $purchaseId): ?array
    {
        return $this->select('raffle_purchases.*, raffles.title as raffle_title, raffles.slug as raffle_slug')
            ->join('raffles', 'raffles.id = raffle_purchases.raffle_id')
            ->where('raffle_purchases.id', $purchaseId)
            ->first();
    }

    /**
     * Estatísticas de compras de uma rifa
     */
    public function getRaffleStats(int $raffleId): array
    {
        $stats = $this->selectCount('id', 'total_purchases')
            ->selectSum('quantity', 'total_numbers')
            ->selectSum('total_amount', 'total_revenue')
            ->selectSum('instant_prize_won', 'total_prizes_won')
            ->where('raffle_id', $raffleId)
            ->where('payment_status', 'paid')
            ->first();

        return [
            'total_purchases' => (int)($stats['total_purchases'] ?? 0),
            'total_numbers' => (int)($stats['total_numbers'] ?? 0),
            'total_revenue' => (float)($stats['total_revenue'] ?? 0),
            'total_prizes_won' => (float)($stats['total_prizes_won'] ?? 0),
        ];
    }
}

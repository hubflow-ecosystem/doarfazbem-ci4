<?php

namespace App\Models;

use CodeIgniter\Model;

class RaffleRankingModel extends Model
{
    protected $table = 'raffle_rankings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'raffle_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'total_numbers',
        'total_spent',
        'position',
        'prize_amount',
        'prize_paid',
        'prize_paid_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Prêmios do ranking (OBSOLETO - usar getRankingPrizes())
    const RANKING_PRIZES = [
        1 => 5000.00, // 1º lugar: R$ 5.000
        2 => 3000.00, // 2º lugar: R$ 3.000
        3 => 2000.00, // 3º lugar: R$ 2.000
    ];

    /**
     * Calcula prêmios do ranking baseado no prêmio principal da rifa
     * Total: 30% dos prêmios extras (que são 10% do principal)
     */
    public static function getRankingPrizes(float $mainPrize): array
    {
        $extraPrizes = $mainPrize * 0.10; // 10% do prêmio principal

        return [
            1 => $extraPrizes * 0.15, // 15% dos extras = 1.5% do principal
            2 => $extraPrizes * 0.10, // 10% dos extras = 1% do principal
            3 => $extraPrizes * 0.05, // 5% dos extras = 0.5% do principal
        ];
    }

    /**
     * Atualiza ou cria entrada no ranking
     */
    public function updateRanking(int $raffleId, int $userId, string $name, string $email, int $numbers, float $spent): bool
    {
        $existing = $this->where('raffle_id', $raffleId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'total_numbers' => $existing['total_numbers'] + $numbers,
                'total_spent' => $existing['total_spent'] + $spent,
            ]);
        }

        return (bool)$this->insert([
            'raffle_id' => $raffleId,
            'user_id' => $userId,
            'buyer_name' => $name,
            'buyer_email' => $email,
            'total_numbers' => $numbers,
            'total_spent' => $spent,
        ]);
    }

    /**
     * Busca ranking de uma rifa
     */
    public function getRanking(int $raffleId, int $limit = 10): array
    {
        return $this->where('raffle_id', $raffleId)
            ->orderBy('total_numbers', 'DESC')
            ->orderBy('total_spent', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Busca Top 3 de uma rifa
     */
    public function getTop3(int $raffleId): array
    {
        return $this->where('raffle_id', $raffleId)
            ->orderBy('total_numbers', 'DESC')
            ->orderBy('total_spent', 'DESC')
            ->limit(3)
            ->findAll();
    }

    /**
     * Busca posição do usuário no ranking
     */
    public function getUserPosition(int $raffleId, int $userId): ?int
    {
        $ranking = $this->where('raffle_id', $raffleId)
            ->orderBy('total_numbers', 'DESC')
            ->orderBy('total_spent', 'DESC')
            ->findAll();

        foreach ($ranking as $index => $entry) {
            if ($entry['user_id'] == $userId) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * Finaliza ranking e atribui prêmios
     */
    public function finalizeRanking(int $raffleId): array
    {
        $top3 = $this->getTop3($raffleId);
        $winners = [];

        // Buscar prêmio principal da rifa para calcular corretamente
        $raffleModel = new RaffleModel();
        $raffle = $raffleModel->find($raffleId);
        $mainPrize = !empty($raffle['main_prize_amount']) ? $raffle['main_prize_amount'] : 100000;
        $rankingPrizes = self::getRankingPrizes($mainPrize);

        foreach ($top3 as $index => $entry) {
            $position = $index + 1;
            $prize = $rankingPrizes[$position] ?? 0;

            $this->update($entry['id'], [
                'position' => $position,
                'prize_amount' => $prize,
            ]);

            $winners[] = [
                'position' => $position,
                'name' => $entry['buyer_name'],
                'email' => $entry['buyer_email'],
                'total_numbers' => $entry['total_numbers'],
                'prize' => $prize,
            ];
        }

        return $winners;
    }

    /**
     * Marca prêmio como pago
     */
    public function markPrizePaid(int $rankingId): bool
    {
        return $this->update($rankingId, [
            'prize_paid' => 1,
            'prize_paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Verifica se usuário está no Top 3
     */
    public function isInTop3(int $raffleId, int $userId): bool
    {
        $position = $this->getUserPosition($raffleId, $userId);
        return $position !== null && $position <= 3;
    }

    /**
     * Busca dados do usuário no ranking
     */
    public function getUserRankingData(int $raffleId, int $userId): ?array
    {
        $data = $this->where('raffle_id', $raffleId)
            ->where('user_id', $userId)
            ->first();

        if ($data) {
            $data['position'] = $this->getUserPosition($raffleId, $userId);
            $data['is_top3'] = $data['position'] <= 3;

            // Buscar prêmio principal da rifa para calcular corretamente
            $raffleModel = new RaffleModel();
            $raffle = $raffleModel->find($raffleId);
            $mainPrize = !empty($raffle['main_prize_amount']) ? $raffle['main_prize_amount'] : 100000;
            $rankingPrizes = self::getRankingPrizes($mainPrize);

            $data['potential_prize'] = $rankingPrizes[$data['position']] ?? 0;
        }

        return $data;
    }
}

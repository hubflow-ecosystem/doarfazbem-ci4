<?php

namespace App\Models;

use CodeIgniter\Model;

class RaffleWinnerModel extends Model
{
    protected $table = 'raffle_winners';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'raffle_id',
        'user_id',
        'winner_name',
        'winner_email',
        'winner_phone',
        'prize_type',
        'winning_number',
        'ranking_position',
        'total_numbers_bought',
        'prize_name',
        'prize_amount',
        'payment_status',
        'paid_at',
        'payment_gateway_id',
        'notification_sent',
        'notification_sent_at',
        'verification_code',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Tipos de prêmio
    const TYPE_MAIN = 'main';       // Prêmio principal (sorteio)
    const TYPE_RANKING = 'ranking'; // Top compradores
    const TYPE_SPECIAL = 'special'; // Cotas premiadas instantâneas

    // Status de pagamento
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';

    /**
     * Registra ganhador do prêmio principal
     */
    public function registerMainWinner(int $raffleId, array $buyerData, string $winningNumber, float $prizeAmount): int
    {
        $data = [
            'raffle_id' => $raffleId,
            'user_id' => $buyerData['user_id'] ?? null,
            'winner_name' => $buyerData['name'],
            'winner_email' => $buyerData['email'],
            'winner_phone' => $buyerData['phone'] ?? null,
            'prize_type' => self::TYPE_MAIN,
            'winning_number' => $winningNumber,
            'prize_name' => 'Prêmio Principal',
            'prize_amount' => $prizeAmount,
            'verification_code' => $this->generateVerificationCode(),
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Registra ganhadores do ranking (top compradores)
     */
    public function registerRankingWinners(int $raffleId, array $rankings): array
    {
        $winnersIds = [];
        $prizes = RaffleRankingModel::RANKING_PRIZES;

        foreach ($rankings as $index => $ranking) {
            $position = $index + 1;
            if (!isset($prizes[$position])) {
                continue;
            }

            $data = [
                'raffle_id' => $raffleId,
                'user_id' => $ranking['user_id'] ?? null,
                'winner_name' => $ranking['buyer_name'],
                'winner_email' => $ranking['buyer_email'],
                'prize_type' => self::TYPE_RANKING,
                'ranking_position' => $position,
                'total_numbers_bought' => $ranking['total_numbers'],
                'prize_name' => "{$position}º Lugar - Maior Comprador",
                'prize_amount' => $prizes[$position],
                'verification_code' => $this->generateVerificationCode(),
            ];

            $this->insert($data);
            $winnersIds[] = $this->getInsertID();
        }

        return $winnersIds;
    }

    /**
     * Registra ganhador de prêmio especial (cota premiada)
     */
    public function registerSpecialWinner(int $raffleId, array $buyerData, string $number, string $prizeName, float $prizeAmount): int
    {
        $data = [
            'raffle_id' => $raffleId,
            'user_id' => $buyerData['user_id'] ?? null,
            'winner_name' => $buyerData['name'],
            'winner_email' => $buyerData['email'],
            'winner_phone' => $buyerData['phone'] ?? null,
            'prize_type' => self::TYPE_SPECIAL,
            'winning_number' => $number,
            'prize_name' => $prizeName,
            'prize_amount' => $prizeAmount,
            'verification_code' => $this->generateVerificationCode(),
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Busca todos os ganhadores de uma rifa
     */
    public function getWinnersByRaffle(int $raffleId): array
    {
        return $this->where('raffle_id', $raffleId)
            ->orderBy('prize_type', 'ASC')
            ->orderBy('ranking_position', 'ASC')
            ->orderBy('prize_amount', 'DESC')
            ->findAll();
    }

    /**
     * Busca ganhadores por tipo
     */
    public function getWinnersByType(int $raffleId, string $type): array
    {
        return $this->where('raffle_id', $raffleId)
            ->where('prize_type', $type)
            ->orderBy('ranking_position', 'ASC')
            ->orderBy('prize_amount', 'DESC')
            ->findAll();
    }

    /**
     * Busca ganhador pelo código de verificação
     */
    public function getByVerificationCode(string $code): ?array
    {
        return $this->where('verification_code', $code)->first();
    }

    /**
     * Atualiza status de pagamento
     */
    public function updatePaymentStatus(int $winnerId, string $status, ?string $gatewayId = null): bool
    {
        $data = ['payment_status' => $status];

        if ($status === self::PAYMENT_PAID) {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }

        if ($gatewayId) {
            $data['payment_gateway_id'] = $gatewayId;
        }

        return $this->update($winnerId, $data);
    }

    /**
     * Marca notificação como enviada
     */
    public function markNotificationSent(int $winnerId): bool
    {
        return $this->update($winnerId, [
            'notification_sent' => 1,
            'notification_sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Estatísticas de ganhadores de uma rifa
     */
    public function getWinnerStats(int $raffleId): array
    {
        $winners = $this->getWinnersByRaffle($raffleId);

        $stats = [
            'total_winners' => count($winners),
            'total_prizes' => 0,
            'main_winner' => null,
            'ranking_winners' => [],
            'special_winners' => [],
            'paid_count' => 0,
            'pending_count' => 0,
        ];

        foreach ($winners as $winner) {
            $stats['total_prizes'] += $winner['prize_amount'];

            if ($winner['payment_status'] === self::PAYMENT_PAID) {
                $stats['paid_count']++;
            } else {
                $stats['pending_count']++;
            }

            switch ($winner['prize_type']) {
                case self::TYPE_MAIN:
                    $stats['main_winner'] = $winner;
                    break;
                case self::TYPE_RANKING:
                    $stats['ranking_winners'][] = $winner;
                    break;
                case self::TYPE_SPECIAL:
                    $stats['special_winners'][] = $winner;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Busca rifas com ganhadores (para histórico público)
     */
    public function getRafflesWithWinners(int $limit = 10, int $offset = 0): array
    {
        $db = \Config\Database::connect();

        return $db->table('raffles r')
            ->select('r.*, COUNT(rw.id) as total_winners, SUM(rw.prize_amount) as total_prizes_paid')
            ->join('raffle_winners rw', 'rw.raffle_id = r.id', 'left')
            ->where('r.status', 'finished')
            ->groupBy('r.id')
            ->orderBy('r.federal_lottery_date', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Gera código de verificação único
     */
    private function generateVerificationCode(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    /**
     * Mascara email para exibição pública
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***';
        }

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 2));
        $domainParts = explode('.', $domain);
        $maskedDomain = substr($domainParts[0], 0, 2) . '***.' . end($domainParts);

        return $maskedName . '@' . $maskedDomain;
    }

    /**
     * Mascara nome para exibição pública
     */
    public static function maskName(string $name): string
    {
        $words = explode(' ', $name);
        $result = [];

        foreach ($words as $word) {
            if (strlen($word) <= 2) {
                $result[] = $word;
            } else {
                $result[] = substr($word, 0, 2) . str_repeat('*', strlen($word) - 2);
            }
        }

        return implode(' ', $result);
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class RaffleNumberModel extends Model
{
    protected $table = 'raffle_numbers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'raffle_id',
        'number',
        'purchase_id',
        'user_id',
        'status',
        'is_special_prize',
        'special_prize_amount',
        'reserved_at',
        'reservation_expires_at',
        'paid_at',
    ];

    protected $useTimestamps = false;

    /**
     * Reserva números aleatórios para uma compra
     * Usa SELECT FOR UPDATE para prevenir race conditions
     */
    public function reserveRandomNumbers(int $raffleId, int $quantity, int $purchaseId, ?int $userId = null): array
    {
        $db = \Config\Database::connect();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // INICIAR TRANSACAO para garantir atomicidade
        $db->transStart();

        try {
            // SELECT FOR UPDATE - bloqueia as linhas selecionadas
            // Previne que duas requisicoes simultaneas reservem os mesmos numeros
            $sql = "SELECT id, number FROM {$this->table}
                    WHERE raffle_id = ? AND status = 'available'
                    ORDER BY RAND()
                    LIMIT ?
                    FOR UPDATE";

            $numbers = $db->query($sql, [$raffleId, $quantity])->getResultArray();

            if (count($numbers) < $quantity) {
                $db->transRollback();
                return []; // Não há números suficientes
            }

            $numberIds = array_column($numbers, 'id');

            // UPDATE atomico de todos os numeros de uma vez
            $db->table($this->table)
                ->whereIn('id', $numberIds)
                ->update([
                    'status' => 'reserved',
                    'purchase_id' => $purchaseId,
                    'user_id' => $userId,
                    'reserved_at' => date('Y-m-d H:i:s'),
                    'reservation_expires_at' => $expiresAt,
                ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', "Falha na transacao de reserva de numeros: raffle={$raffleId}, purchase={$purchaseId}");
                return [];
            }

            return array_column($numbers, 'number');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro ao reservar numeros: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Confirma números reservados como pagos
     */
    public function confirmPayment(int $purchaseId): array
    {
        $numbers = $this->where('purchase_id', $purchaseId)
            ->where('status', 'reserved')
            ->findAll();

        $confirmedNumbers = [];
        $instantPrize = 0;

        foreach ($numbers as $num) {
            $this->update($num['id'], [
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'reservation_expires_at' => null,
            ]);

            $confirmedNumbers[] = $num['number'];

            // Verifica se é cota premiada
            if ($num['is_special_prize'] && $num['special_prize_amount'] > 0) {
                $instantPrize += $num['special_prize_amount'];
            }
        }

        return [
            'numbers' => $confirmedNumbers,
            'instant_prize' => $instantPrize,
        ];
    }

    /**
     * Libera números de reservas expiradas
     */
    public function releaseExpiredReservations(): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('status', 'reserved')
            ->where('reservation_expires_at <', $now)
            ->set([
                'status' => 'available',
                'purchase_id' => null,
                'user_id' => null,
                'reserved_at' => null,
                'reservation_expires_at' => null,
            ])
            ->update();
    }

    /**
     * Busca números de um usuário em uma rifa
     */
    public function getUserNumbers(int $raffleId, int $userId): array
    {
        return $this->where('raffle_id', $raffleId)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->orderBy('number', 'ASC')
            ->findAll();
    }

    /**
     * Busca números de uma compra
     */
    public function getPurchaseNumbers(int $purchaseId): array
    {
        return $this->where('purchase_id', $purchaseId)
            ->whereIn('status', ['reserved', 'paid'])
            ->orderBy('number', 'ASC')
            ->findAll();
    }

    /**
     * Conta números disponíveis
     */
    public function countAvailable(int $raffleId): int
    {
        return $this->where('raffle_id', $raffleId)
            ->where('status', 'available')
            ->countAllResults();
    }

    /**
     * Inicializa números para uma rifa
     */
    public function initializeNumbers(int $raffleId, int $totalNumbers, array $specialPrizes = []): bool
    {
        $db = \Config\Database::connect();
        $digits = strlen((string)$totalNumbers);

        // Prepara números especiais
        $specialNumbers = [];
        foreach ($specialPrizes as $prize) {
            $specialNumbers[$prize['number_pattern']] = $prize['prize_amount'];
        }

        // Insere em lotes para performance
        $batchSize = 1000;
        $batch = [];

        for ($i = 0; $i < $totalNumbers; $i++) {
            $number = str_pad($i, $digits, '0', STR_PAD_LEFT);

            $isSpecial = isset($specialNumbers[$number]);
            $prizeAmount = $isSpecial ? $specialNumbers[$number] : null;

            $batch[] = [
                'raffle_id' => $raffleId,
                'number' => $number,
                'status' => 'available',
                'is_special_prize' => $isSpecial ? 1 : 0,
                'special_prize_amount' => $prizeAmount,
            ];

            if (count($batch) >= $batchSize) {
                $db->table($this->table)->insertBatch($batch);
                $batch = [];
            }
        }

        // Insere o restante
        if (!empty($batch)) {
            $db->table($this->table)->insertBatch($batch);
        }

        return true;
    }

    /**
     * Busca reservas prestes a expirar (para notificação)
     */
    public function getExpiringReservations(int $minutesBefore = 5): array
    {
        $targetTime = date('Y-m-d H:i:s', strtotime("+{$minutesBefore} minutes"));
        $now = date('Y-m-d H:i:s');

        return $this->select('raffle_numbers.*, raffle_purchases.buyer_email, raffle_purchases.buyer_name, raffle_purchases.buyer_phone')
            ->join('raffle_purchases', 'raffle_purchases.id = raffle_numbers.purchase_id')
            ->where('raffle_numbers.status', 'reserved')
            ->where('raffle_numbers.reservation_expires_at <=', $targetTime)
            ->where('raffle_numbers.reservation_expires_at >', $now)
            ->groupBy('raffle_numbers.purchase_id')
            ->findAll();
    }

    /**
     * Marca número como vencedor do sorteio principal
     */
    public function setWinner(int $raffleId, string $winningNumber): ?array
    {
        $number = $this->where('raffle_id', $raffleId)
            ->where('number', $winningNumber)
            ->where('status', 'paid')
            ->first();

        if ($number) {
            $this->update($number['id'], ['status' => 'winner']);
            return $number;
        }

        return null;
    }
}

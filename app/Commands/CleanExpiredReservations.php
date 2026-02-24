<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\RaffleNumberModel;
use App\Models\RafflePurchaseModel;

/**
 * Command para limpar reservas de rifas expiradas
 *
 * Uso:
 * php spark raffles:clean
 *
 * Configure no cron para rodar a cada 5 minutos:
 * Every 5 min: cd /path/to/project && php spark raffles:clean
 */
class CleanExpiredReservations extends BaseCommand
{
    protected $group = 'Raffles';
    protected $name = 'raffles:clean';
    protected $description = 'Limpa reservas de numeros expiradas (> 30 minutos sem pagamento)';

    public function run(array $params)
    {
        CLI::write('=== Limpeza de Reservas Expiradas ===', 'green');
        CLI::newLine();

        $numberModel = new RaffleNumberModel();
        $purchaseModel = new RafflePurchaseModel();

        // Tempo limite de reserva (30 minutos)
        $expirationTime = date('Y-m-d H:i:s', strtotime('-30 minutes'));

        // Buscar compras pendentes expiradas
        $expiredPurchases = $purchaseModel
            ->where('status', 'pending')
            ->where('created_at <', $expirationTime)
            ->findAll();

        if (empty($expiredPurchases)) {
            CLI::write('Nenhuma reserva expirada encontrada.', 'yellow');
            return;
        }

        CLI::write(count($expiredPurchases) . ' compras expiradas encontradas', 'cyan');
        CLI::newLine();

        $totalNumbers = 0;
        $totalPurchases = 0;

        foreach ($expiredPurchases as $purchase) {
            // Liberar numeros reservados
            $affected = $numberModel
                ->where('purchase_id', $purchase['id'])
                ->where('status', 'reserved')
                ->set([
                    'status' => 'available',
                    'user_id' => null,
                    'purchase_id' => null,
                    'reserved_at' => null
                ])
                ->update();

            // Marcar compra como expirada
            $purchaseModel->update($purchase['id'], [
                'status' => 'expired'
            ]);

            $numbersCount = $purchase['quantity'];
            $totalNumbers += $numbersCount;
            $totalPurchases++;

            CLI::write("Compra #{$purchase['id']}: {$numbersCount} numeros liberados", 'green');
        }

        CLI::newLine();
        CLI::write("=== Resumo ===", 'green');
        CLI::write("Compras expiradas: {$totalPurchases}", 'cyan');
        CLI::write("Numeros liberados: {$totalNumbers}", 'cyan');

        // Log
        log_message('info', "Raffles Clean: {$totalPurchases} compras expiradas, {$totalNumbers} numeros liberados");
    }
}

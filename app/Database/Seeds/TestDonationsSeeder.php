<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * TestDonationsSeeder
 *
 * Cria doacoes de teste para todas as campanhas
 */
class TestDonationsSeeder extends Seeder
{
    public function run()
    {
        echo "\n🧪 Criando doacoes de teste...\n\n";

        $db = \Config\Database::connect();

        // Buscar campanhas de teste ativas
        $campaigns = $db->table('campaigns')
            ->where('status', 'active')
            ->where('title LIKE', '%[TESTE]%')
            ->get()
            ->getResultArray();

        if (empty($campaigns)) {
            echo "⚠️  Nenhuma campanha de teste encontrada. Execute TestCampaignsSeeder primeiro.\n";
            return;
        }

        // Buscar usuarios de teste
        $users = $db->table('users')
            ->where('email LIKE', '%@test.doarfazbem.local')
            ->where('role', 'user')
            ->limit(10)
            ->get()
            ->getResultArray();

        if (empty($users)) {
            echo "⚠️  Nenhum usuario de teste encontrado. Execute TestUsersSeeder primeiro.\n";
            return;
        }

        $paymentMethods = ['pix', 'boleto', 'credit_card'];
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'pending', 'failed'];
        $donationCount = 0;

        foreach ($campaigns as $campaign) {
            // Gerar 3-8 doacoes por campanha
            $numDonations = rand(3, 8);

            for ($i = 0; $i < $numDonations; $i++) {
                $user = $users[array_rand($users)];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $status = $statuses[array_rand($statuses)];
                $amount = rand(10, 500) * 1.0;

                // Calcular taxas
                $gatewayFee = $this->calculateGatewayFee($paymentMethod, $amount);
                $platformFee = $this->calculatePlatformFee($campaign['category'], $amount);
                $netAmount = $amount - $gatewayFee - $platformFee;

                $donationData = [
                    'campaign_id' => $campaign['id'],
                    'user_id' => $user['id'],
                    'amount' => $amount,
                    'charged_amount' => $amount,
                    'net_amount' => $netAmount,
                    'payment_gateway_fee' => $gatewayFee,
                    'platform_fee' => $platformFee,
                    'donor_pays_fees' => 0,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'donor_name' => $user['name'],
                    'donor_email' => $user['email'],
                    'is_anonymous' => rand(0, 1),
                    'message' => rand(0, 1) ? 'Doacao de teste - ' . date('Y-m-d H:i:s') : null,
                    'asaas_payment_id' => 'pay_test_' . bin2hex(random_bytes(8)),
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $db->table('donations')->insert($donationData);
                $donationCount++;

                // Se confirmado, atualizar current_amount da campanha
                if ($status === 'confirmed') {
                    $db->table('campaigns')
                        ->where('id', $campaign['id'])
                        ->set('current_amount', 'current_amount + ' . $netAmount, false)
                        ->update();
                }
            }

            echo "✅ {$campaign['title']}: {$numDonations} doacoes criadas\n";
        }

        echo "\n✨ Total: {$donationCount} doacoes de teste criadas!\n\n";

        // Resumo por metodo de pagamento
        $byMethod = $db->table('donations')
            ->select('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->getResultArray();

        echo "📊 RESUMO POR METODO:\n";
        foreach ($byMethod as $method) {
            echo "   - {$method['payment_method']}: {$method['count']} doacoes\n";
        }

        // Resumo por status
        $byStatus = $db->table('donations')
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        echo "\n📊 RESUMO POR STATUS:\n";
        foreach ($byStatus as $s) {
            echo "   - {$s['status']}: {$s['count']} doacoes\n";
        }
    }

    /**
     * Calcula taxa do gateway baseado no metodo
     */
    private function calculateGatewayFee(string $method, float $amount): float
    {
        switch ($method) {
            case 'pix':
                return 0.95;
            case 'boleto':
                return 0.99;
            case 'credit_card':
                return 0.49 + ($amount * 0.0199);
            default:
                return 0.95;
        }
    }

    /**
     * Calcula taxa da plataforma baseado na categoria
     */
    private function calculatePlatformFee(string $category, float $amount): float
    {
        // Campanhas medicas = 0%, outras = 2%
        if ($category === 'medica' || $category === 'medical') {
            return 0.0;
        }
        return $amount * 0.02;
    }

    /**
     * Gera CPF valido para testes
     */
    private function generateCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = rand(0, 9);
        }

        // Calcula primeiro digito verificador
        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) $d1 = 0;

        // Calcula segundo digito verificador
        $d2 = $d1 * 2;
        for ($i = 0; $i < 9; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) $d2 = 0;

        return implode('', $n) . $d1 . $d2;
    }
}

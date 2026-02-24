<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RaffleTestSeeder extends Seeder
{
    public function run()
    {
        // Criar rifa de teste
        $raffleData = [
            'title' => 'Numeros da Sorte - Edicao Dezembro 2024',
            'slug' => 'numeros-da-sorte-dezembro-2024',
            'description' => 'Concorra a premios incriveis enquanto ajuda campanhas sociais! 40% do valor arrecadado vai para as campanhas que voce escolher.',
            'image' => null,
            'total_numbers' => 100000, // 100 mil para teste (ao inves de 1 milhao)
            'number_price' => 1.10,
            'main_prize_percentage' => 40.00,
            'campaign_percentage' => 40.00,
            'platform_percentage' => 60.00,
            'status' => 'active',
            'numbers_sold' => 0,
            'total_revenue' => 0.00,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('raffles')->insert($raffleData);
        $raffleId = $this->db->insertID();

        echo "Rifa criada com ID: {$raffleId}\n";

        // Criar pacotes de cotas
        $packages = [
            ['quantity' => 1,   'discount' => 0,    'popular' => 0, 'order' => 0],
            ['quantity' => 5,   'discount' => 0,    'popular' => 0, 'order' => 1],
            ['quantity' => 10,  'discount' => 5,    'popular' => 0, 'order' => 2],
            ['quantity' => 25,  'discount' => 10,   'popular' => 1, 'order' => 3],
            ['quantity' => 50,  'discount' => 15,   'popular' => 0, 'order' => 4],
            ['quantity' => 100, 'discount' => 20,   'popular' => 0, 'order' => 5],
            ['quantity' => 500, 'discount' => 30,   'popular' => 0, 'order' => 6],
        ];

        foreach ($packages as $pkg) {
            $originalPrice = $pkg['quantity'] * 1.10;
            $discountPrice = $originalPrice * (1 - $pkg['discount'] / 100);

            $this->db->table('raffle_packages')->insert([
                'raffle_id' => $raffleId,
                'quantity' => $pkg['quantity'],
                'original_price' => round($originalPrice, 2),
                'discount_price' => round($discountPrice, 2),
                'discount_percentage' => $pkg['discount'],
                'is_popular' => $pkg['popular'],
                'sort_order' => $pkg['order'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "Pacotes criados!\n";

        // Criar premios especiais (cotas premiadas)
        $specialPrizes = [
            // Sequencias repetidas (R$ 1.000 cada)
            ['pattern' => '111111', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '222222', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '333333', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '444444', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '555555', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '666666', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '777777', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '888888', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '999999', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],
            ['pattern' => '000000', 'name' => 'Sequencia Perfeita', 'amount' => 1000.00],

            // Sequencias especiais
            ['pattern' => '123456', 'name' => 'Sequencia Crescente', 'amount' => 500.00],
            ['pattern' => '654321', 'name' => 'Sequencia Decrescente', 'amount' => 500.00],

            // Numeros redondos (R$ 100 cada) - para 100k
            ['pattern' => '010000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '020000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '030000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '040000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '050000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '060000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '070000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '080000', 'name' => 'Numero Redondo', 'amount' => 100.00],
            ['pattern' => '090000', 'name' => 'Numero Redondo', 'amount' => 100.00],
        ];

        foreach ($specialPrizes as $prize) {
            $this->db->table('raffle_special_prizes')->insert([
                'raffle_id' => $raffleId,
                'number_pattern' => $prize['pattern'],
                'prize_name' => $prize['name'],
                'prize_amount' => $prize['amount'],
                'buyer_percentage' => 50.00,
                'campaign_percentage' => 40.00,
                'platform_percentage' => 10.00,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "Premios especiais criados!\n";

        // Gerar numeros da rifa (100k)
        echo "Gerando 100.000 numeros (isso pode demorar um pouco)...\n";

        // Buscar premios especiais para marcar
        $specialNumbers = [];
        foreach ($specialPrizes as $prize) {
            $specialNumbers[$prize['pattern']] = $prize['amount'];
        }

        // Inserir em lotes
        $batchSize = 5000;
        $batch = [];
        $totalNumbers = 100000;

        for ($i = 0; $i < $totalNumbers; $i++) {
            $number = str_pad($i, 6, '0', STR_PAD_LEFT);

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
                $this->db->table('raffle_numbers')->insertBatch($batch);
                $batch = [];
                echo "  Inseridos " . ($i + 1) . " numeros...\n";
            }
        }

        // Inserir o restante
        if (!empty($batch)) {
            $this->db->table('raffle_numbers')->insertBatch($batch);
        }

        echo "\n=== RIFA DE TESTE CRIADA COM SUCESSO! ===\n";
        echo "ID: {$raffleId}\n";
        echo "Total de numeros: {$totalNumbers}\n";
        echo "Preco por cota: R$ 1,10\n";
        echo "Status: ATIVA\n";
        echo "\nAcesse: /rifas/{$raffleData['slug']}\n";
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class RafflePackageModel extends Model
{
    protected $table = 'raffle_packages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'raffle_id',
        'name',
        'quantity',
        'original_price',
        'discount_price',
        'discount_percentage',
        'is_popular',
        'sort_order',
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';

    /**
     * Busca pacotes de uma rifa
     */
    public function getPackagesByRaffle(int $raffleId): array
    {
        return $this->where('raffle_id', $raffleId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('quantity', 'ASC')
            ->findAll();
    }

    /**
     * Cria pacotes padrão para uma rifa
     */
    public function createDefaultPackages(int $raffleId, float $unitPrice = 1.10): bool
    {
        $packages = [
            ['quantity' => 1,   'discount' => 0,    'popular' => false],
            ['quantity' => 5,   'discount' => 0,    'popular' => false],
            ['quantity' => 10,  'discount' => 5,    'popular' => false],
            ['quantity' => 25,  'discount' => 10,   'popular' => true],  // Popular
            ['quantity' => 50,  'discount' => 15,   'popular' => false],
            ['quantity' => 100, 'discount' => 20,   'popular' => false],
            ['quantity' => 500, 'discount' => 30,   'popular' => false],
        ];

        foreach ($packages as $index => $pkg) {
            $originalPrice = $pkg['quantity'] * $unitPrice;
            $discountPrice = $originalPrice * (1 - $pkg['discount'] / 100);

            $this->insert([
                'raffle_id' => $raffleId,
                'quantity' => $pkg['quantity'],
                'original_price' => $originalPrice,
                'discount_price' => round($discountPrice, 2),
                'discount_percentage' => $pkg['discount'],
                'is_popular' => $pkg['popular'] ? 1 : 0,
                'sort_order' => $index,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    /**
     * Calcula preço para quantidade específica
     */
    public function calculatePrice(int $raffleId, int $quantity): array
    {
        // Busca pacote exato ou calcula baseado no melhor desconto aplicável
        $packages = $this->getPackagesByRaffle($raffleId);

        // Primeiro, verifica se existe pacote exato
        foreach ($packages as $pkg) {
            if ($pkg['quantity'] == $quantity) {
                return [
                    'quantity' => $quantity,
                    'unit_price' => $pkg['discount_price'] / $pkg['quantity'],
                    'total_price' => $pkg['discount_price'],
                    'discount_percentage' => $pkg['discount_percentage'],
                    'original_price' => $pkg['original_price'],
                    'savings' => $pkg['original_price'] - $pkg['discount_price'],
                ];
            }
        }

        // Se não houver pacote exato, usa o melhor desconto aplicável
        $bestDiscount = 0;
        $basePrice = 1.10; // Preço padrão

        if (!empty($packages)) {
            $basePrice = $packages[0]['original_price'] / $packages[0]['quantity'];

            foreach ($packages as $pkg) {
                if ($pkg['quantity'] <= $quantity && $pkg['discount_percentage'] > $bestDiscount) {
                    $bestDiscount = $pkg['discount_percentage'];
                }
            }
        }

        $originalPrice = $quantity * $basePrice;
        $discountPrice = $originalPrice * (1 - $bestDiscount / 100);

        return [
            'quantity' => $quantity,
            'unit_price' => $discountPrice / $quantity,
            'total_price' => round($discountPrice, 2),
            'discount_percentage' => $bestDiscount,
            'original_price' => round($originalPrice, 2),
            'savings' => round($originalPrice - $discountPrice, 2),
        ];
    }
}

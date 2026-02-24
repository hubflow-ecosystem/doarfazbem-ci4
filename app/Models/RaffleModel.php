<?php

namespace App\Models;

use CodeIgniter\Model;

class RaffleModel extends Model
{
    protected $table = 'raffles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'title',
        'slug',
        'description',
        'image',
        'total_numbers',
        'number_price',
        'main_prize_percentage',
        'campaign_percentage',
        'platform_percentage',
        'federal_lottery_date',
        'federal_lottery_result',
        'winning_number',
        'status',
        'numbers_sold',
        'total_revenue',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'total_numbers' => 'required|integer|greater_than[0]',
        'number_price' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Busca rifa ativa atual
     */
    public function getActiveRaffle()
    {
        return $this->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Busca rifa por slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Calcula estatísticas da rifa
     */
    public function getStats(int $raffleId): array
    {
        $raffle = $this->find($raffleId);
        if (!$raffle) {
            return [];
        }

        $percentage = $raffle['total_numbers'] > 0
            ? round(($raffle['numbers_sold'] / $raffle['total_numbers']) * 100, 2)
            : 0;

        // ========== DISTRIBUIÇÃO COMPLETA DA RECEITA ==========

        // 1. TAXAS E RECEITA LÍQUIDA
        // Taxa do gateway sobre TODA a receita (Mercado Pago ~1%)
        $gatewayFeeTotal = $raffle['total_revenue'] * 0.01;
        $revenueLiquid = $raffle['total_revenue'] - $gatewayFeeTotal;

        // 2. DISTRIBUIÇÃO DA RECEITA LÍQUIDA BASEADA NOS PERCENTUAIS
        $mainPrizePool = $revenueLiquid * ($raffle['main_prize_percentage'] / 100); // 50% = área de prêmios
        $campaignsDirect = $revenueLiquid * ($raffle['campaign_percentage'] / 100); // 40%
        $forPlatform = $revenueLiquid * ($raffle['platform_percentage'] / 100);     // 10%

        // 3. PRÊMIOS (valores que serão pagos)
        $db = \Config\Database::connect();

        // Prêmio do ganhador principal = 50% da área de prêmios
        $winnerPrize = $mainPrizePool * 0.5;

        // Top 3 compradores (fixo)
        $topBuyersPrizes = 1500 + 1000 + 500; // R$ 3.000

        // Cotas premiadas (soma dos prêmios especiais cadastrados)
        $specialPrizesTotal = $db->table('raffle_special_prizes')
            ->where('raffle_id', $raffleId)
            ->selectSum('prize_amount')
            ->get()
            ->getRow()->prize_amount ?? 0;

        $totalExtraPrizes = $topBuyersPrizes + $specialPrizesTotal;
        $totalPrizesCommitted = $winnerPrize + $totalExtraPrizes;

        // Sobra da área de prêmios (retorna para campanhas)
        $prizePoolRemainder = $mainPrizePool - $totalPrizesCommitted;
        $forCampaignsTotal = $campaignsDirect + $prizePoolRemainder;

        // 4. ESTIMATIVAS (receita total quando todas as cotas forem vendidas)
        $estimatedRevenue = $raffle['total_numbers'] * $raffle['number_price'];
        $estimatedGatewayFee = $estimatedRevenue * 0.01;
        $estimatedLiquid = $estimatedRevenue - $estimatedGatewayFee;
        $estimatedPrizePool = $estimatedLiquid * ($raffle['main_prize_percentage'] / 100);
        $estimatedWinnerPrize = $estimatedPrizePool * 0.5;

        return [
            'total_numbers' => $raffle['total_numbers'],
            'numbers_sold' => $raffle['numbers_sold'],
            'numbers_available' => $raffle['total_numbers'] - $raffle['numbers_sold'],
            'percentage_sold' => $percentage,

            // Receita atual
            'total_revenue' => $raffle['total_revenue'],
            'gateway_fee' => $gatewayFeeTotal,
            'revenue_liquid' => $revenueLiquid,

            // Área de Prêmios
            'main_prize_pool' => $mainPrizePool,  // Total alocado para prêmios (50%)
            'winner_prize' => $winnerPrize,        // Ganhador principal (50% do pool)
            'top_buyers_prizes' => $topBuyersPrizes,
            'special_prizes' => $specialPrizesTotal,
            'total_extra_prizes' => $totalExtraPrizes,
            'total_prizes_committed' => $totalPrizesCommitted,  // Total que será pago
            'prize_pool_remainder' => $prizePoolRemainder,      // Sobra (volta p/ campanhas)

            // Distribuição líquida
            'campaigns_direct' => $campaignsDirect,         // 40% direto
            'for_campaigns' => $forCampaignsTotal,          // 40% + sobra dos prêmios
            'for_platform' => $forPlatform,                 // 10%

            // Estimativas (receita total esperada quando vender tudo)
            'estimated_revenue' => $estimatedRevenue,
            'estimated_gateway_fee' => $estimatedGatewayFee,
            'estimated_liquid' => $estimatedLiquid,
            'estimated_prize_pool' => $estimatedPrizePool,
            'estimated_winner_prize' => $estimatedWinnerPrize,

            // Compatibilidade (campos antigos)
            'main_prize' => $winnerPrize,  // Alias para winner_prize
            'total_prizes' => $totalPrizesCommitted,
        ];
    }

    /**
     * Atualiza estatísticas após compra
     * Usa incremento atomico para prevenir race conditions
     */
    public function updateStatsAfterPurchase(int $raffleId, int $quantity, float $amount): bool
    {
        $db = \Config\Database::connect();

        // Incremento atomico - previne race condition
        // SET field = field + X ao inves de ler, somar, salvar
        return $db->table($this->table)
            ->where('id', $raffleId)
            ->set('numbers_sold', 'numbers_sold + ' . (int) $quantity, false)
            ->set('total_revenue', 'total_revenue + ' . (float) $amount, false)
            ->update();
    }

    /**
     * Verifica se rifa está esgotada
     */
    public function checkSoldOut(int $raffleId): bool
    {
        $raffle = $this->find($raffleId);
        if (!$raffle) {
            return false;
        }

        if ($raffle['numbers_sold'] >= $raffle['total_numbers']) {
            $this->update($raffleId, ['status' => 'sold_out']);
            return true;
        }

        return false;
    }

    /**
     * Calcula prêmio principal atual
     */
    public function getCurrentMainPrize(int $raffleId): float
    {
        $raffle = $this->find($raffleId);
        if (!$raffle) {
            return 0;
        }

        return $raffle['total_revenue'] * ($raffle['main_prize_percentage'] / 100);
    }

    /**
     * Gera slug único
     */
    public function generateUniqueSlug(string $title): string
    {
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->where('slug', $slug)->first()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Busca rifas para exibição pública
     */
    public function getPublicRaffles(int $limit = 10): array
    {
        return $this->whereIn('status', ['active', 'sold_out'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Gera pacotes automaticamente para uma rifa
     * Calcula preços e quantidades para atingir a arrecadação alvo
     *
     * @param int $raffleId ID da rifa
     * @param float $targetRevenue Arrecadação alvo (ex: 110000)
     * @param float $maxMargin Margem máxima permitida (ex: 0.01 para 1%)
     * @return bool
     */
    public function generatePackages(int $raffleId, float $targetRevenue, float $maxMargin = 0.01): bool
    {
        $raffle = $this->find($raffleId);
        if (!$raffle) {
            return false;
        }

        $totalNumbers = $raffle['total_numbers'];

        // Configuração padrão dos pacotes (quantidade, desconto%, % das vendas estimado)
        $packageConfig = [
            ['qty' => 1,   'discount' => 0,  'salesPct' => 0.10],
            ['qty' => 5,   'discount' => 3,  'salesPct' => 0.15],
            ['qty' => 10,  'discount' => 5,  'salesPct' => 0.20],
            ['qty' => 25,  'discount' => 10, 'salesPct' => 0.30],
            ['qty' => 50,  'discount' => 15, 'salesPct' => 0.15],
            ['qty' => 100, 'discount' => 20, 'salesPct' => 0.10],
        ];

        // Calcular média ponderada de desconto
        $weightedDiscount = 0;
        foreach ($packageConfig as $pkg) {
            $effectiveRate = 1 - ($pkg['discount'] / 100);
            $weightedDiscount += $effectiveRate * $pkg['salesPct'];
        }

        // Calcular preço base necessário
        // targetRevenue = totalNumbers * basePrice * weightedDiscount
        $basePrice = $targetRevenue / ($totalNumbers * $weightedDiscount);

        // Arredondar para 2 casas decimais (para cima para garantir margem)
        $basePrice = ceil($basePrice * 100) / 100;

        // Atualizar preço na rifa
        $this->update($raffleId, ['number_price' => $basePrice]);

        // Deletar pacotes existentes
        $db = \Config\Database::connect();
        $db->table('raffle_packages')->where('raffle_id', $raffleId)->delete();

        // Criar novos pacotes
        foreach ($packageConfig as $index => $pkg) {
            $originalPrice = $pkg['qty'] * $basePrice;
            $discountPrice = round($originalPrice * (1 - $pkg['discount'] / 100), 2);
            $allocatedNumbers = (int) ($totalNumbers * $pkg['salesPct']);
            $maxAvailable = (int) floor($allocatedNumbers / $pkg['qty']);

            $db->table('raffle_packages')->insert([
                'raffle_id' => $raffleId,
                'quantity' => $pkg['qty'],
                'original_price' => $originalPrice,
                'discount_price' => $discountPrice,
                'discount_percentage' => $pkg['discount'],
                'max_available' => $maxAvailable,
                'sold_count' => 0,
                'is_popular' => ($pkg['qty'] == 25) ? 1 : 0, // 25 cotas é o popular
                'sort_order' => $index,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Calcular arrecadação estimada para verificar margem
        $estimatedRevenue = 0;
        foreach ($packageConfig as $pkg) {
            $discountPrice = round($pkg['qty'] * $basePrice * (1 - $pkg['discount'] / 100), 2);
            $allocatedNumbers = (int) ($totalNumbers * $pkg['salesPct']);
            $packagesCount = (int) floor($allocatedNumbers / $pkg['qty']);
            $estimatedRevenue += $packagesCount * $discountPrice;
        }

        $margin = ($estimatedRevenue - $targetRevenue) / $targetRevenue;

        log_message('info', "Rifa #{$raffleId}: Preço base R$ {$basePrice}, Arrecadação estimada R$ {$estimatedRevenue}, Margem: " . round($margin * 100, 2) . "%");

        return true;
    }

    /**
     * Retorna os pacotes de uma rifa com disponibilidade
     */
    public function getPackages(int $raffleId): array
    {
        $db = \Config\Database::connect();
        return $db->table('raffle_packages')
            ->where('raffle_id', $raffleId)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Verifica disponibilidade de um pacote
     */
    public function checkPackageAvailability(int $packageId, int $quantity = 1): bool
    {
        $db = \Config\Database::connect();
        $package = $db->table('raffle_packages')->where('id', $packageId)->get()->getRowArray();

        if (!$package) {
            return false;
        }

        // Se max_available é NULL, não há limite
        if ($package['max_available'] === null) {
            return true;
        }

        return ($package['sold_count'] + $quantity) <= $package['max_available'];
    }

    /**
     * Incrementa contador de vendas do pacote
     */
    public function incrementPackageSold(int $packageId, int $quantity = 1): bool
    {
        $db = \Config\Database::connect();
        return $db->table('raffle_packages')
            ->where('id', $packageId)
            ->set('sold_count', 'sold_count + ' . $quantity, false)
            ->update();
    }
}

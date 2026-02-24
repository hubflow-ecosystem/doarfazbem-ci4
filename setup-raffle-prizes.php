<?php
/**
 * Configura os prêmios extras da rifa atual
 *
 * Estrutura:
 * - Total prêmios extras: 10% do prêmio principal
 * - Top Compradores (30%): 1º=15%, 2º=10%, 3º=5%
 * - Cotas Premiadas (70%): 10 números especiais com valores iguais
 */

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

if ($mysqli->connect_error) {
    die("Erro de conexão: " . $mysqli->connect_error);
}

// Buscar rifa ativa
$result = $mysqli->query("SELECT id, main_prize_amount, total_numbers FROM raffles WHERE status = 'active' LIMIT 1");
$raffle = $result->fetch_assoc();

if (!$raffle) {
    die("Nenhuma rifa ativa encontrada!\n");
}

$raffleId = $raffle['id'];
$mainPrize = $raffle['main_prize_amount'];
$totalNumbers = $raffle['total_numbers'];

echo "=== CONFIGURANDO PRÊMIOS EXTRAS ===\n";
echo "Rifa ID: {$raffleId}\n";
echo "Prêmio Principal: R$ " . number_format($mainPrize, 2, ',', '.') . "\n";
echo "Total de Números: " . number_format($totalNumbers, 0, '.', '.') . "\n\n";

// Calcular total de prêmios extras (10% do principal)
$totalExtraPrizes = $mainPrize * 0.10;
echo "Total Prêmios Extras (10%): R$ " . number_format($totalExtraPrizes, 2, ',', '.') . "\n\n";

// Valores Top Compradores (30% do total extras)
$top1 = $totalExtraPrizes * 0.15; // 15%
$top2 = $totalExtraPrizes * 0.10; // 10%
$top3 = $totalExtraPrizes * 0.05; // 5%

echo "TOP COMPRADORES (30%):\n";
echo "1º lugar: R$ " . number_format($top1, 2, ',', '.') . "\n";
echo "2º lugar: R$ " . number_format($top2, 2, ',', '.') . "\n";
echo "3º lugar: R$ " . number_format($top3, 2, ',', '.') . "\n";
echo "Subtotal: R$ " . number_format($top1 + $top2 + $top3, 2, ',', '.') . "\n\n";

// Determinar formato dos números baseado no total_numbers
$digits = strlen((string)($totalNumbers - 1));
$numberFormat = str_repeat('0', $digits);

echo "Formato dos números: {$digits} dígitos\n\n";

// Cotas Premiadas (70% do total extras / 10 cotas)
$totalCotas = $totalExtraPrizes * 0.70;
$valuePerCota = $totalCotas / 10;

echo "COTAS PREMIADAS (70%):\n";
echo "Total para cotas: R$ " . number_format($totalCotas, 2, ',', '.') . "\n";
echo "Valor por cota: R$ " . number_format($valuePerCota, 2, ',', '.') . "\n\n";

// Limpar prêmios existentes
$mysqli->query("DELETE FROM raffle_special_prizes WHERE raffle_id = {$raffleId}");
echo "✓ Prêmios anteriores removidos\n\n";

// Inserir as 10 cotas premiadas
$patterns = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
$inserted = 0;

echo "Inserindo cotas premiadas:\n";
foreach ($patterns as $pattern) {
    $numberPattern = str_repeat($pattern, $digits);
    $prizeName = "Número {$numberPattern}";

    $stmt = $mysqli->prepare("
        INSERT INTO raffle_special_prizes
        (raffle_id, number_pattern, prize_name, prize_amount, buyer_percentage, campaign_percentage, platform_percentage, is_active, created_at)
        VALUES (?, ?, ?, ?, 100, 0, 0, 1, NOW())
    ");

    $stmt->bind_param("issd", $raffleId, $numberPattern, $prizeName, $valuePerCota);

    if ($stmt->execute()) {
        echo "  ✓ {$numberPattern}: R$ " . number_format($valuePerCota, 2, ',', '.') . "\n";
        $inserted++;
    } else {
        echo "  ✗ Erro ao inserir {$numberPattern}: " . $stmt->error . "\n";
    }

    $stmt->close();
}

echo "\n=== RESUMO FINAL ===\n";
echo "Top Compradores: R$ " . number_format($top1 + $top2 + $top3, 2, ',', '.') . " (30%)\n";
echo "Cotas Premiadas: R$ " . number_format($totalCotas, 2, ',', '.') . " (70%)\n";
echo "TOTAL: R$ " . number_format($totalExtraPrizes, 2, ',', '.') . " (10% do prêmio principal)\n";
echo "\n✓ {$inserted} cotas premiadas inseridas com sucesso!\n";

$mysqli->close();

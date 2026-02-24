<?php
/**
 * Teste final de split payment e arredondamento
 */

echo "=== TESTE SPLIT PAYMENT FINAL ===\n\n";

function testSplit($amount, $category, $donateToPlatform, $donorPaysFees, $paymentMethod) {
    echo str_repeat("-", 70) . "\n";
    echo "CENÁRIO:\n";
    echo "  Valor base: R$ " . number_format($amount, 2) . "\n";
    echo "  Categoria: $category\n";
    echo "  Doar para plataforma: " . ($donateToPlatform ? 'SIM' : 'NÃO') . "\n";
    echo "  Doador paga taxas: " . ($donorPaysFees ? 'SIM' : 'NÃO') . "\n";
    echo "  Método: $paymentMethod\n\n";

    // Calcular platform fee (checkbox)
    $platformFee = $donateToPlatform ? max(1.00, $amount * 0.01) : 0;

    // Calcular gateway fee
    $gatewayFee = 0;
    if ($donorPaysFees) {
        if ($paymentMethod === 'pix') $gatewayFee = 0.95;
        elseif ($paymentMethod === 'boleto') $gatewayFee = 0.99;
        elseif ($paymentMethod === 'credit_card') $gatewayFee = 0.49 + ($amount * 0.0199);
    }

    // Calcular charged amount
    $chargedAmount = $donorPaysFees ? ceil($amount + $gatewayFee + $platformFee) : $amount;

    echo "CÁLCULOS:\n";
    echo "  platform_fee (checkbox): R$ " . number_format($platformFee, 2) . "\n";
    echo "  gateway_fee: R$ " . number_format($gatewayFee, 2) . "\n";
    echo "  charged_amount (enviado ao Asaas): R$ " . number_format($chargedAmount, 2) . "\n\n";

    // Calcular split
    if ($category === 'medica') {
        $creatorAmount = floor($chargedAmount);
        $platformSplit = $chargedAmount - $creatorAmount;
    } else {
        $automaticPlatformFee = max(1.00, $chargedAmount * 0.01);
        $creatorAmount = floor($chargedAmount - $automaticPlatformFee);
        $platformSplit = $chargedAmount - $creatorAmount;
    }

    echo "SPLIT PAYMENT (Asaas):\n";
    echo "  Criador recebe: R$ " . number_format($creatorAmount, 2) . "\n";
    echo "  Plataforma fica com: R$ " . number_format($platformSplit, 2) . "\n\n";

    $totalPlataforma = $platformFee + $platformSplit;
    echo "TOTAL PLATAFORMA: R$ " . number_format($totalPlataforma, 2) . "\n";
    echo "  - Platform fee (checkbox): R$ " . number_format($platformFee, 2) . "\n";
    echo "  - Split automático + arredondamento: R$ " . number_format($platformSplit, 2) . "\n\n";
}

// Teste 1: Campanha médica COM checkbox de plataforma
testSplit(33.46, 'medica', true, true, 'credit_card');

// Teste 2: Campanha médica SEM checkbox de plataforma
testSplit(33.46, 'medica', false, true, 'credit_card');

// Teste 3: Campanha não-médica COM checkbox
testSplit(33.46, 'social', true, true, 'credit_card');

// Teste 4: Campanha não-médica SEM checkbox
testSplit(33.46, 'social', false, true, 'credit_card');

// Teste 5: PIX campanha não-médica
testSplit(32.58, 'social', false, true, 'pix');

echo "=== FIM DOS TESTES ===\n";

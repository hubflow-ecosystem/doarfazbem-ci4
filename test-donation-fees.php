<?php
/**
 * Teste de cálculo de taxas nas doações
 */

echo "=== TESTE DE CÁLCULO DE TAXAS ===\n\n";

// Cenário 1: Doação R$ 33,46 com cartão, doador paga taxas, COM plataforma
echo "CENÁRIO 1: R$ 33,46 + Cartão + Doador paga taxas + Plataforma\n";
$amount = 33.46;
$platformFee = max(1.00, $amount * 0.01);
$gatewayFee = 0.49 + ($amount * 0.0199);
$chargedAmount = ceil($amount + $gatewayFee + $platformFee);
$netAmount = $amount;

echo "  amount: R$ " . number_format($amount, 2) . "\n";
echo "  platform_fee: R$ " . number_format($platformFee, 2) . "\n";
echo "  gateway_fee: R$ " . number_format($gatewayFee, 2) . "\n";
echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";
echo "  net_amount: R$ " . number_format($netAmount, 2) . "\n";

// Split
$platformAmount = $platformFee;
$creatorAmount = $chargedAmount - $platformAmount;
echo "  SPLIT - Plataforma: R$ " . number_format($platformAmount, 2) . "\n";
echo "  SPLIT - Criador: R$ " . number_format($creatorAmount, 2) . "\n";

// Cenário 2: Doação R$ 33,46 com cartão, doador paga taxas, SEM plataforma
echo "\nCENÁRIO 2: R$ 33,46 + Cartão + Doador paga taxas + SEM Plataforma\n";
$amount = 33.46;
$platformFee = 0;
$gatewayFee = 0.49 + ($amount * 0.0199);
$chargedAmount = ceil($amount + $gatewayFee + $platformFee);
$netAmount = $amount;

echo "  amount: R$ " . number_format($amount, 2) . "\n";
echo "  platform_fee: R$ " . number_format($platformFee, 2) . "\n";
echo "  gateway_fee: R$ " . number_format($gatewayFee, 2) . "\n";
echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";
echo "  net_amount: R$ " . number_format($netAmount, 2) . "\n";

// Split
$platformAmount = $platformFee;
$creatorAmount = $chargedAmount - $platformAmount;
echo "  SPLIT - Plataforma: R$ " . number_format($platformAmount, 2) . "\n";
echo "  SPLIT - Criador: R$ " . number_format($creatorAmount, 2) . "\n";

echo "\n=== VALOR QUE DEVE SER ENVIADO AO ASAAS ===\n";
echo "  Cenário 1: R$ 36,00 (charged_amount)\n";
echo "  Cenário 2: R$ 35,00 (charged_amount)\n";

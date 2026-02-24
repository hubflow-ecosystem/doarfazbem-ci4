<?php
/**
 * Teste de arredondamento - Plataforma fica com centavos
 */

echo "=== TESTE DE ARREDONDAMENTO ===\n\n";

// Exemplo 1: Social, R$ 33,46 + cartão + com plataforma
echo "EXEMPLO 1 - Social, R$ 33,46, com plataforma:\n";
$amount = 33.46;
$platformFee = 1.00;
$gatewayFee = 1.16;
$chargedAmount = ceil($amount + $gatewayFee + $platformFee); // 36.00

echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";

// Split para campanha não-médica
$automaticPlatformFee = max(1.00, $chargedAmount * 0.01); // 1.00
$exactCreatorAmount = $chargedAmount - $automaticPlatformFee; // 36 - 1 = 35.00
$creatorAmount = floor($exactCreatorAmount); // 35.00

$platformSplitTotal = $chargedAmount - $creatorAmount; // 36 - 35 = 1.00

echo "  Criador recebe (split): R$ " . number_format($creatorAmount, 2) . "\n";
echo "  Plataforma fica (split): R$ " . number_format($platformSplitTotal, 2) . "\n";
echo "    - Automático (1%): R$ " . number_format($automaticPlatformFee, 2) . "\n";
echo "    - Arredondamento: R$ " . number_format($platformSplitTotal - $automaticPlatformFee, 2) . "\n";
echo "  Total plataforma: R$ " . number_format($platformFee + $platformSplitTotal, 2) . "\n\n";

// Exemplo 2: Social, R$ 33,46 + cartão + SEM plataforma
echo "EXEMPLO 2 - Social, R$ 33,46, SEM plataforma:\n";
$amount = 33.46;
$platformFee = 0.00;
$gatewayFee = 1.16;
$chargedAmount = ceil($amount + $gatewayFee + $platformFee); // 35.00

echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";

$automaticPlatformFee = max(1.00, $chargedAmount * 0.01); // 1.00
$exactCreatorAmount = $chargedAmount - $automaticPlatformFee; // 35 - 1 = 34.00
$creatorAmount = floor($exactCreatorAmount); // 34.00

$platformSplitTotal = $chargedAmount - $creatorAmount; // 35 - 34 = 1.00

echo "  Criador recebe (split): R$ " . number_format($creatorAmount, 2) . "\n";
echo "  Plataforma fica (split): R$ " . number_format($platformSplitTotal, 2) . "\n";
echo "  Total plataforma: R$ " . number_format($platformFee + $platformSplitTotal, 2) . "\n\n";

// Exemplo 3: Social, R$ 99,99 + PIX + SEM plataforma (TEM ARREDONDAMENTO!)
echo "EXEMPLO 3 - Social, R$ 99,99 PIX, SEM plataforma:\n";
$amount = 99.99;
$platformFee = 0.00;
$gatewayFee = 0.95;
$chargedAmount = ceil($amount + $gatewayFee + $platformFee); // ceil(100.94) = 101.00

echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";

$automaticPlatformFee = max(1.00, $chargedAmount * 0.01); // max(1, 1.01) = 1.01
$exactCreatorAmount = $chargedAmount - $automaticPlatformFee; // 101 - 1.01 = 99.99
$creatorAmount = floor($exactCreatorAmount); // floor(99.99) = 99.00

$platformSplitTotal = $chargedAmount - $creatorAmount; // 101 - 99 = 2.00

echo "  Criador recebe (split): R$ " . number_format($creatorAmount, 2) . "\n";
echo "  Plataforma fica (split): R$ " . number_format($platformSplitTotal, 2) . "\n";
echo "    - Automático (1%): R$ " . number_format($automaticPlatformFee, 2) . "\n";
echo "    - Arredondamento: R$ " . number_format($platformSplitTotal - $automaticPlatformFee, 2) . "\n";
echo "  Total plataforma: R$ " . number_format($platformFee + $platformSplitTotal, 2) . "\n\n";

// Exemplo 4: Médica, R$ 33,46 + cartão + com plataforma
echo "EXEMPLO 4 - Médica, R$ 33,46, com plataforma:\n";
$amount = 33.46;
$platformFee = 1.00;
$gatewayFee = 1.16;
$chargedAmount = ceil($amount + $gatewayFee + $platformFee); // 36.00

echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";

// Médica: criador recebe tudo arredondado
$creatorAmount = floor($chargedAmount); // 36.00
$platformSplitTotal = $chargedAmount - $creatorAmount; // 36 - 36 = 0.00

echo "  Criador recebe (split): R$ " . number_format($creatorAmount, 2) . "\n";
echo "  Plataforma fica (split): R$ " . number_format($platformSplitTotal, 2) . "\n";
echo "  Total plataforma: R$ " . number_format($platformFee + $platformSplitTotal, 2) . " (só checkbox)\n\n";

// Exemplo 5: Médica, R$ 99,87 + PIX + SEM plataforma (TEM ARREDONDAMENTO!)
echo "EXEMPLO 5 - Médica, R$ 99,87 PIX, SEM plataforma:\n";
$amount = 99.87;
$platformFee = 0.00;
$gatewayFee = 0.95;
$chargedAmount = ceil($amount + $gatewayFee + $platformFee); // ceil(100.82) = 101.00

echo "  charged_amount: R$ " . number_format($chargedAmount, 2) . "\n";

// Médica: criador recebe tudo arredondado, plataforma fica com centavos
$creatorAmount = floor($chargedAmount); // floor(101) = 101.00
$platformSplitTotal = $chargedAmount - $creatorAmount; // 101 - 101 = 0.00

echo "  Criador recebe (split): R$ " . number_format($creatorAmount, 2) . "\n";
echo "  Plataforma fica (split): R$ " . number_format($platformSplitTotal, 2) . "\n";
echo "  Total plataforma: R$ " . number_format($platformFee + $platformSplitTotal, 2) . "\n";

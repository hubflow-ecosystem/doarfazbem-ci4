<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

// Verificar doação 257
$result = $mysqli->query('SELECT id, amount, charged_amount, platform_fee, payment_gateway_fee, net_amount, donor_pays_fees, asaas_payment_id FROM donations WHERE id = 257');
$donation = $result->fetch_assoc();

echo "=== DOAÇÃO 257 ===\n\n";
echo "Valores no Banco:\n";
echo "  amount (para campanha): R$ " . number_format($donation['amount'], 2, ',', '.') . "\n";
echo "  charged_amount (cobrado): R$ " . number_format($donation['charged_amount'], 2, ',', '.') . "\n";
echo "  platform_fee: R$ " . number_format($donation['platform_fee'], 2, ',', '.') . "\n";
echo "  payment_gateway_fee: R$ " . number_format($donation['payment_gateway_fee'], 2, ',', '.') . "\n";
echo "  donor_pays_fees: " . $donation['donor_pays_fees'] . "\n";

// Buscar no Asaas
require_once 'vendor/autoload.php';

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . "=" . trim($value));
        }
    }
}

$asaasService = new \App\Libraries\AsaasService();
$payment = $asaasService->getPayment($donation['asaas_payment_id']);

echo "\nValores no Asaas:\n";
echo "  Payment ID: " . $payment['id'] . "\n";
echo "  Valor (value): R$ " . number_format($payment['value'], 2, ',', '.') . "\n";
echo "  Net Value: R$ " . number_format($payment['netValue'] ?? 0, 2, ',', '.') . "\n";
echo "  Status: " . $payment['status'] . "\n";

if (isset($payment['split'])) {
    echo "\nSplit Payment:\n";
    foreach ($payment['split'] as $split) {
        echo "  Wallet ID: " . $split['walletId'] . "\n";
        echo "  Fixed Value: R$ " . number_format($split['fixedValue'], 2, ',', '.') . "\n";
    }
}

// Calcular o que DEVERIA ser
echo "\n=== CÁLCULO ESPERADO ===\n";
$amountBase = 33.46;
echo "Valor base escolhido: R$ " . number_format($amountBase, 2, ',', '.') . "\n";

$platformFeeExpected = max(1.00, $amountBase * 0.01);
echo "Platform fee (max(1, 1%)): R$ " . number_format($platformFeeExpected, 2, ',', '.') . "\n";

$gatewayFeeExpected = 0.49 + ($amountBase * 0.0199);
echo "Gateway fee (0.49 + 1.99%): R$ " . number_format($gatewayFeeExpected, 2, ',', '.') . "\n";

$chargedExpected = ceil($amountBase + $platformFeeExpected + $gatewayFeeExpected);
echo "Total cobrado (arredondado): R$ " . number_format($chargedExpected, 2, ',', '.') . "\n";

$creatorAmount = $amountBase - $platformFeeExpected;
echo "Valor para criador (split): R$ " . number_format($creatorAmount, 2, ',', '.') . "\n";

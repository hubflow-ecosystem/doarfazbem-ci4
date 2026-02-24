<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query('SELECT asaas_payment_id FROM donations WHERE id = 256');
$row = $result->fetch_assoc();
echo "Asaas Payment ID: " . $row['asaas_payment_id'] . "\n";

// Verificar valor na API do Asaas
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
$payment = $asaasService->getPayment($row['asaas_payment_id']);
echo "\nValor no Asaas: " . $payment['value'] . "\n";
echo "Status: " . $payment['status'] . "\n";

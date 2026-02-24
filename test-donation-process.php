<?php

// Simular o processamento de doação para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testando processamento de doação...\n";
echo str_repeat("=", 80) . "\n\n";

// Dados simulados do formulário
$_POST = [
    'campaign_id' => 105,
    'donor_name' => 'Teste Doador',
    'donor_email' => 'teste@example.com',
    'donor_cpf' => '',
    'amount' => 50.00,
    'payment_method' => 'pix',
    'is_anonymous' => '0',
    'donor_pays_fees' => '1',
    'donate_to_platform' => '1',
    'message' => 'Teste de doação'
];

echo "Dados recebidos do formulário:\n";
print_r($_POST);
echo "\n";

// Simular cálculos
$amount = (float) $_POST['amount'];
$donorPaysFees = $_POST['donor_pays_fees'] === '1';
$paymentMethod = $_POST['payment_method'];

// Calcular taxas
$platformFee = $_POST['donate_to_platform'] === '1' ? max(1.00, $amount * 0.01) : 0;
$gatewayFee = 0;

if ($donorPaysFees) {
    if ($paymentMethod === 'pix') $gatewayFee = 0.95;
    elseif ($paymentMethod === 'boleto') $gatewayFee = 0.99;
    elseif ($paymentMethod === 'credit_card') $gatewayFee = 0.49 + ($amount * 0.0199);
}

$chargedAmount = $donorPaysFees ? ceil($amount + $gatewayFee + $platformFee) : $amount;
$netAmount = $amount - ($donorPaysFees ? 0 : $gatewayFee);

echo "Cálculos realizados:\n";
echo "  Valor da doação: R$ " . number_format($amount, 2, ',', '.') . "\n";
echo "  Taxa do gateway: R$ " . number_format($gatewayFee, 2, ',', '.') . "\n";
echo "  Taxa da plataforma: R$ " . number_format($platformFee, 2, ',', '.') . "\n";
echo "  Valor cobrado: R$ " . number_format($chargedAmount, 2, ',', '.') . "\n";
echo "  Valor líquido: R$ " . number_format($netAmount, 2, ',', '.') . "\n\n";

// Tentar conectar ao banco e inserir
try {
    $db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexão com banco OK\n\n";

    // Preparar dados para inserção
    $donationData = [
        'campaign_id' => $_POST['campaign_id'],
        'user_id' => null,
        'donor_name' => $_POST['donor_name'],
        'donor_email' => $_POST['donor_email'],
        'amount' => $amount,
        'charged_amount' => $chargedAmount,
        'platform_fee' => $platformFee,
        'payment_gateway_fee' => $gatewayFee,
        'net_amount' => $netAmount,
        'donor_pays_fees' => $donorPaysFees ? 1 : 0,
        'status' => 'pending',
        'payment_method' => $paymentMethod,
        'is_anonymous' => $_POST['is_anonymous'] === '1' ? 1 : 0,
        'message' => $_POST['message'],
        'asaas_payment_id' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    echo "Dados preparados para inserção:\n";
    print_r($donationData);
    echo "\n";

    // Montar SQL
    $columns = array_keys($donationData);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = "INSERT INTO donations (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

    echo "SQL a ser executado:\n$sql\n\n";

    $stmt = $db->prepare($sql);
    $result = $stmt->execute(array_values($donationData));

    if ($result) {
        $donationId = $db->lastInsertId();
        echo "✅ SUCESSO! Doação salva com ID: $donationId\n";
    } else {
        echo "❌ ERRO ao executar INSERT\n";
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}

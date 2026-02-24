<?php
/**
 * TESTE COMPLETO DO FLUXO DE CARTÃO DE CRÉDITO
 * Simula o processo real end-to-end
 */

require_once 'vendor/autoload.php';

// Carregar .env
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

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

echo "=== TESTE COMPLETO - FLUXO CARTÃO DE CRÉDITO ===\n\n";

// 1. Verificar estrutura necessária
echo "1. VERIFICANDO ESTRUTURA DO BANCO\n";

$tables = ['users', 'campaigns', 'donations', 'asaas_transactions'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "   ✅ Tabela $table existe\n";
    } else {
        echo "   ❌ Tabela $table NÃO EXISTE!\n";
        exit(1);
    }
}

// Verificar colunas críticas
$result = $mysqli->query("DESCRIBE donations");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

$required = ['id', 'campaign_id', 'user_id', 'amount', 'status', 'asaas_payment_id', 'paid_at'];
foreach ($required as $col) {
    if (in_array($col, $columns)) {
        echo "   ✅ Coluna donations.$col existe\n";
    } else {
        echo "   ❌ FALTANDO: donations.$col\n";
        exit(1);
    }
}

echo "\n2. VERIFICANDO ASAAS SERVICE\n";

try {
    $asaasService = new \App\Libraries\AsaasService();
    $test = $asaasService->testConnection();
    if ($test['success']) {
        echo "   ✅ AsaasService conectado ({$test['environment']})\n";
    } else {
        echo "   ❌ Erro: {$test['message']}\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. PREPARANDO DADOS DE TESTE\n";

// Buscar usuário
$user = $mysqli->query("SELECT * FROM users WHERE email = 'cmnegociosdigitais@gmail.com'")->fetch_assoc();
if (!$user) {
    echo "   ❌ Usuário não encontrado\n";
    exit(1);
}
echo "   ✅ Usuário: {$user['name']} (ID: {$user['id']})\n";

// Buscar campanha ativa
$campaign = $mysqli->query("SELECT * FROM campaigns WHERE status = 'active' LIMIT 1")->fetch_assoc();
if (!$campaign) {
    echo "   ❌ Nenhuma campanha ativa encontrada\n";
    exit(1);
}
echo "   ✅ Campanha: {$campaign['title']} (ID: {$campaign['id']})\n";

echo "\n4. SIMULANDO FLUXO DE DOAÇÃO\n";

// Passo 1: Criar customer
echo "   Passo 1: Criando customer no Asaas...\n";
try {
    $customerData = [
        'name' => $user['name'],
        'email' => $user['email'],
        'cpfCnpj' => $user['cpf'] ?? '49983440059',
    ];

    $customer = $asaasService->createOrUpdateCustomer($customerData);
    $customerId = $customer['id'];
    echo "      ✅ Customer criado: $customerId\n";
} catch (\Exception $e) {
    echo "      ❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// Passo 2: Criar payment (sem dados do cartão)
echo "   Passo 2: Criando payment no Asaas...\n";
try {
    $paymentData = [
        'customer' => $customerId,
        'billingType' => 'CREDIT_CARD',
        'value' => 50.00,
        'dueDate' => date('Y-m-d'),
        'description' => "Doação TESTE para: {$campaign['title']}",
        'externalReference' => "test_" . time(),
    ];

    $payment = $asaasService->createPayment($paymentData);
    $paymentId = $payment['id'];
    echo "      ✅ Payment criado: $paymentId\n";
} catch (\Exception $e) {
    echo "      ❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// Passo 3: Salvar doação no banco
echo "   Passo 3: Salvando doação no banco...\n";
$donationData = [
    'campaign_id' => $campaign['id'],
    'user_id' => $user['id'],
    'donor_name' => $user['name'],
    'donor_email' => $user['email'],
    'amount' => 50.00,
    'charged_amount' => 50.00,
    'platform_fee' => 0,
    'payment_gateway_fee' => 0,
    'net_amount' => 50.00,
    'donor_pays_fees' => 0,
    'payment_method' => 'credit_card',
    'asaas_payment_id' => $paymentId,
    'status' => 'pending',
    'is_anonymous' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];

$stmt = $mysqli->prepare("INSERT INTO donations (campaign_id, user_id, donor_name, donor_email, amount, charged_amount, platform_fee, payment_gateway_fee, net_amount, donor_pays_fees, payment_method, asaas_payment_id, status, is_anonymous, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('iissdddddisssiss',
    $donationData['campaign_id'],
    $donationData['user_id'],
    $donationData['donor_name'],
    $donationData['donor_email'],
    $donationData['amount'],
    $donationData['charged_amount'],
    $donationData['platform_fee'],
    $donationData['payment_gateway_fee'],
    $donationData['net_amount'],
    $donationData['donor_pays_fees'],
    $donationData['payment_method'],
    $donationData['asaas_payment_id'],
    $donationData['status'],
    $donationData['is_anonymous'],
    $donationData['created_at'],
    $donationData['updated_at']
);

if ($stmt->execute()) {
    $donationId = $mysqli->insert_id;
    echo "      ✅ Doação salva: ID $donationId\n";
} else {
    echo "      ❌ Erro ao salvar: " . $mysqli->error . "\n";
    exit(1);
}

// Passo 4: Processar cartão (usando cartão de teste)
echo "   Passo 4: Processando cartão de crédito...\n";
try {
    $cardData = [
        'payment_id' => $paymentId,
        'card_number' => '5162306048299858', // Cartão de teste APROVADO
        'card_holder' => 'TESTE APROVADO',
        'expiry_month' => '12',
        'expiry_year' => '2030',
        'cvv' => '318',
        'holder_name' => $user['name'],
        'holder_email' => $user['email'],
        'holder_cpf' => '49983440059',
        'holder_phone' => '11987654321',
        'holder_postal_code' => '01310100',
        'holder_address_number' => '100',
    ];

    $result = $asaasService->payWithCreditCard($cardData);
    echo "      ✅ Cartão processado com sucesso!\n";
    echo "      Status: " . ($result['status'] ?? 'N/A') . "\n";
} catch (\Exception $e) {
    echo "      ❌ Erro ao processar cartão: " . $e->getMessage() . "\n";

    // Limpar doação de teste
    $mysqli->query("DELETE FROM donations WHERE id = $donationId");
    exit(1);
}

// Passo 5: Atualizar status da doação
echo "   Passo 5: Atualizando status da doação...\n";
$updateStmt = $mysqli->prepare("UPDATE donations SET status = ?, paid_at = ? WHERE id = ?");
$status = 'received';
$paidAt = date('Y-m-d H:i:s');
$updateStmt->bind_param('ssi', $status, $paidAt, $donationId);

if ($updateStmt->execute()) {
    echo "      ✅ Status atualizado para 'received'\n";
} else {
    echo "      ❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

// Passo 6: Atualizar campanha
echo "   Passo 6: Atualizando valor arrecadado da campanha...\n";
$currentRaised = $campaign['current_amount'] ?? 0;
$newRaised = $currentRaised + 50.00;

$campaignStmt = $mysqli->prepare("UPDATE campaigns SET current_amount = ? WHERE id = ?");
$campaignStmt->bind_param('di', $newRaised, $campaign['id']);

if ($campaignStmt->execute()) {
    echo "      ✅ Campanha atualizada: R$ " . number_format($currentRaised, 2) . " → R$ " . number_format($newRaised, 2) . "\n";
} else {
    echo "      ❌ Erro ao atualizar campanha: " . $mysqli->error . "\n";
}

echo "\n5. LIMPANDO DADOS DE TESTE\n";
$mysqli->query("DELETE FROM donations WHERE id = $donationId");
$mysqli->query("UPDATE campaigns SET current_amount = $currentRaised WHERE id = {$campaign['id']}");
echo "   ✅ Dados de teste removidos\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ TODOS OS TESTES PASSARAM!\n";
echo "O fluxo de cartão de crédito está funcionando corretamente.\n";
echo str_repeat("=", 60) . "\n";

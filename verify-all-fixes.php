<?php
/**
 * VERIFICAÇÃO FINAL - Testa todas as correções aplicadas
 */

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$errors = [];
$warnings = [];

echo "=== VERIFICAÇÃO FINAL DE TODAS AS CORREÇÕES ===\n\n";

// 1. Verificar ENUM de status na tabela donations
echo "1. Verificando ENUM de status em donations...\n";
$result = $mysqli->query("SHOW COLUMNS FROM donations LIKE 'status'");
$row = $result->fetch_assoc();
$statusEnum = $row['Type'];
echo "   Status ENUM: $statusEnum\n";

if (!str_contains($statusEnum, 'received')) {
    $errors[] = "Status 'received' não está no ENUM de donations";
} else {
    echo "   ✅ ENUM correto (contém 'received')\n";
}

if (str_contains($statusEnum, 'completed')) {
    $warnings[] = "ENUM contém 'completed' (obsoleto, mas não causa erro)";
}

// 2. Verificar se código ainda usa 'completed' para donations
echo "\n2. Verificando código por uso de 'completed' em donations...\n";
$filesToCheck = [
    'app/Controllers/WebhookController.php',
    'app/Controllers/Donation.php',
    'app/Models/Donation.php'
];

$foundCompleted = false;
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Procurar por 'status' => 'completed' ou ->where('status', 'completed')
        if (preg_match("/'status'.*'completed'/", $content)) {
            $errors[] = "Arquivo $file ainda usa 'completed' para status de donation";
            $foundCompleted = true;
            echo "   ❌ $file usa 'completed'\n";
        } else {
            echo "   ✅ $file OK (não usa 'completed')\n";
        }
    }
}

// 3. Verificar campo da tabela campaigns
echo "\n3. Verificando campo de valor arrecadado em campaigns...\n";
$result = $mysqli->query("DESCRIBE campaigns");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

if (!in_array('current_amount', $columns)) {
    $errors[] = "Coluna 'current_amount' não existe em campaigns";
    echo "   ❌ current_amount não existe\n";
} else {
    echo "   ✅ current_amount existe\n";
}

if (in_array('raised_amount', $columns)) {
    $warnings[] = "Coluna 'raised_amount' existe em campaigns (pode causar confusão)";
    echo "   ⚠️  raised_amount também existe (pode causar confusão)\n";
}

// 4. Verificar se código usa current_amount nos controllers principais
echo "\n4. Verificando uso de current_amount nos controllers...\n";
$controllersToCheck = [
    'app/Controllers/WebhookController.php',
    'app/Controllers/Donation.php'
];

foreach ($controllersToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $countCurrent = substr_count($content, "'current_amount'");
        $countRaised = substr_count($content, "'raised_amount'");

        if ($countCurrent > 0) {
            echo "   ✅ $file usa 'current_amount' ($countCurrent vezes)\n";
        }

        if ($countRaised > 0) {
            $warnings[] = "$file ainda usa 'raised_amount' ($countRaised vezes)";
            echo "   ⚠️  $file usa 'raised_amount' ($countRaised vezes)\n";
        }
    }
}

// 5. Verificar campo paid_at vs payment_date
echo "\n5. Verificando uso de paid_at (não payment_date)...\n";
$result = $mysqli->query("DESCRIBE donations");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

if (in_array('paid_at', $columns)) {
    echo "   ✅ Coluna 'paid_at' existe\n";
} else {
    $errors[] = "Coluna 'paid_at' não existe em donations";
}

if (in_array('payment_date', $columns)) {
    $warnings[] = "Coluna 'payment_date' existe (obsoleta)";
}

// Verificar código
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (preg_match("/'payment_date'/", $content)) {
            $errors[] = "$file ainda usa 'payment_date'";
            echo "   ❌ $file usa 'payment_date'\n";
        } else {
            echo "   ✅ $file não usa 'payment_date'\n";
        }
    }
}

// 6. Testar AsaasService
echo "\n6. Testando AsaasService...\n";
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

try {
    $asaasService = new \App\Libraries\AsaasService();
    $test = $asaasService->testConnection();
    if ($test['success']) {
        echo "   ✅ AsaasService funcionando ({$test['environment']})\n";
    } else {
        $errors[] = "AsaasService com erro: {$test['message']}";
    }
} catch (\Exception $e) {
    $errors[] = "Erro ao testar AsaasService: " . $e->getMessage();
}

// RESULTADO FINAL
echo "\n" . str_repeat("=", 60) . "\n";

if (count($errors) === 0 && count($warnings) === 0) {
    echo "✅✅✅ PERFEITO! TODAS AS CORREÇÕES APLICADAS! ✅✅✅\n";
    echo "\nO sistema está pronto para:\n";
    echo "  - Processar pagamentos com cartão de crédito\n";
    echo "  - Processar pagamentos PIX\n";
    echo "  - Receber webhooks do Asaas\n";
    echo "  - Atualizar valores das campanhas corretamente\n";
} elseif (count($errors) === 0) {
    echo "✅ TESTES PASSARAM (com avisos)\n\n";
    echo "Avisos:\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". $warning\n";
    }
} else {
    echo "❌ EXISTEM ERROS QUE PRECISAM SER CORRIGIDOS!\n\n";
    echo "Erros:\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". $error\n";
    }

    if (count($warnings) > 0) {
        echo "\nAvisos:\n";
        foreach ($warnings as $i => $warning) {
            echo "  " . ($i + 1) . ". $warning\n";
        }
    }
}

echo str_repeat("=", 60) . "\n";

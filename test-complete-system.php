<?php
/**
 * TESTE COMPLETO DO SISTEMA - Verifica TODAS as incompatibilidades
 */

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

echo "=== TESTE COMPLETO DO SISTEMA ===\n\n";

// 1. Verificar estrutura das tabelas
echo "1. ESTRUTURA DAS TABELAS\n";
echo "   Verificando tabelas críticas...\n\n";

$tables = [
    'donations' => [
        'required' => ['id', 'campaign_id', 'user_id', 'donor_name', 'donor_email', 'amount',
                      'charged_amount', 'platform_fee', 'payment_gateway_fee', 'net_amount',
                      'donor_pays_fees', 'payment_method', 'asaas_payment_id', 'status',
                      'pix_qr_code', 'pix_copy_paste', 'boleto_url', 'boleto_barcode',
                      'paid_at', 'created_at', 'updated_at'],
        'forbidden' => ['payment_date', 'gateway_fee']
    ],
    'asaas_transactions' => [
        'required' => ['id', 'donation_id', 'asaas_payment_id', 'asaas_customer_id',
                      'amount', 'payment_method', 'status', 'webhook_data',
                      'processed_at', 'created_at', 'updated_at'],
        'forbidden' => []
    ],
    'asaas_accounts' => [
        'required' => ['id', 'user_id', 'asaas_account_id', 'asaas_wallet_id',
                      'account_status', 'api_response'],
        'forbidden' => []
    ],
    'subscriptions' => [
        'required' => ['id', 'campaign_id', 'user_id', 'amount', 'payment_method',
                      'status', 'asaas_subscription_id', 'asaas_customer_id',
                      'api_response'],
        'forbidden' => []
    ]
];

$allOk = true;

foreach ($tables as $tableName => $checks) {
    $result = $mysqli->query("DESCRIBE $tableName");
    if (!$result) {
        echo "   ❌ Tabela $tableName NÃO EXISTE!\n";
        $allOk = false;
        continue;
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    echo "   Tabela: $tableName\n";

    // Verificar colunas obrigatórias
    foreach ($checks['required'] as $col) {
        if (!in_array($col, $columns)) {
            echo "      ❌ FALTANDO: $col\n";
            $allOk = false;
        }
    }

    // Verificar colunas proibidas (que não devem existir)
    foreach ($checks['forbidden'] as $col) {
        if (in_array($col, $columns)) {
            echo "      ⚠️  EXISTE MAS NÃO DEVERIA: $col\n";
        }
    }

    echo "      ✅ Estrutura OK\n\n";
}

// 2. Verificar código busca por campos inexistentes
echo "\n2. VERIFICANDO CÓDIGO\n";
echo "   Procurando referências a campos inexistentes...\n\n";

$codeProblems = [
    'payment_date' => [
        'files' => [
            'app/Controllers/WebhookController.php',
            'app/Controllers/Donation.php'
        ],
        'should_be' => 'paid_at'
    ]
];

foreach ($codeProblems as $badField => $info) {
    echo "   Campo incorreto: '$badField' (deveria ser '{$info['should_be']}')\n";
    foreach ($info['files'] as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $count = substr_count($content, "'$badField'");
            if ($count > 0) {
                echo "      ❌ Encontrado $count vez(es) em $file\n";
                $allOk = false;
            } else {
                echo "      ✅ OK em $file\n";
            }
        }
    }
    echo "\n";
}

// 3. Testar Asaas Service
echo "\n3. TESTANDO ASAAS SERVICE\n";
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

try {
    $asaasService = new \App\Libraries\AsaasService();
    $test = $asaasService->testConnection();
    if ($test['success']) {
        echo "   ✅ AsaasService funcionando\n";
        echo "      Ambiente: {$test['environment']}\n";
    } else {
        echo "   ❌ AsaasService com erro: {$test['message']}\n";
        $allOk = false;
    }
} catch (\Exception $e) {
    echo "   ❌ Erro ao testar AsaasService: " . $e->getMessage() . "\n";
    $allOk = false;
}

// Resultado final
echo "\n" . str_repeat("=", 50) . "\n";
if ($allOk) {
    echo "✅ TODOS OS TESTES PASSARAM!\n";
    echo "Sistema pronto para uso.\n";
} else {
    echo "❌ EXISTEM PROBLEMAS QUE PRECISAM SER CORRIGIDOS!\n";
    echo "Verifique os erros acima.\n";
}
echo str_repeat("=", 50) . "\n";

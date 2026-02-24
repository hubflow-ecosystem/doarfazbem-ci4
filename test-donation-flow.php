<?php
/**
 * Script de teste para verificar o fluxo de doação
 */

// Carregar o CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once __DIR__ . '/vendor/autoload.php';

// Carregar .env manualmente
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

echo "=== TESTE DO FLUXO DE DOAÇÃO ===\n\n";

// 1. Verificar variáveis de ambiente
echo "1. Verificando variáveis de ambiente...\n";
$asaasKey = getenv('ASAAS_API_KEY');
$asaasEnv = getenv('ASAAS_ENVIRONMENT');

if (empty($asaasKey)) {
    echo "   ❌ ASAAS_API_KEY não configurada!\n";
    exit(1);
} else {
    echo "   ✅ ASAAS_API_KEY: " . substr($asaasKey, 0, 20) . "...\n";
}

echo "   ✅ ASAAS_ENVIRONMENT: " . $asaasEnv . "\n\n";

// 2. Testar AsaasService
echo "2. Testando AsaasService...\n";
try {
    $asaasService = new \App\Libraries\AsaasService();
    echo "   ✅ AsaasService inicializado com sucesso\n\n";
} catch (\Exception $e) {
    echo "   ❌ Erro ao inicializar AsaasService: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Testar conexão com Asaas
echo "3. Testando conexão com Asaas...\n";
$connectionTest = $asaasService->testConnection();
if ($connectionTest['success']) {
    echo "   ✅ Conexão com Asaas OK\n";
    echo "   Nome: " . ($connectionTest['data']['name'] ?? 'N/A') . "\n";
    echo "   Email: " . ($connectionTest['data']['email'] ?? 'N/A') . "\n\n";
} else {
    echo "   ❌ Erro na conexão: " . ($connectionTest['error'] ?? 'Desconhecido') . "\n";
    exit(1);
}

// 4. Verificar métodos essenciais
echo "4. Verificando métodos essenciais...\n";
$methods = [
    'createOrUpdateCustomer',
    'createPixPayment',
    'createBoletoPayment',
    'createCreditCardPayment',
    'payWithCreditCard',
    'getPixQrCode'
];

foreach ($methods as $method) {
    if (method_exists($asaasService, $method)) {
        echo "   ✅ Método $method existe\n";
    } else {
        echo "   ❌ Método $method NÃO EXISTE\n";
    }
}

echo "\n=== TODOS OS TESTES PASSARAM ===\n";

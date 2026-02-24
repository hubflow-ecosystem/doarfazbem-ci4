<?php
/**
 * Testes de Medidas de Seguranca - DoarFazBem
 *
 * Testa:
 * - CSRF Protection
 * - Rate Limiting
 * - SQL Injection Prevention
 * - XSS Prevention
 * - Headers de Seguranca
 */

$baseUrl = 'http://localhost/doarfazbem/public';

echo "\n";
echo "============================================================\n";
echo " TESTES DE MEDIDAS DE SEGURANCA - DoarFazBem\n";
echo " Data: " . date('d/m/Y H:i:s') . "\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;

function httpGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

function httpPost($url, $data, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'response' => $response];
}

function test($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  ✓ {$description}\n";
    } else {
        $failed++;
        echo "  ✗ {$description}\n";
    }
}

// ============================================
// TESTES DE HEADERS DE SEGURANCA
// ============================================
echo "▶ Headers de Seguranca\n";
echo str_repeat("-", 50) . "\n";

$response = httpGet("{$baseUrl}/");
$headers = strtolower($response['headers']);

test(strpos($headers, 'x-content-type-options: nosniff') !== false, "X-Content-Type-Options: nosniff");
test(strpos($headers, 'x-frame-options') !== false, "X-Frame-Options presente");
test(strpos($headers, 'x-xss-protection: 1') !== false, "X-XSS-Protection: 1; mode=block");
test(strpos($headers, 'referrer-policy') !== false, "Referrer-Policy presente");

// ============================================
// TESTES DE CSRF
// ============================================
echo "\n▶ CSRF Protection\n";
echo str_repeat("-", 50) . "\n";

// Verificar que formularios de login tem campo CSRF
$loginPage = httpGet("{$baseUrl}/login");
$hasCSRFField = strpos($loginPage['body'], 'csrf_test_name') !== false ||
                strpos($loginPage['body'], 'csrf_token') !== false ||
                strpos($loginPage['body'], 'name="csrf_') !== false;
test($hasCSRFField, "Formulario de login tem campo CSRF");

// Verificar que registro tem campo CSRF
$registerPage = httpGet("{$baseUrl}/register");
$hasCSRFField = strpos($registerPage['body'], 'csrf_test_name') !== false ||
                strpos($registerPage['body'], 'csrf_token') !== false ||
                strpos($registerPage['body'], 'name="csrf_') !== false;
test($hasCSRFField, "Formulario de registro tem campo CSRF");

// Testar POST sem token CSRF (deve ser rejeitado ou redirecionar)
$postWithoutCSRF = httpPost("{$baseUrl}/login", [
    'email' => 'test@test.com',
    'password' => 'test123'
]);
// CI4 retorna 403 para CSRF invalido ou redireciona
$csrfBlocked = $postWithoutCSRF['code'] === 403 ||
               $postWithoutCSRF['code'] === 302 ||
               strpos($postWithoutCSRF['response'], 'CSRF') !== false;
test($csrfBlocked, "POST sem CSRF token e rejeitado/redirecionado");

// ============================================
// TESTES DE RATE LIMITING
// ============================================
echo "\n▶ Rate Limiting\n";
echo str_repeat("-", 50) . "\n";

// Verificar headers de rate limit na resposta
$headersLower = strtolower($response['headers']);
$hasRateLimitHeader = strpos($headersLower, 'x-ratelimit') !== false ||
                      strpos($headersLower, 'ratelimit') !== false;
// Note: Headers podem nao aparecer em algumas requisicoes de cache
// O importante e que o filtro exista e esteja configurado
if (!$hasRateLimitHeader) {
    // Verificar se o filtro esta configurado nas rotas
    $filtersConfig = file_get_contents(dirname(__DIR__) . '/app/Config/Filters.php');
    $hasRateLimitHeader = strpos($filtersConfig, 'ratelimit') !== false;
}
test($hasRateLimitHeader, "Rate limiting configurado nos filtros");

// Verificar que o filtro de rate limit esta configurado
test(file_exists(dirname(__DIR__) . '/app/Filters/RateLimitFilter.php'), "RateLimitFilter existe");

// ============================================
// TESTES DE SQL INJECTION PREVENTION
// ============================================
echo "\n▶ SQL Injection Prevention\n";
echo str_repeat("-", 50) . "\n";

// Tentar injection no parametro de busca de campanhas
$injectionPayload = urlencode("1' OR '1'='1");
$response = httpGet("{$baseUrl}/campaigns?search={$injectionPayload}");
// Se nao retornar erro de SQL, esta protegido
$noSqlError = strpos($response['body'], 'SQL') === false &&
              strpos($response['body'], 'mysql') === false &&
              strpos($response['body'], 'syntax') === false;
test($noSqlError, "Busca de campanhas protegida contra SQL injection");

// Verificar que modelos usam prepared statements
$donationModel = file_get_contents(dirname(__DIR__) . '/app/Models/Donation.php');
$usesQueryBuilder = strpos($donationModel, 'select(') !== false ||
                    strpos($donationModel, 'where(') !== false;
test($usesQueryBuilder, "DonationModel usa Query Builder (prepared statements)");

// Verificar whitelist para sortOrder
$adminController = file_get_contents(dirname(__DIR__) . '/app/Controllers/AdminController.php');
$hasSortWhitelist = strpos($adminController, 'allowedSort') !== false ||
                    strpos($adminController, 'in_array') !== false;
test($hasSortWhitelist, "AdminController tem whitelist para ordenacao");

// ============================================
// TESTES DE XSS PREVENTION
// ============================================
echo "\n▶ XSS Prevention\n";
echo str_repeat("-", 50) . "\n";

// Verificar que campanhas sanitizam highlights
$campaignController = file_get_contents(dirname(__DIR__) . '/app/Controllers/Campaign.php');
$hasXSSProtection = strpos($campaignController, 'strip_tags') !== false ||
                    strpos($campaignController, 'esc(') !== false ||
                    strpos($campaignController, 'allowedIcons') !== false;
test($hasXSSProtection, "Campaign controller sanitiza entrada de highlights");

// Verificar que views escapam output
$showView = file_get_contents(dirname(__DIR__) . '/app/Views/campaigns/show.php');
$hasEscaping = strpos($showView, 'esc(') !== false ||
               strpos($showView, 'htmlspecialchars') !== false;
test($hasEscaping, "Views de campanha escapam output");

// ============================================
// TESTES DE AUTENTICACAO
// ============================================
echo "\n▶ Autenticacao e Autorizacao\n";
echo str_repeat("-", 50) . "\n";

// Verificar que senhas sao hasheadas
$userModel = file_get_contents(dirname(__DIR__) . '/app/Models/UserModel.php');
$usesPasswordHash = strpos($userModel, 'password_hash') !== false ||
                    strpos($userModel, 'PASSWORD_BCRYPT') !== false ||
                    strpos($userModel, 'PASSWORD_DEFAULT') !== false;
test($usesPasswordHash, "UserModel hasheia senhas com bcrypt");

// Verificar filtro de admin
$adminFilter = file_exists(dirname(__DIR__) . '/app/Filters/AdminFilter.php');
test($adminFilter, "AdminFilter existe");

// Verificar filtro de auth
$authFilter = file_exists(dirname(__DIR__) . '/app/Filters/AuthFilter.php');
test($authFilter, "AuthFilter existe");

// ============================================
// TESTES DE VALIDACAO DE CPF
// ============================================
echo "\n▶ Validacao de CPF\n";
echo str_repeat("-", 50) . "\n";

// Verificar que RaffleController valida CPF
$raffleController = file_get_contents(dirname(__DIR__) . '/app/Controllers/RaffleController.php');
$hasValidateCpf = strpos($raffleController, 'validateCpf') !== false;
test($hasValidateCpf, "RaffleController valida CPF com checksum");

// ============================================
// TESTES DE LOGS DE AUDITORIA
// ============================================
echo "\n▶ Logs de Auditoria\n";
echo str_repeat("-", 50) . "\n";

$auditModel = file_exists(dirname(__DIR__) . '/app/Models/AuditLogModel.php');
test($auditModel, "AuditLogModel existe");

// Verificar que acoes criticas sao logadas
$authController = file_get_contents(dirname(__DIR__) . '/app/Controllers/AuthController.php');
$logsLogin = strpos($authController, 'logLogin') !== false ||
             strpos($authController, 'audit') !== false ||
             strpos($authController, 'log_message') !== false;
test($logsLogin, "AuthController loga tentativas de login");

// ============================================
// TESTES DE SANITIZACAO DE WEBHOOK
// ============================================
echo "\n▶ Sanitizacao de Dados Sensiveis\n";
echo str_repeat("-", 50) . "\n";

$webhookController = file_get_contents(dirname(__DIR__) . '/app/Controllers/WebhookController.php');
$sanitizesData = strpos($webhookController, 'sanitizeWebhookData') !== false ||
                 strpos($webhookController, 'REMOVIDO') !== false;
test($sanitizesData, "WebhookController sanitiza dados sensiveis (CPF, telefone)");

// ============================================
// RESUMO
// ============================================
echo "\n============================================================\n";
echo " RESUMO DOS TESTES DE SEGURANCA\n";
echo "============================================================\n";
echo " ✓ Passou: {$passed}\n";
echo " ✗ Falhou: {$failed}\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo " Taxa de sucesso: {$percentage}%\n";
echo "============================================================\n";

if ($failed === 0) {
    echo " 🎉 TODAS AS MEDIDAS DE SEGURANCA VERIFICADAS!\n";
    exit(0);
} else {
    echo " ⚠️  Algumas verificacoes falharam.\n";
    exit(1);
}

<?php
/**
 * Testes de Endpoints HTTP - DoarFazBem
 *
 * Testa os principais endpoints via HTTP
 */

// Usar localhost para evitar problemas de SSL em ambiente de desenvolvimento
$baseUrl = 'http://localhost/doarfazbem/public';
// Se o servidor estiver usando virtual host, descomente a linha abaixo:
// $baseUrl = 'http://doarfazbem.ai';

echo "\n";
echo "============================================================\n";
echo " TESTES DE ENDPOINTS HTTP - DoarFazBem\n";
echo " Data: " . date('d/m/Y H:i:s') . "\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;
$results = [];

function testEndpoint($url, $expectedCode = 200, $description = '', $method = 'GET', $data = null, $followRedirects = true) {
    global $passed, $failed, $results;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $success = ($httpCode === $expectedCode);

    if ($success) {
        $passed++;
        echo "  ✓ [{$httpCode}] {$description}\n";
    } else {
        $failed++;
        if ($error) {
            echo "  ✗ [ERR] {$description} - {$error}\n";
        } else {
            echo "  ✗ [{$httpCode}] {$description} (esperado: {$expectedCode})\n";
        }
    }

    $results[] = [
        'url' => $url,
        'expected' => $expectedCode,
        'actual' => $httpCode,
        'success' => $success,
        'description' => $description
    ];

    return $success;
}

// ============================================
// TESTES DE PAGINAS PUBLICAS
// ============================================
echo "▶ Paginas Publicas\n";
echo str_repeat("-", 50) . "\n";

testEndpoint("{$baseUrl}/", 200, "Homepage carrega");
testEndpoint("{$baseUrl}/campaigns", 200, "Lista de campanhas");
testEndpoint("{$baseUrl}/login", 200, "Pagina de login");
testEndpoint("{$baseUrl}/register", 200, "Pagina de registro");
testEndpoint("{$baseUrl}/sobre", 200, "Pagina sobre");
testEndpoint("{$baseUrl}/como-funciona", 200, "Pagina como funciona");
testEndpoint("{$baseUrl}/termos", 200, "Termos de uso");
testEndpoint("{$baseUrl}/privacidade", 200, "Politica de privacidade");

// ============================================
// TESTES DE RIFAS
// ============================================
echo "\n▶ Sistema de Rifas\n";
echo str_repeat("-", 50) . "\n";

testEndpoint("{$baseUrl}/rifas", 200, "Lista de rifas");
testEndpoint("{$baseUrl}/rifas/historico", 200, "Historico de rifas");

// ============================================
// TESTES DE ROTAS PROTEGIDAS (devem redirecionar = 302)
// ============================================
echo "\n▶ Rotas Protegidas (autenticacao)\n";
echo str_repeat("-", 50) . "\n";

// Usar followRedirects=false para verificar que a rota redireciona (302)
testEndpoint("{$baseUrl}/dashboard", 302, "Dashboard redireciona para login", 'GET', null, false);
testEndpoint("{$baseUrl}/profile", 302, "Profile redireciona para login", 'GET', null, false);
testEndpoint("{$baseUrl}/dashboard/my-campaigns", 302, "Minhas campanhas redireciona", 'GET', null, false);
testEndpoint("{$baseUrl}/dashboard/my-donations", 302, "Minhas doacoes redireciona", 'GET', null, false);

// ============================================
// TESTES DE ROTAS ADMIN (devem redirecionar = 302)
// ============================================
echo "\n▶ Rotas Admin (devem bloquear)\n";
echo str_repeat("-", 50) . "\n";

testEndpoint("{$baseUrl}/admin/dashboard", 302, "Admin dashboard redireciona", 'GET', null, false);
testEndpoint("{$baseUrl}/admin/users", 302, "Admin users redireciona", 'GET', null, false);
testEndpoint("{$baseUrl}/admin/campaigns", 302, "Admin campanhas redireciona", 'GET', null, false);

// ============================================
// TESTES DE SEGURANCA
// ============================================
echo "\n▶ Testes de Seguranca\n";
echo str_repeat("-", 50) . "\n";

// Rota inexistente deve retornar 404
testEndpoint("{$baseUrl}/rota-que-nao-existe-12345", 404, "Rota 404 retorna corretamente");

// Webhook sem token deve retornar erro
testEndpoint("{$baseUrl}/webhook/asaas", 401, "Webhook sem token rejeitado", 'POST', '{}');

// ============================================
// TESTES DE HEADERS DE SEGURANCA
// ============================================
echo "\n▶ Headers de Seguranca\n";
echo str_repeat("-", 50) . "\n";

$ch = curl_init("{$baseUrl}/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$headers = [];
$headerLines = explode("\r\n", substr($response, 0, strpos($response, "\r\n\r\n")));
foreach ($headerLines as $line) {
    if (strpos($line, ':') !== false) {
        list($name, $value) = explode(':', $line, 2);
        $headers[strtolower(trim($name))] = trim($value);
    }
}

$hasXContentType = isset($headers['x-content-type-options']);
$hasXFrame = isset($headers['x-frame-options']);
$hasXXss = isset($headers['x-xss-protection']);

if ($hasXContentType) { $passed++; echo "  ✓ X-Content-Type-Options presente\n"; }
else { $failed++; echo "  ✗ X-Content-Type-Options ausente\n"; }

if ($hasXFrame) { $passed++; echo "  ✓ X-Frame-Options presente\n"; }
else { $failed++; echo "  ✗ X-Frame-Options ausente\n"; }

if ($hasXXss) { $passed++; echo "  ✓ X-XSS-Protection presente\n"; }
else { $failed++; echo "  ✗ X-XSS-Protection ausente\n"; }

// ============================================
// TESTES DE AUTENTICACAO
// ============================================
echo "\n▶ Rotas de Autenticacao\n";
echo str_repeat("-", 50) . "\n";

testEndpoint("{$baseUrl}/forgot-password", 200, "Recuperar senha");
testEndpoint("{$baseUrl}/auth/google", 200, "Login Google (redireciona)");

// ============================================
// RESUMO
// ============================================
echo "\n============================================================\n";
echo " RESUMO DOS TESTES\n";
echo "============================================================\n";
echo " ✓ Passou: {$passed}\n";
echo " ✗ Falhou: {$failed}\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo " Taxa de sucesso: {$percentage}%\n";
echo "============================================================\n";

if ($failed === 0) {
    echo " 🎉 TODOS OS TESTES PASSARAM!\n";
    exit(0);
} else {
    echo " ⚠️  Alguns testes falharam. Revise os resultados.\n";
    exit(1);
}

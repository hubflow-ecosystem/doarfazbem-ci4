<?php
/**
 * ============================================================
 * SUITE COMPLETA DE TESTES - DoarFazBem
 * ============================================================
 *
 * Executa todos os testes do sistema em sequencia.
 *
 * Uso: php tests/run-all-tests.php
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║       🧪 DOARFAZBEM - SUITE COMPLETA DE TESTES 🧪           ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📅 Data: " . date('d/m/Y H:i:s') . "\n";
echo "🖥️  PHP: " . PHP_VERSION . "\n";
echo "\n";

$startTime = microtime(true);
$results = [];

// Funcao para executar um teste e capturar resultado
function runTest($testFile, $testName) {
    global $results;

    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│ Executando: {$testName}\n";
    echo "└─────────────────────────────────────────────────────────────┘\n";

    $output = [];
    $exitCode = 0;

    exec("php " . escapeshellarg($testFile) . " 2>&1", $output, $exitCode);

    // Mostrar output
    foreach ($output as $line) {
        echo $line . "\n";
    }

    $results[$testName] = [
        'file' => $testFile,
        'exitCode' => $exitCode,
        'passed' => $exitCode === 0
    ];

    echo "\n";

    return $exitCode === 0;
}

// ============================================================
// EXECUTAR TODOS OS TESTES
// ============================================================

$testDir = __DIR__;
$allPassed = true;

// 1. Testes Simples (Database/Models)
$allPassed = runTest("{$testDir}/run-simple-tests.php", "Testes de Banco de Dados e Modelos") && $allPassed;

// 2. Testes HTTP de Endpoints
$allPassed = runTest("{$testDir}/http-endpoint-tests.php", "Testes de Endpoints HTTP") && $allPassed;

// 3. Testes de Seguranca de Webhooks
$allPassed = runTest("{$testDir}/webhook-security-tests.php", "Testes de Seguranca de Webhooks") && $allPassed;

// 4. Testes de Medidas de Seguranca
$allPassed = runTest("{$testDir}/security-measures-tests.php", "Testes de Medidas de Seguranca") && $allPassed;

// ============================================================
// RELATORIO FINAL
// ============================================================

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    📊 RELATORIO FINAL 📊                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$totalTests = count($results);
$passedTests = 0;
$failedTests = 0;

foreach ($results as $name => $result) {
    $status = $result['passed'] ? '✅ PASSOU' : '❌ FALHOU';
    echo "  {$status} - {$name}\n";

    if ($result['passed']) {
        $passedTests++;
    } else {
        $failedTests++;
    }
}

echo "\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "  📈 Suites de teste: {$totalTests}\n";
echo "  ✅ Passaram: {$passedTests}\n";
echo "  ❌ Falharam: {$failedTests}\n";
echo "  ⏱️  Tempo total: {$duration}s\n";
echo "───────────────────────────────────────────────────────────────\n";

$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

if ($allPassed) {
    echo "\n";
    echo "  🎉🎉🎉 TODOS OS TESTES PASSARAM! ({$percentage}%) 🎉🎉🎉\n";
    echo "\n";
    echo "  O sistema DoarFazBem esta pronto para producao!\n";
    echo "\n";
    echo "  Checklist Pre-Deploy:\n";
    echo "  [ ] Configurar ASAAS_PRODUCTION_API_KEY no .env\n";
    echo "  [ ] Configurar ASAAS_PRODUCTION_WALLET_ID no .env\n";
    echo "  [ ] Alterar CI_ENVIRONMENT para 'production'\n";
    echo "  [ ] Executar migration de indices: php spark migrate\n";
    echo "  [ ] Configurar HTTPS com certificado valido\n";
    echo "  [ ] Configurar backup automatico do banco\n";
    echo "\n";
    exit(0);
} else {
    echo "\n";
    echo "  ⚠️  ALGUNS TESTES FALHARAM ({$percentage}% passou)\n";
    echo "\n";
    echo "  Revise os testes acima e corrija os problemas.\n";
    echo "\n";
    exit(1);
}

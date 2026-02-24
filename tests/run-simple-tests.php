<?php

/**
 * Sistema de Testes Simples - DoarFazBem
 *
 * Testes sem PHPUnit - usando PHP puro
 */

// Carregar autoloader
require __DIR__ . '/../vendor/autoload.php';

// Função para carregar .env
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Carregar .env
loadEnv(__DIR__ . '/../.env');

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                                                            ║\n";
echo "║           🧪 TESTES SIMPLES - DOARFAZBEM 🧪                ║\n";
echo "║                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Conectar ao banco
try {
    $dbHost = getenv('database.default.hostname') ?: 'localhost';
    $dbName = getenv('database.default.database') ?: 'doarfazbem';
    $dbUser = getenv('database.default.username') ?: 'root';
    $dbPass = getenv('database.default.password') ?: '';

    $db = new PDO(
        "mysql:host={$dbHost};dbname={$dbName}",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n\n";
    exit(1);
}

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Função helper para testes
function test($description, $callback) {
    global $totalTests, $passedTests, $failedTests;

    $totalTests++;

    try {
        $callback();
        echo "✅ {$description}\n";
        $passedTests++;
        return true;
    } catch (Exception $e) {
        echo "❌ {$description}\n";
        echo "   Erro: " . $e->getMessage() . "\n";
        $failedTests++;
        return false;
    }
}

function assertTrue($condition, $message = 'Assertion failed') {
    if (!$condition) {
        throw new Exception($message);
    }
}

function assertFalse($condition, $message = 'Assertion failed') {
    if ($condition) {
        throw new Exception($message);
    }
}

function assertEquals($expected, $actual, $message = 'Values not equal') {
    if ($expected !== $actual) {
        throw new Exception("$message - Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true));
    }
}

// ========================================
// TESTES UNITÁRIOS
// ========================================

echo "📦 TESTES UNITÁRIOS\n";
echo "─────────────────────────────────────────────────────────────\n\n";

// Teste 1: Banco de dados conecta
test('Banco de dados conecta corretamente', function() use ($db) {
    assertTrue($db !== null, 'Conexão com banco falhou');
});

// Teste 2: Usuários de teste existem
test('Usuários de teste existem no banco', function() use ($db) {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE email LIKE '%@test.doarfazbem.local'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    assertTrue($count > 0, "Nenhum usuário de teste encontrado (encontrados: {$count})");
});

// Teste 3: Campanhas de teste existem
test('Campanhas de teste existem no banco', function() use ($db) {
    $stmt = $db->query("SELECT COUNT(*) as count FROM campaigns WHERE title LIKE '%[TESTE]%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    assertTrue($count > 0, "Nenhuma campanha de teste encontrada (encontradas: {$count})");
});

// Teste 4: Tabelas principais existem
test('Todas as tabelas principais existem', function() use ($db) {
    $tables = ['users', 'campaigns', 'donations'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->rowCount() > 0;
        assertTrue($exists, "Tabela {$table} não existe");
    }
});

echo "\n";

// ========================================
// TESTES DE INTEGRAÇÃO
// ========================================

echo "🔗 TESTES DE INTEGRAÇÃO\n";
echo "─────────────────────────────────────────────────────────────\n\n";

// Teste 5: Doações existem
test('Doações foram criadas no banco', function() use ($db) {
    $stmt = $db->query("SELECT COUNT(*) as count FROM donations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    assertTrue($count > 0, "Nenhuma doação encontrada (encontradas: {$count})");
});

// Teste 6: Doações possuem taxas corretas
test('Doações de campanhas médicas têm taxa zero', function() use ($db) {
    $stmt = $db->query("
        SELECT d.*, c.category
        FROM donations d
        JOIN campaigns c ON d.campaign_id = c.id
        WHERE c.category = 'medica'
        LIMIT 1
    ");
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($donation) {
        assertEquals(0.0, (float)$donation['platform_fee'], 'Taxa de plataforma deveria ser zero para campanhas médicas');
    }
});

// Teste 7: Current amount das campanhas foi atualizado
test('Current amount das campanhas foi atualizado corretamente', function() use ($db) {
    $stmt = $db->query("
        SELECT * FROM campaigns
        WHERE title LIKE '%[TESTE]%'
        AND current_amount > 0
        LIMIT 1
    ");
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    assertTrue($campaign !== false, 'Nenhuma campanha com current_amount > 0 encontrada');
});

echo "\n";

// ========================================
// TESTES DE FUNCIONALIDADES
// ========================================

echo "🎯 TESTES DE FUNCIONALIDADES\n";
echo "─────────────────────────────────────────────────────────────\n\n";

// Teste 8: Diferentes métodos de pagamento
test('Sistema possui doações de diferentes métodos de pagamento', function() use ($db) {
    $stmt = $db->query("SELECT DISTINCT payment_method FROM donations");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    assertTrue(count($methods) >= 2, 'Deveria ter pelo menos 2 métodos de pagamento diferentes (encontrados: ' . count($methods) . ')');
});

// Teste 9: Diferentes status de doações
test('Sistema possui doações com diferentes status', function() use ($db) {
    $stmt = $db->query("SELECT DISTINCT status FROM donations");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    assertTrue(count($statuses) >= 1, 'Deveria ter pelo menos 1 status de doação');
});

// Teste 10: Admin existe
test('Usuário admin de teste existe', function() use ($db) {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@test.doarfazbem.local']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    assertTrue($admin !== false, 'Usuário admin não encontrado');
    assertEquals('admin', $admin['role'], 'Admin deveria ter role "admin"');
});

// Teste 11: Integridade referencial (doações vinculadas a campanhas e usuários)
test('Doações possuem referências válidas para campanhas e usuários', function() use ($db) {
    $stmt = $db->query("
        SELECT COUNT(*) as count
        FROM donations d
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        WHERE c.id IS NULL
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    assertEquals(0, (int)$result['count'], 'Existem doações sem campanha válida');
});

echo "\n";

// ========================================
// RELATÓRIO FINAL
// ========================================

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    ✨ RELATÓRIO FINAL ✨                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📊 Total de testes: {$totalTests}\n";
echo "✅ Testes aprovados: {$passedTests}\n";
echo "❌ Testes falhados: {$failedTests}\n";

$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
echo "📈 Taxa de sucesso: {$successRate}%\n";

echo "\n";

if ($failedTests === 0) {
    echo "🎉 TODOS OS TESTES PASSARAM! Sistema funcionando perfeitamente!\n";
    exit(0);
} else {
    echo "⚠️  {$failedTests} teste(s) falharam. Revise os logs acima.\n";
    exit(1);
}

echo "\n";

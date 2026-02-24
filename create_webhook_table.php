<?php
/**
 * Script para criar tabela webhook_processed
 * Execute: php create_webhook_table.php
 */

// Carregar configurações do CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'doarfazbem',
];

// Tentar carregar do .env se existir
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    $config['hostname'] = $env['database.default.hostname'] ?? $config['hostname'];
    $config['username'] = $env['database.default.username'] ?? $config['username'];
    $config['password'] = $env['database.default.password'] ?? $config['password'];
    $config['database'] = $env['database.default.database'] ?? $config['database'];
}

try {
    $mysqli = new mysqli(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database']
    );

    if ($mysqli->connect_error) {
        throw new Exception('Erro de conexão: ' . $mysqli->connect_error);
    }

    $sql = "CREATE TABLE IF NOT EXISTS webhook_processed (
        id INT AUTO_INCREMENT PRIMARY KEY,
        webhook_id VARCHAR(255) NOT NULL,
        source VARCHAR(50) NOT NULL DEFAULT 'asaas',
        event_type VARCHAR(100) NULL,
        processed_at DATETIME NOT NULL,
        UNIQUE KEY uk_source_webhook_id (source, webhook_id),
        KEY idx_processed_at (processed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($mysqli->query($sql)) {
        echo "✓ Tabela webhook_processed criada com sucesso!\n";
    } else {
        throw new Exception('Erro ao criar tabela: ' . $mysqli->error);
    }

    // Verificar se tabela existe
    $result = $mysqli->query("SHOW TABLES LIKE 'webhook_processed'");
    if ($result->num_rows > 0) {
        echo "✓ Tabela verificada e existente no banco de dados.\n";
    }

    $mysqli->close();
    echo "\nPróximo passo: Configure as variáveis no .env\n";

} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

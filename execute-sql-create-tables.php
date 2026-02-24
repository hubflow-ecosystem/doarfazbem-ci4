<?php
/**
 * Script para executar SQL de criação de tabelas de notificação
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar config do CodeIgniter
$config = \Config\Database::connect();

$sql = file_get_contents(__DIR__ . '/create-notification-preferences-tables.sql');

// Separar por comandos
$commands = array_filter(array_map('trim', explode(';', $sql)));

echo "Executando SQL...\n\n";

foreach ($commands as $command) {
    if (empty($command) || strpos($command, '--') === 0 || strpos($command, 'SELECT') === 0) {
        continue;
    }

    try {
        $config->query($command);
        echo "✓ Comando executado com sucesso\n";
    } catch (\Exception $e) {
        // Ignorar erro de "coluna já existe" ou "tabela já existe"
        if (strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⚠ Já existe: " . substr($command, 0, 50) . "...\n";
        } else {
            echo "✗ Erro: " . $e->getMessage() . "\n";
            echo "  SQL: " . substr($command, 0, 100) . "...\n\n";
        }
    }
}

echo "\n✓ Processo concluído!\n";
echo "\nVerificando tabelas criadas:\n";

$tables = ['campaign_creator_preferences', 'admin_notification_preferences', 'campaign_milestones_notified'];
foreach ($tables as $table) {
    $result = $config->query("SHOW TABLES LIKE '$table'");
    if ($result->getNumRows() > 0) {
        echo "✓ $table - OK\n";
    } else {
        echo "✗ $table - NÃO ENCONTRADA\n";
    }
}

// Verificar colunas adicionadas em notification_preferences
echo "\nVerificando colunas em notification_preferences:\n";
$result = $config->query("SHOW COLUMNS FROM notification_preferences LIKE 'notify_campaign_%'");
foreach ($result->getResultArray() as $col) {
    echo "✓ " . $col['Field'] . "\n";
}

echo "\n";

<?php
/**
 * Script para adicionar campos birth_date e province na tabela users
 */

require_once __DIR__ . '/vendor/autoload.php';

$db = new mysqli('localhost', 'root', '', 'doarfazbem');

if ($db->connect_error) {
    die("Erro de conexão: " . $db->connect_error);
}

// Verificar se birth_date já existe
$result = $db->query("SHOW COLUMNS FROM users LIKE 'birth_date'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN birth_date DATE NULL AFTER cpf";
    if ($db->query($sql)) {
        echo "Campo birth_date adicionado com sucesso!\n";
    } else {
        echo "Erro ao adicionar birth_date: " . $db->error . "\n";
    }
} else {
    echo "Campo birth_date já existe.\n";
}

// Verificar se province já existe
$result = $db->query("SHOW COLUMNS FROM users LIKE 'province'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN province VARCHAR(100) NULL AFTER address_complement";
    if ($db->query($sql)) {
        echo "Campo province adicionado com sucesso!\n";
    } else {
        echo "Erro ao adicionar province: " . $db->error . "\n";
    }
} else {
    echo "Campo province já existe.\n";
}

// Mostrar estrutura final
echo "\nEstrutura da tabela users:\n";
$result = $db->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

$db->close();

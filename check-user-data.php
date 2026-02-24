<?php
/**
 * Script para verificar dados do usuário
 */

require_once __DIR__ . '/vendor/autoload.php';

$db = new mysqli('localhost', 'root', '', 'doarfazbem');

if ($db->connect_error) {
    die("Erro de conexão: " . $db->connect_error);
}

// Buscar usuário Cesar
$result = $db->query("SELECT id, name, email, cpf, phone, birth_date, postal_code, address, address_number, address_complement, province, city, state FROM users WHERE email = 'cmnegociosdigitais@gmail.com'");

if ($row = $result->fetch_assoc()) {
    echo "=== Dados do Usuário ===\n";
    foreach ($row as $key => $value) {
        echo "{$key}: " . ($value ?? 'NULL') . "\n";
    }

    // Verificar se perfil está completo
    $complete = !empty($row['cpf']) &&
                !empty($row['phone']) &&
                !empty($row['birth_date']) &&
                !empty($row['postal_code']) &&
                !empty($row['address']) &&
                !empty($row['address_number']) &&
                !empty($row['province']) &&
                !empty($row['city']) &&
                !empty($row['state']);

    echo "\n=== Status do Perfil ===\n";
    echo "Perfil Completo: " . ($complete ? 'SIM' : 'NÃO') . "\n";

    if (!$complete) {
        echo "\nCampos faltando:\n";
        if (empty($row['cpf'])) echo "- cpf\n";
        if (empty($row['phone'])) echo "- phone\n";
        if (empty($row['birth_date'])) echo "- birth_date\n";
        if (empty($row['postal_code'])) echo "- postal_code\n";
        if (empty($row['address'])) echo "- address\n";
        if (empty($row['address_number'])) echo "- address_number\n";
        if (empty($row['province'])) echo "- province (Bairro)\n";
        if (empty($row['city'])) echo "- city\n";
        if (empty($row['state'])) echo "- state\n";
    }
} else {
    echo "Usuário não encontrado\n";
}

$db->close();

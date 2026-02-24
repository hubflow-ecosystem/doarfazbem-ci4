<?php
// Teste de atualização de perfil
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

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

echo "=== TESTE DE ATUALIZAÇÃO DE PERFIL ===\n\n";

// Buscar o usuário Cesar
$result = $mysqli->query("SELECT * FROM users WHERE email = 'cmnegociosdigitais@gmail.com'");
$user = $result->fetch_assoc();

if (!$user) {
    echo "❌ Usuário não encontrado!\n";
    exit(1);
}

echo "Usuário encontrado:\n";
echo "  ID: {$user['id']}\n";
echo "  Nome: {$user['name']}\n";
echo "  Email: {$user['email']}\n";
echo "  Telefone atual: " . ($user['phone'] ?: 'NÃO CADASTRADO') . "\n\n";

// Tentar atualizar telefone
$newPhone = '11987654321';
echo "Tentando atualizar telefone para: $newPhone\n";

$stmt = $mysqli->prepare("UPDATE users SET phone = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('si', $newPhone, $user['id']);

if ($stmt->execute()) {
    echo "✅ Telefone atualizado com sucesso!\n";

    // Verificar
    $result = $mysqli->query("SELECT phone FROM users WHERE id = {$user['id']}");
    $updated = $result->fetch_assoc();
    echo "   Novo valor: {$updated['phone']}\n";
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

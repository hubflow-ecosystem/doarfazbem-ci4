<?php

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Verificando contas Asaas dos usuários...\n";
echo str_repeat("=", 60) . "\n\n";

$stmt = $db->query("
    SELECT
        u.id,
        u.name,
        u.email,
        aa.asaas_account_id,
        aa.asaas_wallet_id,
        COUNT(c.id) as campaigns_count
    FROM users u
    LEFT JOIN asaas_accounts aa ON u.id = aa.user_id
    LEFT JOIN campaigns c ON u.id = c.user_id
    GROUP BY u.id, u.name, u.email, aa.asaas_account_id, aa.asaas_wallet_id
    ORDER BY campaigns_count DESC
    LIMIT 10
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "User #{$user['id']}: {$user['name']} ({$user['email']})\n";
    echo "  Campanhas: {$user['campaigns_count']}\n";
    if ($user['asaas_account_id']) {
        echo "  Asaas Account: {$user['asaas_account_id']}\n";
        echo "  Asaas Wallet: " . ($user['asaas_wallet_id'] ?? 'NULL') . "\n";
        echo "  ✅ Tem conta Asaas\n";
    } else {
        echo "  ❌ SEM conta Asaas\n";
    }
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "Campanhas que podem receber doações:\n";
echo str_repeat("=", 60) . "\n\n";

$stmt2 = $db->query("
    SELECT
        c.id,
        c.title,
        c.status,
        u.name as creator_name,
        aa.asaas_account_id
    FROM campaigns c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN asaas_accounts aa ON u.id = aa.user_id
    WHERE c.status = 'active'
    ORDER BY c.id
");

$campaigns = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($campaigns as $camp) {
    echo "Campanha #{$camp['id']}: {$camp['title']}\n";
    echo "  Criador: {$camp['creator_name']}\n";
    echo "  Status: {$camp['status']}\n";
    if ($camp['asaas_account_id']) {
        echo "  ✅ Pode receber doações (tem conta Asaas)\n";
    } else {
        echo "  ❌ NÃO pode receber doações (sem conta Asaas)\n";
    }
    echo "\n";
}

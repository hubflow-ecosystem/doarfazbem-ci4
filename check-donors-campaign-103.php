<?php

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Doadores da campanha #103 (Creche):\n";
echo str_repeat("=", 60) . "\n\n";

$stmt = $db->query("
    SELECT
        id,
        user_id,
        donor_name,
        donor_email,
        amount,
        status
    FROM donations
    WHERE campaign_id = 103 AND status = 'received'
    ORDER BY created_at
");

$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total de doações: " . count($donations) . "\n\n";

foreach ($donations as $d) {
    echo "Doação #{$d['id']}\n";
    echo "  User ID: " . ($d['user_id'] ?? 'NULL') . "\n";
    echo "  Nome: {$d['donor_name']}\n";
    echo "  Email: " . ($d['donor_email'] ?? 'NULL') . "\n";
    echo "  Valor: R$ " . number_format($d['amount'], 2, ',', '.') . "\n";
    echo "\n";
}

// Contar doadores únicos (lógica atual - só email)
$stmt2 = $db->query("
    SELECT COUNT(DISTINCT donor_email) as count
    FROM donations
    WHERE campaign_id = 103 AND status = 'received'
    AND donor_email IS NOT NULL
");
$emailCount = $stmt2->fetch(PDO::FETCH_ASSOC)['count'];

echo "Doadores únicos (só email): {$emailCount}\n";

// Contar doadores únicos (correta - user_id OU email)
$stmt3 = $db->query("
    SELECT COUNT(DISTINCT
        CASE
            WHEN user_id IS NOT NULL THEN CONCAT('user_', user_id)
            ELSE CONCAT('email_', donor_email)
        END
    ) as count
    FROM donations
    WHERE campaign_id = 103 AND status = 'received'
");
$correctCount = $stmt3->fetch(PDO::FETCH_ASSOC)['count'];

echo "Doadores únicos (correto - user_id OU email): {$correctCount}\n";

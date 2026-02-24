<?php

require __DIR__ . '/vendor/autoload.php';

// Test via raw SQL only
$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Testando LÓGICA CENTRALIZADA\n";
echo str_repeat("=", 60) . "\n\n";

$campaignId = 103;

// SQL direto com a lógica centralizada
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_donations,
        SUM(amount) as total_amount,
        COUNT(DISTINCT
            CASE
                WHEN user_id IS NOT NULL THEN CONCAT('user_', user_id)
                WHEN donor_email IS NOT NULL AND donor_email != '' THEN CONCAT('email_', donor_email)
                ELSE NULL
            END
        ) as unique_donors
    FROM donations
    WHERE campaign_id = ? AND status = 'received'
");
$stmt->execute([$campaignId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Campanha #103 (Creche):\n\n";
echo "Total de Doações: {$result['total_donations']}\n";
echo "Valor Total: R$ " . number_format($result['total_amount'], 2, ',', '.') . "\n";
echo "Doadores Únicos: {$result['unique_donors']}\n\n";

// Listar doadores
echo "Detalhes dos doadores:\n";
echo str_repeat("-", 60) . "\n";

$stmt2 = $db->prepare("
    SELECT
        id,
        user_id,
        donor_name,
        donor_email,
        amount,
        CASE
            WHEN user_id IS NOT NULL THEN CONCAT('user_', user_id)
            WHEN donor_email IS NOT NULL AND donor_email != '' THEN CONCAT('email_', donor_email)
            ELSE NULL
        END as donor_identifier
    FROM donations
    WHERE campaign_id = ? AND status = 'received'
    ORDER BY created_at
");
$stmt2->execute([$campaignId]);
$donations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($donations as $d) {
    echo "Doação #{$d['id']}: ";
    if ($d['user_id']) {
        echo "User #{$d['user_id']} ({$d['donor_name']})";
    } elseif ($d['donor_email']) {
        echo "Email: {$d['donor_email']}";
    } else {
        echo "ANÔNIMO SEM IDENTIFICAÇÃO";
    }
    echo " - R$ " . number_format($d['amount'], 2, ',', '.') . "\n";
    echo "  Identificador: " . ($d['donor_identifier'] ?? 'NULL') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "CONCLUSÃO:\n";
echo "- Dr. Felipe (user_216) fez 2 doações = 1 doador único\n";
echo "- 3 doações anônimas sem email/user_id = NÃO CONTAM\n";
echo "- Total de doadores únicos: {$result['unique_donors']}\n";

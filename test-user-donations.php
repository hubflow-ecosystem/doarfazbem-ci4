<?php

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get admin user
$stmt = $db->query("SELECT id, email FROM users WHERE email = 'admin@test.doarfazbem.local'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário admin não encontrado!\n");
}

echo "Testando getUserDonations para: {$user['email']} (ID: {$user['id']})\n";
echo str_repeat("=", 60) . "\n\n";

// Simular getUserDonations (sem filtro de status)
$stmt2 = $db->prepare("
    SELECT donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug
    FROM donations
    JOIN campaigns ON campaigns.id = donations.campaign_id
    WHERE donations.user_id = ?
    ORDER BY donations.created_at DESC
");
$stmt2->execute([$user['id']]);
$donations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "Total de doações (sem filtrar status): " . count($donations) . "\n\n";

foreach ($donations as $donation) {
    echo "Doação #{$donation['id']}\n";
    echo "  Campanha: {$donation['campaign_title']}\n";
    echo "  Valor: R$ " . number_format($donation['amount'], 2, ',', '.') . "\n";
    echo "  Status: {$donation['status']}\n";
    echo "  Data: {$donation['created_at']}\n";
    echo "\n";
}

// Agora com filtro de status = 'received'
$stmt3 = $db->prepare("
    SELECT donations.*, campaigns.title as campaign_title
    FROM donations
    JOIN campaigns ON campaigns.id = donations.campaign_id
    WHERE donations.user_id = ? AND donations.status = 'received'
    ORDER BY donations.created_at DESC
");
$stmt3->execute([$user['id']]);
$receivedDonations = $stmt3->fetchAll(PDO::FETCH_ASSOC);

echo str_repeat("-", 60) . "\n";
echo "Total de doações RECEBIDAS: " . count($receivedDonations) . "\n";

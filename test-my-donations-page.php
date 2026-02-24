<?php

/**
 * Simula Dashboard::myDonations()
 */

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Simular usuário logado (admin)
$userId = 213;

echo "Simulando Dashboard::myDonations() para user ID {$userId}...\n";
echo str_repeat("=", 60) . "\n\n";

// 1. getUserDonations
$stmt = $db->prepare("
    SELECT donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug
    FROM donations
    JOIN campaigns ON campaigns.id = donations.campaign_id
    WHERE donations.user_id = ?
    ORDER BY donations.created_at DESC
");
$stmt->execute([$userId]);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "1. Doações encontradas: " . count($donations) . "\n\n";

if (count($donations) > 0) {
    foreach ($donations as $d) {
        echo "Doação #{$d['id']}\n";
        echo "  Campanha: {$d['campaign_title']}\n";
        echo "  Slug: {$d['campaign_slug']}\n";
        echo "  Valor: R$ " . number_format($d['amount'], 2, ',', '.') . "\n";
        echo "  Status: {$d['status']}\n";
        echo "  Método: {$d['payment_method']}\n";
        echo "  Data: {$d['created_at']}\n";
        echo "\n";
    }

    // Calcular total
    $total_donated = 0;
    foreach ($donations as $donation) {
        if (in_array($donation['status'], ['received', 'paid', 'confirmed'])) {
            $total_donated += (float)$donation['amount'];
        }
    }

    echo "Total Doado: R$ " . number_format($total_donated, 2, ',', '.') . "\n\n";

    // Preparar dados para JSON (como na view)
    $jsonData = array_map(function($d) {
        return [
            'id' => $d['id'],
            'campaign_title' => $d['campaign_title'],
            'campaign_slug' => $d['campaign_slug'],
            'amount' => (float)$d['amount'],
            'payment_method' => $d['payment_method'],
            'status' => $d['status'],
            'created_at' => $d['created_at'],
            'message' => $d['message'] ?? '',
            'is_anonymous' => $d['is_anonymous'] ?? false
        ];
    }, $donations);

    echo "JSON para Alpine.js:\n";
    echo json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";

} else {
    echo "Nenhuma doação encontrada!\n";
}

<?php

/**
 * Recalcula estatísticas de TODAS as campanhas usando método centralizado
 */

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Recalculando estatísticas de todas as campanhas...\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar todas as campanhas
$stmt = $db->query("SELECT id, title FROM campaigns ORDER BY id");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;

foreach ($campaigns as $campaign) {
    // Calcular estatísticas
    $statsStmt = $db->prepare("
        SELECT
            COUNT(*) as total_donations,
            SUM(amount) as total_amount
        FROM donations
        WHERE campaign_id = ? AND status = 'received'
    ");
    $statsStmt->execute([$campaign['id']]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    $totalDonations = $stats['total_donations'] ?? 0;
    $totalAmount = $stats['total_amount'] ?? 0;

    // Atualizar campanha
    $updateStmt = $db->prepare("
        UPDATE campaigns
        SET current_amount = ?,
            donors_count = ?
        WHERE id = ?
    ");
    $updateStmt->execute([$totalAmount, $totalDonations, $campaign['id']]);

    echo "Campanha #{$campaign['id']}: {$campaign['title']}\n";
    echo "  Valor: R$ " . number_format($totalAmount, 2, ',', '.') . "\n";
    echo "  Doações: {$totalDonations}\n";
    echo "  ✅ Atualizado\n\n";

    $updated++;
}

echo str_repeat("=", 60) . "\n";
echo "Total de campanhas atualizadas: {$updated}\n";

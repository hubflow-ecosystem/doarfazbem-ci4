<?php

/**
 * Sync Campaign Amounts with Actual Donations
 * Recalculates current_amount for all campaigns based on received donations
 */

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Sincronizando valores das campanhas...\n";
echo str_repeat("=", 60) . "\n\n";

// Get all campaigns with their donation totals
$stmt = $db->query("
    SELECT
        c.id,
        c.title,
        c.current_amount,
        COALESCE(SUM(d.amount), 0) as total_donated,
        COUNT(d.id) as donation_count
    FROM campaigns c
    LEFT JOIN donations d ON c.id = d.campaign_id AND d.status = 'received'
    GROUP BY c.id
    ORDER BY c.id
");

$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$updated = 0;
$discrepancies = 0;

foreach ($campaigns as $campaign) {
    $currentAmount = floatval($campaign['current_amount']);
    $totalDonated = floatval($campaign['total_donated']);

    echo "Campanha #{$campaign['id']}: {$campaign['title']}\n";
    echo "  Banco: R$ " . number_format($currentAmount, 2, ',', '.') . "\n";
    echo "  Real:  R$ " . number_format($totalDonated, 2, ',', '.') . "\n";
    echo "  Doações: {$campaign['donation_count']}\n";

    if (abs($currentAmount - $totalDonated) > 0.01) {
        echo "  ⚠️  DISCREPÂNCIA DETECTADA!\n";
        $discrepancies++;

        // Update the campaign
        $updateStmt = $db->prepare("
            UPDATE campaigns
            SET current_amount = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$totalDonated, $campaign['id']]);

        echo "  ✅ Atualizado para R$ " . number_format($totalDonated, 2, ',', '.') . "\n";
        $updated++;
    } else {
        echo "  ✅ OK\n";
    }

    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "RESUMO:\n";
echo "  Total de campanhas: " . count($campaigns) . "\n";
echo "  Discrepâncias encontradas: {$discrepancies}\n";
echo "  Campanhas atualizadas: {$updated}\n";
echo "\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking donation status distribution...\n\n";

$stmt = $db->query("
    SELECT status, COUNT(*) as count, SUM(amount) as total
    FROM donations
    GROUP BY status
");

echo "Status Distribution:\n";
echo str_repeat("-", 50) . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['status'], 15) . " | " .
         str_pad($row['count'] . " doações", 15) . " | " .
         "R$ " . number_format($row['total'], 2, ',', '.') . "\n";
}

echo "\n\nGlobal Stats Query (same as Admin Dashboard):\n";
echo str_repeat("-", 50) . "\n";

$stmt2 = $db->query("
    SELECT
        COUNT(*) as total_donations,
        SUM(amount) as total_amount,
        AVG(amount) as average_donation,
        COUNT(DISTINCT campaign_id) as campaigns_with_donations,
        SUM(CASE WHEN payment_method = 'pix' THEN 1 ELSE 0 END) as pix_count,
        SUM(CASE WHEN payment_method = 'credit_card' THEN 1 ELSE 0 END) as credit_card_count,
        SUM(CASE WHEN payment_method = 'boleto' THEN 1 ELSE 0 END) as boleto_count
    FROM donations
    WHERE status = 'received'
");

$stats = $stmt2->fetch(PDO::FETCH_ASSOC);

echo "Total Donations (received): " . $stats['total_donations'] . "\n";
echo "Total Amount: R$ " . number_format($stats['total_amount'], 2, ',', '.') . "\n";
echo "Average Donation: R$ " . number_format($stats['average_donation'], 2, ',', '.') . "\n";
echo "Campaigns with Donations: " . $stats['campaigns_with_donations'] . "\n";
echo "PIX: " . $stats['pix_count'] . " | Credit Card: " . $stats['credit_card_count'] . " | Boleto: " . $stats['boleto_count'] . "\n";

echo "\n";

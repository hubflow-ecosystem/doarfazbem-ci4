<?php

/**
 * Test DonationModel::getGlobalStats()
 */

require __DIR__ . '/vendor/autoload.php';

// Minimal CodeIgniter bootstrap
define('ENVIRONMENT', 'development');
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);

// Test via raw SQL first
$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Testing getGlobalStats() query...\n";
echo str_repeat("=", 60) . "\n\n";

$stmt = $db->query("
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

$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Raw SQL Results:\n";
echo "-----------------\n";
print_r($result);

echo "\n\nFormatted Stats:\n";
echo "-----------------\n";
echo "Total Donations: " . ($result['total_donations'] ?? 0) . "\n";
echo "Total Amount: R$ " . number_format($result['total_amount'] ?? 0, 2, ',', '.') . "\n";
echo "Average Donation: R$ " . number_format($result['average_donation'] ?? 0, 2, ',', '.') . "\n";
echo "Campaigns with Donations: " . ($result['campaigns_with_donations'] ?? 0) . "\n";
echo "PIX: " . ($result['pix_count'] ?? 0) . "\n";
echo "Credit Card: " . ($result['credit_card_count'] ?? 0) . "\n";
echo "Boleto: " . ($result['boleto_count'] ?? 0) . "\n";

echo "\n";

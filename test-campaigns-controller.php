<?php

/**
 * Test Campaign Controller Output
 */

require __DIR__ . '/vendor/autoload.php';

// Simulate CodeIgniter environment
define('ENVIRONMENT', 'development');
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

// Direct database query
$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Testing Campaign::index() output...\n";
echo str_repeat("=", 60) . "\n\n";

$today = date('Y-m-d');

$stmt = $db->query("
    SELECT *
    FROM campaigns
    WHERE status = 'active'
    AND end_date >= '{$today}'
    ORDER BY created_at DESC
    LIMIT 12
");

$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Campanhas retornadas: " . count($campaigns) . "\n\n";

if (count($campaigns) > 0) {
    // Add percentage
    foreach ($campaigns as &$campaign) {
        if ($campaign['goal_amount'] > 0) {
            $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
            $campaign['percentage'] = min($percentage, 100);
        } else {
            $campaign['percentage'] = 0;
        }
    }

    // Simulate what would be passed to view
    echo "JSON que seria passado para x-data:\n";
    echo str_repeat("-", 60) . "\n";

    $json = json_encode($campaigns);

    // Show first 500 chars
    echo substr($json, 0, 500) . "...\n\n";

    echo "Verificando se JSON é válido...\n";
    $decoded = json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON válido!\n";
    } else {
        echo "❌ JSON inválido: " . json_last_error_msg() . "\n";
    }

    echo "\nPrimeira campanha:\n";
    echo "  ID: " . $campaigns[0]['id'] . "\n";
    echo "  Título: " . $campaigns[0]['title'] . "\n";
    echo "  Status: " . $campaigns[0]['status'] . "\n";
    echo "  End Date: " . $campaigns[0]['end_date'] . "\n";
    echo "  Percentage: " . number_format($campaigns[0]['percentage'], 2) . "%\n";
}

echo "\n";

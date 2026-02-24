<?php

/**
 * Debug: Ver exatamente o que está sendo passado para a view
 */

require __DIR__ . '/vendor/autoload.php';

// Simular ambiente CodeIgniter
define('ENVIRONMENT', 'development');
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

// Simular query do CampaignModel
$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

echo "========================================\n";
echo "DEBUG: Dados passados para a view\n";
echo "========================================\n\n";

echo "Total de campanhas: " . count($campaigns) . "\n\n";

if (count($campaigns) > 0) {
    echo "JSON que seria passado para Alpine.js:\n";
    echo "=====================================\n";

    // Calcular percentage para cada campanha
    foreach ($campaigns as &$campaign) {
        if ($campaign['goal_amount'] > 0) {
            $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
            $campaign['percentage'] = min($percentage, 100);
        } else {
            $campaign['percentage'] = 0;
        }
    }

    $json = json_encode($campaigns, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo $json . "\n";
} else {
    echo "⚠️  Nenhuma campanha retornada!\n";
}

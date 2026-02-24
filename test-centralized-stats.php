<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
define('ENVIRONMENT', 'development');
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

// Load config
require SYSTEMPATH . 'Config/DotEnv.php';
$dotenv = new \CodeIgniter\Config\DotEnv(ROOTPATH);
$dotenv->load();

// Test via raw SQL (control)
$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Testando MÉTODO CENTRALIZADO vs SQL DIRETO\n";
echo str_repeat("=", 60) . "\n\n";

$campaignId = 103;

// SQL direto (controle)
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
$sqlResult = $stmt->fetch(PDO::FETCH_ASSOC);

echo "1. SQL DIRETO (controle):\n";
echo "   Total Doações: {$sqlResult['total_donations']}\n";
echo "   Total Amount: R$ " . number_format($sqlResult['total_amount'], 2, ',', '.') . "\n";
echo "   Doadores Únicos: {$sqlResult['unique_donors']}\n\n";

// Método centralizado (novo)
$campaignModel = new \App\Models\CampaignModel();
$campaign = $campaignModel->getCampaignWithStats($campaignId);

echo "2. MÉTODO CENTRALIZADO (getCampaignWithStats):\n";
echo "   Total Doações: {$campaign['total_donations']}\n";
echo "   Current Amount: R$ " . number_format($campaign['current_amount'], 2, ',', '.') . "\n";
echo "   Doadores Únicos: {$campaign['donors_count']}\n";
echo "   Percentage: " . number_format($campaign['percentage'], 2) . "%\n";
echo "   Days Left: " . round($campaign['days_left']) . "\n\n";

// Verificar consistência
$isConsistent = (
    $sqlResult['total_donations'] == $campaign['total_donations'] &&
    $sqlResult['total_amount'] == $campaign['current_amount'] &&
    $sqlResult['unique_donors'] == $campaign['donors_count']
);

echo str_repeat("=", 60) . "\n";
if ($isConsistent) {
    echo "✅ CONSISTENTE! Método centralizado retorna mesmos valores.\n";
} else {
    echo "❌ INCONSISTENTE! Valores divergem:\n";
    echo "   Doações: SQL={$sqlResult['total_donations']} vs Method={$campaign['total_donations']}\n";
    echo "   Amount: SQL={$sqlResult['total_amount']} vs Method={$campaign['current_amount']}\n";
    echo "   Doadores: SQL={$sqlResult['unique_donors']} vs Method={$campaign['donors_count']}\n";
}

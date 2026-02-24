<?php

/**
 * Test Campaign Index - Debug what controller returns
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap minimal CodeIgniter environment
define('ENVIRONMENT', 'development');
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

// Load Config
require APPPATH . 'Config/Paths.php';
$paths = new Config\Paths();

// Boot CodeIgniter
require SYSTEMPATH . 'bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

// Get CampaignModel
$campaignModel = new \App\Models\CampaignModel();

echo "========================================\n";
echo "Testing Campaign::index() Logic\n";
echo "========================================\n\n";

// Simulate what the index() method does
$perPage = 12;
$page = 1;
$category = null;
$search = null;

echo "Filters:\n";
echo "  - Per Page: $perPage\n";
echo "  - Page: $page\n";
echo "  - Category: " . ($category ?? 'none') . "\n";
echo "  - Search: " . ($search ?? 'none') . "\n\n";

// Get campaigns like controller does
if ($search) {
    $campaigns = $campaignModel->searchCampaigns($search, $perPage);
    $total = count($campaigns);
} elseif ($category) {
    $campaigns = $campaignModel->getCampaignsByCategory($category, $perPage, ($page - 1) * $perPage);
    $total = $campaignModel->where('category', $category)->where('status', 'active')->countAllResults();
} else {
    $campaigns = $campaignModel->getActiveCampaigns($perPage, ($page - 1) * $perPage);
    $total = $campaignModel->where('status', 'active')->countAllResults();
}

echo "Results:\n";
echo "  - Total matching campaigns: $total\n";
echo "  - Campaigns returned: " . count($campaigns) . "\n\n";

if (count($campaigns) > 0) {
    echo "Campaigns found:\n";
    echo str_pad("ID", 5) . " | " . str_pad("Title", 50) . " | " . str_pad("Status", 10) . " | End Date\n";
    echo str_repeat("-", 100) . "\n";

    foreach ($campaigns as $campaign) {
        echo str_pad($campaign['id'], 5) . " | " .
             str_pad(substr($campaign['title'], 0, 50), 50) . " | " .
             str_pad($campaign['status'], 10) . " | " .
             ($campaign['end_date'] ?? 'NULL') . "\n";
    }
} else {
    echo "⚠️  NO CAMPAIGNS RETURNED!\n\n";

    // Debug: Check what's in database
    echo "Debug: Checking database directly...\n";
    $db = \Config\Database::connect();
    $query = $db->query("
        SELECT id, title, status, end_date
        FROM campaigns
        WHERE status = 'active'
        AND end_date >= CURDATE()
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $results = $query->getResultArray();

    echo "Direct query found " . count($results) . " campaigns:\n";
    foreach ($results as $row) {
        echo "  - #{$row['id']}: {$row['title']} (status={$row['status']}, end_date={$row['end_date']})\n";
    }
}

echo "\n";

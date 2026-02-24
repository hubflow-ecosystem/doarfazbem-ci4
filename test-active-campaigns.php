<?php

/**
 * Test Active Campaigns Query
 */

require __DIR__ . '/vendor/autoload.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "========================================\n";
    echo "Testing getActiveCampaigns() Logic\n";
    echo "========================================\n\n";

    $today = date('Y-m-d');
    echo "Today's date: $today\n\n";

    // Exact query from CampaignModel::getActiveCampaigns()
    $stmt = $db->query("
        SELECT *
        FROM campaigns
        WHERE status = 'active'
        AND end_date >= '{$today}'
        ORDER BY created_at DESC
        LIMIT 12
    ");

    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Query Results:\n";
    echo "  Total campaigns found: " . count($campaigns) . "\n\n";

    if (count($campaigns) > 0) {
        echo "Campaigns:\n";
        echo str_pad("ID", 5) . " | " . str_pad("Title", 50) . " | " . str_pad("End Date", 12) . " | " . str_pad("Goal", 12) . " | Current\n";
        echo str_repeat("-", 110) . "\n";

        foreach ($campaigns as $c) {
            echo str_pad($c['id'], 5) . " | " .
                 str_pad(substr($c['title'], 0, 50), 50) . " | " .
                 str_pad($c['end_date'], 12) . " | " .
                 str_pad('R$ ' . number_format($c['goal_amount'], 2, ',', '.'), 12) . " | " .
                 'R$ ' . number_format($c['current_amount'], 2, ',', '.') . "\n";
        }
    } else {
        echo "⚠️  NO CAMPAIGNS FOUND!\n\n";

        echo "Debug: Checking all campaigns:\n";
        $stmt2 = $db->query("SELECT id, title, status, end_date FROM campaigns ORDER BY id");
        $all = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all as $row) {
            echo "  - #{$row['id']}: {$row['title']} (status={$row['status']}, end_date={$row['end_date']})\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

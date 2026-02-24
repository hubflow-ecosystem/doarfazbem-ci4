<?php

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking donations by user...\n\n";

// Buscar admin user ID
$stmt = $db->query("SELECT id FROM users WHERE email = 'admin@test.doarfazbem.local'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$adminId = $admin['id'] ?? 'NOT FOUND';

echo "Admin User ID: {$adminId}\n\n";

// Donations por user
$stmt2 = $db->query("
    SELECT user_id, COUNT(*) as count, SUM(amount) as total
    FROM donations
    GROUP BY user_id
    ORDER BY user_id
");

echo str_pad('User ID', 10) . " | " . str_pad('Count', 10) . " | Total\n";
echo str_repeat("-", 50) . "\n";

while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $userId = $row['user_id'] ?? 'NULL';
    $isAdmin = ($userId == $adminId) ? ' *** ADMIN ***' : '';

    echo str_pad($userId, 10) . " | " .
         str_pad($row['count'], 10) . " | " .
         "R$ " . number_format($row['total'], 2, ',', '.') .
         $isAdmin . "\n";
}

echo "\n\nDetalhes das doações do admin:\n";
echo str_repeat("-", 80) . "\n";

$stmt3 = $db->prepare("
    SELECT id, campaign_id, amount, payment_method, status, donor_name, donor_email, created_at
    FROM donations
    WHERE user_id = ?
");
$stmt3->execute([$adminId]);

if ($stmt3->rowCount() > 0) {
    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        echo "Donation #{$row['id']}: R$ {$row['amount']} - {$row['payment_method']} - {$row['status']}\n";
        echo "  Donor: {$row['donor_name']} ({$row['donor_email']})\n";
        echo "  Campaign: {$row['campaign_id']} | Created: {$row['created_at']}\n\n";
    }
} else {
    echo "Nenhuma doação associada ao admin.\n";
}

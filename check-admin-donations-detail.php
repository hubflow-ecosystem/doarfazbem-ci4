<?php

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Detalhes das doações do admin@test.doarfazbem.local:\n\n";

$stmt = $db->query("
    SELECT d.*, c.title as campaign_title
    FROM donations d
    JOIN campaigns c ON d.campaign_id = c.id
    WHERE d.user_id = (SELECT id FROM users WHERE email = 'admin@test.doarfazbem.local')
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Campanha: " . $row['campaign_title'] . "\n";
    echo "Valor: R$ " . number_format($row['amount'], 2, ',', '.') . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Método: " . $row['payment_method'] . "\n";
    echo "Data: " . $row['created_at'] . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

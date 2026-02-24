<?php

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$stmt = $db->query('SELECT id, title, current_amount, goal_amount FROM campaigns WHERE id = 105');
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Campanha #{$row['id']}: {$row['title']}\n";
echo "Current Amount: R$ " . number_format($row['current_amount'], 2, ',', '.') . "\n";
echo "Goal Amount: R$ " . number_format($row['goal_amount'], 2, ',', '.') . "\n";
echo "Percentage: " . number_format(($row['current_amount'] / $row['goal_amount']) * 100, 2) . "%\n";

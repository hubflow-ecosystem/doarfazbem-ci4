<?php

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');

echo "Buscando campanha 'Reforma de Creche'...\n\n";

$stmt = $db->query("SELECT id, slug, title, current_amount, goal_amount FROM campaigns WHERE title LIKE '%Creche%'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "ID: {$row['id']}\n";
    echo "Slug: {$row['slug']}\n";
    echo "Título: {$row['title']}\n";
    echo "Current Amount: R$ " . number_format($row['current_amount'], 2, ',', '.') . "\n";
    echo "Goal Amount: R$ " . number_format($row['goal_amount'], 2, ',', '.') . "\n\n";

    // Check donations
    $stmt2 = $db->prepare("SELECT COUNT(*) as total, SUM(net_amount) as sum_net, SUM(amount) as sum_amount FROM donations WHERE campaign_id = ? AND status = 'received'");
    $stmt2->execute([$row['id']]);
    $donations = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo "Doações com status='received':\n";
    echo "  Total: {$donations['total']}\n";
    echo "  Soma net_amount: R$ " . number_format($donations['sum_net'], 2, ',', '.') . "\n";
    echo "  Soma amount: R$ " . number_format($donations['sum_amount'], 2, ',', '.') . "\n";
} else {
    echo "Campanha não encontrada!\n";
}

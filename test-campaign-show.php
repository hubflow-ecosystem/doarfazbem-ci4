<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$slug = 'mantenha-a-plataforma-ativa';

echo "=== TESTE BUSCA CAMPANHA POR SLUG ===\n\n";

$result = $mysqli->query("SELECT * FROM campaigns WHERE slug = '$slug'");
$campaign = $result->fetch_assoc();

if ($campaign) {
    echo "✅ Campanha encontrada!\n";
    echo "  ID: " . $campaign['id'] . "\n";
    echo "  Title: " . $campaign['title'] . "\n";
    echo "  Status: " . $campaign['status'] . "\n";
    echo "  Image: " . ($campaign['image'] ?: 'vazio') . "\n";
    echo "  Campaign Type: " . $campaign['campaign_type'] . "\n";
} else {
    echo "❌ Campanha NÃO encontrada!\n";
}

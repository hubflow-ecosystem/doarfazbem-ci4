<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

// Atualizar campanha da plataforma para ter mínimo de R$ 5,00
$sql = "UPDATE campaigns
        SET min_donation_amount = 5.00
        WHERE slug = 'mantenha-a-plataforma-ativa'";

if ($mysqli->query($sql)) {
    echo "✅ Valor mínimo da campanha da plataforma atualizado para R$ 5,00!\n";
} else {
    echo "❌ Erro: " . $mysqli->error . "\n";
}

<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

// Verificar se já existe
$check = $mysqli->query("SELECT id FROM campaigns WHERE slug = 'mantenha-a-plataforma-ativa'");
if ($check->num_rows > 0) {
    echo "⚠️  Campanha já existe!\n";
    $campaign = $check->fetch_assoc();
    echo "ID: " . $campaign['id'] . "\n";
    exit(0);
}

$sql = "INSERT INTO campaigns (
    user_id, title, slug, description, category, campaign_type,
    goal_amount, current_amount, end_date, city, state, country,
    status, is_featured, is_urgent, views_count, donors_count,
    created_at, updated_at
) VALUES (
    1,
    'Mantenha a Plataforma DoarFazBem Ativa',
    'mantenha-a-plataforma-ativa',
    'A plataforma DoarFazBem conecta pessoas generosas a causas que transformam vidas. Não cobramos taxas de campanhas médicas, permitindo que 100% das doações cheguem a quem precisa de tratamento. Sua contribuição mantém nossa infraestrutura, servidores, segurança e desenvolvimento de novas funcionalidades para ajudar ainda mais pessoas.',
    'social',
    'flexivel',
    50000.00,
    0.00,
    '" . date('Y-m-d', strtotime('+1 year')) . "',
    'São Paulo',
    'SP',
    'Brasil',
    'active',
    1,
    0,
    0,
    0,
    NOW(),
    NOW()
)";

if ($mysqli->query($sql)) {
    $id = $mysqli->insert_id;
    echo "✅ Campanha da plataforma criada!\n";
    echo "ID: $id\n";
    echo "Slug: mantenha-a-plataforma-ativa\n";
} else {
    echo "❌ Erro: " . $mysqli->error . "\n";
}

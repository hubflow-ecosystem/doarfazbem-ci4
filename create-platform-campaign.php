<?php
/**
 * Script para criar a campanha oficial da plataforma DoarFazBem
 */

$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

// Verificar se já existe
$check = $mysqli->query("SELECT id FROM campaigns WHERE slug = 'mantenha-a-plataforma-ativa'");
if ($check->num_rows > 0) {
    echo "⚠️  Campanha da plataforma já existe!\n";
    $campaign = $check->fetch_assoc();
    echo "ID: " . $campaign['id'] . "\n";
    exit(0);
}

// Buscar usuário admin/plataforma (ID 1 geralmente é o admin)
$adminCheck = $mysqli->query("SELECT id, name, email FROM users WHERE id = 1");
if ($adminCheck->num_rows === 0) {
    echo "❌ Usuário admin não encontrado. Usando ID 1 mesmo assim.\n";
    $userId = 1;
} else {
    $admin = $adminCheck->fetch_assoc();
    $userId = $admin['id'];
    echo "✅ Admin encontrado: {$admin['name']} ({$admin['email']})\n";
}

// Dados da campanha
$data = [
    'user_id' => $userId,
    'title' => 'Mantenha a Plataforma DoarFazBem Ativa',
    'slug' => 'mantenha-a-plataforma-ativa',
    'description' => 'A plataforma DoarFazBem conecta pessoas generosas a causas que transformam vidas. Não cobramos taxas de campanhas médicas, permitindo que 100% das doações cheguem a quem precisa de tratamento. Sua contribuição mantém nossa infraestrutura, servidores, segurança e desenvolvimento de novas funcionalidades para ajudar ainda mais pessoas.',
    'category' => 'social',
    'campaign_type' => 'recorrente',
    'goal_amount' => 50000.00, // Meta de R$ 50.000
    'current_amount' => 0.00,
    'end_date' => date('Y-m-d', strtotime('+1 year')), // 1 ano
    'city' => 'São Paulo',
    'state' => 'SP',
    'country' => 'Brasil',
    'status' => 'active', // Status válido no ENUM
    'is_featured' => 1, // Destacada
    'is_urgent' => 0,
    'views_count' => 0,
    'donors_count' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$stmt = $mysqli->prepare("
    INSERT INTO campaigns (
        user_id, title, slug, description, category, campaign_type,
        goal_amount, current_amount, end_date, city, state, country,
        status, is_featured, is_urgent, views_count, donors_count,
        created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    'isssssddssssiiiisss',
    $data['user_id'],
    $data['title'],
    $data['slug'],
    $data['description'],
    $data['category'],
    $data['campaign_type'],
    $data['goal_amount'],
    $data['current_amount'],
    $data['end_date'],
    $data['city'],
    $data['state'],
    $data['country'],
    $data['status'],
    $data['is_featured'],
    $data['is_urgent'],
    $data['views_count'],
    $data['donors_count'],
    $data['created_at'],
    $data['updated_at']
);

if ($stmt->execute()) {
    $campaignId = $mysqli->insert_id;
    echo "\n✅ CAMPANHA DA PLATAFORMA CRIADA COM SUCESSO!\n";
    echo "   ID: $campaignId\n";
    echo "   Slug: mantenha-a-plataforma-ativa\n";
    echo "   URL: " . "http://localhost/doarfazbem/public/campaigns/mantenha-a-plataforma-ativa" . "\n";
} else {
    echo "❌ Erro ao criar campanha: " . $stmt->error . "\n";
    exit(1);
}

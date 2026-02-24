<?php

/**
 * Script de Simulação de Doações
 *
 * Simula doações de diferentes tipos (PIX, Boleto, Cartão)
 * em campanhas de teste
 *
 * Uso:
 * php tests/scripts/simulate-donations.php
 */

// Carregar autoloader
require __DIR__ . '/../../vendor/autoload.php';

// Função para carregar .env manualmente
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Carregar .env
loadEnv(__DIR__ . '/../../.env');

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                                                            ║\n";
echo "║       🧪 SIMULADOR DE DOAÇÕES - DOARFAZBEM 🧪             ║\n";
echo "║                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Conectar ao banco diretamente
try {
    $dbHost = getenv('database.default.hostname') ?: 'localhost';
    $dbName = getenv('database.default.database') ?: 'doarfazbem';
    $dbUser = getenv('database.default.username') ?: 'root';
    $dbPass = getenv('database.default.password') ?: '';

    $db = new PDO(
        "mysql:host={$dbHost};dbname={$dbName}",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Buscar campanhas de teste ativas
$stmt = $db->prepare("
    SELECT * FROM campaigns
    WHERE status = 'active'
    AND title LIKE '%[TESTE]%'
");
$stmt->execute();
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($campaigns)) {
    echo "❌ Nenhuma campanha de teste encontrada!\n";
    echo "   Execute primeiro: php spark db:seed TestCampaignsSeeder\n\n";
    exit(1);
}

// Buscar usuários de teste (doadores)
$stmt = $db->prepare("
    SELECT * FROM users
    WHERE email LIKE '%@test.doarfazbem.local'
    LIMIT 10
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "❌ Nenhum usuário de teste encontrado!\n";
    echo "   Execute primeiro: php spark db:seed TestUsersSeeder\n\n";
    exit(1);
}

echo "📊 Encontradas " . count($campaigns) . " campanhas de teste\n";
echo "👥 Encontrados " . count($users) . " usuários de teste\n\n";

$totalDonations = 20; // Número de doações a criar
$createdDonations = 0;

echo "🎯 Criando {$totalDonations} doações de teste...\n\n";

for ($i = 1; $i <= $totalDonations; $i++) {
    // Selecionar campanha aleatória
    $campaign = $campaigns[array_rand($campaigns)];

    // Selecionar usuário aleatório
    $user = $users[array_rand($users)];

    // Determinar método de pagamento baseado em porcentagem
    $rand = rand(1, 100);
    $method = 'pix';
    if ($rand <= 50) {
        $method = 'pix';
    } elseif ($rand <= 80) {
        $method = 'boleto';
    } else {
        $method = 'credit_card';
    }

    // Valor aleatório entre R$ 10 e R$ 500
    $amount = rand(10, 500);

    // Calcular taxas
    $platformFee = 0;
    $paymentGatewayFee = 0;

    if ($campaign['category'] !== 'medica') {
        // 1% para não-médicas
        $platformFee = $amount * 0.01;
    }

    // Taxa do gateway (simulação - Asaas cobra ~3.5% para cartão, 2.99% para boleto, 0.99% para PIX)
    if ($method === 'credit_card') {
        $paymentGatewayFee = $amount * 0.035;
    } elseif ($method === 'boleto') {
        $paymentGatewayFee = $amount * 0.0299;
    } else { // PIX
        $paymentGatewayFee = $amount * 0.0099;
    }

    // Valor líquido (o que sobra para a campanha)
    $netAmount = $amount - $platformFee - $paymentGatewayFee;

    // Status aleatório (maioria confirmado)
    // pending, confirmed, received, refunded
    $rand = rand(1, 100);
    if ($rand <= 85) {
        $status = 'received'; // 85% recebido (pago)
    } elseif ($rand <= 95) {
        $status = 'pending'; // 10% pendente
    } else {
        $status = 'refunded'; // 5% reembolsado
    }

    // Criar doação
    $isAnonymous = rand(1, 100) <= 20 ? 1 : 0;
    $donorName = $isAnonymous ? null : $user['name'];
    $donorEmail = $isAnonymous ? null : $user['email'];
    $message = rand(1, 100) <= 40 ? 'Mensagem de apoio de teste!' : null;
    $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
    $paidAt = ($status === 'received') ? $createdAt : null;

    $stmt = $db->prepare("
        INSERT INTO donations (
            campaign_id, user_id, donor_name, donor_email, amount,
            platform_fee, payment_gateway_fee, net_amount, payment_method,
            status, is_anonymous, message, paid_at, created_at, updated_at
        ) VALUES (
            :campaign_id, :user_id, :donor_name, :donor_email, :amount,
            :platform_fee, :payment_gateway_fee, :net_amount, :payment_method,
            :status, :is_anonymous, :message, :paid_at, :created_at, :updated_at
        )
    ");

    $stmt->execute([
        'campaign_id' => $campaign['id'],
        'user_id' => $isAnonymous ? null : $user['id'],
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'amount' => $amount,
        'platform_fee' => $platformFee,
        'payment_gateway_fee' => $paymentGatewayFee,
        'net_amount' => $netAmount,
        'payment_method' => $method,
        'status' => $status,
        'is_anonymous' => $isAnonymous,
        'message' => $message,
        'paid_at' => $paidAt,
        'created_at' => $createdAt,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $donationId = $db->lastInsertId();

    // Atualizar current_amount da campanha se recebido
    if ($status === 'received') {
        $stmt = $db->prepare("
            UPDATE campaigns
            SET current_amount = current_amount + :net_amount
            WHERE id = :campaign_id
        ");
        $stmt->execute([
            'net_amount' => $netAmount,
            'campaign_id' => $campaign['id']
        ]);
    }

    // Emojis para métodos de pagamento
    $methodEmoji = match($method) {
        'pix' => '💳',
        'boleto' => '🧾',
        'credit_card' => '💰',
        default => '💸'
    };

    // Emoji de status
    $statusEmoji = match($status) {
        'received' => '✅',
        'confirmed' => '✔️',
        'pending' => '⏳',
        'refunded' => '↩️',
        default => '❔'
    };

    $anonymousText = $isAnonymous ? '(Anônima)' : '';

    echo "{$methodEmoji} {$statusEmoji} Doação #{$donationId}: R$ {$amount} via {$method} {$anonymousText}\n";
    echo "   👤 " . ($donorName ?? 'Anônimo') . " → 🎯 {$campaign['title']}\n";

    if ($platformFee > 0) {
        echo "   💵 Taxa plataforma: R$ " . number_format($platformFee, 2, ',', '.') . "\n";
    }

    if ($paymentGatewayFee > 0) {
        echo "   🏦 Taxa gateway: R$ " . number_format($paymentGatewayFee, 2, ',', '.') . "\n";
    }

    echo "   💰 Valor líquido: R$ " . number_format($netAmount, 2, ',', '.') . "\n";

    echo "\n";

    $createdDonations++;
    usleep(100000); // 100ms de delay para não sobrecarregar
}

echo "✨ Total: {$createdDonations} doações criadas com sucesso!\n\n";

// Resumo
$stmt = $db->query("
    SELECT
        payment_method,
        status,
        COUNT(*) as count,
        SUM(amount) as total_bruto,
        SUM(net_amount) as total_liquido
    FROM donations
    GROUP BY payment_method, status
");
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 RESUMO POR MÉTODO E STATUS:\n";
foreach ($summary as $row) {
    $methodEmoji = match($row['payment_method']) {
        'pix' => '💳',
        'boleto' => '🧾',
        'credit_card' => '💰',
        default => '💸'
    };

    $statusEmoji = match($row['status']) {
        'received' => '✅',
        'confirmed' => '✔️',
        'pending' => '⏳',
        'refunded' => '↩️',
        default => '❔'
    };

    echo "{$methodEmoji} {$statusEmoji} {$row['payment_method']} - {$row['status']}: ";
    echo "{$row['count']} doações - R$ " . number_format($row['total_bruto'], 2, ',', '.') . " (Líquido: R$ " . number_format($row['total_liquido'], 2, ',', '.') . ")\n";
}

echo "\n✅ Simulação concluída!\n\n";

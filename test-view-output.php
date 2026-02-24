<?php

/**
 * Test what the view would output
 */

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$today = date('Y-m-d');

$stmt = $db->query("
    SELECT *
    FROM campaigns
    WHERE status = 'active'
    AND end_date >= '{$today}'
    ORDER BY created_at DESC
    LIMIT 12
");

$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Adicionar percentage
foreach ($campaigns as &$campaign) {
    if ($campaign['goal_amount'] > 0) {
        $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
        $campaign['percentage'] = min($percentage, 100);
    } else {
        $campaign['percentage'] = 0;
    }
}

echo "Testando json_encode()...\n";
echo "==========================\n\n";

$json = json_encode($campaigns);

if ($json === false) {
    echo "❌ ERRO ao fazer json_encode()!\n";
    echo "Erro: " . json_last_error_msg() . "\n";
} else {
    echo "✅ JSON gerado com sucesso!\n";
    echo "Tamanho: " . strlen($json) . " bytes\n\n";

    // Verificar se tem caracteres inválidos
    if (json_decode($json) === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON inválido!\n";
        echo "Erro: " . json_last_error_msg() . "\n";
    } else {
        echo "✅ JSON válido!\n\n";

        // Mostrar primeiros 500 caracteres
        echo "Primeiros 500 caracteres do JSON:\n";
        echo substr($json, 0, 500) . "...\n";
    }
}

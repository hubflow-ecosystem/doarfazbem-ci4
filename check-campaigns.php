<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Verificando campanhas de teste...\n\n";

    $stmt = $db->query("
        SELECT id, title, status, end_date, DATE(created_at) as created
        FROM campaigns
        WHERE title LIKE '%[TESTE]%'
        ORDER BY id
    ");

    echo str_pad("ID", 5) . " | " . str_pad("Título", 45) . " | " . str_pad("Status", 10) . " | " . str_pad("End Date", 12) . " | Created\n";
    echo str_repeat("-", 110) . "\n";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo str_pad($row['id'], 5) . " | " .
             str_pad(substr($row['title'], 0, 45), 45) . " | " .
             str_pad($row['status'], 10) . " | " .
             str_pad($row['end_date'] ?? 'NULL', 12) . " | " .
             $row['created'] . "\n";
    }

    echo "\n\nVerificando filtro de data atual...\n";
    echo "Data de hoje: " . date('Y-m-d') . "\n\n";

    $stmt2 = $db->query("
        SELECT COUNT(*) as total
        FROM campaigns
        WHERE title LIKE '%[TESTE]%'
        AND status = 'active'
        AND end_date >= '" . date('Y-m-d') . "'
    ");

    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "Campanhas de teste com status='active' e end_date >= hoje: " . $result['total'] . "\n";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

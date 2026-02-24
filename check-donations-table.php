<?php

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Estrutura da tabela donations:\n";
echo str_repeat("=", 80) . "\n";

$stmt = $db->query('DESCRIBE donations');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    printf("%-30s | %-20s | %-5s\n", $col['Field'], $col['Type'], $col['Null']);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Total de colunas: " . count($columns) . "\n";

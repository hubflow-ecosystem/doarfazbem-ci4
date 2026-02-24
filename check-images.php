<?php

require __DIR__ . '/vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=doarfazbem', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking campaign images and slugs...\n\n";

$stmt = $db->query("
    SELECT id, title, image, slug
    FROM campaigns
    WHERE status = 'active'
    AND end_date >= CURDATE()
    ORDER BY id
");

echo str_pad("ID", 5) . " | " . str_pad("Image File", 30) . " | Slug\n";
echo str_repeat("-", 100) . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['id'], 5) . " | " .
         str_pad($row['image'] ?? 'NULL', 30) . " | " .
         ($row['slug'] ?? 'NULL') . "\n";
}

echo "\n";

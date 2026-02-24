<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query('DESCRIBE donations');
echo "Colunas relacionadas a PIX/Boleto/Asaas na tabela donations:\n\n";
while ($row = $result->fetch_assoc()) {
    if (strpos($row['Field'], 'pix') !== false ||
        strpos($row['Field'], 'boleto') !== false ||
        strpos($row['Field'], 'asaas') !== false) {
        echo "  ✓ " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n\nVerificando quais colunas necessárias faltam:\n";
$needed = ['pix_qr_code', 'pix_copy_paste', 'boleto_url', 'boleto_barcode', 'asaas_payment_id'];
$result = $mysqli->query('DESCRIBE donations');
$existing = [];
while ($row = $result->fetch_assoc()) {
    $existing[] = $row['Field'];
}

foreach ($needed as $col) {
    if (!in_array($col, $existing)) {
        echo "  ❌ FALTANDO: $col\n";
    } else {
        echo "  ✅ OK: $col\n";
    }
}

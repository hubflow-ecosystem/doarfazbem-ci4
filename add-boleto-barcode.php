<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

if ($mysqli->connect_error) {
    die("❌ Erro de conexão: " . $mysqli->connect_error);
}

$sql = "ALTER TABLE donations ADD COLUMN boleto_barcode VARCHAR(100) NULL AFTER boleto_url";

if ($mysqli->query($sql)) {
    echo "✅ Coluna boleto_barcode adicionada com sucesso!\n";
} else {
    echo "❌ Erro: " . $mysqli->error . "\n";
}

$mysqli->close();

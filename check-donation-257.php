<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query('SELECT id, amount, charged_amount, platform_fee, payment_gateway_fee, net_amount, donor_pays_fees, payment_method FROM donations WHERE id = 257');
if ($row = $result->fetch_assoc()) {
    echo "Doação 257:\n";
    foreach ($row as $k => $v) {
        echo "  $k: $v\n";
    }
}

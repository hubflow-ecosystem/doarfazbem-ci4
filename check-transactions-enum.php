<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query("SHOW COLUMNS FROM transactions LIKE 'status'");
if ($result && $row = $result->fetch_assoc()) {
    echo "Transactions status ENUM: " . $row['Type'] . "\n";
} else {
    echo "Table transactions does not exist or has no status column\n";
}

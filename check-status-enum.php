<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query("SHOW COLUMNS FROM donations LIKE 'status'");
$row = $result->fetch_assoc();
echo "Status ENUM values: " . $row['Type'] . "\n";

<?php
// Direct MySQL connection
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("DELETE FROM raffle_special_prizes WHERE raffle_id = 1");

if ($result) {
    echo "✓ Deleted all special prizes (cotas premiadas)\n";
    echo "Affected rows: " . $mysqli->affected_rows . "\n";
} else {
    echo "Error: " . $mysqli->error . "\n";
}

$mysqli->close();

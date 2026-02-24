<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
echo "Campaigns table structure:\n";
$result = $mysqli->query('DESCRIBE campaigns');
while($row = $result->fetch_assoc()) {
    echo "  " . $row['Field'] . " | " . $row['Type'] . "\n";
}

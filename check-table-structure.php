<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query('DESCRIBE raffle_special_prizes');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

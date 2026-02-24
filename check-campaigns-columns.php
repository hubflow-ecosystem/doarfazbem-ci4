<?php
$mysqli = new mysqli('localhost', 'root', '', 'doarfazbem');
$result = $mysqli->query('DESCRIBE campaigns');
echo "Colunas existentes:\n";
while ($row = $result->fetch_assoc()) {
    if (stripos($row['Field'], 'min') !== false || stripos($row['Field'], 'goal') !== false) {
        echo "  " . $row['Field'] . "\n";
    }
}

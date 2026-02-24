<?php
$db = new mysqli('localhost', 'root', '', 'doarfazbem');
$db->query("UPDATE users SET province = 'Fazenda' WHERE id = 238");
echo $db->affected_rows > 0 ? "Bairro atualizado para 'Fazenda'" : "Erro: " . $db->error;
$db->close();

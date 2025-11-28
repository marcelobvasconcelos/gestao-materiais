<?php
require_once 'config.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, nome FROM empresas_terceirizadas LIMIT 5");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - " . $row['nome'] . "\n";
}
?>

<?php
require_once 'config.php';
$conn = getDbConnection();
$result = $conn->query("SHOW CREATE TABLE materiais");
$row = $result->fetch_assoc();
echo $row['Create Table'];
?>

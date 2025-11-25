<?php
// Limpar qualquer sessão PHP se existir
session_start();
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
?>
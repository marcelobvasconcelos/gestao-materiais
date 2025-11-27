<?php
require_once 'config.php';

// Simulate session as Admin
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1;
$_SESSION['empresas_permitidas'] = 'ALL';

// Target User ID (picked from previous run)
$target_id = 8; 

echo "Testing Update on User ID: " . $target_id . "\n";

// 2. Update the user
ob_start();

$_GET['tipo'] = 'usuarios';
$_GET['acao'] = 'atualizar';

// Prepare input data
$dados = [
    'id' => $target_id,
    'nome' => 'Marcelo (Updated)',
    'email' => 'marcelo_gestor@gmail.com',
    'perfil_id' => 2,
    'departamento' => 'asdf',
    'empresas_vinculadas' => []
];

include 'api_filtrada.php';
$output = ob_get_clean();

echo "Raw Output:\n";
echo $output;
?>

<?php
require_once 'config.php';

// Simulate session as Admin
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1;
$_SESSION['empresas_permitidas'] = 'ALL';

// Mock input data for update
$input_data = [
    'id' => 2, // Assuming user 2 exists (Gestor)
    'nome' => 'Gestor Teste',
    'email' => 'gestor@teste.com',
    'perfil_id' => 2,
    'departamento' => 'TI',
    'empresas_vinculadas' => []
];

// Capture output
ob_start();

// Set API parameters
$_GET['tipo'] = 'usuarios';
$_GET['acao'] = 'atualizar';

// Mock file_get_contents('php://input')
// Since we can't easily mock php://input for include, we'll need to modify api_filtrada.php temporarily or rely on the fact that api_filtrada.php reads from $dados variable if we set it?
// Wait, api_filtrada.php likely does $dados = json_decode(file_get_contents('php://input'), true);
// Let's check how $dados is populated.
// If it reads php://input, we might need a different approach or just inject $dados if the script allows.
// Let's check the beginning of api_filtrada.php first.

// For now, I'll assume I can't easily mock php://input in a simple include without a wrapper.
// I'll check api_filtrada.php start first.
?>

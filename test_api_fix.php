<?php
require_once 'config.php';

// Simulate session
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1;
$_SESSION['empresas_permitidas'] = 'ALL';

// Capture output
ob_start();

// 1. Get a valid material ID
$_GET['tipo'] = 'materiais';
$_GET['acao'] = 'listar';
include 'api_filtrada.php';
$list_output = ob_get_clean();
$list_data = json_decode($list_output, true);

if ($list_data['sucesso'] && count($list_data['dados']) > 0) {
    $material_id = $list_data['dados'][0]['id'];
    echo "Testing with Material ID: " . $material_id . "\n";
    
    // 2. Test estoque_por_local
    ob_start();
    $_GET['tipo'] = 'materiais';
    $_GET['acao'] = 'estoque_por_local';
    $_GET['material_id'] = $material_id;
    include 'api_filtrada.php';
    $output = ob_get_clean();
    echo $output;
} else {
    echo "No materials found to test.";
}
?>

<?php
require_once 'config.php';

// Simulate session
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1;
$_SESSION['empresas_permitidas'] = 'ALL';

// Test estoque_por_local with ID 25
$_GET['tipo'] = 'materiais';
$_GET['acao'] = 'estoque_por_local';
$_GET['material_id'] = 25;

include 'api_filtrada.php';
?>

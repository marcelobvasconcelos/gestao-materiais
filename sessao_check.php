<?php
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Conectar ao banco
require_once 'config.php';

// Conectar ao banco
try {
    $conn = getDbConnection();
} catch (Exception $e) {
    die('Erro de conexão');
}

// Carregar dados do usuário e empresas vinculadas
$stmt = $conn->prepare('SELECT u.*, p.nome as perfil_nome FROM usuarios u LEFT JOIN perfis_acesso p ON u.perfil_id = p.id WHERE u.id = ?');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario || $usuario['ativo'] != 1) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Definir empresas permitidas baseado no perfil
if ($usuario['perfil_id'] == 1) { // Administrador
    $_SESSION['empresas_permitidas'] = 'ALL';
} else {
    // Buscar empresas vinculadas
    $stmt = $conn->prepare('SELECT empresa_id FROM usuarios_empresas WHERE usuario_id = ?');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $empresas = [];
    while ($row = $result->fetch_assoc()) {
        $empresas[] = $row['empresa_id'];
    }
    
    // Se não tem empresas vinculadas, usar JSON da tabela usuarios
    if (empty($empresas) && !empty($usuario['empresas_vinculadas'])) {
        $empresas = json_decode($usuario['empresas_vinculadas'], true) ?: [];
    }
    
    $_SESSION['empresas_permitidas'] = $empresas;
}

$_SESSION['usuario_perfil'] = $usuario['perfil_id'];
$_SESSION['usuario_nome'] = $usuario['nome'];

$conn->close();

// Função para aplicar filtro de empresa nas queries
function aplicarFiltroEmpresa($query, $alias = '') {
    if ($_SESSION['empresas_permitidas'] === 'ALL') {
        return $query;
    }
    
    if (empty($_SESSION['empresas_permitidas'])) {
        return $query . " AND 1=0"; // Bloquear tudo se não tem empresas
    }
    
    $empresas_str = implode(',', array_map('intval', $_SESSION['empresas_permitidas']));
    $campo_empresa = $alias ? $alias . '.empresa_id' : 'empresa_id';
    
    return $query . " AND $campo_empresa IN ($empresas_str)";
}
?>
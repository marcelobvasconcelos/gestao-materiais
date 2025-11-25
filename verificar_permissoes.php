<?php
// ============================================================================
// VERIFICAÇÃO DE PERMISSÕES - Sistema de Gestão de Materiais
// ============================================================================

function verificarPermissaoAdmin() {
    session_start();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    // Verificar se é administrador (perfil_id = 1)
    if ($_SESSION['perfil_id'] != 1) {
        return false;
    }
    
    return true;
}

function verificarPermissao($acao) {
    session_start();
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    $perfil_id = $_SESSION['perfil_id'];
    
    // Administrador tem todas as permissões
    if ($perfil_id == 1) {
        return true;
    }
    
    // Definir permissões por perfil
    $permissoes = [
        2 => ['criar', 'editar', 'relatorios'], // Gestor
        3 => ['criar'], // Operador
        4 => ['consultar'] // Consulta
    ];
    
    return isset($permissoes[$perfil_id]) && in_array($acao, $permissoes[$perfil_id]);
}
?>
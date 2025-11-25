<?php
require_once 'config.php';

echo "=== DEBUG RELATÓRIO MOVIMENTAÇÕES (COM FILTRO) ===\n";
$conn = getDbConnection();

// Função copiada de api_filtrada.php
function aplicarFiltroEmpresa($query, $alias = '', $coluna = 'empresa_id') {
    if (!isset($_SESSION['empresas_permitidas']) || $_SESSION['empresas_permitidas'] === 'ALL') {
        return $query;
    }
    
    if (empty($_SESSION['empresas_permitidas'])) {
        return $query . " AND 1=0";
    }
    
    $empresas_str = implode(',', array_map('intval', $_SESSION['empresas_permitidas']));
    $campo_empresa = $alias ? $alias . '.' . $coluna : $coluna;
    
    return $query . " AND $campo_empresa IN ($empresas_str)";
}

// Simular Sessão de Gestor (Marcelo - ID 8)
// Empresas: Lua - Limpeza (1), Teste (8)
$_SESSION['empresas_permitidas'] = [1, 8];
echo "Simulando Gestor (Empresas: 1, 8)...\n";

$periodo = 30;
$data_inicio = date('Y-m-d', strtotime("-$periodo days"));
echo "Data Início: $data_inicio\n";

// 1. Testar Query de Entradas
echo "\n--- Query Entradas ---\n";
$query_ent = "SELECT 'Entrada' as tipo, me.data_entrada as data, m.nome as material, e.nome as empresa, me.quantidade, u.nome as responsavel
          FROM movimentacoes_entrada me
          JOIN materiais m ON me.material_id = m.id
          LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
          LEFT JOIN usuarios u ON me.responsavel_id = u.id
          WHERE me.data_entrada >= '$data_inicio'";

$query_ent = aplicarFiltroEmpresa($query_ent, 'm'); 

echo "SQL: $query_ent\n";
$result = $conn->query($query_ent);
if ($result) {
    echo "Linhas retornadas: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Erro SQL: " . $conn->error . "\n";
}

// 2. Testar Query de Saídas
echo "\n--- Query Saídas ---\n";
$query_sai = "SELECT 'Saída' as tipo, ms.data_saida as data, m.nome as material, e.nome as empresa, ms.quantidade, '-' as responsavel
          FROM movimentacoes_saida ms
          JOIN materiais m ON ms.material_id = m.id
          LEFT JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
          WHERE ms.data_saida >= '$data_inicio'";

// Filtro igual ao da API
$query_sai = aplicarFiltroEmpresa($query_sai, 'm'); 

echo "SQL: $query_sai\n";
$result = $conn->query($query_sai);
if ($result) {
    echo "Linhas retornadas: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Erro SQL: " . $conn->error . "\n";
}
?>

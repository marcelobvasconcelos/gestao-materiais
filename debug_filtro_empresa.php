<?php
require_once 'config.php';

echo "=== DEBUG FILTRO EMPRESA ===\n";
$conn = getDbConnection();

// Simular Sessão de Admin
$_SESSION['empresas_permitidas'] = 'ALL';

// Parâmetros de teste
$empresa_id = 1; // Lua - Limpeza (ID conhecido)
$periodo = 30;
$data_inicio = date('Y-m-d', strtotime("-$periodo days"));

echo "Testando filtro para Empresa ID: $empresa_id\n";
echo "Data Início: $data_inicio\n";

// 1. Query Entradas com Filtro
echo "\n--- Query Entradas (Filtrada) ---\n";
$query_ent = "SELECT 'Entrada' as tipo, me.data_entrada as data, m.nome as material, e.nome as empresa, me.quantidade
          FROM movimentacoes_entrada me
          JOIN materiais m ON me.material_id = m.id
          LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
          WHERE me.data_entrada >= '$data_inicio'";

if ($empresa_id) $query_ent .= " AND m.empresa_id = " . intval($empresa_id);

echo "SQL: $query_ent\n";
$result = $conn->query($query_ent);
if ($result) {
    echo "Linhas retornadas: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo " - " . $row['material'] . " (" . $row['empresa'] . ")\n";
    }
}

// 2. Query Saídas com Filtro
echo "\n--- Query Saídas (Filtrada) ---\n";
$query_sai = "SELECT 'Saída' as tipo, ms.data_saida as data, m.nome as material, e.nome as empresa, ms.quantidade
          FROM movimentacoes_saida ms
          JOIN materiais m ON ms.material_id = m.id
          LEFT JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
          WHERE ms.data_saida >= '$data_inicio'";

if ($empresa_id) $query_sai .= " AND ms.empresa_solicitante_id = " . intval($empresa_id);

echo "SQL: $query_sai\n";
$result = $conn->query($query_sai);
if ($result) {
    echo "Linhas retornadas: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo " - " . $row['material'] . " (" . $row['empresa'] . ")\n";
    }
}
?>

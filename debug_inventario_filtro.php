<?php
require_once 'config.php';

echo "=== DEBUG INVENTÁRIO FILTRO ===\n";
$conn = getDbConnection();

// Simular Sessão de Admin
$_SESSION['empresas_permitidas'] = 'ALL';

$empresa_id = 1; // ID de teste

echo "Testando filtro para Empresa ID: $empresa_id\n";

$query = "SELECT m.id, m.nome, m.codigo_sku, e.nome as empresa, c.nome as categoria, 
                 m.estoque_atual, m.unidade_medida_id, l.nome as local,
                 m.valor_unitario, (m.estoque_atual * m.valor_unitario) as valor_total
          FROM materiais m
          LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
          LEFT JOIN categorias_materiais c ON m.categoria_id = c.id
          LEFT JOIN locais_armazenamento l ON m.local_id = l.id
          WHERE m.ativo = 1";

// Simular aplicarFiltroEmpresa (que adiciona restrições baseadas na sessão)
// Como é ALL, não adiciona nada ou adiciona 1=1
// Mas vamos ver a implementação real se possível, ou assumir que funciona como nos outros endpoints.
// Vou copiar a lógica do api_filtrada.php para ter certeza.

function aplicarFiltroEmpresaSimulado($query, $alias_tabela) {
    if (!isset($_SESSION['empresas_permitidas'])) {
        return $query . " AND 1=0"; // Bloqueia tudo
    }
    
    $permitidas = $_SESSION['empresas_permitidas'];
    
    if ($permitidas === 'ALL') {
        return $query;
    } else if (is_array($permitidas) && count($permitidas) > 0) {
        $ids = implode(',', array_map('intval', $permitidas));
        return $query . " AND {$alias_tabela}.empresa_id IN ($ids)";
    } else {
        return $query . " AND 1=0";
    }
}

$query = aplicarFiltroEmpresaSimulado($query, 'm');

if ($empresa_id) {
    $query .= " AND m.empresa_id = " . intval($empresa_id);
}

$query .= " ORDER BY e.nome, m.nome";

echo "SQL Gerado: $query\n\n";

$result = $conn->query($query);

if ($result) {
    echo "Sucesso! Linhas retornadas: " . $result->num_rows . "\n";
    $dados = $result->fetch_all(MYSQLI_ASSOC);
    print_r(array_slice($dados, 0, 2)); // Mostrar 2 primeiros
} else {
    echo "Erro na query: " . $conn->error . "\n";
}
?>

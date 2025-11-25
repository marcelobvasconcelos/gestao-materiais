<?php
header('Content-Type: application/json');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        throw new Exception('Conexão falhou');
    }
    
    // Verificar estrutura da tabela materiais
    $result = $conn->query("DESCRIBE materiais");
    $estrutura_materiais = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Verificar estrutura da tabela empresas_terceirizadas
    $result = $conn->query("DESCRIBE empresas_terceirizadas");
    $estrutura_empresas = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Testar a consulta exata da API
    $query = "SELECT m.id, m.nome, m.codigo_sku, m.estoque_atual, e.nome as empresa_nome FROM materiais m LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id WHERE m.ativo = 1 LIMIT 5";
    
    $result = $conn->query($query);
    $teste_consulta = [];
    $erro_consulta = null;
    
    if ($result) {
        $teste_consulta = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $erro_consulta = $conn->error;
    }
    
    echo json_encode([
        'estrutura_materiais' => $estrutura_materiais,
        'estrutura_empresas' => $estrutura_empresas,
        'query_testada' => $query,
        'resultado_query' => $teste_consulta,
        'erro_query' => $erro_consulta
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
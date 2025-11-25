<?php
// Script para testar a geração de SKU
header('Content-Type: application/json');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        throw new Exception('Conexão falhou: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    
    // Testar se as tabelas existem
    $result = $conn->query("SHOW TABLES LIKE 'categorias_materiais'");
    if ($result->num_rows == 0) {
        echo json_encode(['erro' => 'Tabela categorias_materiais não existe']);
        exit;
    }
    
    $result = $conn->query("SHOW TABLES LIKE 'empresas_terceirizadas'");
    if ($result->num_rows == 0) {
        echo json_encode(['erro' => 'Tabela empresas_terceirizadas não existe']);
        exit;
    }
    
    // Testar dados
    $result = $conn->query("SELECT id, nome FROM categorias_materiais LIMIT 5");
    $categorias = $result->fetch_all(MYSQLI_ASSOC);
    
    $result = $conn->query("SELECT id, nome FROM empresas_terceirizadas LIMIT 5");
    $empresas = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'sucesso' => true,
        'categorias' => $categorias,
        'empresas' => $empresas,
        'conexao' => 'OK'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
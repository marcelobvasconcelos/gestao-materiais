<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = 'localhost';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

try {
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset('utf8mb4');
    
    if ($conn->connect_error) {
        throw new Exception('Conexão falhou: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}

$tipo = $_GET['tipo'] ?? '';
$acao = $_GET['acao'] ?? '';
$dados = json_decode(file_get_contents('php://input'), true);

// TESTE
if ($tipo === 'teste') {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Conexão OK!']);
    exit;
}

// EMPRESAS
if ($tipo === 'empresas' && $acao === 'criar') {
    try {
        $stmt = $conn->prepare('INSERT INTO empresas_terceirizadas (nome, tipo_servico, numero_contrato, responsavel_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato'], $dados['responsavel_id']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa cadastrada!']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

// MATERIAIS
if ($tipo === 'materiais' && $acao === 'criar') {
    try {
        $stmt = $conn->prepare('INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssiiiiddd', $dados['nome'], $dados['codigo_sku'], $dados['categoria_id'], $dados['unidade_medida_id'], $dados['empresa_id'], $dados['local_id'], $dados['estoque_atual'], $dados['ponto_reposicao'], $dados['estoque_maximo']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Material cadastrado!']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

$conn->close();
echo json_encode(['sucesso' => false, 'erro' => 'Ação não encontrada']);
?>
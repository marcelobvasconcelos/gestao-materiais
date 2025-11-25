<?php
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$tipo = $_GET['tipo'] ?? '';
$acao = $_GET['acao'] ?? '';
$dados = json_decode(file_get_contents('php://input'), true);

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        throw new Exception('Conexão falhou');
    }
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão']);
    exit;
}

ob_clean();

// TESTE
if ($tipo === 'teste') {
    echo json_encode(['sucesso' => true, 'mensagem' => 'OK']);
    exit;
}

// EMPRESAS
if ($tipo === 'empresas') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT id, nome, tipo_servico, numero_contrato FROM empresas_terceirizadas LIMIT 50');
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        $stmt = $conn->prepare('INSERT INTO empresas_terceirizadas (nome, tipo_servico, numero_contrato, responsavel_id) VALUES (?, ?, ?, 1)');
        $stmt->bind_param('sss', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa cadastrada']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar']);
        }
        exit;
    }
}

// MATERIAIS
if ($tipo === 'materiais') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT id, nome, codigo_sku, estoque_atual FROM materiais LIMIT 50');
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        $stmt = $conn->prepare('INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo) VALUES (?, ?, 1, 1, 1, 1, ?, ?, ?)');
        $stmt->bind_param('ssddd', $dados['nome'], $dados['codigo_sku'], $dados['estoque_atual'], $dados['ponto_reposicao'], $dados['estoque_maximo']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Material cadastrado']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar']);
        }
        exit;
    }
}

$conn->close();
echo json_encode(['sucesso' => false, 'erro' => 'Ação não encontrada']);
?>
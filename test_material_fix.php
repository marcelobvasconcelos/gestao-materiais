<?php
// test_material_fix.php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1; // Admin
$_SESSION['empresas_permitidas'] = 'ALL';

require_once 'config.php';
$conn = getDbConnection();

echo "=== TESTE DE CORREÇÃO DE MATERIAIS ===\n";

// 1. Testar CRIAÇÃO sem local_id
echo "[1] Testando CRIAÇÃO sem local_id...\n";
$sku = 'TEST-' . time();
$dadosCriar = [
    'nome' => 'Material Teste Fix',
    'codigo_sku' => $sku,
    'categoria_id' => 2,
    'unidade_medida_id' => 1,
    'empresa_id' => 1,
    'ponto_reposicao' => 10,
    'estoque_maximo' => 100,
    'local_id' => null // Simulando envio nulo
];

// Simular chamada API (via cURL para isolamento)
$ch = curl_init('http://localhost/gestao-materiais/api_filtrada.php?tipo=materiais&acao=criar');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosCriar));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Cookie: PHPSESSID=' . session_id()]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "    Resposta: $response\n";
$json = json_decode($response, true);

if ($json['sucesso']) {
    echo "    >>> SUCESSO na criação!\n";
    
    // Verificar no banco se local_id = 1
    $stmt = $conn->prepare("SELECT id, local_id FROM materiais WHERE codigo_sku = ?");
    $stmt->bind_param('s', $sku);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $idMaterial = $res['id'];
    echo "    ID Criado: $idMaterial, Local ID: " . $res['local_id'] . " (Esperado: 1)\n";
    
    if ($res['local_id'] == 1) {
        echo "    >>> Local ID correto (1).\n";
    } else {
        echo "    >>> FALHA: Local ID incorreto.\n";
    }

    // 2. Testar ATUALIZAÇÃO sem local_id
    echo "\n[2] Testando ATUALIZAÇÃO sem local_id (deve manter 1)...\n";
    $dadosUpdate = [
        'id' => $idMaterial,
        'nome' => 'Material Teste Fix Atualizado',
        'categoria_id' => 2,
        'unidade_medida_id' => 1,
        'empresa_id' => 1,
        'ponto_reposicao' => 20,
        'estoque_maximo' => 200,
        'local_id' => null // Simulando envio nulo
    ];
    
    $ch = curl_init('http://localhost/gestao-materiais/api_filtrada.php?tipo=materiais&acao=atualizar');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosUpdate));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Cookie: PHPSESSID=' . session_id()]);
    $responseUpdate = curl_exec($ch);
    curl_close($ch);
    
    echo "    Resposta Update: $responseUpdate\n";
    $jsonUpdate = json_decode($responseUpdate, true);
    
    if ($jsonUpdate['sucesso']) {
        echo "    >>> SUCESSO na atualização!\n";
        // Verificar persistência
        $res = $conn->query("SELECT local_id, nome FROM materiais WHERE id = $idMaterial")->fetch_assoc();
        echo "    Nome: " . $res['nome'] . "\n";
        echo "    Local ID: " . $res['local_id'] . " (Esperado: 1)\n";
    } else {
        echo "    >>> FALHA na atualização: " . ($jsonUpdate['erro'] ?? 'Desconhecido') . "\n";
    }
    
    // Limpeza
    $conn->query("DELETE FROM materiais WHERE id = $idMaterial");
    echo "\n[3] Limpeza realizada.\n";
    
} else {
    echo "    >>> FALHA na criação: " . ($json['erro'] ?? 'Desconhecido') . "\n";
}
?>

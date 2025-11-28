<?php
// test_entry_management.php
ini_set('session.save_path', sys_get_temp_dir());
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1; // Admin
$_SESSION['empresas_permitidas'] = 'ALL';

require_once 'config.php';
$conn = getDbConnection();

echo "=== TESTE DE GESTÃO DE ENTRADAS ===\n";

// 1. Criar material de teste
$sku = 'ENT-' . time();
if (!$conn->query("INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES ('Material Teste Entrada', '$sku', 2, 1, 22, 1, 0, 1)")) {
    die("Erro ao criar material: " . $conn->error . "\n");
}
$idMaterial = $conn->insert_id;
echo "[1] Material criado: ID $idMaterial (Estoque inicial: 0)\n";

// 2. Criar Entrada (10 unidades)
echo "[2] Criando entrada de 10 unidades...\n";
$dadosEntrada = [
    'data_entrada' => date('Y-m-d'),
    'material_id' => $idMaterial,
    'quantidade' => 10,
    'nota_fiscal' => 'NF123',
    'responsavel_id' => 1,
    'local_destino_id' => 1,
    'observacao' => 'Teste auto'
];

$ch = curl_init('http://127.0.0.1:8080/api_filtrada.php?tipo=entrada&acao=criar');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosEntrada));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Cookie: PHPSESSID=' . session_id()]);
$response = curl_exec($ch);
if ($response === false) {
    echo "    Erro cURL: " . curl_error($ch) . "\n";
}
echo "    Resposta Bruta: " . $response . "\n";
$resCriar = json_decode($response, true);
curl_close($ch);

if ($resCriar['sucesso']) {
    echo "    >>> Entrada criada com sucesso.\n";
    // Verificar estoque
    $estoque = $conn->query("SELECT estoque_atual FROM materiais WHERE id = $idMaterial")->fetch_assoc()['estoque_atual'];
    echo "    Estoque atual: $estoque (Esperado: 10)\n";
    
    if ($estoque == 10) {
        // 3. Buscar ID da entrada
        $idEntrada = $conn->query("SELECT id FROM movimentacoes_entrada WHERE material_id = $idMaterial ORDER BY id DESC LIMIT 1")->fetch_assoc()['id'];
        echo "[3] ID da Entrada: $idEntrada\n";
        
        // 4. Excluir Entrada
        echo "[4] Excluindo entrada...\n";
        $ch = curl_init('http://127.0.0.1:8080/api_filtrada.php?tipo=entrada&acao=excluir');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $idEntrada]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Cookie: PHPSESSID=' . session_id()]);
        $resExcluir = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if ($resExcluir['sucesso']) {
            echo "    >>> Entrada excluída com sucesso.\n";
            // Verificar estoque (deve voltar a 0)
            $estoqueFinal = $conn->query("SELECT estoque_atual FROM materiais WHERE id = $idMaterial")->fetch_assoc()['estoque_atual'];
            echo "    Estoque final: $estoqueFinal (Esperado: 0)\n";
            
            if ($estoqueFinal == 0) {
                echo "\n>>> SUCESSO TOTAL: Fluxo de entrada e exclusão validado! <<<\n";
            } else {
                echo "\n>>> FALHA: Estoque não foi estornado corretamente.\n";
            }
        } else {
            echo "    >>> FALHA ao excluir: " . ($resExcluir['erro'] ?? 'Erro desconhecido') . "\n";
        }
        
    } else {
        echo "    >>> FALHA: Estoque não atualizou na entrada.\n";
    }
} else {
    echo "    >>> FALHA ao criar entrada: " . ($resCriar['erro'] ?? 'Erro desconhecido') . "\n";
}

// Limpeza
$conn->query("DELETE FROM movimentacoes_entrada WHERE material_id = $idMaterial");
$conn->query("DELETE FROM materiais WHERE id = $idMaterial");
echo "\n[5] Limpeza realizada.\n";
?>

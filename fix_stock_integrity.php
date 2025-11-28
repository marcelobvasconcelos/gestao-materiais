<?php
require_once 'config.php';
header('Content-Type: text/plain');

$conn = getDbConnection();

echo "=== CORREÇÃO DE ESTOQUE ===\n\n";

// Buscar todos os materiais
$sql = "SELECT id, nome, estoque_atual FROM materiais";
$result = $conn->query($sql);
$materiais = $result->fetch_all(MYSQLI_ASSOC);
$corrigidos = 0;

foreach ($materiais as $m) {
    $id = $m['id'];
    
    // Somar Entradas
    $sqlEntrada = "SELECT SUM(quantidade) as total FROM movimentacoes_entrada WHERE material_id = $id";
    $resEntrada = $conn->query($sqlEntrada);
    $totalEntrada = $resEntrada ? floatval($resEntrada->fetch_assoc()['total']) : 0;
    
    // Somar Saídas
    $sqlSaida = "SELECT SUM(quantidade) as total FROM movimentacoes_saida WHERE material_id = $id";
    $resSaida = $conn->query($sqlSaida);
    $totalSaida = $resSaida ? floatval($resSaida->fetch_assoc()['total']) : 0;
    
    // Calcular Estoque Teórico
    $estoqueCalculado = $totalEntrada - $totalSaida;
    $estoqueAtual = floatval($m['estoque_atual']);
    
    // Verificar Divergência
    if (abs($estoqueAtual - $estoqueCalculado) > 0.001) {
        echo "Corrigindo ID $id ({$m['nome']}): De $estoqueAtual para $estoqueCalculado... ";
        
        $stmt = $conn->prepare("UPDATE materiais SET estoque_atual = ? WHERE id = ?");
        $stmt->bind_param("di", $estoqueCalculado, $id);
        
        if ($stmt->execute()) {
            echo "OK\n";
            $corrigidos++;
        } else {
            echo "ERRO: " . $stmt->error . "\n";
        }
    }
}

echo "\nTotal de materiais corrigidos: $corrigidos\n";
?>

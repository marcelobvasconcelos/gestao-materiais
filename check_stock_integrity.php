<?php
require_once 'config.php';
header('Content-Type: text/plain');

$conn = getDbConnection();

echo "=== VERIFICAÇÃO DE INTEGRIDADE DE ESTOQUE ===\n\n";

// Buscar todos os materiais
$sql = "SELECT id, nome, estoque_atual FROM materiais";
$result = $conn->query($sql);

if (!$result) {
    die("Erro ao buscar materiais: " . $conn->error);
}

$materiais = $result->fetch_all(MYSQLI_ASSOC);
$divergencias = 0;

echo sprintf("%-5s | %-30s | %-10s | %-10s | %-10s | %-10s\n", "ID", "Nome", "Atual", "Entradas", "Saídas", "Calculado");
echo str_repeat("-", 85) . "\n";

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
    
    // Verificar Divergência (com pequena tolerância para float)
    $diff = abs($estoqueAtual - $estoqueCalculado);
    $status = ($diff < 0.001) ? "OK" : "ERRO";
    
    if ($status === "ERRO") {
        $divergencias++;
        echo sprintf("%-5d | %-30s | %-10.2f | %-10.2f | %-10.2f | %-10.2f [DIVERGÊNCIA]\n", 
            $id, substr($m['nome'], 0, 30), $estoqueAtual, $totalEntrada, $totalSaida, $estoqueCalculado);
    } else {
        // Descomente para ver todos
        // echo sprintf("%-5d | %-30s | %-10.2f | %-10.2f | %-10.2f | %-10.2f [OK]\n", $id, substr($m['nome'], 0, 30), $estoqueAtual, $totalEntrada, $totalSaida, $estoqueCalculado);
    }
}

echo "\n" . str_repeat("-", 85) . "\n";
if ($divergencias === 0) {
    echo "SUCESSO: Nenhum material com divergência de estoque encontrado.\n";
} else {
    echo "ATENÇÃO: Foram encontradas $divergencias divergências no estoque.\n";
}
?>

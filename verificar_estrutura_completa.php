<?php
header('Content-Type: text/html; charset=utf-8');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        die('Erro de conexão: ' . $conn->connect_error);
    }
    
    echo "<h2>Estrutura das Tabelas</h2>";
    
    $tabelas = ['categorias_materiais', 'unidades_medida', 'locais_armazenamento', 'empresas_terceirizadas', 'materiais'];
    
    foreach ($tabelas as $tabela) {
        echo "<h3>Tabela: $tabela</h3>";
        
        $result = $conn->query("SHOW TABLES LIKE '$tabela'");
        if ($result->num_rows > 0) {
            echo "<p><strong>Existe:</strong> SIM</p>";
            
            // Mostrar colunas
            $result = $conn->query("DESCRIBE $tabela");
            if ($result) {
                echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p><strong>Existe:</strong> NÃO</p>";
        }
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
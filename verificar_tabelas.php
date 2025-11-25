<?php
require_once 'config.php';

echo "<h2>Verificação de Tabelas do Banco de Dados</h2>";

try {
    $conn = getDbConnection();
    
    // Listar todas as tabelas
    $result = $conn->query("SHOW TABLES");
    
    echo "<h3>Tabelas existentes:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li><strong>{$row[0]}</strong></li>";
    }
    echo "</ul>";
    
    // Verificar estrutura da tabela materiais
    echo "<h3>Estrutura da tabela 'materiais':</h3>";
    $result = $conn->query("DESCRIBE materiais");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>

<?php
header('Content-Type: text/html; charset=utf-8');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        die('Erro de conexão: ' . $conn->connect_error);
    }
    
    echo "<h2>Estrutura Real das Tabelas</h2>";
    
    $tabelas = ['categorias_materiais', 'unidades_medida', 'locais_armazenamento'];
    
    foreach ($tabelas as $tabela) {
        echo "<h3>$tabela</h3>";
        
        $result = $conn->query("DESCRIBE $tabela");
        if ($result) {
            $colunas = [];
            echo "<p><strong>Colunas:</strong> ";
            while ($row = $result->fetch_assoc()) {
                $colunas[] = $row['Field'];
                echo $row['Field'] . " (" . $row['Type'] . "), ";
            }
            echo "</p>";
            
            // Gerar INSERT baseado nas colunas reais
            if (in_array('id', $colunas)) {
                $campos = ['id'];
                $valores = ['1'];
                
                // Adicionar campos que existem
                if (in_array('nome', $colunas)) {
                    $campos[] = 'nome';
                    $valores[] = "'Teste'";
                }
                if (in_array('descricao', $colunas)) {
                    $campos[] = 'descricao';
                    $valores[] = "'Descrição teste'";
                }
                if (in_array('simbolo', $colunas)) {
                    $campos[] = 'simbolo';
                    $valores[] = "'un'";
                }
                
                $sql = "INSERT IGNORE INTO $tabela (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ");";
                echo "<p><strong>SQL correto:</strong> <code>$sql</code></p>";
                
                // Executar o INSERT
                if ($conn->query($sql)) {
                    echo "<p><strong>✓ Inserido com sucesso!</strong></p>";
                } else {
                    echo "<p><strong>✗ Erro:</strong> " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p>Tabela não existe ou erro: " . $conn->error . "</p>";
        }
        echo "<hr>";
    }
    
    // Verificar se agora podemos cadastrar material
    echo "<h3>Teste de Cadastro de Material</h3>";
    $sql_material = "INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES ('Teste Material', 'TEST001', 1, 1, 1, 1, 10.0, 1)";
    
    if ($conn->query($sql_material)) {
        echo "<p><strong>✓ Material cadastrado com sucesso!</strong></p>";
        
        // Verificar se aparece na consulta
        $result = $conn->query("SELECT * FROM materiais WHERE codigo_sku = 'TEST001'");
        if ($result && $result->num_rows > 0) {
            $material = $result->fetch_assoc();
            echo "<p><strong>Material encontrado:</strong> " . $material['nome'] . "</p>";
        }
    } else {
        echo "<p><strong>✗ Erro ao cadastrar material:</strong> " . $conn->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
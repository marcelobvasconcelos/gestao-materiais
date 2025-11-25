<?php
header('Content-Type: text/html; charset=utf-8');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        die('Erro de conexão: ' . $conn->connect_error);
    }
    
    echo "<h2>Verificação de Relações: Materiais ↔ Empresas</h2>";
    
    // 1. Estrutura da tabela materiais
    echo "<h3>1. Estrutura da tabela materiais</h3>";
    $result = $conn->query("DESCRIBE materiais");
    if ($result) {
        echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 2. Verificar constraints de chave estrangeira
    echo "<h3>2. Constraints de Chave Estrangeira</h3>";
    $result = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'gestao_materiais_terceirizados' 
        AND TABLE_NAME = 'materiais' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'><tr><th>Constraint</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['CONSTRAINT_NAME']}</td><td>{$row['COLUMN_NAME']}</td><td>{$row['REFERENCED_TABLE_NAME']}</td><td>{$row['REFERENCED_COLUMN_NAME']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma constraint encontrada</p>";
    }
    
    // 3. Verificar dados nas tabelas relacionadas
    echo "<h3>3. Dados nas Tabelas Relacionadas</h3>";
    
    $tabelas = [
        'empresas_terceirizadas' => 'id, nome, status',
        'categorias_materiais' => 'id, nome',
        'unidades_medida' => 'id, descricao, simbolo',
        'locais_armazenamento' => 'id, nome, ativo'
    ];
    
    foreach ($tabelas as $tabela => $campos) {
        echo "<h4>$tabela</h4>";
        $result = $conn->query("SELECT $campos FROM $tabela LIMIT 5");
        if ($result && $result->num_rows > 0) {
            $dados = $result->fetch_all(MYSQLI_ASSOC);
            echo "<table border='1'>";
            echo "<tr>";
            foreach (array_keys($dados[0]) as $campo) {
                echo "<th>$campo</th>";
            }
            echo "</tr>";
            foreach ($dados as $linha) {
                echo "<tr>";
                foreach ($linha as $valor) {
                    echo "<td>$valor</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p><strong>VAZIA</strong> - Nenhum registro encontrado</p>";
        }
    }
    
    // 4. Testar inserção com dados válidos
    echo "<h3>4. Teste de Inserção</h3>";
    
    // Verificar se empresa ID 1 existe
    $result = $conn->query("SELECT id, nome FROM empresas_terceirizadas WHERE id = 1");
    if ($result && $result->num_rows > 0) {
        $empresa = $result->fetch_assoc();
        echo "<p>✓ Empresa ID 1 existe: {$empresa['nome']}</p>";
        
        // Tentar inserir material
        $sql = "INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) 
                VALUES ('Material Teste', 'TEST999', 1, 1, 1, 1, 10.0, 1)";
        
        if ($conn->query($sql)) {
            echo "<p>✓ Material inserido com sucesso!</p>";
            
            // Verificar se aparece na consulta com JOIN
            $result = $conn->query("
                SELECT m.nome, m.codigo_sku, e.nome as empresa_nome 
                FROM materiais m 
                LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id 
                WHERE m.codigo_sku = 'TEST999'
            ");
            
            if ($result && $result->num_rows > 0) {
                $material = $result->fetch_assoc();
                echo "<p>✓ Material encontrado com empresa: {$material['nome']} - {$material['empresa_nome']}</p>";
            }
        } else {
            echo "<p>✗ Erro ao inserir material: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✗ Empresa ID 1 não existe</p>";
        
        // Criar empresa se não existir
        $sql_empresa = "INSERT INTO empresas_terceirizadas (id, nome, tipo_servico, status) VALUES (1, 'Empresa Teste', 'Limpeza', 'Ativa')";
        if ($conn->query($sql_empresa)) {
            echo "<p>✓ Empresa ID 1 criada</p>";
        } else {
            echo "<p>✗ Erro ao criar empresa: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
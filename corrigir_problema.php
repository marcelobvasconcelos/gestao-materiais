<?php
header('Content-Type: text/html; charset=utf-8');

try {
    $conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
    if ($conn->connect_error) {
        die('Erro de conexão: ' . $conn->connect_error);
    }
    
    echo "<h2>Diagnóstico e Correção</h2>";
    
    // 1. Verificar se as tabelas de dependência existem
    $tabelas = ['categorias_materiais', 'unidades_medida', 'empresas_terceirizadas', 'locais_armazenamento'];
    
    foreach ($tabelas as $tabela) {
        $result = $conn->query("SHOW TABLES LIKE '$tabela'");
        echo "<p><strong>Tabela $tabela:</strong> " . ($result->num_rows > 0 ? "EXISTE" : "NÃO EXISTE") . "</p>";
        
        if ($result->num_rows > 0) {
            $count = $conn->query("SELECT COUNT(*) as total FROM $tabela")->fetch_assoc()['total'];
            echo "<p>→ Registros: $count</p>";
        }
    }
    
    // 2. Criar dados básicos se não existirem
    echo "<h3>Inserindo dados básicos...</h3>";
    
    // Categorias
    $conn->query("INSERT IGNORE INTO categorias_materiais (id, nome, ativo) VALUES (1, 'Limpeza', 1)");
    
    // Unidades
    $conn->query("INSERT IGNORE INTO unidades_medida (id, nome, simbolo, ativo) VALUES (1, 'Unidade', 'un', 1)");
    
    // Locais
    $conn->query("INSERT IGNORE INTO locais_armazenamento (id, nome, ativo) VALUES (1, 'Almoxarifado Central', 1)");
    
    // Verificar se empresa ID 1 existe
    $result = $conn->query("SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE id = 1");
    $empresa_existe = $result->fetch_assoc()['total'] > 0;
    
    if (!$empresa_existe) {
        $conn->query("INSERT INTO empresas_terceirizadas (id, nome, tipo_servico, status) VALUES (1, 'Empresa Teste', 'Limpeza', 'Ativa')");
        echo "<p>Empresa criada</p>";
    }
    
    // 3. Tentar inserir material novamente
    echo "<h3>Inserindo material...</h3>";
    
    $sql = "INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) 
            VALUES ('Detergente Teste', 'TEST001', 1, 1, 1, 1, 50.0, 1)";
    
    if ($conn->query($sql)) {
        echo "<p><strong>Material inserido:</strong> OK</p>";
        
        // Verificar se foi salvo
        $result = $conn->query("SELECT COUNT(*) as total FROM materiais");
        $total = $result->fetch_assoc()['total'];
        echo "<p><strong>Total de materiais agora:</strong> $total</p>";
        
        if ($total > 0) {
            $result = $conn->query("SELECT * FROM materiais LIMIT 1");
            $material = $result->fetch_assoc();
            echo "<p><strong>Material encontrado:</strong> " . $material['nome'] . "</p>";
        }
        
    } else {
        echo "<p><strong>Erro ao inserir material:</strong> " . $conn->error . "</p>";
    }
    
    // 4. Verificar constraints
    echo "<h3>Verificando estrutura da tabela materiais:</h3>";
    $result = $conn->query("SHOW CREATE TABLE materiais");
    $create_table = $result->fetch_assoc()['Create Table'];
    echo "<pre>" . htmlspecialchars($create_table) . "</pre>";
    
} catch (Exception $e) {
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
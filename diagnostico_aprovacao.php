<?php
require_once 'config.php';

echo "<h2>Diagnóstico: Fluxo de Aprovação de Usuário</h2>";

try {
    $conn = getDbConnection();
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
    
    // 1. Verificar estrutura da tabela usuarios
    echo "<h3>1. Estrutura da tabela 'usuarios'</h3>";
    $result = $conn->query("DESCRIBE usuarios");
    echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
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
    
    // 2. Verificar último usuário pendente
    echo "<h3>2. Usuários Pendentes</h3>";
    $result = $conn->query("SELECT id, nome, email, status FROM usuarios_pendentes ORDER BY id DESC LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nome</th><th>Email</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $color = $row['status'] === 'Aprovado' ? 'orange' : 'blue';
            echo "<tr style='color: $color;'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td><strong>{$row['status']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário pendente encontrado</p>";
    }
    
    // 3. Verificar últimos usuários criados
    echo "<h3>3. Últimos Usuários Criados (tabela 'usuarios')</h3>";
    $result = $conn->query("SELECT id, nome, email, perfil_id, ativo, created_at FROM usuarios ORDER BY id DESC LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil ID</th><th>Ativo</th><th>Criado em</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['perfil_id']}</td>";
            echo "<td>" . ($row['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "<td>" . ($row['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>⚠️ Nenhum usuário encontrado na tabela 'usuarios'</p>";
    }
    
    // 4. Simular aprovação de usuário (se houver pendente)
    echo "<h3>4. Teste de Aprovação (simulação)</h3>";
    $pendente = $conn->query("SELECT * FROM usuarios_pendentes WHERE status = 'Pendente' LIMIT 1")->fetch_assoc();
    
    if ($pendente) {
        echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
        echo "<strong>Usuário pendente encontrado:</strong><br>";
        echo "ID: {$pendente['id']}<br>";
        echo "Nome: {$pendente['nome']}<br>";
        echo "Email: {$pendente['email']}<br>";
        echo "<br><strong>SQL que seria executado:</strong><br>";
        
        $perfil_teste = 2; // Gestor
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES ('{$pendente['nome']}', '{$pendente['email']}', '[SENHA_HASH]', $perfil_teste, '{$pendente['departamento']}', 1)";
        echo "<code>$sql</code>";
        echo "</div>";
        
        // Testar se conseguimos fazer o INSERT (dry-run)
        echo "<h4>Teste de INSERT (sem commit):</h4>";
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES (?, ?, ?, ?, ?, 1)');
            $stmt->bind_param('sssis', $pendente['nome'], $pendente['email'], $pendente['senha'], $perfil_teste, $pendente['departamento']);
            
            if ($stmt->execute()) {
                $novo_id = $conn->insert_id;
                echo "<p style='color: green;'>✅ INSERT funcionaria! Novo ID seria: $novo_id</p>";
                $conn->rollback(); // Desfazer para não criar de verdade
                echo "<p style='color: orange;'>⚠️ Rollback executado - nada foi salvo</p>";
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p style='color: red;'>❌ ERRO no INSERT: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>Nenhum usuário pendente disponível para teste</p>";
    }
    
    // 5. Verificar índices e constraints
    echo "<h3>5. Constraints e Índices</h3>";
    $result = $conn->query("
        SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios'
    ");
    echo "<table border='1' cellpadding='5'><tr><th>Constraint</th><th>Tipo</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['CONSTRAINT_NAME']}</td><td>{$row['CONSTRAINT_TYPE']}</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
}
?>

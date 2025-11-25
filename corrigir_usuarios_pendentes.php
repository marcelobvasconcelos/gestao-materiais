<?php
require_once 'config.php';

echo "<h2>Correção: Migrar Usuários Aprovados Pendentes</h2>";

try {
    $conn = getDbConnection();
    
    // Buscar usuários pendentes aprovados que não existem na tabela usuarios
    $sql = "SELECT up.* FROM usuarios_pendentes up 
            LEFT JOIN usuarios u ON up.email = u.email 
            WHERE up.status = 'Aprovado' AND u.id IS NULL";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        echo "<p style='color: green;'>✅ Todos os usuários aprovados já estão na tabela de usuários!</p>";
        exit;
    }
    
    echo "<p style='color: orange;'>Encontrados {$result->num_rows} usuários aprovados que precisam ser migrados:</p>";
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nome</th><th>Email</th><th>Ação</th></tr>";
    
    $migrados = 0;
    $erros = 0;
    
    while ($pendente = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$pendente['id']}</td>";
        echo "<td>{$pendente['nome']}</td>";
        echo "<td>{$pendente['email']}</td>";
        
        $conn->begin_transaction();
        try {
            // Definir perfil_id padrão como 2 (Gestor) se não houver
            $perfil_id = 2; // Gestor
            
            // Inserir na tabela usuarios
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES (?, ?, ?, ?, ?, 1)');
            $stmt->bind_param('sssis', 
                $pendente['nome'], 
                $pendente['email'], 
                $pendente['senha'], 
                $perfil_id, 
                $pendente['departamento']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir: " . $stmt->error);
            }
            
            $novo_usuario_id = $conn->insert_id;
            
            if (!$novo_usuario_id) {
                throw new Exception("ID não foi gerado");
            }
            
            $conn->commit();
            $migrados++;
            echo "<td style='color: green;'>✅ Migrado com sucesso! ID: $novo_usuario_id</td>";
            
        } catch (Exception $e) {
            $conn->rollback();
            $erros++;
            echo "<td style='color: red;'>❌ Erro: " . $e->getMessage() . "</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Resumo:</h3>";
    echo "<p>✅ Migrados com sucesso: <strong>$migrados</strong></p>";
    if ($erros > 0) {
        echo "<p style='color: red;'>❌ Erros: <strong>$erros</strong></p>";
    }
    
    if ($migrados > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-top: 20px;'>";
        echo "<strong>✅ Correção concluída!</strong><br>";
        echo "Os usuários agora podem fazer login no sistema.<br>";
        echo "<strong>Perfil atribuído:</strong> Gestor (ID: 2)<br>";
        echo "<strong>Próximo passo:</strong> Um administrador pode ajustar os perfis e vincular empresas se necessário.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO GERAL: " . $e->getMessage() . "</p>";
}
?>

<hr>
<h3>Verificação Final</h3>
<p><a href="diagnostico_aprovacao.php" style="padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">🔍 Executar Diagnóstico Novamente</a></p>

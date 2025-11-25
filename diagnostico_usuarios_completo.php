<?php
require_once 'config.php';

echo "<h2>Diagnóstico: Comparação Usuários Pendentes vs Usuários</h2>";

try {
    $conn = getDbConnection();
    
    echo "<h3>Usuários APROVADOS em 'usuarios_pendentes':</h3>";
    $result = $conn->query("SELECT id, nome, email, status, data_criacao FROM usuarios_pendentes WHERE status = 'Aprovado' ORDER BY id");
    
    $emails_aprovados = [];
    echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
    echo "<tr><th>ID Pendente</th><th>Nome</th><th>Email</th><th>Status</th><th>Data Criação</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $emails_aprovados[] = $row['email'];
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nome']}</td>";
        echo "<td><strong>{$row['email']}</strong></td>";
        echo "<td style='color: orange;'>{$row['status']}</td>";
        echo "<td>{$row['data_criacao']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (empty($emails_aprovados)) {
        echo "<p>Nenhum usuário aprovado encontrado.</p>";
        exit;
    }
    
    // Buscar esses emails na tabela usuarios
    echo "<h3>Esses MESMOS emails na tabela 'usuarios':</h3>";
    $emails_str = "'" . implode("','", $emails_aprovados) . "'";
    $sql = "SELECT id, nome, email, perfil_id, ativo, created_at FROM usuarios WHERE email IN ($emails_str)";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Usuário</th><th>Nome</th><th>Email</th><th>Perfil ID</th><th>Ativo</th><th>Criado em</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            $ativo_color = $row['ativo'] == 1 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td><strong>{$row['email']}</strong></td>";
            echo "<td>{$row['perfil_id']}</td>";
            echo "<td style='color: $ativo_color;'><strong>" . ($row['ativo'] ? 'SIM ✅' : 'NÃO ❌') . "</strong></td>";
            echo "<td>" . ($row['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin-top: 20px;'>";
        echo "<h4>📊 Análise:</h4>";
        echo "<p>✅ Os usuários <strong>EXISTEM</strong> na tabela 'usuarios'.</p>";
        
        // Verificar se algum está inativo
        $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE email IN ($emails_str) AND ativo = 0");
        $inativos = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE email IN ($emails_str) AND ativo = 0")->fetch_assoc()['total'];
        
        if ($inativos > 0) {
            echo "<p style='color: red;'>⚠️ <strong>$inativos usuário(s) está(ão) INATIVO(S)</strong> - isso impede o login!</p>";
            echo "<p><strong>Solução:</strong> Execute o script de ativação abaixo.</p>";
        } else {
            echo "<p style='color: green;'>✅ Todos os usuários estão ATIVOS.</p>";
        }
        
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ NENHUM desses emails foi encontrado na tabela 'usuarios'!</p>";
        echo "<p>Isso é estranho - o script de correção deveria ter encontrado e migrado eles.</p>";
    }
    
    // Verificar empresas vinculadas
    echo "<h3>Empresas Vinculadas:</h3>";
    $sql = "SELECT u.email, ue.empresa_id, e.nome as empresa_nome 
            FROM usuarios u 
            LEFT JOIN usuarios_empresas ue ON u.id = ue.usuario_id 
            LEFT JOIN empresas_terceirizadas e ON ue.empresa_id = e.id 
            WHERE u.email IN ($emails_str)";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Email</th><th>Empresa ID</th><th>Empresa Nome</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['email']}</td>";
            echo "<td>" . ($row['empresa_id'] ?? '<span style="color: orange;">Nenhuma ❌</span>') . "</td>";
            echo "<td>" . ($row['empresa_nome'] ?? '<span style="color: orange;">Não vinculado</span>') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
}
?>

<?php if (isset($inativos) && $inativos > 0): ?>
<hr>
<h3>🔧 Ativar Usuários Inativos</h3>
<form method="POST" style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;">
    <p><strong>Deseja ativar todos os usuários aprovados?</strong></p>
    <button type="submit" name="ativar" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
        ✅ Sim, Ativar Todos
    </button>
</form>

<?php
if (isset($_POST['ativar'])) {
    try {
        $emails_str = "'" . implode("','", $emails_aprovados) . "'";
        $conn->query("UPDATE usuarios SET ativo = 1 WHERE email IN ($emails_str)");
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-top: 20px;'>";
        echo "<strong>✅ Usuários ativados com sucesso!</strong><br>";
        echo "<a href='" . $_SERVER['PHP_SELF'] . "' style='padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>🔄 Recarregar Diagnóstico</a>";
        echo "</div>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erro ao ativar: " . $e->getMessage() . "</p>";
    }
}
?>
<?php endif; ?>

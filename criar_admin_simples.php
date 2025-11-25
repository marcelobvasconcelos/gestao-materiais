<?php
// Script simples para criar apenas o usuário administrador
echo "<h2>Criando Usuário Administrador</h2>";
echo "<pre>";

// Configurações de produção
$servername = '172.24.1.50';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

try {
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    echo "✅ Conectado ao banco de dados!\n";

    // Verificar se as tabelas existem
    $tables_check = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($tables_check->num_rows == 0) {
        throw new Exception('Tabela usuarios não existe. Execute primeiro o script configurar_producao.php');
    }

    $tables_check = $conn->query("SHOW TABLES LIKE 'perfis_acesso'");
    if ($tables_check->num_rows == 0) {
        throw new Exception('Tabela perfis_acesso não existe. Execute primeiro o script configurar_producao.php');
    }

    echo "✅ Tabelas encontradas!\n";

    // Inserir perfil admin se não existir
    $conn->query("INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
    (1, 'Administrador', 'Acesso total ao sistema', 1)");
    echo "✅ Perfil administrador inserido/verificado!\n";

    // Criar hash da senha
    $senha_hash = password_hash('123', PASSWORD_DEFAULT);

    // Inserir usuário admin
    $stmt = $conn->prepare("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssii', $id, $nome, $email, $senha_hash, $perfil_id, $ativo);

    $id = 1;
    $nome = 'Marcelo';
    $email = 'adm.ti.uast@ufrpe.br';
    $perfil_id = 1;
    $ativo = 1;

    if ($stmt->execute()) {
        echo "✅ Usuário administrador criado com sucesso!\n";
        echo "\n📧 Email: adm.ti.uast@ufrpe.br\n";
        echo "🔑 Senha: 123\n";
        echo "\n⚠️ IMPORTANTE: Altere a senha após o primeiro login!\n";
    } else {
        echo "❌ Erro ao criar usuário: " . $stmt->error . "\n";
    }

    // Verificar se foi criado
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE email = 'adm.ti.uast@ufrpe.br'");
    $count = $result->fetch_assoc();
    echo "\n👤 Total de usuários com este email: " . $count['total'] . "\n";

    $conn->close();

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
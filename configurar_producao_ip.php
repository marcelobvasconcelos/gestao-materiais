<?php
// Script de configuração com IP configurável
echo "<h2>Configuração de Produção - IP Configurável</h2>";

// Verificar se foi passado IP via parâmetro
$ip_producao = $_GET['ip'] ?? '172.24.1.50';

echo "<p>IP do servidor MySQL: <strong>$ip_producao</strong></p>";
echo "<p>Para usar um IP diferente, acesse: <code>configurar_producao_ip.php?ip=SEU_IP</code></p>";
echo "<hr>";

// Configurações
$servername = $ip_producao;
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

echo "<pre>";
echo "🔧 Conectando ao servidor MySQL ($servername)...\n";

try {
    // Conectar sem especificar banco primeiro
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    echo "✅ Conexão estabelecida!\n";

    // Criar banco se não existir
    echo "📦 Criando/verificando banco de dados...\n";
    $conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($conn->error) {
        throw new Exception('Erro ao criar banco: ' . $conn->error);
    }
    echo "✅ Banco '$database' criado/verificado!\n";

    // Selecionar banco
    $conn->select_db($database);

    // Criar tabelas básicas necessárias
    echo "🛠️ Criando tabelas essenciais...\n";

    // Perfis de acesso
    $conn->query("CREATE TABLE IF NOT EXISTS perfis_acesso (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(50) NOT NULL,
        descricao TEXT,
        ativo TINYINT(1) DEFAULT 1,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Usuários
    $conn->query("CREATE TABLE IF NOT EXISTS usuarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        perfil_id INT DEFAULT 1,
        departamento VARCHAR(50),
        telefone VARCHAR(20),
        ativo TINYINT(1) DEFAULT 1,
        ultimo_acesso TIMESTAMP NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (perfil_id) REFERENCES perfis_acesso(id)
    )");

    echo "✅ Tabelas criadas!\n";

    // Inserir dados básicos
    echo "📝 Inserindo dados básicos...\n";

    // Perfis
    $conn->query("INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
    (1, 'Administrador', 'Acesso total ao sistema', 1)");

    // Usuário admin
    $senha_hash = password_hash('123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
    (1, 'Marcelo', 'adm.ti.uast@ufrpe.br', '$senha_hash', 1, 1)");

    echo "✅ Dados básicos inseridos!\n";

    // Verificar usuário criado
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1");
    $usuarios = $result->fetch_assoc();

    echo "👥 Usuários ativos: " . $usuarios['total'] . "\n";

    $conn->close();

    echo "\n🎉 CONFIGURAÇÃO BÁSICA REALIZADA COM SUCESSO!\n\n";
    echo "Para fazer login, use:\n";
    echo "📧 Email: adm.ti.uast@ufrpe.br\n";
    echo "🔑 Senha: 123\n\n";
    echo "⚠️ IMPORTANTE: Altere a senha após o primeiro login!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";

    // Sugestões de solução
    echo "\n💡 POSSÍVEIS SOLUÇÕES:\n";
    echo "1. Verifique se o IP do servidor MySQL está correto\n";
    echo "2. Certifique-se de que o MySQL está rodando no servidor\n";
    echo "3. Verifique se o firewall permite conexões na porta 3306\n";
    echo "4. Teste a conectividade: ping $ip_producao\n";
    echo "5. Verifique as permissões do usuário 'inventario'\n";
}

echo "</pre>";
?>
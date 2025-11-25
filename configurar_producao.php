<?php
// Script completo para configurar produção
echo "<h2>Configuração Completa de Produção</h2>";
echo "<pre>";

// Configurações de produção
$servername = '172.24.1.50';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

echo "🔧 Conectando ao servidor MySQL...\n";

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

    // Criar tabelas
    echo "🛠️ Criando tabelas...\n";

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

    // Empresas terceirizadas
    $conn->query("CREATE TABLE IF NOT EXISTS empresas_terceirizadas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        tipo_servico VARCHAR(50),
        numero_contrato VARCHAR(50),
        cnpj VARCHAR(20),
        telefone VARCHAR(20),
        email VARCHAR(100),
        status ENUM('Ativa', 'Inativa') DEFAULT 'Ativa',
        responsavel_id INT,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
    )");

    // Usuários x Empresas
    $conn->query("CREATE TABLE IF NOT EXISTS usuarios_empresas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        empresa_id INT NOT NULL,
        data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE,
        UNIQUE KEY unique_usuario_empresa (usuario_id, empresa_id)
    )");

    // Categorias de materiais
    $conn->query("CREATE TABLE IF NOT EXISTS categorias_materiais (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        ativo TINYINT(1) DEFAULT 1,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Unidades de medida
    $conn->query("CREATE TABLE IF NOT EXISTS unidades_medida (
        id INT PRIMARY KEY AUTO_INCREMENT,
        descricao VARCHAR(50) NOT NULL,
        simbolo VARCHAR(10) NOT NULL,
        ativo TINYINT(1) DEFAULT 1
    )");

    // Locais de armazenamento
    $conn->query("CREATE TABLE IF NOT EXISTS locais_armazenamento (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        ativo TINYINT(1) DEFAULT 1,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Materiais
    $conn->query("CREATE TABLE IF NOT EXISTS materiais (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(200) NOT NULL,
        codigo_sku VARCHAR(50) UNIQUE,
        categoria_id INT,
        unidade_medida_id INT,
        empresa_id INT NOT NULL,
        local_id INT,
        estoque_atual DECIMAL(10,2) DEFAULT 0,
        ponto_reposicao DECIMAL(10,2) DEFAULT 0,
        estoque_maximo DECIMAL(10,2) DEFAULT 0,
        ativo TINYINT(1) DEFAULT 1,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias_materiais(id),
        FOREIGN KEY (unidade_medida_id) REFERENCES unidades_medida(id),
        FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id),
        FOREIGN KEY (local_id) REFERENCES locais_armazenamento(id)
    )");

    // Movimentações de entrada
    $conn->query("CREATE TABLE IF NOT EXISTS movimentacoes_entrada (
        id INT PRIMARY KEY AUTO_INCREMENT,
        data_entrada DATE NOT NULL,
        material_id INT NOT NULL,
        quantidade DECIMAL(10,2) NOT NULL,
        nota_fiscal VARCHAR(50),
        responsavel_id INT NOT NULL,
        local_destino_id INT,
        observacao TEXT,
        data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (material_id) REFERENCES materiais(id),
        FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
        FOREIGN KEY (local_destino_id) REFERENCES locais_armazenamento(id)
    )");

    // Movimentações de saída
    $conn->query("CREATE TABLE IF NOT EXISTS movimentacoes_saida (
        id INT PRIMARY KEY AUTO_INCREMENT,
        data_saida DATE NOT NULL,
        material_id INT NOT NULL,
        quantidade DECIMAL(10,2) NOT NULL,
        responsavel_id INT NOT NULL,
        local_origem_id INT,
        destino VARCHAR(100),
        observacao TEXT,
        data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (material_id) REFERENCES materiais(id),
        FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
        FOREIGN KEY (local_origem_id) REFERENCES locais_armazenamento(id)
    )");

    // Histórico de estoque
    $conn->query("CREATE TABLE IF NOT EXISTS historico_estoque (
        id INT PRIMARY KEY AUTO_INCREMENT,
        material_id INT NOT NULL,
        tipo_movimentacao ENUM('entrada', 'saida', 'ajuste') NOT NULL,
        quantidade DECIMAL(10,2) NOT NULL,
        estoque_anterior DECIMAL(10,2) NOT NULL,
        estoque_atual DECIMAL(10,2) NOT NULL,
        responsavel_id INT NOT NULL,
        observacao TEXT,
        data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (material_id) REFERENCES materiais(id),
        FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
    )");

    // Usuários pendentes
    $conn->query("CREATE TABLE IF NOT EXISTS usuarios_pendentes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        departamento VARCHAR(50),
        justificativa TEXT,
        status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
        aprovado_por INT NULL,
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_aprovacao TIMESTAMP NULL,
        FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
    )");

    echo "✅ Tabelas criadas!\n";

    // Inserir dados básicos
    echo "📝 Inserindo dados básicos...\n";

    // Perfis
    $conn->query("INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
    (1, 'Administrador', 'Acesso total ao sistema', 1),
    (2, 'Gestor', 'Gerenciamento operacional', 1),
    (3, 'Operador', 'Operações básicas', 1),
    (4, 'Consulta', 'Apenas visualização', 1)");

    // Categorias
    $conn->query("INSERT IGNORE INTO categorias_materiais (id, nome, descricao, ativo) VALUES
    (1, 'Limpeza', 'Produtos de limpeza e higiene', 1),
    (2, 'Ferramentas', 'Ferramentas e equipamentos', 1),
    (3, 'Equipamentos', 'Equipamentos diversos', 1),
    (4, 'Escritório', 'Material de escritório', 1),
    (5, 'Manutenção', 'Materiais para manutenção', 1)");

    // Unidades
    $conn->query("INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES
    (1, 'Unidade', 'un'),
    (2, 'Litro', 'L'),
    (3, 'Quilograma', 'kg'),
    (4, 'Caixa', 'cx'),
    (5, 'Pacote', 'pct')");

    // Locais
    $conn->query("INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES
    (1, 'Almoxarifado Central', 'Depósito principal', 1),
    (2, 'Almoxarifado Limpeza', 'Produtos de limpeza', 1),
    (3, 'Almoxarifado Manutenção', 'Materiais de manutenção', 1)");

    // Empresa de teste
    $conn->query("INSERT IGNORE INTO empresas_terceirizadas (id, nome, tipo_servico, numero_contrato, status, responsavel_id) VALUES
    (1, 'Empresa Teste', 'Limpeza', 'CT-2024-001', 'Ativa', 1)");

    // Usuário admin
    $senha_hash = password_hash('123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
    (1, 'Marcelo', 'adm.ti.uast@ufrpe.br', '$senha_hash', 1, 1)");

    // Vincular admin à empresa
    $conn->query("INSERT IGNORE INTO usuarios_empresas (usuario_id, empresa_id) VALUES (1, 1)");

    echo "✅ Dados básicos inseridos!\n";

    // Verificar tabelas criadas
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    echo "📊 Tabelas criadas: " . count($tables) . "\n";
    echo "   - " . implode("\n   - ", $tables) . "\n";

    // Verificar usuário criado
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1");
    $usuarios = $result->fetch_assoc();

    echo "👥 Usuários ativos: " . $usuarios['total'] . "\n";

    $conn->close();

    echo "\n🎉 CONFIGURAÇÃO COMPLETA REALIZADA COM SUCESSO!\n\n";
    echo "Para fazer login, use:\n";
    echo "📧 Email: adm.ti.uast@ufrpe.br\n";
    echo "🔑 Senha: 123\n\n";
    echo "⚠️ IMPORTANTE: Altere a senha após o primeiro login!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        $conn->close();
    }
}

echo "</pre>";
?>
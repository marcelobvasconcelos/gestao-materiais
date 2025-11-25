-- ============================================================================
-- SCRIPT COMPLETO - Criar todas as tabelas necessárias
-- ============================================================================

-- Criar banco se não existir
CREATE DATABASE IF NOT EXISTS gestao_materiais_terceirizados;
USE gestao_materiais_terceirizados;

-- Tabela de Perfis de Acesso
CREATE TABLE IF NOT EXISTS perfis_acesso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    permissoes JSON,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255),
    perfil_id INT DEFAULT 1,
    departamento VARCHAR(100),
    ativo TINYINT(1) DEFAULT 1,
    ultimo_acesso TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis_acesso(id)
);

-- Tabela de Empresas Terceirizadas
CREATE TABLE IF NOT EXISTS empresas_terceirizadas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    tipo_servico VARCHAR(100),
    numero_contrato VARCHAR(50),
    cnpj VARCHAR(20),
    responsavel_id INT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('Ativa', 'Inativa') DEFAULT 'Ativa',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
);

-- Tabela de Locais de Armazenamento
CREATE TABLE IF NOT EXISTS locais_armazenamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Categorias de Materiais
CREATE TABLE IF NOT EXISTS categorias_materiais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Unidades de Medida
CREATE TABLE IF NOT EXISTS unidades_medida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    simbolo VARCHAR(10) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Materiais
CREATE TABLE IF NOT EXISTS materiais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    codigo_sku VARCHAR(50) UNIQUE,
    descricao TEXT,
    categoria_id INT,
    unidade_medida_id INT,
    empresa_id INT,
    local_id INT,
    estoque_atual DECIMAL(10,2) DEFAULT 0,
    ponto_reposicao DECIMAL(10,2) DEFAULT 0,
    estoque_maximo DECIMAL(10,2) DEFAULT 0,
    valor_unitario DECIMAL(10,2),
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_materiais(id),
    FOREIGN KEY (unidade_medida_id) REFERENCES unidades_medida(id),
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id),
    FOREIGN KEY (local_id) REFERENCES locais_armazenamento(id)
);

-- Tabela de Movimentações de Entrada
CREATE TABLE IF NOT EXISTS movimentacoes_entrada (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_entrada DATETIME NOT NULL,
    material_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    nota_fiscal VARCHAR(50),
    responsavel_id INT,
    local_destino_id INT,
    observacao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materiais(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (local_destino_id) REFERENCES locais_armazenamento(id)
);

-- Tabela de Movimentações de Saída
CREATE TABLE IF NOT EXISTS movimentacoes_saida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_saida DATETIME NOT NULL,
    material_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    empresa_solicitante_id INT,
    finalidade VARCHAR(100),
    responsavel_autorizacao_id INT,
    local_destino VARCHAR(200),
    observacao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materiais(id),
    FOREIGN KEY (empresa_solicitante_id) REFERENCES empresas_terceirizadas(id),
    FOREIGN KEY (responsavel_autorizacao_id) REFERENCES usuarios(id)
);

-- Inserir dados básicos
INSERT IGNORE INTO perfis_acesso (id, nome, descricao, permissoes) VALUES
(1, 'Administrador', 'Acesso total ao sistema', '{"criar": true, "editar": true, "excluir": true, "relatorios": true, "usuarios": true}'),
(2, 'Gestor', 'Gerenciamento operacional', '{"criar": true, "editar": true, "excluir": false, "relatorios": true, "usuarios": false}'),
(3, 'Operador', 'Operações básicas', '{"criar": true, "editar": false, "excluir": false, "relatorios": false, "usuarios": false}'),
(4, 'Consulta', 'Apenas visualização', '{"criar": false, "editar": false, "excluir": false, "relatorios": true, "usuarios": false}');

INSERT IGNORE INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES
('Administrador', 'admin@universidade.edu.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'TI', 1);

INSERT IGNORE INTO locais_armazenamento (nome, descricao) VALUES
('Almoxarifado Central', 'Depósito principal da universidade'),
('Almoxarifado Limpeza', 'Materiais de limpeza e higiene'),
('Almoxarifado Manutenção', 'Ferramentas e materiais de manutenção'),
('Almoxarifado Escritório', 'Material de escritório e papelaria');

INSERT IGNORE INTO categorias_materiais (nome, descricao) VALUES
('Limpeza', 'Produtos de limpeza e higiene'),
('Ferramentas', 'Ferramentas e equipamentos'),
('Equipamentos', 'Equipamentos diversos'),
('Escritório', 'Material de escritório'),
('Manutenção', 'Materiais para manutenção');

INSERT IGNORE INTO unidades_medida (nome, simbolo) VALUES
('Unidade', 'un'),
('Litro', 'L'),
('Quilograma', 'kg'),
('Caixa', 'cx'),
('Pacote', 'pct'),
('Resma', 'rsm'),
('Rolo', 'rl'),
('Lata', 'lt');
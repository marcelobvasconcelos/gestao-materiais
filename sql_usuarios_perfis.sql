-- ============================================================================
-- SCRIPT SQL - Sistema de Gestão de Materiais Terceirizados
-- Tabelas para Usuários e Perfis de Acesso
-- ============================================================================

-- Tabela de Perfis de Acesso
CREATE TABLE IF NOT EXISTS perfis_acesso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    permissoes JSON,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir perfis padrão
INSERT INTO perfis_acesso (id, nome, descricao, permissoes) VALUES
(1, 'Administrador', 'Acesso total ao sistema', '{"criar": true, "editar": true, "excluir": true, "relatorios": true, "usuarios": true}'),
(2, 'Gestor', 'Gerenciamento operacional', '{"criar": true, "editar": true, "excluir": false, "relatorios": true, "usuarios": false}'),
(3, 'Operador', 'Operações básicas', '{"criar": true, "editar": false, "excluir": false, "relatorios": false, "usuarios": false}'),
(4, 'Consulta', 'Apenas visualização', '{"criar": false, "editar": false, "excluir": false, "relatorios": true, "usuarios": false}');

-- Atualizar tabela de usuários (caso já exista)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS perfil_id INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS senha VARCHAR(255),
ADD COLUMN IF NOT EXISTS departamento VARCHAR(100),
ADD COLUMN IF NOT EXISTS ultimo_acesso TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Adicionar chave estrangeira
ALTER TABLE usuarios 
ADD CONSTRAINT fk_usuarios_perfil 
FOREIGN KEY (perfil_id) REFERENCES perfis_acesso(id);

-- Criar usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES
('Administrador', 'admin@universidade.edu.br', 'temp', 1, 'TI', 1)
ON DUPLICATE KEY UPDATE nome = nome;

-- Atualizar com hash correto da senha admin123
UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@universidade.edu.br';

-- Índices para performance
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_perfil ON usuarios(perfil_id);
CREATE INDEX idx_usuarios_ativo ON usuarios(ativo);
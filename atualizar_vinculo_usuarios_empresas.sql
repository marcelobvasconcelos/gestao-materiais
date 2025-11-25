-- ============================================================================
-- SCRIPT SQL - Atualização para Vínculo de Usuários com Empresas
-- ============================================================================

-- Verificar se a tabela usuarios_empresas existe, se não, criar
CREATE TABLE IF NOT EXISTS usuarios_empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vinculo (usuario_id, empresa_id)
);

-- Adicionar campo empresas_vinculadas na tabela usuarios se não existir
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS empresas_vinculadas JSON AFTER departamento;

-- Atualizar usuário admin para ter acesso a todas as empresas (JSON vazio significa todas)
UPDATE usuarios SET empresas_vinculadas = JSON_ARRAY() WHERE perfil_id = 1;

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_usuarios_empresas_usuario ON usuarios_empresas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_empresas_empresa ON usuarios_empresas(empresa_id);

-- Verificar estrutura das tabelas
DESCRIBE usuarios;
DESCRIBE usuarios_empresas;
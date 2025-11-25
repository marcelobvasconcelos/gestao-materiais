-- ============================================================================
-- SCRIPT SQL - Vínculo de Usuários com Empresas
-- ============================================================================

-- Adicionar campo de empresas vinculadas na tabela usuarios
ALTER TABLE usuarios 
ADD COLUMN empresas_vinculadas JSON AFTER departamento;

-- Criar tabela de vínculo usuário-empresa (alternativa mais robusta)
CREATE TABLE IF NOT EXISTS usuarios_empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vinculo (usuario_id, empresa_id)
);

-- Atualizar usuário admin para ter acesso a todas as empresas
UPDATE usuarios SET empresas_vinculadas = JSON_ARRAY() WHERE perfil_id = 1;
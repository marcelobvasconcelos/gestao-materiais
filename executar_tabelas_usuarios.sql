-- Execute este script para criar as tabelas necessárias para o sistema de usuários

USE gestao_materiais_terceirizados;

-- Tabela para usuários pendentes de aprovação
CREATE TABLE IF NOT EXISTS usuarios_pendentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    departamento VARCHAR(100),
    justificativa TEXT,
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprovado_por INT NULL,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
);

-- Tabela de vínculo entre usuários e empresas
CREATE TABLE IF NOT EXISTS usuarios_empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_empresa (usuario_id, empresa_id)
);

-- Verificar se as tabelas foram criadas
SELECT 'Tabelas criadas com sucesso!' as status;
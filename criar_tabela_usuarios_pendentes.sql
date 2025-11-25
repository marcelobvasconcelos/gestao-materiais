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
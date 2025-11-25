CREATE TABLE IF NOT EXISTS locais_empresas (
    local_id INT NOT NULL,
    empresa_id INT NOT NULL,
    PRIMARY KEY (local_id, empresa_id),
    FOREIGN KEY (local_id) REFERENCES locais_armazenamento(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE
);

-- Opcional: Inserir vínculos iniciais para locais existentes (vincular a todas as empresas ou nenhuma)
-- Por padrão, deixaremos sem vínculo, o usuário deverá editar.

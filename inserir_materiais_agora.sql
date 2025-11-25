-- Inserir materiais imediatamente
USE gestao_materiais_terceirizados;

-- Inserir materiais de teste
INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES
('Detergente Neutro', 'LIMPEM0001', 1, 2, 1, 1, 50.0, 1),
('Papel Higiênico', 'LIMPEM0002', 1, 5, 1, 1, 100.0, 1),
('Chave de Fenda', 'FEREM0001', 2, 1, 1, 1, 10.0, 1)
ON DUPLICATE KEY UPDATE nome=VALUES(nome);
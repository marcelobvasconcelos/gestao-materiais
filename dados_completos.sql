-- Inserir dados completos com estrutura correta
USE gestao_materiais_terceirizados;

-- Categorias
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES 
(1, 'Limpeza', 'Produtos de limpeza e higiene'),
(2, 'Ferramentas', 'Ferramentas e equipamentos'),
(3, 'Equipamentos', 'Equipamentos diversos'),
(4, 'Escritório', 'Material de escritório'),
(5, 'Manutenção', 'Materiais para manutenção');

-- Unidades de medida
INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES 
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct');

-- Locais
INSERT IGNORE INTO locais_armazenamento (id, nome, descricao) VALUES 
(1, 'Almoxarifado Central', 'Depósito principal da universidade'),
(2, 'Almoxarifado Limpeza', 'Materiais de limpeza e higiene'),
(3, 'Almoxarifado Manutenção', 'Ferramentas e materiais de manutenção'),
(4, 'Almoxarifado Escritório', 'Material de escritório e papelaria');

-- Materiais de exemplo
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES
('Detergente Neutro', 'LIMP001', 1, 2, 1, 2, 50.0, 1),
('Papel Higiênico', 'LIMP002', 1, 5, 1, 2, 100.0, 1),
('Chave de Fenda', 'FERR001', 2, 1, 1, 3, 10.0, 1),
('Papel A4', 'ESCR001', 4, 5, 1, 4, 25.0, 1);

-- Verificar
SELECT 'Materiais cadastrados:' as status;
SELECT m.id, m.nome, m.codigo_sku, m.estoque_atual, c.nome as categoria, u.simbolo as unidade
FROM materiais m 
LEFT JOIN categorias_materiais c ON m.categoria_id = c.id
LEFT JOIN unidades_medida u ON m.unidade_medida_id = u.id;
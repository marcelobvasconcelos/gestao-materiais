-- Inserir categoria ID 1 que estava faltando
USE gestao_materiais_terceirizados;

INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES (1, 'Limpeza', 'Produtos de limpeza e higiene');

-- Verificar se foi inserida
SELECT id, nome FROM categorias_materiais ORDER BY id;
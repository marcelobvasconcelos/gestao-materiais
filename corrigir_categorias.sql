-- Corrigir categorias e dependências
USE gestao_materiais_terceirizados;

-- Verificar estrutura da tabela categorias_materiais
DESCRIBE categorias_materiais;

-- Inserir categoria com a estrutura correta
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES (1, 'Limpeza', 'Produtos de limpeza');

-- Verificar se foi inserida
SELECT * FROM categorias_materiais WHERE id = 1;
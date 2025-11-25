-- Verificar e corrigir problemas com materiais
USE gestao_materiais_terceirizados;

-- Verificar se tabela materiais existe
SELECT 'Verificando tabela materiais...' as status;
SHOW TABLES LIKE 'materiais';

-- Verificar estrutura
SELECT 'Estrutura da tabela materiais:' as status;
DESCRIBE materiais;

-- Contar materiais existentes
SELECT 'Total de materiais:' as status, COUNT(*) as total FROM materiais;

-- Verificar materiais ativos
SELECT 'Materiais ativos:' as status, COUNT(*) as total FROM materiais WHERE ativo = 1;

-- Testar a consulta exata da API
SELECT 'Testando consulta da API:' as status;
SELECT m.id, m.nome, m.codigo_sku, m.estoque_atual, e.nome as empresa_nome 
FROM materiais m 
LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id 
WHERE m.ativo = 1 
LIMIT 5;

-- Se não há materiais, inserir alguns de teste
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES
('Detergente Neutro', 'LIMPEM0001', 1, 2, 1, 1, 50.0, 1),
('Papel Higiênico', 'LIMPEM0002', 1, 5, 1, 1, 100.0, 1),
('Chave de Fenda', 'FEREM0001', 2, 1, 1, 1, 10.0, 1);

-- Verificar novamente após inserção
SELECT 'Após inserção - Total de materiais:' as status, COUNT(*) as total FROM materiais WHERE ativo = 1;
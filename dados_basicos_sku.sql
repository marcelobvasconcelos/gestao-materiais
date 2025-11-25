-- ============================================================================
-- DADOS BÁSICOS PARA SISTEMA SKU
-- ============================================================================

USE gestao_materiais_terceirizados;

-- Garantir que existem categorias básicas
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES
(1, 'Limpeza', 'Produtos de limpeza e higiene'),
(2, 'Ferramentas', 'Ferramentas e equipamentos'),
(3, 'Equipamentos', 'Equipamentos diversos'),
(4, 'Escritório', 'Material de escritório'),
(5, 'Manutenção', 'Materiais para manutenção'),
(6, 'Segurança', 'Equipamentos de segurança'),
(7, 'Informática', 'Materiais de informática'),
(8, 'Elétrica', 'Materiais elétricos');

-- Garantir que existem unidades de medida básicas
INSERT IGNORE INTO unidades_medida (id, nome, simbolo) VALUES
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct'),
(6, 'Resma', 'rsm'),
(7, 'Rolo', 'rl'),
(8, 'Lata', 'lt'),
(9, 'Metro', 'm'),
(10, 'Peça', 'pç');

-- Garantir que existem locais de armazenamento básicos
INSERT IGNORE INTO locais_armazenamento (id, nome, descricao) VALUES
(1, 'Almoxarifado Central', 'Depósito principal da universidade'),
(2, 'Almoxarifado Limpeza', 'Materiais de limpeza e higiene'),
(3, 'Almoxarifado Manutenção', 'Ferramentas e materiais de manutenção'),
(4, 'Almoxarifado Escritório', 'Material de escritório e papelaria'),
(5, 'Almoxarifado Segurança', 'Equipamentos de segurança'),
(6, 'Almoxarifado Informática', 'Materiais de informática');

-- Criar índice para otimizar busca de SKU
CREATE INDEX IF NOT EXISTS idx_materiais_sku ON materiais(codigo_sku);
CREATE INDEX IF NOT EXISTS idx_materiais_categoria ON materiais(categoria_id);
CREATE INDEX IF NOT EXISTS idx_materiais_empresa ON materiais(empresa_id);

-- Verificar estrutura
SELECT 'Categorias cadastradas:' as info;
SELECT id, nome FROM categorias_materiais WHERE ativo = 1;

SELECT 'Unidades de medida cadastradas:' as info;
SELECT id, nome, simbolo FROM unidades_medida WHERE ativo = 1;

SELECT 'Locais de armazenamento cadastrados:' as info;
SELECT id, nome FROM locais_armazenamento WHERE ativo = 1;
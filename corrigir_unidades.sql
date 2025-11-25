-- Corrigir unidades de medida
USE gestao_materiais_terceirizados;

-- Verificar estrutura da tabela unidades_medida
DESCRIBE unidades_medida;

-- Inserir unidades básicas (IDs 1-8)
INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES 
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct'),
(6, 'Resma', 'rsm'),
(7, 'Rolo', 'rl'),
(8, 'Lata', 'lt');

-- Verificar se foram inseridas
SELECT id, descricao, simbolo FROM unidades_medida ORDER BY id;
-- Inserção simples - apenas materiais
USE gestao_materiais_terceirizados;

-- Desabilitar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 0;

-- Inserir materiais diretamente (sem depender de outras tabelas)
INSERT INTO materiais (nome, codigo_sku, estoque_atual, ativo) VALUES
('Detergente Neutro', 'LIMP001', 50.0, 1),
('Papel Higiênico', 'LIMP002', 100.0, 1),
('Sabão em Pó', 'LIMP003', 25.0, 1);

-- Reabilitar verificação
SET FOREIGN_KEY_CHECKS = 1;

-- Verificar
SELECT COUNT(*) as total FROM materiais;
SELECT id, nome, codigo_sku, estoque_atual FROM materiais;
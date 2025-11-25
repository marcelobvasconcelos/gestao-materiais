-- Solução rápida - inserir dados sem constraints
USE gestao_materiais_terceirizados;

-- Desabilitar verificação de chaves estrangeiras temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Inserir dados básicos
INSERT IGNORE INTO categorias_materiais (id, nome) VALUES (1, 'Limpeza');
INSERT IGNORE INTO unidades_medida (id, nome, simbolo) VALUES (1, 'Unidade', 'un');
INSERT IGNORE INTO locais_armazenamento (id, nome) VALUES (1, 'Almoxarifado Central');
INSERT IGNORE INTO empresas_terceirizadas (id, nome, tipo_servico, status) VALUES (1, 'Empresa Teste', 'Limpeza', 'Ativa');

-- Inserir materiais
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ativo) VALUES
('Detergente Neutro', 'LIMP001', 1, 1, 1, 1, 50.0, 1),
('Papel Higiênico', 'LIMP002', 1, 1, 1, 1, 100.0, 1),
('Sabão em Pó', 'LIMP003', 1, 1, 1, 1, 25.0, 1);

-- Reabilitar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;

-- Verificar se funcionou
SELECT 'Materiais inseridos:' as status;
SELECT id, nome, codigo_sku, estoque_atual FROM materiais;
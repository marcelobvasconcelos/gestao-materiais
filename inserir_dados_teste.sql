-- Inserir dados de teste para categorias e empresas
USE gestao_materiais_terceirizados;

-- Inserir categorias se não existirem
INSERT IGNORE INTO categorias_materiais (id, nome, descricao, ativo) VALUES
(1, 'Limpeza', 'Produtos de limpeza e higiene', 1),
(2, 'Ferramentas', 'Ferramentas e equipamentos', 1),
(3, 'Equipamentos', 'Equipamentos diversos', 1),
(4, 'Escritório', 'Material de escritório', 1),
(5, 'Manutenção', 'Materiais para manutenção', 1);

-- Inserir uma empresa de teste se não existir
INSERT IGNORE INTO empresas_terceirizadas (id, nome, tipo_servico, numero_contrato, status, responsavel_id) VALUES
(1, 'Empresa Teste', 'Limpeza', 'CT-2024-001', 'Ativa', 1);

-- Verificar dados inseridos
SELECT 'Categorias inseridas:' as info;
SELECT id, nome FROM categorias_materiais;

SELECT 'Empresas inseridas:' as info;
SELECT id, nome FROM empresas_terceirizadas;
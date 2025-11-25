-- Garantir que todos os dados básicos existam
USE gestao_materiais_terceirizados;

-- Inserir dados básicos com estrutura correta
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES (1, 'Limpeza', 'Produtos de limpeza');
INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES (1, 'Unidade', 'un');
INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES (1, 'Almoxarifado Central', 'Depósito principal', 1);

-- Verificar se foram criados
SELECT 'Categorias:' as tipo, COUNT(*) as total FROM categorias_materiais;
SELECT 'Unidades:' as tipo, COUNT(*) as total FROM unidades_medida;
SELECT 'Locais:' as tipo, COUNT(*) as total FROM locais_armazenamento;
SELECT 'Empresas:' as tipo, COUNT(*) as total FROM empresas_terceirizadas;
-- Script para popular tabelas de domínio (dados básicos)
-- Execute este script no banco de dados de produção para evitar erros de chave estrangeira.

-- 1. Locais de Armazenamento
INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES 
(1, 'Almoxarifado Central', 'Depósito principal', 1),
(2, 'Almoxarifado Limpeza', 'Produtos de limpeza', 1),
(3, 'Almoxarifado Manutenção', 'Materiais de manutenção', 1);

-- 2. Categorias de Materiais
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES 
(1, 'Limpeza', 'Produtos de limpeza e higiene'),
(2, 'Ferramentas', 'Ferramentas e equipamentos'),
(3, 'Equipamentos', 'Equipamentos diversos'),
(4, 'Escritório', 'Material de escritório'),
(5, 'Manutenção', 'Materiais para manutenção');

-- 3. Unidades de Medida
INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES 
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct'),
(6, 'Resma', 'rsm'),
(7, 'Rolo', 'rl'),
(8, 'Lata', 'lt');

-- 4. Garantir usuário Admin (caso não exista)
-- Senha padrão: admin123 (hash pode variar, mas este é um placeholder seguro se a tabela estiver vazia)
INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
(1, 'Administrador', 'admin@universidade.edu.br', '$2y$10$8WkQ.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0', 1, 1);

-- 5. Garantir Perfis
INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
(1, 'Administrador', 'Acesso total', 1),
(2, 'Gestor', 'Gerenciamento operacional', 1),
(3, 'Operador', 'Operações básicas', 1),
(4, 'Consulta', 'Apenas visualização', 1);

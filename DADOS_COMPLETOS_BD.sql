-- ============================================================================
-- DADOS COMPLETOS DO BANCO DE DADOS
-- Sistema de Gestão de Materiais Terceirizados
-- ============================================================================

USE gestao_materiais_terceirizados;

-- ============================================================================
-- 1. PERFIS DE ACESSO
-- ============================================================================
INSERT IGNORE INTO perfis_acesso (id, nome, descricao, permissoes, ativo) VALUES
(1, 'Administrador', 'Acesso total ao sistema', '{"criar": true, "editar": true, "excluir": true, "relatorios": true, "usuarios": true}', 1),
(2, 'Gestor', 'Gerenciamento operacional', '{"criar": true, "editar": true, "excluir": false, "relatorios": true, "usuarios": false}', 1),
(3, 'Operador', 'Operações básicas', '{"criar": true, "editar": false, "excluir": false, "relatorios": false, "usuarios": false}', 1),
(4, 'Consulta', 'Apenas visualização', '{"criar": false, "editar": false, "excluir": false, "relatorios": true, "usuarios": false}', 1);

-- ============================================================================
-- 2. USUÁRIO ADMINISTRADOR PADRÃO
-- ============================================================================
-- Senha: admin123 (hash gerado com password_hash())
INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, departamento, ativo) VALUES
(1, 'Administrador do Sistema', 'admin@universidade.edu.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'TI', 1);

-- ============================================================================
-- 3. CATEGORIAS DE MATERIAIS
-- ============================================================================
INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES
(1, 'Limpeza', 'Produtos de limpeza e higiene pessoal'),
(2, 'Ferramentas', 'Ferramentas manuais e equipamentos básicos'),
(3, 'Equipamentos', 'Equipamentos eletrônicos e eletrodomésticos'),
(4, 'Escritório', 'Material de escritório e papelaria'),
(5, 'Manutenção', 'Materiais para manutenção predial e equipamentos'),
(6, 'Segurança', 'Equipamentos de proteção individual e coletiva'),
(7, 'Informática', 'Equipamentos e acessórios de informática'),
(8, 'Elétrica', 'Materiais elétricos e eletrônicos');

-- ============================================================================
-- 4. UNIDADES DE MEDIDA
-- ============================================================================
INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct'),
(6, 'Resma', 'rsm'),
(7, 'Rolo', 'rl'),
(8, 'Lata', 'lt'),
(9, 'Metro', 'm'),
(10, 'Metro quadrado', 'm²'),
(11, 'Galão', 'gal'),
(12, 'Saco', 'sc'),
(13, 'Tubo', 'tb'),
(14, 'Frasco', 'fr'),
(15, 'Conjunto', 'cj');

-- ============================================================================
-- 5. LOCAIS DE ARMAZENAMENTO
-- ============================================================================
INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES
(1, 'Almoxarifado Central', 'Depósito principal da universidade', 1),
(2, 'Almoxarifado Limpeza', 'Materiais de limpeza e higiene', 1),
(3, 'Almoxarifado Manutenção', 'Ferramentas e materiais de manutenção', 1),
(4, 'Almoxarifado Escritório', 'Material de escritório e papelaria', 1),
(5, 'Almoxarifado Informática', 'Equipamentos e acessórios de TI', 1),
(6, 'Almoxarifado Segurança', 'Equipamentos de proteção e segurança', 1),
(7, 'Depósito Externo A', 'Depósito auxiliar - Bloco A', 1),
(8, 'Depósito Externo B', 'Depósito auxiliar - Bloco B', 1);

-- ============================================================================
-- 6. EMPRESAS TERCEIRIZADAS (EXEMPLOS)
-- ============================================================================
INSERT IGNORE INTO empresas_terceirizadas (id, nome, tipo_servico, numero_contrato, cnpj, status, responsavel_id) VALUES
(1, 'CleanService Ltda', 'Limpeza e Conservação', 'CT-2024-001', '12.345.678/0001-90', 'Ativa', 1),
(2, 'TechMaint Soluções', 'Manutenção Predial', 'CT-2024-002', '23.456.789/0001-01', 'Ativa', 1),
(3, 'SecureGuard Segurança', 'Segurança Patrimonial', 'CT-2024-003', '34.567.890/0001-12', 'Ativa', 1),
(4, 'GreenGarden Jardinagem', 'Jardinagem e Paisagismo', 'CT-2024-004', '45.678.901/0001-23', 'Ativa', 1),
(5, 'InfoTech Suporte', 'Suporte Técnico TI', 'CT-2024-005', '56.789.012/0001-34', 'Ativa', 1);

-- ============================================================================
-- 7. MATERIAIS DE EXEMPLO
-- ============================================================================

-- Materiais de Limpeza (CleanService)
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
('Detergente Neutro 5L', 'LIMCL0001', 1, 2, 1, 2, 25.00, 10.00, 50.00, 1),
('Desinfetante Pinho 2L', 'LIMCL0002', 1, 2, 1, 2, 30.00, 15.00, 60.00, 1),
('Papel Higiênico 30m', 'LIMCL0003', 1, 7, 1, 2, 150.00, 50.00, 300.00, 1),
('Sabão em Pó 2kg', 'LIMCL0004', 1, 3, 1, 2, 20.00, 8.00, 40.00, 1),
('Álcool Gel 70% 1L', 'LIMCL0005', 1, 2, 1, 2, 40.00, 20.00, 80.00, 1),
('Luva Descartável (cx)', 'LIMCL0006', 1, 4, 1, 2, 15.00, 5.00, 30.00, 1),
('Saco de Lixo 100L', 'LIMCL0007', 1, 5, 1, 2, 80.00, 30.00, 150.00, 1),
('Esponja Dupla Face', 'LIMCL0008', 1, 1, 1, 2, 200.00, 50.00, 400.00, 1);

-- Ferramentas e Manutenção (TechMaint)
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
('Chave de Fenda Phillips', 'FERTE0001', 2, 1, 2, 3, 12.00, 5.00, 25.00, 1),
('Chave Inglesa 10"', 'FERTE0002', 2, 1, 2, 3, 8.00, 3.00, 15.00, 1),
('Martelo 500g', 'FERTE0003', 2, 1, 2, 3, 6.00, 2.00, 12.00, 1),
('Furadeira Elétrica', 'EQUTE0001', 3, 1, 2, 3, 4.00, 1.00, 8.00, 1),
('Parafuso Phillips 3x25', 'MANTE0001', 5, 4, 2, 3, 500.00, 200.00, 1000.00, 1),
('Tinta Látex Branca 18L', 'MANTE0002', 5, 8, 2, 3, 10.00, 4.00, 20.00, 1),
('Fita Isolante Preta', 'MANTE0003', 5, 7, 2, 3, 25.00, 10.00, 50.00, 1);

-- Material de Escritório (Exemplo genérico)
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
('Papel A4 75g (resma)', 'ESCGE0001', 4, 6, 1, 4, 50.00, 20.00, 100.00, 1),
('Caneta Esferográfica Azul', 'ESCGE0002', 4, 1, 1, 4, 200.00, 50.00, 400.00, 1),
('Grampeador Médio', 'ESCGE0003', 4, 1, 1, 4, 15.00, 5.00, 30.00, 1),
('Grampo 26/6 (cx)', 'ESCGE0004', 4, 4, 1, 4, 25.00, 10.00, 50.00, 1),
('Pasta Suspensa A4', 'ESCGE0005', 4, 1, 1, 4, 100.00, 30.00, 200.00, 1);

-- Equipamentos de Segurança (SecureGuard)
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
('Capacete de Segurança', 'SEGSE0001', 6, 1, 3, 6, 20.00, 8.00, 40.00, 1),
('Óculos de Proteção', 'SEGSE0002', 6, 1, 3, 6, 30.00, 12.00, 60.00, 1),
('Luva de Segurança Par', 'SEGSE0003', 6, 1, 3, 6, 50.00, 20.00, 100.00, 1),
('Colete Refletivo', 'SEGSE0004', 6, 1, 3, 6, 25.00, 10.00, 50.00, 1);

-- Equipamentos de Informática (InfoTech)
INSERT IGNORE INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
('Mouse Óptico USB', 'INFIN0001', 7, 1, 5, 5, 15.00, 5.00, 30.00, 1),
('Teclado ABNT2 USB', 'INFIN0002', 7, 1, 5, 5, 12.00, 4.00, 25.00, 1),
('Cabo HDMI 2m', 'INFIN0003', 7, 1, 5, 5, 20.00, 8.00, 40.00, 1),
('Pen Drive 16GB', 'INFIN0004', 7, 1, 5, 5, 25.00, 10.00, 50.00, 1);

-- ============================================================================
-- 8. USUÁRIOS DE EXEMPLO (GESTORES DAS EMPRESAS)
-- ============================================================================
-- Senhas: 123456 (hash gerado com password_hash())
INSERT IGNORE INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES
('João Silva', 'joao.silva@cleanservice.com', '$2y$10$e0MYzXyjpJS7Pd0QLeUbnOahLl.B9iHNyK0rX9z8GeW.A2Bq2O1gC', 2, 'Operações', 1),
('Maria Santos', 'maria.santos@techmaint.com', '$2y$10$e0MYzXyjpJS7Pd0QLeUbnOahLl.B9iHNyK0rX9z8GeW.A2Bq2O1gC', 2, 'Manutenção', 1),
('Carlos Oliveira', 'carlos.oliveira@secureguard.com', '$2y$10$e0MYzXyjpJS7Pd0QLeUbnOahLl.B9iHNyK0rX9z8GeW.A2Bq2O1gC', 3, 'Segurança', 1),
('Ana Costa', 'ana.costa@inftech.com', '$2y$10$e0MYzXyjpJS7Pd0QLeUbnOahLl.B9iHNyK0rX9z8GeW.A2Bq2O1gC', 3, 'TI', 1);

-- ============================================================================
-- 9. VÍNCULOS USUÁRIOS-EMPRESAS
-- ============================================================================
INSERT IGNORE INTO usuarios_empresas (usuario_id, empresa_id) VALUES
(2, 1), -- João Silva -> CleanService
(3, 2), -- Maria Santos -> TechMaint  
(4, 3), -- Carlos Oliveira -> SecureGuard
(5, 5); -- Ana Costa -> InfoTech

-- ============================================================================
-- 10. MOVIMENTAÇÕES DE EXEMPLO (ENTRADAS)
-- ============================================================================
INSERT IGNORE INTO movimentacoes_entrada (data_entrada, material_id, quantidade, nota_fiscal, responsavel_id, local_destino_id, observacao) VALUES
('2024-01-15 09:00:00', 1, 20.00, 'NF-001234', 2, 2, 'Reposição mensal de detergente'),
('2024-01-15 10:30:00', 3, 100.00, 'NF-001235', 2, 2, 'Estoque papel higiênico'),
('2024-01-16 14:00:00', 9, 5.00, 'NF-002001', 3, 3, 'Novas chaves de fenda'),
('2024-01-16 15:30:00', 11, 2.00, 'NF-002002', 3, 3, 'Martelos para manutenção'),
('2024-01-17 08:00:00', 18, 10.00, 'NF-003001', 4, 6, 'Capacetes de segurança'),
('2024-01-17 11:00:00', 22, 8.00, 'NF-005001', 5, 5, 'Mouses para laboratório');

-- ============================================================================
-- 11. MOVIMENTAÇÕES DE EXEMPLO (SAÍDAS)
-- ============================================================================
INSERT IGNORE INTO movimentacoes_saida (data_saida, material_id, quantidade, empresa_solicitante_id, finalidade, responsavel_autorizacao_id, local_destino, observacao) VALUES
('2024-01-18 09:00:00', 1, 5.00, 1, 'Limpeza', 2, 'Bloco A - Salas de aula', 'Limpeza semanal'),
('2024-01-18 10:00:00', 3, 20.00, 1, 'Limpeza', 2, 'Banheiros Bloco B', 'Reposição banheiros'),
('2024-01-19 14:00:00', 9, 2.00, 2, 'Manutenção', 3, 'Laboratório de Química', 'Reparo bancadas'),
('2024-01-19 15:00:00', 18, 3.00, 3, 'Segurança', 4, 'Obra nova biblioteca', 'EPI para obra'),
('2024-01-20 08:30:00', 22, 5.00, 5, 'Uso Administrativo', 5, 'Secretaria Acadêmica', 'Substituição equipamentos');

-- ============================================================================
-- VERIFICAÇÕES FINAIS
-- ============================================================================

-- Contar registros inseridos
SELECT 'RESUMO DA INSERÇÃO DE DADOS:' as info;
SELECT 'Perfis de Acesso' as tabela, COUNT(*) as registros FROM perfis_acesso
UNION ALL
SELECT 'Usuários', COUNT(*) FROM usuarios  
UNION ALL
SELECT 'Categorias', COUNT(*) FROM categorias_materiais
UNION ALL
SELECT 'Unidades de Medida', COUNT(*) FROM unidades_medida
UNION ALL  
SELECT 'Locais', COUNT(*) FROM locais_armazenamento
UNION ALL
SELECT 'Empresas', COUNT(*) FROM empresas_terceirizadas
UNION ALL
SELECT 'Materiais', COUNT(*) FROM materiais
UNION ALL
SELECT 'Vínculos Usuário-Empresa', COUNT(*) FROM usuarios_empresas
UNION ALL
SELECT 'Movimentações Entrada', COUNT(*) FROM movimentacoes_entrada
UNION ALL
SELECT 'Movimentações Saída', COUNT(*) FROM movimentacoes_saida;

-- ============================================================================
-- USUÁRIOS PARA TESTE
-- ============================================================================
SELECT 'USUÁRIOS PARA TESTE:' as info;
SELECT 
    u.nome,
    u.email,
    'Senha: admin123 ou 123456' as senha,
    p.nome as perfil,
    CASE 
        WHEN u.perfil_id = 1 THEN 'Todas as empresas'
        ELSE GROUP_CONCAT(e.nome SEPARATOR ', ')
    END as empresas_acesso
FROM usuarios u
LEFT JOIN perfis_acesso p ON u.perfil_id = p.id
LEFT JOIN usuarios_empresas ue ON u.id = ue.usuario_id  
LEFT JOIN empresas_terceirizadas e ON ue.empresa_id = e.id
GROUP BY u.id, u.nome, u.email, p.nome, u.perfil_id
ORDER BY u.perfil_id, u.nome;
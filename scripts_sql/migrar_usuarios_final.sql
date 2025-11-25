-- ============================================================
-- SCRIPT FINAL: Migrar Usuários Pendentes com CARGO
-- ============================================================

-- Versão corrigida incluindo o campo 'cargo'
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, cargo, ativo)
SELECT 
    nome,
    email,
    senha,
    2 AS perfil_id,  -- 2 = Gestor
    COALESCE(departamento, 'Não informado') AS departamento,
    'Colaborador' AS cargo,  -- Valor padrão para cargo
    1 AS ativo
FROM usuarios_pendentes
WHERE status = 'Aprovado'
AND id IN (1, 2, 3);  -- IDs específicos dos 3 usuários

-- Verificar se foram inseridos
SELECT id, nome, email, cargo, perfil_id, ativo
FROM usuarios 
WHERE email IN ('testedehoje2@gmail.com', 'testedehoje@gmail.com', 'marcelo_teste@gmail.com')
ORDER BY id DESC;

-- ============================================================
-- ALTERNATIVA: Inserir um por vez (mais seguro)
-- ============================================================

-- Usuário ID 1
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, cargo, ativo)
SELECT nome, email, senha, 2, COALESCE(departamento, 'TI'), 'Colaborador', 1
FROM usuarios_pendentes WHERE id = 1 LIMIT 1;

-- Usuário ID 2
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, cargo, ativo)
SELECT nome, email, senha, 2, COALESCE(departamento, 'TI'), 'Colaborador', 1
FROM usuarios_pendentes WHERE id = 2 LIMIT 1;

-- Usuário ID 3
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, cargo, ativo)
SELECT nome, email, senha, 2, COALESCE(departamento, 'TI'), 'Colaborador', 1
FROM usuarios_pendentes WHERE id = 3 LIMIT 1;

-- ============================================================
-- SCRIPT DE CORREÇÃO: Migrar Usuários Pendentes Aprovados
-- VERSÃO 2 - CORRIGIDO PARA PROBLEMAS DE COLLATION
-- ============================================================

-- 1. Migrar usuários pendentes aprovados (SEM verificar duplicados primeiro)
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo)
SELECT 
    nome,
    email,
    senha,
    2 AS perfil_id,  -- 2 = Gestor
    COALESCE(departamento, 'Não informado') AS departamento,
    1 AS ativo
FROM usuarios_pendentes
WHERE status = 'Aprovado'
AND id NOT IN (
    -- Usar IDs ao invés de emails para evitar problema de collation
    SELECT up.id 
    FROM usuarios_pendentes up
    INNER JOIN usuarios u ON up.email COLLATE utf8mb4_unicode_ci = u.email COLLATE utf8mb4_unicode_ci
    WHERE up.status = 'Aprovado'
);

-- 2. Verificar se foram inseridos
SELECT id, nome, email, perfil_id, ativo, created_at
FROM usuarios 
WHERE email IN ('testedehoje2@gmail.com', 'testedehoje@gmail.com', 'marcelo_teste@gmail.com')
ORDER BY id DESC;

-- 3. Verificar total
SELECT COUNT(*) as total_usuarios FROM usuarios;

-- ============================================================
-- Se preferir uma abordagem mais direta (insere 1 por vez):
-- ============================================================

-- OPÇÃO ALTERNATIVA: Inserir manualmente cada usuário
-- Copie os valores da tabela usuarios_pendentes e cole abaixo:

-- Usuário 1: testedehoje2
-- INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo)
-- VALUES ('testedehoje2', 'testedehoje2@gmail.com', '[copie o hash da senha aqui]', 2, 'TI', 1);

-- Usuário 2: teste de hoje  
-- INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo)
-- VALUES ('teste de hoje', 'testedehoje@gmail.com', '[copie o hash da senha aqui]', 2, 'TI', 1);

-- Usuário 3: Marcelo_teste
-- INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo)
-- VALUES ('Marcelo_teste', 'marcelo_teste@gmail.com', '[copie o hash da senha aqui]', 2, 'TI', 1);

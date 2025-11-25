-- ============================================================
-- SCRIPT DE CORREÇÃO: Migrar Usuários Pendentes Aprovados
-- ============================================================
-- Execute este script DIRETAMENTE no MySQL/phpMyAdmin
-- ============================================================

-- 1. Verificar quais usuários pendentes aprovados NÃO estão em usuarios
SELECT up.id, up.nome, up.email, up.status
FROM usuarios_pendentes up
LEFT JOIN usuarios u ON up.email = u.email
WHERE up.status = 'Aprovado' AND u.id IS NULL;

-- Se aparecerem os 3 usuários acima, execute os comandos abaixo:

-- 2. Migrar os usuários pendentes aprovados para a tabela usuarios
INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo)
SELECT 
    nome,
    email,
    senha,
    2 AS perfil_id,  -- 2 = Gestor (pode ajustar depois)
    departamento,
    1 AS ativo
FROM usuarios_pendentes
WHERE status = 'Aprovado'
AND email NOT IN (SELECT email FROM usuarios);

-- 3. Verificar se foram inseridos
SELECT id, nome, email, perfil_id, ativo 
FROM usuarios 
WHERE email IN (
    SELECT email FROM usuarios_pendentes WHERE status = 'Aprovado'
)
ORDER BY id DESC;

-- 4. (OPCIONAL) Se quiser limpar os pendentes aprovados após migração:
-- DELETE FROM usuarios_pendentes WHERE status = 'Aprovado';

-- ============================================================
-- NOTAS:
-- - Perfil padrão: 2 (Gestor)
-- - Todos ficam ativos (ativo = 1)
-- - Senha vem já em hash da tabela pendentes
-- - Depois você pode vincular empresas manualmente
-- ============================================================

-- Script de Migração para Atualizar Banco de Dados
-- Execute este script no seu banco de dados de produção (phpMyAdmin ou terminal MySQL)

-- 1. Adicionar coluna local_origem_id na tabela movimentacoes_saida
-- Verifica se a coluna não existe antes de adicionar (para evitar erro se rodar 2x, embora MySQL puro não tenha IF NOT EXISTS para coluna direto no ALTER simples, o comando abaixo é o padrão)
ALTER TABLE movimentacoes_saida 
ADD COLUMN local_origem_id INT(11) NULL DEFAULT NULL;

-- 2. Adicionar chave estrangeira para a nova coluna
ALTER TABLE movimentacoes_saida 
ADD CONSTRAINT fk_saida_local 
FOREIGN KEY (local_origem_id) REFERENCES locais_armazenamento(id);

-- 3. (Opcional/Preventivo) Verificar se local_destino_id existe em movimentacoes_entrada
-- Caso não exista, o comando seria:
-- ALTER TABLE movimentacoes_entrada ADD COLUMN local_destino_id INT(11) NULL DEFAULT NULL;
-- ALTER TABLE movimentacoes_entrada ADD CONSTRAINT fk_entrada_local FOREIGN KEY (local_destino_id) REFERENCES locais_armazenamento(id);

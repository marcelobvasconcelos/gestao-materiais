-- Adicionar campo número do contrato na tabela empresas_terceirizadas
ALTER TABLE empresas_terceirizadas 
ADD COLUMN numero_contrato VARCHAR(50) AFTER tipo_servico;
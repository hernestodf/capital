-- Adicionar campo de comprovante de pagamento em contas_pagar
-- Data: 2026-04-27

ALTER TABLE contas_pagar 
ADD COLUMN comprovante_anexo VARCHAR(255) NULL COMMENT 'Path do arquivo do comprovante de pagamento' AFTER observacao;

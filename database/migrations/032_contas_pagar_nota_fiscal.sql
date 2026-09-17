-- Migration 032: Adicionar coluna nota_fiscal em contas_pagar
-- Data: 2026-05-24

ALTER TABLE `contas_pagar`
  ADD COLUMN `nota_fiscal` varchar(255) DEFAULT NULL AFTER `comprovante_anexo`;

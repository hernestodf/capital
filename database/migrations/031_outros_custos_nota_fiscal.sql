-- Migration 031: Adicionar coluna nota_fiscal em evento_outros_custos
-- Data: 2026-05-24

ALTER TABLE `evento_outros_custos`
  ADD COLUMN `nota_fiscal` varchar(255) DEFAULT NULL AFTER `observacao`;

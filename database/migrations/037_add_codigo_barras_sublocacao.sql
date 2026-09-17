-- Migration 037: Adicionar codigo_barras na Sublocacao e renomear produto_fornecedor
-- Data: 2026-06-11
--
-- 1. Adiciona coluna codigo_barras em produto_evento_sublocacao
-- 2. Renomeia produto_fornecedor para produto (se ainda existir como produto_fornecedor)

ALTER TABLE produto_evento_sublocacao
  ADD COLUMN IF NOT EXISTS codigo_barras VARCHAR(100) DEFAULT NULL AFTER id_sublocacao_item;

-- Se a coluna produto nao existir mas produto_fornecedor existir, renomeia
-- NOTA: Se produto ja existe como VARCHAR, pule esta etapa
-- ALTER TABLE produto_evento_sublocacao CHANGE COLUMN produto_fornecedor produto VARCHAR(255) DEFAULT NULL;

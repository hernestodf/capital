-- Migration 020: Adicionar suporte a parcelas em contas_pagar
-- Data: 2026-04-27
-- Finalidade: Suporte para pagamento com entrada + parcelas para fornecedores

ALTER TABLE contas_pagar
  ADD COLUMN entrada_valor DECIMAL(10,2) NULL AFTER valor,
  ADD COLUMN parcelas_qTD INT NULL AFTER entrada_valor,
  ADD COLUMN parcelas_valor DECIMAL(10,2) NULL AFTER parcelas_qtd,
  ADD COLUMN primeira_parcela_vencimento DATE NULL AFTER parcelas_valor,
  ADD COLUMN intervalo_parcelas INT DEFAULT 30 AFTER primeira_parcela_vencimento;

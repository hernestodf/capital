-- Migration: correcao_estoque_producao.sql
-- Corrige incompatibilidades encontradas na auditoria do modulo de estoque
-- Data: 2026-04-29
-- Aplicar: mysql -u usuario -p nome_banco < correcao_estoque_producao.sql

-- ============================================================
-- 1. Colunas de auditoria na tabela produtos
-- ============================================================
-- O migration 026 adiciona estas colunas mas pode nao ter sido aplicado.
-- Se ja existirem, ADD COLUMN IF NOT EXISTS ignora sem erro.

ALTER TABLE produtos 
    ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1 AFTER observacao,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- ============================================================
-- 2. Popular coluna status com valor padrao para registros existentes
-- ============================================================
UPDATE produtos SET status = 1 WHERE status IS NULL;

-- ============================================================
-- 3. Indices de performance
-- ============================================================
-- Esses indices aceleram as consultas de busca e filtragem por status.

ALTER TABLE produtos ADD INDEX IF NOT EXISTS idx_produto (produto);
ALTER TABLE seriaisproduto ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE seriaisproduto ADD INDEX IF NOT EXISTS idx_produto_status (id_produto, status);

-- ============================================================
-- 4. Verificacao pos-migracao
-- ============================================================
-- Descomente as linhas abaixo para verificar se a migracao funcionou:

-- DESC produtos;
-- SELECT COUNT(*) as total FROM produtos;
-- SELECT COUNT(*) as com_status FROM produtos WHERE status IS NOT NULL;
-- SHOW INDEX FROM produtos;
-- SHOW INDEX FROM seriaisproduto;

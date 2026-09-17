-- Migration: 026_estoque_indexes_and_audit.sql
-- Adicionar indices e colunas de auditoria ao modulo de estoque
-- Data: 2026-04-29

-- Indices para performance nas buscas
ALTER TABLE produtos ADD INDEX IF NOT EXISTS idx_produto (produto);
ALTER TABLE seriaisproduto ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE seriaisproduto ADD INDEX IF NOT EXISTS idx_produto_status (id_produto, status);

-- Colunas de auditoria temporal na tabela produtos
ALTER TABLE produtos 
    ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1 AFTER observacao,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Migration 036: Integrar Estoque Próprio com Sublocação
-- Data: 2026-06-10
-- 
-- 1. Nova tabela: produto_evento_seriais (vincula seriais do estoque a itens do evento)
-- 2. ALTER TABLE produto_evento_sublocacao: adiciona serial_fornecedor, produto_fornecedor, custo_unit
-- 3. ALTER TABLE produtos_evento: adiciona qtd_alocada, qtd_sublocada

CREATE TABLE IF NOT EXISTS produto_evento_seriais (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  id_produto_evento    INT NOT NULL,
  id_serial            INT NOT NULL,
  status               ENUM('alocado','entregue','devolvido') DEFAULT 'alocado',
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_produto_evento) REFERENCES produtos_evento(id) ON DELETE CASCADE,
  FOREIGN KEY (id_serial) REFERENCES seriaisproduto(id) ON DELETE CASCADE,
  UNIQUE KEY uk_serial_evento (id_serial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE produto_evento_sublocacao
  ADD COLUMN serial_fornecedor VARCHAR(255) DEFAULT NULL AFTER valor_unit,
  ADD COLUMN produto_fornecedor VARCHAR(255) DEFAULT NULL AFTER serial_fornecedor,
  ADD COLUMN custo_unit DECIMAL(10,2) DEFAULT 0 AFTER total;

ALTER TABLE produtos_evento
  ADD COLUMN qtd_alocada DECIMAL(10,2) DEFAULT 0 AFTER qtd,
  ADD COLUMN qtd_sublocada DECIMAL(10,2) DEFAULT 0 AFTER qtd_alocada;

-- Migration 032: Criar tabelas do módulo Sublocação
-- Data: 2026-06-09

CREATE TABLE IF NOT EXISTS sublocacao_itens (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  id_fornecedor   INT NOT NULL,
  produto         VARCHAR(255) NOT NULL,
  codigo          VARCHAR(100),
  quantidade      DECIMAL(10,2) DEFAULT 1,
  valor_unit      DECIMAL(10,2) DEFAULT 0,
  observacao      TEXT,
  status          TINYINT(1) DEFAULT 1,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS produto_evento_sublocacao (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  id_produto_evento    INT NOT NULL,
  id_fornecedor        INT NOT NULL,
  id_sublocacao_item   INT DEFAULT NULL,
  quantidade           DECIMAL(10,2) DEFAULT 1,
  valor_unit           DECIMAL(10,2) DEFAULT 0,
  total                DECIMAL(10,2) DEFAULT 0,
  status               ENUM('pendente','pago','cancelado') DEFAULT 'pendente',
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_produto_evento) REFERENCES produtos_evento(id) ON DELETE CASCADE,
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE CASCADE,
  FOREIGN KEY (id_sublocacao_item) REFERENCES sublocacao_itens(id) ON DELETE SET NULL
);

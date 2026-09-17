-- Migration: 012_create_salas_produtos_tables.sql
-- Criar tabelas para modulo de Salas e Produtos de Eventos
-- Data: 2026-04-21

-- Tabela de categorias de sala (para ordenacao da listagem)
CREATE TABLE IF NOT EXISTS categorias_sala (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL,
    ordem INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ordem (ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de salas de evento
CREATE TABLE IF NOT EXISTS salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_evento INT NOT NULL,
    nome_sala VARCHAR(255) NOT NULL,
    categoria_sala INT,
    orientacoes_montagem TEXT,
    ordem INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_evento) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_sala) REFERENCES categorias_sala(id) ON DELETE SET NULL,
    INDEX idx_evento (id_evento),
    INDEX idx_ordem (ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de produtos de evento (itens por sala)
CREATE TABLE IF NOT EXISTS produtos_evento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_evento INT NOT NULL,
    id_sala INT NOT NULL,
    id_planilha INT,
    produto VARCHAR(255) NOT NULL,
    observacao_montagem TEXT,
    qtd DECIMAL(10,2) DEFAULT 1,
    valor_unit DECIMAL(10,2) DEFAULT 0.00,
    dias INT DEFAULT 1,
    total_item DECIMAL(10,2) DEFAULT 0.00,
    custo_unit DECIMAL(10,2) DEFAULT 0.00,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_evento) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sala) REFERENCES salas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_planilha) REFERENCES planilhas(id) ON DELETE SET NULL,
    INDEX idx_evento (id_evento),
    INDEX idx_sala (id_sala)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias padrao
INSERT IGNORE INTO categorias_sala (nome_categoria, ordem) VALUES
('Principal', 1),
('Secundaria', 2),
('Support', 3);

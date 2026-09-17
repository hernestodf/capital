-- Migration: 011_create_eventos_table.sql
-- Criar tabela de eventos (orcamentos e locacoes)
-- Data: 2026-04-21

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_produtor INT,
    id_demandante INT,
    nome_evento VARCHAR(255) NOT NULL,
    local_evento VARCHAR(255),
    os_cliente VARCHAR(100),
    demandante_local VARCHAR(255),
    telefone_demandantelocal VARCHAR(20),
    observacao TEXT,
    data_montagem DATE,
    hora_montagem TIME,
    data_inicio DATE,
    hora_inicio TIME,
    data_fim DATE,
    hora_fim TIME,
    data_desmontagem DATE,
    hora_desmontagem TIME,
    estado ENUM('O', 'L') DEFAULT 'O' COMMENT 'O=Orcamento, L=Locacao',
    status_locacao ENUM('A', 'F') DEFAULT 'A' COMMENT 'A=Andamento, F=Finalizada',
    evento_montado TINYINT(1) DEFAULT 0,
    evento_desmontado TINYINT(1) DEFAULT 0,
    observacoes_fechamento TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (id_produtor) REFERENCES produtores(id) ON DELETE SET NULL,
    FOREIGN KEY (id_demandante) REFERENCES demandantes(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_status_locacao (status_locacao),
    INDEX idx_data_inicio (data_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

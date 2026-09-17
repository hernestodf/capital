-- Migration 017: Criar tabelas de cotação de itens por fornecedor
-- Data: 2026-04-26
-- Sistema: SisLoc v2.5.0

-- ============================================
-- Tabela: item_cotacoes
-- Propostas de fornecedores por item do evento
-- ============================================
CREATE TABLE IF NOT EXISTS item_cotacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produto_evento INT NOT NULL,
    id_fornecedor INT NOT NULL,
    id_evento INT NOT NULL,
    valor_proposto DECIMAL(10,2) NOT NULL,
    vencedor ENUM('S','N') DEFAULT 'N',
    status ENUM('aguardando','com_proposta','vencedor_definido') DEFAULT 'aguardando',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_produto_fornecedor (id_produto_evento, id_fornecedor),
    INDEX idx_produto (id_produto_evento),
    INDEX idx_evento (id_evento),
    INDEX idx_fornecedor (id_fornecedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabela: item_cotacoes_parcelas
-- Parcelas de pagamento (fase futura)
-- ============================================
CREATE TABLE IF NOT EXISTS item_cotacoes_parcelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cotacao INT NOT NULL,
    numero_parcela TINYINT NOT NULL,
    descricao VARCHAR(100),
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    enviado_pagamento ENUM('S','N') DEFAULT 'N',
    pago ENUM('S','N') DEFAULT 'N',
    data_pagamento DATE NULL,
    comprovante VARCHAR(255) NULL,
    whatsapp_enviado ENUM('S','N') DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cotacao (id_cotacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabela: item_cotacoes_mensagens
-- Thread de emails entre admin e fornecedores
-- ============================================
CREATE TABLE IF NOT EXISTS item_cotacoes_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cotacao INT NOT NULL,
    id_fornecedor INT NOT NULL,
    tipo ENUM('enviado','recebido') NOT NULL,
    remetente VARCHAR(255) NOT NULL,
    destinatario VARCHAR(255) NOT NULL,
    assunto VARCHAR(500),
    corpo LONGTEXT,
    message_id_email VARCHAR(500) NULL,
    in_reply_to VARCHAR(500) NULL,
    lido ENUM('S','N') DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cotacao (id_cotacao),
    INDEX idx_fornecedor (id_fornecedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabela: item_cotacoes_anexos
-- Anexos das mensagens
-- ============================================
CREATE TABLE IF NOT EXISTS item_cotacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mensagem INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    tamanho INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mensagem (id_mensagem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Adicionar coluna fornecedor_vencedor em produtos_evento
-- ============================================
ALTER TABLE produtos_evento
    ADD COLUMN IF NOT EXISTS fornecedor_vencedor VARCHAR(255) DEFAULT NULL
    AFTER custo_unit;

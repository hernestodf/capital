-- Migration 025: Criar tabela de logs de requisições API
-- Data: 2026-04-27
-- Objetivo: Logar todas as requisições à API para rate limiting e auditoria

CREATE TABLE IF NOT EXISTS api_request_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL COMMENT 'ID da API key usada',
    endpoint VARCHAR(255) NOT NULL COMMENT 'Endpoint acessado',
    method VARCHAR(10) NOT NULL COMMENT 'Método HTTP (GET, POST, etc)',
    ip_address VARCHAR(45) NOT NULL COMMENT 'IP do cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key_id (api_key_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de requisições à API';

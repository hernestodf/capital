-- Migration 024: Criar tabela de API Keys para integrações externas
-- Data: 2026-04-27
-- Objetivo: Permitir autenticação via API Key para integrações externas

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nome descritivo da aplicação/site',
    api_key VARCHAR(64) NOT NULL UNIQUE COMMENT 'Chave API (hash SHA-256)',
    api_key_public VARCHAR(16) NOT NULL UNIQUE COMMENT 'Prefixo público para identificação (ex: sk_live_abc123)',
    permissions JSON COMMENT 'Permissões específicas (ex: ["colaboradores:write", "fornecedores:read"])',
    rate_limit INT DEFAULT 1000 COMMENT 'Máximo de requests por hora',
    ip_whitelist TEXT COMMENT 'IPs permitidos (JSON array, null = todos)',
    status TINYINT(1) DEFAULT 1 COMMENT '1=ativo, 0=inativo',
    last_used_at TIMESTAMP NULL COMMENT 'Último uso da API key',
    created_by INT COMMENT 'ID do usuário que criou',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_api_key (api_key),
    INDEX idx_api_key_public (api_key_public),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chaves de API para integrações externas';

-- Inserir API key de exemplo para desenvolvimento
-- IMPORTANTE: Alterar esta key em produção!
-- Esta key tem hash de 'sisloc_dev_key_2026'
INSERT INTO api_keys (name, api_key, api_key_public, permissions, rate_limit, status) VALUES
('Site Externo - Desenvolvimento', 
 SHA2('sisloc_dev_key_2026', 256), 
 'sk_dev_001', 
 '["auth:login", "auth:read", "colaboradores:write", "fornecedores:write", "categorias:write", "subcategorias:write", "montagem:write", "montagem:read", "eventos:read"]',
 1000, 
 1);

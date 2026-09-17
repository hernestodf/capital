-- Migration: Sistema de Aprendizado Estendido
-- Adiciona tabelas para feedback, interações e preferências

-- Feedback do usuário
CREATE TABLE IF NOT EXISTS ai_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarefa_id VARCHAR(36),
    agente VARCHAR(50) NOT NULL,
    tipo ENUM('positivo','negativo','sugestao') NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comentario TEXT,
    contexto JSON,
    aprendido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agente (agente),
    INDEX idx_tipo (tipo),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Histórico de interações
CREATE TABLE IF NOT EXISTS ai_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id VARCHAR(36),
    requisicao TEXT,
    agentes_executados JSON,
    resultado ENUM('sucesso','parcial','falha') DEFAULT 'sucesso',
    tempo_total INT DEFAULT 0,
    arquivos_gerados JSON,
    feedback_recebido ENUM('positivo','negativo','neutro'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_resultado (resultado),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preferências do usuário
CREATE TABLE IF NOT EXISTS ai_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id VARCHAR(36) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    chave VARCHAR(100) NOT NULL,
    valor TEXT,
    frequencia_uso INT DEFAULT 1,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_pref (usuario_id, chave),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessões de aprendizado
CREATE TABLE IF NOT EXISTS ai_learning_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id VARCHAR(36),
    agente VARCHAR(50),
    requisicao TEXT,
    resultado TEXT,
    erros_encontrados INT DEFAULT 0,
    correcoes_aplicadas INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_agente (agente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

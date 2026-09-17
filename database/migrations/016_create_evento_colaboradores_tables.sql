-- Modulo Recursos Humanos - Alocacao de Colaboradores em Eventos
-- Migration 016

-- 1. Tabela de alocacao de colaboradores em eventos
CREATE TABLE IF NOT EXISTS evento_colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_evento INT NOT NULL,
    id_colaborador INT NOT NULL,
    funcao VARCHAR(100) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    valor_diaria DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    data_vencimento_pagamento DATE NULL,
    data_pagamento DATE NULL,
    comprovante_anexo VARCHAR(255) NULL,
    enviar_pagamento ENUM('N','S') NOT NULL DEFAULT 'N',
    token_presenca VARCHAR(64) NOT NULL,
    status ENUM('A','I') NOT NULL DEFAULT 'A',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evento_colaborador (id_evento, id_colaborador),
    INDEX idx_evento (id_evento),
    INDEX idx_colaborador (id_colaborador),
    INDEX idx_token (token_presenca),
    INDEX idx_enviar_pagamento (enviar_pagamento),
    FOREIGN KEY (id_evento) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_colaborador) REFERENCES colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabela de presencas diarias
CREATE TABLE IF NOT EXISTS evento_colaborador_presencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_alocacao INT NOT NULL,
    data DATE NOT NULL,
    foto_entrada VARCHAR(255) NULL,
    geo_entrada_lat DECIMAL(10,8) NULL,
    geo_entrada_lng DECIMAL(11,8) NULL,
    hora_entrada_real DATETIME NULL,
    foto_saida VARCHAR(255) NULL,
    geo_saida_lat DECIMAL(10,8) NULL,
    geo_saida_lng DECIMAL(11,8) NULL,
    hora_saida_real DATETIME NULL,
    status ENUM('aguardando','parcial','completo') NOT NULL DEFAULT 'aguardando',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_alocacao_data (id_alocacao, data),
    INDEX idx_alocacao (id_alocacao),
    INDEX idx_data (data),
    INDEX idx_status (status),
    FOREIGN KEY (id_alocacao) REFERENCES evento_colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

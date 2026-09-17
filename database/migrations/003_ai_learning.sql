-- Tabela de padrões de aprendizado
CREATE TABLE IF NOT EXISTS ai_learning (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agente VARCHAR(50) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    contexto TEXT,
    padrao TEXT NOT NULL,
    exemplo TEXT,
    sucesso_rate DECIMAL(5,2) DEFAULT 1.00,
    frequencia INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_agente (agente),
    INDEX idx_tipo (tipo),
    INDEX idx_contexto (contexto(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de métricas de execução
CREATE TABLE IF NOT EXISTS ai_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarefa_id VARCHAR(36) NOT NULL,
    agente VARCHAR(50) NOT NULL,
    acao VARCHAR(255),
    tempo_execucao INT DEFAULT 0,
    linhas_codigo INT DEFAULT 0,
    bugs_encontrados INT DEFAULT 0,
    retrabalho TINYINT(1) DEFAULT 0,
    score_validacao INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agente (agente),
    INDEX idx_tarefa (tarefa_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de execuções de agentes
CREATE TABLE IF NOT EXISTS ai_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id VARCHAR(50) NOT NULL,
    context TEXT,
    result TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    success TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agent (agent_id),
    INDEX idx_started (started_at),
    INDEX idx_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de correções/aprendizados
CREATE TABLE IF NOT EXISTS ai_corrections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL,
    solucao TEXT,
    arquivos_afetados JSON,
    prevenido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_prevenido (prevenido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir padrões iniciais
INSERT INTO ai_learning (agente, tipo, contexto, padrao, exemplo, sucesso_rate) VALUES
('IMPLEMENTADOR', 'nomenclatura', 'controllers', 'Sempre usar sufixo Controller', 'class ClienteController extends Controller', 1.00),
('IMPLEMENTADOR', 'nomenclatura', 'repositories', 'Sempre usar sufixo Repository', 'class ClienteRepository extends BaseRepository', 1.00),
('IMPLEMENTADOR', 'nomenclatura', 'services', 'Sempre usar sufixo Service', 'class ClienteService extends BaseService', 1.00),
('COMPONENT', 'estrutura', 'views', 'Sempre usar componentes do design system', 'renderButton([...])', 1.00),
('SECURITY', 'validacao', 'forms', 'Sempre incluir token CSRF', '<?php echo csrf_token(); ?>', 1.00),
('SECURITY', 'validacao', 'output', 'Sempre escapar output HTML', 'htmlspecialchars($var, ENT_QUOTES, "UTF-8")', 1.00),
('SECURITY', 'validacao', 'sql', 'Sempre usar prepared statements', '$this->query("SELECT * FROM t WHERE id = ?", [$id])', 1.00);

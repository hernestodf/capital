-- Modulo de Montagem de Produtos
-- Vincula seriais a salas de eventos para controle de montagem

CREATE TABLE IF NOT EXISTS montagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_serial INT NOT NULL,
    id_evento INT NOT NULL,
    id_sala INT NOT NULL,
    observacao_item TEXT,
    status ENUM('pendente','montado','devolvido') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_serial) REFERENCES seriaisproduto(id) ON DELETE CASCADE,
    FOREIGN KEY (id_evento) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sala) REFERENCES salas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_serial_evento (id_serial, id_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indice para consultas rapidas
CREATE INDEX idx_montagens_evento ON montagens(id_evento);
CREATE INDEX idx_montagens_sala ON montagens(id_sala);
CREATE INDEX idx_montagens_serial ON montagens(id_serial);
CREATE INDEX idx_montagens_status ON montagens(status);

-- Migration 019: Criar tables para modulo Fechamento de Eventos
-- Data: 2026-04-27

-- 1. Adaptar contas_pagar para suportar eventos e multiplos tipos
ALTER TABLE contas_pagar 
  ADD COLUMN evento_id INT NULL AFTER id,
  ADD COLUMN tipo ENUM('colaborador','fornecedor','outro') NOT NULL DEFAULT 'outro' AFTER evento_id,
  ADD COLUMN referencia_id INT NULL AFTER tipo,
  ADD CONSTRAINT fk_contas_pagar_evento 
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL;

-- 2. Criar tabela de fotos do evento
CREATE TABLE evento_fotos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evento_id INT NOT NULL,
  sala_id INT NULL,
  caminho_arquivo VARCHAR(500) NOT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
  FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE SET NULL,
  INDEX idx_evento_sala (evento_id, sala_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

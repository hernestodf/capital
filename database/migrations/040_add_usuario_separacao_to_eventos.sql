-- Migration 040: Campo "Separado por" em eventos
-- Rastreia qual usuário do sistema separou fisicamente o estoque para o evento

ALTER TABLE eventos
    ADD COLUMN id_usuario_separacao INT NULL AFTER id_demandante,
    ADD CONSTRAINT fk_eventos_usuario_separacao
        FOREIGN KEY (id_usuario_separacao) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_eventos_usuario_separacao ON eventos(id_usuario_separacao);

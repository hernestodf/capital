-- Migration: 013_add_categoria_to_produtos_evento.sql
-- Adicionar coluna id_categoria na tabela produtos_evento
-- Data: 2026-04-21

ALTER TABLE produtos_evento
ADD COLUMN id_categoria INT AFTER id_sala,
ADD FOREIGN KEY (id_categoria) REFERENCES categorias_sala(id) ON DELETE SET NULL,
ADD INDEX idx_categoria (id_categoria);

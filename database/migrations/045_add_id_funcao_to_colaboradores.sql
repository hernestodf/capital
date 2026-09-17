-- Migration 045: adicionar id_funcao (FK para funcoes) em colaboradores
-- Mantém atua_como como fallback para dados legados e compatibilidade com API mobile

ALTER TABLE colaboradores ADD COLUMN id_funcao INT NULL AFTER atua_como;

-- Popula id_funcao para registros existentes que tenham nome de função no cadastro
-- Corrige tipo para compatibilidade com funcoes.id (INT UNSIGNED)
ALTER TABLE colaboradores MODIFY COLUMN id_funcao INT UNSIGNED NULL;

-- Popula id_funcao para registros existentes (collate explícito para evitar mismatch)
UPDATE colaboradores c
JOIN funcoes f ON f.nome COLLATE utf8mb4_0900_ai_ci = c.atua_como
SET c.id_funcao = f.id;

ALTER TABLE colaboradores
ADD CONSTRAINT fk_colaboradores_funcao
FOREIGN KEY (id_funcao) REFERENCES funcoes(id) ON DELETE SET NULL;

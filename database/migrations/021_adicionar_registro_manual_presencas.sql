-- Migration 021: Adicionar coluna registro_manual na tabela evento_colaborador_presencas
ALTER TABLE evento_colaborador_presencas
  ADD COLUMN registro_manual ENUM('S','N') DEFAULT 'N' AFTER status,
  ADD COLUMN observacao TEXT AFTER registro_manual;

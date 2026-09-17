-- Migration 042: campos de energia em produtos_evento e planilhas
-- potencia_w: potência do equipamento em Watts
-- horas_uso: horas de uso previstas no evento (default 20h)
-- kWh e kVA são calculados na aplicação (não armazenados)

ALTER TABLE produtos_evento
  ADD COLUMN potencia_w DECIMAL(10,2) DEFAULT NULL AFTER custo_unit,
  ADD COLUMN horas_uso  DECIMAL(10,2) DEFAULT 20.00 AFTER potencia_w;

ALTER TABLE planilhas
  ADD COLUMN potencia_w DECIMAL(10,2) DEFAULT NULL;

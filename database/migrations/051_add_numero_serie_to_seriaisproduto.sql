-- Migration: Adiciona numero_serie (numero de serie do FABRICANTE) a seriaisproduto
--
-- Distinto do campo `serial` (que hoje representa o codigo interno de
-- rastreio / "Codigo de Barras" na interface, e passa a ser tambem o
-- conteudo gerado para o QR Code). numero_serie e um dado opcional,
-- preenchido manualmente, sem relacao com a geracao em lote por faixa.

ALTER TABLE seriaisproduto
    ADD COLUMN numero_serie VARCHAR(255) DEFAULT NULL COMMENT 'Numero de serie do fabricante (opcional, manual)'
    AFTER serial;

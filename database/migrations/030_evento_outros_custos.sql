-- Migration 030: Criar tabela evento_outros_custos
-- Separa o registro de outros custos do módulo Contas a Pagar
-- Segue o padrão de evento_colaboradores e evento_itens (enviado_contas_pagar S/N)
-- Data: 2026-05-24

CREATE TABLE IF NOT EXISTS `evento_outros_custos` (
  `id`                       int NOT NULL AUTO_INCREMENT,
  `evento_id`                int NOT NULL,
  `descricao`                varchar(255) NOT NULL,
  `valor`                    decimal(10,2) NOT NULL,
  `data_vencimento`          date NOT NULL,
  `observacao`               text,
  `enviado_contas_pagar`     enum('S','N') NOT NULL DEFAULT 'N',
  `data_envio_contas_pagar`  datetime DEFAULT NULL,
  `conta_pagar_id`           int DEFAULT NULL,
  `created_at`               datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_evento_id` (`evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar registros existentes de contas_pagar tipo='outro' para a nova tabela
-- Eles já estavam em contas_pagar, então marcamos como enviado='S'
INSERT INTO `evento_outros_custos`
  (evento_id, descricao, valor, data_vencimento, observacao, enviado_contas_pagar, data_envio_contas_pagar, conta_pagar_id, created_at)
SELECT
  evento_id, descricao, valor, data_vencimento, observacao,
  'S', created_at, id, created_at
FROM contas_pagar
WHERE tipo = 'outro'
  AND NOT EXISTS (
    SELECT 1 FROM evento_outros_custos eoc
    WHERE eoc.conta_pagar_id = contas_pagar.id
  );

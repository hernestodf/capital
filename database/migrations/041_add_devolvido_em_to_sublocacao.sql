-- Migration 041: Campo de devolução física em sublocações
-- devolvido_em é independente do status de pagamento:
-- pode estar pago e não devolvido, ou devolvido e pendente de pagamento

ALTER TABLE produto_evento_sublocacao
    ADD COLUMN devolvido_em DATETIME NULL DEFAULT NULL AFTER status;

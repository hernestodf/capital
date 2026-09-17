-- Migration 018: Adicionar Foreign Keys nas tabelas de cotacao
-- Data: 2026-04-26
-- Sistema: SisLoc v2.5.0

-- ============================================
-- Foreign Keys: item_cotacoes (ja existentes)
-- ============================================
-- fk_cotacoes_produto_evento: item_cotacoes.id_produto_evento -> produtos_evento.id (CASCADE)
-- fk_cotacoes_fornecedor: item_cotacoes.id_fornecedor -> fornecedores.id (CASCADE)
-- fk_cotacoes_evento: item_cotacoes.id_evento -> eventos.id (CASCADE)

-- ============================================
-- Foreign Keys: item_cotacoes_mensagens
-- ============================================
ALTER TABLE item_cotacoes_mensagens
    ADD CONSTRAINT fk_msg_cotacao
    FOREIGN KEY (id_cotacao) REFERENCES item_cotacoes(id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_msg_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE CASCADE;

-- ============================================
-- Foreign Keys: item_cotacoes_anexos
-- ============================================
ALTER TABLE item_cotacoes_anexos
    ADD CONSTRAINT fk_anexo_mensagem
    FOREIGN KEY (id_mensagem) REFERENCES item_cotacoes_mensagens(id) ON DELETE CASCADE;

-- ============================================
-- Foreign Keys: item_cotacoes_parcelas
-- ============================================
ALTER TABLE item_cotacoes_parcelas
    ADD CONSTRAINT fk_parcela_cotacao
    FOREIGN KEY (id_cotacao) REFERENCES item_cotacoes(id) ON DELETE CASCADE;

-- Migration 023: Adicionar índices para otimização de queries
-- Data: 2026-04-27
-- Objetivo: Melhorar performance de queries frequentes

-- Índice para busca de contas por tipo e referência (ContasPagarRepository)
ALTER TABLE contas_pagar 
ADD INDEX idx_tipo_referencia (tipo, referencia_id);

-- Índice para busca de contas por evento
ALTER TABLE contas_pagar 
ADD INDEX idx_evento_id (evento_id);

-- Índice para busca de contas por status
ALTER TABLE contas_pagar 
ADD INDEX idx_status (status);

-- Índice para busca de contas por vencimento
ALTER TABLE contas_pagar 
ADD INDEX idx_data_vencimento (data_vencimento);

-- Índice para busca de produto_evento por evento
ALTER TABLE produtos_evento 
ADD INDEX idx_evento_id (id_evento);

-- Índice para busca de produto_evento por sala
ALTER TABLE produtos_evento 
ADD INDEX idx_sala_id (id_sala);

-- Índice para busca de cotacoes por produto_evento
ALTER TABLE cotacoes 
ADD INDEX idx_produto_evento (id_produto_evento);

-- Índice para busca de cotacoes por fornecedor
ALTER TABLE cotacoes 
ADD INDEX idx_fornecedor (id_fornecedor);

-- Índice para busca de cotacoes por estado
ALTER TABLE cotacoes 
ADD INDEX idx_estado (estado);

-- Índice para token de presença (busca única)
ALTER TABLE evento_colaboradores 
ADD INDEX idx_token_presenca (token_presenca);

-- Índice para busca de colaboradores por evento
ALTER TABLE evento_colaboradores 
ADD INDEX idx_evento_id (id_evento);

-- Índice para busca de colaboradores por colaborador
ALTER TABLE evento_colaboradores 
ADD INDEX idx_colaborador_id (id_colaborador);

-- Índice para busca de presenças por alocação
ALTER TABLE evento_colaborador_presencas 
ADD INDEX idx_alocacao_id (id_alocacao);

-- Índice para busca de presenças por data
ALTER TABLE evento_colaborador_presencas 
ADD INDEX idx_data (data);

-- Índice para busca de seriais por produto
ALTER TABLE produtos_seriais 
ADD INDEX idx_produto_id (id_produto);

-- Índice para busca de seriais por status
ALTER TABLE produtos_seriais 
ADD INDEX idx_status (status);

-- Índice para busca de eventos por estado
ALTER TABLE eventos 
ADD INDEX idx_estado (estado);

-- Índice para busca de eventos por data
ALTER TABLE eventos 
ADD INDEX idx_data_evento (data_evento);

-- Índice para busca de eventos por cliente
ALTER TABLE eventos 
ADD INDEX idx_cliente_id (id_cliente);

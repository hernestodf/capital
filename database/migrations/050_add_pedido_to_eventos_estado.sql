-- Migration 050: Adiciona 'P' (Pedido) ao ENUM de eventos.estado
-- A tela Montar OS (views/evento/partials/edit-montar-os.php) oferece o
-- estado "Pedido" desde aa81720 (2026-07-01), mas o ENUM do banco nunca foi
-- atualizado — ficou ENUM('O','L') da migration 011. Selecionar "Pedido"
-- gera Warning 1265 (Data truncated for column 'estado') e o MySQL grava
-- string vazia em vez de 'P', entao o evento nunca sai de "Orcamento".

ALTER TABLE eventos
    MODIFY estado ENUM('O', 'L', 'P') DEFAULT 'O' COMMENT 'O=Orcamento, L=Locacao, P=Pedido';

-- Linhas que ja sofreram a truncagem (warning 1265) antes desta migration
-- ficaram com estado = '' (valor invalido do ENUM antigo). Repoe para 'O'
-- (Orcamento) por ser o fallback seguro -- nao ha como recuperar se a
-- intencao original era 'L' ou 'P'.
UPDATE eventos SET estado = 'O' WHERE estado = '' OR estado NOT IN ('O', 'L', 'P');

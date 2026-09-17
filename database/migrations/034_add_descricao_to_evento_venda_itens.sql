ALTER TABLE evento_venda_itens
  ADD COLUMN descricao VARCHAR(255) NULL DEFAULT NULL
  AFTER id_fornecedor;

ALTER TABLE contas_pagar MODIFY COLUMN tipo ENUM('colaborador','fornecedor','outro','venda_comissao') NOT NULL DEFAULT 'outro';

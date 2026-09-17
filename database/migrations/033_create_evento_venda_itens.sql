CREATE TABLE IF NOT EXISTS evento_venda_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_evento INT NOT NULL,
    tabela TINYINT(1) NOT NULL COMMENT '2 = Tabela 2 (5%), 3 = Tabela 3 (4%)',
    id_fornecedor INT NOT NULL,
    valor_venda DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    comissao_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_evento) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE INDEX idx_evento_tabela ON evento_venda_itens (id_evento, tabela);
CREATE INDEX idx_fornecedor ON evento_venda_itens (id_fornecedor);

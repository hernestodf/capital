-- Migration: 007_create_clientes.sql
-- Criar tabela de clientes

CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cpf_cnpj VARCHAR(20),
  nome_fantasia VARCHAR(255),
  razao_social VARCHAR(255),
  contato VARCHAR(255),
  email VARCHAR(255),
  telefone VARCHAR(20),
  cep VARCHAR(10),
  endereco VARCHAR(255),
  numero VARCHAR(20),
  complemento VARCHAR(255),
  bairro VARCHAR(100),
  cidade VARCHAR(100),
  estado VARCHAR(2),
  observacao TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

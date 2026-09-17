-- Migration: Adiciona campos de contato à tabela users
ALTER TABLE users
ADD COLUMN telefone VARCHAR(20) NULL AFTER name,
ADD COLUMN celular VARCHAR(20) NULL AFTER telefone,
ADD COLUMN cep VARCHAR(10) NULL AFTER celular;

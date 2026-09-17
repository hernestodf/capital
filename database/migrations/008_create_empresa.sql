-- Migration: 008_create_empresa.sql
-- Criar tabela de configurações da empresa

CREATE TABLE IF NOT EXISTS `empresa` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `nome`              VARCHAR(100) DEFAULT 'SISLOC',
    `identificador`     VARCHAR(50)  DEFAULT 'SISLOC',
    `subtitulo`         VARCHAR(100) DEFAULT '',
    `tagline`           VARCHAR(100) DEFAULT 'Locacao',
    `cnpj`              VARCHAR(20)  NULL,
    `inscricao_estadual` VARCHAR(50) NULL,
    `endereco`          VARCHAR(255) NULL,
    `cidade`            VARCHAR(100) NULL,
    `estado`            VARCHAR(2)   NULL,
    `cep`               VARCHAR(10)  NULL,
    `telefone`          VARCHAR(20)  NULL,
    `whatsapp`          VARCHAR(20)  NULL,
    `email`             VARCHAR(100) NULL,
    `site`              VARCHAR(100) NULL,
    `pix_chave`         VARCHAR(100) NULL,
    `pix_tipo`          VARCHAR(20)  NULL,
    `logo_path`         VARCHAR(255) NULL,
    `rodape_pdf`        VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir registro padrão
INSERT INTO `empresa` (`nome`, `identificador`, `tagline`) 
VALUES ('SISLOC', 'SISLOC', 'Locação')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

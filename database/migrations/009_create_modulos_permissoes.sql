-- Migration: 009_create_modulos_permissoes.sql
-- Criar tabelas de módulos e permissões do sistema

-- Tabela de módulos do sistema
CREATE TABLE IF NOT EXISTS `modulos` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `nome`              VARCHAR(50) NOT NULL UNIQUE,
    `titulo`            VARCHAR(100) NOT NULL,
    `descricao`         VARCHAR(255) NULL,
    `icone`             VARCHAR(50) NULL,
    `ordem`             INT DEFAULT 0,
    `ativo`             TINYINT(1) DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de permissões
CREATE TABLE IF NOT EXISTS `permissoes` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `modulo_id`         INT NOT NULL,
    `nome`              VARCHAR(50) NOT NULL,
    `titulo`            VARCHAR(100) NOT NULL,
    `descricao`         VARCHAR(255) NULL,
    `role_required`     VARCHAR(50) DEFAULT 'administrador',
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE,
    INDEX idx_modulo (modulo_id),
    INDEX idx_nome (nome),
    INDEX idx_role (role_required)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de relação role-permissões
CREATE TABLE IF NOT EXISTS `role_permissoes` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `role`              VARCHAR(50) NOT NULL,
    `permissao_id`      INT NOT NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permissao (role, permissao_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir módulo de Configurações
INSERT INTO `modulos` (`nome`, `titulo`, `descricao`, `icone`, `ordem`, `ativo`) VALUES
('configuracoes', 'Configurações', 'Configurações do sistema - Empresa e Permissões', 'settings', 100, 1);

-- Inserir permissões do módulo Configurações
INSERT INTO `permissoes` (`modulo_id`, `nome`, `titulo`, `descricao`, `role_required`) VALUES
(1, 'configuracoes.view', 'Visualizar Configurações', 'Acessar página de configurações', 'administrador'),
(1, 'configuracoes.empresa.edit', 'Editar Dados da Empresa', 'Alterar informações da empresa', 'administrador'),
(1, 'configuracoes.permissoes.view', 'Visualizar Permissões', 'Ver matriz de permissões', 'administrador'),
(1, 'configuracoes.permissoes.edit', 'Editar Permissões', 'Alterar permissões por perfil', 'administrador');

-- Inserir módulo de Clientes
INSERT INTO `modulos` (`nome`, `titulo`, `descricao`, `icone`, `ordem`, `ativo`) VALUES
('clientes', 'Clientes', 'Cadastro e gerenciamento de clientes', 'users', 10, 1);

INSERT INTO `permissoes` (`modulo_id`, `nome`, `titulo`, `descricao`, `role_required`) VALUES
(2, 'clientes.view', 'Visualizar Clientes', 'Listar clientes', 'administrador'),
(2, 'clientes.create', 'Criar Cliente', 'Adicionar novo cliente', 'administrador'),
(2, 'clientes.edit', 'Editar Cliente', 'Editar cliente existente', 'administrador'),
(2, 'clientes.delete', 'Excluir Cliente', 'Remover cliente', 'administrador');

-- Inserir módulo de Usuários
INSERT INTO `modulos` (`nome`, `titulo`, `descricao`, `icone`, `ordem`, `ativo`) VALUES
('usuarios', 'Usuários', 'Cadastro e gerenciamento de usuários do sistema', 'user-circle', 20, 1);

INSERT INTO `permissoes` (`modulo_id`, `nome`, `titulo`, `descricao`, `role_required`) VALUES
(3, 'usuarios.view', 'Visualizar Usuários', 'Listar usuários', 'administrador'),
(3, 'usuarios.create', 'Criar Usuário', 'Adicionar novo usuário', 'administrador'),
(3, 'usuarios.edit', 'Editar Usuário', 'Editar usuário existente', 'administrador'),
(3, 'usuarios.delete', 'Excluir Usuário', 'Remover usuário', 'administrador'),
(3, 'usuarios.toggle', 'Ativar/Desativar Usuário', 'Alterar status do usuário', 'administrador');

-- Atribuir todas as permissões ao role administrador
INSERT INTO `role_permissoes` (`role`, `permissao_id`)
SELECT 'administrador', id FROM `permissoes`;

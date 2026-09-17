-- Migration: Usuário Administrador
-- Email: hernestodf@gmail.com
-- Senha inicial definida via password_hash() — já trocada em produção desde então.

INSERT INTO users (name, email, password, role, status) VALUES
('Hernesto', 'hernestodf@gmail.com', '$2y$12$Jq27K8GOmu1zWZJvhe7xAuzlF0jiyZ8MRci.84mFtlLel32AWUZa', 'administrador', 1);
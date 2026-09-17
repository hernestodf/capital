-- Migration: Atualiza tabela users com novos perfis
-- Perfis: administrador, produtor, estoquista

-- Alterar tabela users para suportar novos perfis
ALTER TABLE users MODIFY COLUMN role ENUM('guest','administrador','produtor','estoquista') DEFAULT 'guest';

-- Atualizar usuários existentes (se houver)
UPDATE users SET role = 'administrador' WHERE role = 'admin';
UPDATE users SET role = 'guest' WHERE role = 'user';
UPDATE users SET role = 'guest' WHERE role = 'manager';

-- Inserir usuários padrão para teste (senha: 123456)
-- Remover ou comentar em produção
INSERT INTO users (name, email, password, role, status) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 1),
('Produtor', 'produtor@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'produtor', 1),
('Estoquista', 'estoquista@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estoquista', 1);

-- Senhas: 123456 (hash gerado com password_hash('123456', PASSWORD_DEFAULT))

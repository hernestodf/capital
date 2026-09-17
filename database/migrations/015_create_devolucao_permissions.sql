-- Modulo de Devolucao (Desmontagem) de Produtos
-- Permissoes para o modulo de devolucao

-- Inserir modulo de devolucao
INSERT INTO modulos (nome, titulo, descricao, icone, ordem, ativo)
VALUES ('devolucao', 'Devolucao', 'Gerenciamento de devolucao/desmontagem de produtos', 'truck-return', 6, 1)
ON DUPLICATE KEY UPDATE nome = nome;

-- Inserir permissoes do modulo devolucao
INSERT INTO permissoes (modulo_id, nome, titulo, descricao, role_required)
SELECT m.id, 'devolucao.listar', 'Listar Devolucoes', 'Visualizar listagem de devolucoes', 'administrador'
FROM modulos m WHERE m.nome = 'devolucao'
ON DUPLICATE KEY UPDATE nome = nome;

INSERT INTO permissoes (modulo_id, nome, titulo, descricao, role_required)
SELECT m.id, 'devolucao.criar', 'Criar Devolucao', 'Processar devolucao de produtos', 'administrador'
FROM modulos m WHERE m.nome = 'devolucao'
ON DUPLICATE KEY UPDATE nome = nome;

INSERT INTO permissoes (modulo_id, nome, titulo, descricao, role_required)
SELECT m.id, 'devolucao.editar', 'Editar Devolucao', 'Editar devolucao existente', 'administrador'
FROM modulos m WHERE m.nome = 'devolucao'
ON DUPLICATE KEY UPDATE nome = nome;

INSERT INTO permissoes (modulo_id, nome, titulo, descricao, role_required)
SELECT m.id, 'devolucao.excluir', 'Excluir Devolucao', 'Excluir devolucao', 'administrador'
FROM modulos m WHERE m.nome = 'devolucao'
ON DUPLICATE KEY UPDATE nome = nome;

-- Vincular permissoes aos roles (administrador, produtor, estoquista)
INSERT INTO role_permissoes (role, permissao_id)
SELECT 'administrador', p.id
FROM permissoes p
INNER JOIN modulos m ON p.modulo_id = m.id
WHERE m.nome = 'devolucao' AND p.nome LIKE 'devolucao.%'
ON DUPLICATE KEY UPDATE role = role;

INSERT INTO role_permissoes (role, permissao_id)
SELECT 'produtor', p.id
FROM permissoes p
INNER JOIN modulos m ON p.modulo_id = m.id
WHERE m.nome = 'devolucao' AND p.nome LIKE 'devolucao.%'
ON DUPLICATE KEY UPDATE role = role;

INSERT INTO role_permissoes (role, permissao_id)
SELECT 'estoquista', p.id
FROM permissoes p
INNER JOIN modulos m ON p.modulo_id = m.id
WHERE m.nome = 'devolucao' AND p.nome LIKE 'devolucao.%'
ON DUPLICATE KEY UPDATE role = role;

-- Migration 028: Adicionar permissoes montagem:write e montagem:read nas API keys
-- Data: 2026-05-19
-- Objetivo: Permitir que apps usem os endpoints /api/v1/montagem/*

-- Atualizar chave de dev
UPDATE api_keys 
SET permissions = JSON_ARRAY_APPEND(permissions, '$', CAST('"montagem:write"' AS JSON)),
    permissions = JSON_ARRAY_APPEND(permissions, '$', CAST('"montagem:read"' AS JSON))
WHERE api_key_public = 'sk_dev_001'
AND NOT JSON_CONTAINS(permissions, '"montagem:write"');

-- Chave android ja tem *:write e *:read (cobre tudo)

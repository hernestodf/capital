-- Migration 025: Criar API Key para App Android
-- Data: 2026-05-19
-- Objetivo: Autenticacao do app mobile via header X-Api-Key

-- IMPORTANTE: Troque a chave abaixo por uma chave forte e unica!
-- Esta chave tem hash de 'sisloc_android_app_prod_2026@#'

INSERT INTO api_keys (
    name,
    api_key,
    api_key_public,
    permissions,
    rate_limit,
    ip_whitelist,
    status,
    created_by,
    created_at
)
SELECT
    'App Android - SisLoc',
    SHA2('sisloc_android_app_prod_2026@#', 256),
    'sk_android_001',
    '[
        "auth:login",
        "auth:read",
        "auth:logout",
        "eventos:read",
        "estoque:write",
        "*:write",
        "*:read"
    ]',
    5000,
    NULL,
    1,
    (SELECT id FROM users WHERE email = 'admin' LIMIT 1),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM api_keys WHERE api_key_public = 'sk_android_001');

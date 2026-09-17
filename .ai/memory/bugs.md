# bugs
(a preencher pelo Memory Engine)

---
id: bug-001
type: bug
status: observed
confidence: 95
evidence: 1
first_seen: 2026-06-08
last_verified: 2026-06-08
superseded_by: null
tags: [php, evento]
source_commit: ""
---
# Undefined array key "enviado_pagamento" em EventoController.php:716

Linha usava acesso direto `$col['enviado_pagamento']` sem verificação. Corrigido para `!empty($col['enviado_pagamento'])` que retorna false quando a chave não existe.

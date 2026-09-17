# Memory Engine — as 4 regras que tornam a memória confiável

## 1. PROMOÇÃO (observação -> aprendizado)
- 1ª detecção: status: observed, evidence: 1 (vai para memory/observations.md)
- reaparece em commit/contexto distinto: evidence += 1
- evidence >= 3: promover para learned_patterns.md com status: confirmed
- evidence < 3: citar como "tendência", nunca como regra

## 2. CONFLITO (qual versão vence)
- PROIBIDO manter duas afirmações contraditórias ativas.
- Conhecimento antigo -> status: deprecated, superseded_by: <id-novo> (move p/ deprecated.md)
- Conhecimento novo entra com evidence reiniciada.
- Justificativa da virada -> decisions.md (com commit).

## 3. RECUPERAÇÃO (o que ler, sem ler tudo)
- QUENTE (sempre): summaries/current_state.md + itens confirmed + rules relevantes.
- FRIA (sob demanda por tag): context/*, reports/*, memory/bugs|mistakes.
- INDEX.md mapeia arquivos -> tags. Casar tags da tarefa em tasks/current.md.

## 4. DECAIMENTO (o que esquecer / reconfirmar)
- Todo item tem last_verified.
- (hoje - last_verified) > 90 dias e sem reaparecer -> status: stale.
- Item stale NÃO é autoridade; fact_verifier deve reconfirmar.

## Formato canônico (frontmatter) de TODO item
---
id: pat-001
type: pattern        # pattern|decision|bug|mistake|dead_code|rule
status: observed     # observed|confirmed|deprecated|stale
confidence: 0-100
evidence: 1
first_seen: AAAA-MM-DD
last_verified: AAAA-MM-DD
superseded_by: null
tags: []
source_commit: ""
---
# Título
Corpo narrativo (a IA lê isto; o script lê o frontmatter acima).

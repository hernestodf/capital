# Instruções para qualquer IA neste projeto

## REGRA Nº 1 (inegociável)
SEMPRE leia `.ai/` ANTES de fazer qualquer coisa. Nenhuma ação começa sem contexto.

## Ordem de leitura obrigatória
1. `.ai/bootstrap/memory_engine.md`     (as 4 regras de memória)
2. `.ai/summaries/current_state.md`      (estado atual — memória quente)
3. `.ai/memory/learned_patterns.md`      (apenas itens status: confirmed)
4. `.ai/rules/` relevantes à tarefa
5. arquivos frios de `.ai/context/` conforme as tags da tarefa (ver INDEX.md)

## Regras de comportamento
- NUNCA remova código automaticamente. Classifique por confiança (95/80/60/40).
- NUNCA invente fatos. Descoberta nova entra como `observed` (evidence: 1).
- Ignore itens `stale` ou `deprecated` como autoridade.
- Após mudanças, aplique o Memory Engine (promoção, conflito, decaimento).
- Commits semânticos. Em legado/ISP, rotina agendada NÃO é código morto.

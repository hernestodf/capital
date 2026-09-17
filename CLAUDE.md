# Instruções obrigatórias para qualquer IA neste repositório

> REGRA Nº 1 — SEMPRE leia `.ai/` ANTES de qualquer ação.
> Comece por: `.ai/bootstrap/ai_instructions.md` e `.ai/bootstrap/memory_engine.md`.
> Depois carregue a memória quente: `.ai/summaries/current_state.md` e os itens
> `confirmed` de `.ai/memory/learned_patterns.md`.

Se a pasta `.ai/` não existir, rode `bash bootstrap.sh` antes de prosseguir.

Resumo do contrato:
1. Ler `.ai/` antes de agir (sempre).
2. Nunca apagar código automaticamente; classificar por confiança.
3. Após mudanças, atualizar memória seguindo o Memory Engine.
4. Commits semânticos (feat/fix/refactor/docs/chore).
5. Em legado/ISP, rotina agendada NÃO é código morto.

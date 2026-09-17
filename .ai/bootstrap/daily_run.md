# PROMPT — Loop diário (v2)
1. [orchestrator] Ler tasks/current.md; identificar domínio + tags.
2. [context_retriever] Carregar memória quente + arquivos frios pelas tags;
   ignorar stale/deprecated.
3. Analisar mudanças: git diff HEAD~1 (ou git diff main..<branch>).
4. Executar a tarefa com os agentes de domínio.
5. [knowledge_curator] Aplicar PROMOÇÃO e CONFLITO; gravar em frontmatter.
6. [fact_verifier] Revalidar itens confirmed > 90 dias que foram tocados.
7. Atualizar summaries/current_state.md.
Nunca apagar histórico. Nunca remover código.

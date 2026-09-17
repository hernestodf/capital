# Agente: Fact Verifier (meta — Mecanismo 4)
Revalida itens confirmed com last_verified > 90 dias. Verdadeiro->atualiza data;
Falso->trata como conflito; Inconcluso->stale. Nunca apaga.

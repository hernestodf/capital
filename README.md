# AI Bootstrap Kit — Sistema de Inteligência Persistente para Projetos

Kit para transformar qualquer projeto (PHP/Tailwind/ISP e outros) em um
"cérebro persistente": memória estruturada + agentes + Git como memória temporal.

## Conteúdo
- `bootstrap.sh`     → instalador idempotente. Cria a estrutura .ai/ se não existir,
                       preserva o que já existe, grava a regra "sempre ler antes de agir"
                       (CLAUDE.md / AGENTS.md) e ativa os git hooks.
- `BOOTSTRAP_v2.md`  → documento mestre v2: Memory Engine (promoção, conflito,
                       recuperação, decaimento) + 4 agentes meta + formato canônico.
- `BOOTSTRAP_v1.md`  → versão original (estrutura base + todos os agentes worker).

## Como usar
1. Copie `bootstrap.sh` para a RAIZ do seu projeto.
2. Rode:
       bash bootstrap.sh
   (cria o que faltar; rode quantas vezes quiser — nunca sobrescreve sua memória)
   Para só verificar sem criar:
       bash bootstrap.sh --check
3. Cole o prompt de `.ai/bootstrap/first_run.md` na sua IA (1ª vez).
4. No dia a dia, use `.ai/bootstrap/daily_run.md`.

## Regra nº 1
Toda IA DEVE ler `.ai/` antes de agir. Isso fica gravado em CLAUDE.md (raiz),
AGENTS.md (raiz) e .ai/bootstrap/ai_instructions.md.

## Memory Engine (as 4 regras)
1. PROMOÇÃO   — observação vira regra só com evidence >= 3.
2. CONFLITO   — nunca duplica; rebaixa o antigo (deprecated + superseded_by).
3. RECUPERAÇÃO — memória quente (sempre) vs fria (por tags no INDEX.md).
4. DECAIMENTO — item > 90 dias sem reconfirmar vira 'stale' e perde autoridade.

Detalhes completos em BOOTSTRAP_v2.md e em .ai/bootstrap/memory_engine.md (gerado).

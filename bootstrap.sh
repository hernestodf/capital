#!/usr/bin/env bash
#
# bootstrap.sh — Auto-instalador do Sistema de Inteligência Persistente (v2)
#
# O QUE FAZ:
#   - Verifica se a estrutura .ai/ existe; cria SOMENTE o que falta (idempotente).
#   - Nunca sobrescreve arquivo já existente (preserva sua memória acumulada).
#   - Instala os git hooks e a regra "SEMPRE LEIA .ai/ ANTES DE AGIR".
#
# COMO USAR:
#   bash bootstrap.sh            # cria o que faltar
#   bash bootstrap.sh --check    # só relata o que falta, não cria nada
#
set -euo pipefail

ROOT="$(pwd)"
AI="$ROOT/.ai"
MODE="${1:-create}"
CREATED=0; SKIPPED=0; MISSING=0

# ---- helpers ----------------------------------------------------------------
seed() { # seed <caminho> <<'EOF' ... EOF  (cria só se não existir)
  local path="$1"
  if [ -e "$path" ]; then
    if [ "$MODE" = "--check" ]; then echo "OK    $path"; else SKIPPED=$((SKIPPED+1)); fi
    cat >/dev/null   # descarta o heredoc
    return
  fi
  if [ "$MODE" = "--check" ]; then
    echo "FALTA $path"; MISSING=$((MISSING+1)); cat >/dev/null; return
  fi
  mkdir -p "$(dirname "$path")"
  cat > "$path"
  echo "criado  $path"; CREATED=$((CREATED+1))
}

dir() { # garante diretório
  [ "$MODE" = "--check" ] && return
  mkdir -p "$1"
}

# ---- diretórios -------------------------------------------------------------
for d in bootstrap context memory monitoring reports agents rules tasks summaries scripts; do
  dir "$AI/$d"
done
dir "$ROOT/.githooks"

# =============================================================================
# 1. REGRA DE LEITURA OBRIGATÓRIA (raiz + bootstrap)
# =============================================================================
seed "$ROOT/CLAUDE.md" <<'EOF'
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
EOF

# também para ferramentas que leem AGENTS.md
seed "$ROOT/AGENTS.md" <<'EOF'
# Ponto de entrada para agentes de IA
SEMPRE leia `.ai/bootstrap/ai_instructions.md` antes de qualquer ação.
Se `.ai/` não existir, execute `bash bootstrap.sh`.
EOF

seed "$AI/bootstrap/ai_instructions.md" <<'EOF'
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
EOF

# =============================================================================
# 2. MEMORY ENGINE (as 4 regras)
# =============================================================================
seed "$AI/bootstrap/memory_engine.md" <<'EOF'
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
EOF

seed "$AI/bootstrap/first_run.md" <<'EOF'
# PROMPT — Primeira execução
Leia .ai/bootstrap/memory_engine.md e siga as 4 regras.
Mapeie estrutura, stack, arquitetura, APIs, banco, integrações e regras de negócio.
Leia composer.json/package.json/Dockerfile/configs. Analise git log -50 e branches.
Preencha context/*, summaries/*, rules/* (deduzidos do código).
Gere reports/* baseline (frontmatter + confiança + last_verified).
Toda descoberta de padrão entra em memory/observations.md como 'observed'.
Preencha INDEX.md com TAGS por arquivo.
NÃO altere código nesta execução. Apenas analise e documente.
EOF

seed "$AI/bootstrap/daily_run.md" <<'EOF'
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
EOF

seed "$AI/bootstrap/onboarding.md" <<'EOF'
# Onboarding (IA ou dev)
1. Leia CLAUDE.md na raiz.
2. Leia .ai/bootstrap/ai_instructions.md e memory_engine.md.
3. Leia summaries/current_state.md para o estado atual.
4. Só então comece a trabalhar.
EOF

seed "$AI/bootstrap/project_discovery.md" <<'EOF'
# Checklist de descoberta do projeto
- [ ] Stack e versões
- [ ] Arquitetura e camadas
- [ ] APIs / rotas / endpoints
- [ ] Banco (tabelas, views, procedures)
- [ ] Integrações externas (MikroTik, pagamento, e-mail)
- [ ] Regras de negócio e rotinas agendadas
- [ ] Dependências (composer.json / package.json)
- [ ] Git: histórico, branches, datas dos arquivos
EOF

# =============================================================================
# 3. CONTEXT
# =============================================================================
seed "$AI/context/stack.md" <<'EOF'
# Stack
- Linguagens / frameworks / versões: (a preencher)
- Frontend: Tailwind CSS
- Banco: MySQL
- Servidor: Apache/Nginx
EOF
seed "$AI/context/architecture.md"   <<'EOF'
# Arquitetura
Camadas, fluxo de dados e decisões estruturais. (a preencher)
EOF
seed "$AI/context/business_rules.md" <<'EOF'
# Regras de Negócio
## Clientes / Planos / Boletos / Vencimentos
## Bloqueio / Desbloqueio
## Rotinas agendadas (cron/mensal)  <- NÃO marcar como código morto
EOF
seed "$AI/context/database.md"     <<'EOF'
# Banco de Dados
Tabelas, views, procedures e relações. (a preencher)
EOF
seed "$AI/context/api_patterns.md" <<'EOF'
# Padrões de API
Convenções de endpoints, formatos de resposta, auth. (a preencher)
EOF
seed "$AI/context/integrations.md" <<'EOF'
# Integrações
MikroTik/RouterOS, pagamento, e-mail. Credenciais referenciadas (nunca expostas).
EOF
seed "$AI/context/deployment.md"   <<'EOF'
# Deployment
Como o deploy e o rollback funcionam. Ver scripts/.
EOF
seed "$AI/context/terminology.md"  <<'EOF'
# Glossário do domínio
PPPoE, bloqueio, plano, vencimento, secret... (a preencher)
EOF

# =============================================================================
# 4. MEMORY
# =============================================================================
seed "$AI/memory/observations.md"     <<'EOF'
# Observações (staging — status: observed)
Itens aqui ainda NÃO são regra. Promovem para learned_patterns.md ao atingir evidence >= 3.
EOF
seed "$AI/memory/learned_patterns.md" <<'EOF'
# Padrões Aprendidos (status: confirmed)
Apenas itens com evidence >= 3. Formato frontmatter (ver memory_engine.md).
EOF
seed "$AI/memory/decisions.md"   <<'EOF'
# Decisões Arquiteturais (ADR)
## [AAAA-MM-DD] Título
- Contexto / Decisão / Consequências / Commit
EOF
seed "$AI/memory/deprecated.md"  <<'EOF'
# Conhecimento Descontinuado
Destino de itens 'deprecated' (substituídos). Preserva histórico. Nunca apagar.
EOF
for f in mistakes bugs improvements refactors; do
  seed "$AI/memory/$f.md" <<EOF
# ${f}
(a preencher pelo Memory Engine)
EOF
done

# =============================================================================
# 5. MONITORING (JSONL append-only)
# =============================================================================
for f in production_errors frontend_errors php_errors api_health performance deployments incidents; do
  seed "$AI/monitoring/$f.jsonl" <<EOF
{"_note":"${f} — append-only JSONL: uma linha JSON por evento"}
EOF
done

# =============================================================================
# 6. REPORTS (frontmatter)
# =============================================================================
for f in dead_php dead_js duplicated_code unused_routes unused_dependencies database_cleanup architecture_review security_review; do
  seed "$AI/reports/$f.md" <<EOF
# Relatório: ${f}
Itens em formato frontmatter (id, status, confidence, evidence, last_verified, tags).
EOF
done

# =============================================================================
# 7. AGENTS (worker + meta)
# =============================================================================
# meta
seed "$AI/agents/orchestrator.md" <<'EOF'
# Agente: Orchestrator (meta)
Lê tasks/current.md, identifica domínio+tags, chama context_retriever,
seleciona agentes de execução e, ao final, aciona knowledge_curator.
Não escreve código nem memória; coordena.
EOF
seed "$AI/agents/context_retriever.md" <<'EOF'
# Agente: Context Retriever (meta — Mecanismo 3)
Carrega memória quente sempre; lê INDEX.md e carrega só os frios pelas tags.
Ignora stale/deprecated. Devolve resumo do contexto. Minimiza tokens.
EOF
seed "$AI/agents/knowledge_curator.md" <<'EOF'
# Agente: Knowledge Curator (meta — Mecanismos 1 e 2)
PROMOÇÃO: procura item existente; evidence++; promove em evidence>=3.
CONFLITO: rebaixa o antigo (deprecated + superseded_by); nunca duplica.
Usa frontmatter; atualiza last_verified e source_commit. Nunca apaga histórico.
EOF
seed "$AI/agents/fact_verifier.md" <<'EOF'
# Agente: Fact Verifier (meta — Mecanismo 4)
Revalida itens confirmed com last_verified > 90 dias. Verdadeiro->atualiza data;
Falso->trata como conflito; Inconcluso->stale. Nunca apaga.
EOF
# workers + especializados (stubs com objetivo)
declare -A AGENTS=(
  [backend]="Implementa lógica de servidor seguindo rules/ e api_patterns.md."
  [frontend]="UI consistente com ui_rules.md e Tailwind; reaproveita componentes."
  [database]="Migrations reversíveis; lê database_rules.md antes de qualquer ALTER."
  [reviewer]="Revisa diff: segurança, padrões, performance, regressões. Só comenta."
  [security]="Procura SQLi, XSS, secrets, validação ausente; gera security_review.md."
  [performance]="Detecta N+1, falta de índice, payloads grandes; atualiza performance."
  [memory_manager]="Limpeza/dedup geral (a inteligência de memória está nos meta-agentes)."
  [dead_code_php]="Funções/arquivos/classes/endpoints PHP sem referência; cruza com git."
  [dead_code_js]="JS sem import, funções/handlers órfãos; cruza com git."
  [duplicate_code]="Lógica duplicada com % de similaridade e arquivo:linha."
  [route_auditor]="Endpoints sem chamadas (PHP/Apache/Nginx/API)."
  [dependency_auditor]="Deps em composer.json/package.json sem uso real."
  [database_auditor]="Tabelas/views/procedures sem acesso; candidata a arquivamento."
  [deployment_manager]="Orquestra deploy.sh/rollback.sh; healthcheck antes; loga deploy."
  [monitoring_agent]="Roda log_scan.sh; classifica erros; atualiza monitoring/."
  [documentation_agent]="Mantém context/ e summaries/ sincronizados com o código."
  [php_legacy_specialist]="Trata PHP procedural/legado; cuidado com chamadas dinâmicas/cron."
  [isp_business_rules]="Domínio ISP: PPPoE, boletos, bloqueios, rotinas mensais."
  [mikrotik_integrations]="Integrações RouterOS: login PPPoE, bloqueio/liberação, filas."
  [mysql_optimizer]="Queries lentas, índices, EXPLAIN, full scans."
  [ui_tailwind_specialist]="Padroniza Tailwind, remove CSS legado, responsividade."
  [production_incident_agent]="Incidentes: coleta logs, causa provável, hotfix, pós-morte."
)
for name in "${!AGENTS[@]}"; do
  seed "$AI/agents/$name.md" <<EOF
# Agente: ${name}
Objetivo: ${AGENTS[$name]}
Regras: lê .ai/ antes de agir; nunca apaga código; classifica por confiança;
atualiza memória via Memory Engine.
EOF
done

# =============================================================================
# 8. RULES / TASKS / SUMMARIES
# =============================================================================
seed "$AI/rules/security.md" <<'EOF'
# Segurança
- Prepared statements sempre (nunca concatenar SQL).
- Secrets só em .env. Validar/sanitizar toda entrada. Auth em endpoints sensíveis.
EOF
for f in coding_style performance api_rules database_rules ui_rules naming; do
  seed "$AI/rules/$f.md" <<EOF
# Regras: ${f}
(a preencher; deduzir do código existente)
EOF
done
for f in roadmap backlog bugs_to_fix future_ideas; do
  seed "$AI/tasks/$f.md" <<EOF
# ${f}
EOF
done
seed "$AI/tasks/current.md" <<'EOF'
# Tarefa Atual
- Objetivo:
- Branch: feature/...
- Tags: []        # usadas pela recuperação seletiva
- Critérios de aceite:
EOF
seed "$AI/summaries/current_state.md" <<'EOF'
# Estado Atual do Projeto — [AAAA-MM-DD]
- Última mudança relevante:
- Branches ativas:
- Pendências críticas:
- Riscos conhecidos:
EOF
for f in project_summary architecture_summary business_summary; do
  seed "$AI/summaries/$f.md" <<EOF
# ${f}
(a preencher na primeira execução)
EOF
done

# =============================================================================
# 9. SCRIPTS .ai/scripts
# =============================================================================
seed "$AI/scripts/healthcheck.sh" <<'EOF'
#!/usr/bin/env bash
echo "=== HEALTHCHECK $(date) ==="
command -v php >/dev/null && echo "PHP ok" || echo "PHP ausente"
mysqladmin ping 2>/dev/null | grep -q alive && echo "MySQL ok" || echo "MySQL ?"
curl -sf http://localhost/api/health >/dev/null && echo "API ok" || echo "API ?"
EOF
seed "$AI/scripts/diagnostics.sh" <<'EOF'
#!/usr/bin/env bash
echo "=== GIT ==="; git log --oneline -10
echo "=== DIRS ==="; find . -maxdepth 2 -type d -not -path '*/.git*' | sort
echo "PHP:"; find . -name '*.php' | wc -l
echo "JS:";  find . -name '*.js'  | wc -l
EOF
seed "$AI/scripts/log_scan.sh" <<'EOF'
#!/usr/bin/env bash
for f in /var/log/apache2/error.log /var/log/nginx/error.log ./storage/logs/*.log; do
  [ -f "$f" ] || continue
  echo "=== $f ==="; grep -iE 'error|fatal|exception|warning' "$f" | tail -n 30
done
EOF
seed "$AI/scripts/deploy.sh" <<'EOF'
#!/usr/bin/env bash
set -e
bash .ai/scripts/healthcheck.sh
git pull origin main
echo "- $(date): deploy $(git rev-parse --short HEAD)" >> .ai/monitoring/deployments.jsonl
EOF
seed "$AI/scripts/rollback.sh" <<'EOF'
#!/usr/bin/env bash
set -e
PREV=${1:-HEAD~1}
git checkout "$PREV" -- .
echo "{\"event\":\"rollback\",\"to\":\"$PREV\",\"at\":\"$(date -Iseconds)\"}" >> .ai/monitoring/incidents.jsonl
EOF

# =============================================================================
# 10. GIT HOOKS
# =============================================================================
seed "$ROOT/.githooks/pre-commit" <<'EOF'
#!/usr/bin/env bash
echo "pre-commit: checando secrets..."
if git diff --cached | grep -qE "(password|secret|api_key)[[:space:]]*=[[:space:]]*['\"][^'\"]+"; then
  echo "Possível secret hardcoded. Revise antes de commitar."; exit 1
fi
exit 0
EOF
seed "$ROOT/.githooks/post-commit" <<'EOF'
#!/usr/bin/env bash
echo "post-commit: rode o loop diário do .ai/bootstrap/daily_run.md sobre git diff HEAD~1"
# claude "Execute .ai/bootstrap/daily_run.md sobre: $(git diff HEAD~1 --stat)"
exit 0
EOF

# =============================================================================
# 11. INDEX + README
# =============================================================================
seed "$AI/INDEX.md" <<'EOF'
# INDEX — mapa com TAGS (para recuperação seletiva)
| Arquivo | Tags |
|---------|------|
| context/database.md | banco, sql, mysql |
| context/integrations.md | mikrotik, pagamento, integracao |
| context/api_patterns.md | api, http, rotas |
| reports/* | auditoria, limpeza |
| memory/bugs.md | bug, correcao |
| rules/security.md | seguranca |
EOF
seed "$AI/README.md" <<'EOF'
# .ai/ — Cérebro persistente do projeto
Sistema de memória + agentes. Toda IA DEVE ler CLAUDE.md e ai_instructions.md
antes de agir. As 4 regras de memória estão em bootstrap/memory_engine.md.
EOF

# ---- finalização ------------------------------------------------------------
if [ "$MODE" != "--check" ]; then
  chmod +x "$AI"/scripts/*.sh "$ROOT"/.githooks/* 2>/dev/null || true
  git config core.hooksPath .githooks 2>/dev/null || true
  echo ""
  echo "=== bootstrap concluído: $CREATED criados, $SKIPPED preservados ==="
  echo "Hooks ativados em .githooks/ . Regra de leitura gravada em CLAUDE.md."
else
  echo ""
  echo "=== check: $MISSING faltando ==="
fi

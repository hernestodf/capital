# Observações (staging — status: observed)
Itens aqui ainda NÃO são regra. Promovem para learned_patterns.md ao atingir evidence >= 3.

## obs-001: Stack do projeto (atualizado 2026-06-27)
- PHP >= 8.0, MySQL, Apache, Tailwind CSS
- Framework: ConectaFramework (custom MVC)
- Integrações ativas: SMTP (EmailService), WhatsApp (WhatsAppService), PDF (mPDF), Excel (PhpSpreadsheet)
- Integrações removidas: MailJet, IMAP/webklex, Sistema de Cotação (todos removidos junho 2026)

## obs-002: Estrutura de diretórios
- src/Core: Router, Application, Request/Response
- src/Controllers: Auth, Cliente, Dashboard, Evento, etc.
- src/Repository: padrão repository (BaseRepository)
- views/: templates PHP
- storage/: uploads, logs

## obs-003: Roteamento
- Router custom com suporte a grupos e middleware
- Rotas no formato Controller@action (array syntax)
- Prefixo /api/ para endpoints API
- Prefixo /cron/ para tarefas agendadas (protegido por CronAuthMiddleware)

## obs-004: Credenciais de integração
⚠️ Credenciais removidas deste arquivo (estava acessível via HTTP — .ai/ não é bloqueado pelo LiteSpeed para arquivos estáticos).
Consultar: arquivo `.env` na raiz do projeto (nunca commitar).
- DB host/banco: sisloc.online / sisloc_newsisloc
- BASE_URL: https://capital.sisloc.online/public

## obs-005: Migrações (atualizado 2026-06-29)
- Para executar: usar host sisloc.online (produção)
- Banco: sisloc_newsisloc, Usuário: capital
- Total: 43 arquivos SQL (001–043, com gaps em 029 e duplicatas em 009, 025, 032)
- Última gerada localmente: `043_create_funcoes.sql` (untracked, aguarda commit + deploy)
- **Execução em produção da 043**: ⚠️ NÃO CONFIRMADA — existe localmente, não se sabe se rodou em prod

## obs-007: Deploy Process — Ambiente Local vs Produção (2026-06-28)
- Esta máquina (`192.168.1.106`) é **desenvolvimento local**
- Produção está em `177.11.54.229` → `capital.sisloc.online`
- Path de produção via FTP: `/public_html/subdomains/capital/`
- Credenciais FTP em `.env` → `FTP_USER`, `FTP_PASS` (host: `sisloc.online`)
- `ftp -n sisloc.online` → `user sisloc <senha>` → `put <local> <remoto>`
- Banco é sempre remoto (`sisloc.online`); acesso via MySQL CLI com `--skip-ssl`
- CLAUDE.md: "alterações são imediatas" = válido quando rodando **via cPanel Terminal no servidor**

## obs-006: ID removido de todas as listagens
- Todas as listagens tiveram a coluna ID removida (cliente, fornecedor, planilha, evento, estoque, demandante, produtor, usuario, colaborador)
- ID continua funcional via `data-id` nas `<tr>` para ações JS
- Justificativa: clientes não entendem o que é ID

## obs-007: Prefixo de caminhos de assets e BASE_URL
- `BASE_URL` = `https://capital.sisloc.online/public`
- Em templates: `<?= $baseUrl ?>/js/eventos/...` → URL final correta (NÃO usar `/public/` explícito)
- Funções PHP server-side: usar path físico `dirname(__DIR__, 2) . '/public/js/eventos/...'`

## obs-008: Capital é fork independente — NUNCA deployar para outras instâncias
- **status: confirmed**, evidence: 3 (planejamento + execução + confirmação)
- Capital (`capital.sisloc.online`) é fork INDEPENDENTE das instâncias MT/DF/BA/MG.
- Script `deploy/full-deploy.py` — NÃO EXECUTAR (envia para fork original).
- Deploy = alterar código em `/var/www/html/capital/` (já está no ar).
- Migrations: executar no banco `sisloc_newsisloc` via host `sisloc.online`.

## obs-009: Git Recriado após Corrupção (2026-06-27) ✅ RESOLVIDO
---
id: obs-009
type: observation
status: resolved
confidence: 100
evidence: 3
first_seen: 2026-06-18
last_verified: 2026-06-27
tags: [git, infrastructure]
---
Git foi corrompido em 2026-06-18 (`bad object HEAD`). Solução: `git init` com backup em `.git.corrupted/`.
Commits atuais: `044e504` (inicial, 728 arquivos), `896f92c` (CLAUDE.md), e commits subsequentes da sessão de 2026-06-27.
Remote ainda não configurado — TD-003 em backlog.

## obs-010: Arquitetura Confirmada (atualizado 2026-06-29)
---
id: obs-010
type: observation
status: confirmed
confidence: 95
evidence: 3
first_seen: 2026-06-18
last_verified: 2026-06-29
tags: [architecture, codebase, php]
---
Controllers: 37 web + 12 API = 49 total (incluindo FuncaoController, PresencaAppApiController)
Services: 42 (+FuncaoService; -CentralSyncService, -CategoriaSalaCentralService)
Repositories: 33 (+FuncaoRepository)
Views (arquivos PHP): 73
Migrations SQL: 45 (001–044, com gaps e duplicatas; próxima: 045)
Rotas registradas: 287
Commits: 55
Maior service: EmailService (814 LOC)
Maior controller: EventoController (~1473 LOC e crescendo)

## obs-011: API Mobile Apps
- 11 Controllers API, todos com ApiKeyMiddleware
- Mobile apps: Montagem, Devolução, Presença, Estoque (Alocação)
- Response format: JSON `{success: bool, data: {...}}`
- SessaoApiController substituiu AuthApiController (novo nome)

## obs-012: Status de Integrações (2026-06-27 — ATUALIZADO)
| Serviço | Status | Arquivo |
|---------|--------|---------|
| SMTP | ✅ Ativo | EmailService.php (814 LOC) |
| WhatsApp | ✅ Ativo | WhatsAppService.php (385 LOC) |
| PDF (mPDF) | ✅ Ativo | PdfGeneratorService.php |
| Excel | ✅ Ativo | PlanilhaService.php |
| Webhook Sync | ✅ Ativo | WebhookSyncController → Connection + CategoriaSincronizadoService (cache) |
| Mailjet | ❌ Removido | MailjetService não existe mais |
| IMAP Cron | ❌ Removido | CronImapController não existe; ImapService.php é código morto |
| Cotação | ❌ Removido | CotacaoController/Service removidos |

## obs-013: Sistema de Temas CSS
- 12 temas (CSS variables)
- Persistência: storage/theme.json
- Switching via /configuracoes/update-theme (admin)
- CSS injetado em views/layout/header.php

## obs-014: RBAC via Banco (atualizado 2026-06-29 s13 — RESOLVIDO TD-004)
- `src/Auth/Rbac.php` — `check()` agora lê de `role_permissoes JOIN permissoes` via DB; cache estático por request
- Migration `046_seed_rbac.sql` — seed de 17 módulos, 75 permissões, 141 role_permissoes
- UI de gestão em `/configuracoes` aba Permissões (checkboxes por role, AJAX)
- Roles atuais: `administrativo`, `comercial`, `estoquista`, `guest`
- Fallback: array vazio se DB falhar (sem crash)

## obs-015: Serviços de Sync ProFox → SisLoc (SUPERADO EM 2026-06-29)
---
id: obs-015
type: observation
status: deprecated
confidence: 100
evidence: 3
first_seen: 2026-06-27
last_verified: 2026-06-29
superseded_by: obs-021
tags: [service, sync, webhook, active]
---
Conhecimento antigo: em 2026-06-27 os três serviços (`CentralSyncService`, `CategoriaSincronizadoService`, `CategoriaSalaCentralService`) foram tratados como ativos. Em 2026-06-29, após refatoração e nova busca em todo o projeto, `CentralSyncService` foi confirmado sem chamadores e removido (ver `obs-021`). No mesmo dia, `CategoriaSalaCentralService` também foi removido; categorias de Salas e Produtos passaram a usar somente banco local (`categorias_sala`) via `CategoriaSalaService`.

## obs-016: Novos Serviços (desde baseline 2026-06-18)
---
id: obs-016
type: observation
status: observed
confidence: 90
evidence: 1
first_seen: 2026-06-27
last_verified: 2026-06-27
tags: [service, alocacao, new]
---
Dois novos services criados após o baseline:
1. `ConsultaAlocacaoService.php` — consulta de alocações de estoque
2. `ProdutoEventoAlocacaoService.php` — alocação produto/evento (mobile)
Usados por: AlocacaoEstoqueApiController, ProdutoController

## obs-017: Migration 038 (não documentada anteriormente)
---
id: obs-017
type: observation
status: confirmed
confidence: 100
evidence: 3
first_seen: 2026-06-27
last_verified: 2026-06-27
tags: [database, migration, montagem]
---
`038_add_produto_evento_to_montagens.sql`:
Adiciona `id_produto_evento INT NULL` à tabela `montagens`
com FK para `produtos_evento(id)` ON DELETE SET NULL
e índice `idx_montagens_produto_evento`.
Propósito: rastrear qual item específico de uma sala foi alocado na montagem.
Execução em produção: ✅ CONFIRMADA — a coluna existe e está em uso.
## obs-019: Rotas Mortas em public/index.php — Detectadas 2026-06-28
---
id: obs-019
type: observation
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-28
last_verified: 2026-06-28
tags: [routing, dead-code, index.php]
---
Dois endpoints no router de `public/index.php` apontam para controllers inexistentes:
- Linha 367: `/test/diag` → `App\Controllers\TestDiagController` (não existe em `src/Controllers/`)
- Linha 371: `/cron/imap-cotacao` → `App\Controllers\CronImapController` (removido com o módulo de cotação)

Ação pendente: remover ambas as linhas de `public/index.php` após confirmação do usuário (TD-022, TD-023).

## obs-018: Inconsistência de Rotas / Métodos Antigos em public/index.php ✅ RESOLVIDO
---
id: obs-018
type: observation
status: resolved
confidence: 100
evidence: 2
first_seen: 2026-06-27
last_verified: 2026-06-27
tags: [routing, regression, index.php]
---
Após a renomeação dos papéis de usuário no commit `01102b9`, o arquivo `public/index.php` (linhas 110-111) não tinha sido atualizado e ainda continha referências antigas a `/administrador` e `/produtor`. A inconsistência foi corrigida atualizando as rotas para `/administrativo` e `/comercial` chamando os novos métodos do DashboardController, alinhando com a raiz e views/public.

## obs-020: Módulo `funcoes` Implementado Localmente — 2026-06-29
---
id: obs-020
type: observation
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
tags: [funcoes, colaboradores, migration, local-db, refactoring]
---
A feature de "Atua Como" de colaboradores foi migrada da API ProFox para banco local:
- Tabela `funcoes` criada via migration `043_create_funcoes.sql` com seed de **12 funções padrão** (não 13 — contagem anterior errada).
- `FuncaoRepository`, `FuncaoService`, `FuncaoController` criados.
- Rotas `/funcoes-colaborador/{list,store,update/{id},delete/{id}}` em `index.php`.
- `views/colaborador/_form.php` atualizado: select `atuaComo` agora carrega de `/funcoes` (público, sem auth).
- Modal "Gerenciar Funções" inline na view com CRUD via `/funcoes-colaborador/*` (autenticado).
- Scripts inline de create.php e edit.php foram consolidados em `_form.php`.
- ⚠️ `colaboradores.atua_como` continua VARCHAR — NÃO é FK para `funcoes.id` — renomear função não atualiza colaboradores existentes (ver TD-035).
- ⚠️ Rota `/funcoes-colaborador/list` existe no router mas NÃO é usada no JS — o modal usa `GET /funcoes` público (ver TD-036).

## obs-021: CentralSyncService Removido — TD-033 Resolvido
---
id: obs-021
type: dead_code
status: resolved
confidence: 100
evidence: 3
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [dead-code, ProFox, sync, CentralSyncService, TD-033]
---
`src/Service/CentralSyncService.php` (182 LOC) propagava CRUDs locais de categoria/subcategoria para a API central ProFox. Após a refatoração de 2026-06-29, `CategoriaController`, `SubcategoriaController` e `FornecedorController` deixaram de instanciá-lo e passaram a usar serviços locais (`CategoriaService`, `SubcategoriaService`). Grep em todo o projeto confirmou zero chamadores. Arquivo removido em 2026-06-29, resolvendo TD-033.

## obs-022: `opcache_reset.php` na Pasta `public/` — Removido
---
id: obs-022
type: observation
status: resolved
confidence: 100
evidence: 2
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [security, public, debug, opcache]
---
Arquivo `public/opcache_reset.php` (artifact de desenvolvimento para invalidar OPCache) foi removido da pasta `public/` em 2026-06-29, eliminando o risco SEC-006.

## obs-023: Backups SQL na pasta `backups/` Não Versionados
---
id: obs-023
type: observation
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
tags: [backup, security, gitignore]
---
Três arquivos de backup gerados em 2026-06-29 (`backup_pre_truncate_*.sql`) estão na pasta `backups/` local. São untracked pelo git. Verificar se `backups/` está no `.gitignore` — dados de produção não devem ser versionados.

## obs-024: Relatório `refactor_profox_api.md` Contém Informações Falsas
---
id: obs-024
type: observation
status: observed
confidence: 95
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
tags: [documentation, inconsistency, ProFox, dead-doc]
---
O arquivo `.ai/reports/refactor_profox_api.md` data de 2026-06-05 e mistura estado planejado com estado real. Em 2026-06-29, `CentralSyncService.php` e `CategoriaSalaCentralService.php` foram de fato removidos; `CategoriaSincronizadoService.php` continua ativo, e as tabelas AI ainda aparecem como pendência de limpeza em outros relatórios. **Documento obsoleto — não usar como fonte do estado atual.**

## obs-025: Categorias de Salas e Produtos São Locais — TD-034 Resolvido
---
id: obs-025
type: decision
status: resolved
confidence: 100
evidence: 2
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [salas-produtos, categorias-sala, local-db, ProFox, TD-034]
---
O fluxo de categorias da aba **Salas e Produtos** foi desacoplado da API central ProFox. `CategoriaSalaController::listAll()` agora usa somente `CategoriaSalaService::getAtivas()` e a tabela local `categorias_sala`. `CategoriaSalaCentralService.php` foi removido após confirmação de zero chamadores. A UI já possuía modal `modalCategoria`; o botão `+ Categorias` foi reposicionado ao lado do dropdown `#id_categoria` para cadastro local rápido.

## obs-026: `colaboradores.atua_como` é VARCHAR — sem FK para `funcoes`
---
id: obs-026
type: observation
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
tags: [funcoes, colaboradores, data-integrity, TD-035]
---
A migration 043 criou a tabela `funcoes` mas **não alterou** a coluna `colaboradores.atua_como`. Essa coluna continua sendo `VARCHAR`, armazenando o *nome* da função como texto livre — não é FK para `funcoes.id`. Consequência: renomear uma função no modal "Gerenciar Funções" não atualiza os colaboradores que já têm aquele valor salvo. O vínculo é puramente semântico (nome igual), sem integridade referencial no banco. Ver TD-035.

## obs-027: `/funcoes-colaborador/list` — Rota Morta ✅ RESOLVIDO
---
id: obs-027
type: dead_code
status: resolved
confidence: 100
evidence: 2
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [dead-code, routing, funcoes, TD-036]
---
A rota `GET /funcoes-colaborador/list` foi removida de `public/index.php` no commit `162f717` (sessão 11). O grupo `/funcoes-colaborador` permanece com 3 rotas ativas (store/update/delete). Ver obs-029-update para detalhes.

## obs-029: Edge case — comercial sem produtor vinculado (sessão 11)
---
id: obs-029
type: observation
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
tags: [rbac, comercial, produtor, edge-case, TD-038]
---
`EventoController::index()` filtra `$eventos` por `id_produtor` quando `Rbac::isComercial()`. Mas `$stats` é calculado com `countByFilters(array_merge($filters, $idProdutor ? ['id_produtor' => $idProdutor] : []))`. Se `$idProdutor` for null (comercial sem produtor vinculado), a condição ternária retorna `[]` e o merge resulta em `$filters` sem filtro — stats mostram contagem de TODOS os eventos enquanto a lista mostra 0. Inconsistência visual baixa (cenário raro) registrada como TD-038.

## obs-027-update: `/funcoes-colaborador/list` removida — TD-036 resolvido
---
id: obs-027
type: dead_code
status: resolved
confidence: 100
evidence: 2
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [dead-code, routing, funcoes, TD-036]
---
Rota `GET /funcoes-colaborador/list` foi removida de `index.php` no commit `162f717`. O grupo `/funcoes-colaborador` permanece ativo com 3 rotas autenticadas: `POST /store`, `POST /update/{id}`, `POST /delete/{id}`. Ver obs-027 original.

## obs-028: `backups/` não estava em `.gitignore` (corrigido 2026-06-29)
---
id: obs-028
type: observation
status: resolved
confidence: 100
evidence: 1
first_seen: 2026-06-29
last_verified: 2026-06-29
resolved: 2026-06-29
tags: [backup, security, gitignore]
---
Três arquivos SQL de backup gerados em 2026-06-29 (`backups/backup_pre_truncate_*.sql`) estavam untracked e sem proteção de `.gitignore`. Esses arquivos contêm dados de produção. `backups/` foi adicionado ao `.gitignore` nesta sessão. Backups futuros não serão versionados acidentalmente.

## obs-031: Sessão s17 — Módulo Estoque Completo (2026-07-01)
---
id: obs-031
type: observation
status: confirmed
confidence: 100
evidence: 1
first_seen: 2026-07-01
last_verified: 2026-07-01
tags: [estoque, serial, barcode, montagem, disponibilidade, s17]
---
**Mudanças de s17:**

1. **Nomenclatura serial→código de barras** (UI completa): todas as views, labels, toasts e mensagens de erro atualizadas. Banco, classes PHP e rotas inalterados.

2. **Campo catalog `produtos.codigo_barras`** (migration 047): campo opcional VARCHAR(100) em `produtos` para código do modelo/catálogo. Distinto de `seriaisproduto.serial` (código da unidade física, globalmente único). Presente em create/edit; removido da listagem (pouco valor visual ali).

3. **Fix bug edição**: `SerialProdutoService::checkDuplicata()` usava `!==` (strict) para comparar `$existing['id']` (string PDO) com `$ignoreId` (int). Fix: `(int)$existing['id'] !== $ignoreId`. **Regra geral:** sempre castear IDs vindos de PDO antes de comparação estrita.

4. **Fix alocação `qtd_alocada`**: `MontagemService::encaminharParaSala()` agora sincroniza `produto_evento_seriais` e recalcula `qtd_alocada`. `removerDaSala()` idem na direção inversa. Dois helpers privados: `alocarSerial()` e `desalocarSerial()`. `MontagemRepository::findMontagemWithDetails()` retorna `id_serial` e `id_produto_evento`.

5. **Listagem `/estoque` redesenhada**: removidas colunas "Cód. Barras" e "Em Evento"; adicionadas "Total", "Disponíveis" (verde), "Na Rua" (badge amarelo clicável → modal com seriais por evento/sala). Fonte de verdade: tabela `montagens`, não `qtd_alocada` (que pode ter stale data).

6. **Novo endpoint**: `GET /estoque/em-campo?id_produto=X` → seriais com montagem ativa, com evento + sala. Adicionado em `ProdutoController::emCampo()` e rota `routes/web.php`.

7. **8 testes e2e** em `tests/e2e/codigos-barras-crud.spec.js`: add único, add em lote, editar, alterar status, excluir, duplicata mesmo produto, duplicata cross-produto, lote parcial. Todos passam.

**Commits:** `2e5ad63` (alocação), `23009ed` (listagem), mais commits da parte de nomenclatura/migration/testes.

## obs-030: Sessão s16 — Auditoria de Segurança Completa (2026-07-01)
---
id: obs-030
type: observation
status: confirmed
confidence: 100
evidence: 1
first_seen: 2026-07-01
last_verified: 2026-07-01
tags: [security, architecture, data-consistency, s16]
---
Sessão s16 (2026-07-01): 36 commits, 306 arquivos alterados, 273 issues corrigidas.

**Segurança resolvida:** sec-008 (passwords), sec-009 (display_errors), sec-010 (SQL injection), sec-011 (stack trace), sec-012 (CSP unsafe-inline), sec-013 (SSL PDO), sec-014 (rate limiting/CORS), XSS em 8 views, path traversal, MIME upload, IP spoofing, exception leak.

**Novos arquivos criados:**
- `src/Repository/DashboardRepository.php` — 24 queries extraídas de DashboardController (commit `b6f2799`)
- `src/Repository/ContasPagarRepository.php` expandido — métodos de contas_pagar migrados de FechamentoRepository (commit `15a5cc7`)
- `src/Repository/ProdutoEventoSerialRepository.php` — 2 novos métodos: `findBySerialAndProdutoEvento()`, `atualizarStatus()` (commit `cd1eab0`)

**Inconsistência detectada:** CHANGELOG menciona `PublicOriginMiddleware.php` como "Novo" (commit `bfd41cd`) mas o arquivo **NÃO existe** em `src/Http/Middleware/`. Funcionalidade foi provavelmente implementada em `public/index.php` ou `routes/publico.php` diretamente. **Investigar antes de criar o arquivo.**

**Fix de consistência de dados:** `DevolucaoService::desalocarSerialDoProdutoEvento()` — quando serial devolvido, `produto_evento_seriais.status → 'devolvido'` e `produtos_evento.qtd_alocada` recalculado via `COUNT(*) WHERE status='alocado'`. Evita stale data pós-devolução.

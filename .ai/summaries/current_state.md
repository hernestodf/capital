# Estado Atual do Projeto — 2026-07-01 (sessão 17 — Módulo Estoque: Códigos de Barras + Disponibilidade)

---

## Métricas Reais (verificadas em 2026-07-01)

| Métrica | Valor atual |
|---------|-------------|
| Arquivos PHP src/ | 148 |
| Controllers web | 38 |
| Controllers API | 12 |
| Controllers totais | 50 |
| Services | 44 |
| Repositories | 33 |
| Views PHP | 73 |
| Migrations SQL | 50 (001–047 + 002/003 reescritos) |
| Rotas registradas | ~252 (web + API + public + cron) |
| JS files | 16 (1 core + 1 module + 14 eventos) |
| CSS files | 1 (1358 LOC custom) |
| Tabelas no banco | 36+ ativas |
| Commits git | 123 (HEAD em `23009ed`) |
| Skills .ai/ | 31 |
| Testes Playwright | 19 specs |
| MCP servers | 15 documentados |

---

## Git

| Item | Estado |
|------|--------|
| Branch | `main` |
| Commits | 119 (HEAD em `24d1167`) |
| Remote | ⚠️ Nenhum configurado (won't fix — decisão do usuário) |
| Status | Limpo |
| Date range | 2026-06-27 → 2026-07-01 (5 dias de commits) |

---

## Deploy e Ambiente

> ⚠️ Esta máquina é **desenvolvimento local** (`192.168.1.106`). Produção: `177.11.54.229`.

| Item | Valor |
|------|-------|
| Servidor produção | `capital.sisloc.online` (`177.11.54.229`) |
| Path FTP | `/public_html/subdomains/capital/` |
| FTP host/user | `sisloc.online` / `sisloc` |
| Banco | `sisloc_newsisloc` @ `sisloc.online` |
| PHP versão | 8.4.20 (produção) / 8.4.21 (local) |
| Web Server | Apache httpd (cPanel managed) |
| SSL | Let's Encrypt (auto-renewal) |

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.0+ MVC custom (sem framework) |
| Banco | MySQL via PDO (`Connection.php`) |
| Frontend CSS | Custom CSS (1358 LOC, neon design system) |
| Frontend JS | Vanilla JS (707 LOC) + Alpine.js |
| PDF | mPDF 8.3 |
| Email | PHPMailer 7 |
| Excel | PhpSpreadsheet 5.7 |
| WhatsApp | Bot próprio via HTTP API (201.23.68.17:3000) |
| MCP Servers | Git, MySQL, SSH, Docker, Playwright, RAG, Logs, Health |
| RAG | Markdown-based index + retrieval rules |
| Testes | Playwright (smoke, e2e, regression) |

---

## Arquitetura

```
Request → Router → Middleware (CSRF, Auth, RBAC) → Controller → Service → Repository → Connection (PDO) → MySQL
                                ↓
                         ErrorHandler ← Logger
                                ↓
                         Response (HTML/JSON)
```

| Camada | Arquivos | Padrão |
|--------|----------|--------|
| Core | Application, Router, Response, Env, Session, Csrf, Logger, ErrorHandler | Framework custom |
| Http | Controller (base), 5 Middleware, BaseCrudController | MVC |
| Controllers | 50 (39 web + 11 API/cron) | Um por entidade |
| Services | 41 | Lógica de negócio |
| Repositories | 32 | Acesso a dados (PDO raw SQL) |
| Database | Connection (PDO singleton) | Prepared statements |

---

## RBAC

| Role | Permissões | Dashboard |
|------|-----------|-----------|
| administrativo | Acesso total (75 permissões) | /dashboard/administrativo |
| comercial | Eventos próprios + estoque limitado | /dashboard/comercial |
| estoquista | Estoque apenas | /dashboard/estoquista |
| guest | Nenhum | /auth/login |

**Migrado para banco** via migration 046 (2026-06-29): `permissoes` + `role_permissoes` tables.

---

## Sessões Recentes

| Sessão | Data | Features / Fixes |
|--------|------|-----------------|
| s12 | 2026-06-29 | AIPOS 4.0 reinit: métricas corrigidas |
| s13 | 2026-06-29 | TD-004 RBAC via DB; limpeza AI morta; /meu-perfil; routes split; EventoPdfController refatorado |
| s14 | 2026-06-30 | Context gap analysis; documentação .ai/ atualizada |
| s15 | 2026-06-30 | AIPOS 4.0 completion: 31 skills, 19 testes, 8 monitoring, RAG script, MCP migrations |
| s16 | 2026-07-01 | **Auditoria segurança + arquitetura:** 36 commits, 306 arquivos, 273 issues; sec-008→014 resolvidos; DashboardRepository + ContasPagarRepository extraídos; qtd_alocada fix na devolução; deploy produção ✅ |
| s17 | 2026-07-01 | **Módulo Estoque — Códigos de Barras + Disponibilidade:** renomear serial→código de barras (UI completa), campo catalog `codigo_barras` em `produtos` (migration 047), fix bug edição (PDO type cast), 8 testes e2e, fix alocação (`encaminharParaSala`/`removerDaSala` sincronizam `qtd_alocada`), listagem com Total/Disponíveis/Na Rua + modal por evento/sala |

---

## Pendências Abertas

### Segurança (RESOLVIDAS na s16 — 2026-07-01)
- ~~**sec-008**~~ ✅ passwords em `config/states.php` — `changeme` removidos
- ~~**sec-009**~~ ✅ `display_errors=1` — ErrorHandler endurecido para produção
- ~~**sec-010**~~ ✅ SQL injection LIMIT/OFFSET — BaseRepository, FechamentoRepository, SessaoApiController
- ~~**sec-011**~~ ✅ Stack trace exposta — ErrorHandler retorna mensagens genéricas
- ~~**sec-012**~~ ✅ `unsafe-inline` CSP — Response.php removeu unsafe-inline
- ~~**sec-013**~~ ✅ SSL verify PDO — Connection.php configura via `DB_SSL_VERIFY`
- ~~**sec-014**~~ ✅ Sem rate limiting auth — CORS endurecido, PublicOriginMiddleware referenciada
- ~~**TD-041**~~ ✅ DashboardController queries — DashboardRepository criado (commit `b6f2799`)

### Infraestrutura (média)
- **TD-013** — Refatorar EmailService (440 LOC — templates HTML inline)
- **TD-039** — ContasPagarController: extrair WhatsApp methods para WhatsAppController
- **TD-040** — FechamentoService (483 LOC)

### Limpeza (baixa)
- 7 tabelas AI mortas (0 rows) — aguarda confirmação para DROP (TD-025 — backlog mostra item duplicado)
- `PublicOriginMiddleware.php` citada no changelog `bfd41cd` mas **não existe** no filesystem — investigar

---

## Estado Geral da Produção

- **Site:** `capital.sisloc.online` — ✅ operacional
- **Código:** ✅ Deploy s16 em produção (2026-07-01 00:30)
- **Banco:** ✅ Migrations 043–046 aplicadas; 002/003 já existiam
- **Git:** ✅ Funcional (119 commits, sem remote — won't fix)
- **Bugs conhecidos:** Nenhum crítico ativo (fix de alocação qtd_alocada deployado em s17)
- **Segurança:** ✅ CRITICAL sec-008→014 resolvidos; HIGH sec-012→014 resolvidos
- **Consistência de dados:** ✅ `qtd_alocada` agora decrementado na devolução (`cd1eab0`)

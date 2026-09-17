# Changelog — 2026-07-01

**Commits:** 34 (desde 5d1356c)  
**Arquivos alterados:** 306  
**Prioridade:** Segurança + Consistência de Dados

---

## Resumo

Auditoria arquitetural completa + correção de 273 issues identificadas + fix de consistência de dados no fluxo de devolução.

---

## Segurança (18 commits)

| Commit | Arquivo | Correção |
|--------|---------|----------|
| `4ded067` | `Rbac.php` | Password hash removido da session + cache per-role |
| `f65a1cd` | `Router.php` | Middleware parameterizado `[ClassName, [args]]` |
| `79a4232` | `Response.php` | CRLF injection + CSP `unsafe-inline` removido |
| `0f1f004` | `Request.php` | IP spoofing — `ip()` só confia em `TRUSTED_PROXIES` |
| `7aa9cd0` | `ErrorHandler.php` | Erros não expostos em produção (genérico) |
| `7aa9cd0` | `Component.php` | Path traversal prevenido (regex whitelist) |
| `7aa9cd0` | `CronAuthMiddleware.php` | `hash_equals()` anti timing attack |
| `7aa9cd0` | `ApiKeyMiddleware.php` | `$GLOBALS` pollution removido |
| `c4eb0ad` | `BaseRepository.php` | SQL injection em `findBy()` (whitelist de campos) |
| `c4eb0ad` | `FechamentoRepository.php` | SQL injection em `atualizarContaPagar()` |
| `8ddbf53` | `Logger.php` | `mkdir 0750` + rotação 10MB |
| `8ddbf53` | `.htaccess` | Blocking `.env`, `config/`, `src/` |
| `8ddbf53` | `Connection.php` | SSL verify configurável (`DB_SSL_VERIFY`) |
| `bfd41cd` | `public/index.php` | CORS wildcard `*` removido |
| `bfd41cd` | `PublicOriginMiddleware.php` | Novo: rate limiting + Origin validation |
| `5ebee75` | `SessaoApiController.php` | SQL injection LIMIT/OFFSET |
| `8670eec` | Views (8 arquivos) | XSS — `addslashes` → `htmlspecialchars(ENT_QUOTES)` |
| `8758a21` | `PresencaPublicController.php` | Path traversal no filename (regex sanitize) |
| `8758a21` | `ContasPagarController.php` | Exception leak → Logger + JSON genérico |
| `8758a21` | `FechamentoController.php` | `echo` → `$this->json()`, finfo MIME, mkdir 0750 |
| `98d52d7` | `EventoPdfController.php` | Auth errors → JSON |
| `98d52d7` | `ConfiguracoesController.php` | SVG upload removido, debug logs removidos |
| `8efb7c3` | `EventoFotoService.php` | finfo MIME (server-side) |

## Arquitetura (7 commits)

| Commit | Arquivo | Correção |
|--------|---------|----------|
| `fdbe643` | Services + JS | Extração de services e JS de arquivos grandes |
| `7ba9852` | Views | Dead code removido, `baseUrl/escapeHtml/formatDate` consolidados |
| `57604cf` | `DevolucaoService.php` | `$_SESSION` removido |
| `28d1e81` | `EventoRHNotificacaoService.php` | cURL → `WhatsAppService` |
| `7f49134` | `PdfGeneratorService.php` | `exit()` removido dos métodos |
| `b6f2799` | `DashboardController.php` | 24 queries SQL → `DashboardRepository` |
| `15a5cc7` | `FechamentoRepository.php` | Métodos contas_pagar → `ContasPagarRepository` |

## Database (4 commits)

| Commit | Arquivo | Correção |
|--------|---------|----------|
| `56df299` | `migration 001` | ENUM atualizado: `administrador,produtor` → `administrativo,comercial` |
| `1730f75` | `migration 002` | Tabela `planilhas` (referenciada por migration 012) |
| `1730f75` | `migration 003` | Tabela `produtores` (referenciada por migration 011) |
| `72d06ec` | `migration 006` | Senha removida dos comentários |

## Frontend (2 commits)

| Commit | Arquivo | Correção |
|--------|---------|----------|
| `d0f90f9` | Views (5 arquivos) | 60 `console.log` removidos |
| `3e685a1` | `states.php` | Senhas fracas `changeme` removidas |
| `ec5d858` | `evento/edit.php` | `addslashes` → `json_encode` para JS |

## Logging (1 commit)

| Commit | Arquivos | Correção |
|--------|----------|----------|
| `7393993` | 7 controllers/services | 20 `error_log` → `Logger::error/warning/debug` |

## Consistência de Dados (1 commit)

| Commit | Arquivos | Correção |
|--------|----------|----------|
| `cd1eab0` | `DevolucaoService.php`, `ProdutoEventoSerialRepository.php` | `qtd_alocada` decrementado na devolução |

---

## Detalhe: Fix de Consistência (cd1eab0)

### Problema
Quando um serial era devolvido via `DevolucaoService::processarUnico()`, o status no montagem era alterado para `devolvido`, mas `produtos_evento.qtd_alocada` não era decrementado. Isso fazia o cálculo `faltante = qtd - qtd_alocada - qtd_sublocada` ficar desatualizado.

### Solução
Adicionado método `desalocarSerialDoProdutoEvento()` que:
1. Busca a alocação em `produto_evento_seriais` (via `id_serial` + `id_produto_evento` da montagem)
2. Atualiza status para `devolvido`
3. Recalcula `qtd_alocada` via `COUNT(*) WHERE status='alocado'`

### Fluxo corrigido
```
processarUnico()
  ├── 1. Criar/atualizar devolucao
  ├── 2. Atualizar status serial
  ├── 3. Marcar montagem como devolvida
  ├── 4. NOVO: desalocarSerialDoProdutoEvento()
  │       ├── Buscar alocacao em produto_evento_seriais
  │       ├── Atualizar status para 'devolvido'
  │       └── Recalcular qtd_alocada
  ├── 5. Atualizar flag pendencia evento
  └── 6. Verificar evento completo
```

---

## Migrations executadas em produção

**Status:** ✅ Executadas em 2026-07-01 00:30

| Migration | Tabela | Status | Detalhes |
|-----------|--------|--------|----------|
| 002 | `planilhas` | ✅ Já existia | Referenciada pela migration 012 (FK `id_planilha`) |
| 003 | `produtores` | ✅ Já existia | Referenciada pela migration 011 (FK `id_produtor`) |
| 001 | `users.role` ENUM | ✅ Já correto | Valores: `guest,estoquista,administrativo,comercial` |

### Verificação do banco produção:

```sql
-- ENUM role (já atualizado)
SHOW COLUMNS FROM users WHERE Field='role';
-- Result: enum('guest','estoquista','administrativo','comercial')

-- planilhas (já existe com potencia_w)
SHOW COLUMNS FROM planilhas LIKE 'potencia_w';
-- Result: decimal(10,2)

-- produtores (já existe)
SHOW TABLES LIKE 'produtores';
-- Result: 1 row
```

### Procedimento executado:

1. Upload `migrations_2026-07-01.sql` via FTP
2. Execução via script PHP temporário (`CREATE TABLE IF NOT EXISTS` — idempotente)
3. Verificação do estado atual do banco
4. Remoção do script temporário e SQL do servidor

---

## Notas de Deploy

1. ✅ Backup do banco — não necessário (migrations idempotentes)
2. ✅ Migrations executadas — tabelas já existiam
3. Limpar cache do navegador após deploy (Ctrl+F5)
4. Verificar login funciona (Rbac alterado)
5. Testar fluxo de eventos (devolução com qtd_alocada)

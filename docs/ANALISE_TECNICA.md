# Análise Técnica Completa — SisLoc

**Data:** 2026-04-29
**Escopo:** Todo o código fonte do projeto
**Metodologia:** Análise estática de todos os arquivos PHP, JS, CSS, SQL e views

---

## 1. BANCO DE DADOS

### 1.1 Estrutura

**41 tabelas** identificadas:

| Categoria | Tabelas | Count |
|-----------|---------|-------|
| Autenticação/RBAC | `users`, `modulos`, `permissoes`, `role_permissoes`, `api_keys`, `api_request_logs` | 6 |
| Negócio Core | `clientes`, `demandantes`, `produtores`, `eventos`, `empresa` | 5 |
| Fornecedores | `categorias`, `subcategorias`, `fornecedores` | 3 |
| RH/Colaboradores | `colaboradores`, `colaboradores_verificacao`, `evento_colaboradores`, `evento_colaborador_presencas` | 4 |
| Estoque/Produtos | `secao`, `produtos`, `seriaisproduto`, `planilhas`, `unidademedida` | 5 |
| Eventos/Operações | `categorias_sala`, `salas`, `produtos_evento`, `montagens`, `devolucoes`, `evento_fotos` | 6 |
| Financeiro/Cotações | `contas_pagar`, `item_cotacoes`, `item_cotacoes_parcelas`, `item_cotacoes_mensagens`, `item_cotacoes_anexos` | 5 |
| AI System | `ai_learning`, `ai_metrics`, `ai_executions`, `ai_corrections`, `ai_feedbacks`, `ai_interactions`, `ai_preferences` | 7 |

### 1.2 Relacionamentos

**40 foreign keys** definidas. A maioria com `ON DELETE CASCADE` ou `SET NULL` corretamente configurados.

**Pontos positivos:**
- `Connection.php` usa PDO com `ATTR_EMULATE_PREPARES => false` (prepared statements reais no MySQL)
- Todas as queries nos Repositories usam prepared statements com bind parameters
- `Connection::query()` e `Connection::exec()` SEMPRE usam prepared statements

### 1.3 Problemas Encontrados

**MÉDIO — Tabelas sem migrações**
- `contas_pagar` e `devolucoes` não têm arquivo de migration — foram criadas manualmente fora do sistema
- Migration `011_create_eventos_table.sql` referencia `produtores(id)` antes da tabela existir

**MÉDIO — Typo em migration**
- `020_adicionar_parcelas_contas_pagar.sql` linha 7: coluna `parcelas_qTD` (uppercase "TD")

**MÉDIO — Índices faltando**
Tabelas com buscas LIKE mas sem índices nas colunas pesquisadas:
- `clientes`: sem índice em `nome_fantasia`, `razao_social`, `cpf_cnpj`, `email`
- `colaboradores`: sem índice em `nome`, `email`, `telefone`
- `fornecedores`: sem índice em `nome_fantasia`, `razao_social`, `cpf_cnpj`
- `planilhas`: sem índice em `item`, `descricao`
- `categorias`: sem índice (buscas LIKE sem FULLTEXT)

**MÉDIO — Query N+1**
`FechamentoRepository::getFornecedores()` (linha 91-103): dentro de um foreach, executa query separada para cada fornecedor. Deve ser um JOIN.

**BAIXO — Colunas dinâmicas interpoladas**
- `BaseRepository::create()`: nomes de colunas vêm de `$data` keys, interpolados diretamente no SQL. Mitigado pelo filtro `$fillable`.
- `CotacaoRepository::update()`: mesmo padrão.
- `FechamentoRepository::criarContaPagar()`: mesmo padrão.

---

## 2. PHP (BACKEND)

### 2.1 Arquitetura

```
public/index.php → Application → Router → Middleware → Controller → Service → Repository → PDO
```

**Positivo:**
- Separação MVC clara: Controllers (HTTP), Services (lógica), Repositories (dados)
- Router suporta grupos com prefixos e middleware stacking
- Detecção automática de ambiente (local/produção) via HTTP_HOST
- Auto-detect de subpasta via SCRIPT_NAME

**Negativo:**
- **Zero dependency injection** — Controllers instanciam serviços com `new`
- **Sem container DI** — impossível mockar para testes unitários
- **Sem Models/Entities** — dados trafegam como arrays puros
- **`Application.php` é God class** — faz routing, config, themes, views, sessions, URLs

### 2.2 Segurança

#### CSRF — PARCIAL (6/10)

**OK:**
- Token gerado com `random_bytes(32)` (criptograficamente seguro)
- Comparação constant-time (`!==`)
- Regenerado no login

**PROBLEMAS:**
- CSRF **NÃO é obrigatório no login** (`AuthController::doLogin()` linha 64: só valida se token presente)
- **Maioria dos POST routes NÃO valida CSRF** (usuarios, clientes, demandantes, estoque, eventos)
- Não há middleware CSRF — validação é feita manualmente em cada controller (inconsistente)

#### XSS — CRÍTICO (3/10)

**PROBLEMAS CRÍTICOS:**
- **Zero output encoding no framework.** `Response::html()` envia HTML raw
- `Application::view()` usa `extract($data)` — variáveis injetadas raw nas views
- **Sem headers de segurança:** Content-Security-Policy, X-Frame-Options, X-Content-Type-Options
- `scripts.js` linha 257: `showToast()` usa `innerHTML` com `title` e `msg` sem escaping — chamado com dados de usuário de múltiplos arquivos

#### Sessão — FRACO (4/10)

**PROBLEMAS CRÍTICOS:**
- **Sem `session.cookie_httponly`** — cookies acessíveis via JavaScript (XSS → roubo de sessão)
- **Sem `session.cookie_secure`** — cookies enviados por HTTP plaintext
- **Sem `session.cookie_samesite`** — vulnerável a CSRF cross-site
- **Rate limiting por sessão** — atacante abre novas sessões para bypassar limite de 5 tentativas

#### Senhas — BOM (8/10)

**OK:**
- `password_hash()` com `PASSWORD_DEFAULT` (bcrypt)
- `password_verify()` no login
- Regeneração de session ID no login

**Falta:** Validação de força (tamanho mínimo, complexidade, breach checking)

#### Upload de Arquivos — MISTO (6/10)

**OK em UploadService:**
- Valida MIME com `finfo_open()`, tamanho, extensão
- Nomes gerados com `uniqid()` + `random_bytes()`

**PROBLEMAS:**
- `CotacaoController::uploadAnexo()` confia em `$_FILES['type']` (controlado pelo cliente) — permite upload de `.php` disfarçado de PDF
- Diretórios criados com `0777` (permissão total)
- Arquivos em `public/uploads/` acessíveis via URL direto

### 2.3 Vulnerabilidades

| Severidade | Vulnerabilidade | Localização |
|------------|----------------|-------------|
| **CRÍTICO** | CSRF opcional no login | `AuthController::doLogin()` linha 64 |
| **CRÍTICO** | Sem headers de segurança (CSP, X-Frame, etc) | Todo o framework |
| **CRÍTICO** | XSS via `showToast()` innerHTML | `scripts.js:257` |
| **CRÍTICO** | Sem httponly/secure/samesite nos cookies | Session config |
| **ALTO** | Cron `/cron/imap-cotacao` sem autenticação | `public/index.php:322` |
| **ALTO** | Secret de cron exposto na URL (`?key=`) | `public/index.php:357-363` |
| **ALTO** | MIME type de upload controlado pelo cliente | `CotacaoController:262-336` |
| **ALTO** | Rate limiting bypassável por nova sessão | `AuthController:doLogin()` |
| **ALTO** | `BaseCrudController` chama método inexistente | `BaseCrudController:176,181` |
| **ALTO** | `AIController::apiExecute()` chama `$this->request->json()` inexistente | `AIController:161` |
| **MÉDIO** | `Request::ip()` confia em `X-Forwarded-For` sem validação | `Request.php:114-117` |
| **MÉDIO** | `AuthController::sessionInfo()` acessível por não-autenticados | `AuthController:114` |
| **MÉDIO** | Colunas interpoladas dinamicamente em SQL | `BaseRepository`, `CotacaoRepository` |
| **BAIXO** | Classe `Session` duplicada em `Application.php` e `Session.php` | `Application.php:445` |

---

## 3. JAVASCRIPT (FRONTEND)

### 3.1 Organização

| Arquivo | Linhas | Padrão |
|---------|--------|--------|
| `scripts.js` | 579 | Global (30+ funções no `window`) |
| `cotacao.js` | 441 | IIFE |
| `montar-os.js` | 881 | IIFE |
| `fechamento.js` | 1118 | IIFE |
| `salas-produtos.js` | 1049 | IIFE |
| `rh.js` | 539 | IIFE |
| `fornecedores.js` | 153 | IIFE |
| `dados-evento.js` | 85 | Global |
| `tabs.js` | 56 | Global |
| `devolver-os.js` | 27 | IIFE (stub) |

### 3.2 Problemas

**CRÍTICO — Poluição do namespace global**
`scripts.js` define ~30+ funções diretamente em `window`. Colide com qualquer lib terceira.

**CRÍTICO — XSS via innerHTML**
60+ usos de `innerHTML`. `showToast()` em `scripts.js:257` injeta `title` e `msg` sem escaping — dados de usuário de múltiplos arquivos passam por aqui.

**ALTO — Funções duplicadas (5x)**
`escapeHtml()` redefinida em: `cotacao.js`, `rh.js`, `fechamento.js`, `salas-produtos.js`, `montar-os.js`

`formatDate()` / `formatDateTimeBR()` redefinidas em: `rh.js`, `fechamento.js`, `cotacao.js`

**ALTO — Error handlers vazios**
`salas-produtos.js` linhas 640, 664, 808, 832: `.catch(function() {})` — erros silenciosamente ignorados.

**ALTO — `apiFetch` helper definido mas não usado**
`scripts.js` tem um helper `apiFetch()` completo com CSRF, timeout e error handling. Nenhum módulo o utiliza — todos escrevem fetch inline.

**MÉDIO — Inline onclick em HTML gerado**
Múltiplos arquivos geram HTML com `onclick="funcao(ID)"` — frágil e vetor XSS se dados não escapados.

**MÉDIO — cloneNode workaround**
`montar-os.js` e `rh.js` usam `cloneNode(true)` + `replaceChild` para remover listeners — gambiarra para má gestão de eventos.

---

## 4. CSS

### 4.1 Estrutura

`styles.css`: **1341 linhas**, único arquivo.

**Positivo:**
- 20+ variáveis CSS em `:root` (cores, sombras, z-index, layout)
- 5 breakpoints responsivos (1440, 1366, 1280, 1024, 768, 640, 480px)
- Design system consistente (`.btn-*`, `.badge-*`, `.fg/.fi/.fl`)

### 4.2 Problemas

**ALTO — Regras CSS conflitantes**

| Seletor | Primeira definição | Segunda definição |
|---------|-------------------|-------------------|
| `.text-xs` | `font-size:11px` (linha 514) | `font-size:12px` (linha 1141) |
| `.rounded-lg` | `border-radius:8px` (linha 509) | `border-radius:14px` (linha 1176) |
| `.toast` | `min-width:340px; padding:16px` (linha 329) | `min-width:320px; padding:14px` (linha 821) |

**ALTO — Classes utilitárias duplicadas**
`.flex`, `.items-center`, `.justify-between`, `.gap-*`, `.border`, `.w-full`, `.overflow-*` definidas DUAS vezes (v1 linhas 521-556 + v2 linhas 1123-1200).

**MÉDIO — Cores hardcoded**
- `.bg-red-600` usa `#D62B2B` ao invés de `var(--neon-red)`
- `.td-act-blue:hover` usa `#3b82f6` ao invés de `var(--neon-blue)`

**BAIXO — Sem `prefers-reduced-motion`**
Sem media query para usuários que preferem animações reduzidas.

---

## 5. FLUXOS DO SISTEMA

### 5.1 Autenticação

```
GET /auth/login → Form com CSRF → POST /auth/login
  → Rate limit (5 tentativas/15min, POR SESSÃO ← bypassável)
  → Rbac::login(email, password)
  → password_verify()
  → Check status do usuário
  → Session::set('user', $user)
  → Session::regenerate() (chamado 2x ← redundante)
  → Redirect para dashboard por role
```

**Falha:** Rate limiting por sessão, não por IP. Attacker cria novas sessões para bypassar.

### 5.2 Request Lifecycle

```
Browser → .htaccess (rewrite) → public/index.php
  → Application::boot() (Env, ErrorHandler, Session)
  → Routes registradas inline em index.php
  → Request criado (wraps $_GET, $_POST, $_SERVER)
  → Router::dispatch()
    → Normaliza URI (remove SCRIPT_NAME base path)
    → Match por regex
    → Middleware chain (se houver)
    → Controller action
  → Response::send()
```

**OK:** Router detecta subpasta automaticamente via SCRIPT_NAME.

### 5.3 RBAC

```
AuthMiddleware → Rbac::isGuest() → bloqueia se não logado
  → Controller → Rbac::check('modulo.acao') → verifica role do usuário
```

**Falha:** `$roles` hierarchy definida mas **NUNCA USADA**. `Rbac::check()` faz match exato de role, ignora herança.

### 5.4 CSRF

```
Csrf::generate() → $_SESSION['csrf_token'] → view (hidden input)
  → POST → Csrf::validate($token) → comparação strict
```

**Falha:** Token NÃO é rotacionado após validação. Válida apenas se presente (não obrigatório).

### 5.5 CRUD Padrão

```
GET /modulo/create → Form → POST /modulo/store
  → Controller valida CSRF + RBAC
  → Service::create($data) → validate() → sanitize()
  → Repository::insert($data)
  → Redirect com ?success=created
```

**OK:** Padrão consistente em todos os módulos.

---

## 6. PROBLEMAS ENCONTRADOS — RESUMO

| # | Severidade | Categoria | Descrição |
|---|-----------|-----------|-----------|
| 1 | **CRÍTICO** | Segurança | CSRF opcional no login (`AuthController:64`) |
| 2 | **CRÍTICO** | Segurança | Sem headers CSP/X-Frame/X-Content-Type |
| 3 | **CRÍTICO** | XSS | `showToast()` innerHTML sem escaping (`scripts.js:257`) |
| 4 | **CRÍTICO** | Sessão | Sem httponly/secure/samesite nos cookies |
| 5 | **ALTO** | Sessão | Rate limiting bypassável por nova sessão |
| 6 | **ALTO** | Upload | MIME type controlado pelo cliente (`CotacaoController`) |
| 7 | **ALTO** | Cron | `/cron/imap-cotacao` sem autenticação |
| 8 | **ALTO** | Cron | Secret exposto na URL query string |
| 9 | **ALTO** | CSS | Regras conflitantes (`.text-xs`, `.rounded-lg`, `.toast`) |
| 10 | **ALTO** | JS | 30+ funções globais poluindo namespace |
| 11 | **ALTO** | JS | `escapeHtml()` duplicada 5 vezes |
| 12 | **ALTO** | JS | Error handlers vazios (4 em `salas-produtos.js`) |
| 13 | **ALTO** | Código | `BaseCrudController` e `AIController` chamam métodos inexistentes |
| 14 | **ALTO** | XSS | PHP variables em JS context sem escaping (`evento/edit.php`) |
| 15 | **ALTO** | XSS | 60+ innerHTML com escaping inconsistente |
| 16 | **MÉDIO** | DB | 5 tabelas sem índices nas colunas de busca |
| 17 | **MÉDIO** | DB | Query N+1 em `FechamentoRepository::getFornecedores()` |
| 18 | **MÉDIO** | DB | Tabelas sem migrations (`contas_pagar`, `devolucoes`) |
| 19 | **MÉDIO** | DB | Typo `parcelas_qTD` em migration 020 |
| 20 | **MÉDIO** | RBAC | Hierarquia de roles definida mas não usada |
| 21 | **MÉDIO** | CSRF | Maioria dos POST routes sem validação CSRF |
| 22 | **MÉDIO** | Info Leak | `sessionInfo()` acessível por não-autenticados |
| 23 | **MÉDIO** | JS | `apiFetch` helper definido mas não utilizado |
| 24 | **MÉDIO** | CSS | Cores hardcoded ao invés de variáveis CSS |
| 25 | **MÉDIO** | Arquitetura | Zero dependency injection |
| 26 | **BAIXO** | DB | Colunas dinâmicas interpoladas em SQL (mitigado por fillable) |
| 27 | **BAIXO** | Sessão | `session_regenerate_id()` chamado 2x no login |
| 28 | **BAIXO** | Código | Classe `Session` duplicada |
| 29 | **BAIXO** | CSS | Demo-only styles inflando CSS |
| 30 | **BAIXO** | JS | `console.log` em produção (`cotacao.js`, `fechamento.js`) |

---

## 7. RECOMENDAÇÕES

### 7.1 Corrigir Imediatamente (Crítico/Alto)

1. **Tornar CSRF obrigatório em TODOS os POST routes**
   - Criar `CsrfMiddleware` e aplicar nos grupos de rotas
   - Remover a verificação condicional do login

2. **Adicionar headers de segurança**
   ```php
   // Em Response::send() ou middleware:
   header("Content-Security-Policy: default-src 'self'");
   header("X-Frame-Options: DENY");
   header("X-Content-Type-Options: nosniff");
   header("X-XSS-Protection: 0");
   ```

3. **Configurar sessão segura**
   ```php
   ini_set('session.cookie_httponly', '1');
   ini_set('session.cookie_secure', '1');  // apenas em produção/HTTPS
   ini_set('session.cookie_samesite', 'Strict');
   ini_set('session.use_strict_mode', '1');
   ```

4. **Rate limiting por IP, não por sessão**
   - Usar tabela `login_attempts` com `ip_address` + `timestamp`
   - Limpar tentativas antigas automaticamente

5. **Proteger endpoints cron**
   - `/cron/imap-cotacao`: adicionar autenticação por header ou IP whitelist
   - `/cron/rh-notificacoes`: usar header `X-Cron-Secret` ao invés de query param

6. **Validar MIME por conteúdo, não por `$_FILES['type']`**
   - Usar `finfo_file()` em `CotacaoController::uploadAnexo()`

7. **Escapar output no JS**
   - Corrigir `showToast()` para usar `textContent` ao invés de `innerHTML`
   - Escapar todas as variáveis PHP em JS com `json_encode()` ao invés de interpolação direta

8. **Corrigir CSS conflitante**
   - Remover definições duplicadas de `.text-xs`, `.rounded-lg`, `.toast`
   - Consolidar utilitários v1 e v2

9. **Corrigir código quebrado**
   - `BaseCrudController::getStoreData()` → `$this->request()->all()` não existe
   - `AIController::apiExecute()` → `$this->request->json()` não existe

### 7.2 Melhorar (Médio)

10. **Adicionar índices faltantes**
    ```sql
    ALTER TABLE clientes ADD INDEX idx_nome_fantasia (nome_fantasia);
    ALTER TABLE clientes ADD INDEX idx_email (email);
    ALTER TABLE colaboradores ADD INDEX idx_nome (nome);
    ALTER TABLE colaboradores ADD INDEX idx_email (email);
    ALTER TABLE fornecedores ADD INDEX idx_nome_fantasia (nome_fantasia);
    ALTER TABLE planilhas ADD INDEX idx_item (item);
    ```

11. **Resolver query N+1**
    - `FechamentoRepository::getFornecedores()`: usar JOIN ao invés de query dentro de foreach

12. **Centralizar CSRF validation**
    - Criar middleware ao invés de repetir em cada controller

13. **Implementar RBAC middleware**
    - Criar `PermissionMiddleware` que verifica permissão por rota
    - Remover checks manuais dos controllers

14. **Escapar PHP variables em JS context**
    - Usar `json_encode($var)` ao invés de `<?= $var ?>` em scripts

15. **Consolidar utilitários JS**
    - Mover `escapeHtml()`, `formatDate()`, `formatDateTime()` para `scripts.js`
    - Fazer módulos usarem o `apiFetch()` helper

### 7.3 Refatoração Sugerida (Baixo/Longo Prazo)

16. **Adicionar DI Container** (PSR-11)
    - Injetar Services nos Controllers via construtor
    - Injetar Repositories nos Services via construtor

17. **Criar Models/Entities**
    - Tipar dados ao invés de usar arrays
    - Validar tipos em tempo de compilação (ou com PHPStan)

18. **Separar CSS por componente**
    - Usar build tool (esbuild, Vite) para concatenar
    - Remover demo-only styles do CSS de produção

19. **Adicionar testes automatizados**
    - PHPUnit para Services e Repositories
    - Integration tests para Controllers

20. **Implementar PSR-3 Logger**
    - Logger estruturado (JSON) para agregação
    - Levels corretos (error, warning, info, debug)

---

**FIM DO RELATÓRIO**

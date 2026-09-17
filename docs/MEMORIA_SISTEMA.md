# MEMÓRIA INTELIGENTE DO SISTEMA — SisLoc (NovoFramework)

> Criada em: 2026-05-08
> Versão: 2.5.0
> Framework: ConectaFramework (PHP 8.4 custom MVC)
> Base: `/var/www/html/novo_sisloc`
> Produção: `https://profox.sisloc.online` (FTP → `sisloc.online:/public_html/subdomains/profox`)

---

## SUMÁRIO

1. [RESUMO DO SISTEMA](#1-resumo-do-sistema)
2. [ARQUITETURA GERAL](#2-arquitetura-geral)
3. [MAPA DE MÓDULOS](#3-mapa-de-módulos)
4. [DEPENDÊNCIAS CRÍTICAS](#4-dependências-críticas)
5. [FLUXOS CRÍTICOS](#5-fluxos-críticos)
6. [REGRAS DE NEGÓCIO ESSENCIAIS](#6-regras-de-negócio-essenciais)
7. [BANCO DE DADOS](#7-banco-de-dados)
8. [PADRÕES E CONVENÇÕES](#8-padrões-e-convenções)
9. [FRONTEND](#9-frontend)
10. [SEGURANÇA](#10-segurança)
11. [PROBLEMAS ENCONTRADOS](#11-problemas-encontrados)
12. [MEMÓRIA OPERACIONAL PARA IA](#12-memória-operacional-para-ia)
13. [RELATÓRIO FINAL](#13-relatório-final)

---

## 1. RESUMO DO SISTEMA

O **SisLoc** é um sistema de gestão de locação de equipamentos para eventos. Gerencia o ciclo completo desde o orçamento (cotação) até a execução (montagem, evento, desmontagem, devolução) e fechamento financeiro.

**Principais funcionalidades:**
- Gestão de clientes, demandantes, fornecedores, produtores, colaboradores
- Orçamento e conversão em locação
- Montagem de equipamentos (controle de seriais por sala)
- Cotação com fornecedores via e-mail (Mailjet + IMAP)
- Controle de presença de colaboradores com foto + geolocalização
- Fechamento financeiro (contas a pagar, pagamento por parcela)
- Notificações WhatsApp (link de verificação de colaborador, envio de comprovantes)
- API REST pública v1 para cadastro externo
- Sistema IA (Agentes) para aprendizado do próprio sistema
- 21 temas visuais customizáveis
- RBAC com 3 perfis: administrador, produtor, estoquista

**Tecnologias:**
| Componente | Tecnologia |
|---|---|
| Backend | PHP 8.4 (PSR-4 com Composer) |
| Frontend | Vanilla JS (IIFE pattern) + CSS custom properties |
| Database | MariaDB/MySQL via PDO |
| Template | PHP nativo (extract + ob_start) |
| Email | Mailjet API v3.1 + PHPMailer (fallback) |
| WhatsApp | Bot externo Node.js (Baileys) em `201.23.68.17:3000` |
| IMAP | webklex/php-imap ^6.2 |
| PDF | mpdf/mpdf ^8.3 |
| Upload | MIME validation via `finfo` |

---

## 2. ARQUITETURA GERAL

### 2.1 Request Lifecycle

```
URL → public/index.php → Application::getInstance()->run()
  → Env::load() → ErrorHandler::register() → Debug::start() → Session::start()
  → registerRoutes() (130+ rotas)
  → new Request() → Router::dispatch(Request)
  → Middleware chain (Auth → Csrf)
  → Controller → Service → Repository → Database (PDO)
  → Response (view/json/redirect)
```

### 2.2 Estrutura de Diretórios

```
public/index.php           → Entry point + 130+ rotas definidas inline
public/css/styles.css      → CSS global (1336 linhas)
public/js/scripts.js       → JS global (601 linhas)
public/js/eventos/         → 8 módulos JS específicos de evento
public/uploads/            → Uploads públicos

src/
├── Auth/Rbac.php          → RBAC (hardcoded permissions array)
├── Core/                  → Application, Router, Request, Response, Csrf, Env, Session, Logger, ErrorHandler, Debug, Component
├── Database/Connection.php → PDO singleton
├── Http/Controller.php    → Base controller
├── Controllers/           → 35 controllers
├── Service/               → 38 services
├── Repository/            → 31 repositories

views/
├── layout/header.php      → DOCTYPE, head, sidebar, topbar, `<div id="main">`
├── layout/sidebar.php     → Nav com RBAC checks
├── layout/footer.php      → Close divs, toast container, scripts
└── (19 diretórios de views)

docs/layout/branco/
├── styles.css             → Design System (1299 linhas)
├── scripts.js             → Design System JS (498 linhas)
└── assets/components/     → 24 componentes PHP

storage/
├── theme.json             → Tema ativo
├── logs/                  → Logs diários
└── uploads/               → Uploads privados (comprovantes)
```

### 2.3 Design Patterns

| Pattern | Onde |
|---|---|
| Singleton | Application, Connection (PDO), Component |
| Service Layer | Controllers delegam para Services |
| Repository | Services usam Repositories para dados |
| Middleware Chain | Router executa callbacks aninhados |
| Front Controller | public/index.php único entry point |
| IIFE (JS) | Todos os módulos JS de evento |

### 2.4 Arquivos Mais Importantes (NÃO ALTERAR SEM CUIDADO)

| Arquivo | Importância | Risco |
|---|---|---|
| `public/index.php` | **CRÍTICO** — Todas as 130+ rotas | Qualquer erro quebra o sistema inteiro |
| `src/Core/Application.php` | **CRÍTICO** — Singleton, boot, view rendering | Quebra todo o request cycle |
| `src/Core/Router.php` | **CRÍTICO** — Dispatch, middleware chain | Navegação inteira quebra |
| `src/Auth/Rbac.php` | **CRÍTICO** — Permissões hardcoded | Permissões mal alteradas podem travar acesso |
| `src/Database/Connection.php` | **CRÍTICO** — PDO singleton | DB inteiro para de funcionar |
| `src/Http/Controller.php` | **CRÍTICO** — Base de todos controllers | Todos os controllers quebram |
| `views/layout/header.php` | **ALTA** — Layout global | Toda UI quebra |
| `src/Controllers/EventoController.php` | **ALTA** — 1168 linhas, maior controller | Módulo mais complexo, N+1 bug conhecido |
| `src/Core/Env.php` | **ALTA** — Carrega .env, detecta ambiente | Deploy quebra se mal alterado |
| `src/Http/Middleware/ApiKeyMiddleware.php` | **ALTA** — API v1 auth | API externa para de funcionar |

---

## 3. MAPA DE MÓDULOS

### 3.1 Relação entre Módulos

```
                    ┌─────────────┐
                    │   Clientes  │
                    └──────┬──────┘
                           │ FK
                    ┌──────▼──────┐     ┌──────────────┐
                    │ Demandantes │────▶│   Fornecedores│
                    └──────┬──────┘     └──────┬───────┘
                           │ FK                │ FK
                    ┌──────▼──────┐     ┌──────▼───────┐
                    │            │     │  Categorias   │
                    │   Eventos  │     │  Subcategorias│
                    │            │     └──────────────┘
                    └──┬───┬──┬─┘
                       │   │  │
          ┌────────────┘   │  └──────────────┐
          ▼                ▼                 ▼
   ┌───────────┐   ┌───────────┐    ┌──────────────┐
   │   Salas   │   │ Planilhas │    │  Produtores   │
   └─────┬─────┘   └───────────┘    └──────────────┘
         │
    ┌────▼─────┐        ┌──────────────────┐     ┌──────────────┐
    │Produtos  │───────▶│  Cotação (Email)  │────▶│  Contas Pagar│
    │ Evento   │        │  + WhatsApp       │     │  + WhatsApp  │
    └────┬─────┘        └──────────────────┘     └──────────────┘
         │
    ┌────▼─────┐        ┌──────────────────┐
    │Montagens │───────▶│   Devoluções     │
    │(Seriais) │        │  (Seriais)       │
    └──────────┘        └──────────────────┘

         ┌─────────────┐      ┌──────────────────┐
         │Colaboradores│─────▶│  Verificação (WP)│
         └──────┬──────┘      └──────────────────┘
                │
         ┌──────▼──────┐      ┌──────────────────┐
         │Evento RH    │─────▶│  Presença (Foto) │
         │Alocação     │      │  + Geolocalização│
         └─────────────┘      └──────────────────┘
                │
         ┌──────▼──────┐
         │  Fechamento │
         │  (Financeiro)│
         └─────────────┘
```

### 3.2 Módulos e suas Views

| Módulo | Controller | Views | JS |
|---|---|---|---|
| Auth | `AuthController` | `auth/login.php` | — |
| Usuarios | `UsuarioController` | `usuario/` (3) | — |
| Clientes | `ClienteController` | `cliente/` (3) | — |
| Demandantes | `DemandanteController` | `demandante/` (3) | — |
| Fornecedores | `FornecedorController` | `fornecedor/` (3) | — |
| Colaboradores | `ColaboradorController` | `colaborador/` (3) | — |
| Produtores | `ProdutorController` | `produtor/` (3) | — |
| Planilhas | `PlanilhaController` | `planilha/` (3) | — |
| Estoque | `ProdutoController` | `estoque/` (5) | — |
| Eventos | `EventoController` | `evento/` (5) + `partials/` (10) | 8 módulos em `js/eventos/` |
| Contas Pagar | `ContasPagarController` | `contas_pagar/` (3) | — |
| Configuracoes | `ConfiguracoesController` | `configuracoes/` (1) | — |
| Dashboard | `DashboardController` | `dashboard/` (3) | — |
| AI | `AIController` | `ai/` (4) | — |
| Presenca | `PresencaPublicController` | `presenca/` (1, falta erro.php) | — |
| Verificacao | `VerificacaoController` | `verificacao/` (3) | — |

### 3.3 Serviços Compartilhados

| Serviço | Usado por | Função |
|---|---|---|
| `WhatsAppService` | ColaboradorController, ContasPagarController, ConfiguracoesController | Envio de mensagens + status |
| `ColaboradorVerificacaoService` | ColaboradorController, VerificacaoController, PublicoController | Geração de token + envio link |
| `UploadService` | ContasPagarController, CotacaoController | Upload seguro com validação MIME |
| `EmailService` | CotacaoController | Envio de email via Mailjet + SMTP |
| `ImapService` | CronImapController | Processamento de emails recebidos |
| `PdfGeneratorService` | EventoController | Geração de PDF (mPDF) |
| `EventoColaboradorService` | EventoRHController, FechamentoController, PresencaPublicController | Alocação + presença |
| `PermissaoService` | ConfiguracoesController | Matriz de permissões |
| `BaseService` | Todos os services (herança) | findOrFail, sanitize, validateRequired |
| `BaseRepository` | Todos os repositories (herança) | CRUD genérico, paginate, fill |

---

## 4. DEPENDÊNCIAS CRÍTICAS

### 4.1 Externas

| Dependência | Tipo | Risco |
|---|---|---|
| `whatsapp-bot` (201.23.68.17:3000) | Serviço Node.js externo | **ALTO** — Se cair, envio de links de verificação e comprovantes falha |
| Mailjet API | Serviço cloud | **ALTO** — Se cair, cotações não são enviadas |
| IMAP (mail.sisloc.online) | Serviço de email | **MÉDIO** — Se cair, respostas de cotação não são processadas |
| SMTP (mail.sisloc.online) | Serviço de email | **MÉDIO** — Fallback quando Mailjet falha |
| ViaCEP API | API pública | **BAIXO** — Só preenchimento de CEP em formulários |
| Google Fonts | CDN | **BAIXO** — UI sem fonte customizada ainda funciona |
| mPDF | Biblioteca PHP | **MÉDIO** — Geração de PDF trava sem ela |

### 4.2 Entre Módulos

| Módulo A | depende de | Módulo B | Tipo |
|---|---|---|---|
| Eventos | → | Clientes | FK `id_cliente` |
| Eventos | → | Demandantes | FK `id_demandante` |
| Eventos | → | Produtores | FK `id_produtor` |
| Produtos Evento | → | Planilhas | FK `id_planilha` |
| Produtos Evento | → | Salas | FK + locação |
| Montagem | → | Seriais Produto | FK `id_serial` |
| Montagem | → | Salas | FK `id_sala` |
| Devolução | → | Montagem | FK `id_serial + id_evento` |
| Cotação | → | Fornecedores | FK + envio email |
| Cotação | → | Produtos Evento | FK `id_produto_evento` |
| Contas Pagar | → | Fornecedores (ou Colaboradores) | FK + tipo |
| Evento RH | → | Colaboradores | FK `id_colaborador` |
| Colaboradores | → | WhatsAppService | Envio de link |
| Contas Pagar | → | WhatsAppService | Envio de comprovante |

### 4.3 Dependências Perigosas (alto acoplamento)

1. **EventoController (1168 linhas)**: Conhecido N+1 bug (linhas 164-165). Editar requer mexer em 12+ services. Separar em sub-controllers é recomendado mas arriscado.

2. **WhatsAppService (usado em 3 controllers)**: Se a assinatura `sendMessage()` mudar, quebra ColaboradorVerificacaoService, ContasPagarController e ConfiguracoesController.

3. **DashboardController**: 15+ queries por página, mistura serviço com query direta, método privado duplicado. Refatorar pode quebrar estatísticas.

4. **Application::view() (linha 433)**: Usa `extract($data)` dentro de `ob_start` — qualquer colisão de nome de variável produz bug silencioso.

5. **RendererTable/Component**: Views que usam `renderTable()` com `actionBtns` dependem do `DOMContentLoaded` JS para atribuir handlers. Mudar a estrutura do HTML gerado quebra a interatividade.

---

## 5. FLUXOS CRÍTICOS

### 5.1 Login

```
GET /auth/login → AuthController::login() → render form
POST /auth/login → AuthController::doLogin()
  → Rate limit check (archivo storage/.login_lock_*)
  → Csrf::validate()
  → Rbac::login(email, password)
    → UserRepository::findByEmail()
    → password_verify() (bcrypt)
    → Session::set('user', $user)
  → Role-based redirect (/dashboard/{role})
```

**Regras:** max 5 tentativas, lockout 15min, 3 roles, status=0 bloqueia login.

### 5.2 CRUD Padrão (ex: Cliente)

```
GET  /clientes       → index()  → all() → renderTable()
GET  /clientes/create → create() → render form
POST /clientes/store  → store() → CSRF → sanitize() → validate() → create() → redirect
GET  /clientes/edit/1  → edit(1) → find(1) → render form
POST /clientes/update/1 → update(1) → CSRF → sanitize() → validate() → update(1) → redirect
POST /clientes/delete/1 → delete(1) → CSRF → delete(1) → JSON
POST /clientes/toggle/1 → toggle(1) → CSRF → toggleStatus(1) → JSON
```

**Sanitização:** `BaseService::sanitize()` faz trim. Services filhos fazem `preg_replace('/\D/', '', $telefone)`.

### 5.3 Evento → Cotação → Fechamento

```
CRIAR EVENTO (estado=O, orçamento)
  → Adicionar salas
  → Adicionar produtos_evento por sala (com qtd, valor)
  → CONVERTER (O → L) — vira locação
  → Solicitar COTAÇÃO para fornecedores (via email)
    → Mailjet envia email com [COT-{id}-EVT-{id}]
    → Fornecedor responde → IMAP processa → salva em item_cotacoes_mensagens
    → Marcar vencedor → atualiza custo_unit
  → MONTAR (inserir seriais por sala)
  → DEVOLVER (após evento, registrar devolução de seriais)
  → FECHAR (status_locacao = F)
    → Alocar colaboradores no RH
    → Registrar presença (foto + geolocalização)
    → Gerar contas a pagar (colaboradores + fornecedores)
    → Enviar comprovantes via WhatsApp
```

### 5.4 Ciclo de Serial

```
ATIVO (estoque) → montagem pendente → montagem montado (em sala)
  → devolução A (OK) → ATIVO (volta ao estoque)
  → devolução P (pendência/avaria) → S (solucionado) → ATIVO
```

### 5.5 Verificação de Colaborador via WhatsApp

```
Admin clica "Enviar Link"
  → ColaboradorVerificacaoService::gerarToken(id) → token 64 hex
  → ColaboradorVerificacaoService::enviarLinkWhatsapp(id, token)
    → POST /sessions/{sessionId}/send-message
    → "Olá {nome}! Clique no link: {URL}/colaboradores/verificar/{token}"
  → Colaborador abre link → tira foto + geolocalização
  → VerificacaoController::processarVerificacao(token)
    → status=verificado → ativo=1
```

---

## 6. REGRAS DE NEGÓCIO ESSENCIAIS

### 6.1 Eventos
- Estado: `O` (orçamento) → `L` (locação) → `F` (finalizado). **Irreversível**.
- `total_item = qtd × valor_unit × dias`
- Converter e finalizar são one-way (não tem "voltar atrás")

### 6.2 RBAC
- **administrador**: tudo (usuários, configurações, AI)
- **produtor**: clientes, eventos, cotação, fechamento, contas, planilhas
- **estoquista**: estoque, montagem, devolução **apenas**
- Verificação no array `Rbac::$permissions` (hardcoded) — banco só para UI

### 6.3 Cotação
- Um vencedor por `produto_evento` (desmarca anterior)
- Email segue formato `[COT-{id}-EVT-{id}]`
- CCO sempre para o produtor do evento
- WhatsApp de vencedor é **NO-OP** (código comentado — nunca envia)

### 6.4 Colaboradores
- `ativo = 0` no cadastro → só ativa após verificação via WhatsApp
- Verificação requer: token único + foto + geolocalização
- Duas tentativas de alocação em evento: `UNIQUE(id_evento, id_colaborador)`

### 6.5 Fechamento
- Duplicidade: `contaPagarExists(tipo, referencia_id)` prevê pagamento duplicado
- Parcelamento: cria entrada + N parcelas, valida entrada ≤ total

### 6.6 Tema
- 21 temas whitelistados (TemaService + ConfiguracoesController)
- Persistência: `storage/theme.json` (override) → `config/app.php` (fallback) → `default`

---

## 7. BANCO DE DADOS

### 7.1 Overview (34 tabelas)

| Grupo | Tabelas |
|---|---|
| Core | `users`, `empresa`, `modulos`, `permissoes`, `role_permissoes` |
| AI | `ai_learning`, `ai_metrics`, `ai_executions`, `ai_corrections`, `ai_feedbacks`, `ai_interactions`, `ai_preferences`, `ai_learning_sessions` |
| Clientes | `clientes`, `demandantes` |
| Produtos | `produtos`, `seriaisproduto`, `secao`, `planilhas`, `unidademedida`, `categorias`, `subcategorias` |
| Eventos | `eventos`, `salas`, `categorias_sala`, `produtos_evento`, `montagens`, `devolucoes`, `evento_fotos` |
| Pessoas | `colaboradores`, `colaboradores_verificacao`, `evento_colaboradores`, `evento_colaborador_presencas`, `produtores` |
| Procurement | `fornecedores`, `item_cotacoes`, `item_cotacoes_parcelas`, `item_cotacoes_mensagens`, `item_cotacoes_anexos` |
| Financeiro | `contas_pagar` |
| API | `api_keys`, `api_request_logs` |

### 7.2 Relacionamentos Principais

```
clientes ─┬─ demandantes (id_cliente)
          └─ eventos (id_cliente)

eventos ─┬─ salas (id_evento)
         ├─ produtos_evento (id_evento) ── planilhas (id_planilha)
         ├─ montagens (id_evento) ── seriaisproduto (id_serial)
         ├─ devolucoes (id_evento) ── seriaisproduto (id_serial)
         ├─ evento_colaboradores (id_evento) ── colaboradores (id_colaborador)
         │   └─ evento_colaborador_presencas (id_alocacao)
         ├─ item_cotacoes (id_evento) ── fornecedores (id_fornecedor)
         │   └─ item_cotacoes_mensagens ── item_cotacoes_anexos
         └─ contas_pagar (evento_id)

fornecedores ── categorias (id_categoria) ── subcategorias (id_subcategoria)
```

### 7.3 Queries Pesadas (Performance)

| Local | Query | Problema |
|---|---|---|
| `BaseRepository::all()` | `SELECT * FROM {table}` | **Sem LIMIT** — todas as linhas |
| `EventoController:164` | `findBySala()` em loop | **N+1** — 1 query por sala |
| `DashboardController` | 15+ queries separadas | **Sem cache** — toda página |
| `ContasPagarService::search()` | Filtros com JOINs | Pode ser lento em milhares de registros |
| `RelatorioEstoqueService` | JOINs múltiplos sem índice | Potencialmente pesado |

---

## 8. PADRÕES E CONVENÇÕES

### 8.1 Código PHP

- **Namespace**: `App\` (PSR-4)
- **Controller**: `App\Controllers\<Nome>Controller extends Controller`
- **Service**: `App\Service\<Nome>Service extends BaseService`
- **Repository**: `App\Repository\<Nome>Repository extends BaseRepository`
- **Middleware**: `App\Http\Middleware\<Nome>Middleware implements MiddlewareInterface`
- **View**: `views/<pasta>/<nome>.php`

### 8.2 Estrutura de View

```php
<?php require dirname(__DIR__) . '/layout/header.php'; ?>
<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
?>
    <section class="section active" id="sec-modulo">
      <div class="section-header">...</div>
      <div class="divider"></div>
      <div class="card">
        <div class="card-head">
          <span class="card-title">...</span>
          <a href="..." class="btn btn-sm btn-cyan">Novo</a>
        </div>
        <div class="card-body" style="padding:0">
          <?= renderTable([...]) ?>
        </div>
      </div>
    </section>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
```

**NUNCA** incluir header/sidebar/topbar separadamente — o `header.php` já faz tudo.

### 8.3 Padrão Controller

- `__construct($request)` → `parent::__construct($request)` + instanciar services
- `$baseUrl = rtrim(Env::get('BASE_URL', ''), '/')`
- Toda action pública verifica `Rbac::check()` primeiro
- `store/update` validam CSRF primeiro, depois processam
- `delete/toggle` retornam JSON
- Usar `$this->get()` e `$this->post()` em vez de `$_GET/$_POST`

### 8.4 Padrão de Tabela

- Usar `renderTable()` com `searchable => true`, `paginated => true`
- `$actionBtns = renderTableActions('default')` **antes** do foreach
- `rows[]` com `'data-id' => $item['id']` como primeiro elemento
- Status sempre como botão toggle clicável (nunca badge estático)

### 8.5 Padrão JS

- Módulos de evento: IIFE pattern `(function() { 'use strict'; ... })()`
- NUNCA usar `alert()` ou `confirm()` sem tratamento — usar `showToast()`
- Funções de CRUD: `editModulo(id)`, `deleteModulo(id)`, `toggleModulo(id, btn)`
- Handlers de botões de ação via `DOMContentLoaded`

### 8.6 Componentes do Design System

| Função | Uso |
|---|---|
| `renderTable()` | Listagens com busca + paginação |
| `renderInput()` | Campos de formulário (NUNCA HTML inline) |
| `renderButton()` | Apenas actions especiais (form usa `<button>` direto) |
| `renderModal()` | Diálogos, confirmações |
| `renderBadge()` | Status, tags |
| `renderCard()` | Containers |
| `renderCardStat()` | KPIs |
| `renderAlert()` | Erros de validação (só em create/edit) |
| `renderTabs*()` | Abas (NUNCA HTML manual de tabs) |
| `showToast()` | Feedback ao usuário |

---

## 9. FRONTEND

### 9.1 CSS

| Arquivo | Linhas | Função |
|---|---|---|
| `public/css/styles.css` | 1336 | CSS principal do sistema |
| `docs/layout/branco/styles.css` | 1299 | Design System "White Rabbit" |

**Variáveis CSS (design tokens):**
- `--bg-*`: darkest, dark, surface, card, elevated, hover, border, border-sub
- `--text-*`: 1 (preto) a 4 (cinza claro)
- `--neon-*`: 7 cores (red, orange, yellow, green, cyan, blue, purple) + glow
- `--z-*`: base (1) a toast (5000)
- `--shadow-*`: sm, md, lg, xl
- `--sb-*`: sidebar widths (260px/64px), topbar height (60px)

**Responsivo:** Desktop-first, breakpoints em 1440/1366/1280/1024/768/640/480px.

### 9.2 JS Global

| Função | Descrição |
|---|---|
| `showToast(type, title, msg, duration)` | Notificação toast |
| `openModal(id)` / `closeModal(id)` | Controle de modal |
| `toggleSidebar()` | Colapsar sidebar |
| `togglePop(id)` | Dropdown/popover |
| `switchTab(group, index)` | Troca de abas |
| `tblSearch(id, term)` / `tblSetPerPage()` / `tblGoToPage()` | Paginação client-side |
| `sortTable(colIdx, btn)` | Ordenação por coluna |
| `stepNext()` / `stepPrev()` | Stepper |
| `apiFetch(url, options)` | Fetch wrapper com CSRF + timeout + toast |
| `setRating()` / `hoverRating()` / `leaveRating()` | Rating widget |
| `toggleChipSel()` / `removeChip()` | Chip component |
| `initProgressBars()` | Animação de progresso |

### 9.3 Módulos JS de Evento

| Módulo | Tamanho | Função |
|---|---|---|
| `montar-os.js` | 880 linhas | Montagem de seriais em salas |
| `salas-produtos.js` | 1048 linhas | CRUD de produtos por sala (AJAX puro) |
| `rh.js` | 538 linhas | Alocação de colaboradores + presença |
| `fechamento.js` | 1117 linhas | Fechamento financeiro |
| `devolver-os.js` | 26 linhas | Devolução (minimal) |
| `cotacao.js` | 440 linhas | Marcação de vencedor + propostas |
| `dados-evento.js` | — | Dados gerais do evento |
| `fornecedores.js` | — | Fornecedores do evento |
| `tabs.js` | — | Navegação por abas do evento |

---

## 10. SEGURANÇA

### 10.1 CSRF
- Token por sessão via `Csrf::getToken()` / `Csrf::validate()`
- Middleware `CsrfMiddleware` valida em POST/PUT/DELETE/PATCH
- Token enviado em campo oculto `_csrf_token` (alguns usam `csrf_token`)

### 10.2 Autenticação
- Sessão PHP com `cookie_httponly=1`, `use_strict_mode=1`, `sid_length=48`
- Senha bcrypt via `password_hash()` / `password_verify()`
- Rate limit por IP (arquivo em `storage/`): 5 tentativas, 15min lockout
- API v1: `x-api-key` header → SHA256 → consulta `api_keys` → IP whitelist → rate limit

### 10.3 Upload
- `UploadService` valida MIME via `finfo` (não confia no client)
- Extensões whitelistadas (pdf, doc, docx, jpg, jpeg, png, xls, xlsx, txt)
- Tamanho máximo configurável (default 5MB)
- Nome sanitizado: `uniqid() + '_' + bin2hex(random_bytes(8)) + ext`

### 10.4 XSS
- `htmlspecialchars()` usado na maioria dos outputs, mas inconsistente
- Algumas views têm `$_GET` direto sem escape
- Tema (`theme.json`) pode conter XSS se admin malicioso escrever nele

### 10.5 SQL Injection
- Repositories usam prepared statements (PDO) — seguro
- Services que usam `Connection::query()` com parâmetros — seguro
- DashboardController tem raw SQL com concatenação — **risco**

### 10.6 API v1 para App Android (2026-05-19)

Autenticação por **API Key** (`X-Api-Key` header), sem sessão PHP, sem CSRF. Criada para integração com app móvel.

| Item | Valor |
|------|-------|
| **API Key** | `sisloc_android_app_prod_2026@#` (hash: SHA2 na tabela `api_keys`) |
| **Header** | `X-Api-Key: sisloc_android_app_prod_2026@#` |
| **Middlewares** | `ApiKeyMiddleware` (valida hash → IP whitelist → rate limit) + `AuthMiddleware` opcional para login |
| **Permissões API** | `auth:login`, `auth:read`, `eventos:read`, `estoque:write`, `*`, `*:read` |
| **Base URL** | `https://profox.sisloc.online/public` |

#### Controllers criados

| Controller | Arquivo |
|------------|---------|
| `App\Controllers\Api\AuthApiController` | `src/Controllers/Api/AuthApiController.php` |
| `App\Controllers\Api\EventoApiController` | `src/Controllers/Api/EventoApiController.php` |

#### Novo método storeBatchApi() no SerialProdutoController

| Item | Valor |
|------|-------|
| **Arquivo** | `src/Controllers/SerialProdutoController.php:203` |
| **Rota** | `POST /api/v1/seriais/store-batch` |
| **Cookie gerado** | `PHPSESSID` (após login via `/auth/login`) |

#### Endpoints da API v1 — Grupo específico para Android

| Método | Rota | Controller::Action | Descrição |
|--------|------|-------------------|-----------|
| `POST` | `/api/v1/auth/login` | `AuthApiController::login` | Login email+senha, cria sessão PHP + cookie PHPSESSID |
| `GET` | `/api/v1/auth/session` | `AuthApiController::session` | Verifica se sessão atual está válida |
| `POST` | `/api/v1/auth/logout` | `AuthApiController::logout` | Encerra sessão |
| `GET` | `/api/v1/eventos/ativos` | `EventoApiController::ativos` | Lista eventos com `status=1` (ativos) |
| `GET` | `/api/v1/eventos` | `EventoApiController::ativos` | Alias de `/ativos` |
| `POST` | `/api/v1/seriais/store-batch` | `SerialProdutoController::storeBatchApi` | Cadastra lote de seriais no estoque |

#### Fluxo de autenticação do Android

```
POST /api/v1/auth/login  → { email, password }
  ← { success, user, role, PHPSESSID no cookie }
       │
       ▼
Shopping cart armazena PHPSESSID + envia em todas as requisições subsequentes
       │
       ▼
Sistema reconhece usuário por $_SESSION['user']
```

#### Cadastro de seriais em lote (Android só precisa enviar o lote)

```
POST /api/v1/seriais/store-batch
  Headers: X-Api-Key: ..., Cookie: PHPSESSID=... (se estiver logado)
  Body form-encoded:
    id_produto: 123
    seriais: SN001\nSN002\nSN003
  ← { success, message, data: { success, duplicates, errors, seriais[] } }

O lote é cadastrado no banco com status='ATIVO' diretamente.
NÃO é enviado para salas — isso é tarefa exclusiva do SisLoc web (MontagemController).
```

#### Rota de logout do WhatsApp

```
POST /api/v1/seriais/store-batch
  Body form-encoded:
    id_produto: 123
    seriais: SN001
```


---

## 11. PROBLEMAS ENCONTRADOS

### 11.1 CRÍTICOS

| # | Problema | Local | Impacto |
|---|---|---|---|
| C1 | **import-db.php web-acessível** | `public/import-db.php` | Qualquer um pode importar SQL no DB |
| C2 | **Autoload fix web-acessível** | `public/_fix.php`, `_fix2.php`, `fix-autoload.php` | Execução remota de código |
| C3 | **Senhas em plaintext no .env** | `.env` (DB, FTP, SMTP, IMAP) | Vazamento de credenciais |
| C4 | **View faltando `views/presenca/erro.php`** | `PresencaPublicController:30` | Erro 500 quando token de presença é inválido |

### 11.2 ALTOS

| # | Problema | Local | Impacto |
|---|---|---|---|
| A1 | **N+1 em EventoController** | `EventoController.php:164-165` | Slow page load em eventos com muitas salas |
| A2 | **CORS wildcard com credentials** | `public/index.php:9-13` | Vulnerabilidade de CSRF cross-origin |
| A3 | **Debug route expõe $_SESSION** | `public/debug-route.php:46` | Vazamento de sessão |
| A4 | **Logo upload sem validação** | `ConfiguracoesController.php:129` | Upload de arquivo arbitrário |
| A5 | **$_GET direto em views** | `views/test/diagnostic.php:28`, `views/estoque/*.php` | XSS |
| A6 | **Unbounded SELECT * (sem LIMIT)** | `BaseRepository.php:15`, vários repos | Performance degrada com dados |
| A7 | **FechamentoController bypassa repo** | `FechamentoController.php` | SQL direto misturado com lógica |
| A8 | **session_id sisloc_novo não existe no bot** | `.env:55` | Envio WhatsApp não funciona (sessão não conectada) |

### 11.3 MÉDIOS

| # | Problema | Local | Impacto |
|---|---|---|---|
| M1 | **Debug::render() nunca chamado** | `Application.php:34` | Overhead desnecessário |
| M2 | **E_DEPRECATED logado como ERROR** | `ErrorHandler.php:56` | Polui logs (470KB num dia) |
| M3 | **Tema sem escape no header** | `views/layout/header.php:43-53` | Stored XSS se admin modificar tema |
| M4 | **WhatsApp winner notification é NO-OP** | `CotacaoService.php` | Funcionalidade prometida não implementada |
| M5 | **Form data sem escape em retry** | `views/colaborador/create.php` | XSS em dados rejeitados |
| M6 | **Inconsistência require vs require_once** | `views/dashboard/*.php` | Pode causar double-include |

### 11.4 BAIXOS

| # | Problema | Local |
|---|---|---|
| B1 | _clearcache.php web-acessível | `public/_clearcache.php` |
| B2 | Botão "Verificar Agora" no WhatsApp não implementado | `ConfiguracoesController` |
| B3 | DashboardController com método duplicado | `linhas 296-297` |
| B4 | External IP scan para phpinfo.php | `storage/logs/` |

---

## 12. CORREÇÕES APLICADAS EM 2026-05-20

### 12.1 Bugs Críticos Corrigidos

| # | Problema | Arquivo | Linha | Correção |
|---|---|---|---|---|
| C1 | **Foto de colaborador não salva** | `ColaboradorController.php` | 88, 152 | Adicionado `'foto' => $this->post('foto')` no `store()` e `update()` — campo era enviado pelo form mas descartado pelo controller |
| C2 | **Referência a coluna inexistente `status`** | `ContasPagarController.php` | 86 | Trocado `WHERE status = 1` por `WHERE ativo = 1` na tabela `colaboradores` (coluna real é `ativo`) |
| C3 | **CSRF quebra requisições AJAX JSON** | `CsrfMiddleware.php` | 29-31 | Adicionado fallback para `$request->input('_csrf_token')` quando `Content-Type: application/json` — middleware só lia de `$_POST` |
| C4 | **Vazamento de stack trace em produção** | `ContasPagarController.php` | 379 | Removido `$e->getTraceAsString()` da resposta JSON — expunha caminhos internos do servidor |
| C5 | **Logger sem fallback no ErrorHandler** | `ErrorHandler.php` | 56-59, 88-93, 110-113 | Envolvido `Logger::error()` em try/catch com fallback para `error_log()` — prevenia crash recursivo se Logger falhasse |
| C6 | **updateStatus() sobrescrevia motivo** | `SerialProdutoRepository.php` | 40-44 | Só atualiza coluna `motivo` quando valor não vazio — antes limpava motivo ao trocar status |
| C7 | **Erros ocultos em produção** | `ErrorHandler.php` | 15, 65-69, 103-107, 127-135 | Removido bloqueio de `display_errors` e condicionais `APP_ENV=local` — erros agora aparecem na tela em qualquer ambiente |

### 12.2 Causa Raiz de Cada Bug

| Bug | Causa Raiz | Lição |
|---|---|---|
| C1 | Controller omitiu campo do array `$data` | **Sempre verificar se todos os campos do formulário estão no array de dados do controller, comparando com `$fillable` do repository** |
| C2 | Nome de coluna hardcoded errado | **Sempre executar `DESCRIBE tabela` antes de escrever queries SQL — nomes de colunas `status`/`ativo` são fáceis de confundir entre tabelas** |
| C3 | Middleware não considerava JSON body | **Middleware que lê dados POST precisa tratar `php://input` para JSON, pois `$_POST` só é populado para form-urlencoded/multipart** |
| C4 | Stack trace incluído em resposta de erro | **Nunca incluir `getTraceAsString()` em respostas de produção — usar apenas mensagem amigável + log interno** |
| C5 | Logger chamado sem proteção | **TODO callback de error/exception handler precisa de try/catch pois o Logger pode não estar carregado durante bootstrap** |
| C6 | SQL sempre setava `motivo` | **SQL de update condicional: só alterar colunas que o usuário realmente mudou** |
| C7 | Debug propositalmente desligado em produção | **Em fase de desenvolvimento ativo, manter `display_errors=1` + debug page — desligar apenas quando sistema estiver estável** |

---

## 13. MEMÓRIA OPERACIONAL PARA IA

### 12.1 O que NÃO pode ser alterado sem validação

1. **`Rbac::$permissions` array** — qualquer alteração nas permissões quebra o acesso. Adicionar novo módulo = adicionar no array.
2. **`Application::view()`** — usa `extract($data)`. Mudar o mecanismo quebra todas as views.
3. **`header.php`/`sidebar.php`/`footer.php`** — todas as views dependem. Alterar estrutura = quebrar 50+ arquivos.
4. **`Controller::post()`** — detecta JSON vs form. Mudar = quebrar todos os controllers.
5. **Rotas em `public/index.php`** — nome de grupo "eventos" já usado em 8 arquivos JS. Renomear = quebrar JS.
6. **`renderTable()` output HTML** — classes `tbl-*`, `td-actions`, `data-id` são referenciadas por JS. Mudar = quebrar interatividade.
7. **Componentes do Design System** — 24 views dependem deles. Sempre verificar `registry.json`.

### 12.2 Ordem Correta de Alterações

**Para adicionar novo módulo CRUD:**
1. Migration no banco
2. Repository (extends BaseRepository) + `$fillable`
3. Service (extends BaseService) + métodos `search/create/update/delete/toggleStatus`
4. Controller (extends Controller) + 7 actions: index, create, store, edit, update, delete, toggle
5. Views: index.php (tabela), create.php, edit.php
6. Rotas em `public/index.php` (grupo com Auth+Csrf)
7. Permissões no array `Rbac.php`
8. Sidebar (se aplicável)
9. Testar + verificar dead code

**Para corrigir bug:**
1. Ler a memória do sistema primeiro
2. Verificar impacto do arquivo
3. Rastrear dependências (quem mais usa este arquivo?)
4. Mudança mínima possível
5. Testar módulo afetado + módulos dependentes

### 12.3 Arquivos que SEMPRE precisam ser lidos antes de alterar

| Se for alterar... | Leia primeiro... |
|---|---|
| Qualquer controller | `src/Http/Controller.php`, rotas em `index.php` |
| Qualquer view | `views/layout/header.php`, components usados |
| `renderTable()` | `public/js/scripts.js` (tblSearch, tblGoToPage) |
| Componente PHP | `docs/layout/branco/assets/registry.json` |
| Rbac | `src/Auth/Rbac.php` + `src/Service/PermissaoService.php` |
| WhatsApp | `src/Service/WhatsAppService.php` + `.env` |
| Evento | `src/Controllers/EventoController.php` + `public/js/eventos/*.js` |

### 12.4 Dependências Perigosas

| Altere isto... | Quebra isto... |
|---|---|
| `view()` retorno | Todos os controllers (35) |
| `Controller::post()` | Todos os controllers que recebem POST |
| `Connection::get()` | Todos os repositories (31) |
| `renderTable()` signature | Todas as views que usam tabela (15+) |
| `showToast()` signature | Todos os JS modules |
| `BaseService::sanitize()` | Todos os services que herdam (38) |
| Nome de grupo de rota | JS que usa `BASE_URL + /grupo/...` |

### 12.5 Regras Obrigatórias

1. **NUNCA** usar `$_GET`/`$_POST`/`$_FILES` diretamente em views — sempre pelo controller
2. **SEMPRE** usar `htmlspecialchars()` ao exibir dados não confiáveis
3. **SEMPRE** validar CSRF em POST/PUT/DELETE
4. **SEMPRE** verificar RBAC em toda action
5. **NUNCA** fazer query direta no controller — usar service → repository
6. **NUNCA** usar `renderButton()` em forms — usar `<button>` direto
7. **SEMPRE** usar `showToast()` para feedback — nunca `alert()`
8. **SEMPRE** usar `type="button"` em botões que não são submit
9. **NUNCA** escrever HTML de tabs manualmente — usar `renderTabs*()`
10. **SEMPRE** verificar `registry.json` antes de criar novo componente

---

## 13. RELATÓRIO FINAL

### 13.1 Visão Geral

| Aspecto | Valor |
|---|---|
| Linhas de código PHP | ~15.000 (src/) |
| Views | ~50+ em 19 diretórios |
| JS | ~4.000+ linhas (8 módulos evento + global) |
| CSS | ~2.600 linhas (2 arquivos) |
| Tabelas | 34 |
| Rotas | 130+ |
| Controllers | 35 |
| Services | 38 |
| Repositories | 31 |
| Componentes UI | 24 |
| Temas | 21 |
| Perfis RBAC | 3 |

### 13.2 Complexidade Técnica

| Módulo | Complexidade | Acoplamento | Risco de alteração |
|---|---|---|---|
| Eventos | **Muito Alta** (1168 linhas) | **Muito Alto** (12+ services) | **ALTO** |
| Cotação | **Alta** (email + IMAP + WhatsApp) | **Alto** (4 services externos) | **ALTO** |
| Fechamento | **Alta** (financeiro + parcelas + fotos) | **Alto** (EventoController) | **MÉDIO** |
| Montagem/Devolução | **Média** (AJAX, transações) | **Médio** (seriais) | **MÉDIO** |
| Dashboard | **Média** (15+ queries) | **Médio** (todos os repositórios) | **BAIXO** |
| CRUDs simples | **Baixa** (padrão repetitivo) | **Baixo** | **BAIXO** |
| Configurações | **Baixa** | **Médio** (WhatsApp) | **BAIXO** |
| Auth | **Baixa** | **Baixo** | **CRÍTICO** (se quebrar, ninguém entra) |

### 13.3 Prioridade de Correções

| Prioridade | Problema | Esforço estimado |
|---|---|---|
| **1. CRÍTICA** | Remover web-acessíveis `_fix.php`, `import-db.php`, `fix-autoload.php` | 5 min |
| **2. CRÍTICA** | Criar `views/presenca/erro.php` | 10 min |
| **3. ALTA** | Corrigir N+1 em EventoController (usar `groupBySala()`) | 30 min |
| **4. ALTA** | Remover CORS wildcard ou fixar origem | 5 min |
| **5. ALTA** | Criar sessão `sisloc_novo` no WhatsApp bot ou trocar para `novoframework` | 15 min |
| **6. MÉDIA** | Remover `_clearcache.php`, `debug-route.php` do acesso web | 5 min |
| **7. MÉDIA** | Adicionar LIMIT em `BaseRepository::all()` | 10 min |
| **8. MÉDIA** | Separar EventoController em sub-controllers | 4-8h |
| **9. MÉDIA** | Mover queries do DashboardController para services | 2h |
| **10. MÉDIA** | Adicionar MIME validation em ConfiguracoesController (logo) | 15 min |
| **11. BAIXA** | Chamar `Debug::render()` no footer ou remover inicialização | 10 min |
| **12. BAIXA** | Implementar WhatsApp winner notification (atualmente NO-OP) | 30 min |

### 13.4 Refatorações Futuras Recomendadas

| Projeto | Benefício | Risco | Esforço |
|---|---|---|---|
| Separar EventoController (1168 linhas → 5-6 controllers) | Manutenibilidade | Médio (mexer em rotas) | 1-2 dias |
| Mover queries para repository pattern (Dashboard, Fechamento) | Consistência | Baixo | 4-6h |
| Criar trait de CSRF/RBAC automática | Reduz boilerplate em 35 controllers | Baixo | 2h |
| Adicionar paginação server-side em todas as listagens | Performance com dados reais | Médio (mexer em views) | 1-2 dias |
| Unificar CSS (2 arquivos → 1) | Performance de carga | Baixo | 1h |
| Migrar JS para módulos ES6 | Organização | Baixo | 2-3h |
| Adicionar testes automatizados | Qualidade | Baixo | 3-5 dias |
| Cache de dashboard (15+ queries → 1 cache hit) | Performance | Baixo | 2h |

### 13.5 Análise do Erro Reportado (Stack Trace)

```
Error: #0 Application.php(433): include()
        #1 Controller.php(27): Application->view()
        #2 ContasPagarController.php(62): Controller->view()
```

**Causa provável:** O erro ocorre no sistema **`/var/www/html/sisloc/`** (não no `sisloc_novo`), que é uma instalação diferente/backup. No `sisloc_novo`, a view `views/contas_pagar/create.php` (linha 85) existe e carrega corretamente.

**Hipóteses para o erro na outra instalação:**
1. O `BASE_URL` está configurado para um path diferente e o `$viewFile` gerado em `Application::view()` aponta para diretório inexistente
2. O autoload do Composer está desatualizado (não rodou `composer dump-autoload`)
3. A view `create.php` foi adicionada em `sisloc_novo` mas não copiada para a outra instalação

**No `sisloc_novo` não há erro.** A view `views/contas_pagar/create.php` existe e é funcional.

---

## 14. REGRA: Custo de Produtos (campo `custo_unit`)

O campo **`custo_unit`** na tabela `produtos_evento` armazena o **valor TOTAL do custo** do item, não o unitário.

### Regras:
- O usuário preenche o custo total no campo "Custo Un." — esse valor JÁ representa o custo total daquele item
- **NUNCA multiplicar `custo_unit` por `qtd`** em cálculos (SQL, PHP ou JS)
- `total_item` (venda) = `qtd * valor_unit * dias` — esse sim é calculado com multiplicação
- `custo_unit` = valor direto, sem multiplicação

### Locais que precisam de verificação (todos usam `custo_unit` direto, sem `* qtd`):
1. `ProdutoEventoRepository::getTotaisEvento()` — `SUM(custo_unit)`
2. `ProdutoEventoRepository::groupBySala()` — `SUM(custo_unit)`
3. `views/evento/partials/edit-salas-produtos.php` — `$c` (não `$c * $q`)
4. `public/js/eventos/salas-produtos.js` — `custo += cus` (não `cus * qtd`)

---

## 15. REGRA: Mascaras `data-mask` e Busca CEP

### Mascaras disponíveis (JS puro, sem biblioteca):
| Máscara | Uso | Exemplo |
|---------|-----|---------|
| `(XX) XXXX-XXXX` | Telefone fixo | (61) 3214-5678 |
| `(XX) XXXXX-XXXX` | Celular | (61) 98219-8228 |
| `XXXXX-XXX` | CEP | 70680-100 |
| `XX.XXX.XXX/XXXX-XX` | CNPJ | 12.345.678/0001-90 |
| `XXX.XXX.XXX-XX` | CPF | 123.456.789-00 |

### Onde aplicar:
- **TODO formulário** com `telefone`: `data-mask="(XX) XXXX-XXXX"`
- **TODO formulário** com `cep`: `data-mask="XXXXX-XXX"` + busca ViaCEP no `blur`
- **SEMPRE** adicionar o script de `data-mask` e busca CEP tanto no `create.php` quanto no `edit.php`
- A busca CEP usa `fetch(BASE_URL + '/proxy/cep/' + cep)` via ProxyController (CSP do cPanel bloqueia chamada direta)

### Views que já seguem esse padrão:
- `views/usuario/create.php`, `views/usuario/edit.php`
- `views/cliente/create.php`, `views/cliente/edit.php`
- `views/fornecedor/create.php`, `views/fornecedor/edit.php`
- `views/colaborador/create.php`, `views/colaborador/edit.php` (corrigido em 2026-05-11)

---

## 16. REGRA: Variaveis no header.php

O `views/layout/header.php` NUNCA pode usar nomes de variaveis genericos que possam conflitar com variaveis passadas pelos controllers para as views filhas.

### Regras:
- **SEMPRE** prefixar variaveis locais do header com `$_` (ex: `$_empresa`, `$_db`, `$_stmt`, `$_empresaTitle`)
- **NUNCA** usar `$empresa`, `$db`, `$stmt` sem prefixo no header.php
- Qualquer view que recebe `$empresa` do controller (ex: configuracoes/index.php) tera os dados corrompidos se o header sobrescrever essa variavel

### Consequencia do erro:
Em `views/configuracoes/index.php`, o `$empresa` vindo do controller com 18 campos era substituido pelo `$empresa` do header com apenas 2 campos (`nome`, `identificador`), fazendo todos os campos do formulario aparecerem vazios.

---

## 17. REGRA: Redirect apos criar registro

### Regra:
Apos criar um registro via formulario, redirecionar para a tela de **edicao** (edit), nao para a listagem.

### Implementacao:
- O controller `store()` retorna `data.id` no JSON de sucesso
- O JS usa `window.location.href = BASE_URL + '/modulo/edit/' + data.id`
- Exemplo: `views/evento/create.php` linha 76: `/eventos/edit/` + data.id

---

## 18. INFRAESTRUTURA — Produção ProFox

### Ambientes

| Ambiente | Local | URL | Deploy |
|----------|-------|-----|--------|
| **Desenvolvimento** | `/var/www/html/novo_sisloc/` | `http://localhost/novo_sisloc/public` | Edição direta |
| **Produção ProFox** | FTP → `sisloc.online:/public_html/subdomains/profox/` | `https://profox.sisloc.online` | `rsync` ou FTP |

### Credenciais FTP Produção
```
Host: sisloc.online
Usuário: sisloc
Porta: 21
Path: /public_html/subdomains/profox
```
> As senhas estão no `.env` local. **NUNCA commit arquivos `.env`.**

### Fluxo de Deploy
1. Editar em `/var/www/html/novo_sisloc/` (local)
2. Executar `composer dump-autoload` se criou novas classes
3. Enviar via FTP/rsync para `sisloc.online:/public_html/subdomains/profox/`
4. **Importante:** Excluir `vendor/` e `.env` do upload (manter .env do servidor)

### URLs importantes
| Recurso | URL |
|---------|-----|
| Sistema | `https://profox.sisloc.online/public` |
| WhatsApp Bot | `http://201.23.68.17:3000` |
| Painel WHM/cPanel | `https://sisloc.online/cpanel` |

### Observações
- PHP 8.4 no servidor de produção
- LiteSpeed (LSAPI) — OPcache pode exigir refresh manual
- `UPLOAD_PATH=/public_html/subdomains/profox/storage/uploads`
- `BASE_URL=https://profox.sisloc.online/public`

---

## 19. PROTOCOLO DE DEBUG — Erro 500

### Regra Obrigatória
**ANTES de tentar corrigir qualquer erro 500, SEMPRE reproduzir e capturar a resposta real do servidor.** Nunca adivinhar a causa.

### Passo a passo

1. **Reproduzir no navegador** — abrir F12 > Network, ver body da resposta
2. **Ou testar via curl** — capturar headers e body completos:
   ```bash
   curl -v -X POST "https://profox.sisloc.online/public/rota/action" \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -H "X-Requested-With: XMLHttpRequest" \
     -d "_csrf_token=test&campo=valor"
   ```
3. **Verificar logs do PHP** — `storage/logs/` ou `error_log`
4. **Identificar tipo do erro**:
   - **Fatal** (ShutdownHandler) = erro de classe, sintaxe, OPcache, ou PHP 8.4 incompatibilidade
   - **Capturado** (try/catch) = erro de lógica, banco, validação — a mensagem está no JSON
5. **Verificar PHP 8.4 deprecations** — warnings antes do JSON quebram `r.json()` no frontend
6. **SEMPRE executar `php -l arquivo.php`** antes de deploy
7. **Verificar dependências** com `grep` — quem mais usa esta função/classe/arquivo?
8. **Após deploy** — testar endpoint via curl novamente para confirmar correção

### Erros comuns que parecem 500 mas não são
| Sintoma | Provável causa |
|---------|---------------|
| Deprecation warning antes do JSON | PHP 8.4 nullable parameter ou ini_set deprecado |
| Erro "500" no console mas toast mostra mensagem | Controller retornou 500 com JSON válido — não é erro fatal |
| `r.json()` falha no catch | Resposta é HTML (não JSON) — ErrorHandler ou fatal sem AJAX check |
| Único controller quebra | Conflito de assinatura de método com Controller pai |
| Tudo quebra | `Rbac.php` com `};` no lugar de `];`, ou Logger crash |

---

## 20. AUDIT 2026-05-20 — Estado do Sistema

### Módulos: 27 de 27 funcionais
- **130+ rotas**, 43 controllers, 38 services, 31 repositories, 72 views
- Todos os módulos seguem o padrão MVC → CRUD completo

### Correções deste audit
| # | Problema | Arquivo | Correção |
|---|----------|---------|----------|
| 1 | View `presenca/erro.php` inexistente — crash se token inválido | `views/presenca/erro.php` | Criada view de erro |
| 2 | `ProxyController` sem rotas — CEP/CNPJ quebrados | `public/index.php` | Rotas `/proxy/cnpj/` e `/proxy/cep/` adicionadas |
| 3 | Views de teste órfãs | `views/test/` | Removidas |

### Pendências conhecidas
| # | Problema | Impacto |
|---|----------|---------|
| P1 | Firewall bloqueia porta 3000 — WhatsApp não envia | Nenhuma mensagem da produção |
| P2 | `sendMedia()` e `listSessions()` sem uso | Código morto |
| P3 | `CronRHController` ignorado (rota usa closure) | Código morto |
| P4 | `storeBatchApi()` sem rota | Código morto |

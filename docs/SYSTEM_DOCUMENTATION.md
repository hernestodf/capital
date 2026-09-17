# SisLoc v3.0 - Documentação Técnica Completa

> **Gerado em:** 2026-04-27  
> **Framework:** ConectaFramework (evolução do NovoFramework)  
> **Propósito:** Sistema de Locação de Equipamentos para Eventos  
> **Versão:** 3.0.0

---

## Sumário

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Arquitetura e Estrutura](#2-arquitetura-e-estrutura)
3. [Banco de Dados](#3-banco-de-dados)
4. [Backend (PHP)](#4-backend-php)
5. [Frontend (JS + CSS)](#5-frontend-js--css)
6. [Fluxos do Sistema](#6-fluxos-do-sistema)
7. [Integrações Externas](#7-integrações-externas)
8. [RBAC - Controle de Acesso](#8-rbac---controle-de-acesso)
9. [Agentes e Automações](#9-agentes-e-automações)
10. [Problemas e Melhorias](#10-problemas-e-melhorias)
11. [Guia de Onboarding](#11-guia-de-onboarding)
12. [Mapa de Fluxos](#12-mapa-de-fluxos)
13. [Pontos Críticos](#13-pontos-críticos)

---

## 1. Visão Geral do Sistema

### 1.1 Propósito

O **SisLoc** é um sistema completo para gestão de **locação de equipamentos para eventos**. Ele permite:

- Cadastro de clientes e produtores
- Criação e gerenciamento de eventos (orçamentos e locações)
- Organização de salas e produtos por evento
- Controle de montagem e devolução de equipamentos
- Gestão de estoque com seriais
- Verificação de colaboradores via WhatsApp
- Geração de PDFs (orçamentos e relatórios)

### 1.2 Principais Módulos

| Módulo | Descrição | URL Base |
|--------|-----------|----------|
| **Autenticação** | Login/logout de usuários | `/auth` |
| **Dashboard** | Painéis por perfil (admin, produtor, estoquista) | `/dashboard` |
| **Clientes** | Gestão de clientes (pessoas jurídicas) | `/clientes` |
| **Demandantes** | Responsáveis locais vinculados a clientes | `/demandantes` |
| **Produtores** | Produtores de eventos vinculados a usuários | `/produtores` |
| **Eventos** | Orçamentos e locações de equipamentos | `/eventos` |
| **Salas** | Ambientes/salas dentro de eventos | `/salas` (API) |
| **Produtos-Evento** | Itens alocados em salas de eventos | `/produtos-evento` (API) |
| **Estoque** | Gestão de produtos e seriais | `/estoque` |
| **Montagem** | Controle de montagem/devolução de seriais | `/montagem` |
| **Fornecedores** | Fornecedores com categorias/subcategorias | `/fornecedores` |
| **Colaboradores** | Funcionários/freelancers com verificação | `/colaboradores` |
| **Planilhas** | Catálogo de produtos com valores | `/planilhas` |
| **Configurações** | Empresa, permissões, temas | `/configuracoes` |
| **AI Agents** | Sistema de agentes de IA (experimental) | `/ai` |

### 1.3 Como os Módulos se Conectam

```
CLIENTES ──┐
           ├──> EVENTOS ──┬──> SALAS ──> PRODUTOS-EVENTO
DEMANDANTES┘              │
                          ├──> MONTAGEM ──> SERIAIS-PRODUTO
PRODUTORES ───────────────┘               │
                                          └──> ESTOQUE
FORNECEDORES ──────────────────────────────┘

COLABORADORES ──> VERIFICACAO (WhatsApp)

USUARIOS ──> PRODUTORES (vinculo 1:1 opcional)
```

---

## 2. Arquitetura e Estrutura

### 2.1 Padrão Arquitetural

**MVC + Repository-Service + RBAC**

```
HTTP Request
    ↓
public/index.php (Entry Point)
    ↓
Application::boot() → Router::dispatch()
    ↓
AuthMiddleware (se protegido)
    ↓
Controller (recebe request, valida CSRF, chama service)
    ↓
Service (regras de negócio, validação, sanitização)
    ↓
Repository (acesso a dados, queries SQL)
    ↓
Connection (PDO singleton)
    ↓
MySQL Database
    ↓
Response::html(view) ou Response::json(data)
    ↓
View (header.php + conteúdo + footer.php)
```

### 2.2 Estrutura de Diretórios

```
/var/www/html/sisloc/
├── .env                          # Configurações ambiente (NÃO versionar)
├── .env.example                  # Template de configurações
├── composer.json                 # Autoload PSR-4, dependências
├── config/app.php                # Identidade da app, temas, timezone
│
├── public/
│   ├── index.php                 # Entry point + TODAS as rotas
│   ├── .htaccess                 # Rewrite Apache
│   ├── css/styles.css            # CSS unificado (Design System 3.0)
│   └── js/scripts.js             # JavaScript unificado
│
├── src/
│   ├── Core/                     # Componentes centrais
│   │   ├── Application.php       # Singleton, roteamento, 21 temas
│   │   ├── Router.php            # Registro e dispatch de rotas
│   │   ├── Request.php           # Wrapper HTTP request
│   │   ├── Response.php          # Respostas HTML/JSON/Redirect
│   │   ├── Env.php               # Carregador .env
│   │   ├── Session.php           # Gerenciamento de sessão
│   │   ├── Csrf.php              # Proteção CSRF
│   │   ├── Logger.php            # Logging estruturado
│   │   ├── Debug.php             # Debug bar (dev)
│   │   ├── ErrorHandler.php      # Tratamento de erros
│   │   └── Component.php         # Helpers UI
│   │
│   ├── Http/
│   │   ├── Controller.php        # Controller base abstrato
│   │   ├── Middleware.php        # Interface middleware
│   │   └── ErrorController.php   # Handlers 404/500/403/401
│   │
│   ├── Auth/
│   │   └── Rbac.php              # Controle de acesso baseado em roles
│   │
│   ├── Database/
│   │   └── Connection.php        # PDO singleton, detecção local/produção
│   │
│   ├── Repository/               # 23 repositórios
│   │   ├── BaseRepository.php    # CRUD, paginação, findBy* mágico
│   │   ├── UserRepository.php
│   │   ├── ClienteRepository.php
│   │   ├── ColaboradorRepository.php
│   │   ├── ColaboradorVerificacaoRepository.php
│   │   ├── EventoRepository.php
│   │   ├── ProdutoRepository.php
│   │   ├── SerialProdutoRepository.php
│   │   └── ... (outros 15)
│   │
│   ├── Service/                  # 27 serviços
│   │   ├── BaseService.php       # findOrFail, validação, sanitização
│   │   ├── EventoService.php
│   │   ├── ProdutoService.php
│   │   ├── ColaboradorVerificacaoService.php
│   │   ├── PdfGeneratorService.php     # Compartilhado (criado ETAPA 4)
│   │   ├── RelatorioEstoqueService.php
│   │   └── ... (outros 22)
│   │
│   └── Controllers/              # 23 controllers
│       ├── EventoController.php        # 770 linhas (refatorado)
│       ├── ProdutoController.php
│       ├── ColaboradorController.php
│       └── ... (outros 20)
│
├── views/                        # 49 arquivos PHP
│   ├── layout/                   # Header, footer, sidebar
│   ├── evento/                   # index, create, edit + 7 partials
│   ├── estoque/                  # index, create, edit, seriais, relatorios
│   ├── colaborador/              # index, create, edit
│   └── ... (outros 15 módulos)
│
├── database/
│   ├── sisloc.sql                # Dump completo (60.7 KB)
│   └── migrations/               # 14 migrations
│
├── docs/                         # Documentação técnica
├── storage/                      # Overrides (theme.json)
└── tmp/                          # Cache mPDF
```

### 2.2 Padrões Identificados

| Padrão | Implementação |
|--------|---------------|
| **Singleton** | `Application`, `Connection` (PDO) |
| **Repository** | `BaseRepository` + concretizações |
| **Service Layer** | `BaseService` + concretizações |
| **Middleware** | `AuthMiddleware` para rotas protegidas |
| **Template View** | PHP templates com layout compartilhado |
| **Front Controller** | `public/index.php` centraliza tudo |
| **Dependency Injection** | Manual nos construtores (sem container) |

### 2.3 Melhorias Sugeridas na Estrutura

1. **Separar rotas do `index.php`** para arquivos dedicados (ex: `routes/eventos.php`)
2. **Adicionar container de DI** para evitar instanciamento manual
3. **Mover lógica de negocio dos controllers** para services (EventoController ainda tem 770 linhas)
4. **Criar validators dedicados** em vez de validação inline nos services
5. **Adicionar testes automatizados** (PHPUnit)
6. **Implementar cache** (Redis/Memcached) para queries frequentes

---

## 3. Banco de Dados

### 3.1 Visão Geral

- **Engine:** MySQL 8.4 (InnoDB)
- **Charset:** utf8mb4
- **Total de Tabelas:** 25+ tabelas
- **Migrations:** 14 arquivos em `database/migrations/`

### 3.2 Tabelas Core (5)

| Tabela | Função | Colunas Principais |
|--------|--------|-------------------|
| `users` | Usuários do sistema | id, name, email, password, role, status, telefone, celular, cep |
| `empresa` | Configurações da empresa | id, nome, cnpj, endereco, telefone, whatsapp, email, pix_chave, logo_path, rodape_pdf |
| `modulos` | Módulos do sistema | id, nome, titulo, descricao, icone, ordem, ativo |
| `permissoes` | Permissões por módulo | id, modulo_id, nome, titulo, descricao, role_required |
| `role_permissoes` | Mapeamento role↔permissão | id, role, permissao_id |

### 3.3 Tabelas de Negócio (9)

| Tabela | Função | Relacionamentos |
|--------|--------|-----------------|
| `clientes` | Pessoas jurídicas clientes | 1:N → demandantes, eventos |
| `demandantes` | Responsáveis locais | N:1 → clientes, N:N → eventos |
| `produtores` | Produtores de eventos | N:1 → users, N:N → eventos |
| `fornecedores` | Fornecedores de equipamentos | N:1 → categorias, N:1 → subcategorias |
| `categorias` | Categorias de fornecedores | 1:N → subcategorias, fornecedores |
| `subcategorias` | Subcategorias | N:1 → categorias |
| `colaboradores` | Funcionários/freelancers | 1:N → colaboradores_verificacao |
| `colaboradores_verificacao` | Tokens de verificação | N:1 → colaboradores |
| `eventos` | Orçamentos/locações | N:1 → clientes, N:1 → produtores, N:1 → demandantes |

### 3.4 Tabelas de Evento/Inventário (8)

| Tabela | Função | Relacionamentos |
|--------|--------|-----------------|
| `salas` | Salas/ambientes de eventos | N:1 → eventos, N:1 → categorias_sala |
| `categorias_sala` | Categorias de salas | 1:N → salas |
| `produtos_evento` | Itens alocados em salas | N:1 → eventos, N:1 → salas, N:1 → planilhas |
| `planilhas` | Catálogo de produtos/valores | 1:N → produtos_evento, N:1 → unidademedida |
| `unidademedida` | Unidades de medida | 1:N → planilhas |
| `produtos` | Produtos do estoque | N:1 → secao, 1:N → seriaisproduto |
| `secao` | Seções de produtos | 1:N → produtos |
| `seriaisproduto` | Seriais de produtos | N:1 → produtos, N:N → montagens |
| `montagens` | Controle de montagem/devolução | N:1 → seriaisproduto, N:1 → eventos, N:1 → salas |

### 3.5 Tabelas de IA (7)

| Tabela | Função |
|--------|--------|
| `ai_learning` | Padrões de aprendizado |
| `ai_metrics` | Métricas de execução |
| `ai_executions` | Histórico de execuções de agentes |
| `ai_corrections` | Correções aplicadas |
| `ai_feedbacks` | Feedback dos usuários |
| `ai_interactions` | Histórico de interações |
| `ai_preferences` | Preferências dos usuários |

### 3.6 Diagrama ER (Texto)

```
users (1) ────────────────< (N) produtores (1) ────< (N) eventos
                                                         │
clientes (1) ────────────< (N) demandantes (N) ──────────┘
                                                         │
fornecedores (N) ──> (1) categorias                      │
                      │                                  │
                      └──> (N) subcategorias             │
                                                         │
colaboradores (1) ───< (N) colaboradores_verificacao     │
                                                         │
eventos (1) ──────< (N) salas (N) ──> (1) categorias_sala│
    │                                 │                  │
    └──< (N) produtos_evento ────────>┘                  │
                        │                                │
                        └──> (1) planilhas ──> (1) unidademedida
                        │
                        └──> (1) montagens ──> (1) seriaisproduto ──> (1) produtos ──> (1) secao
```

### 3.7 Inconsistências Identificadas

1. **Tabela `empresa` sem índice único** - permite múltiplas empresas (deveria ter limite 1)
2. **`colaboradores.ativo` TINYINT(1)** mas alguns queries tratam como boolean e outros como inteiro
3. **`eventos.estado`** usa 'O' (orçamento) e 'L' (locação) - deveria ser ENUM ou tabela separada
4. **`seriaisproduto.status`** usa strings ('ATIVO', 'MANUTENCAO', 'VENDER') - sem validação no banco
5. **Foreign keys ausentes** em várias tabelas (ex: `eventos.id_cliente` não tem FK declarada)
6. **`ai_*` tabelas** sem relacionamentos formais com `users`

---

## 4. Backend (PHP)

### 4.1 Controllers (23 total)

| Controller | Métodos Principais | Responsabilidade |
|------------|-------------------|------------------|
| `AuthController` | login, doLogin, logout | Autenticação |
| `DashboardController` | administrador, produtor, estoquista | Painéis por perfil |
| `UserController` | CRUD completo + toggle | Gestão de usuários |
| `ClienteController` | CRUD + toggle | Gestão de clientes |
| `DemandanteController` | CRUD + toggle | Gestão de demandantes |
| `ProdutorController` | CRUD + toggle | Gestão de produtores |
| `EventoController` | CRUD + toggle + converter + finalizar + itens + PDF | Gestão completa de eventos |
| `SalaController` | CRUD + toggle + listByEvento | Gestão de salas |
| `ProdutoEventoController` | CRUD + autocomplete + bySala/byEvento | Itens de evento |
| `ProdutoController` | CRUD + seriais + relatorios + PDF | Gestão de estoque |
| `SerialProdutoController` | CRUD + storeBatch + updateStatus | Seriais de produtos |
| `MontagemController` | inserirSerial/inserirLote + encaminhar/devolver + verificar | Controle de montagem |
| `ColaboradorController` | CRUD + toggle + enviarLink + reenviarLink | Gestão de colaboradores |
| `VerificacaoController` | mostrarFormulario + processarVerificacao | Verificação pública via token |
| `FornecedorController` | CRUD + toggle + getSubcategorias | Gestão de fornecedores |
| `CategoriaController` | CRUD AJAX | Categorias de fornecedores |
| `SubcategoriaController` | CRUD AJAX | Subcategorias |
| `PlanilhaController` | CRUD | Catálogo de produtos/valores |
| `CategoriaSalaController` | CRUD + toggle | Categorias de salas |
| `SecaoController` | CRUD AJAX | Seções de produtos |
| `UnidadeMedidaController` | CRUD AJAX | Unidades de medida |
| `ConfiguracoesController` | index + update + updateRoles + updateTheme + whatsapp-* | Configurações do sistema |
| `AIController` | dashboard + agents + execute + orchestrate + API | Sistema de IA |

### 4.2 Services (27 total)

Serviços seguem padrão `BaseService` com:
- `findOrFail($id)`
- `validateRequired($data, $fields)`
- `sanitize($data)`

**Serviços Notáveis:**

| Serviço | Função Especial |
|---------|-----------------|
| `PdfGeneratorService` | Compartilhado para todos os PDFs (criado na ETAPA 4) |
| `ColaboradorVerificacaoService` | Gera tokens, envia WhatsApp, processa verificação com foto+geolocation |
| `RelatorioEstoqueService` | Gera 5 tipos de relatórios de estoque |
| `EventoService` | `converterEmLocacao()`, `finalizarLocacao()`, `countByFilters()` |
| `MontagemService` | Controle de fluxo de seriais entre estoque e eventos |
| `AIService` | Integração com IA (detalhes não identificados) |

### 4.3 Repositories (23 total)

Seguem padrão `BaseRepository` com:
- `create($data)`, `update($id, $data)`, `delete($id)`
- `paginate($page, $perPage)`
- `findBy*()` mágico via `__call()`
- `search()` com paginação (implementações customizadas)

### 4.4 Fluxo de Dados

```
1. Request chega em public/index.php
2. Application::boot() inicializa Router, Session, Csrf
3. Router dispatcha para Controller method
4. Controller:
   a. Verifica permissão com Rbac::check()
   b. Valida CSRF token
   c. Extrai dados do request ($this->post(), $this->get())
   d. Chama Service method
5. Service:
   a. Valida dados (validate())
   b. Sanitiza dados (sanitizeData())
   c. Chama Repository method
6. Repository:
   a. Monta SQL com parâmetros
   b. Executa via Connection (PDO)
   c. Retorna array/bool/int
7. Service retorna para Controller
8. Controller retorna Response::html(view) ou Response::json()
9. View renderiza com header.php + conteúdo + footer.php
```

### 4.5 Padrões e Falhas

| Item | Status | Observação |
|------|--------|------------|
| Separação Controller/Service/Repository | ✅ Bom | Padr consistente |
| Validação CSRF | ✅ Bom | Em todos os POSTs |
| RBAC | ✅ Bom | Hardcoded + DB |
| N+1 queries | ⚠️ Parcial | Colaborador corrigido, outros podem existir |
| SQL parameterizado | ✅ Bom | BaseRepository usa prepared statements |
| Error handling | ⚠️ Parcial | try/catch em alguns controllers, não todos |
| Logging | ✅ Bom | Logger melhorado na ETAPA 5 |
| Comentários no código | ❌ Ruim | Poucos comentários explicativos |
| Testes | ❌ Ausente | Nenhum teste PHPUnit identificado |
| Documentação inline | ❌ Ruim | PHPDoc incompleto |

---

## 5. Frontend (JS + CSS)

### 5.1 CSS

**Arquivo:** `public/css/styles.css` + `docs/layout/branco/styles.css`

**Design System 3.0 "White Rabbit":**
- 21 temas configuráveis
- 24+ componentes reutilizáveis
- Variáveis CSS (`--neon-*`, `--bg-*`, `--text-*`)
- Classes utilitárias (btn-*, badge-*, card-*, etc)

**Componentes Principais:**
- `renderButton()`, `renderInput()`, `renderTable()`, `renderModal()`
- `renderBadge()`, `renderCard()`, `renderAlert()`, `renderToast()`
- `renderTabs*()`, `renderAvatar()`, `renderToggle()`, `renderProgress()`

**Problemas:**
- Estilos inline ainda existem em views de evento (~132 ocorrências)
- Alguns componentes usam IDs ao invés de classes
- Responsividade parcial (media queries existem mas não cobrem todos os casos)

### 5.2 JavaScript

**Arquivos:**
- `public/js/scripts.js` - Funções globais (toast, modal, popover, sidebar)
- `public/js/eventos/*.js` - 8 arquivos modulares para módulo de eventos (ETAPA 6)

**Padrão de Interação:**
```
Usuário clica botão
    ↓
Event handler (onclick ou addEventListener)
    ↓
fetch() AJAX para endpoint PHP
    ↓
Controller processa → Service → Repository → DB
    ↓
Response JSON retorna
    ↓
JS atualiza DOM (showToast, reload, update table)
```

**Funções Globais (scripts.js):**
- `showToast(type, title, msg)` - Feedback visual
- `openModal(id)`, `closeModal(id)` - Controle de modais
- `toggleSidebar()` - Menu lateral
- `tblSearch()`, `tblSetPerPage()`, `tblGoToPage()` - Tabelas
- `sortTable()` - Ordenação

**Módulo Eventos (JS separado por tab):**
| Arquivo | Responsabilidade |
|---------|-----------------|
| `tabs.js` | Switching de tabs horizontais e verticais + popover |
| `dados-evento.js` | Salvar evento, finalizar locação |
| `salas-produtos.js` | CRUD completo de salas e produtos (~1550 linhas) |
| `montar-os.js` | Stub (implementação futura) |
| `devolver-os.js` | Stub |
| `rh.js` | Stub |
| `fornecedores.js` | Stub |
| `fechamento.js` | Stub |

**Variáveis Globais Disponíveis:**
```javascript
window.EVENTO_ID    // ID do evento sendo editado
window.CSRF_TOKEN   // Token CSRF para requisições POST
window.SALAS_MAP    // Mapa de salas do evento
BASE_URL            // URL base do sistema
```

### 5.3 Responsividade

- Grid system: `col2`, `col3`, `col4` (CSS classes)
- Media queries para sidebar (mobile esconde sidebar)
- Tabelas: scroll horizontal em telas pequenas
- Formulários: inputs com width 100% por padrão
- **Problema:** Alguns grids usam `grid-template-columns:repeat(4,1fr)` sem media query para mobile

---

## 6. Fluxos do Sistema

### 6.1 Cadastro de Usuário

```
Administrador → /configuracoes → aba Usuários → "Novo Usuário"
    ↓
Modal renderizado com renderInput() para campos
    ↓
Preenche: name, email, password, role, telefone, etc
    ↓
Clica "Salvar" → JS submit
    ↓
POST /users/store
    ↓
UserController::store()
    ├─ Valida CSRF
    ├─ Rbac::check('usuarios.criar')
    ├─ Extrai dados do POST
    └─ UserService::create()
         ├─ sanitizeData()
         ├─ validate() - checa email único, senha mínima
         ├─ password_hash() - criptografa senha
         └─ UserRepository::create() - INSERT INTO users
    ↓
Response JSON {success: true}
    ↓
JS: showToast('green', 'Sucesso', 'Usuário criado')
    ↓
Recarrega página ou atualiza tabela
```

### 6.2 Login

```
Usuário acessa / → redirect para /auth/login
    ↓
Preenche email + senha
    ↓
POST /auth/login
    ↓
AuthController::doLogin()
    ├─ Valida CSRF
    ├─ Busca user por email
    ├─ password_verify()
    ├─ Se OK:
    │   ├─ Session::regenerate()
    │   ├─ $_SESSION['user_id'] = $user['id']
    │   ├─ $_SESSION['user_role'] = $user['role']
    │   └─ Redirect para dashboard conforme role:
    │       - administrador → /dashboard/administrador
    │       - produtor → /dashboard/produtor
    │       - estoquista → /dashboard/estoquista
    └─ Se falhou:
        └─ Retorna com error message
```

### 6.3 CRUD de Eventos (Completo)

#### 6.3.1 Listagem

```
GET /eventos
    ↓
EventoController::index()
    ├─ Rbac::check('eventos.listar')
    ├─ Extrai page, search, estado, status_locacao
    ├─ EventoService::search($filters, $page, 15)
    │   └─ EventoRepository::search() - SELECT com WHERE dinâmico
    ├─ EventoService::countByFilters($filters)
    └─ Renderiza evento/index.php com:
        - Stats cards (total, ativos, etc)
        - renderTable() com searchable + paginated
        - Filtros (estado, status_locacao)
        - Botões: Ver, Editar, Excluir, PDF
```

#### 6.3.2 Criação

```
GET /eventos/create
    ↓
EventoController::create()
    ├─ Busca clientes, produtores, demandantes
    └─ Renderiza evento/create.php com form

Usuário preenche formulário
    ↓
JS: submit via fetch()
    ↓
POST /eventos/store
    ↓
EventoController::store()
    ├─ Valida CSRF
    ├─ Rbac::check('eventos.criar')
    ├─ Extrai todos os campos do POST
    └─ EventoService::create()
         └─ EventoRepository::create() - INSERT INTO eventos
    ↓
Response JSON {success: true, id: $id}
    ↓
JS: redirect para /eventos/edit/$id
```

#### 6.3.3 Edição (Complexo - 7 tabs)

```
GET /eventos/edit/{id}
    ↓
EventoController::edit($id)
    ├─ Busca evento (findOrFail)
    ├─ Busca clientes, produtores, demandantes
    ├─ Busca salas do evento
    ├─ Busca categorias ativas
    ├─ Busca produtos por sala
    ├─ Busca totais do evento
    ├─ Busca dados de montagem
    └─ Renderiza evento/edit.php com:
        - Tab horizontal 0: "Locações" (com sub-tabs verticais)
            - VTab 0: Dados do Evento (edit-dados.php)
            - VTab 1: Salas e Produtos (edit-salas-produtos.php)
        - Tab horizontal 1: Montar OS
        - Tab horizontal 2: Devolver OS
        - Tab horizontal 3: Recursos Humanos
        - Tab horizontal 4: Fornecedores
        - Tab horizontal 5: Fechamento

JavaScript: 8 arquivos JS separados (ver seção 5.2)
```

#### 6.3.4 Adicionar Item ao Evento

```
Usuário preenche formulário de item na tab "Salas e Produtos"
    ↓
JS salas-produtos.js::adicionarItem()
    ↓
POST /eventos/itens/adicionar
    ↓
EventoController::adicionarItem()
    ├─ Valida CSRF
    ├─ Rbac::check('eventos.editar')
    ├─ Extrai: id_evento, id_sala, id_categoria, id_planilha, qtd, valor_unit, dias, custo_unit
    ├─ Calcula total_item = qtd * valor_unit * dias
    └─ ProdutoEventoService::create()
         └─ ProdutoEventoRepository::create() - INSERT INTO produtos_evento
    ↓
Response JSON {ok: true, item: {...}}
    ↓
JS: adiciona item no DOM sem refresh (esconde estado vazio, atualiza totais)
```

#### 6.3.5 Gerar PDF

```
Usuário clica "Gerar PDF" na listagem ou edição
    ↓
GET /eventos/pdf/{id}
    ↓
EventoController::gerarPdf($id)
    ├─ Rbac::check('eventos.editar')
    ├─ Busca evento, itens, salas
    ├─ Resolve nomes de cliente/produtor/demandante
    ├─ Calcula totais por sala e geral
    ├─ PdfGeneratorService::setupHeaderFooter()
    ├─ gerarEventoHtml() - monta HTML completo
    ├─ Logger::info('PDF generated', {...})
    └─ PdfGeneratorService::download($html, $filename)
         ├─ mPDF::WriteHTML()
         └─ mPDF::Output($filename, 'D') - força download
```

### 6.4 Verificação de Colaborador (Público)

```
Admin cria colaborador em /colaboradores/create
    ↓
Admin clica "Enviar Link"
    ↓
POST /colaboradores/enviar-link/{id}
    ↓
ColaboradorController::enviarLink($id)
    ├─ Valida CSRF
    ├─ Rbac::check('colaboradores.editar')
    └─ ColaboradorVerificacaoService::gerarToken($id)
         ├─ bin2hex(random_bytes(32)) - token de 64 chars
         └─ Repository::createToken() - INSERT colaboradores_verificacao
    ↓
ColaboradorVerificacaoService::enviarLinkWhatsapp($id, $token)
    ├─ Monta URL: WHATSAPP_PUBLIC_URL/colaboradores/verificar/{token}
    ├─ Monta mensagem: "Olá {nome}! Clique no link..."
    ├─ POST para Bot API: WHATSAPP_BOT_URL/sessions/{sessionId}/send-message
    │   ├─ Headers: Content-Type: application/json, x-api-key: {apiKey}
    │   └─ Body: {number, message, linkPreview: true}
    ├─ Logger::whatsapp('SENDING', ...)
    └─ Se sucesso: Repository::markAsSent()
    ↓
Colaborador recebe WhatsApp com link
    ↓
Clica no link → GET /colaboradores/verificar/{token} (público, sem auth)
    ↓
VerificacaoController::mostrarFormulario($token)
    ├─ Verifica token válido
    └─ Renderiza verificacao/formulario.php com:
        - Nome do colaborador
        - Câmera para foto (getUserMedia API)
        - Geolocalização (navigator.geolocation)
    ↓
Colaborador tira foto + permite localização
    ↓
POST /colaboradores/verificar/{token}
    ↓
VerificacaoController::processarVerificacao($token)
    ├─ Salva foto em storage/
    ├─ ColaboradorVerificacaoService::processarVerificacao()
    │   ├─ Repository::markAsVerified() - salva foto + geolocation
    │   └─ Repository::toggleStatus() + UPDATE colaboradores SET ativo=1
    └─ Renderiza verificacao/sucesso.php
```

### 6.5 Controle de Estoque (Seriais)

```
Estoquista acessa /estoque
    ↓
Lista produtos com contagem de seriais
    ↓
Clica "Seriais" em um produto
    ↓
GET /estoque/seriais/{id}
    ↓
ProdutoController::seriais($id)
    ├─ Busca produto
    ├─ Busca todos seriais do produto
    └─ Renderiza estoque/seriais.php com:
        - Form para adicionar serial único
        - Form para adicionar lote de seriais
        - Tabela de seriais existentes com status badges
        - Ações: Ativar, Manutenção, Marcar para Vender, Excluir

Adicionar Serial Único:
    POST /seriais/store
    ↓
    SerialProdutoController::store()
    └─ SerialProdutoService::create()
         └─ INSERT INTO seriaisproduto (serial, id_produto, status='ATIVO')

Marcar para Manutenção:
    POST /seriais/update-status/{id}
    ↓
    SerialProdutoController::updateStatus()
    └─ UPDATE seriaisproduto SET status='MANUTENCAO', motivo=$motivo
```

### 6.6 Montagem de Evento

```
Usuário acessa /montagem
    ↓
MontagemController::index()
    ├─ Busca todos eventos
    ├─ Busca seriais disponíveis (status='ATIVO')
    └─ Renderiza montagem/index.php com:
        - Selecionar evento
        - Buscar serial por código
        - Inserir serial na montagem do evento
        - Encaminhar serial para sala específica
        - Marcar como montado/devolvido

Fluxo de Serial na Montagem:
    1. Serial inserido → montagens INSERT (status='pendente')
    2. Encaminhado para sala → UPDATE id_sala
    3. Marcado como montado → UPDATE status='montado'
    4. Devolvido → UPDATE status='devolvido' ou DELETE da montagem
```

---

## 7. Integrações Externas

### 7.1 WhatsApp Bot API

**Servidor:** `http://201.23.68.17:3000`  
**Session:** `novoframework` (status: CONNECTED)  
**Autenticação:** Header `x-api-key: {WHATSAPP_API_KEY}`

**Endpoints Usados:**
| Método | Endpoint | Uso |
|--------|----------|-----|
| GET | `/health` | Health check |
| GET | `/sessions/{id}` | Status da sessão |
| POST | `/sessions/{id}/send-message` | Enviar mensagem de texto |
| POST | `/sessions/{id}/send-media` | Enviar mídia (PDF, imagens) |
| GET | `/sessions/{id}/qr` | QR Code (PNG) |
| POST | `/sessions/{id}/logout` | Desconectar + gerar novo QR |

**Configuração (.env):**
```env
WHATSAPP_BOT_URL=http://201.23.68.17:3000
WHATSAPP_API_KEY=sua_chave_aqui
WHATSAPP_SESSION_ID=novoframework
WHATSAPP_PUBLIC_URL=https://seudominio.com/sisloc/public
```

**Fluxo de Envio:**
```php
// ColaboradorVerificacaoService::enviarLinkWhatsapp()
$telefone = preg_replace('/[^0-9]/', '', $colaborador['telefone']);
$endpoint = $botUrl . '/sessions/' . $sessionId . '/send-message';

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'number' => $telefone,
    'message' => $mensagem,
    'linkPreview' => true,
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $apiKey,
]);
```

### 7.2 ViaCEP API

**URL:** `https://viacep.com.br/ws/{CEP}/json/`  
**Uso:** Auto-preenchimento de endereços em formulários (clientes, usuários)

### 7.3 BrasilAPI

**URL:** `https://brasilapi.com.br/api/cnpj/v1/{CNPJ}`  
**Uso:** Auto-preenchimento de dados de empresas (fornecedores, clientes)

---

## 8. RBAC - Controle de Acesso

### 8.1 Roles (4)

| Role | Descrição | Dashboard | Módulos Principais |
|------|-----------|-----------|-------------------|
| `guest` | Não logado | - | Apenas login |
| `administrador` | Acesso total | /dashboard/administrador | Todos os módulos |
| `produtor` | Gestão de produção | /dashboard/produtor | Eventos, montagem, colaboradores |
| `estoquista` | Gestão de estoque | /dashboard/estoquista | Estoque, seriais, montagem |

### 8.2 Hierarquia

```
administrador → [administrador, produtor, estoquista]
produtor      → [produtor, estoquista]
estoquista    → [estoquista]
guest         → []
```

### 8.3 Implementação

**Hardcoded em `src/Auth/Rbac.php`:**
```php
self::$permissions = [
    'eventos' => ['administrador', 'produtor'],
    'eventos.listar' => ['administrador', 'produtor'],
    'eventos.criar' => ['administrador', 'produtor'],
    'eventos.editar' => ['administrador', 'produtor'],
    'eventos.excluir' => ['administrador'],
    // ... ~100+ permissões
];
```

**Database (para interface `/configuracoes`):**
- `modulos` - lista de módulos
- `permissoes` - permissões por módulo
- `role_permissoes` - vinculação role↔permissão

**Verificação:**
```php
// No controller
if (!Rbac::check('eventos.editar')) {
    return $this->redirect($baseUrl . '/dashboard');
}

// Na view (sidebar)
if (\App\Auth\Rbac::check('eventos.listar')) {
    // mostra link no menu
}
```

### 8.4 Permissões por Módulo

| Módulo | listar | criar | editar | excluir |
|--------|--------|-------|--------|---------|
| usuarios | admin | admin | admin | admin |
| clientes | admin, produtor | admin, produtor | admin, produtor | admin |
| demandantes | admin, produtor | admin, produtor | admin, produtor | admin |
| eventos | admin, produtor | admin, produtor | admin, produtor | admin |
| estoque | admin, estoquista | admin, estoquista | admin, estoquista | admin |
| montagem | admin, produtor, estoquista | - | admin, produtor, estoquista | - |
| colaboradores | admin | admin | admin | admin |
| fornecedores | admin | admin | admin | admin |
| produtores | admin | admin | admin | admin |
| planilhas | admin | admin | admin | admin |
| configuracoes | admin | - | admin | - |
| ai | admin | - | - | admin |

---

## 9. Agentes e Automações

### 9.1 WhatsApp Bot (Node.js)

**Localização:** Servidor externo `201.23.68.17:3000`  
**Tecnologia:** Node.js + Express + whatsapp-web.js  
**Documentação Completa:** Ver contexto da conversa anterior

**Funcionalidades:**
- Envio de mensagens de texto
- Envio de mídia (PDFs, imagens)
- Multi-sessão (suporte a várias empresas)
- QR Code para autenticação
- Health check

### 9.2 AI Agents (Experimental)

**Módulo:** `/ai`  
**Status:** Em desenvolvimento  
**Tabelas:** `ai_learning`, `ai_metrics`, `ai_executions`, `ai_corrections`, `ai_feedbacks`, `ai_interactions`, `ai_preferences`, `ai_learning_sessions`

**Funcionalidades Identificadas:**
- Dashboard de agentes
- Execução de tarefas
- Orquestração de múltiplos agentes
- Aprendizado de padrões
- Feedback de usuários
- Métricas de performance

**API Endpoints:**
- `GET /api/ai/health` - Health check
- `POST /api/ai/execute` - Executar agente
- `POST /api/ai/orchestrate` - Orquestrar múltiplos agentes
- `POST /api/ai/learn/pattern` - Aprender padrão
- `POST /api/ai/learn/feedback` - Registrar feedback

**Detalhes de implementação:** Não identificados completamente (código em análise)

### 9.3 Automações de Formulário

**CEP Auto-complete:**
```javascript
input.addEventListener('blur', function() {
    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(r => r.json())
        .then(data => {
            document.getElementById('logradouro').value = data.logradouro;
            document.getElementById('bairro').value = data.bairro;
            document.getElementById('cidade').value = data.localidade;
            document.getElementById('uf').value = data.uf;
        });
});
```

**CNPJ Auto-complete:**
```javascript
input.addEventListener('blur', function() {
    fetch('https://brasilapi.com.br/api/cnpj/v1/' + cnpj)
        .then(r => r.json())
        .then(data => {
            document.getElementById('razao_social').value = data.razao_social;
            document.getElementById('nome_fantasia').value = data.nome_fantasia;
            document.getElementById('telefone').value = data.ddd_telefone;
            // Endereço também é preenchido
        });
});
```

---

## 10. Problemas e Melhorias

### 10.1 Pontos Fracos

| Problema | Gravidade | Impacto |
|----------|-----------|---------|
| **Sem testes automatizados** | Alta | Bugs em produção difíceis de detectar |
| **Controller EventoController com 770 linhas** | Média | Difícil manutenção |
| **SQL sem foreign keys declaradas** | Alta | Integridade de dados comprometida |
| **Código morto (ai/ agents não usados)** | Baixa | Confusão para novos devs |
| **Estilos inline (~132 ocorrências)** | Baixa | Dificulta manutenção de CSS |
| **Comentários escassos** | Média | Difícil entender lógica complexa |
| **Logging inconsistente** | Média | Nem todas as ações são logadas |
| **Validação duplicada** | Baixa | Service + Controller validam mesmos campos |

### 10.2 Gargalos Identificados

1. **N+1 Queries:**
   - ✅ Corrigido em ColaboradorController (batch fetching)
   - ⚠️ Verificar em outros controllers (Fornecedor, Evento)

2. **PDF Generation:**
   - ✅ Refatorado para serviço compartilhado (ETAPA 4)
   - ⚠️ mPDF cria diretório temporário a cada chamada

3. **Session Handler:**
   - PHP default session handler (arquivos)
   - Deveria usar database/Redis para produção

4. **Assets sem versionamento:**
   - CSS/JS usam `filemtime()` para cache busting
   - Deveria usar build system (Webpack/Vite)

### 10.3 Código Duplicado

| O quê | Onde | Status |
|-------|------|--------|
| Header/Footer PDF | EventoController + RelatorioEstoqueService | ✅ Resolvido (PdfGeneratorService) |
| Temp dir creation | Múltiplos services | ✅ Resolvido (PdfGeneratorService) |
| Empresa query | Múltiplos controllers | ✅ Resolvido (PdfGeneratorService) |
| CRUD methods pattern | Todos controllers | Padrão consistente (bom) |
| CSRF validation | Todo POST | Padrão consistente (bom) |

### 10.4 Melhorias Sugeridas

**Curto Prazo:**
1. Adicionar foreign keys no banco de dados
2. Criar testes PHPUnit para services críticos
3. Adicionar PHPDoc em todos os methods públicos
4. Implementar cache para queries frequentes
5. Mover rotas do `index.php` para arquivos dedicados

**Médio Prazo:**
1. Refatorar EventoController em controllers menores
2. Implementar sistema de notificações (email/sms)
3. Adicionar exportação CSV/Excel para relatórios
4. Criar API RESTful completa para integrações externas
5. Implementar filas para envio de WhatsApp (async)

**Longo Prazo:**
1. Migrar para framework estabelecido (Laravel/Symfony)
2. Adicionar frontend framework (Vue.js/React)
3. Implementar websockets para notificações real-time
4. Criar sistema de multi-tenancy
5. Adicionar GraphQL para API flexível

---

## 11. Guia de Onboarding

### 11.1 Para Novos Desenvolvedores

#### Passo 1: Entender o Propósito

O SisLoc é um sistema de **locação de equipamentos para eventos**. Pense nele como um ERP simplificado para empresas que alugam equipamentos (som, iluminação, painéis, etc) para eventos.

#### Passo 2: Configurar Ambiente Local

```bash
# 1. Clonar repositório
cd /var/www/html/sisloc

# 2. Instalar dependências
composer install

# 3. Configurar ambiente
cp .env.example .env
# Editar .env com suas credenciais de banco local

# 4. Importar banco
mysql -u root -p sisloc < database/sisloc.sql

# 5. Acessar
# URL: http://localhost/sisloc/public
# Login: verificar no banco (tabela users)
```

#### Passo 3: Entender a Arquitetura

```
Requisição → public/index.php → Router → Controller → Service → Repository → MySQL
                                                                    ↓
Resposta ← View (header + conteúdo + footer) ← Response ← Controller
```

**Regras de Ouro:**
1. **NUNCA** colocar lógica de negócio no controller
2. **SEMPRE** usar Service para regras de negócio
3. **SEMPRE** usar Repository para acesso a dados
4. **NUNCA** usar SQL direto no controller (exceto casos excepcionais)
5. **SEMPRE** validar CSRF em POSTs
6. **SEMPRE** verificar permissão com `Rbac::check()`

#### Passo 4: Criar um Novo Módulo

**Checklist completo:** Ver `AGENTS.md` → "REGRA OBRIGATÓRIA: Checklist Completo para Criar Novo Módulo"

Resumo:
1. Database (migration SQL)
2. Repository (classe)
3. Service (classe)
4. Controller (classe)
5. Views (index, create, edit)
6. Rotas (public/index.php)
7. RBAC Array (Rbac.php) ← **CRUCIAL**
8. RBAC Database (modulos, permissoes)
9. Sidebar (link no menu)
10. Dead Code Analysis

#### Passo 5: Padrões de Código

**Controller:**
```php
public function index(): Response
{
    if (!Rbac::check('modulo.listar')) {
        return $this->redirect($this->baseUrl . '/dashboard');
    }
    
    $page = (int) $this->get('page', 1);
    $search = $this->get('search', '');
    $data = $this->service->search($search, $page, 15);
    
    return $this->view('modulo/index', [
        'items' => $data['data'],
        'pagination' => $data['pagination'],
        'search' => $search,
    ]);
}
```

**Service:**
```php
public function create(array $data): int
{
    $this->validate($data);
    $data = $this->sanitizeData($data);
    return $this->repository->create($data);
}
```

**View:**
```php
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

    <section class="section active" id="sec-modulo">
        <!-- Conteúdo aqui -->
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
```

#### Passo 6: Componentes UI

Sempre usar Design System 3.0:
```php
// Inputs
echo renderInput(['name' => 'nome', 'label' => 'Nome', 'required' => true]);

// Tabelas
echo renderTable([
    'id' => 'tbl-modulo',
    'searchable' => true,
    'paginated' => true,
    'headers' => [...],
    'rows' => $rows,
]);

// Botões
echo renderButton(['label' => 'Salvar', 'variant' => 'cyan', 'type' => 'submit']);

// Toast (JS)
showToast('green', 'Sucesso', 'Registro criado com sucesso!');
```

### 11.2 Fluxo de Desenvolvimento Típico

```
1. Receber demanda (ex: "Adicionar campo X no módulo Y")
   ↓
2. Analisar banco: DESCRIBE tabela_y
   ↓
3. Criar migration SQL (se novo campo)
   ↓
4. Atualiar Repository (se novo campo, adicionar no $fillable)
   ↓
5. Atualizar Service (validação, sanitização)
   ↓
6. Atualizar Controller (extrair novo campo do POST)
   ↓
7. Atualizar View (adicionar campo no formulário)
   ↓
8. Atualizar JS (se necessário)
   ↓
9. Testar manualmente
   ↓
10. Commit e push
```

---

## 12. Mapa de Fluxos

### 12.1 Fluxo Principal: Criar Evento Completo

```
[ADMIN]
  │
  ├─> /eventos/create
  │     └─> Preenche: cliente, produtor, demandante, datas, local
  │     └─> Salva → Redirect para /eventos/edit/{id}
  │
  ├─> /eventos/edit/{id} (Tab: Dados do Evento)
  │     └─> Ajusta dados → Salva (AJAX)
  │
  ├─> /eventos/edit/{id} (Tab: Salas e Produtos)
  │     ├─> Cria Sala → POST /api/eventos/salas (AJAX)
  │     ├─> Adiciona Produto → POST /eventos/itens/adicionar (AJAX)
  │     └─> Ajusta quantidades/valores → inline (AJAX)
  │
  ├─> /eventos/pdf/{id} (Gera Orçamento)
  │     └─> Download PDF
  │
  ├─> Converter em Locação (quando cliente aceita)
  │     └─> POST /eventos/converter/{id} (AJAX)
  │         └─> estado: 'O' → 'L'
  │
  ├─> /montagem
  │     ├─> Insere seriais na montagem do evento
  │     ├─> Encaminha para salas
  │     └─> Marca como montado
  │
  └─> Finalizar Locação
        └─> POST /eventos/finalizar/{id} (AJAX)
            └─> status_locacao: 'A' → 'F'
```

### 12.2 Fluxo: Verificação de Colaborador

```
[ADMIN]
  │
  ├─> /colaboradores/create
  │     └─> Preenche: nome, telefone, email, tipo, atua_como
  │     └─> Salva → Lista de colaboradores
  │
  ├─> Clica "Enviar Link"
  │     └─> POST /colaboradores/enviar-link/{id}
  │         ├─> Gera token (64 chars hex)
  │         └─> Envia WhatsApp via Bot API
  │
  └─> Aguarda verificação
        
[COLABORADOR]
  │
  ├─> Recebe WhatsApp com link
  │     └─> Clica → /colaboradores/verificar/{token}
  │
  ├─> Formulário de verificação
  │     ├─> Tira foto (câmera)
  │     └─> Permite geolocalização
  │     └─> Envia → POST /colaboradores/verificar/{token}
  │
  └─> Verificação concluída
        └─> Colaborador ativado automaticamente
```

### 12.3 Fluxo: Controle de Estoque

```
[ESTOQUISTA]
  │
  ├─> /estoque
  │     └─> Lista produtos com contagem de seriais
  │
  ├─> /estoque/create
  │     └─> Cria produto → Define seção, custo, pode_ser_locado
  │
  ├─> /estoque/seriais/{id}
  │     ├─> Adiciona serial único
  │     ├─> Adiciona lote de seriais
  │     └─> Tabela de seriais:
  │         ├─> Ativar
  │         ├─> Marcar Manutenção (com motivo)
  │         ├─> Marcar para Vender
  │         └─> Excluir
  │
  └─> /estoque/relatorios
        └─> Gera PDF por tipo:
            ├─> Geral
            ├─> Por seção
            ├─> Manutenção
            ├─> Para vender
            └─> Por produto
```

### 12.4 Fluxo: Montagem de Evento

```
[PRODUTOR / ESTOQUISTA]
  │
  ├─> /montagem
  │     └─> Seleciona evento
  │
  ├─> Inserir Serial
  │     ├─> Busca serial por código
  │     └─> Adiciona na montagem (status='pendente')
  │
  ├─> Encaminhar para Sala
  │     └─> UPDATE montagens SET id_sala = X
  │
  ├─> Marcar como Montado
  │     └─> UPDATE montagens SET status = 'montado'
  │
  └─> Devolver
        └─> UPDATE montagens SET status = 'devolvido'
            OU DELETE da montagem
```

---

## 13. Pontos Críticos

### 13.1 Críticos (Ação Imediata Necessária)

| # | Problema | Risco | Ação Recomendada |
|---|----------|-------|------------------|
| 1 | **Sem testes automatizados** | Bugs em produção | Criar suite PHPUnit mínima para services |
| 2 | **Foreign keys ausentes** | Integridade de dados | Adicionar FKs em todas as relações N:1 |
| 3 | **SQL injection potencial** | Segurança | Revisar todas as queries não-parameterizadas |
| 4 | **Session handler em arquivos** | Performance/segurança em produção | Migrar para database/Redis |
| 5 | **WHATSAPP_API_KEY exposta no .env** | Segurança | Garantir .env no .gitignore |

### 13.2 Importantes (Ação em Curto Prazo)

| # | Problema | Impacto | Ação Recomendada |
|---|----------|---------|------------------|
| 6 | **EventoController 770 linhas** | Manutenibilidade | Extrair controllers menores (SalaController, ItemController) |
| 7 | **Logging inconsistente** | Debug difícil | Adicionar logging em todas as ações críticas |
| 8 | **Código morto (AI agents)** | Confusão | Remover ou documentar como experimental |
| 9 | **Estilos inline** | CSS difícil de manter | Extrair para classes utilitárias |
| 10 | **N+1 queries não mapeadas** | Performance | Auditar todos os controllers com loops |

### 13.3 Melhorias (Médio Prazo)

| # | Melhoria | Benefício |
|---|----------|-----------|
| 11 | Cache (Redis) | Performance em consultas frequentes |
| 12 | Filas para WhatsApp | Melhor UX (não bloqueante) |
| 13 | API RESTful completa | Integrações externas |
| 14 | Notificações (email/SMS) | Melhor comunicação com usuários |
| 15 | Exportação CSV/Excel | Relatórios portáteis |

### 13.4 Dívida Técnica Acumulada

| Área | Dívida | Esforço |
|------|--------|---------|
| **Testes** | 0% cobertura | Alto (semanas) |
| **Documentação** | PHPDoc incompleto | Médio (dias) |
| **Refatoração** | Controllers grandes | Médio (dias) |
| **Banco** | FKs ausentes, índices faltando | Baixo (horas) |
| **Frontend** | Estilos inline, JS não-minificado | Baixo (horas) |

---

## Apêndice A: Referências Rápidas

### Comandos Úteis

```bash
# Ver rotas
grep -n "router->" public/index.php | head -20

# Verificar conexão DB
php -r "require 'vendor/autoload.php'; print_r(\App\Database\Connection::testConnection());"

# Listar controllers
ls src/Controllers/*.php

# Buscar código morto
grep -rn "function " src/Controllers/ | wc -l

# Testar WhatsApp Bot
curl -H "x-api-key: $WHATSAPP_API_KEY" http://201.23.68.17:3000/health
```

### Estrutura de Tabela: eventos

```sql
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,                    -- FK →<think>

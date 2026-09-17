# Módulo Sublocação — Documentação Técnica Completa

> Gerado em 2026-06-10. Cobre banco, rotas, controllers, services, repositórios, views, JS e fluxo do `EVENTO_ID`.

---

## 1. Conceito

Um item de `produtos_evento` (ex: "Nobreak 60 un.") pode ter **N sublocadores**, cada um com quantidade, valor unitário e total independentes. Cada sublocador gera uma conta a pagar separada quando o botão "Gerar Contas" é acionado.

## 2. Banco de Dados

### `sublocacao_itens` (catálogo do sublocador)

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT PK | |
| id_fornecedor | INT FK → fornecedores | Sublocador dono do item |
| produto | VARCHAR(255) | Nome do produto |
| codigo | VARCHAR(100) | Código interno |
| quantidade | DECIMAL(10,2) | Quantidade padrão |
| valor_unit | DECIMAL(10,2) | Valor unitário padrão |
| observacao | TEXT | |
| status | TINYINT(1) | 1=ativo |
| created_at / updated_at | TIMESTAMP | |

Gerenciado no formulário `views/fornecedor/edit.php` → card "Itens para Sublocação".

### `produto_evento_sublocacao` (vínculo item do evento ↔ sublocador)

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT PK | |
| id_produto_evento | INT FK → produtos_evento | Item do evento |
| id_fornecedor | INT FK → fornecedores | Sublocador |
| id_sublocacao_item | INT FK → sublocacao_itens (nullable) | Item do catálogo |
| quantidade | DECIMAL(10,2) | Qtd sublocada |
| valor_unit | DECIMAL(10,2) | Valor unitário |
| total | DECIMAL(10,2) | qtd × valor_unit |
| status | ENUM('pendente','pago','cancelado') | |

Criação: Migration `032_criar_sublocacao.sql`.

---

## 3. Arquitetura de Arquivos

```
public/index.php                           ← Rotas (grupos /api/sublocacao e /eventos/fornecedores-html)
│
├── src/Controllers/Api/SublocacaoApiController.php   ← 6 endpoints JSON
├── src/Controllers/SublocacaoItemController.php      ← CRUD catálogo de itens
├── src/Controllers/EventoController.php              ← fornecedoresTabHtml() (AJAX)
│
├── src/Service/SublocacaoService.php                 ← vincular/desvincular/gerarContas
├── src/Service/SublocacaoItemService.php             ← CRUD wrapper sublocacao_itens
│
├── src/Repository/ProdutoEventoSublocacaoRepository.php   ← produto_evento_sublocacao
├── src/Repository/SublocacaoItemRepository.php            ← sublocacao_itens
│
├── views/evento/edit.php                                 ← HPane 4: require edit-sublocacao.php
├── views/evento/partials/edit-sublocacao.php             ← Aba Sublocação: container + modal + TODO JS inline
├── views/evento/partials/edit-sublocacao-content.php     ← Conteúdo da aba (renderizado server-side e via AJAX)
├── views/fornecedor/edit.php                             ← Card "Itens para Sublocação" + modal CRUD
│
├── public/js/eventos/tabs.js                             ← switchHTab() + refreshSublocacaoTab()
└── public/js/eventos/fornecedores.js                     ← Modal de solicitação de cotação
```

---

## 4. Rotas

Definidas em `public/index.php`:

### Grupo `/api/sublocacao` (Auth + CSRF)

| Método | Rota | Controller::method | Descrição |
|--------|------|-------------------|-----------|
| GET | `/api/sublocacao/itens/{idFornecedor}` | SublocacaoApiController::itens | Catálogo de itens do sublocador |
| GET | `/api/sublocacao/list/{idProdutoEvento}` | SublocacaoApiController::listVinculos | Vínculos de um item |
| POST | `/api/sublocacao/vincular` | SublocacaoApiController::vincular | Vincula sublocador a item |
| POST | `/api/sublocacao/desvincular/{id}` | SublocacaoApiController::desvincular | Remove vínculo |
| POST | `/api/sublocacao/gerar-contas/{idEvento}` | SublocacaoApiController::gerarContas | Gera contas_pagar |
| GET | `/api/sublocacao/fornecedores` | SublocacaoApiController::fornecedores | Lista fornecedores ativos |

### Rota AJAX dedicada (Auth)

| Método | Rota | Controller::method | Descrição |
|--------|------|-------------------|-----------|
| GET | `/eventos/fornecedores-html/{id}` | EventoController::fornecedoresTabHtml | Retorna HTML da aba Sublocação |

---

## 5. Fluxo Completo (Front-end → Back-end)

### 5.1 Abertura da aba Sublocação

1. Usuário clica na tab horizontal `data-idx="4"` (Sublocação)
2. `tabs.js:110-118` — Event delegation captura o clique no `.loc-htab`
3. `switchHTab(4, tabButton)` → mostra `.loc-hpane-4`, esconde as outras
4. Como `idx === 4`: chama `refreshSublocacaoTab()` (`tabs.js:58`)
5. `refreshSublocacaoTab()` (`tabs.js:64-85`):
   - Obtém `EVENTO_ID` de `window.EVENTO_ID` (fallback: `typeof EVENTO_ID` global)
   - Se `!eventoId` → retorna (sem ID = sem carregar)
   - Fetch: `GET /eventos/fornecedores-html/{eventoId}`
   - Se `data.success && data.html`: injeta `data.html` no `#sublocacao-container`
   - Chama `carregarTodosBadgesSublocacao()` se existir
6. `EventoController::fornecedoresTabHtml($id)` (`src/Controllers/EventoController.php:223`):
   - Busca `$evento`, `$salas`, `$produtosPorSala`, `$fornecedores`
   - Renderiza `edit-sublocacao-content.php` via `ob_start()/require/ob_get_clean()`
   - Retorna JSON: `{ success: true, html: "...", hasItems: bool }`

### 5.2 Renderização do conteúdo

`edit-sublocacao-content.php` (`views/evento/partials/edit-sublocacao-content.php`):
- Agrupa `$produtosPorSala` por sala
- Renderiza cards por sala com badges de contagem
- Cada item tem botão "Sublocar" → `onclick="abrirModalSublocacao(itemId, nomeProduto)"`
- Cada item tem badge `<span class="sala-item-subloc-badge" data-item-id="...">` (inicialmente oculto)

### 5.3 Modal de Sublocação

Abrir (`edit-sublocacao.php:143` → `abrirModalSublocacao()`):

```
abrirModalSublocacao(itemId, nomeProduto)
  ├── Define _sublocacaoItemId = itemId
  ├── Popula campos do modal (#modal-sublocacao)
  ├── carregarFornecedoresSublocacao(callback)
  │     └── GET /api/sublocacao/fornecedores → popula select #sublocacao-fornecedor
  └── carregarVinculos(itemId)
        └── GET /api/sublocacao/list/{itemId} → renderizarVinculos(data)
```

Vincular (`edit-sublocacao.php:217` → `vincularSublocador()`):

```
vincularSublocador()
  ├── Lê: itemId, fornId, catItemId, qtd, valor
  ├── Validações
  ├── POST /api/sublocacao/vincular
  │     └── SublocacaoApiController::vincular()
  │           └── SublocacaoService::vincularSublocador()
  │                 └── ProdutoEventoSublocacaoRepository::create()
  │                       └── INSERT INTO produto_evento_sublocacao (...)
  ├── Se sucesso:
  │     ├── Recarrega vínculos (carregarVinculos ou renderizar do response)
  │     └── atualizarBadgeSublocacao(itemId)
  └── Se erro: showToast
```

Remover (`edit-sublocacao.php:257` → `removerVinculo()`):

```
removerVinculo(id)
  ├── confirm()
  ├── POST /api/sublocacao/desvincular/{id}
  │     └── SublocacaoService::desvincularSublocador()
  │           └── ProdutoEventoSublocacaoRepository::delete(id)
  ├── Recarrega vínculos
  └── Atualiza badge
```

### 5.4 Gerar Contas a Pagar

Botão "Gerar Contas a Pagar dos Sublocados" → `gerarContasSublocacao()`:

```
gerarContasSublocacao()
  ├── confirm()
  ├── POST /api/sublocacao/gerar-contas/{EVENTO_ID}
  │     └── SublocacaoApiController::gerarContas()
  │           └── SublocacaoService::gerarContasPagar(idEvento)
  │                 ├── Busca evento (para data_fim + nome)
  │                 ├── Busca vínculos (ProdutoEventoSublocacaoRepository::findByEvento)
  │                 ├── Para cada vínculo:
  │                 │     ├── Pula status='cancelado' ou valor <= 0
  │                 │     ├── Calcula vencimento = data_fim + 30 dias
  │                 │     └── INSERT INTO contas_pagar (tipo='fornecedor', id_fornecedor, evento_id,
  │                 │           descricao="Sublocação: {produto} - {fornecedor} - {evento}",
  │                 │           valor, data_vencimento, status='PENDENTE')
  │                 └── Retorna { success, contas[], errors[] }
  └── Se sucesso: showToast + redirect para /contas-pagar após 2s
```

---

## 6. Fluxo do EVENTO_ID na Sublocação

### Caminho do ID

```
Controller: EventoController::edit($id)
  └─ $evento['id'] = $id
       └─ edit.php: $eventoId = $evento['id'] ?? 0           (linha 12)
            └─ <script> var EVENTO_ID = <?= (int)$eventoId ?> (linha 41)
                 └─ window.EVENTO_ID = EVENTO_ID               (linha 59)
```

### Uso do ID em cada camada

| Camada | Como usa | Arquivo:linha |
|--------|----------|--------------|
| **PHP (server-side)** | `$evento['id']` disponível em todos os partials | Controller passa `'evento' => $evento` |
| **JS - tabs.js** | `EVENTO_ID` (global) + `window.EVENTO_ID` (fallback) | `tabs.js:67` |
| **JS - edit-sublocacao.php (inline)** | `EVENTO_ID` (global) | `edit-sublocacao.php:309` (gerarContasSublocacao) |
| **AJAX - fornecedoresTabHtml** | Rota `GET /eventos/fornecedores-html/{id}` | `tabs.js:71` |
| **AJAX - gerarContas** | Rota `POST /api/sublocacao/gerar-contas/{idEvento}` | `edit-sublocacao.php:309` |

### Pontos de falha do ID

1. **`$evento['id']` é 0/null**: `EVENTO_ID = 0` → `refreshSublocacaoTab()` em `tabs.js:68` retorna imediatamente (`if (!eventoId) return;`) → aba Sublocação nunca carrega conteúdo
2. **Partial `edit-sublocacao.php` ausente no servidor**: Pane 4 existe mas está vazio → `#sublocacao-container` não existe → `refreshSublocacaoTab()` não encontra o container (`tabs.js:66`)
3. **Partial `edit-sublocacao-content.php` ausente**: O servidor renderiza pane 4 vazio → conteúdo só aparece ao clicar na aba (AJAX) → se o arquivo também faltar no servidor, `fornecedoresTabHtml()` retorna erro
4. **Rotas `/api/sublocacao/*` ausentes**: Modal abre mas chamadas API falham → erros no console, `showToast` vermelho

---

## 7. JavaScript — Funções e Dependências

### Funções definidas em `edit-sublocacao.php` (inline, linhas 76–343)

| Função | Escopo | Depende de |
|--------|--------|-----------|
| `carregarFornecedoresSublocacao(cb)` | global | `BASE_URL`, API `/api/sublocacao/fornecedores` |
| `popularSelectFornecedores(lista)` | global | DOM `#sublocacao-fornecedor` |
| `carregarItensSublocador(idFornecedor)` | global | `BASE_URL`, API `/api/sublocacao/itens/{id}` |
| `preencherDadosItem(itemId)` | global | DOM `#sublocacao-quantidade`, `#sublocacao-valor-unit` |
| `abrirModalSublocacao(itemId, nomeProduto)` | global | `carregarFornecedoresSublocacao`, `carregarVinculos` |
| `fecharModalSublocacao()` | global | DOM `#modal-sublocacao` |
| `carregarVinculos(itemId)` | global | `BASE_URL`, API `/api/sublocacao/list/{id}` |
| `renderizarVinculos(vinculos)` | global | DOM `#sublocacao-lista-vinculos` |
| `vincularSublocador()` | global | `CSRF_TOKEN`, API `/api/sublocacao/vincular`, `atualizarBadgeSublocacao` |
| `removerVinculo(id)` | global | `CSRF_TOKEN`, API `/api/sublocacao/desvincular/{id}`, `atualizarBadgeSublocacao` |
| `atualizarBadgeSublocacao(itemId)` | global | `BASE_URL`, API `/api/sublocacao/list/{id}` |
| `carregarTodosBadgesSublocacao()` | global | Chamado por `refreshSublocacaoTab()` e `DOMContentLoaded` |
| `gerarContasSublocacao()` | global | `CSRF_TOKEN`, `EVENTO_ID`, API `/api/sublocacao/gerar-contas/{id}` |
| `esc(text)` | global | Helper HTML escape |

### Funções em `tabs.js`

| Função | Descrição |
|--------|-----------|
| `switchHTab(idx, el)` | Troca aba horizontal. Se `idx === 4` → chama `refreshSublocacaoTab()` |
| `refreshSublocacaoTab()` | Fetch AJAX do HTML da aba Sublocação. Injeta em `#sublocacao-container` |

### Funções em `fornecedores.js`

| Função | Descrição |
|--------|-----------|
| `abrirCotacao()` | Navega para tela de cotação |
| `abrirModalSolicitacao()` | Modal de solicitação de cotação a fornecedores |
| `enviarSolicitacao()` | Envia cotação em massa via `/cotacao/solicitar-cotacao` |

---

## 8. Variáveis Globais Necessárias

Todas as funções JS do módulo assumem estas variáveis no escopo global:

| Variável | Origem | Definida em |
|----------|--------|------------|
| `EVENTO_ID` | `edit.php:41` | ID do evento (`$evento['id']`) |
| `window.EVENTO_ID` | `edit.php:59` | Cópia em window |
| `CSRF_TOKEN` | `edit.php:42` | Token CSRF |
| `window.CSRF_TOKEN` | `edit.php:61` | Cópia em window |
| `BASE_URL` | `edit.php:40` | URL base do sistema |
| `window.BASE_URL` | `edit.php:60` | Cópia em window |
| `SALAS_MAP` | `edit.php:53-57` | Mapa `{ id: { nome, obs, produtos } }` |

---

## 9. Troubleshooting

### Aba Sublocação não carrega ao clicar

1. Verificar console: `console.log(EVENTO_ID)` — se for `0` ou `undefined`, o `switchHTab` nem tenta o fetch
2. Verificar network: requisição para `/eventos/fornecedores-html/{id}` — status 404 = rota ausente; status 500 = erro no PHP
3. Verificar DOM: `document.getElementById('sublocacao-container')` — se null, o partial `edit-sublocacao.php` não foi incluído

### Modal abre mas não carrega fornecedores

1. Network: `GET /api/sublocacao/fornecedores` — 404 = rota ausente; 403 = sem permissão RBAC
2. Console: `_sublocacaoFornecedoresCache` — se cache vazio, API retornou array vazio

### Vincular sublocador falha

1. Network: `POST /api/sublocacao/vincular` — verificar payload e resposta
2. CSRF: token inválido → 400 Bad Request
3. Console: verificar mensagem `data.error` do response

### Gerar Contas não funciona

1. `EVENTO_ID` precisa ser válido e > 0
2. Network: `POST /api/sublocacao/gerar-contas/{id}` — verificar resposta
3. Verificar se existem vínculos ativos: `GET /api/sublocacao/list/{idProdutoEvento}` para cada item

---

## 10. Catálogo de Itens do Sublocador (fora do evento)

Gerenciado em `views/fornecedor/edit.php` (card "Itens para Sublocação"):

### Rotas (grupo `/fornecedores/{id}/itens`)

| Método | Rota | Controller::method |
|--------|------|-------------------|
| GET | `/fornecedores/{id}/itens` | SublocacaoItemController::list |
| POST | `/fornecedores/{id}/itens/store` | SublocacaoItemController::store |
| POST | `/fornecedores/{id}/itens/update/{itemId}` | SublocacaoItemController::update |
| POST | `/fornecedores/{id}/itens/delete/{itemId}` | SublocacaoItemController::delete |

### JS inline (edit.php do fornecedor, linhas 808-891)

- `abrirModalItemSublocacao(fornId)` — abre modal de cadastro
- `editarItemSublocacao(id, fornId, dados)` — preenche modal para edição
- `salvarItemSublocacao(fornId)` — POST store ou update
- `excluirItemSublocacao(id, fornId)` — POST delete

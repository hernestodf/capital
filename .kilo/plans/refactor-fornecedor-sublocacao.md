# Plano: Refatorar Fornecedores em Eventos + Simplificar Sublocação

## Objetivo
Remover o conceito de "fornecedor vencedor" único de Salas e Produtos, simplificar a aba Sublocação para CRUD direto de itens sublocados (com produto digitável e código de barras), e ajustar o Fechamento para enviar esses itens ao contas a pagar.

---

## Fase 1 — Salas e Produtos (remover referências a fornecedor)

### 1.1 `views/evento/partials/edit-salas-produtos.php`
- **Linhas 230-234:** Remover badge `fornecedor_vencedor`
- **Linhas 257-259:** Remover botão "Definir Fornecedor" (`abrirModalVencedor`)
- **Linha 102:** Remover input `valor_custo_fornecedor` do form "Adicionar Item"

### 1.2 `public/js/eventos/salas-produtos.js`
- Remover: `abrirModalVencedor`, `criarModalVencedor`, `_mvrConfirmar`, `carregarFornecedores`, `popularSelectFornecedor`, `_fornecedoresCache`
- Remover badge `fornecedor_vencedor` da renderização inline (`criarElementoItem`, linha 357-360)
- Remover botão "Definir Fornecedor" da renderização inline (linha 373)
- Remover chamada `refreshFornecedoresTab()` (linhas 480, 559)

### 1.3 `public/js/eventos/salas-produtos-events.js`
- **Linha 84:** Remover handler `.btn-abrir-vencedor`

### 1.4 `src/Controllers/EventoController.php`
- **Linhas 213-215:** Remover carregamento de `$fornecedores` no `edit()`
- **Linhas 223-252:** Remover método `fornecedoresTabHtml()`

### 1.5 Remover arquivos mortos da cotação/fornecedores (Fase 1.5)
- `views/evento/partials/edit-fornecedores.php` — REMOVER
- `views/evento/partials/edit-fornecedores-content.php` — REMOVER
- `public/js/eventos/fornecedores.js` — REMOVER
- ~~Cotação removida depois (Fase 4)~~ → Mantida por enquanto para não quebrar nada; only remove the vencedor-rapido flow

---

## Fase 2 — Sublocação (simplificar CRUD + remover alocação)

### 2.1 `views/evento/edit.php`
- **Linha 97-99:** Renomear HTab label `"Fornecedores"` → `"Sublocação"`
- Remover carregamento de `fornecedores.js` do `<script>` list

### 2.2 `views/evento/partials/edit-sublocacao.php`

**Remover seção "Estoque Próprio"** (linhas 121-134):
- Bloco "Estoque Próprio" com badge count
- Lista `#alocacao-lista`
- Botão "Alocar Seriais"
- Divider antes

**Simplificar modal "Sublocação"** (linhas 152-198):

Novo layout do formulário:
| Campo | Tipo | Origem |
|-------|------|--------|
| Sublocador | Select | `/api/sublocacao/fornecedores` (mantido) |
| Produto | Text (input livre) | **Novo** — usuário digita o nome |
| Código de Barras | Text | **Novo** |
| Quantidade | Number | Mantido |
| Valor Unitário (R$) | Decimal | Mantido |
| Serial Fornecedor | Text | Mantido (opcional) |
| Custo Unitário (R$) | Decimal | Mantido |

**Alterações no JS inline (linhas 211-502):**
- Remover `onchange="carregarItensSublocador(this.value)"` do select de fornecedor
- Remover função `carregarItensSublocador()` — não precisa mais carregar itens do catálogo
- Remover função `preencherDadosItem()` — não precisa mais
- Remover função `alocarSeriaisSelecionados()` e relacionadas
- Remover referências a `abrirModalAlocarSerial`
- `vincularSublocador()`: ajustar para enviar `produto` (texto digitado) + `codigo_barras` + demais campos
- `carregarVinculos()`: adicionar `produto` e `codigo_barras` nas colunas

### 2.3 Banco de Dados — Migration 037

```sql
ALTER TABLE produto_evento_sublocacao
    ADD COLUMN IF NOT EXISTS codigo_barras VARCHAR(100) DEFAULT NULL AFTER id_sublocacao_item,
    ADD COLUMN IF NOT EXISTS produto VARCHAR(255) DEFAULT NULL AFTER codigo_barras;
```

### 2.4 `src/Controllers/Api/SublocacaoApiController.php`

**`vincular()`:**
- Aceitar `produto` (string) e `codigo_barras` (string) nos params
- Salvar no INSERT

**`listVinculos()`:**
- Incluir `codigo_barras` e `produto` no SELECT/retorno

### 2.5 `src/Service/SublocacaoService.php`

**`vincularSublocador()`:**
- Parâmetros: adicionar `produto`, `codigo_barras`
- Inserir no INSERT

**`findByProdutoEvento()`:**
- Incluir `codigo_barras` e `produto` no SELECT

### 2.6 `public/js/eventos/alocacao-estoque.js`
- REMOVER (estoque próprio sai da Sublocação, fica só na Montagem)

### 2.7 Ajustar exibição na listagem principal da Sublocação
Na view `edit-sublocacao.php` (linhas 1-105, a listagem por sala):
- Remover colunas `qtd_alocada` e `faltante` da tabela de itens
- Manter colunas: Produto, Qtd, Qtd Sublocada (agora é a qtd total dos vínculos), ações

---

## Fase 3 — Fechamento + Contas a Pagar

### 3.1 `src/Repository/FechamentoRepository.php`

**`listarFornecedoresVencedores()` (linhas 72-111):**
- Refatorar para ler de `produto_evento_sublocacao` em vez de `item_cotacoes` + `fornecedor_vencedor`
- JOIN: `produto_evento_sublocacao` → `fornecedores` → `produtos_evento`
- Agrupar por fornecedor
- Verificar `contas_pagar` onde `tipo = 'fornecedor' AND referencia_id = produto_evento_sublocacao.id`

**`getTotais()` (linha 150-154):**
- Trocar `fornecedor_vencedor IS NOT NULL` por SUM de `produto_evento_sublocacao.total WHERE status != 'cancelado'`

### 3.2 `src/Controllers/FechamentoController.php`

**`fornecedores()` (linha 157):**
- Atualizar para chamar novo método do repositório

**`fornecedorPagamento()`:**
- Ajustar `referencia_id` para apontar para `produto_evento_sublocacao.id` em vez de `item_cotacoes.id`

### 3.3 DB — migration para manter compatibilidade
Manter `produto_evento_sublocacao.status` como `'pendente' | 'pago' | 'cancelado'`.
Após gerar contas, status muda para `'pago'`.

### 3.4 `views/evento/partials/edit-fechamento-fornecedores.php`
- Manter o modal de pagamento (à vista/parcelado) pois é útil
- Ajustar labels e data attributes conforme novo fluxo

### 3.5 `public/js/eventos/fechamento.js`
- `loadFornecedores()` / `renderFornecedores()`: ajustar para exibir os itens da sublocação (fornecedor + produto + qtd + valor)
- Manter lógica de envio ao contas a pagar

---

## Fase 4 — Remover Código Morto (sistema de cotação)

### 4.1 Arquivos para remover (após validar que não são usados em outro lugar):
- `src/Controllers/CotacaoController.php` — REMOVER
- `src/Repository/CotacaoRepository.php` — REMOVER
- `views/evento/cotacao.php` — REMOVER
- `public/js/cotacao.js` — REMOVER
- `database/migrations/017_create_cotacoes_tables.sql` — manter apenas por histórico (não remover migration executada)

### 4.2 Rotas para remover:
- `/cotacao/vencedor-rapido` (POST)
- `/cotacao/fornecedores-lista` (GET)
- `/cotacao/solicitar-cotacao` (POST)
- `/cotacao/upload-anexo` (POST)
- `/eventos/cotacao/{idEvento}/{idProdutoEvento}` (GET)
- `/eventos/fornecedores-html/{id}` (GET)
- `/cotacao/*` restante

### 4.3 Coluna `fornecedor_vencedor` em `produtos_evento`:
- Manter no banco (não quebrar existentes), ignorar no código

---

## Resumo das alterações no Modal Sublocação (antes → depois)

| Campo | Antes | Depois |
|-------|-------|--------|
| Sublocador | Select (obrigatório) | Select (mantido) |
| Item do Catálogo | Select com carregamento dinâmico | **Removido** |
| Produto | Campo "Produto Fornecedor" (texto secundário) | **Campo principal** (texto livre, destaque) |
| Código de Barras | — | **Novo** |
| Quantidade | Input number | Mantido |
| Valor Unitário | Input decimal | Mantido |
| Serial Fornecedor | Input text (secundário) | Mantido (opcional) |
| Custo Unitário | Input decimal | Mantido |
| Alocar Estoque | Seção completa | **Removido** |

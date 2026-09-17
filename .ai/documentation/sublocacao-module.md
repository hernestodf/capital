# Módulo Sublocação

Implementado em 2026-06-09. Migration `032_criar_sublocacao.sql` executada na produção.

## Conceito

Um item em `produtos_evento` (ex: "Nobreak 60 un.") pode ter **N sublocadores**, cada um com sua própria **quantidade**, **valor unitário** e **total**. Cada sublocador gera uma conta a pagar separada.

## Tabelas

### `sublocacao_itens`
Catálogo de itens que cada sublocador oferece. Gerenciado no formulário de edição do fornecedor (aba "Itens para Sublocação").

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT PK | Auto incremento |
| id_fornecedor | INT FK → fornecedores | Sublocador dono do item |
| produto | VARCHAR(255) | Nome do produto |
| codigo | VARCHAR(100) | Código interno do sublocador |
| quantidade | DECIMAL(10,2) | Quantidade padrão |
| valor_unit | DECIMAL(10,2) | Valor unitário padrão |
| observacao | TEXT | Observações |
| status | TINYINT(1) | 1=ativo, 0=inativo |

### `produto_evento_sublocacao`
Junção entre `produtos_evento` e `fornecedores` — cada linha é um sublocador vinculado a um item do evento.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT PK | Auto incremento |
| id_produto_evento | INT FK → produtos_evento | Item do evento |
| id_fornecedor | INT FK → fornecedores | Sublocador |
| id_sublocacao_item | INT FK → sublocacao_itens | Item do catálogo (opcional) |
| quantidade | DECIMAL(10,2) | Quantidade sublocada |
| valor_unit | DECIMAL(10,2) | Valor unitário acordado |
| total | DECIMAL(10,2) | quantidade * valor_unit |
| status | ENUM('pendente','pago','cancelado') | Status do vínculo |

## Arquivos

### Criados (7)
| Arquivo | Responsabilidade |
|---------|-----------------|
| `database/migrations/032_criar_sublocacao.sql` | Cria as 2 tabelas |
| `src/Repository/SublocacaoItemRepository.php` | CRUD + findByFornecedor, countByFornecedor |
| `src/Repository/ProdutoEventoSublocacaoRepository.php` | CRUD + findByProdutoEvento, findByEvento, countByProdutoEvento |
| `src/Service/SublocacaoItemService.php` | Wrapper de negócio para sublocacao_itens |
| `src/Service/SublocacaoService.php` | Vincular/desvincular sublocadores, gerar contas a pagar |
| `src/Controllers/Api/SublocacaoApiController.php` | 6 endpoints JSON |
| `src/Controllers/SublocacaoItemController.php` | 4 endpoints CRUD para itens do catálogo |

### Modificados (9)
| Arquivo | Mudança |
|---------|---------|
| `public/index.php` | Rotas do grupo `/api/sublocacao` e `/fornecedores/{id}/itens` |
| `src/Repository/ContasPagarRepository.php` | Fillable: adicionado tipo, evento_id, referencia_id |
| `src/Repository/FornecedorRepository.php` | Subquery `total_itens_sublocacao` no all() |
| `src/Controllers/FornecedorController.php` | Injeta SublocacaoItemService, passa itens p/ view |
| `views/layout/sidebar.php` | Label "Fornecedores" → "Sublocação" |
| `views/fornecedor/index.php` | Título, coluna "Itens Sublocação", labels |
| `views/fornecedor/edit.php` | Card "Itens para Sublocação" com CRUD + modal |
| `views/evento/partials/edit-sublocacao.php` | **Nova aba Sublocação** (pane 4) — lista de itens, botão Sublocar, modal vínculo, Gerar Contas |
| `views/evento/edit.php` | Pane 4 alterado de `edit-fornecedores.php` para `edit-sublocacao.php`; label da tab renomeada |
| `views/evento/partials/edit-salas-produtos.php` | Sublocação removida (tudo movido para aba própria) |
| `public/js/eventos/salas-produtos.js` | Sublocação removida (tudo movido para aba própria) |

## API Endpoints

### Grupo: `/api/sublocacao` (com Auth + CSRF)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/itens/{idFornecedor}` | Catálogo de itens do sublocador |
| GET | `/list/{idProdutoEvento}` | Vínculos de um item do evento |
| POST | `/vincular` | Vincula sublocador a item |
| POST | `/desvincular/{id}` | Remove vínculo |
| POST | `/gerar-contas/{idEvento}` | Gera contas_pagar para todos os vínculos |
| GET | `/fornecedores` | Lista fornecedores ativos para select |

### Grupo: `/fornecedores/{id}` (com Auth + CSRF)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/itens` | Lista itens do catálogo do sublocador |
| POST | `/itens/store` | Cria item no catálogo |
| POST | `/itens/update/{itemId}` | Atualiza item |
| POST | `/itens/delete/{itemId}` | Exclui item |

## Fluxo de Uso

1. **Cadastro Sublocador** → Form fornecedor (sidebar "Sublocação")
2. **Catálogo de Itens** → No edit do sublocador, aba "Itens para Sublocação" cadastra produtos que ele fornece
3. **Evento → Salas e Produtos** → Adiciona item normalmente
4. **Aba Sublocação** → Navega para a aba "Sublocação" no evento
5. **Vincular Sublocadores** → Clica em "Sublocar" no item → modal → seleciona sublocador + quantidade + valor
6. **Gerar Contas a Pagar** → Botão "Gerar Contas a Pagar dos Sublocados" na aba → cria contas_pagar com tipo='fornecedor', vencimento = data_fim + 30 dias
6. **Pagamento** → Em contas_pagar, realiza o pagamento normalmente

## Geração de Contas a Pagar

A função `SublocacaoService::gerarContasPagar()`:
- Percorre todos os vínculos ativos do evento
- Calcula `total = quantidade * valor_unit` para cada um
- Cria `contas_pagar` com:
  - `tipo = 'fornecedor'`
  - `id_fornecedor` do sublocador
  - `evento_id` do evento
  - `descricao = "Sublocação: {produto} - {fornecedor} - {evento}"`
  - `valor = total`
  - `data_vencimento = data_fim + 30 dias`
  - `status = 'PENDENTE'`

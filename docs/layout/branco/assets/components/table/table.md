# Table Component

## Uso
- Listagem de dados tabulares
- Relatorios e resumos
- Tabelas de CRUD com acoes
- Dados ordenaveis e interativos

## Classes Base

### Container e Tabela
- `tbl-container` - Container com toolbar (busca + per-page), tabela e paginacao
- `table-wrap` - Wrapper com scroll horizontal
- `data-table` - Tabela principal
- `data-table striped` - Linhas alternadas (zebra)
- `data-table hoverable` - Destaque no hover

### Cabeçalho
- `th-sort` - Header ordenavel
- `sort-ico` - Icone de ordenacao

### Celulas
- `td-name` - Celula de nome (bold)
- `td-mono` - Celula monoespacada (valores, IDs)

### Acoes
- `td-actions` - Container flex dos botoes de acao por linha
- `td-act-menu` - Wrapper do dropdown de acoes (escondido por padrao)
- `td-act-toggle` - Botao "..." / "Acoes" que abre o dropdown
- `td-act-toggle-label` - Label "Acoes" (visivel em viewport <= 1366px)
- `td-act-dropdown` - Menu dropdown com opcoes de acao
- `td-act-item` - Item dentro do dropdown
- `td-act-compact` - Classe no menu quando ha 2 ou menos acoes (dropdown oculto ate viewport pequeno)
- `td-act-red/purple/green/cyan` - Cores de hover por variant no dropdown

### Toolbar
- `tbl-toolbar` - Barra de busca + seletor por pagina
- `tbl-search` - Input de busca com icone
- `tbl-search-input` - Campo de texto da busca
- `tbl-per-page` - Seletor de linhas por pagina
- `tbl-per-page-select` - Select dropdown do per-page

### Paginacao
- `tbl-pagination` - Rodape com info + botoes de pagina
- `tbl-info` - Texto "Mostrando X-Y de Z"
- `tbl-pages` - Container dos botoes de pagina
- `tbl-page-btn` - Botao de pagina
- `tbl-page-btn active` - Pagina atual
- `tbl-page-btn:disabled` - Botoes anterior/proximo desabilitados
- `tbl-no-results` - Mensagem "Nenhum resultado encontrado"

## Comportamento de Acoes

O `renderTable()` gera automaticamente botoes de acao com dropdown inteligente:

### 2 acoes ou menos (ex: confirm = Aprovar + Rejeitar)
- Botoes inline visiveis em viewport grande
- Dropdown `.td-act-compact` com TODAS as acoes (oculto ate viewport <= 1280px)
- Em 1280px: botoes inline desaparecem, botao "Acoes" aparece

### 3+ acoes (ex: crud = Ver + Editar + Excluir)
- 2 primeiros botoes inline + botao "..." com dropdown
- Dropdown contem TODAS as acoes (incluindo as 2 primeiras)
- Em 1280px: botoes inline desaparecem, botao "Acoes" aparece

### Posicionamento do Dropdown
- Usa `position:fixed` quando aberto para escapar de `overflow:hidden`
- JS (`togglePop`) calcula coordenadas via `getBoundingClientRect()`
- Atualiza posicao no evento `scroll`

## Presets de Acoes (`renderTableActions`)

| Preset     | Botoes                                          |
|------------|-------------------------------------------------|
| `default`  | Editar (cyan) + Excluir (red)                   |
| `crud`     | Ver (green) + Editar (purple) + Excluir (red)   |
| `confirm`  | Aprovar (green) + Rejeitar (red)                |

## Exemplos

### Tabela com CRUD + Busca + Paginacao
```php
echo renderTable([
    'id'               => 'tbl-locacoes',
    'searchable'       => true,
    'searchPlaceholder'=> 'Buscar cliente...',
    'paginated'        => true,
    'perPage'          => 8,
    'headers' => [
        ['label' => 'Cliente', 'sortable' => true],
        ['label' => 'Equipamento', 'sortable' => true],
        ['label' => 'Valor', 'sortable' => true],
        ['label' => 'Status']
    ],
    'actionBtns' => renderTableActions('crud'),
    'rows' => [
        ['Construtora Nova Era', 'Andaime 10m', ['html'=>true,'content'=>'<span class="td-mono">R$ 4.200</span>'], ['html'=>true,'content'=>renderBadge(['label'=>'Ativo','variant'=>'green','size'=>'sm','pulse'=>true])]],
    ]
]);
```

### Tabela com Confirmacao
```php
echo renderTable([
    'id'               => 'tbl-pendencias',
    'searchable'       => true,
    'paginated'        => true,
    'perPage'          => 5,
    'headers' => [
        ['label' => 'Solicitacao', 'sortable' => true],
        ['label' => 'Status']
    ],
    'actionBtns' => renderTableActions('confirm'),
    'rows' => [
        ['LOC-2026-0041', ['html'=>true,'content'=>renderBadge(['label'=>'Aguardando','variant'=>'yellow','size'=>'sm'])]],
    ]
]);
```

### Tabela Simples (sem busca/paginacao/acoes)
```php
echo renderTable([
    'id'      => 'tbl-resumo',
    'headers' => [
        ['label' => 'Mes', 'sortable' => true],
        ['label' => 'Receita', 'sortable' => true]
    ],
    'rows' => [
        ['Janeiro', 'R$ 38.400'],
        ['Fevereiro', 'R$ 41.200']
    ]
]);
```

### Celulas com Classes Especiais
```php
'rows' => [
    [
        ['html'=>true,'content'=>'<span class="td-name">Joao Silva</span>'],
        ['html'=>true,'content'=>'<span class="td-mono">USR-001</span>'],
        ['html'=>true,'content'=>renderBadge(['label'=>'Ativo','variant'=>'green','size'=>'sm'])]
    ],
]
```

### Acoes Customizadas
```php
'actionBtns' => [
    ['label'=>'Ver','variant'=>'green','size'=>'sm','icon'=>'<svg ...>...</svg>'],
    ['label'=>'Editar','variant'=>'purple','size'=>'sm','icon'=>'<svg ...>...</svg>'],
    ['label'=>'Excluir','variant'=>'red','size'=>'sm','icon'=>'<svg ...>...</svg>','onclick'=>'deleteItem(1)'],
]
```

## Parametros

| Parametro           | Tipo   | Padrao           | Descricao                                           |
|---------------------|--------|------------------|-----------------------------------------------------|
| `headers`           | array  | `[]`             | Array de `['label' => string, 'sortable' => bool]`  |
| `rows`              | array  | `[]`             | Array de arrays (string ou `['html' => true, 'content' => '...']`) |
| `id`                | string | `''`             | ID da tabela para sorting/paginacao/busca           |
| `striped`           | bool   | `true`           | Linhas alternadas (zebra)                           |
| `hoverable`         | bool   | `true`           | Destaque no hover                                   |
| `searchable`        | bool   | `false`          | Exibir input de busca                               |
| `searchPlaceholder` | string | `'Buscar...'`    | Placeholder do campo de busca                       |
| `paginated`         | bool   | `false`          | Habilitar paginacao                                 |
| `perPage`           | int    | `5`              | Linhas por pagina                                   |
| `perPageOptions`    | array  | `[5,10,25,50]`   | Opcoes do seletor por pagina                        |
| `actionBtns`        | array  | `[]`             | Array de botoes de acao ou resultado de `renderTableActions()` |

## Funcoes JS (scripts.js)

| Funcao            | Descricao                                    |
|-------------------|----------------------------------------------|
| `tblSearch(id,val)` | Filtra linhas da tabela pelo texto           |
| `tblSetPerPage(id,val)` | Altera quantidade de linhas por pagina   |
| `tblGoToPage(id,page)` | Navega para pagina especifica             |
| `sortTable(id,col)` | Ordena tabela pela coluna (asc/desc)        |
| `togglePop(id)` | Abre/fecha dropdown de acoes (position:fixed)|

## Regras para IA
1. Usar `sortable => true` em colunas numericas e alfabeticas
2. Celulas com badges, botoes ou HTML devem usar `['html' => true, 'content' => '...']`
3. Celulas de texto simples podem usar string direto
4. Usar `striped => false` em tabelas com muitas colunas coloridas
5. Usar `id` quando a tabela precisa de sorting, busca ou paginacao via JS
6. Combinar com `renderBadge` para colunas de status
7. Usar `td-name` para nomes em destaque dentro de celulas
8. Usar `td-mono` para valores numericos, IDs e codigos
9. Usar `renderTableActions('crud'|'confirm'|'default')` para presets de acoes
10. Para acoes customizadas, cada botao aceita: `label`, `variant`, `size`, `icon`, `onclick`
11. O dropdown de acoes e automatico: 2 ou menos = inline, 3+ = inline + dropdown
12. Em viewport <= 1280px, todas as acoes migram para o dropdown "Acoes"

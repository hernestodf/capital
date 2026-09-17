# Design System 3.0 - Componentes SisLoc

## VISÃO GERAL

O SisLoc utiliza o **Design System 3.0 "White Rabbit"** - um sistema completo de componentes PHP com render functions, CSS variables e JavaScript vanilla.

**Localização:** `/var/www/html/sisloc/docs/layout/branco/`

**SEMPRE usar estes componentes ao criar interfaces. NUNCA criar estilos inline ou componentes do zero sem verificar se já existe.**

---

## REGISTRO DE COMPONENTES (32 componentes)

### 1. Button (`assets/components/button`)
- **Função PHP:** `renderButton($config)`
- **Classes:** `btn`, `btn-sm`, `btn-lg`, `btn-xl`, `btn-icon`
- **Variantes:** `btn-cyan` (primário), `btn-green` (sucesso), `btn-red` (erro), `btn-blue`, `btn-purple`, `btn-orange`, `btn-yellow`, `btn-ghost`, `btn-ghost-cyan`, `btn-ghost-red`, `btn-outline-cyan`
- **Estados:** normal, hover, loading (com `btn-spin`), disabled
- **Uso:** Ações principais, submit, navegação, gatilho de modais

### 2. Modal (`assets/components/modal`)
- **Funções PHP:** `renderModal()`, `renderModalConfirm()`, `renderModalSuccess()`, `renderModalForm()`
- **Classes:** `modal-overlay`, `modal`, `modal-sm`, `modal-md`, `modal-lg`, `modal-header`, `modal-body`, `modal-footer`, `modal-icon`, `modal-title`, `modal-close`
- **JS:** `openModal(id)`, `closeModal(id)`, `closeModalOutside(event, id)`
- **Uso:** Diálogos, confirmações, formulários popup

### 3. Input (`assets/components/input`)
- **Funções PHP:** `renderInput()`, `renderFormRow()`
- **Classes:** `fg` (form group), `fl` (form label), `fi` (form input)
- **Tipos:** text, email, password, number, tel, url, select, textarea
- **Estados:** default, focus, disabled, error, success
- **Uso:** Todos os campos de formulário

### 4. Table (`assets/components/table`)
- **Funções PHP:** `renderTable()`, `renderTableActions()`
- **Classes:** `data-table`, `tbl-container`, `table-wrap`, `th-sort`, `td-name`, `td-mono`, `td-actions`
- **Presets de ações:** `renderTableActions('crud')`, `renderTableActions('confirm')`, `renderTableActions('default')`
- **JS:** `tblSearch(id,val)`, `tblSetPerPage(id,val)`, `tblGoToPage(id,page)`, `sortTable(id,col)`
- **Features:** sorting, busca, paginação, dropdown de ações responsivo
- **Uso:** Listagens, relatórios, CRUD

### 5. Card (`assets/components/card`)
- **Funções PHP:** `renderCard()`, `renderCardStat()`, `renderCustomCard()`
- **Classes:** `card`, `card-body`, `card-header`, `card-footer`, `card-stat`, `card-glow`
- **Uso:** Containers de conteúdo, KPIs, estatísticas

### 6. Badge (`assets/components/badge`)
- **Função PHP:** `renderBadge($config)`
- **Classes:** `badge`, `badge-sm`, `badge-lg`, `badge-{cor}`, `dot pulse`
- **Cores:** red, green, cyan, blue, purple, orange, yellow
- **Uso:** Indicadores de status, contadores, labels

### 7. Alert (`assets/components/alert`)
- **Função PHP:** `renderAlert($config)`
- **Classes:** `alert`, `alert-{cor}`
- **Variantes:** alert-red, alert-green, alert-yellow, alert-cyan, alert-blue, alert-purple
- **Uso:** Notificações inline, mensagens de erro/sucesso/aviso

### 8. Toast (`assets/components/toast`)
- **Funções PHP:** `renderToastTrigger()`, `renderToastContainer()`
- **JS:** `showToast(type, title, msg, duration)`
- **Tipos:** red (erro), green (sucesso), cyan (info), yellow (atenção), purple (especial), orange (alerta)
- **Uso:** NOTIFICAÇÕES TEMPORÁRIAS - SEMPRE usar ao invés de alert()

### 9. Tabs (`assets/components/tabs`)
- **Funções PHP:** `renderTabsColor()`, `renderTabsUnderline()`, `renderTabsPill()`, `renderTabsSegmented()`, `renderTabsVertical()`
- **Classes:** `htabs-color`, `htabs-underline`, `htabs-pill`, `htabs-segmented`, `vtabs`
- **JS:** `switchTab(group, index)`
- **Uso:** Navegação entre seções

### 10. Accordion (`assets/components/accordion`)
- **Função PHP:** `renderAccordion($config)`
- **Classes:** `accordion-item`, `accordion-header`, `accordion-body`
- **Uso:** FAQ, configurações expansíveis, conteúdo colapsável

### 11. Sidebar (`assets/components/sidebar`)
- **Classes:** `#sidebar`, `.sb-full`, `.sb-mini`, `.ni`, `.si`, `.sub`, `.nav-lbl`
- **JS:** `toggleSidebar()`, `openSidebar()`, `closeSidebar()`, `toggleSub(id, el)`
- **Uso:** Navegação principal, menu lateral fixo

### 12. Avatar (`assets/components/avatar`)
- **Funções PHP:** `renderAvatar()`, `renderAvatarGroup()`
- **Classes:** `avatar`, `av-sm`, `av-lg`, `av-xl`, `av-online`, `av-busy`, `av-offline`, `avatar-group`
- **Uso:** Identificação de usuários, perfis

### 13. Toggle (`assets/components/toggle`)
- **Funções PHP:** `renderToggle()`, `renderToggleRow()`
- **Classes:** `tog`, `tog-s`, `tog-cyan`, `tog-red`
- **Uso:** Switches, ativar/desativar configurações

### 14. Progress (`assets/components/progress`)
- **Funções PHP:** `renderProgress()`, `renderProgressGroup()`
- **Classes:** `prog-wrap`, `prog-track`, `prog-bar`, `prog-thick`
- **JS:** `initProgressBars()`
- **Uso:** Barras de progresso, indicadores de completude

### 15. Spinner (`assets/components/spinner`)
- **Funções PHP:** `renderSpinner()`, `renderSpinnerDots()`, `renderSpinnerRing()`, `renderSpinnerBar()`, `renderSpinnerWithLabel()`
- **Classes:** `spinner-sm`, `spinner-md`, `spinner-lg`, `spinner-xl`, `spinner-dots`, `spinner-bar`, `spinner-ring`
- **Uso:** Indicadores de carregamento

### 16. Skeleton (`assets/components/skeleton`)
- **Funções PHP:** `renderSkeleton()`, `renderSkeletonCard()`, `renderSkeletonTable()`
- **Classes:** `skel`, `skel-block`, `skel-card`, `skel-row`, `skel-circle`
- **Uso:** Placeholders de loading

### 17. Chip (`assets/components/chip`)
- **Funções PHP:** `renderChip()`, `renderChipRemovable()`, `renderChipGroup()`
- **Classes:** `chip`, `chip-removable`, `chip-group`
- **JS:** `toggleChipSel()`, `removeChip()`
- **Uso:** Filtros, seleções múltiplas, tags

### 18. Tag (`assets/components/chip`)
- **Função PHP:** `renderTag($config)`
- **Classes:** `tag-{cor}`
- **Uso:** Etiquetas de status, labels informativos

### 19. Popover (`assets/components/popover`)
- **Função PHP:** `renderPopover($config)`
- **Classes:** `popover`, `popover-wrap`, `pop-bottom`
- **JS:** `togglePop(id)`
- **Uso:** Menus contextuais, dropdowns, informações extras

### 20. Timeline (`assets/components/timeline`)
- **Função PHP:** `renderTimeline($config)`
- **Classes:** `timeline`, `tl-item`, `tl-dot`
- **Uso:** Histórico de eventos, logs, cronologia

### 21. Stepper (`assets/components/stepper`)
- **Função PHP:** `renderStepper()` (via demo.php)
- **Classes:** `stepper`, `step-item`, `step-circle`, `step-pane`
- **JS:** `stepNext()`, `stepPrev()`, `updateStepper()`
- **Uso:** Formulários multi-etapa, wizards

### 22. Rating (`assets/components/rating`)
- **Função PHP:** `renderRating($config)`
- **Classes:** `rating`, `rating-sm`, `rating-lg`, `rating-group`, `star`
- **JS:** `setRating()`, `hoverRating()`, `leaveRating()`
- **Uso:** Avaliações, feedback de satisfação

### 23. List Item (`assets/components/listitem`)
- **Funções PHP:** `renderListItem()`, `renderListItemGroup()`
- **Classes:** `list-item`, `list-item-icon`, `list-item-body`, `list-item-divider`
- **Uso:** Listagens de itens, registros

### 24. Toast (`assets/components/toast`)
- **Funções PHP:** `renderToastTrigger()`, `renderToastContainer()`
- **JS:** `showToast(type, title, msg, duration)`, `dismissToast()`
- **Uso:** Notificações temporárias, feedback de ações

---

## CSS VARIABLES (DESIGN TOKENS)

```css
--neon-cyan / --neon-cyan-glow       /* Cor primária */
--neon-green / --neon-green-glow     /* Sucesso */
--neon-red / --neon-red-glow         /* Erro, perigo */
--neon-yellow / --neon-yellow-glow   /* Atenção */
--neon-blue / --neon-blue-glow       /* Processando */
--neon-purple / --neon-purple-glow   /* Especial */
--neon-orange / --neon-orange-glow   /* Alerta */
--bg-darkest: #EDF1F7               /* Fundo principal */
--bg-dark: #E0E8F2                  /* Fundo secundário */
--bg-surface: #D0DCF0               /* Superfícies */
--bg-card: #FFFFFF                  /* Cards */
--bg-elevated: #FFFFFF              /* Elementos elevados */
--bg-hover: #BFD0E8                 /* Hover state */
--bg-border: #9FB4CE               /* Bordas */
--bg-border-sub: #C4D4E6           /* Bordas subtis */
--text-1: #0D1829                  /* Texto principal */
--text-2: #1E2E45                  /* Texto secundário */
--text-3: #4A6080                  /* Texto terciário */
--text-4: #6B82A0                  /* Texto quaternário */
--z-base: 1
--z-dropdown: 1000
--z-popover: 2000
--z-tooltip: 3000
--z-modal: 4000
--z-toast: 5000
--sb-full: 260px                   /* Sidebar full width */
--sb-mini: 64px                    /* Sidebar collapsed */
--tb-h: 60px                       /* Topbar height */
```

---

## FUNÇÕES JAVASCRIPT GLOBAIS

| Função | Descrição |
|--------|-----------|
| `toggleSidebar()` | Abre/fecha sidebar |
| `openSidebar()` / `closeSidebar()` | Controla sidebar programaticamente |
| `toggleSub(id, el)` | Expande submenu no sidebar |
| `showSection(id, el)` | Navega entre seções |
| `openModal(id)` | Abre modal |
| `closeModal(id)` | Fecha modal |
| `closeModalOutside(event, id)` | Fecha modal ao clicar fora |
| `togglePop(id)` | Abre/fecha popover/dropdown |
| `showToast(type, title, msg, duration)` | Exibe toast notification |
| `dismissToast(el)` | Remove toast |
| `tblSearch(id, val)` | Filtra tabela |
| `tblSetPerPage(id, val)` | Altera linhas por página |
| `tblGoToPage(id, page)` | Navega para página |
| `sortTable(colIdx, btn)` | Ordena tabela |
| `switchTab(group, index)` | Troca tab genérico |
| `stepNext()` / `stepPrev()` | Navega stepper |
| `setRating(el, val)` | Define rating |
| `toggleChipSel(el)` | Seleciona/deseleciona chip |
| `removeChip(btn)` | Remove chip removível |
| `initProgressBars()` | Anima barras de progresso |

---

## SIGNIFICADO DAS CORES

| Cor | Uso | Componentes |
|-----|-----|-------------|
| Cyan | Primário, ações padrão, info | botões, headers, badges |
| Green | Sucesso, confirmar, ativo | botões, badges, alerts |
| Red | Erro, deletar, perigo, atraso | botões, badges, alerts |
| Yellow | Atenção, pendente, aviso | badges, alerts, chips |
| Purple | Especial, promoção, destaque | badges, alerts, timeline |
| Blue | Processando, em andamento | badges, alerts |
| Orange | Alerta importante | toast, button |

---

## REGRAS OBRIGATÓRIAS PARA IA

### NUNCA
1. Criar botão com estilos inline
2. Inventar novas cores (usar variáveis CSS)
3. Duplicar componentes existentes
4. Usar alert() (usar Toast)
5. Criar modais sem usar modal-overlay
6. Criar tabelas sem usar renderTable()
7. Criar formulários sem usar fg/fl/fi

### SEMPRE
1. Usar classes do design system
2. Seguir documentação .md de cada componente
3. Usar variáveis CSS (--neon-*, --bg-*, --text-*)
4. Incluir estados de loading em ações
5. Usar Toast para feedback de ações
6. Manter consistência visual
7. Usar aria-labels quando necessário
8. Verificar registry.json antes de criar novo componente

### PADRÕES DE FORMULÁRIO
```php
<!-- Estrutura padrão -->
<div class="fg">
  <div class="fl">Label</div>
  <input class="fi" type="text" placeholder="Placeholder"/>
</div>

<!-- Grid 2 colunas -->
<div class="col2">
  <div class="fg">...</div>
  <div class="fg">...</div>
</div>
```

### PADRÕES DE TABELA
```php
echo renderTable([
    'id' => 'tbl-exemplo',
    'searchable' => true,
    'paginated' => true,
    'perPage' => 10,
    'headers' => [['label' => 'Coluna', 'sortable' => true]],
    'actionBtns' => renderTableActions('crud'),
    'rows' => [
        ['Dado', ['html'=>true,'content'=>renderBadge(['label'=>'Ativo','variant'=>'green','size'=>'sm'])]]
    ]
]);
```

### PADRÕES DE FEEDBACK
```javascript
// Toast de sucesso
showToast('green', 'Sucesso!', 'Operação concluída.');

// Toast de erro
showToast('red', 'Erro!', 'Falha ao salvar.');

// Toast de info
showToast('cyan', 'Info', 'Dados atualizados.');
```

---

## LOCALIZAÇÃO DOS ARQUIVOS

- **CSS Principal:** `/docs/layout/branco/styles.css`
- **JS Principal:** `/docs/layout/branco/scripts.js`
- **Componentes PHP:** `/docs/layout/branco/assets/components/{component}/{component}.php`
- **Documentação:** `/docs/layout/branco/assets/components/{component}/{component}.md`
- **Registry:** `/docs/layout/branco/assets/registry.json`
- **AI Usage:** `/docs/layout/branco/design-system/AI_USAGE.md`
- **Demo:** `/docs/layout/branco/demo.php`
- **Login:** `/docs/layout/branco/login.php`
- **Locações:** `/docs/layout/branco/locacoes.php`

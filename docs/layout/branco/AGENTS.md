# AGENTS.md — SisLoc v3.0 Design System Registry

## Visao Geral

Design System 3.0 "White Rabbit" para SisLoc v3.0. Sistema de componentes PHP com render functions, CSS variables e JS vanilla. Viewport alvo: **1280px+**.

## Arquitetura

```
/var/www/html/branco/
  styles.css              # CSS monolitico (design tokens + todos componentes)
  scripts.js              # JS central (tabelas, toasts, modais, popovers, sidebar)
  demo.php                # Demo de todos os componentes
  login.php               # Tela de login (self-contained, CSS inline)
  locacoes.php            # Tela complexa (tabs h+v, tabelas, modal)
  assets/
    css/components.css     # CSS modular (mirror do styles.css)
    components/
      <name>/<name>.php    # Render function do componente
      <name>/<name>.md     # Documentacao do componente
```

## CSS Variables (Design Tokens)

```css
--neon-cyan / --neon-cyan-glow       /* Cor primaria */
--neon-green / --neon-red / --neon-yellow / --neon-purple / --neon-orange / --neon-blue
--bg-card / --bg-surface / --bg-hover / --bg-elevated / --bg-border-sub
--text-1 / --text-2 / --text-3 / --text-4
--z-base:1 / --z-dropdown:1000 / --z-popover:2000 / --z-tooltip:3000 / --z-modal:4000 / --z-toast:5000
```

## Funcoes JS (scripts.js)

| Funcao              | Descricao                                         |
|---------------------|---------------------------------------------------|
| `toggleSidebar()`   | Abre/fecha sidebar                                |
| `toggleSub(el)`     | Expande submenu no sidebar                        |
| `switchTab(g,i)`    | Troca tab generico                                |
| `togglePop(id)`     | Abre/fecha popover/dropdown (position:fixed)      |
| `openModal(id)`     | Abre modal                                        |
| `closeModal()`      | Fecha modal                                       |
| `showToast(t,t,m,d)`| Exibe toast (type, title, msg, duration)          |
| `tblSearch(id,val)` | Filtra tabela                                     |
| `tblSetPerPage(id,v)`| Altera linhas por pagina                         |
| `tblGoToPage(id,p)` | Navega para pagina                                |
| `sortTable(id,col)` | Ordena tabela                                     |
| `stepNext/stepPrev` | Navega stepper                                    |

---

## Componentes (21)

### 1. Accordion
- **Arquivo:** `assets/components/accordion/accordion.php`
- **Funcao:** `renderAccordion($config)`
- **Params:** `id`, `items` (array de `title`, `iconBg`, `content`, `open`, `icon`)
- **Desc:** Itens colapsaveis com icones e estado aberto/fechado

### 2. Alert
- **Arquivo:** `assets/components/alert/alert.php`
- **Funcao:** `renderAlert($config)`
- **Params:** `variant` (red/green/yellow/cyan/blue/purple), `title`, `message`, `icon`, `dismissible`
- **Desc:** Banners de alerta para erro, sucesso, aviso e informacao

### 3. Avatar
- **Arquivo:** `assets/components/avatar/avatar.php`
- **Funcoes:** `renderAvatar($config)`, `renderAvatarGroup($config)`
- **Params:** `initials`, `variant` (cyan/green/red/purple/yellow/blue), `size` (sm/default/lg/xl), `status` (online/busy/offline); grupo: `avatars`, `overflow`
- **Desc:** Circulo com iniciais, cores, status online e agrupamento

### 4. Badge
- **Arquivo:** `assets/components/badge/badge.php`
- **Funcao:** `renderBadge($config)`
- **Params:** `label`, `variant` (red/green/cyan/blue/purple/orange/yellow), `size` (sm/default/lg), `pulse`
- **Desc:** Indicador de status com dot pulsante opcional

### 5. Button
- **Arquivo:** `assets/components/button/button.php`
- **Funcao:** `renderButton($config)`
- **Params:** `label`, `variant` (cyan/green/red/blue/purple/orange/yellow/ghost/ghost-cyan/ghost-red/outline-cyan), `size` (xs/sm/md/lg/xl/icon/icon-sm/icon-md/icon-xs/icon-lg), `icon`, `iconPosition`, `loading`, `disabled`, `onclick`, `type`, `extra`
- **Desc:** Botao com variantes solid, ghost, outline; suporte a icone, loading e disabled

### 6. Card
- **Arquivo:** `assets/components/card/card.php`
- **Funcoes:** `renderCard($config)`, `renderCardStat($config)`, `renderCustomCard($config)`
- **Params Card:** `title`, `tag`, `body`, `footer`, `padding`
- **Params Stat:** `icon`, `value`, `label`, `color` (usa `--stat-color` CSS var no icone)
- **Params Custom:** `title`, `body`, `footer`, `glow`
- **Desc:** Container em 3 estilos: basico, stat card (KPI) com icone colorido, e custom com glow

### 7. Chip
- **Arquivo:** `assets/components/chip/chip.php`
- **Funcoes:** `renderChip($config)`, `renderChipRemovable($config)`, `renderTag($config)`, `renderChipGroup($chips)`
- **Params:** `label`, `variant`, `selected`, `onclick` / `onRemove`
- **Desc:** Chips selecionaveis, removiveis e tags estaticas

### 8. Input
- **Arquivo:** `assets/components/input/input.php`
- **Funcoes:** `renderInput($config)`, `renderFormRow($fields)`
- **Params:** `type` (text/email/password/number/tel/url/search/date/time/datetime-local/select/textarea), `label`, `name`, `placeholder`, `value`, `disabled`, `state` (default/focus/success/error), `hint`, `icon`, `options`, `rows`, `extra`
- **Desc:** Campo de formulario com validacao, icones, select, textarea e layout 2 colunas

### 9. List Item
- **Arquivo:** `assets/components/listitem/listitem.php`
- **Funcoes:** `renderListItem($config)`, `renderListItemGroup($items)`
- **Params:** `title`, `subtitle`, `icon`, `iconBg`, `action`, `onclick`
- **Desc:** Linha de lista com icone, titulo/subtitulo e acao

### 10. Modal
- **Arquivo:** `assets/components/modal/modal.php`
- **Funcoes:** `renderModal($config)`, `renderModalConfirm($config)`, `renderModalSuccess($config)`, `renderModalForm($config)`
- **Params:** `id`, `size` (sm/md/lg), `variant`, `title`, `subtitle`, `icon`, `body`, `footer`, `fields`, `col2`
- **Desc:** Dialog overlay em 4 presets: info, confirmacao danger, sucesso, formulario com campos auto

### 11. Popover
- **Arquivo:** `assets/components/popover/popover.php`
- **Funcao:** `renderPopover($config)`
- **Params:** `id`, `trigger`, `title`, `content`, `position` (bottom/top), `minWidth`
- **Desc:** Popover flutuante com toggle via `togglePop()`

### 12. Progress
- **Arquivo:** `assets/components/progress/progress.php`
- **Funcoes:** `renderProgress($config)`, `renderProgressGroup($items)`
- **Params:** `label`, `value`, `percent`, `variant` (red/cyan/green/yellow/purple/blue), `height` (default/thick)
- **Desc:** Barra de progresso animada com label e variantes de cor

### 13. Rating
- **Arquivo:** `assets/components/rating/rating.php`
- **Funcao:** `renderRating($config)`
- **Params:** `id`, `max`, `value`, `size` (sm/default/lg), `interactive`, `showValue`
- **Desc:** Avaliacao por estrelas com interacao opcional

### 14. Sidebar
- **Arquivo:** HTML manual (sem render function)
- **Classes:** `#sidebar`, `.sb-head`, `.sb-scroll`, `.sb-foot`, `.ni`, `.sub`, `.nav-lbl`
- **JS:** `toggleSidebar()`, `toggleSub()`, `showSection()`
- **Desc:** Sidebar fixa com modo mini colapsavel, submenus e user card

### 15. Skeleton
- **Arquivo:** `assets/components/skeleton/skeleton.php`
- **Funcoes:** `renderSkeleton($config)`, `renderSkeletonCard($config)`, `renderSkeletonTable($rows, $cols)`
- **Params:** `width`, `height`, `circle`, `lines`, `avatar`
- **Desc:** Placeholders de loading com shimmer para linhas, cards e tabelas

### 16. Spinner
- **Arquivo:** `assets/components/spinner/spinner.php`
- **Funcoes:** `renderSpinner($config)`, `renderSpinnerDots()`, `renderSpinnerRing()`, `renderSpinnerBar()`, `renderSpinnerWithLabel($config)`
- **Params:** `size` (sm/md/lg/xl), `variant` (cyan/red/green/purple/yellow), `label`
- **Desc:** Indicadores de loading: ring, dots, bar e labeled

### 17. Table
- **Arquivo:** `assets/components/table/table.php`
- **Funcoes:** `renderTable($config)`, `renderTableActions($type)`
- **Params:** `headers`, `rows`, `id`, `striped`, `hoverable`, `searchable`, `searchPlaceholder`, `paginated`, `perPage`, `perPageOptions`, `actionBtns`
- **Presets de acao:** `default` (Editar+Excluir), `crud` (Ver+Editar+Excluir), `confirm` (Aprovar+Rejeitar)
- **Comportamento de acoes:**
  - 2 ou menos: botoes inline + dropdown `.td-act-compact` (oculto ate viewport <= 1280px)
  - 3+: 2 inline + dropdown "..." com TODAS as acoes
  - Em 1280px: todos os botoes migram para dropdown "Acoes" (position:fixed)
- **Desc:** Tabela de dados com sorting, busca, paginacao e coluna de acoes com dropdown responsivo

### 18. Tabs
- **Arquivo:** `assets/components/tabs/tabs.php`
- **Funcoes:** `renderTabsColor($config)`, `renderTabsUnderline($config)`, `renderTabsPill($config)`, `renderTabsSegmented($config)`, `renderTabsVertical($config)`
- **Params:** `id`, `tabs` (array de `label`, `color`, `icon`, `active`, `content`), `activeIndex`; vertical: `dark`
- **Desc:** Navegacao por tabs em 5 estilos: colorido, underline, pill, segmentado e vertical

### 19. Timeline
- **Arquivo:** `assets/components/timeline/timeline.php`
- **Funcao:** `renderTimeline($config)`
- **Params:** `items` (array de `time`, `title`, `desc`, `color`, `icon`)
- **Desc:** Timeline vertical com dots coloridos e eventos

### 20. Toast
- **Arquivo:** `assets/components/toast/toast.php`
- **Funcoes:** `renderToastTrigger($config)`, `renderToastContainer()`
- **Params:** `label`, `variant`, `type`, `title`, `message`, `duration`
- **Desc:** Botao que dispara notificacao flutuante via `showToast()` + container fixo

### 21. Toggle
- **Arquivo:** `assets/components/toggle/toggle.php`
- **Funcoes:** `renderToggle($config)`, `renderToggleRow($config)`
- **Params:** `id`, `checked`, `variant` (cyan/red), `size` (default/small); row: `label`
- **Desc:** Switch/toggle com cores cyan/red e layout inline com label

---

## Regras Gerais para IA

1. Sempre usar `require_once` com `__DIR__` para incluir componentes PHP
2. Celulas de tabela com HTML devem usar `['html' => true, 'content' => '...']`
3. Badges em colunas de status: `renderBadge(['label'=>'Ativo','variant'=>'green','size'=>'sm','pulse'=>true])`
4. Valores monetarios: `<span class="td-mono">R$ 4.200,00</span>`
5. Acoes de tabela: usar presets `renderTableActions('crud'|'confirm'|'default')`
6. O dropdown de acoes e automatico - nao criar manualmente
7. Popover/dropdown usa `position:fixed` via JS - nao precisa de z-index no container
8. Viewport alvo: 1280px+ com breakpoints em 1440px, 1366px, 1280px
9. Sidebar: `body.mini` colapsa para 64px; toggle via `closeSidebar()`/`openSidebar()`
10. CSS custom properties: usar `var(--neon-cyan)`, `var(--bg-card)`, etc - nunca hardcoded

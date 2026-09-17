# Sidebar Component

## Uso
- Navegação principal do sistema
- Menu lateral fixo
- Acesso a todas as seções

## Classes Base

### Container
- `#sidebar` - Container principal (fixed, 260px full, 64px mini)
- `.sb-head` - Cabeçalho com logo e botão collapse
- `.sb-scroll` - Área scrollável de navegação
- `.sb-foot` - Rodapé com user card

### Logo
- `.logo-wrap` - Container do logo
- `.logo-ico` - Ícone quadrado (34x34px, cyan)
- `.logo-name` - Nome do sistema
- `.logo-ver` - Versão (monospace)

### Navegação
- `.nav-lbl` - Label de seção (uppercase, 10px)
- `.ni` - Navigation Item (44px height)
- `.ni-left` - Container ícone + label
- `.ni-label` - Label do item
- `.nav-badge` - Badge contador
- `.chv` - Chevron para submenu
- `.si` - Sub Item (38px height)
- `.si-dot` - Ponto indicador
- `.sub` - Submenu container

### User Card
- `.user-card` - Container do usuário
- `.avatar` - Avatar do usuário
- `.user-texts` - Container de textos
- `.u-name` - Nome do usuário
- `.u-role` - Função/cargo

### Tooltip (modo colapsado)
- `.ni-tooltip` - Tooltip no modo mini

## Estados
- Expandido: largura 260px
- Colapsado: `body.mini` ativa, largura 64px
- Item ativo: `.ni.active` (fundo cyan)
- Submenu aberto: `.sub.open`

## JavaScript
```javascript
// Toggle sidebar
toggleSidebar()

// Toggle submenu
toggleSub('id', element)

// Mostrar seção
showSection('id', element)
```

## Exemplos

### Sidebar Completa
```html
<aside id="sidebar">
  <div class="sb-head">
    <div class="logo-wrap">
      <div class="logo-ico">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/>
        </svg>
      </div>
      <div>
        <div class="logo-name">SisLoc</div>
        <div class="logo-ver">v3.0 · White</div>
      </div>
    </div>
    <button class="collapse-btn" onclick="toggleSidebar()">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
      </svg>
    </button>
  </div>

  <div class="sb-scroll">
    <nav>
      <!-- Item simples -->
      <div class="ni active" onclick="showSection('dashboard',this)">
        <div class="ni-left">
          <svg>...</svg>
          <span class="ni-label">Dashboard</span>
        </div>
        <div class="ni-tooltip">Dashboard</div>
      </div>

      <!-- Label de seção -->
      <div class="nav-lbl">UI Elements</div>

      <!-- Item com submenu -->
      <div class="ni" onclick="toggleSub('ui',this)">
        <div class="ni-left">
          <svg>...</svg>
          <span class="ni-label">UI Elements</span>
        </div>
        <svg class="chv">...</svg>
        <div class="ni-tooltip">UI Elements</div>
      </div>
      <div class="sub" id="sub-ui">
        <div class="si" onclick="showSection('buttons',this)">
          <div class="si-dot"></div>
          Buttons
        </div>
        <div class="si" onclick="showSection('cards',this)">
          <div class="si-dot"></div>
          Cards
        </div>
      </div>

      <!-- Item com badge -->
      <div class="ni" onclick="showSection('alerts',this)">
        <div class="ni-left">
          <svg>...</svg>
          <span class="ni-label">Alertas</span>
        </div>
        <span class="nav-badge">3</span>
        <div class="ni-tooltip">Alertas</div>
      </div>
    </nav>
  </div>

  <div class="sb-foot">
    <div class="user-card" onclick="openRight()">
      <div class="avatar">MA</div>
      <div class="user-texts">
        <div class="u-name">Marco Antônio</div>
        <div class="u-role">Administrador</div>
      </div>
    </div>
  </div>
</aside>
```

## Regras para IA
1. Sidebar é sempre fixed à esquerda
2. Fundo escuro (#0F172A), texto branco
3. Ícones SVG inline com stroke
4. Item ativo tem fundo cyan e borda
5. Submenu só funciona quando sidebar expandido
6. Tooltip aparece automaticamente no modo mini
7. Labels de seção somem no modo mini
8. Badge some no modo mini
9. User card abre o right-canvas (perfil)
10. Collapse button rotaciona seta no modo mini
11. Usar `showSection()` para navegação
12. Usar `toggleSub()` para submenus

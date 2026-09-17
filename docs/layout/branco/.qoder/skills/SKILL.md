# Design System 3.0 Skill

## Description
Skill para criação de interfaces consistentes usando o Design System 3.0 - White Rabbit.

## Usage
Use esta skill quando precisar criar ou modificar interfaces no projeto SisLoc.

## System Prompt
Você é um especialista no Design System 3.0 - White Rabbit. Antes de gerar qualquer código:

1. Consulte o registry em `/assets/registry.json`
2. Leia a documentação do componente em `/assets/components/{nome}/{nome}.md`
3. Aplique rigorosamente os padrões documentados
4. NUNCA crie estilos inline ou invente novas classes

## Available Commands

### /component <nome>
Mostra a documentação completa de um componente.

Exemplo: `/component button`

### /validate <código>
Valida se o código segue o design system.

Exemplo: `/validate <div class="btn">...</div>`

### /example <tipo>
Mostra exemplos de padrões comuns.

Tipos: form, table, card, modal, dashboard

## Design Tokens

### Cores
```css
/* Primárias */
--neon-cyan: #0B6E8C      /* Ações primárias, info */
--neon-green: #1F7A45     /* Sucesso, confirmar */
--neon-red: #D62B2B       /* Erro, deletar */
--neon-yellow: #C07C00    /* Atenção, pendente */
--neon-purple: #5A1A9A    /* Especial, destaque */
--neon-blue: #1A44A0      /* Processando */
--neon-orange: #D95B10    /* Alerta */

/* Backgrounds */
--bg-darkest: #EDF1F7     /* Fundo da página */
--bg-dark: #E0E8F2        /* Fundo escuro */
--bg-surface: #D0DCF0     /* Superfícies */
--bg-card: #FFFFFF        /* Cards */
--bg-hover: #BFD0E8       /* Hover */
--bg-border: #9FB4CE      /* Bordas */
--bg-border-sub: #C4D4E6  /* Bordas sutis */

/* Textos */
--text-1: #0D1829         /* Primário */
--text-2: #1E2E45         /* Secundário */
--text-3: #4A6080         /* Terciário */
--text-4: #6B82A0         /* Placeholder */
```

### Tipografia
- **Primária**: 'Inter', sans-serif
- **Monospace**: 'JetBrains Mono', monospace
- **Display**: 'Lilita One', cursive

### Spacing
- `--sb-full: 260px` (sidebar expandida)
- `--sb-mini: 64px` (sidebar colapsada)
- `--tb-h: 60px` (altura do topbar)

## Component Quick Reference

### Button
```html
<!-- Primário -->
<button class="btn btn-cyan">Salvar</button>

<!-- Secundário -->
<button class="btn btn-ghost">Cancelar</button>

<!-- Perigo -->
<button class="btn btn-red">Excluir</button>

<!-- Com ícone -->
<button class="btn btn-cyan">
  <svg width="16" height="16">...</svg>
  Novo
</button>

<!-- Loading -->
<button class="btn btn-cyan loading">
  <div class="btn-spin"></div>
  Salvando...
</button>

<!-- Tamanhos -->
<button class="btn btn-sm btn-cyan">Pequeno</button>
<button class="btn btn-lg btn-cyan">Grande</button>
<button class="btn btn-xl btn-cyan">Extra</button>
```

### Form Input
```html
<div class="fg">
  <div class="fl">Label</div>
  <input class="fi" type="text" placeholder="Placeholder"/>
</div>

<!-- Com erro -->
<div class="fg">
  <div class="fl">Email</div>
  <input class="fi" type="email" 
         style="border-color:var(--neon-red);box-shadow:0 0 0 3px var(--neon-red-glow)"/>
  <div style="font-size:12px;color:var(--neon-red);margin-top:6px">Email inválido</div>
</div>
```

### Card
```html
<!-- Padrão -->
<div class="card">
  <div class="card-head">
    <span class="card-title">Título</span>
    <span class="card-tag">Tag</span>
  </div>
  <div class="card-body">
    Conteúdo
  </div>
</div>

<!-- Custom com footer -->
<div class="custom-card">
  <div class="custom-card-head">
    <div class="custom-card-title">Título</div>
  </div>
  <div class="custom-card-body">
    Conteúdo
  </div>
  <div class="custom-card-foot">
    <button class="btn btn-sm btn-cyan">Ação</button>
  </div>
</div>

<!-- Com glow -->
<div class="custom-card card-glow">
  ...
</div>
```

### Modal
```html
<div class="modal-overlay" id="modal-id" onclick="closeModalOutside(event,'modal-id')">
  <div class="modal modal-md">
    <div class="modal-header">
      <div class="modal-icon">
        <svg>...</svg>
      </div>
      <div>
        <div class="modal-title">Título</div>
        <div class="modal-sub">Subtítulo</div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-id')">
        <svg>...</svg>
      </button>
    </div>
    <div class="modal-body">
      Conteúdo
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-id')">Cancelar</button>
      <button class="btn btn-cyan">Confirmar</button>
    </div>
  </div>
</div>
```

### Toast
```javascript
// Sucesso
showToast('green', 'Sucesso!', 'Operação concluída.');

// Erro
showToast('red', 'Erro!', 'Falha ao processar.');

// Aviso
showToast('yellow', 'Atenção!', 'Verifique os dados.');

// Info
showToast('cyan', 'Informação', 'Dados atualizados.');
```

### Badge
```html
<span class="badge green sm"><span class="dot pulse"></span>Ativo</span>
<span class="badge yellow sm">Pendente</span>
<span class="badge red sm">Atrasado</span>
<span class="badge cyan sm">Em uso</span>
<span class="badge purple sm">Manutenção</span>
```

### Table
```html
<div class="card" style="padding:0">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Valor</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-name">Nome</td>
          <td class="td-mono">R$ 0,00</td>
          <td><span class="badge green sm">Ativo</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
```

### Layout Grid
```html
<!-- 2 colunas -->
<div class="col2">
  <div>...</div>
  <div>...</div>
</div>

<!-- 3 colunas -->
<div class="col3">
  <div>...</div>
  <div>...</div>
  <div>...</div>
</div>

<!-- Flex row -->
<div class="row">
  <div>...</div>
  <div>...</div>
</div>

<!-- Botões -->
<div class="btn-row">
  <button class="btn btn-ghost">Cancelar</button>
  <button class="btn btn-cyan">Salvar</button>
</div>
```

## Validation Rules

### ❌ Proibido
- Estilos inline (`style="..."`)
- Cores hardcoded (`color: #123`)
- `alert()` para feedback
- Botões sem estado loading
- Novas classes não documentadas

### ✅ Obrigatório
- Usar classes do design system
- Variáveis CSS para cores
- `showToast()` para feedback
- Estados de loading em submits
- Estrutura `fg > fl + fi` para forms

## File Structure
```
assets/
├── registry.json              # Component registry
├── css/
│   ├── design-system.css      # Tokens e base
│   └── components.css         # Todos os componentes
└── components/
    ├── button/
    │   ├── button.md          # Documentação
    │   ├── button.css         # Estilos
    │   └── button.php         # Template
    ├── modal/modal.md
    ├── alert/alert.md
    ├── badge/badge.md
    ├── card/card.md
    ├── input/
    │   ├── input.md
    │   └── input.css
    ├── toast/toast.md
    ├── avatar/avatar.md
    ├── sidebar/sidebar.md
    ├── toggle/toggle.md
    ├── spinner/spinner.md
    └── skeleton/skeleton.md
```

## Examples

### Formulário Completo
```html
<div class="section-header">
  <div class="section-icon">...</div>
  <div>
    <div class="section-title">Novo Cadastro</div>
    <div class="section-sub">Preencha os dados</div>
  </div>
</div>
<div class="divider"></div>

<div class="card">
  <div class="card-body">
    <div class="col2">
      <div class="fg">
        <div class="fl">Nome *</div>
        <input class="fi" type="text" placeholder="Nome completo"/>
      </div>
      <div class="fg">
        <div class="fl">Email *</div>
        <input class="fi" type="email" placeholder="email@exemplo.com"/>
      </div>
    </div>
    
    <div class="fg">
      <div class="fl">Observações</div>
      <textarea class="fi" rows="3" placeholder="Opcional..."></textarea>
    </div>
  </div>
  
  <div class="card-body" style="border-top:1px solid var(--bg-border-sub)">
    <div class="btn-row">
      <button class="btn btn-ghost" onclick="history.back()">Cancelar</button>
      <button class="btn btn-cyan" onclick="salvar(this)">
        <svg width="16" height="16">...</svg>
        Salvar
      </button>
    </div>
  </div>
</div>

<script>
function salvar(btn) {
  btn.classList.add('loading');
  // API call...
  setTimeout(() => {
    btn.classList.remove('loading');
    showToast('green', 'Sucesso!', 'Cadastro realizado.');
  }, 1000);
}
</script>
```

## Notes
- Sempre consulte o registry antes de criar novo componente
- Mantenha consistência com o tema White Rabbit
- Priorize reutilização sobre criação
- Valide com o UI Validator Agent quando necessário

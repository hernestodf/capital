# AI Usage Guide - Design System 3.0

## Como usar este Design System com IA

### 1. ANTES de Gerar UI

Sempre consultar:
1. `assets/registry.json` - Lista completa de componentes
2. `assets/components/{component}/{component}.md` - Documentação do componente
3. `assets/css/components.css` - Estilos disponíveis

### 2. FLUXO DE TRABALHO

```
1. Usuário pede uma interface
2. IA consulta registry.json
3. IA verifica se componente existe
4. Se existe → reutilizar com documentação .md
5. Se não existe → criar novo seguindo padrão
```

### 3. REGRAS OBRIGATÓRIAS

#### Nunca
- Criar botão com estilos inline
- Inventar novas cores (usar variáveis CSS)
- Duplicar componentes existentes
- Usar alert() (usar Toast)
- Criar modais sem usar modal-overlay

#### Sempre
- Usar classes do design system
- Seguir documentação .md de cada componente
- Usar variáveis CSS (--neon-*, --bg-*, --text-*)
- Incluir estados de loading em ações
- Usar Toast para feedback
- Manter consistência visual

### 4. CORES E SIGNIFICADOS

| Cor | Uso | Componentes |
|-----|-----|-------------|
| Cyan | Primário, ações padrão, info | botões, headers, badges |
| Green | Sucesso, confirmar, ativo | botões, badges, alerts |
| Red | Erro, deletar, perigo, atraso | botões, badges, alerts |
| Yellow | Atenção, pendente, aviso | badges, alerts, chips |
| Purple | Especial, promoção, destaque | badges, alerts, timeline |
| Blue | Processando, em andamento | badges, alerts |
| Orange | Alerta importante | toast, button |

### 5. COMPONENTES MAIS USADOS

#### Para Ações
```html
<!-- Botão primário -->
<button class="btn btn-cyan">Salvar</button>

<!-- Botão secundário -->
<button class="btn btn-ghost">Cancelar</button>

<!-- Botão perigo -->
<button class="btn btn-red">Excluir</button>

<!-- Botão com loading -->
<button class="btn btn-cyan loading">
  <div class="btn-spin"></div>
  Salvando...
</button>
```

#### Para Feedback
```javascript
// Toast de sucesso
showToast('green', 'Sucesso!', 'Operação concluída.');

// Toast de erro
showToast('red', 'Erro!', 'Falha ao salvar.');

// Toast de info
showToast('cyan', 'Info', 'Dados atualizados.');
```

#### Para Confirmações
```html
<!-- Modal de confirmação -->
<div class="modal-overlay" id="modal-confirm">
  <div class="modal modal-sm">
    <div class="modal-header" style="background:var(--neon-red)">
      <div class="modal-icon">...</div>
      <div>
        <div class="modal-title">Confirmar</div>
        <div class="modal-sub">Esta ação não pode ser desfeita</div>
      </div>
    </div>
    <div class="modal-body">
      <p>Tem certeza?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-confirm')">Cancelar</button>
      <button class="btn btn-red">Confirmar</button>
    </div>
  </div>
</div>
```

#### Para Formulários
```html
<div class="fg">
  <div class="fl">Nome</div>
  <input class="fi" type="text" placeholder="Digite seu nome"/>
</div>
```

#### Para Status
```html
<span class="badge green sm"><span class="dot pulse"></span>Ativo</span>
<span class="badge yellow sm">Pendente</span>
<span class="badge red sm">Atrasado</span>
```

### 6. PADRÕES DE LAYOUT

#### Grid de Cards
```html
<div class="col3">
  <!-- 3 cards de stats -->
</div>

<div class="col2">
  <!-- 2 cards de conteúdo -->
</div>
```

#### Lista de Itens
```html
<div class="card">
  <div class="card-body" style="padding:0">
    <div class="list-item">...</div>
    <div class="list-item-divider"></div>
    <div class="list-item">...</div>
  </div>
</div>
```

#### Tabela
```html
<div class="card" style="padding:0">
  <div class="table-wrap">
    <table class="data-table">
      <thead>...</thead>
      <tbody>...</tbody>
    </table>
  </div>
</div>
```

### 7. CHECKLIST ANTES DE ENTREGAR

- [ ] Usou componentes existentes do registry?
- [ ] Seguiu a documentação .md?
- [ ] Usou variáveis CSS ao invés de cores hardcoded?
- [ ] Botões de ação têm estado loading?
- [ ] Feedback usa Toast (não alert)?
- [ ] Confirmações usam Modal?
- [ ] Formulários usam fg > fl + fi?
- [ ] Status usam Badge?
- [ ] Layout usa col2/col3 quando aplicável?

### 8. ATUALIZANDO COMPONENTES

Quando perceber necessidade de melhoria:

1. Identificar o problema
2. Atualizar o arquivo `.md` do componente
3. Atualizar o CSS se necessário
4. Atualizar o `registry.json` se adicionar variante
5. Documentar a mudança

### 9. EXEMPLO DE PROMPT IDEAL

```
Criar página de cadastro de cliente.

Usar componentes do Design System 3.0:
- Formulário com fg/fl/fi
- Botões btn-cyan e btn-ghost
- Toast para feedback
- Modal para confirmação

Layout:
- Header com título
- Form em 2 colunas (col2)
- Botões alinhados no final
```

### 10. REFERÊNCIA RÁPIDA

| Elemento | Classe | Docs |
|----------|--------|------|
| Botão | `.btn` | `components/button/button.md` |
| Modal | `.modal` | `components/modal/modal.md` |
| Toast | `showToast()` | `components/toast/toast.md` |
| Input | `.fi` | `components/input/input.md` |
| Badge | `.badge` | `components/badge/badge.md` |
| Alert | `.alert` | `components/alert/alert.md` |
| Card | `.card` | `components/card/card.md` |
| Avatar | `.avatar` | `components/avatar/avatar.md` |
| Toggle | `.tog` | `components/toggle/toggle.md` |
| Spinner | `.spinner` | `components/spinner/spinner.md` |

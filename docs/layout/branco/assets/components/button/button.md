# Button Component

## Uso
- Ações principais do sistema
- Submit de formulários
- Navegação entre páginas
- Gatilho de modais e ações

## Classes Base

### Tamanho
- `btn` - Tamanho padrão (11px 20px, 14.5px font)
- `btn-sm` - Pequeno (8px 14px, 13px font)
- `btn-lg` - Grande (14px 26px, 16px font)
- `btn-xl` - Extra grande (17px 32px, 17px font)
- `btn-icon` - Botão ícone quadrado (42x42px)
- `btn-icon-sm` - Botão ícone pequeno (36x36px)
- `btn-icon-lg` - Botão ícone grande (52x52px)

### Cores Sólidas
- `btn-red` - Vermelho (erro, deletar, perigo)
- `btn-green` - Verde (sucesso, confirmar, salvar)
- `btn-cyan` - Ciano (ação primária, padrão do sistema)
- `btn-blue` - Azul (processando, info)
- `btn-purple` - Roxo (especial, destaque)
- `btn-orange` - Laranja (alerta)
- `btn-yellow` - Amarelo (atenção)
- `btn-ghost` - Cinza neutro (ação secundária, cancelar)

### Variantes Ghost (fundo claro, borda colorida)
- `btn-ghost-cyan` - Fundo branco, texto cyan, hover preenche cyan
- `btn-ghost-red` - Fundo branco, texto vermelho, hover preenche vermelho

### Variantes Outline
- `btn-outline-cyan` - Fundo transparente, borda cyan, hover preenche

## Estados
- Normal: estado padrão
- Hover: elevação com `translateY(-2px)` e sombra aumentada
- Loading: adicionar `<div class="btn-spin"></div>` dentro do botão, texto fica transparente
- Disabled: adicionar classe `.disabled` (opacity 0.5, sem pointer events)

## Exemplos

### Botão Sólido Padrão
```html
<button class="btn btn-cyan">Salvar</button>
```

### Botão com Ícone
```html
<button class="btn btn-green">
  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
  </svg>
  Confirmar
</button>
```

### Botão Loading
```html
<button class="btn btn-cyan loading">
  <div class="btn-spin"></div>
  Salvando...
</button>
```

### Botão Disabled
```html
<button class="btn btn-cyan disabled">Desabilitado</button>
```

### Botão Ghost
```html
<button class="btn btn-ghost">Cancelar</button>
```

### Botão Ghost Cyan
```html
<button class="btn btn-ghost-cyan">Ver Detalhes</button>
```

### Botão Outline
```html
<button class="btn btn-outline-cyan">Saiba Mais</button>
```

### Grupo de Botões
```html
<div class="btn-row">
  <button class="btn btn-cyan">Primário</button>
  <button class="btn btn-ghost">Cancelar</button>
</div>
```

## Regras para IA
1. Usar `btn-cyan` como botão primário padrão do sistema
2. Usar `btn-green` para ações de confirmação/sucesso
3. Usar `btn-red` para ações destrutivas (excluir, cancelar irreversível)
4. Usar `btn-ghost` para ações secundárias (cancelar, voltar)
5. Sempre adicionar estado loading em botões de submit
6. Usar `btn-sm` em tabelas e cards compactos
7. Usar `btn-lg` ou `btn-xl` em CTAs principais
8. Ícones devem usar SVG inline com `width="16" height="16"`

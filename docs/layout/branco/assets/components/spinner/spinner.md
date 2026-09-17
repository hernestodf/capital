# Spinner Component

## Uso
- Indicadores de carregamento
- Estados de espera
- Loading de dados
- Processamento em andamento

## Tipos

### 1. Ring Spinner (Circular)
Spinner rotativo clássico.

### 2. Dots Spinner
Três pontos pulsando.

### 3. Bar Spinner
Barra de progresso indeterminada.

### 4. Dual Ring Spinner
Dois anéis girando em direções opostas.

## Classes Base

### Ring Spinner
- `spinner` - Base
- `spinner sm` - Pequeno (18px)
- `spinner md` - Médio (32px)
- `spinner lg` - Grande (52px)
- `spinner xl` - Extra grande (72px)
- Cores: `cyan`, `red`, `green`, `purple`, `yellow`

### Dots Spinner
- `spinner-dots` - Container
- `spinner-dot` - Ponto individual (3 pontos)

### Bar Spinner
- `spinner-bar` - Container da barra
- `spinner-bar-fill` - Barra animada

### Dual Ring
- `spinner-ring` - Container (pseudo-elements)

### Wrapper com Label
- `spinner-wrap` - Container flex
- `spinner-label` - Texto ao lado

## Exemplos

### Ring Spinners - Tamanhos
```html
<div class="spinner sm cyan"></div>
<div class="spinner md cyan"></div>
<div class="spinner lg cyan"></div>
<div class="spinner xl cyan"></div>
```

### Ring Spinners - Cores
```html
<div class="spinner lg cyan"></div>
<div class="spinner lg red"></div>
<div class="spinner lg green"></div>
<div class="spinner lg purple"></div>
<div class="spinner lg yellow"></div>
```

### Dots
```html
<div class="spinner-dots">
  <div class="spinner-dot"></div>
  <div class="spinner-dot"></div>
  <div class="spinner-dot"></div>
</div>
```

### Bar Indeterminado
```html
<div class="spinner-bar" style="width:100%">
  <div class="spinner-bar-fill"></div>
</div>
```

### Dual Ring
```html
<div class="spinner-ring"></div>
```

### Spinner com Label
```html
<div class="spinner-wrap">
  <div class="spinner md cyan"></div>
  <div class="spinner-label">Carregando dados do servidor...</div>
</div>
```

### Em Botão Loading
```html
<button class="btn btn-cyan loading">
  <div class="btn-spin"></div>
  Salvando...
</button>
```

## Regras para IA
1. Usar `spinner md` para loading geral
2. Usar `spinner sm` em botões e elementos compactos
3. Usar `spinner lg` ou `xl` para loading de página
4. Usar `spinner-dots` para carregamento leve
5. Usar `spinner-bar` para progresso indeterminado
6. Usar `spinner-ring` para efeito mais elaborado
7. Cor cyan = padrão, red = erro, green = sucesso
8. Sempre usar `spinner-wrap` quando tiver label
9. Em botões, usar `btn-spin` dentro de `.btn.loading`

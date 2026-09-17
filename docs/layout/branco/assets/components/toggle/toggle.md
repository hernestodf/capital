# Toggle Component

## Uso
- Ativar/desativar configurações
- Switches booleanos
- Preferências do usuário

## Classes Base

### Tamanhos
- `tog` - Padrão (46x27px track, 17x17px thumb)
- `tog-s` - Pequeno (40x22px track, 14x14px thumb)

### Cores
- `tog` - Padrão (cyan quando ativo)
- `tog-cyan` - Cyan explícito
- `tog-red` - Vermelho quando ativo

## Estrutura
```html
<label class="tog">
  <input type="checkbox"/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

## Exemplos

### Toggle Padrão
```html
<label class="tog">
  <input type="checkbox"/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

### Toggle Ativo
```html
<label class="tog">
  <input type="checkbox" checked/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

### Toggle Cyan
```html
<label class="tog cyan">
  <input type="checkbox" checked/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

### Toggle Red
```html
<label class="tog red">
  <input type="checkbox"/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

### Toggle Pequeno (tog-s)
```html
<label class="tog-s">
  <input type="checkbox" checked/>
  <div class="tog-track"></div>
  <div class="tog-thumb"></div>
</label>
```

### Em Linha com Label
```html
<div class="lc-toggle-row">
  <div>Notificações por email</div>
  <label class="tog-s">
    <input type="checkbox" checked/>
    <div class="tog-track"></div>
    <div class="tog-thumb"></div>
  </label>
</div>
```

## Regras para IA
1. Sempre usar `<label>` como container para acessibilidade
2. Input checkbox deve estar dentro do label
3. Track e thumb são divs vazias (estilizados via CSS)
4. Usar `tog-s` em listas compactas e filtros
5. Usar `tog` padrão em configurações
6. Usar `tog-red` para ações de perigo/desativar
7. Thumb usa animação cubic-bezier para efeito elástico

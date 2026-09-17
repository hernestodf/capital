# Input Component

## Uso
- Campos de texto em formulários
- Campos de email e senha
- Campos de busca
- Textareas e selects

## Classes Base

### Estrutura do Grupo
- `fg` - Form group (container, margin-bottom: 16px)
- `fl` - Form label (uppercase, 11px, bold)
- `fi` - Form input (campo de entrada)

### Tipos de Input Suportados
- `type="text"` - Texto padrão
- `type="email"` - Email
- `type="password"` - Senha
- `type="number"` - Número
- `type="tel"` - Telefone
- `type="url"` - URL
- `<select>` - Dropdown
- `<textarea>` - Texto multilinha

### Estados
- Default: borda `var(--bg-border-sub)`, fundo `var(--bg-surface)`
- Focus: borda `var(--neon-cyan)`, shadow `var(--neon-cyan-glow)`
- Disabled: opacidade reduzida
- Error: borda vermelha (inline style)
- Success: borda verde (inline style)

## Exemplos

### Input Simples
```html
<div class="fg">
  <div class="fl">Nome</div>
  <input class="fi" type="text" placeholder="Digite seu nome"/>
</div>
```

### Email
```html
<div class="fg">
  <div class="fl">Email</div>
  <input class="fi" type="email" placeholder="email@exemplo.com"/>
</div>
```

### Password
```html
<div class="fg">
  <div class="fl">Senha</div>
  <input class="fi" type="password" placeholder="••••••••"/>
</div>
```

### Textarea
```html
<div class="fg">
  <div class="fl">Mensagem</div>
  <textarea class="fi" rows="4" placeholder="Digite sua mensagem..."></textarea>
</div>
```

### Select
```html
<div class="fg">
  <div class="fl">Categoria</div>
  <select class="fi">
    <option>Opção 1</option>
    <option>Opção 2</option>
    <option>Opção 3</option>
  </select>
</div>
```

### Disabled
```html
<div class="fg">
  <div class="fl">Campo Desabilitado</div>
  <input class="fi" type="text" value="Não editável" disabled/>
</div>
```

### Input com Ícone
```html
<div class="fg">
  <div class="fl">Buscar</div>
  <div style="position:relative">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
         style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-3)">
      <circle cx="11" cy="11" r="8"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
    </svg>
    <input class="fi" type="text" placeholder="Buscar..." style="padding-left:44px"/>
  </div>
</div>
```

### Grid de Inputs (2 colunas)
```html
<div class="col2">
  <div class="fg">
    <div class="fl">Nome</div>
    <input class="fi" type="text" placeholder="Nome"/>
  </div>
  <div class="fg">
    <div class="fl">Sobrenome</div>
    <input class="fi" type="text" placeholder="Sobrenome"/>
  </div>
</div>
```

### Validação - Erro
```html
<div class="fg">
  <div class="fl">Email</div>
  <input class="fi" type="email" value="email-invalido"
         style="border-color:var(--neon-red);box-shadow:0 0 0 3px var(--neon-red-glow)"/>
  <div style="font-size:12px;color:var(--neon-red);margin-top:6px">Email inválido</div>
</div>
```

### Validação - Sucesso
```html
<div class="fg">
  <div class="fl">Email</div>
  <input class="fi" type="email" value="email@valido.com"
         style="border-color:var(--neon-green);box-shadow:0 0 0 3px var(--neon-green-glow)"/>
  <div style="font-size:12px;color:var(--neon-green);margin-top:6px">Email válido</div>
</div>
```

## Regras para IA
1. Sempre usar `fg > fl + fi` como estrutura padrão
2. Label deve ser uppercase e curto (1-2 palavras)
3. Placeholder deve ser instrutivo e claro
4. Para inputs com ícone, usar `position:relative` no wrapper e padding-left no input
5. Validação usa inline style com cores neon
6. Mensagem de validação abaixo do input com font-size 12px
7. Usar `col2` para grids de 2 colunas
8. Select usa a mesma classe `fi`
9. Textarea deve ter atributo `rows` explícito

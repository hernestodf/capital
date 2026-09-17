# Avatar Component

## Uso
- Identificação visual de usuários
- Perfis e contas
- Lista de membros
- Indicadores de status online

## Classes Base

### Tamanhos
- `av` - Padrão (40x40px, 13px font)
- `av-sm` - Pequeno (28x28px, 10px font)
- `av-lg` - Grande (56x56px, 18px font)
- `av-xl` - Extra grande (80x80px, 24px font)
- `av-lg` no profile hero (70x70px)

### Cores
- `av cyan` - Ciano (padrão)
- `av green` - Verde
- `av red` - Vermelho
- `av purple` - Roxo
- `av yellow` - Amarelo
- `av blue` - Azul

### Status
- `av-online` - Indicador online (ponto verde)
- `av-busy` - Indicador ocupado (ponto amarelo)
- `av-offline` - Indicador offline (ponto cinza)

### Avatar Group
- `avatar-group` - Container para grupo sobreposto
- `av-more` - Avatar "mais" (+N)

## Exemplos

### Avatar Simples
```html
<div class="avatar">MA</div>
```

### Tamanhos
```html
<div class="row" style="align-items:center;gap:16px">
  <div class="av cyan av-sm">MA</div>
  <div class="av cyan">MA</div>
  <div class="av cyan av-lg">MA</div>
  <div class="av cyan av-xl">MA</div>
</div>
```

### Cores
```html
<div class="row" style="gap:12px">
  <div class="av cyan">AC</div>
  <div class="av green">BG</div>
  <div class="av red">CR</div>
  <div class="av purple">DP</div>
  <div class="av yellow">EY</div>
  <div class="av blue">FB</div>
</div>
```

### Status Online
```html
<div class="av-online">
  <div class="av cyan av-lg">ON</div>
</div>

<div class="av-online av-busy">
  <div class="av green av-lg">OC</div>
</div>

<div class="av-online av-offline">
  <div class="av purple av-lg">OF</div>
</div>
```

### Avatar Group
```html
<div class="avatar-group">
  <div class="av cyan">MA</div>
  <div class="av green">JB</div>
  <div class="av red">LC</div>
  <div class="av purple">RF</div>
  <div class="av blue">SK</div>
  <div class="av av-more">+8</div>
</div>
```

### No Header/Topbar
```html
<div class="avatar" onclick="openRight()">MA</div>
```

### No User Card (Sidebar)
```html
<div class="user-card" onclick="openRight()">
  <div class="avatar">MA</div>
  <div class="user-texts">
    <div class="u-name">Marco Antônio</div>
    <div class="u-role">Administrador</div>
  </div>
</div>
```

### Profile Hero
```html
<div class="av-lg">MA</div>
```

## Regras para IA
1. Usar iniciais do nome como conteúdo (2-3 letras)
2. Avatar padrão (40px) para listas e headers
3. `av-sm` para tabelas compactas
4. `av-lg` para perfis e destaque
5. `av-xl` para páginas de perfil
6. Usar `av-online`, `av-busy`, `av-offline` com wrapper
7. Avatar group usa margin-left negativo para sobreposição
8. Cores podem ser usadas para diferenciar usuários
9. Fundo sempre colorido, texto sempre branco
10. Borda branca em av-lg de perfil

# Skeleton Component

## Uso
- Placeholders de carregamento
- Estados de loading antes dos dados chegarem
- Simular estrutura do conteúdo

## Classes Base

### Skeleton Base
- `skel` - Base com animação shimmer

### Variantes
- `skel-block` - Bloco de skeleton (display:block)
- `skel-card` - Card esqueleto completo
- `skel-row` - Linha com avatar + texto
- `skel-circle` - Círculo (para avatares)
- `skel-content` - Conteúdo real (após loading)

### Container
- `#skeleton-demo` - Container de demonstração
- `.loading` - Classe para estado de loading

## Exemplos

### Card Skeleton
```html
<div id="skeleton-demo" class="loading">
  <!-- Skeleton state -->
  <div class="skel-block">
    <div class="skel-card">
      <div class="skel-row">
        <div class="skel skel-circle" style="width:48px;height:48px"></div>
        <div style="flex:1">
          <div class="skel" style="height:14px;width:60%;margin-bottom:8px"></div>
          <div class="skel" style="height:12px;width:40%"></div>
        </div>
      </div>
      <div class="skel" style="height:12px;width:100%;margin-bottom:8px"></div>
      <div class="skel" style="height:12px;width:85%;margin-bottom:8px"></div>
      <div class="skel" style="height:12px;width:70%"></div>
    </div>
  </div>

  <!-- Real content state -->
  <div class="skel-content">
    <div class="list-item">
      <div class="list-item-icon">...</div>
      <div class="list-item-body">
        <div class="list-item-title">Título real</div>
        <div class="list-item-sub">Subtítulo</div>
      </div>
    </div>
  </div>
</div>
```

### Toggle Loading via JavaScript
```javascript
function loadSkeleton() {
  var btn = document.getElementById('skeleton-load-btn');
  btn.disabled = true;
  btn.textContent = 'Carregando...';
  document.getElementById('skeleton-demo').classList.add('loading');

  setTimeout(function() {
    document.getElementById('skeleton-demo').classList.remove('loading');
    btn.disabled = false;
    btn.textContent = 'Simular Carregamento';
    showToast('green', 'Conteúdo carregado!', 'Dados exibidos.');
  }, 2200);
}
```

### Skeleton Simples
```html
<!-- Loading placeholder -->
<div class="skel" style="height:14px;width:60%;margin-bottom:8px"></div>
<div class="skel" style="height:12px;width:40%"></div>
```

### Skeleton de Lista
```html
<div class="skel-block">
  <div class="skel-card">
    <div class="skel-row">
      <div class="skel skel-circle" style="width:40px;height:40px"></div>
      <div style="flex:1">
        <div class="skel" style="height:14px;width:50%;margin-bottom:8px"></div>
        <div class="skel" style="height:12px;width:30%"></div>
      </div>
    </div>
  </div>
  <!-- repetir para mais items -->
</div>
```

## Regras para IA
1. Skeleton deve espelhar estrutura do conteúdo real
2. Usar `skel-card` para cada item da lista
3. Usar `skel-circle` para avatares/ícones
4. Linhas de texto: height 12-14px, widths variados
5. Primeira linha mais larga (60-70%)
6. Linhas subsequentes mais estreitas (30-50%)
7. Usar `#skeleton-demo.loading` para toggle
8. Conteúdo real vai em `.skel-content`
9. Animação shimmer automática via CSS
10. Simular carregamento com setTimeout de 2-3s

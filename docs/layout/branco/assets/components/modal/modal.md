# Modal Component

## Uso
- Diálogos de confirmação
- Formulários popup
- Exibição de detalhes
- Alertas complexos

## Classes Base

### Tamanhos
- `modal-overlay` - Container overlay (fundo escuro)
- `modal` - Container do modal
- `modal-sm` - Modal pequeno (max-width: 420px)
- `modal-md` - Modal médio (max-width: 560px)
- `modal-lg` - Modal grande (max-width: 720px)

### Estrutura
- `modal-header` - Cabeçalho com fundo cyan
- `modal-icon` - Ícone do header (42x42px, fundo rgba branco)
- `modal-title` - Título do modal
- `modal-sub` - Subtítulo/descrição
- `modal-body` - Conteúdo principal
- `modal-footer` - Rodapé com botões
- `modal-close` - Botão de fechar (X)

## Estados
- Fechado: `opacity: 0; pointer-events: none`
- Aberto: adicionar classe `.open` no modal-overlay

## JavaScript
```javascript
// Abrir modal
openModal('modal-id')

// Fechar modal
closeModal('modal-id')

// Fechar ao clicar fora (no overlay)
onclick="closeModalOutside(event, 'modal-id')"
```

## Exemplos

### Modal de Informação
```html
<div class="modal-overlay" id="modal-info" onclick="closeModalOutside(event, 'modal-info')">
  <div class="modal modal-md">
    <div class="modal-header">
      <div class="modal-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <div class="modal-title">Informação</div>
        <div class="modal-sub">Detalhes importantes</div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-info')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <p>Conteúdo informativo aqui.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-info')">Fechar</button>
      <button class="btn btn-cyan">Confirmar</button>
    </div>
  </div>
</div>
```

### Modal de Perigo (Confirmação de Delete)
```html
<div class="modal-overlay" id="modal-danger" onclick="closeModalOutside(event, 'modal-danger')">
  <div class="modal modal-sm">
    <div class="modal-header" style="background: var(--neon-red)">
      <div class="modal-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <div class="modal-title">Confirmar Exclusão</div>
        <div class="modal-sub">Esta ação não pode ser desfeita</div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-danger')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <p>Tem certeza que deseja excluir este item? Esta ação é irreversível.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-danger')">Cancelar</button>
      <button class="btn btn-red">Excluir</button>
    </div>
  </div>
</div>
```

### Modal de Sucesso
```html
<div class="modal-overlay" id="modal-success" onclick="closeModalOutside(event, 'modal-success')">
  <div class="modal modal-md">
    <div class="modal-header" style="background: var(--neon-green)">
      <div class="modal-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <div class="modal-title">Operação Concluída</div>
        <div class="modal-sub">Tudo funcionou como esperado</div>
      </div>
    </div>
    <div class="modal-body">
      <p>Sua operação foi realizada com sucesso!</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-green" onclick="closeModal('modal-success')">OK</button>
    </div>
  </div>
</div>
```

## Regras para IA
1. Sempre usar `modal-overlay` como container externo com onclick para fechar
2. Modal deve ter header, body e footer
3. Header padrão é cyan; usar inline style para outras cores
4. Usar `modal-sm` para confirmações simples
5. Usar `modal-md` para formulários e informações
6. Usar `modal-lg` para conteúdo complexo
7. Footer deve alinhar botões à direita
8. Botão de fechar sempre no header
9. Incluir `overflow: hidden` no body quando modal aberto (já tratado pelo JS)

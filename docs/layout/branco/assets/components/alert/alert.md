# Alert Component

## Uso
- Mensagens de erro inline
- Notificações de sucesso
- Avisos e alertas
- Informações contextuais

## Classes Base

### Cores
- `alert red` - Erro crítico, falha
- `alert green` - Sucesso, operação concluída
- `alert yellow` - Atenção, aviso importante
- `alert cyan` - Informação, dica
- `alert blue` - Processando, em andamento
- `alert purple` - Promoção, destaque especial

### Estrutura
- `alert` - Container principal
- `alert-ico` - Ícone (22x22px)
- `alert-body` - Container do texto
- `alert-title` - Título em negrito
- `alert-desc` - Descrição detalhada
- `alert-close` - Botão de fechar (opcional)

## Exemplos

### Alerta de Erro
```html
<div class="alert red">
  <div class="alert-ico">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </div>
  <div class="alert-body">
    <div class="alert-title">Erro Crítico</div>
    <div class="alert-desc">Ocorreu um erro ao processar sua solicitação.</div>
  </div>
  <button class="alert-close">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  </button>
</div>
```

### Alerta de Sucesso
```html
<div class="alert green">
  <div class="alert-ico">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </div>
  <div class="alert-body">
    <div class="alert-title">Operação Concluída</div>
    <div class="alert-desc">Locação #3812 criada com sucesso.</div>
  </div>
</div>
```

### Alerta de Atenção
```html
<div class="alert yellow">
  <div class="alert-ico">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
  </div>
  <div class="alert-body">
    <div class="alert-title">Atenção</div>
    <div class="alert-desc">3 equipamentos precisam de manutenção nos próximos 7 dias.</div>
  </div>
</div>
```

### Alerta de Informação
```html
<div class="alert cyan">
  <div class="alert-ico">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </div>
  <div class="alert-body">
    <div class="alert-title">Informação</div>
    <div class="alert-desc">Sistema atualizado para a versão 3.0 com novo design neon.</div>
  </div>
</div>
```

## Regras para IA
1. Usar `red` para erros críticos que bloqueiam ação
2. Usar `green` para confirmação de sucesso
3. Usar `yellow` para avisos que requerem atenção mas não bloqueiam
4. Usar `cyan` para informações úteis e dicas
5. Usar `blue` para status de processamento
6. Usar `purple` para destaques especiais e promoções
7. Sempre incluir ícone relevante ao tipo de alerta
8. Botão de fechar é opcional - usar apenas quando alerta pode ser dismissado
9. Alertas inline (na página) são diferentes de Toasts (notificações flutuantes)

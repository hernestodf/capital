# Toast Component

## Uso
- Notificações temporárias flutuantes
- Feedback de ações do usuário
- Alertas não-bloqueantes
- Confirmações rápidas

## Classes Base

### Cores
- `toast red` - Erro, falha, perigo
- `toast green` - Sucesso, conclusão
- `toast cyan` - Informação, padrão
- `toast yellow` - Atenção, aviso
- `toast purple` - Destaque, especial
- `toast orange` - Alerta importante

### Estrutura
- `#toast-container` - Container fixo (bottom-right)
- `toast` - Toast individual
- `toast-icon` - Ícone (36x36px)
- `toast-body` - Container do texto
- `toast-title` - Título em negrito
- `toast-msg` - Mensagem detalhada
- `toast-close` - Botão de fechar

### JavaScript
```javascript
// Mostrar toast
showToast(type, title, message, duration)

// Parâmetros:
// type: 'red' | 'green' | 'cyan' | 'yellow' | 'purple' | 'orange'
// title: string (obrigatório)
// message: string (opcional)
// duration: milissegundos (default: 4500)

// Fechar toast manualmente
dismissToast(toastElement)
```

## Exemplos

### Toast de Erro
```javascript
showToast('red', 'Erro!', 'Alguma operação falhou.');
```

### Toast de Sucesso
```javascript
showToast('green', 'Sucesso!', 'Operação concluída com êxito.');
```

### Toast de Informação
```javascript
showToast('cyan', 'Informação!', 'Dados atualizados.');
```

### Toast de Aviso
```javascript
showToast('yellow', 'Atenção!', 'Verifique antes de continuar.');
```

### Toast Especial
```javascript
showToast('purple', 'Destaque!', 'Novo recurso disponível.');
```

### Toast Alerta
```javascript
showToast('orange', 'Alerta!', 'Ação requer atenção.');
```

### Toast com Duração Customizada
```javascript
showToast('green', 'Salvo!', 'Dados salvos com sucesso.', 3000);
```

### Trigger via HTML (onclick)
```html
<button class="btn btn-red" onclick="showToast('red','Erro!','Falha ao salvar.')">
  Toast Erro
</button>

<button class="btn btn-green" onclick="showToast('green','Sucesso!','Operação concluída.')">
  Toast Sucesso
</button>

<button class="btn btn-cyan" onclick="showToast('cyan','Info!','Dados atualizados.')">
  Toast Info
</button>
```

## Regras para IA
1. Usar toast para feedback de ações (NÃO usar alert())
2. Toast é não-bloqueante (diferente de modal)
3. Toast desaparece automaticamente (default 4.5s)
4. Toast aparece no canto inferior direito
5. Toast empilha verticalmente (mais recente no topo)
6. Sempre incluir título curto e descritivo
7. Mensagem é opcional - usar para contexto adicional
8. `red` para erros de operação
9. `green` para sucesso de operações
10. `cyan` para informações neutras
11. `yellow` para avisos que requerem atenção
12. Duração customizada: 3000ms para info, 6000ms para erros críticos

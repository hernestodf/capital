# Relatório: Código Morto - WhatsApp

**Data:** 2026-06-04
**Contexto:** Remoção do uso do WhatsApp

## Métodos Não Utilizados em WhatsAppService

| Método | Linha | Status |
|--------|-------|--------|
| `sendMedia()` | 106 | NÃO USADO |
| `listSessions()` | 321 | NÃO USADO |
| `createSession()` | 302 | NÃO USADO (privado) |
| `formatWhatsAppId()` | 377 | NÃO USADO (privado) |

## Controllers que Usam WhatsAppService

| Controller | Métodos Usados |
|------------|----------------|
| `ConfiguracoesController` | `getStatus()`, `getQrImage()`, `restartSession()`, `logout()`, `sendMessage()` |
| `ContasPagarController` | `generateComprovanteMessage()`, `sendMessage()`, `getStatus()`, `restartSession()`, `logout()` |

## Views que Referenciam WhatsApp

| View | Uso |
|------|-----|
| `views/public/test-whatsapp-bot.php` | Teste - pode remover |
| `views/colaborador/index.php` | Botão "Enviar Link" |
| `views/configuracoes/index.php` | Painel de configuração |

## Recomendações

### Manter (comportamento atual)
- `WhatsAppService.php` - usado em ContasPagar e Configurações
- Controllers e views referenciados

### Remover (se WhatsApp for desativado)
```bash
# Remover serviço
rm /var/www/html/newsisloc/src/Service/WhatsAppService.php

# Remover referências nos controllers
# Editar: ConfiguracoesController.php, ContasPagarController.php

# Remover views de teste
rm /var/www/html/newsisloc/public/test-whatsapp-bot.php

# Remover botões de envio no WhatsApp
# Editar: views/colaborador/index.php, views/configuracoes/index.php
```

### Atualizar .env
```bash
# Remover variáveis WhatsApp
# WHATSAPP_BOT_URL
# WHATSAPP_API_KEY
# WHATSAPP_SESSION_ID
# WHATSAPP_PUBLIC_URL
```
# Código Morto Identificado

## Status: 2026-06-05

### 1. Módulo de IA (AI)
**Status:** ATIVO - NÃO é código morto

| Arquivo | Uso |
|---------|-----|
| `src/Controllers/AIController.php` | Controlador de IA - rotas `/ai/*` |
| `src/Service/AIService.php` | Serviço de IA |
| `src/Repository/AILearningRepository.php` | Repositório de aprendizado |

**Observação:** Módulo estruturado e pode ser útil para:
- Assistente de diagnóstico
- Análise de logs
- Sugerências de otimização

### 2. ISP/MikroTik
**Status:** NÃO IMPLEMENTADO - NÃO há código morto

Pesquisa realizada:
- NENHUMAR referência encontrada em `src/` ou `views/`
- NENHUMA integração RADIUS ativa
- NENHUMA tabela `mikrotik_*`, `radius_*`, `pppoe_*` no banco

### 3. WhatsApp API
**Status:** ATIVO - Integração funcionando

| Arquivo | Uso |
|---------|-----|
| `src/Service/WhatsAppService.php` | Serviço WhatsApp |
| Integração com `WHATSAPP_API_KEY` no .env | Ativa |

### 4. ProFox Networks API
**Status:** REMOVIDO

Arquivos removidos:
- `src/Service/CentralSyncService.php`
- `src/Service/CategoriaSincronizadoService.php`
- `src/Service/CategoriaSalaCentralService.php`

### 5. Tabelas de IA Removidas
As seguintes tabelas foram removidas do banco (não possuíam dados):
- `ai_corrections`
- `ai_executions`
- `ai_feedbacks`
- `ai_interactions`
- `ai_learning`
- `ai_learning_sessions`
- `ai_metrics`
- `ai_preferences`

### 6. INSTANCE (Identificação de Instância)
**Status:** NÃO é código morto

`INSTANCE` é uma variável de ambiente que identifica qual instância do sistema SisLoc está rodando:
- `mt` = Minas Gerais (servidor principal)
- `ba` = Bahia
- `df` = Distrito Federal

Usado em:
- `scripts/deploy.sh` - Deploy automatizado
- `scripts/sync-instances.sh` - Sincronização entre instâncias
- `src/Core/Env.php` - Carregamento de configurações

## Conclusão

Após análise:
- ✅ Módulo de IA está ativo
- ✅ ISP/MikroTik não foi implementado (não há código morto)
- ✅ WhatsApp API está ativo
- ✅ ProFox API foi removida com sucesso
- ✅ INSTANCE é funcional, não é código morto
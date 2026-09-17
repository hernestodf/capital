# Relatório: Código Morto - Módulo de IA

**Data:** 2026-06-04
**Contexto:** Remoção do módulo de IA

## Arquivos do Módulo de IA

### Controllers
| Arquivo | Linhas | Uso |
|---------|--------|-----|
| `src/Controllers/AIController.php` | 300+ | **ATIVO** - rotas em `/ai/*` |
| `views/public/index.php` | - | **ATIVO** - referências |

### Services
| Arquivo | Métodos | Uso |
|---------|---------|-----|
| `src/Service/AIService.php` | 15+ | **ATIVO** - usado por AIController |

### Repositories
| Arquivo | Métodos | Uso |
|---------|---------|-----|
| `src/Repository/AILearningRepository.php` | 10+ | **ATIVO** - usado por AIService |

## Tabelas do Banco (Produção)

| Tabela | Registros | Status |
|--------|-----------|--------|
| `ai_corrections` | 0 | NÃO USADA |
| `ai_executions` | 0 | NÃO USADA |
| `ai_feedbacks` | 0 | NÃO USADA |
| `ai_interactions` | 0 | NÃO USADA |
| `ai_learning` | 0 | NÃO USADA |
| `ai_learning_sessions` | 0 | NÃO USADA |
| `ai_metrics` | 0 | NÃO USADA |
| `ai_preferences` | 0 | NÃO USADA |

## Análise

### Se REMOVER o módulo de IA:

```bash
# Remover arquivos
rm /var/www/html/newsisloc/src/Controllers/AIController.php
rm /var/www/html/newsisloc/src/Service/AIService.php
rm /var/www/html/newsisloc/src/Repository/AILearningRepository.php

# Remover tabelas do banco
mysql -u capital -pMarcelo123 -D capital -e "
DROP TABLE IF EXISTS ai_corrections, ai_executions, ai_feedbacks, 
ai_interactions, ai_learning, ai_learning_sessions, ai_metrics, ai_preferences;
"

# Remover rotas do index.php
# Remover referências a AIController
```

### Se MANTER o módulo de IA:

- Atualizar `src/Controllers/AIController.php`
- Popular tabelas `ai_*` com dados
- Configurar endpoints de API

## Recomendação

**MANTER** - o módulo de IA está estruturado e pode ser útil para:
- Assistente de diagnóstico
- Análise de logs
- Sugerências de otimização

Se for **remover**, execute os comandos acima.
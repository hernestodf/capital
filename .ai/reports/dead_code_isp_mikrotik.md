# Relatório: Código Morto - ISP/MikroTik

**Data:** 2026-06-04
**Contexto:** Remoção do uso de ISP e MikroTik

## Pesquisa Realizada

### MikroTik/RouterOS
- **Resultado:** NENHUMAR referência encontrada em `src/` ou `views/`
- **Conclusão:** Não há código ativo de MikroTik

### ISP/PPPoE
- **Resultado:** Referências apenas em:
  - Textos de contrato ("TERMO DE DISPONIBILIDADE...")
  - Comentários genéricos
- **Conclusão:** Não há funcionalidade ativa de ISP

### RADIUS/Secrets
- **Resultado:** Referências em:
  - MailjetService.php (e-mails)
  - EmailService.php (e-mails)
  - Outros arquivos (strings "secret" em contextos genéricos)
- **Conclusão:** Não há integração RADIUS ativa

## Tabelas do Banco Não Utilizadas

| Tabela | Status |
|--------|--------|
| `mikrotik_*` | NÃO EXISTE |
| `radius_*` | NÃO EXISTE |
| `pppoe_*` | NÃO EXISTE |

## Conclusão

**NÃO HÁ CÓDIGO MORTO de MikroTik ou ISP a ser removido.**

O sistema não possui implementação ativa de MikroTik ou funcionalidades de ISP (PPPoE blocking/desblocking).

## Próximos Passos

Se o sistema for migrado para outro domínio (não ISP):
1. Atualizar `context/business_rules.md` - remover seções de ISP
2. Atualizar `.env` - manter apenas integrações ativas
3. Remover referências de MikroTik da documentação
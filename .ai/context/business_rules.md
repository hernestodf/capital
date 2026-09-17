# Regras de Negócio
## Clientes / Planos / Boletos / Vencimentos
- Gestão de clientes com planos de fibra
- Boletos gerados com vencimento automático
- Integração com MikroTik para PPPoE

## Bloqueio / Desbloqueio
- Bloqueio automático de serviço em dias de atraso
- Liberação via painel ou pagamento

## Rotinas agendadas (cron/mensal)  <- NÃO marcar como código morto
- CronImapController: verificação de e-mails
- CronRHController: recrutamento
- MigracoesController: execução de migrações

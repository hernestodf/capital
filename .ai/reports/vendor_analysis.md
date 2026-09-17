# Relatório: Análise de Dependências (vendor/)

**Data:** 2026-06-04

## Dependências no composer.json

| Pacote | Versão | Uso no Código |
|--------|--------|---------------|
| php | >=8.0 | Requerido |
| mpdf/mpdf | ^8.3 | **USADO** - Geração de PDFs |
| phpmailer/phpmailer | ^7.0 | **USADO** - Envio de e-mails |
| webklex/php-imap | ^6.2 | **USADO** - Integração IMAP |
| phpoffice/phpspreadsheet | ^5.7 | **USADO** - import-xlsx.php |

## Vendor Extras (não listados em composer.json)

| Pacote | Uso | Observação |
|--------|-----|------------|
| illuminate/support | Paginação | **NECESSÁRIO** - dependência transitiva de illuminate/collections |
| illuminate/pagination | Paginação | **NECESSÁRIO** - dependência de illuminate/support |
| symfony/* | Vários | Dependência transitiva - **NÃO REMOVER** |
| carbonphp/carbon | Data/Time | Dependência transitiva - **NÃO REMOVER** |

## Análise de Uso

### USADO (necessário)
- **mpdf/mpdf** - PdfGeneratorService, EventoController, FechamentoController
- **phpmailer/phpmailer** - EmailService, MailjetService (fallback SMTP)
- **webklex/php-imap** - ImapService, CronImapController
- **phpoffice/phpspreadsheet** - import-xlsx.php (script de importação)

### NÃO USADO DIRETAMENTE
- **illuminate/support** - Usado apenas pela função paginate() custom, mas é dependência de illuminate/collections
- **symfony/console** - Não usado
- **symfony/process** - Não usado
- **maennchen/zipstream-php** - Não identificado uso
- **setasign/fpdi** - Não identificado uso
- **nesbot/carbon** - Não usado diretamente
- **markbaker/complex** - Não usado

## Recomendação

**MANTER TODOS OS PACOTES** - Remoção não é viável porque:
1. `mpdf`, `phpmailer`, `webklex/php-imap` são críticos
2. `phpoffice/phpspreadsheet` usado em script de importação
3. `illuminate/support` é dependência de `illuminate/collections` (atualizado automaticamente)
4. Pacotes como `symfony/*` e `carbon` são dependências transitivas de outras libs
5. Remoção pode quebrar autoload e funcionalidades

## Próximos Passos

1. Executar `composer install --no-dev` em produção
2. Verificar se `import-xlsx.php` precisa ser migrado ou mantido
3. Rodar `composer audit` para vulnerabilidades

## Status de Segurança

- ✅ `setasign/fpdi` atualizado de v2.6.6 para v2.6.7 (corrige CVE-2026-45802)
# Relatório: Limpeza do Projeto

**Data:** 2026-06-04

## Status: CONCLUÍDO

### Itens Removidos

| Diretório/Arquivo | Motivo |
|-------------------|--------|
| `Service/` (raiz) | Duplicado - versão em `src/Service/` é a correta |
| `backup_git_ai.tar.gz` | Backup antigo |
| `_*.php` (raiz) | Scripts de debug/autoload |
| `diag*.php` (raiz) | Scripts de debug |
| `test*.php` (raiz) | Scripts de teste |
| `debug*.php` (raiz) | Scripts de debug |
| `import-*.php` (raiz) | Scripts de importação |
| `fix-autoload.php` (raiz) | Debug |
| `camera.html` (raiz) | Não identificado |
| `*.apk` (raiz) | Apps móveis antigos |
| `*SEBRAE*.xlsx` (raiz) | Arquivo sensível |
| `public/_*.php` | Scripts de debug |
| `public/diag*.php` | Scripts de debug |
| `public/test*.php` | Scripts de teste |
| `public/debug*.php` | Scripts de debug |
| `public/import-*.php` | Scripts de importação |
| `public/fix-autoload.php` | Debug |
| `public/*.apk` | Apps móveis antigos |
| `public/error_log` | Log de erro |
| `public/montaapp/` | Diretório vazio |

### Itens Mantidos (com justificativa)

| Item | Justificativa |
|------|---------------|
| `public/index.php` | Entrada principal da aplicação |
| `public/import-xlsx.php` | Script de importação (manter se usado) |
| `public/css/`, `public/js/` | Assets estáticos |
| `public/uploads/` | Uploads de usuários |
| `public/uploads_old/` | Backup de uploads - verificar antes de remover |
| `public/presencaapp/` | PWA API em uso |
| `public/montaapp/` | Removido (estava vazio) |

### Ferramentas de Debug (.ai/scripts/)

| Script | Função |
|--------|--------|
| `healthcheck.sh` | Verifica PHP, MySQL, API |
| `diagnostics.sh` | Informações do sistema |
| `log_scan.sh` | Escaneia logs de erro |
| `error_analyzer.sh` | Análise de erros 500 |
| `error_diagnoser.php` | Diagnóstico de código |
| `db_check.sh` | Verifica conexão BD |
| `monitor_errors.sh` | Monitoramento em tempo real |
| `backup_env.sh` | Backup seguro do .env |

### Itens Removidos Adicionalmente

| Diretório/Arquivo | Motivo |
|-------------------|--------|
| `BOOTSTRAP_v1.md`, `BOOTSTRAP_v2.md` | Histórico - versão atual em `.ai/` |
| `favicon.ico` | Não referenciado |
| `debug_test.html`, `test-debug-cotacao.html`, `test_location.txt` | Debug |
| `deploy/` | Scripts Python obsoletos |
| `tmp/*` | Cache do mPDF |

### Status Final

| Categoria | Status |
|-----------|--------|
| Arquivos removidos | 15+ |
| Diretórios removidos | `deploy/`, `tmp/*` |
| Manter | `devolucao/`, `docs/`, `uploads_old/` |
| Observação | `uploads_old/` contém comprovantes - verificar antes de remover |
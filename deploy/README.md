# Deploy - NovoFramework

Script para deploy automático via FTP.

## Requisitos

```bash
pip install -r requirements.txt
```

## Configuração

Preencha os dados FTP no arquivo `.env` na raiz do projeto:

```env
FTP_HOST=ftp.seudominio.com
FTP_PORT=21
FTP_USER=usuario_ftp
FTP_PASS=senha_ftp
FTP_PATH=/public_html/novoframework
```

## Uso

```bash
cd deploy
python3 deploy.py
```

### Menu:

1. **Testar conexão FTP** - Verifica se consegue conectar no servidor
2. **Enviar arquivos via FTP** - Faz upload da pasta public/
3. **Exportar banco de dados local** - Gera dump SQL
4. **Deploy completo** - Exporta DB + envia arquivos
5. **Sair**

## O que é enviado

Arquivos enviados (pasta `public/`):
- PHP
- CSS
- JS
- Imagens
- Views

Arquivos **ignorados**:
- vendor/
- .env
- .git
- *.pyc
- *.sql
- database/
- storage/

## Fluxo completo

```bash
# 1. Exportar banco local (opcional)
mysqldump -u root -pProfox123 novoframework > database/dump.sql

# 2. Enviar via FTP
python3 deploy.py
# Escolher opção 2 ou 4

# 3. No servidor: criar .env de produção com DB_ONLINE_*

# 4. No servidor: importar dump.sql
mysql -u admin -p database < dump.sql
```

## Dicas

- O script detecta arquivos modificados e envia apenas os necessários
- Mostra progresso de upload
- Trata erros de conexão automaticamente

## CI/CD (GitHub Actions)

O workflow `.github/workflows/ci-cd.yml` roda em todo push/PR para `main`:

1. **Job `ci`** — `composer validate`, `composer install`, lint de sintaxe PHP
   (`php -l`) em `src/`, `views/`, `config/`, `public/`, e `eslint` em `src/js/`.
2. **Job `deploy`** — só roda em push direto para `main` e só se `ci` passar.
   Chama `python3 deploy/deploy.py --upload` (modo não-interativo) usando
   credenciais FTP vindas de **GitHub Secrets** (`FTP_HOST`, `FTP_PORT`,
   `FTP_USER`, `FTP_PASS`, `FTP_PATH`), nunca do `.env` do repositório.

Para rodar o deploy manualmente sem o menu interativo (ex: outro CI, cron):

```bash
python3 deploy/deploy.py --upload           # envia arquivos via FTP e sai
python3 deploy/deploy.py --test-connection  # testa conexao FTP e sai
```

Configure os secrets em GitHub → Settings → Secrets and variables → Actions,
com os mesmos valores que hoje estão no `.env` local (`FTP_HOST`, `FTP_PORT`,
`FTP_USER`, `FTP_PASS`, `FTP_PATH`). O `.env` continua sendo usado apenas em
execuções locais/interativas.
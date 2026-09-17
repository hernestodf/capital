# 🚀 GUIA DE DEPLOY PRODUCTION-READY — SISLOC v3.0.0

**Data da Análise:** 27 de Abril de 2026  
**Versão do Sistema:** 3.0.0  
**Framework:** ConectaFramework (MVC Custom PHP)  
**Analista:** AI Engineer DevOps Senior

---

## 📋 PASSO 1 — STACK TECNOLÓGICA

### **Stack Identificada:**

| Componente | Versão/Technology | Observação |
|------------|-------------------|------------|
| **PHP** | >= 8.4.11 | Requerido >= 8.0, testado em 8.4 |
| **Framework** | ConectaFramework (MVC Custom) | Desenvolvimento próprio |
| **Servidor Web** | Apache 2.4.64 | Com mod_rewrite |
| **Banco de Dados** | MySQL/MariaDB | Via PDO |
| **Gerenciador de Dependências** | Composer | PSR-4 autoload |
| **CSS/JS** | Design System 3.0 "White Rabbit" | Sem npm/build |
| **Bot WhatsApp** | Node.js (externo) | API REST em http://201.23.68.17:3000 |

### **Dependências PHP (composer.json):**

```json
{
    "mpdf/mpdf": "^8.3",           // Geração de PDFs
    "phpmailer/phpmailer": "^7.0", // Envio de emails SMTP
    "webklex/php-imap": "^6.2"     // Recebimento de emails IMAP
}
```

### **Estrutura de URLs:**

```
Entry Point: /var/www/html/sisloc/public/index.php
Rewrite:     public/.htaccess (Apache mod_rewrite)
Base URL:    http://localhost/sisloc/public (local)
             https://dominio.com (produção)
```

### **Arquivo de Configuração Principal:**

```
.env                    → Variáveis de ambiente (NÃO versionado)
config/app.php         → Configurações da aplicação (tema, versão)
src/Database/Connection.php → Conexão DB baseada em APP_ENV
```

---

## 📋 PASSO 2 — ARQUIVOS DE CONFIGURAÇÃO

### **2.1 Arquivo .env (CRÍTICO)**

**Caminho:** `/var/www/html/sisloc/.env`  
**Status no Git:** ❌ NÃO COMMITAR (está no .gitignore)  
**Modelo:** `.env.example` deve ser versionado

### **Tabela de Variáveis de Ambiente:**

| Variável | Valor Local | Valor Produção | Sensível |
|----------|-------------|----------------|----------|
| `APP_ENV` | `local` | `production` | Não |
| `BASE_URL` | `http://localhost/sisloc/public` | `https://sisloc.online` | Não |
| `DB_LOCAL_HOST` | `localhost` | _(não usado em prod)_ | Não |
| `DB_LOCAL_PORT` | `3306` | _(não usado em prod)_ | Não |
| `DB_LOCAL_NAME` | `sisloc` | _(não usado em prod)_ | Não |
| `DB_LOCAL_USER` | `admin` | _(não usado em prod)_ | **Sim** |
| `DB_LOCAL_PASS` | `admin` | _(não usado em prod)_ | **Sim** |
| `DB_ONLINE_HOST` | `sisloc.online` | `localhost` ou IP DB | Não |
| `DB_ONLINE_PORT` | `3306` | `3306` | Não |
| `DB_ONLINE_NAME` | `sisloc_novo` | `sisloc_producao` | Não |
| `DB_ONLINE_USER` | `Birobiro987!` | `sisloc_user` | **Sim** |
| `DB_ONLINE_PASS` | `admin` | `SENHA_FORTE_AQUI` | **Sim** |
| `DB_CHARSET` | `utf8mb4` | `utf8mb4` | Não |
| `SESSION_LIFETIME` | `120` | `120` | Não |
| `WHATSAPP_BOT_URL` | `http://201.23.68.17:3000` | Mesmo ou produção | Não |
| `WHATSAPP_API_KEY` | `06892917...` | Manter ou trocar | **Sim** |
| `WHATSAPP_SESSION_ID` | `novoframework` | `sisloc_prod` | Não |
| `WHATSAPP_PUBLIC_URL` | `https://201.23.68.17/sisloc/public` | `https://sisloc.online` | Não |
| `MAILJET_API_KEY` | `cb126467...` | Manter | **Sim** |
| `MAILJET_SECRET_KEY` | `ee35686e...` | Manter | **Sim** |
| `MAILJET_FROM_EMAIL` | `profox@sisloc.online` | Manter | Não |
| `SMTP_HOST` | `mail.sisloc.online` | Manter | Não |
| `SMTP_PORT` | `587` | `587` | Não |
| `SMTP_USER` | `profox@sisloc.online` | Manter | **Sim** |
| `SMTP_PASS` | `Micro987!` | Manter | **Sim** |
| `IMAP_HOST` | `mail.sisloc.online` | Manter | Não |
| `IMAP_PORT` | `993` | `993` | Não |
| `IMAP_USER` | `profox@sisloc.online` | Manter | **Sim** |
| `IMAP_PASS` | `Micro987!` | Manter | **Sim** |
| `CRON_SECRET` | `CHANGE_ME_SECRET` | `SENHA_CRON_FORTE` | **Sim** |
| `UPLOAD_PATH` | `/var/www/html/sisloc/storage/uploads` | Mesmo caminho em prod | Não |

### **2.2 Arquivos com Configuração Hardcoded:**

| Arquivo | Linha | Configuração | Problema |
|---------|-------|--------------|----------|
| `public/.htaccess` | 3 | `RewriteBase /sisloc/public/` | ❌ Deve mudar para `/` em produção raiz |
| `src/Service/UploadService.php` | 78 | `mkdir($uploadDir, 0755, true)` | ✅ OK |
| `src/Core/Logger.php` | 17 | `mkdir(self::$logPath, 0777, true)` | ⚠️ 777 inseguro |

### **2.3 Arquivo .env.example (DEVE SER CRIADO):**

```bash
# AMBIENTE
APP_ENV=production

# URL BASE
BASE_URL=https://seu-dominio.com

# DATABASE PRODUÇÃO
DB_ONLINE_HOST=localhost
DB_ONLINE_PORT=3306
DB_ONLINE_NAME=nome_banco
DB_ONLINE_USER=usuario_banco
DB_ONLINE_PASS=senha_segura

# WHATSAPP API
WHATSAPP_BOT_URL=http://ip-bot:3000
WHATSAPP_API_KEY=chave_api_whatsapp
WHATSAPP_SESSION_ID=sisloc_prod
WHATSAPP_PUBLIC_URL=https://seu-dominio.com

# EMAIL
MAILJET_API_KEY=sua_key
MAILJET_SECRET_KEY=seu_secret
MAILJET_FROM_EMAIL=noreply@seu-dominio.com

SMTP_HOST=mail.seu-dominio.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=email@seu-dominio.com
SMTP_PASS=senha_email

# IMAP
IMAP_HOST=mail.seu-dominio.com
IMAP_PORT=993
IMAP_USER=email@seu-dominio.com
IMAP_PASS=senha_email

# CRON
CRON_SECRET=senha_cron_muito_segura

# UPLOAD
UPLOAD_PATH=/var/www/html/sisloc/storage/uploads
```

---

## 📋 PASSO 3 — MODO DEBUG

### **3.1 Configuração Atual:**

**Arquivo:** `src/Core/ErrorHandler.php`  
**Linhas:** 14-16

```php
// LINHA 14
error_reporting(E_ALL);

// LINHA 15
ini_set('display_errors', '0');

// LINHA 16
ini_set('log_errors', '1');
```

**Comportamento por ambiente (linhas 61-66, 95-99):**
- `APP_ENV=local`: Mostra página de debug completa com stack trace
- `APP_ENV=production`: Mostra página genérica "Erro Interno" com ID

### **3.2 ATIVAR Debug (para desenvolvimento/testing):**

**ARQUIVO:** `.env`  
**ALTERAR:**
```env
DE:   APP_ENV=production
PARA: APP_ENV=local
```

**ARQUIVO:** `src/Core/ErrorHandler.php` (OPCIONAL, se quiser ver erros na tela em prod)  
**LINHA:** 15
```php
DE:   ini_set('display_errors', '0');
PARA: ini_set('display_errors', '1');
```

### **3.3 DESATIVAR Debug (PRODUÇÃO):**

**ARQUIVO:** `.env`
```env
APP_ENV=production
```

**ARQUIVO:** `src/Core/ErrorHandler.php`  
**LINHA:** 15
```php
ini_set('display_errors', '0');
```

✅ **Já está configurado corretamente para produção!** O sistema:
- NÃO exibe erros em produção (display_errors = 0)
- LOGA todos os erros em `storage/logs/`
- Mostra página amigável com ID do erro

### **3.4 Logs de Erro:**

**Caminho:** `/var/www/html/sisloc/storage/logs/`  
**Formato:** `error-YYYY-MM-DD.log`  
**Rotação:** Diária (automática pelo Logger.php)

**Arquivo:** `src/Core/Logger.php` (linhas 15-50)
```php
private static string $logPath = __DIR__ . '/../../storage/logs';
```

---

## 📋 PASSO 4 — PERMISSÕES DE PASTAS

### **4.1 Pastas que Precisam de Escrita:**

Baseado na análise de código (`move_uploaded_file`, `mkdir`, `file_put_contents`):

| Pasta | Uso | Permissão | Criada Por |
|-------|-----|-----------|------------|
| `storage/uploads/` | Uploads de arquivos | 775 | Vários controllers |
| `storage/uploads/comprovantes/` | Comprovantes de pagamento | 775 | ContasPagarController |
| `storage/uploads/fotos_eventos/` | Fotos de eventos | 775 | EventoFotoService, FechamentoController |
| `storage/uploads/cotacoes/` | Anexos de cotações | 775 | CotacaoController |
| `storage/uploads/logos/` | Logos da empresa | 775 | ConfiguracoesController |
| `storage/logs/` | Logs de erro | 775 | Logger.php |
| `tmp/mpdf/` | Cache do mPDF | 777 | PdfGeneratorService |
| `tmp/ttfontdata/` | Fontes do mPDF | 777 | PdfGeneratorService |
| `tmp/ttfonts/` | Fontes do mPDF | 777 | PdfGeneratorService |
| `storage/theme.json` | Tema ativo | 664 | TemaService |

### **4.2 Comandos de Permissão (PRODUÇÃO):**

```bash
# ============================================
# OPÇÃO 1: Permissões abertas (menos seguro)
# ============================================
cd /var/www/html/sisloc

# Pastas de upload
chmod -R 777 storage/uploads
chmod -R 777 storage/uploads/comprovantes
chmod -R 777 storage/uploads/fotos_eventos
chmod -R 777 storage/uploads/cotacoes
chmod -R 777 storage/uploads/logos

# Logs
chmod -R 777 storage/logs

# Temp (mPDF)
chmod -R 777 tmp/mpdf
chmod -R 777 tmp/ttfontdata
chmod -R 777 tmp/ttfonts

# Theme
chmod 666 storage/theme.json
```

```bash
# ============================================
# OPÇÃO 2: Permissões seguras (RECOMENDADO)
# ============================================
cd /var/www/html/sisloc

# Dono: www-data (Apache), Grupo: www-data
chown -R www-data:www-data /var/www/html/sisloc

# Pastas: 755 (dono write, outros read+execute)
find /var/www/html/sisloc -type d -exec chmod 755 {} \;

# Arquivos: 644 (dono write, outros read)
find /var/www/html/sisloc -type f -exec chmod 644 {} \;

# Pastas de upload: 775 (grupo write)
chmod -R 775 storage/uploads
chmod -R 775 storage/uploads/comprovantes
chmod -R 775 storage/uploads/fotos_eventos
chmod -R 775 storage/uploads/cotacoes
chmod -R 775 storage/uploads/logos

# Logs: 775
chmod -R 775 storage/logs

# Temp mPDF: 775
chmod -R 775 tmp/mpdf
chmod -R 775 tmp/ttfontdata
chmod -R 775 tmp/ttfonts

# Theme: 664
chmod 664 storage/theme.json

# .env: 640 (somente dono e grupo)
chmod 640 .env
```

### **4.3 Verificação Pós-Deploy:**

```bash
# Testar escrita
touch storage/uploads/test.txt && echo "✅ Uploads OK" || echo "❌ Uploads FAIL"
touch storage/logs/test.txt && echo "✅ Logs OK" || echo "❌ Logs FAIL"
touch tmp/mpdf/test.txt && echo "✅ Temp OK" || echo "❌ Temp FAIL"

# Limpar teste
rm -f storage/uploads/test.txt storage/logs/test.txt tmp/mpdf/test.txt
```

---

## 📋 PASSO 5 — CRON JOBS

### **5.1 Scripts Identificados:**

| # | Endpoint | Função | Frequência | Proteção |
|---|----------|--------|------------|----------|
| 1 | `/cron/imap-cotacao` | Processar emails recebidos (cotações) | A cada 5 min | `CRON_SECRET` |
| 2 | `/cron/rh-notificacoes` | Enviar notificações RH | A cada 1 hora | _(não implementada)_ |

### **5.2 Cron Jobs Configurados:**

```bash
# ============================================
# CRON JOBS - SISLOC PRODUÇÃO
# ============================================
# Editar: crontab -e

# 1. Processar emails IMAP de cotações (a cada 5 minutos)
#    - Recebe respostas de fornecedores por email
#    - Salva no banco de dados automaticamente
#    - Protegido por CRON_SECRET
*/5 * * * * curl -s "https://seu-dominio.com/cron/imap-cotacao?key=SENHA_CRON_AQUI" >> /var/www/html/sisloc/storage/logs/cron-imap.log 2>&1

# 2. Notificações RH (a cada 1 hora)
#    - Envia lembretes de alocação
#    - Notificações de presença
*/1 * * * * curl -s "https://seu-dominio.com/cron/rh-notificacoes?key=SENHA_CRON_AQUI" >> /var/www/html/sisloc/storage/logs/cron-rh.log 2>&1

# 3. Limpeza de logs antigos (diariamente às 3h)
#    - Remove logs com mais de 30 dias
0 3 * * * find /var/www/html/sisloc/storage/logs -name "*.log" -mtime +30 -delete >> /var/www/html/sisloc/storage/logs/cron-cleanup.log 2>&1

# 4. Limpeza de temp mPDF (semanalmente, domingo 4h)
#    - Remove cache antigo do mPDF
0 4 * * 0 find /var/www/html/sisloc/tmp -type f -mtime +7 -delete >> /var/www/html/sisloc/storage/logs/cron-cleanup.log 2>&1
```

### **5.3 Explicação dos Campos Cron:**

```
┌───── minuto (0-59)
│ ┌───── hora (0-23)
│ │ ┌───── dia do mês (1-31)
│ │ │ ┌───── mês (1-12)
│ │ │ │ ┌───── dia da semana (0-7, 0 e 7 = Domingo)
│ │ │ │ │
* * * * * comando
```

**Frequências sugeridas:**
- **IMAP (5 min):** Emails de cotação são críticos, devem ser processados rapidamente
- **RH (1 hora):** Notificações não são urgentes, hourly é suficiente
- **Limpeza logs (diário):** Evita disco cheio, 3h é horário de baixo uso
- **Limpeza temp (semanal):** mPDF não precisa de limpeza frequente

### **5.4 Testar Cron Jobs:**

```bash
# Testar manualmente
curl "https://seu-dominio.com/cron/imap-cotacao?key=SENHA_CRON_AQUI"

# Ver logs
tail -f /var/www/html/sisloc/storage/logs/cron-imap.log
tail -f /var/www/html/sisloc/storage/logs/cron-rh.log

# Verificar se cron está rodando
systemctl status cron
```

---

## 📋 PASSO 6 — ACESSO LOCAL E ONLINE SIMULTÂNEO

### **6.1 Mecanismo Atual:**

✅ **JÁ IMPLEMENTADO!** O sistema usa `.env` com detecção automática:

**Arquivo:** `src/Database/Connection.php` (linhas 18-40)

```php
$env = Env::get('APP_ENV', 'local');

if ($env === 'production') {
    // Usa DB_ONLINE_*
} else {
    // Usa DB_LOCAL_*
}
```

### **6.2 Como Funciona:**

| Ambiente | APP_ENV | Banco Usado | BASE_URL |
|----------|---------|-------------|----------|
| **Local** | `local` | `DB_LOCAL_*` | `http://localhost/sisloc/public` |
| **Produção** | `production` | `DB_ONLINE_*` | `https://sisloc.online` |

### **6.3 Estratégia Recomendada:**

**✅ MANTER COMO ESTÁ** — já é a melhor prática!

**Fluxo de Deploy:**
1. Código é o mesmo para local e produção
2. Apenas `.env` muda entre ambientes
3. `.env` NÃO está no git (.gitignore)
4. Criar `.env.example` como template

### **6.4 Ajuste Necessário no .htaccess:**

**Problema:** `RewriteBase` hardcoded para subpasta

**Arquivo:** `public/.htaccess` (linha 3)

**PARA SUBPASTA (ex: dominio.com/sisloc):**
```apache
RewriteBase /sisloc/public/
```

**PARA RAIZ (ex: dominio.com):**
```apache
RewriteBase /
```

**SOLUÇÃO AUTOMÁTICA (RECOMENDADA):**
```apache
# Detectar automaticamente
RewriteEngine On
RewriteCond %{REQUEST_URI} ^/sisloc/public/
RewriteRule ^ - [E=BASE:/sisloc/public/]
RewriteCond %{REQUEST_URI} !^/sisloc/public/
RewriteRule ^ - [E=BASE:/]
RewriteBase %{ENV:BASE}
```

---

## 📋 PASSO 7 — CHECKLIST DE DEPLOY

### **PRÉ-DEPLOY (Fazer ANTES de subir arquivos):**

```
[ ] 1. Criar .env.production baseado em .env.example
[ ] 2. Alterar APP_ENV=production no .env
[ ] 3. Configurar DB_ONLINE_* com credenciais do servidor
[ ] 4. Alterar BASE_URL para https://seu-dominio.com
[ ] 5. Trocar CRON_SECRET para senha forte
[ ] 6. Ajustar RewriteBase no .htaccess (se necessário)
[ ] 7. Exportar banco de dados local:
       mysqldump -u admin -p sisloc > database/sisloc_backup.sql
[ ] 8. Revisar .gitignore (garantir que .env não será commitado)
[ ] 9. Rodar composer install --no-dev (gerar autoload otimizado)
[ ] 10. Testar localmente com APP_ENV=production
```

### **COMANDOS PRÉ-DEPLOY:**

```bash
# 1. Backup do banco local
mysqldump -u admin -p sisloc > database/sisloc_$(date +%Y%m%d).sql

# 2. Instalar dependências de produção
cd /var/www/html/sisloc
composer install --no-dev --optimize-autoloader

# 3. Verificar se .env está no .gitignore
grep -q "^\\.env$" .gitignore && echo "✅ .env protegido" || echo "❌ ADICIONAR .env AO .gitignore"

# 4. Criar .env.example se não existir
if [ ! -f .env.example ]; then
    cp .env .env.example
    # Remover senhas do exemplo
    sed -i 's/DB_LOCAL_PASS=.*/DB_LOCAL_PASS=/' .env.example
    sed -i 's/DB_ONLINE_PASS=.*/DB_ONLINE_PASS=/' .env.example
    sed -i 's/WHATSAPP_API_KEY=.*/WHATSAPP_API_KEY=/' .env.example
    echo "✅ .env.example criado"
fi
```

---

### **NO SERVIDOR (Após upload dos arquivos):**

```
[ ] 1. Verificar versão do PHP (requerido >= 8.0)
[ ] 2. Verificar extensões PHP: pdo_mysql, mbstring, json, curl, gd
[ ] 3. Upload dos arquivos (exceto vendor/, .env, storage/logs/)
[ ] 4. Rodar composer install --no-dev
[ ] 5. Criar/editar .env no servidor
[ ] 6. Importar banco de dados
[ ] 7. Configurar permissões de pastas
[ ] 8. Configurar cron jobs
[ ] 9. Configurar virtual host Apache (se necessário)
[ ] 10. Reiniciar Apache
```

### **COMANDOS NO SERVIDOR:**

```bash
# 1. Verificar PHP
php -v  # Deve ser >= 8.0

# 2. Verificar extensões
php -m | grep -E "pdo_mysql|mbstring|json|curl|gd"

# 3. Instalar dependências
cd /var/www/html/sisloc
composer install --no-dev --optimize-autoloader

# 4. Criar .env
nano .env
# (copiar conteúdo de .env.production e ajustar)

# 5. Importar banco
mysql -u usuario -p nome_banco < database/sisloc_backup.sql

# 6. Executar migrations
mysql -u usuario -p nome_banco < database/migrations/024_create_api_keys.sql
mysql -u usuario -p nome_banco < database/migrations/025_create_api_request_logs.sql

# 7. Permissões
chown -R www-data:www-data /var/www/html/sisloc
find /var/www/html/sisloc -type d -exec chmod 755 {} \;
find /var/www/html/sisloc -type f -exec chmod 644 {} \;
chmod -R 775 storage/uploads
chmod -R 775 storage/logs
chmod -R 775 tmp

# 8. Cron jobs
crontab -e
# (adicionar entradas da seção 5.2)

# 9. Apache virtual host (se necessário)
nano /etc/apache2/sites-available/sisloc.conf

# 10. Reiniciar Apache
sudo systemctl restart apache2
sudo systemctl enable apache2
```

### **Virtual Host Apache (Opcional):**

```apache
<VirtualHost *:80>
    ServerName sisloc.online
    DocumentRoot /var/www/html/sisloc/public
    
    <Directory /var/www/html/sisloc/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sisloc-error.log
    CustomLog ${APACHE_LOG_DIR}/sisloc-access.log combined
</VirtualHost>

# Habilitar
sudo a2ensite sisloc.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

### **PÓS-DEPLOY (Verificação):**

```
[ ] 1. Testar URL: https://seu-dominio.com
[ ] 2. Testar login com credenciais de admin
[ ] 3. Testar upload de arquivo
[ ] 4. Testar geração de PDF
[ ] 5. Testar envio de email (cotação)
[ ] 6. Testar recebimento de email (IMAP cron)
[ ] 7. Verificar logs de erro: tail -f storage/logs/error-*.log
[ ] 8. Verificar Apache logs: tail -f /var/log/apache2/sisloc-error.log
[ ] 9. Testar API REST: curl -H "X-API-Key: chave" https://dominio/api/v1/colaboradores
[ ] 10. Verificar cron jobs: tail -f storage/logs/cron-*.log
```

### **COMANDOS PÓS-DEPLOY:**

```bash
# 1. Testar URL
curl -I https://seu-dominio.com

# 2. Verificar logs de erro em tempo real
tail -f /var/www/html/sisloc/storage/logs/error-$(date +%Y-%m-%d).log

# 3. Testar conexão com banco
php -r "require 'vendor/autoload.php'; print_r(\App\Database\Connection::testConnection());"

# 4. Testar API REST
curl -H "X-API-Key: sisloc_dev_key_2026" https://seu-dominio.com/api/v1/colaboradores

# 5. Verificar cron
curl "https://seu-dominio.com/cron/imap-cotacao?key=SENHA_CRON"

# 6. Testar upload
ls -la storage/uploads/

# 7. Verificar permissões
ls -la storage/
ls -la tmp/
```

---

### **QUANDO ESTÁVEL — FINALIZAÇÃO:**

```
[ ] 1. Confirmar que APP_ENV=production no .env
[ ] 2. Confirmar que display_errors=0
[ ] 3. Remover arquivos de debug/teste do servidor
[ ] 4. Configurar SSL (Let's Encrypt)
[ ] 5. Configurar backup automático do banco
[ ] 6. Configurar monitoramento (uptime, logs)
[ ] 7. Documentar credenciais em cofre seguro
[ ] 8. Notificar equipe que sistema está em produção
```

### **COMANDOS DE FINALIZAÇÃO:**

```bash
# 1. SSL com Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d seu-dominio.com

# 2. Backup automático do banco (cron diário)
echo "0 2 * * * mysqldump -u usuario -p'senha' nome_banco | gzip > /backup/sisloc_\$(date +\%Y\%m\%d).sql.gz" | crontab -

# 3. Remover arquivos de dev
rm -rf public/test/
rm -rf tests/
rm -f test_*.php

# 4. Verificar status final
php -v
mysql -V
apache2 -v
systemctl status apache2
systemctl status cron
```

---

## 📊 RESUMO EXECUTIVO

### **Tempo Estimado de Deploy:** 1-2 horas

### **Complexidade:** MÉDIA

### **Riscos Identificados:**

| Risco | Impacto | Mitigação |
|-------|---------|-----------|
| `.env` commitado no git | 🔴 CRÍTICO | Já está no .gitignore ✅ |
| Permissões 777 em produção | 🟡 MÉDIO | Usar 775 com www-data ✅ |
| Cron jobs não configurados | 🟡 MÉDIO | Seguir checklist |
| `.htaccess` RewriteBase errado | 🟡 MÉDIO | Ajustar conforme estrutura |
| WhatsApp bot fora do ar | 🟠 BAIXO | Sistema funciona sem ele |
| API Keys expostas | 🔴 CRÍTICO | Trocar em produção |

### **Pré-requisitos do Servidor:**

- ✅ PHP >= 8.0 (testado em 8.4)
- ✅ Apache 2.4+ com mod_rewrite
- ✅ MySQL 5.7+ ou MariaDB 10.3+
- ✅ Composer 2.x
- ✅ Extensões: pdo_mysql, mbstring, json, curl, gd, xml
- ✅ Cron daemon ativo
- ✅ SSL (Let's Encrypt recomendado)

---

## 🚨 PONTOS DE ATENÇÃO

### **1. SEGURANÇA:**
- ❌ NUNCA commitar `.env`
- ✅ Trocar `CRON_SECRET` em produção
- ✅ Trocar `WHATSAPP_API_KEY` se exposto
- ✅ Usar HTTPS obrigatoriamente
- ✅ Configurar firewall (portas 80, 443, 3306 bloqueada externamente)

### **2. PERFORMANCE:**
- ✅ Usar `composer install --optimize-autoloader`
- ✅ Configurar opcache PHP
- ✅ Usar CDN para assets (opcional)
- ✅ Configurar cache de navegador para assets estáticos

### **3. MONITORAMENTO:**
- ✅ Verificar logs diariamente
- ✅ Configurar alertas de erro (email/Slack)
- ✅ Monitorar espaço em disco
- ✅ Backup diário do banco

---

**📝 Documento gerado em:** 27/04/2026  
**🔒 Classificação:** CONFIDENCIAL  
**👥 Destinatário:** Equipe DevOps/Dev  

---

**FIM DO GUIA DE DEPLOY**

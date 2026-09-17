# Detecção Automática de Ambiente

## Como Funciona

O sistema detecta automaticamente se está rodando em **desenvolvimento local** ou **produção** baseado no nome do domínio (`HTTP_HOST`). Não é necessário editar arquivos ao fazer deploy.

## Princípio

```
Acessou via localhost     → Ambiente local  → Usa DB_LOCAL_*
Acessou via sisloc.online → Ambiente production → Usa DB_ONLINE_*
```

A detecção acontece no `src/Core/Env.php` durante o carregamento das configurações.

## Arquivo `.env` — Estrutura

O `.env` contém **TODAS** as configurações de ambos os ambientes:

```env
# APP_ENV comentado → detecção automática
# APP_ENV=local

BASE_URL=http://localhost/sisloc/public

# --- Banco LOCAL ---
DB_LOCAL_HOST=localhost
DB_LOCAL_NAME=sisloc
DB_LOCAL_USER=admin
DB_LOCAL_PASS=admin

# --- Banco PRODUÇÃO ---
DB_ONLINE_HOST=localhost
DB_ONLINE_NAME=sisloc_novo
DB_ONLINE_USER=sisloc_Birobiro
DB_ONLINE_PASS=Micro987!

# --- Produção ---
UPLOAD_PATH=/public_html/subdomains/profox/storage/uploads
WHATSAPP_PUBLIC_URL=https://profox.sisloc.online/public
```

O sistema lê tudo e escolhe automaticamente qual bloco usar.

## Regras de Detecção

### Local (desenvolvimento)

Detectado quando o `HTTP_HOST` **NÃO** contém nenhum dos domínios de produção:

| HTTP_HOST | Detectado como |
|---|---|
| `localhost` | local |
| `127.0.0.1` | local |
| `192.168.1.100` | local |
| `sisloc.local` | local |
| Qualquer outro | local |

**Usa:**
- `DB_LOCAL_HOST`, `DB_LOCAL_NAME`, `DB_LOCAL_USER`, `DB_LOCAL_PASS`
- Debug ativado (erros com stack trace completo)
- Sem redirecionamento HTTPS

### Produção

Detectado quando o `HTTP_HOST` contém um dos domínios registrados:

| HTTP_HOST | Detectado como |
|---|---|
| `sisloc.online` | production |
| `profox.sisloc.online` | production |
| `mt.sisloc.online` | production |
| `sisloc.com` | production |
| `profox.com` | production |

**Usa:**
- `DB_ONLINE_HOST`, `DB_ONLINE_NAME`, `DB_ONLINE_USER`, `DB_ONLINE_PASS`
- Erros genéricos (seguro para usuário final)
- Debug desativado

## Deploy para Produção

### Passo único: copiar arquivos

```bash
# Copia tudo para o servidor
rsync -avz --exclude=vendor --exclude=.git \
  . usuario@servidor:/public_html/subdomains/profox/
```

**NÃO precisa:**
- ~~Editar `.env` para trocar `APP_ENV`~~
- ~~Trocar `BASE_URL` manualmente~~
- ~~Alterar configuração de banco~~

O sistema detecta tudo sozinho.

## Multi-Instância (Mesmo Código, Servidores Diferentes)

O **mesmo código** roda em qualquer instância. Cada uma só precisa do seu `.env`:

### Instância ProFox (profox.sisloc.online)

```env
BASE_URL=https://profox.sisloc.online/public
DB_ONLINE_NAME=sisloc_novo
UPLOAD_PATH=/public_html/subdomains/profox/storage/uploads
```

### Instância MT (mt.sisloc.online)

```env
BASE_URL=https://mt.sisloc.online/public
DB_ONLINE_NAME=sisloc_novo_mt
UPLOAD_PATH=/public_html/subdomains/profoxmt/storage/uploads
```

**Código PHP:** exatamente o mesmo. Zero alterações.

## Fluxo Completo

```
Browser acessa https://profox.sisloc.online/public/dashboard
    │
    ▼
Apache recebe → PHP inicia
    │
    ▼
Env::load() lê .env na raiz do projeto
    │
    ├── Carrega todas variáveis do .env
    │
    ├── APP_ENV está definido?
    │   ├── SIM → usa o valor definido
    │   └── NÃO → detectEnvironment():
    │       ├── HTTP_HOST contém "sisloc.online"? → production
    │       └── Não contém? → local
    │
    ├── Normaliza BASE_URL (remove trailing slash)
    │
    ▼
Connection::load()
    ├── APP_ENV=production? → usa DB_ONLINE_*
    └── APP_ENV=local? → usa DB_LOCAL_*
    │
    ▼
Sistema roda com configurações corretas
```

## Adicionar Novo Domínio de Produção

Para que um novo domínio seja detectado como produção, adicione ao array em `Env::detectEnvironment()`:

```php
$productionIndicators = [
    'sisloc.online',
    'profox.sisloc.online',
    'mt.sisloc.online',     // ← novo
    'sisloc.com',
    'profox.com',
];
```

## Forçar Ambiente (Opcional)

Se precisar forçar um ambiente específico, descomente no `.env`:

```env
# Forçar local mesmo em produção (debug)
APP_ENV=local

# Forçar produção no desenvolvimento (testes)
APP_ENV=production
```

## O Que é Afetado pela Detecção

| Componente | Local | Produção |
|---|---|---|
| Banco de dados | `DB_LOCAL_*` | `DB_ONLINE_*` |
| Base URL | do `.env` local | do `.env` produção |
| Debug (erros detalhados) | Ativado | Desativado |
| Página de erro | Stack trace completo | Mensagem genérica |
| Upload de arquivos | Não usado | `UPLOAD_PATH` |
| WhatsApp links | `BASE_URL` | `WHATSAPP_PUBLIC_URL` |

## Arquivos Envolvidos

| Arquivo | Responsabilidade |
|---|---|
| `src/Core/Env.php` | Detecção automática (`detectEnvironment()`) |
| `src/Database/Connection.php` | Escolhe `DB_LOCAL_*` ou `DB_ONLINE_*` |
| `src/Core/ErrorHandler.php` | Exibe erro detalhado (local) ou genérico (produção) |
| `src/Core/Debug.php` | Ativa/desativa debug queries e logs |
| `.env` | Configurações de ambos os ambientes |

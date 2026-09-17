# API REST - SisLoc v3.0.0

## Visão Geral

API REST para integração externa com o SisLoc. Permite que sites e aplicativos externos enviem dados para o sistema de forma segura usando **API Keys**.

**Base URL:** `https://sisloc.seudominio.com/api/v1`

---

## 🔐 Autenticação

Todas as requisições devem incluir o header:

```
X-API-Key: sua_api_key_aqui
```

### Como obter uma API Key

1. Acesse o painel admin do SisLoc
2. Execute a migration: `mysql -u user -p db < database/migrations/024_create_api_keys.sql`
3. A API key de desenvolvimento é: `sisloc_dev_key_2026`
4. Para produção, gere uma nova key com: `hash('sha256', 'sua_chave_secreta')`

### API Key de Desenvolvimento

```
API Key: sisloc_dev_key_2026
Prefixo: sk_dev_001
Permissões: ["colaboradores:write", "fornecedores:write", "categorias:write", "subcategorias:write"]
Rate Limit: 1000 requests/hora
```

---

## 📋 Endpoints

### 1. Colaboradores

#### Listar Colaboradores
```http
GET /api/v1/colaboradores
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "origem": "Indicação",
      "tipo": "FUNCIONARIO",
      "estado_para_trabalho": "MG",
      "nome": "João Silva",
      "telefone": "31999999999",
      "email": "joao@email.com",
      "atua_como": "Técnico de Som",
      "foto": null,
      "cep": "30100010",
      "endereco": "Rua Teste",
      "bairro": "Centro",
      "cidade": "Belo Horizonte",
      "estado": "MG",
      "tipo_chave_pix": "cpf",
      "chavepix": "12345678900",
      "observacao": "",
      "ativo": 1
    }
  ]
}
```

#### Criar Colaborador
```http
POST /api/v1/colaboradores
Content-Type: application/json

{
  "nome": "João Silva",
  "email": "joao@email.com",
  "telefone": "31999999999",
  "origem": "Indicação",
  "tipo": "FUNCIONARIO",
  "estado_para_trabalho": "MG",
  "atua_como": "Técnico de Som",
  "cep": "30100010",
  "endereco": "Rua Teste",
  "bairro": "Centro",
  "cidade": "Belo Horizonte",
  "estado": "MG",
  "tipo_chave_pix": "cpf",
  "chavepix": "12345678900",
  "observacao": "Observações"
}
```

**Campos obrigatórios:**
- `nome` (string)

**Campos opcionais:**
- `email` (string, formato válido)
- `telefone` (string) — **alias**: `whatsapp` (para compatibilidade legada)
- `origem` (string)
- `tipo` (enum: `FUNCIONARIO` ou `FREELANCE`)
- `estado_para_trabalho` (string, UF ou "Todos")
- `atua_como` (string)
- `cep` (string, apenas números)
- `endereco` (string)
- `bairro` (string)
- `cidade` (string)
- `estado` (string, UF)
- `tipo_chave_pix` (string: `cpf`, `cnpj`, `email`, `telefone`, `aleatoria`)
- `chavepix` (string)
- `observacao` (text)

**Nota:** Os campos `documento`, `profissao`, `funcao_evento`, `valor_diaria` foram removidos da API (não existem no banco de dados). Use `telefone` em vez de `whatsapp`.

---

### 2. Fornecedores

#### Listar Fornecedores
```http
GET /api/v1/fornecedores
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_categoria": 1,
      "id_subcategoria": 2,
      "cpf_cnpj": "12.345.678/0001-90",
      "nome_fantasia": "Tech Som",
      "razao_social": "Tech Som Ltda",
      "telefone": "1133334444",
      "email": "contato@techsom.com",
      "cep": "30100010",
      "endereco": "Rua Teste",
      "numero": "123",
      "complemento": "Sala 1",
      "bairro": "Centro",
      "cidade": "São Paulo",
      "estado": "SP",
      "observacao": "",
      "status": 1
    }
  ]
}
```

#### Criar Fornecedor
```http
POST /api/v1/fornecedores
Content-Type: application/json

{
  "nome_fantasia": "Tech Som",
  "razao_social": "Tech Som Ltda",
  "cpf_cnpj": "12345678000195",
  "email": "contato@techsom.com",
  "telefone": "1133334444",
  "endereco": "Rua Teste, 123",
  "id_categoria": 1,
  "id_subcategoria": 2
}
```

**Campos obrigatórios:**
- `nome_fantasia` (string)

**Campos opcionais:**
- `razao_social` (string)
- `cpf_cnpj` (string) — **alias**: `cnpj` (para compatibilidade legada)
- `email` (string, formato válido)
- `telefone` (string)
- `cep` (string, apenas números)
- `endereco` (string)
- `numero` (string)
- `complemento` (string)
- `bairro` (string)
- `cidade` (string)
- `estado` (string, UF)
- `observacao` (text)

**Nota:** O campo `cnpj` foi renomeado para `cpf_cnpj` paraRefletir que pode ser CPF ou CNPJ. Ambos são aceitos (backward compatibility).

---

### 3. Categorias

#### Listar Categorias
```http
GET /api/v1/categorias
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "categoria": "Sonorização",
      "status": 1,
      "created_at": "2026-05-02 00:00:00"
    }
  ]
}
```

#### Criar Categoria
```http
POST /api/v1/categorias
Content-Type: application/json

{
  "categoria": "Sonorização"
}
```

**Campos obrigatórios:**
- `categoria` (string) — **alias**: `nome` (para compatibilidade legada)

**Campos opcionais:**
- Nenhum (o status é definido como 1 automaticamente)

**Nota:** O campo `nome` foi renomeado para `categoria`. Ambos são aceitos.

---

### 4. Subcategorias

#### Listar Subcategorias
```http
GET /api/v1/subcategorias
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_categoria": 1,
      "subcategoria": "Mesa de Som",
      "status": 1,
      "created_at": "2026-05-02 00:00:00"
    }
  ]
}
```

#### Criar Subcategoria
```http
POST /api/v1/subcategorias
Content-Type: application/json

{
  "subcategoria": "Mesa de Som",
  "id_categoria": 1
}
```

**Campos obrigatórios:**
- `subcategoria` (string) — **alias**: `nome` (para compatibilidade legada)
- `id_categoria` (int)

**Campos opcionais:**
- Nenhum (o status é definido como 1 automaticamente)

**Nota:** O campo `nome` foi renomeado para `subcategoria`. Ambos são aceitos.

---

## 🛡️ Segurança

### Permissões

Cada API key tem permissões específicas no formato `recurso:acao`:

| Permissão | Descrição |
|-----------|-----------|
| `colaboradores:read` | Listar colaboradores |
| `colaboradores:write` | Criar/editar colaboradores |
| `fornecedores:read` | Listar fornecedores |
| `fornecedores:write` | Criar/editar fornecedores |
| `categorias:read` | Listar categorias |
| `categorias:write` | Criar/editar categorias |
| `subcategorias:read` | Listar subcategorias |
| `subcategorias:write` | Criar/editar subcategorias |
| `*:read` | Leitura em todos os recursos |
| `*:write` | Escrita em todos os recursos |

### Rate Limiting

- **Default:** 1000 requests por hora
- **HTTP 429:** Retornado quando excedido
- **Reset:** Automático após 1 hora

### IP Whitelist

Opcionalmente, você pode restringir a API key a IPs específicos:

```sql
UPDATE api_keys 
SET ip_whitelist = '["192.168.1.100", "203.0.113.50"]'
WHERE id = 1;
```

---

## 📝 Exemplos de Código

### JavaScript (Fetch API)

```javascript
// Criar colaborador
async function criarColaborador(dados) {
    const response = await fetch('https://sisloc.seudominio.com/api/v1/colaboradores', {
        method: 'POST',
        headers: {
            'X-API-Key': 'sisloc_dev_key_2026',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            nome: dados.nome,
            email: dados.email,
            telefone: dados.telefone, // usar 'telefone' (não 'whatsapp')
            origem: dados.origem || 'API Externa',
            tipo: dados.tipo || 'FUNCIONARIO',
            estado_para_trabalho: dados.estado_para_trabalho,
            atua_como: dados.atua_como,
            cep: dados.cep,
            endereco: dados.endereco,
            bairro: dados.bairro,
            cidade: dados.cidade,
            estado: dados.estado,
            tipo_chave_pix: dados.tipo_chave_pix,
            chavepix: dados.chavepix,
            observacao: dados.observacao
        })
    });
    
    return await response.json();
}

// Usar
criarColaborador({
    nome: 'Maria Santos',
    email: 'maria@email.com',
    telefone: '11988888888',
    atua_como: 'DJ',
    estado: 'SP',
    cidade: 'São Paulo',
    bairro: 'Centro',
    endereco: 'Rua Exemplo'
}).then(console.log);
```

### PHP (cURL)

```php
<?php
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://sisloc.seudominio.com/api/v1/fornecedores',
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-Key: sisloc_dev_key_2026',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'nome_fantasia' => 'Tech Som Pro',
        'cpf_cnpj' => '12345678000195', // usar cpf_cnpj (não 'cnpj')
        'email' => 'contato@techsom.com',
        'telefone' => '1133334444',
        'id_categoria' => 1,
        'id_subcategoria' => 2
    ]),
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

### Python (Requests)

```python
import requests
import json

url = "https://sisloc.seudominio.com/api/v1/colaboradores"
headers = {
    "X-API-Key": "sisloc_dev_key_2026",
    "Content-Type": "application/json"
}
data = {
    "nome": "Carlos Oliveira",
    "email": "carlos@email.com",
    "telefone": "11977777777",  # usar 'telefone' (não 'whatsapp')
    "atua_como": "Iluminador",
    "tipo": "FREELANCE",
    "estado": "SP",
    "cidade": "São Paulo"
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

---

## ⚠️ Códigos de Erro

| HTTP Status | Significado |
|-------------|-------------|
| `200` | Sucesso |
| `201` | Criado com sucesso |
| `400` | Requisição inválida (validação falhou) |
| `401` | API Key não fornecida ou inválida |
| `403` | Permissão negada |
| `429` | Rate limit excedido |
| `500` | Erro interno do servidor |

### Exemplo de Erro

```json
{
  "success": false,
  "message": "Campo nome é obrigatório"
}
```

---

## 📊 Monitoramento

Todas as requisições são logadas na tabela `api_request_logs`:

```sql
SELECT 
    ak.name as api_key_name,
    arl.endpoint,
    arl.method,
    arl.ip_address,
    arl.created_at
FROM api_request_logs arl
JOIN api_keys ak ON arl.api_key_id = ak.id
ORDER BY arl.created_at DESC
LIMIT 100;
```

---

## 🚀 Deploy em Produção

### 1. Gerar nova API Key segura

```php
<?php
// Gerar chave aleatória
$secretKey = bin2hex(random_bytes(32));
echo "Sua API Key: " . $secretKey . "\n";
echo "Hash para banco: " . hash('sha256', $secretKey) . "\n";
```

### 2. Inserir no banco

```sql
INSERT INTO api_keys (name, api_key, api_key_public, permissions, rate_limit, status) VALUES
('Site Produção', 
 SHA2('SUA_CHAVE_AQUI', 256), 
 'sk_live_001', 
 '["colaboradores:write", "fornecedores:write"]',
 5000, 
 1);
```

### 3. Configurar IP Whitelist (opcional)

```sql
UPDATE api_keys 
SET ip_whitelist = '["IP_DO_SEU_SERVIDOR"]'
WHERE api_key_public = 'sk_live_001';
```

### 4. Desativar chave de desenvolvimento

```sql
UPDATE api_keys SET status = 0 WHERE api_key_public = 'sk_dev_001';
```

---

## 📦 Migrações Necessárias

Execute estas migrations antes de usar a API:

```bash
mysql -u user -p database < database/migrations/024_create_api_keys.sql
mysql -u user -p database < database/migrations/025_create_api_request_logs.sql
```

---

## 🔧 Troubleshooting

### "API Key não fornecida"

Verifique se o header `X-API-Key` está sendo enviado:

```javascript
fetch('/api/v1/colaboradores', {
    headers: {
        'X-API-Key': 'sisloc_dev_key_2026'  // ← Certifique-se de incluir
    }
});
```

### "Permissão negada"

Verifique as permissões da API key:

```sql
SELECT permissions FROM api_keys WHERE api_key_public = 'sk_dev_001';
```

### "Rate limit excedido"

Aguarde 1 hora ou aumente o limite:

```sql
UPDATE api_keys SET rate_limit = 5000 WHERE id = 1;
```

---

**Versão:** 1.0.0  
**Última atualização:** 2026-04-27  
**Suporte:** admin@sisloc.com

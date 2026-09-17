# Debug: Mensagens Não Aparecem

## ✅ Dados Existem no Backend

Teste confirmou que:
- ✅ Cotação ID 8 existe para SONORIZACAO
- ✅ 2 mensagens existem (1 enviada, 1 recebida)
- ✅ Serviço retorna dados corretamente
- ✅ JavaScript foi corrigido para usar `cotacoes` quando não há propostas

## 🔍 Possíveis Causas

### 1. Cache do Navegador
**Problema:** Browser está usando versão antiga do JavaScript (sem correções)

**Solução:**
```
1. Pressione Ctrl+Shift+Delete
2. Selecione "Cached images and files"
3. Clique em "Clear data"
4. OU pressione Ctrl+F5 para recarregar sem cache
```

### 2. JavaScript Não Carregou
**Problema:** Arquivo cotacao.js não foi incluído ou falhou ao carregar

**Solução:**
```
1. Abra DevTools (F12)
2. Vá para aba "Console"
3. Procure por logs começando com [COTACAO]
4. Se não houver logs, o JavaScript não carregou
```

### 3. Erro no JavaScript
**Problema:** Algum erro impede a execução

**Solução:**
```
1. Abra DevTools (F12)
2. Vá para aba "Console"
3. Procure erros em vermelho
4. Veja a mensagem de erro e linha
```

### 4. Endpoint Falhou
**Problema:** Fetch para /cotacao/fornecedores-by-item falhou

**Solução:**
```
1. Abra DevTools (F12)
2. Vá para aba "Network"
3. Recarregue a página
4. Procure por "fornecedores-by-item"
5. Clique na requisição
6. Veja:
   - Status: deve ser 200
   - Response: deve ter dados
```

## 🛠️ Passo a Passo de Debug

### Passo 1: Verificar Console

1. Abra a página: `http://localhost/sisloc/public/eventos/cotacao/1/2`
2. Pressione F12
3. Vá para aba "Console"
4. Procure estas mensagens:

```
[COTACAO] Script loaded, waiting for DOM...
[COTACAO] BASE_URL: http://localhost/sisloc/public
[COTACAO] COTACAO_DATA: {idEvento: 1, idProdutoEvento: 2, ...}
[COTACAO] DOMContentLoaded fired
[COTACAO] emails-container found, calling carregarFornecedoresComEmails()
[COTACAO] Carregando fornecedores com emails...
[COTACAO] Response received 200
[COTACAO] Data received: {success: true, data: [...]}
[COTACAO] Rendering 1 fornecedor(es)
```

**Se você vê estas mensagens:**
- ✅ JavaScript carregou corretamente
- ✅ Deve estar mostrando as mensagens

**Se NÃO vê estas mensagens:**
- ❌ JavaScript não está carregando
- Verifique os próximos passos

### Passo 2: Verificar se Script Foi Carregado

No DevTools (F12):
1. Vá para aba "Sources" ou "Debugger"
2. No lado esquerdo, expanda `localhost`
3. Navegue para `sisloc/public/js/`
4. Clique em `cotacao.js`
5. Verifique se o arquivo abre

**Se o arquivo NÃO aparece:**
- View não está incluindo o script
- Verifique `views/evento/cotacao.php`

**Se o arquivo aparece:**
- Verifique se há erros de sintaxe no console

### Passo 3: Verificar Network

No DevTools (F12):
1. Vá para aba "Network"
2. Recarregue a página (Ctrl+R)
3. Procure por `fornecedores-by-item`

**Deve ver:**
```
Request URL: http://localhost/sisloc/public/cotacao/fornecedores-by-item/2
Request Method: GET
Status Code: 200 OK
```

**Clique na requisição e veja:**
- Aba "Headers": Veja se não há erro de autenticação
- Aba "Response": Deve conter:
```json
{
  "success": true,
  "data": [
    {
      "id_fornecedor": 1,
      "nome_fantasia": "AURATEC INDUSTRIAL LTDA",
      "email": "hernestodf@gmail.com",
      "num_mensagens": 2,
      "id_cotacao": 8
    }
  ]
}
```

**Se status é 401 ou 403:**
- Problema de autenticação
- Verifique se está logado

**Se status é 500:**
- Erro no servidor
- Veja logs de erro do PHP

**Se status é 200 mas response está vazia:**
- Problema no banco de dados
- Execute o script de teste

### Passo 4: Testar Endpoint Manualmente

No terminal:
```bash
curl -b cookies.txt http://localhost/sisloc/public/cotacao/fornecedores-by-item/2
```

Ou via PHP:
```bash
php /var/www/html/sisloc/tests/debug_mensagens.php
```

### Passo 5: Verificar Banco de Dados

```bash
mysql -u admin -padmin sisloc -e "
SELECT m.id, m.id_cotacao, m.tipo, m.assunto
FROM item_cotacoes_mensagens m
WHERE m.id_cotacao = 8;
"
```

**Deve retornar:**
```
id  id_cotacao  tipo      assunto
13  8           enviado   [COT-SOLIC-EVT-1] ...
14  8           recebido  Re: [COT-SOLIC-EVT-1] ...
```

## 🔧 Correções Aplicadas

### 1. View (cotacao.php)
Adicionado `cotacoes` ao COTACAO_DATA:
```javascript
window.COTACAO_DATA = {
    propostas: [...],
    cotacoes: [...],  // ← ADICIONADO
    fornecedores: [...]
};
```

### 2. Controller (CotacaoViewController.php)
Adicionado `$cotacoes` aos dados da view:
```php
$cotacoes = $this->cotacaoService->getCotacoesAtivas($idProdutoEvento);
return $this->view('evento/cotacao', [
    ...
    'cotacoes' => $cotacoes,
    ...
]);
```

### 3. Service (CotacaoService.php)
Adicionado método `getCotacoesAtivas()`:
```php
public function getCotacoesAtivas(int $idProdutoEvento): array
{
    return $this->repo->findByProdutoEvento($idProdutoEvento);
}
```

Adicionado `id_cotacao` no retorno:
```php
foreach ($fornecedores as &$fornecedor) {
    $fornecedor['id_cotacao'] = $cotacaoMap[$fornecedor['id_fornecedor']] ?? null;
}
```

### 4. JavaScript (cotacao.js)
Corrigido `carregarThread()` para buscar em `cotacoes`:
```javascript
// Primeiro tenta nas propostas
for (var j = 0; j < propostas.length; j++) {
    if (propostas[j].id_fornecedor == fornecedorId) {
        cotacaoId = propostas[j].id;
        break;
    }
}

// Se nao encontrou, busca nas cotacoes
if (!cotacaoId) {
    for (var k = 0; k < cotacoes.length; k++) {
        if (cotacoes[k].id_fornecedor == fornecedorId) {
            cotacaoId = cotacoes[k].id;
            break;
        }
    }
}
```

Adicionados logs de debug:
```javascript
console.log('[COTACAO] Script loaded, waiting for DOM...');
console.log('[COTACAO] BASE_URL:', BASE_URL);
console.log('[COTACAO] COTACAO_DATA:', data);
```

## ✅ Resultado Esperado

Após recarregar a página (Ctrl+F5), você deve ver:

```
┌─────────────────────────────────────────────┐
│ Cotacao - SONORIZACAO PARA 100 PESSOAS      │
│ Proclamação da republica | HALL DE ENTRADA  │
├─────────────────────────────────────────────┤
│ ... (outras seções)                         │
├─────────────────────────────────────────────┤
│ Propostas Recebidas                    1    │
│ AURATEC INDUSTRIAL LTDA                     │
│ Status: Aguardando                          │
│ R$ 0,00                          26/04 16:56│
├─────────────────────────────────────────────┤
│ Emails / Conversas por Fornecedor           │
│ ▶ AURATEC INDUSTRIAL LTDA          (2)     │ ← DEVE APARECER!
│   hernestodf@gmail.com                      │
└─────────────────────────────────────────────┘
```

**Ao clicar no fornecedor:**
```
▼ AURATEC INDUSTRIAL LTDA                    (2)
  hernestodf@gmail.com
┌──────────────────────────────────────────┐
│ [COT-SOLIC-EVT-1] Solicitação de...     │
│ teste                                    │
│                             26/04 16:56  │
└──────────────────────────────────────────┘
┌──────────────────────────────────────────┐
│ Re: [COT-SOLIC-EVT-1] Solicitação de... │
│ tenho sim vou te mandar ai...           │
│ 📎 locacao_Eventos de teste.pdf         │
│                             26/04 16:57  │
└──────────────────────────────────────────┘

[📧 Enviar Mensagem]
```

## 📝 Resumo

**Para ver suas mensagens:**

1. ✅ Limpe cache do navegador (Ctrl+Shift+Delete)
2. ✅ Recarregue a página (Ctrl+F5)
3. ✅ Abra console (F12) e procure logs `[COTACAO]`
4. ✅ Verifique aba Network para ver requisições
5. ✅ Clique no fornecedor para expandir accordion
6. ✅ Veja as mensagens enviadas e recebidas

**Se ainda não funciona:**
- Envie screenshot do Console (F12)
- Envie screenshot da aba Network
- Execute: `php /var/www/html/sisloc/tests/debug_mensagens.php`
- Envie o output do script

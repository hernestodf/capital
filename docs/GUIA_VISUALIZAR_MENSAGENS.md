# Guia: Como Ver as Mensagens de Email na Cotação

## Fluxo Completo do Email

```
1. Usuário envia solicitação de cotação
   ↓
2. Sistema envia email para fornecedor (PHPMailer)
   ↓
3. Fornecedor responde o email
   ↓
4. Cron do IMAP processa emails a cada 5 minutos
   curl http://localhost/sisloc/public/cron/imap-cotacao?key=CHANGE_ME_SECRET
   ↓
5. Email é salvo no banco (item_cotacoes_mensagens)
   ↓
6. Usuário recarrega a página de cotação
   ↓
7. JavaScript carrega e exibe as mensagens
```

## Visualizando as Mensagens

### Passo 1: Acessar a Página de Cotação

**URL:** `http://localhost/sisloc/public/eventos/cotacao/1/2`

Substitua os números:
- `1` = ID do evento
- `2` = ID do produto (SONORIZACAO, AGUA, etc.)

### Passo 2: Ver a Seção "Emails / Conversas por Fornecedor"

Na parte inferior da página, você verá:

```
┌─────────────────────────────────────────────┐
│ Emails / Conversas por Fornecedor           │
├─────────────────────────────────────────────┤
│ ▶ AURATEC INDUSTRIAL LTDA          (2)     │
│   hernestodf@gmail.com                      │
└─────────────────────────────────────────────┘
```

### Passo 3: Clicar no Fornecedor para Expandir

Clique no nome do fornecedor para abrir o accordion:

```
┌─────────────────────────────────────────────┐
│ ▼ AURATEC INDUSTRIAL LTDA          (2)     │
│   hernestodf@gmail.com                      │
├─────────────────────────────────────────────┤
│  ┌──────────────────────────────────────┐  │
│  │ [COT-SOLIC-EVT-1] Solicitação de... │  │
│  │ teste                                │  │
│  │                           26/04 16:56 │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │ Re: [COT-SOLIC-EVT-1] Solicitação.. │  │
│  │ tenho sim vou te mandar ai...       │  │
│  │ 📎 locacao_Eventos de teste.pdf     │  │
│  │                           26/04 16:57 │  │
│  └──────────────────────────────────────┘  │
│                                             │
│  [📧 Enviar Mensagem]                       │
└─────────────────────────────────────────────┘
```

### Passo 4: Entender as Mensagens

**Mensagens Enviadas (tipo = 'enviado'):**
- Fundo: `var(--bg-elevated)` (cinza)
- Alinhamento: Direita
- Borda: Cinza
- Exemplo: Solicitação de cotação que você enviou

**Mensagens Recebidas (tipo = 'recebido'):**
- Fundo: `var(--cyan-alpha)` (azul claro transparente)
- Alinhamento: Esquerda
- Borda: Ciano
- Exemplo: Resposta do fornecedor

## Componentes Envolvidos

### 1. View (PHP)
**Arquivo:** `views/evento/cotacao.php`

**Linha 148-152:** Container inicial com "Carregando..."
```php
<div class="card-body" id="emails-container">
  <div style="text-align:center;padding:24px;color:var(--text-3)">
    <div style="font-size:14px">Carregando...</div>
  </div>
</div>
```

**Linha 190-203:** Dados para JavaScript
```javascript
window.COTACAO_DATA = {
    idEvento: 1,
    idProdutoEvento: 2,
    csrfToken: '...',
    propostas: [...],
    cotacoes: [
        {
            "id": 8,
            "id_fornecedor": 1,
            "fornecedor_nome": "AURATEC INDUSTRIAL LTDA",
            ...
        }
    ],
    fornecedores: [...]
};
```

### 2. JavaScript
**Arquivo:** `public/js/cotacao.js`

**Função principal:**
```javascript
// Linha 114-135: Carrega lista de fornecedores com emails
function carregarFornecedoresComEmails() {
    fetch(BASE_URL + '/cotacao/fornecedores-by-item/' + data.idProdutoEvento)
    .then(r => r.json())
    .then(d => {
        if (d.success && d.data && d.data.length > 0) {
            renderFornecedoresEmails(d.data);  // Renderiza accordion
        }
    });
}

// Linha 137-169: Renderiza o HTML dos fornecedores
function renderFornecedoresEmails(fornecedores) {
    // Cria accordion para cada fornecedor
    // Ao clicar, chama toggleAccordion()
}

// Linha 173-186: Abre/fecha accordion
window.toggleAccordion = function(id) {
    var content = document.getElementById(id);
    if (content.style.display === 'none') {
        content.style.display = 'block';
        carregarThread(fornecedorId);  // Carrega mensagens
    }
}

// Linha 190-224: Busca mensagens do fornecedor
function carregarThread(fornecedorId) {
    // Encontra cotacaoId nas cotacoes
    fetch(BASE_URL + '/cotacao/thread/' + cotacaoId + '/' + fornecedorId)
    .then(r => r.json())
    .then(d => {
        renderThread(d.data, fornecedorId);  // Renderiza mensagens
    });
}

// Linha 226-255: Renderiza mensagens estilo chat
function renderThread(mensagens, fornecedorId) {
    mensagens.forEach(msg => {
        // Enviado → alinhado à direita
        // Recebido → alinhado à esquerda com fundo azul
        // Mostra anexos se houver
    });
}
```

### 3. Controller (PHP)
**Arquivo:** `src/Controllers/CotacaoController.php`

**Endpoints:**

1. **GET `/cotacao/fornecedores-by-item/{idProdutoEvento}`**
   - Linha 244-256
   - Retorna lista de fornecedores com mensagens
   - Exemplo resposta:
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

2. **GET `/cotacao/thread/{idCotacao}/{idFornecedor}`**
   - Linha 208-220
   - Retorna todas as mensagens da thread
   - Exemplo resposta:
   ```json
   {
     "success": true,
     "data": [
       {
         "id": 13,
         "tipo": "enviado",
         "assunto": "[COT-SOLIC-EVT-1] Solicitação...",
         "corpo": "teste",
         "created_at": "2026-04-26 16:56:13",
         "anexos": []
       },
       {
         "id": 14,
         "tipo": "recebido",
         "assunto": "Re: [COT-SOLIC-EVT-1] Solicitação...",
         "corpo": "tenho sim vou te mandar ai...",
         "created_at": "2026-04-26 16:57:58",
         "anexos": [
           {
             "nome_arquivo": "locacao_Eventos.pdf",
             "path": "/var/www/html/sisloc/storage/uploads/cotacoes/...",
             "mime_type": "application/pdf"
           }
         ]
       }
     ]
   }
   ```

### 4. Service (PHP)
**Arquivo:** `src/Service/CotacaoService.php`

**Método:** `getFornecedoresByProdutoEvento()`
- Linha 267-289
- Busca cotações do produto
- Busca fornecedores com mensagens
- Adiciona `id_cotacao` para cada fornecedor

### 5. Repository (PHP)
**Arquivo:** `src/Repository/CotacaoMensagemRepository.php`

**Métodos:**

1. `findFornecedoresByCotacao($idCotacao)`
   - Linha 109-122
   - Retorna fornecedores que tem mensagens para uma cotação
   - Conta número de mensagens

2. `findByCotacaoFornecedor($idCotacao, $idFornecedor)`
   - Linha 13-62
   - Retorna todas as mensagens da thread
   - Inclui anexos se houver

## Debug: Se as Mensagens Não Aparecem

### Verificação 1: Banco de Dados
```bash
mysql -u admin -padmin sisloc -e "
SELECT m.id, m.id_cotacao, m.tipo, m.assunto, m.remetente, m.created_at
FROM item_cotacoes_mensagens m
WHERE m.id_cotacao = 8;
"
```

### Verificação 2: Endpoint API
```bash
# Testar endpoint de fornecedores
curl -b cookies.txt http://localhost/sisloc/public/cotacao/fornecedores-by-item/2

# Testar endpoint de thread
curl -b cookies.txt http://localhost/sisloc/public/cotacao/thread/8/1
```

### Verificação 3: Console do Navegador
1. Abrir DevTools (F12)
2. Ir para aba "Console"
3. Ver erros JavaScript
4. Ir para aba "Network"
5. Ver requisições XHR
6. Ver resposta dos endpoints

### Verificação 4: Cron IMAP
```bash
# Rodar cron manualmente
curl "http://localhost/sisloc/public/cron/imap-cotacao?key=CHANGE_ME_SECRET"

# Deve retornar:
{"success":true,"processados":1,"erros":0,"total":1}
```

## Exemplo Visual Completo

### Página de Cotação - SONORIZACAO

```
┌────────────────────────────────────────────────────────┐
│  Cotacao - SONORIZACAO PARA 100 PESSOAS                │
│  Proclamação da republica | HALL DE ENTRADA            │
├────────────────────────────────────────────────────────┤
│  [Informações do Produto]                              │
│  Quantidade: 2.00                                      │
│  Valor Unitário: R$ 50,00                              │
│  Total: R$ 3.000,00                                    │
├────────────────────────────────────────────────────────┤
│  [Propostas]                                           │
│  Nenhum fornecedor enviou proposta ainda               │
├────────────────────────────────────────────────────────┤
│  Emails / Conversas por Fornecedor                     │
│                                                        │
│  ▼ AURATEC INDUSTRIAL LTDA                    (2)     │
│    hernestodf@gmail.com                                │
│  ┌──────────────────────────────────────────────────┐ │
│  │ ┌────────────────────────────────────────────┐   │ │
│  │ │ [COT-SOLIC-EVT-1] Solicitação de Proposta │   │ │
│  │ │ teste                                      │   │ │
│  │ │                              26/04 16:56   │   │ │
│  │ └────────────────────────────────────────────┘   │ │
│  │ ┌────────────────────────────────────────────┐   │ │
│  │ │ Re: [COT-SOLIC-EVT-1] Solicitação de...   │   │ │
│  │ │ tenho sim vou te mandar ai                 │   │ │
│  │ │ Em dom., 26 de abr. de 2026 às 16:56...   │   │ │
│  │ │ 📎 locacao_Eventos de teste3_2026-04-22... │   │ │
│  │ │                              26/04 16:57   │   │ │
│  │ └────────────────────────────────────────────┘   │ │
│  │                                                   │ │
│  │ [📧 Enviar Mensagem]                              │ │
│  └──────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────┘
```

## Resumo

**Para ver suas mensagens:**

1. ✅ Acesse a página de cotação: `/eventos/cotacao/{idEvento}/{idProdutoEvento}`
2. ✅ Aguarde o JavaScript carregar (página mostra "Carregando..." → lista fornecedores)
3. ✅ Clique no fornecedor para expandir o accordion
4. ✅ Veja todas as mensagens (enviadas e recebidas)
5. ✅ Clique em "Enviar Mensagem" para responder

**Se não aparecer:**
- Verifique o console do navegador (F12)
- Verifique se há mensagens no banco
- Verifique se o cron do IMAP rodou
- Recarregue a página (Ctrl+F5)

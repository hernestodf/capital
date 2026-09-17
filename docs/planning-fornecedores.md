# PLANO COMPLETO — Módulo Fornecedores / Cotação de Itens

**Data:** 2026-04-25  
**Sistema:** SisLoc v2.5.0  
**Módulo:** Fornecedores / Cotação de Itens por Evento  
**Arquivo Principal:** `edit-fornecedores.php` (tab hpane-4 dentro de evento/edit.php)

---

## 1. ANÁLISE DO BANCO DE DADOS ATUAL

### Tabela `produtos_evento` (EXISTENTE)

| Campo | Tipo | Atual | Novo |
|-------|------|-------|------|
| `id` | INT | PK | — |
| `id_evento` | INT | FK eventos | — |
| `id_sala` | INT | FK salas | — |
| `id_categoria` | INT | FK categorias_sala | — |
| `id_planilha` | INT | FK planilhas | — |
| `produto` | VARCHAR(255) | Nome | — |
| `observacao_montagem` | TEXT | — | — |
| `qtd` | DECIMAL(10,2) | Quantidade | — |
| `valor_unit` | DECIMAL(10,2) | Preço venda | — |
| `dias` | INT | Dias locação | — |
| `total_item` | DECIMAL(10,2) | qtd × valor × dias | — |
| `custo_unit` | DECIMAL(10,2) | **JÁ EXISTE (default 0.00)** | Preencher com vencedor |
| `status` | TINYINT(1) | Ativo | — |

### Tabela `fornecedores` (EXISTENTE)

| Campo | Tipo | Uso |
|-------|------|-----|
| `id` | INT | PK |
| `id_categoria` | INT | FK categorias |
| `id_subcategoria` | INT | FK subcategorias |
| `cpf_cnpj` | VARCHAR(20) | CNPJ/CPF |
| `nome_fantasia` | VARCHAR(255) | Nome exibição |
| `razao_social` | VARCHAR(255) | Razão social |
| `telefone` | VARCHAR(20) | WhatsApp |
| `email` | VARCHAR(255) | Email fornecedor |
| `status` | TINYINT(1) | Ativo/Inativo |

### Tabela `eventos` (EXISTENTE)

- `id_produtor` → FK para `produtores` → `produtores.email` (CCO automático)

### Tabela `produtores` (EXISTENTE)

| Campo | Tipo | Uso |
|-------|------|-----|
| `id` | INT | PK |
| `id_users` | INT | FK users |
| `nome` | VARCHAR(255) | Nome |
| `email` | VARCHAR(255) | Email para CCO |
| `telefone` | VARCHAR(20) | Telefone |

### IMPACTO: NÃO QUEBRA NADA

- `custo_unit` já existe em `produtos_evento` — apenas vamos preenchê-lo
- Nenhuma coluna será removida ou renomeada
- Novas tabelas são isoladas (sem FK que quebre fluxos existentes)

---

## 2. NOVAS TABELAS

### `item_cotacoes` — Propostas de fornecedores por item

```sql
CREATE TABLE item_cotacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produto_evento INT NOT NULL,        -- FK → produtos_evento.id
    id_fornecedor INT NOT NULL,            -- FK → fornecedores.id
    id_evento INT NOT NULL,                -- FK → eventos.id
    valor_proposto DECIMAL(10,2) NOT NULL, -- Valor da proposta
    vencedor ENUM('S','N') DEFAULT 'N',    -- Se é a vencedora
    status ENUM('aguardando','com_proposta','vencedor_definido') DEFAULT 'aguardando',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_produto_fornecedor (id_produto_evento, id_fornecedor),
    INDEX idx_produto (id_produto_evento),
    INDEX idx_evento (id_evento),
    INDEX idx_fornecedor (id_fornecedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `item_cotacoes_parcelas` — Parcelas de pagamento

```sql
CREATE TABLE item_cotacoes_parcelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cotacao INT NOT NULL,               -- FK → item_cotacoes.id (vencedora)
    numero_parcela TINYINT NOT NULL,       -- 1, 2, 3...
    descricao VARCHAR(100),                -- "Entrada", "Parcela 1", etc.
    valor DECIMAL(10,2) NOT NULL,          -- Valor da parcela
    data_vencimento DATE NOT NULL,         -- Vencimento
    enviado_pagamento ENUM('S','N') DEFAULT 'N', -- Marcado para pagamento
    pago ENUM('S','N') DEFAULT 'N',        -- Efetivamente pago
    data_pagamento DATE NULL,              -- Data real do pagamento
    comprovante VARCHAR(255) NULL,         -- Path do comprovante
    whatsapp_enviado ENUM('S','N') DEFAULT 'N', -- Comprovante enviado via WhatsApp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cotacao (id_cotacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `item_cotacoes_mensagens` — Thread de emails

```sql
CREATE TABLE item_cotacoes_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cotacao INT NOT NULL,               -- FK → item_cotacoes.id
    id_fornecedor INT NOT NULL,            -- FK → fornecedores.id
    tipo ENUM('enviado','recebido') NOT NULL,
    remetente VARCHAR(255) NOT NULL,
    destinatario VARCHAR(255) NOT NULL,
    assunto VARCHAR(500),
    corpo LONGTEXT,
    message_id_email VARCHAR(500) NULL,    -- Message-ID do email (para threading)
    in_reply_to VARCHAR(500) NULL,         -- In-Reply-To (threading)
    lido ENUM('S','N') DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cotacao (id_cotacao),
    INDEX idx_fornecedor (id_fornecedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `item_cotacoes_anexos` — Anexos das mensagens

```sql
CREATE TABLE item_cotacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mensagem INT NOT NULL,              -- FK → item_cotacoes_mensagens.id
    nome_arquivo VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    tamanho INT,                           -- Bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mensagem (id_mensagem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 3. FLUXO COMPLETO DO MÓDULO (ATUALIZADO)

### 3.1 Estrutura da Aba Fornecedores (hpane-4) — AGRUPADA POR SALA

```
┌─────────────────────────────────────────────────────────────────────┐
│  ABA "FORNECEDORES" (hpane-4) dentro de evento/edit.php            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  LISTAGEM AGRUPADA POR SALA:                                        │
│                                                                     │
│  ┌─ Sala: Plenária ────────────────────────────────────────────┐   │
│  │  Item          │ Qtd │ Custo    │ Status  │ Ações            │   │
│  │  Água          │ 100 │ R$ 0,00  │ Sem cot.│ [📋 Ver]        │   │
│  │                │     │          │         │ [📧 Solicitar]   │   │
│  │  Credenciamento│  50 │ R$ 0,00  │ 1 prop. │ [📋 Ver]        │   │
│  │                │     │          │         │ [📧 Solicitar]   │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌─ Sala: Credenciamento ──────────────────────────────────────┐   │
│  │  Água          │ 200 │ R$ 150,00│ Vencedor│ [📋 Ver]        │   │
│  │                │     │          │         │ [📧 Solicitar]   │   │
│  │  Mesa          │  20 │ R$ 0,00  │ Sem cot.│ [📋 Ver]        │   │
│  │                │     │          │         │ [📧 Solicitar]   │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Modal Rápido: Solicitar Cotação (da listagem por sala)

Ao clicar **[📧 Solicitar]** na listagem → abre modal rápido:

```
┌──────────────────────────────────────────────────────────────┐
│  📧 Solicitar Proposta — Água (Plenária)              [✕]   │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Enviar para:                                                │
│  ☑ Silva Iluminação (silva@email.com)                       │
│  ☑ Santos Equipamentos (santos@email.com)                   │
│  ☐ Souza Locações (souza@email.com)                         │
│  ☐ [Buscar mais fornecedores...]                             │
│                                                              │
│  Mensagem (pré-preenchida com dados do item):               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Olá, gostaríamos de solicitar proposta para:         │   │
│  │                                                      │   │
│  │ Item: Água                                           │   │
│  │ Quantidade: 100                                      │   │
│  │ Evento: Show XYZ                                     │   │
│  │ Data: 01/05/2026 a 03/05/2026                       │   │
│  │ Local: Rua XYZ, São Paulo                            │   │
│  │                                                      │   │
│  │ [editável...]                                        │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  📎 Anexo: [Selecionar arquivo]                              │
│                                                              │
│  ℹ️ Cópia oculta (CCO) será enviada para: produtor@...      │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│  [Cancelar]     [📤 Enviar para 2 Fornecedores]              │
└──────────────────────────────────────────────────────────────┘
```

### 3.3 Tela Dedicada de Cotação (ao clicar "📋 Ver Propostas")

**NÃO É MODAL — é uma página dedicada:** `GET /eventos/cotacao/{id_evento}/{id_produto_evento}`

```
┌──────────────────────────────────────────────────────────────────────┐
│  ← Voltar para Fornecedores  │  Água — Plenária                     │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ═══ SEÇÃO 1: SOLICITAR COTAÇÃO ═══                                 │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  📧 Enviar Solicitação de Proposta:                          │   │
│  │                                                              │   │
│  │  Para: [Select múltiplo de fornecedores ▼]                  │   │
│  │  ☑ Silva Iluminação (silva@email.com)                       │   │
│  │  ☑ Santos Equip. (santos@email.com)                         │   │
│  │  ☐ Souza Locações (souza@email.com)                         │   │
│  │                                                              │   │
│  │  Mensagem:                                                   │   │
│  │  ┌──────────────────────────────────────────────────────┐   │   │
│  │  │ Olá, gostaríamos de solicitar proposta para:         │   │   │
│  │  │ Item: Água | Qtd: 100 | Evento: Show XYZ            │   │   │
│  │  │ [editável...]                                        │   │   │
│  │  └──────────────────────────────────────────────────────┘   │   │
│  │  📎 [Anexar]          [📤 Enviar Solicitação]               │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ═══ SEÇÃO 2: DEFINIR VENCEDOR ═══                                  │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  Fornecedor:                                                 │   │
│  │  ┌───────────────────────────────┐  ┌──────────────────┐    │   │
│  │  │ Select: Fornecedor ▼          │  │ Custo: R$ _____  │    │   │
│  │  │  - Silva Iluminação           │  └──────────────────┘    │   │
│  │  │  - Santos Equipamentos        │                          │   │
│  │  │  - Souza Locações             │  [⭐ Marcar como Vencedor]│   │
│  │  └───────────────────────────────┘                          │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  Ao marcar vencedor:                                                 │
│  → Atualiza produtos_evento.custo_unit = valor do campo custo       │
│  → Salva fornecedor_vencedor (nome) no item                         │
│  → Toast verde: "Vencedor definido! Custo atualizado."              │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ═══ SEÇÃO 3: EMAILS/TROCA DE MENSAGENS (por fornecedor) ═══       │
│                                                                      │
│  ┌─ ▶ Silva Iluminação (silva@email.com) — 5 emails ──────────┐   │
│  │  ┌──────────────────────────────────────────────────────┐   │   │
│  │  │ 📧 Enviado — 25/04/2026 14:30                        │   │   │
│  │  │ Para: silva@email.com | CCO: produtor@empresa.com    │   │   │
│  │  │ Assunto: [COT-42-EVT-15] Solicitação - Água          │   │   │
│  │  │ ───────────────────────────────────────────────────  │   │   │
│  │  │ Olá, segue solicitação de proposta...                │   │   │
│  │  │ 📎 solicitacao.pdf                                   │   │   │
│  │  └──────────────────────────────────────────────────────┘   │   │
│  │  ┌──────────────────────────────────────────────────────┐   │   │
│  │  │ 📬 Recebido — 25/04/2026 15:00                       │   │   │
│  │  │ De: silva@email.com                                  │   │   │
│  │  │ Segue nossa proposta de R$ 200,00...                 │   │   │
│  │  │ 📎 proposta.pdf                                      │   │   │
│  │  └──────────────────────────────────────────────────────┘   │   │
│  │  ┌──────────────────────────────────────────────────────┐   │   │
│  │  │ 💬 Responder para Silva Iluminação:                   │   │   │
│  │  │ ┌────────────────────────────────────────────────┐   │   │   │
│  │  │ │ Digite sua mensagem...                          │   │   │   │
│  │  │ └────────────────────────────────────────────────┘   │   │   │
│  │  │ [📎 Anexar]              [💬 Responder]               │   │   │
│  │  │                      [📄 Solicitar Nova Proposta]     │   │   │
│  │  └──────────────────────────────────────────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌─ ▶ Santos Equipamentos (santos@email.com) — 2 emails ──────┐   │
│  │  (mesmo padrão — accordion fechado)                         │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 4. QUANDO MARCAR VENCEDOR (TELA DEDICADA)

```
TELA: /eventos/cotacao/{id_evento}/{id_produto_evento}

Fluxo:
1. Admin seleciona fornecedor no select
2. Digita o valor no campo "Custo: R$ ___"
3. Clica "⭐ Marcar como Vencedor"
4. Backend:
   a. Desmarca vencedor anterior (UPDATE SET vencedor='N')
   b. Marca nova cotação como vencedora (UPDATE SET vencedor='S')
   c. Atualiza produtos_evento.custo_unit = valor do campo custo
   d. Salva nome do fornecedor vencedor no item (fornecedor_vencedor)
   e. Atualiza item_cotacoes.status = 'vencedor_definido'
5. Toast verde: "Vencedor definido! Custo do item atualizado."
6. Badge na listagem principal muda para "Vencedor" (sem reload)
```

---

## 5. FLUXO DE PAGAMENTO COM PARCELAS

```
1. Admin define parcelas (ex: entrada R$1500 + 3x R$416,66)
   → Salva em item_cotacoes_parcelas

2. Por parcela: clica "Enviar para Pagamento"
   → enviado_pagamento = 'S'
   → Habilita botão "Marcar como Pago"

3. Admin clica "Marcar como Pago" + anexa comprovante
   → pago = 'S', data_pagamento = hoje, comprovante = path
   → Dispara WhatsApp para fornecedor (telefone do cadastro)
   → Mensagem: "Pagamento da parcela X confirmado. Segue comprovante."

4. Após pago: botão "Reenviar Comprovante via WhatsApp" aparece
```

---

## 6. FLUXO DE EMAILS (SMTP + IMAP)

### 6.1 Templates de Email

#### Template: Solicitação de Proposta (envio em massa)

```
Assunto: [COT-SOLIC-EVT-{id_evento}] Solicitação de Proposta - {nome_item}

Corpo:
══════════════════════════════════════════════════════
Olá, {nome_fornecedor}!

Gostaríamos de solicitar uma proposta de preço para:

  Item: {nome_item}
  Quantidade: {qtd}
  Evento: {nome_evento}
  Período: {data_inicio} a {data_fim}
  Local: {local_evento}

Por favor, envie sua proposta respondendo este email.

Atenciosamente,
{nome_empresa}
══════════════════════════════════════════════════════

CCO: {produtores.email} (sempre)
Anexos: opcional
```

#### Template: Resposta do Admin para fornecedor específico

```
Assunto: Re: [COT-{id_cotacao}-EVT-{id_evento}] {assunto_anterior}

Corpo:
══════════════════════════════════════════════════════
{corpo_digitado_pelo_admin}
══════════════════════════════════════════════════════

CCO: {produtores.email} (sempre)
Anexos: opcional
```

#### Template: Solicitar Nova Proposta (para fornecedor específico)

```
Assunto: [COT-RENOV-EVT-{id_evento}] Nova Solicitação - {nome_item}

Corpo:
══════════════════════════════════════════════════════
Olá, {nome_fornecedor}!

Gostaríamos de solicitar uma nova proposta para:

  Item: {nome_item}
  Quantidade: {qtd}
  Evento: {nome_evento}
  Período: {data_inicio} a {data_fim}
  Local: {local_evento}

Por favor, envie sua proposta atualizada respondendo este email.

Atenciosamente,
{nome_empresa}
══════════════════════════════════════════════════════

CCO: {produtores.email} (sempre)
```

### 6.2 Configurações de Email

**Servidor:**
- SMTP: `mail.sisloc.online:465` (SSL)
- IMAP: `mail.sisloc.online:993` (SSL)
- Usuário: `profox@sisloc.online`

### Envio (PHPMailer)

```
1. Admin escreve mensagem + anexa arquivo
2. POST /cotacao/enviar-mensagem/{id_cotacao}
3. PHPMailer envia para email do fornecedor
   → CCO: email do produtor (via eventos.id_produtor → produtores.email)
   → Assunto: [COT-{id_cotacao}-EVT-{id_evento}] - Referência do item
4. Salva em item_cotacoes_mensagens (tipo='enviado')
5. Salva anexos em /uploads/cotacoes/{id_mensagem}/
```

### Recebimento (CRON IMAP)

```
1. CRON roda a cada 5 min: GET /cron/imap-cotacao?key=SECRET
2. Conecta IMAP: mail.sisloc.online:993 (SSL) profox@sisloc.online
3. Busca emails não lidos
4. Extrai token do assunto: [COT-123-EVT-5]
5. Identifica id_cotacao = 123
6. Salva mensagem em item_cotacoes_mensagens (tipo='recebido')
7. Salva anexos
8. Marca como lido no servidor IMAP
9. Encaminha cópia para email do produtor
```

---

## 7. ROTAS

### Tela Principal (dentro de eventos, com view)

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/eventos/cotacao/{id_evento}/{id_produto_evento}` | Tela dedicada de cotação de um item |

### Autenticadas (AuthMiddleware) — AJAX

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/cotacao/propostas/{id_produto_evento}` | Listar propostas de um item (JSON) |
| `POST` | `/cotacao/marcar-vencedor/{id_cotacao}` | Marcar proposta vencedora (atualiza custo) |
| `POST` | `/cotacao/solicitar-cotacao` | Solicitar cotação em massa (modal rápido) |
| `GET` | `/cotacao/thread/{id_cotacao}/{id_fornecedor}` | Thread de emails com fornecedor (JSON) |
| `POST` | `/cotacao/enviar-mensagem/{id_cotacao}/{id_fornecedor}` | Enviar email + anexo (responder) |
| `POST` | `/cotacao/solicitar-proposta/{id_cotacao}/{id_fornecedor}` | Solicitar nova proposta por email |

### CRON

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/cron/imap-cotacao?key=SECRET` | Processar emails recebidos via IMAP |

---

## 8. ARQUIVOS A CRIAR/MODIFICAR

### Novos

| Arquivo | Descrição |
|---------|-----------|
| `database/migrations/017_create_cotacoes_tables.sql` | 4 tabelas novas |
| `src/Controllers/CotacaoController.php` | Rotas AJAX (marcar vencedor, emails) |
| `src/Controllers/CotacaoViewController.php` | View da tela dedicada de cotação |
| `src/Controllers/CronImapController.php` | CRON IMAP |
| `src/Service/CotacaoService.php` | Lógica de propostas, vencedor, emails |
| `src/Service/ImapService.php` | Conexão IMAP, busca emails, salva anexos |
| `src/Service/EmailService.php` | PHPMailer com CCO produtor |
| `src/Repository/CotacaoRepository.php` | Queries item_cotacoes |
| `src/Repository/CotacaoMensagemRepository.php` | Queries mensagens/anexos |
| `views/evento/cotacao.php` | Tela dedicada de cotação |

### Modificar

| Arquivo | Mudança |
|---------|---------|
| `views/evento/partials/edit-fornecedores.php` | Listagem agrupada por sala + botão "Ver" |
| `public/js/eventos/fornecedores.js` | JS: listagem por sala, abrir tela cotação |
| `public/index.php` | Adicionar rotas de cotação |
| `composer.json` | Adicionar phpmailer/phpmailer |
| `.env` | Adicionar config SMTP + IMAP |
| `src/Controllers/EventoController.php` | Passar itens agrupados por sala para partial |

---

## 9. ORDEM DE IMPLEMENTAÇÃO

1. **composer require phpmailer/phpmailer webklex/php-imap**
2. **Migration 017** — criar 4 tabelas (sem parcelas nesta fase)
3. **CotacaoRepository** — queries básicas
4. **CotacaoService** — lógica de propostas, vencedor, emails
5. **EmailService** — PHPMailer com CCO produtor
6. **ImapService** — webklex/php-imap, busca emails, salva anexos
7. **CotacaoController** — rotas AJAX (marcar vencedor, emails)
8. **CotacaoViewController** — view da tela dedicada
9. **edit-fornecedores.php** — partial com listagem agrupada por sala
10. **cotacao.php** — view da tela dedicada de cotação
11. **fornecedores.js** — JS com listagem por sala, abrir tela cotação
12. **cotacao.js** — JS da tela dedicada (emails, marcar vencedor)
13. **Rotas em index.php**
14. **Config .env** — SMTP + IMAP

---

## 10. CONFIGURAÇÕES .env (novas)

```env
# Email SMTP
SMTP_HOST=mail.sisloc.online
SMTP_PORT=465
SMTP_USER=profox@sisloc.online
SMTP_PASS=senha_da_conta
SMTP_SECURE=ssl

# IMAP
IMAP_HOST=mail.sisloc.online
IMAP_PORT=993
IMAP_USER=profox@sisloc.online
IMAP_PASS=senha_da_conta

# CRON
CRON_SECRET_KEY=sua_chave_secreta
```

---

## 11. REGRAS DE NEGÓCIO

| Regra | Implementação |
|-------|--------------|
| Listagem agrupada por sala | Items organizados: Sala → Items (usando `id_sala`) |
| Botão [📧 Solicitar] em cada item | Abre modal rápido com select múltiplo + template |
| Modal rápido: select múltiplo | Checkboxes para cada fornecedor ativo |
| Template pré-preenchido | Dados do item: nome, qtd, evento, datas, local |
| Mensagem editável | Admin pode editar o texto antes de enviar |
| CCO obrigatório | Todo email envia CCO para `produtores.email` via `eventos.id_produtor` |
| Solicitar cotação em massa | Envia 1 email individual por fornecedor (cada um como destinatário principal) |
| "Ver Propostas" abre tela dedicada | `window.location.href = BASE_URL + '/eventos/cotacao/' + eventoId + '/' + itemId` |
| Tela dedicada: 3 seções | 1. Solicitar cotação → 2. Definir vencedor → 3. Emails |
| Formulário vencedor | Select fornecedor + campo custo + botão "Marcar como Vencedor" |
| Custo atualiza produtos_evento | `UPDATE produtos_evento SET custo_unit = valor_campo WHERE id = id_item` |
| Nome fornecedor salva no item | `UPDATE produtos_evento SET fornecedor_vencedor = nome WHERE id = id_item` |
| Vencedor único por item | UPDATE desmarca anterior antes de marcar novo |
| Emails agrupados por fornecedor | Accordion: cada fornecedor com propostas = 1 seção |
| Thread por fornecedor | Mensagens filtradas por `id_cotacao + id_fornecedor` |
| Responder envia email + CCO produtor | PHPMailer: To=fornecedor, CCO=produtores.email |
| Solicitar Proposta envia template | Email pré-formatado com dados do item (qtd, evento, datas) |
| Todo email tem token no assunto | `[COT-{id}-EVT-{id_evento}]` ou `[COT-SOLIC-EVT-{id_evento}]` |
| Todo POST valida CSRF | `Csrf::validate()` em todas as rotas |
| Toasts para feedback | `showToast('green'|'red'|'yellow', title, msg)` |

---

## 12. DEPENDÊNCIAS EXTERNAS

| Dependência | Uso | Instalação |
|-------------|-----|------------|
| **PHPMailer** | Envio de emails SMTP | `composer require phpmailer/phpmailer` |
| **webklex/php-imap** | Recebimento de emails (PHP 8.4) | `composer require webklex/php-imap` |
| **WhatsApp Bot** | Envio comprovantes | Já configurado (201.23.68.17:3000) |

### NOTA: Por que webklex/php-imap?

No **PHP 8.4**, a extensão `imap` nativa foi **removida** (deprecated desde PHP 8.2). O pacote `webklex/php-imap`:
- É uma implementação puramente PHP (sem extensão C)
- Compatível com PHP 8.0 - 8.4+
- Suporta IMAP4, conexão SSL/TLS
- Mantido ativamente (última versão: 2024)
- API moderna com suporte a attachments, folders, etc.

**Exemplo de uso:**

```php
use Webklex\PHPIMAP\ClientManager;

$cm = new ClientManager([
    'default' => 'default',
    'accounts' => [
        'default' => [
            'host' => 'mail.sisloc.online',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => 'profox@sisloc.online',
            'password' => 'senha',
            'protocol' => 'imap',
        ]
    ]
]);

$client = $cm->account('default');
$client->connect();
$folders = $client->getFolders();
$messages = $client->getFolder('INBOX')->messages()->all()->get();
```

---

## 13. CHECKLIST ANTES DE PRODUÇÃO

- [ ] Migration 017 executada no banco
- [ ] PHPMailer instalado (`composer require phpmailer/phpmailer`)
- [ ] webklex/php-imap instalado (`composer require webklex/php-imap`)
- [ ] **NÃO** instalar extensão `php-imap` (não existe no PHP 8.4)
- [ ] Configurações SMTP/IMAP no .env
- [ ] CRON configurado para rodar a cada 5 min
- [ ] Pasta `public/uploads/cotacoes/` criada com permissões (755)
- [ ] Permissões RBAC adicionadas (`cotacao.*`)
- [ ] Teste de envio de email funcionando
- [ ] Teste de recebimento IMAP com webklex/php-imap
- [ ] Teste de listagem agrupada por sala funcionando
- [ ] Teste de marcar vencedor atualizando custo_unit
- [ ] Teste de thread de emails por fornecedor
- [ ] Teste de CCO para produtor
- [ ] Código morto analisado e removido
- [ ] Todos os toasts e feedbacks funcionando

---

**Última atualização:** 2026-04-26  
**Status:** Aguardando aprovação para implementação

---

## NOTAS DE REVISÃO (2026-04-26)

### Mudanças em relação ao planning original

| Aspecto | Original | Revisado |
|---------|----------|----------|
| **Navegação** | Modal na mesma página | Tela dedicada (`/eventos/cotacao/{id_evento}/{id_produto_evento}`) |
| **Agrupamento** | Tabela plana com todos itens | **Agrupada por sala** (Sala → Items) |
| **Formulário vencedor** | Dentro do modal, sem campo custo | **Select + campo custo** que atualiza `produtos_evento.custo_unit` |
| **Parcelas** | Incluídas no modal | **Removidas do escopo inicial** (tabela mantida para fase futura) |
| **Emails** | Accordion dentro do modal | **Accordion na tela dedicada**, 2 ações: Responder + Solicitar Proposta |
| **CCO Produtor** | Mencionado | **SEMPRE em cópia** em todos os emails |
| **Novo arquivo** | — | `views/evento/cotacao.php` (tela dedicada) |
| **Novo controller** | — | `CotacaoViewController.php` (renderiza tela dedicada) |

### Fluxo resumido correto

```
Evento Edit → Tab Fornecedores → Listagem por Sala
    │
    ├─ Sala: Plenária
    │   ├─ Água [📋 Ver] → /eventos/cotacao/15/42
    │   └─ Credenciamento [📋 Ver] → /eventos/cotacao/15/43
    │
    └─ Sala: Credenciamento
        ├─ Água [📋 Ver] → /eventos/cotacao/15/50
        └─ Mesa [📋 Ver] → /eventos/cotacao/15/51

Tela de Cotação (/eventos/cotacao/{evento}/{item}):
    1. Select Fornecedor + Campo Custo → Marcar Vencedor
    2. Emails agrupados por fornecedor (accordion)
       - Responder
       - Solicitar Proposta
       - Anexos
    3. CCO automático para produtor
```

### Nota técnica: PHP 8.4

| Item | Detalhe |
|------|---------|
| **Versão PHP** | 8.4.11 |
| **Extensão imap** | Removida no PHP 8.4 |
| **Solução** | `webklex/php-imap` (100% PHP, sem extensão C) |
| **NÃO executar** | `apt install php-imap` (não funciona) |

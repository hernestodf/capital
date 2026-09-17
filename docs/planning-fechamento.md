# Planejamento — Módulo Fechamento de Eventos

> Sistema: SISLOC v2.5.0
> Data: 2026-04-27
> Objetivo: Criar módulo de fechamento para documentar eventos, gerenciar pagamentos de colaboradores e fornecedores, registrar fotos e gerar relatórios PDF.

---

## 1. Contexto e Objetivo

O módulo de Fechamento é acessado dentro da tela de edição de evento (`views/evento/edit.php`) como uma nova aba horizontal (HPane 5). Ele serve para:

1. **Documentar o evento** para entrega ao cliente
2. **Listar colaboradores** que trabalharam no evento e enviar para pagamento
3. **Listar fornecedores** de propostas vencedoras e enviar para contas a pagar
4. **Registrar fotos** do evento por sala e fotos gerais
5. **Gerar relatórios PDF** — um para o cliente (sem valores) e um interno (com custos e lucro)

---

## 2. Análise do Banco de Dados

### 2.1 Tabelas Existentes Reaproveitadas

| Tabela | Colunas Uteis | Uso no Fechamento |
|--------|--------------|-------------------|
| `evento_colaboradores` | `id_evento`, `id_colaborador`, `funcao`, `valor_diaria`, `data_inicio`, `data_fim`, `enviar_pagamento` | Dados base dos colaboradores |
| `evento_colaborador_presencas` | `id_alocacao`, `data`, `status` (parcial/completo) | Confirmar presença |
| `item_cotacoes` | `id_evento`, `id_fornecedor`, `valor_proposto`, `vencedor='S'` | Fornecedores vencedores |
| `fornecedores` | `id`, `nome_fantasia`, `email`, `telefone`, `cpf_cnpj` | Dados do fornecedor |
| `contas_pagar` | `id`, `id_fornecedor`, `descricao`, `valor`, `status`, `data_vencimento` | Contas a pagar (precisa adaptação) |
| `eventos` | `id`, `nome_evento`, `observacoes_fechamento`, `status_locacao` | Dados do evento |
| `salas` | `id`, `id_evento`, `nome_sala` | Salas para fotos por sala |
| `produtos_evento` | `id_evento`, `id_sala`, `produto`, `qtd`, `valor_unit`, `total_item`, `fornecedor_vencedor` | Itens do evento para PDF |
| `colaboradores` | `id`, `nome`, `telefone`, `email`, `chavepix`, `tipo_chave_pix` | Dados do colaborador |

### 2.2 Tabelas Novas Necessárias

```sql
-- Fotos do evento
CREATE TABLE evento_fotos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evento_id INT NOT NULL,
  sala_id INT NULL,
  caminho_arquivo VARCHAR(500) NOT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
  FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE SET NULL,
  INDEX idx_evento_sala (evento_id, sala_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2.3 Adaptações em Tabelas Existentes

```sql
-- Adicionar colunas em contas_pagar para suportar eventos e múltiplos tipos
ALTER TABLE contas_pagar 
  ADD COLUMN evento_id INT NULL AFTER id,
  ADD COLUMN tipo ENUM('colaborador','fornecedor','outro') NOT NULL DEFAULT 'outro' AFTER evento_id,
  ADD COLUMN referencia_id INT NULL AFTER tipo,
  ADD CONSTRAINT fk_contas_pagar_evento 
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL;
```

**Por que essas colunas?**
- `evento_id` → vincula a conta ao evento para totalização
- `tipo` → distingue colaborador vs fornecedor vs outro
- `referencia_id` → ID da origem (`id_alocacao` ou `id_cotacao`) para rastreabilidade

---

## 3. Fluxo de Dados

```
Evento concluído (status_locacao = 'F')
    │
    ├─→ Tab Colaboradores
    │     ├─ Busca evento_colaboradores + JOIN colaboradores + presenças
    │     ├─ Exibe: nome, função, período, dias, presença, valor/dia, valor total
    │     ├─ Confirma presença (toggle visual)
    │     └─ "Enviar para Pagamento" → INSERT INTO contas_pagar (tipo='colaborador', evento_id, referencia_id=id_alocacao)
    │
    ├─→ Tab Fornecedores
    │     ├─ Busca item_cotacoes WHERE vencedor='S' + JOIN fornecedores + produtos_evento
    │     ├─ Exibe: fornecedor, serviço, sala, valor, NF (editável), vencimento (editável)
    │     └─ "Enviar para Pagamento" → INSERT INTO contas_pagar (tipo='fornecedor', evento_id, referencia_id=id_cotacao)
    │
    ├─→ Tab Fotos & PDF
    │     ├─ Upload por sala → evento_fotos (sala_id NOT NULL)
    │     ├─ Upload geral → evento_fotos (sala_id NULL)
    │     ├─ Preview em grid com opção de remover
    │     ├─ PDF sem valores → /fechamento/pdf/{id}?sem_valores=1
    │     └─ PDF completo → /fechamento/pdf/{id}
    │
    └─→ Totalizadores
          ├─ Total Colaboradores: SUM(valor) FROM contas_pagar WHERE tipo='colaborador' AND evento_id=X
          ├─ Total Fornecedores: SUM(valor) FROM contas_pagar WHERE tipo='fornecedor' AND evento_id=X
          ├─ Custo Total: soma dos dois
          ├─ Receita: SUM(total_item) FROM produtos_evento WHERE id_evento=X
          └─ Lucro: receita - custo_total
```

---

## 4. Estrutura de Arquivos

### 4.1 Novos Arquivos (9)

| Arquivo | Descrição |
|---------|-----------|
| `database/migrations/019_criar_tables_fechamento.sql` | Migrations de novas tabelas |
| `src/Repository/EventoFotoRepository.php` | CRUD de fotos do evento |
| `src/Repository/FechamentoRepository.php` | Queries de fechamento (colaboradores, fornecedores, totais) |
| `src/Service/EventoFotoService.php` | Upload/remoção de fotos com validação |
| `src/Service/FechamentoService.php` | Enviar pagamento, calcular totais |
| `src/Controllers/FechamentoController.php` | Controller com métodos AJAX |
| `views/evento/partials/edit-fechamento.php` | View principal com 3 tabs verticais |
| `views/evento/partials/edit-fechamento-colaboradores.php` | Partial Tab 1: Colaboradores |
| `views/evento/partials/edit-fechamento-fornecedores.php` | Partial Tab 2: Fornecedores |
| `views/evento/partials/edit-fechamento-fotos.php` | Partial Tab 3: Fotos & PDF |

### 4.2 Arquivos Modificados (5)

| Arquivo | Modificação |
|---------|-------------|
| `public/js/eventos/fechamento.js` | Reescrita completa com AJAX |
| `public/css/styles.css` | Adicionar CSS do fechamento |
| `public/index.php` | Adicionar rotas /fechamento |
| `src/Auth/Rbac.php` | Adicionar permissões |
| `views/evento/edit.php` | Adicionar script fechamento.js |

---

## 5. Estrutura do Controller

**Arquivo:** `src/Controllers/FechamentoController.php`

| Método | Rota | Tipo | Descrição |
|--------|------|------|-----------|
| `colaboradores($id)` | `GET /fechamento/colaboradores/{id}` | AJAX JSON | Lista colaboradores do evento com presenças e valores |
| `fornecedores($id)` | `GET /fechamento/fornecedores/{id}` | AJAX JSON | Lista fornecedores vencedores do evento |
| `totais($id)` | `GET /fechamento/totais/{id}` | AJAX JSON | Retorna totais financeiros do evento |
| `enviarColaboradorPagamento()` | `POST /fechamento/colaborador/pagamento` | AJAX JSON | Cria conta a pagar para colaborador |
| `enviarFornecedorPagamento()` | `POST /fechamento/fornecedor/pagamento` | AJAX JSON | Cria conta a pagar para fornecedor |
| `uploadFoto()` | `POST /fechamento/foto/upload` | AJAX JSON | Upload de foto (validação tipo/tamanho) |
| `removerFoto()` | `POST /fechamento/foto/remover` | AJAX JSON | Remove foto do banco e disco |
| `listarFotos($id)` | `GET /fechamento/fotos/{id}` | AJAX JSON | Lista fotos com contagem por sala |
| `pdfFechamento($id)` | `GET /fechamento/pdf/{id}` | Download PDF | Gera PDF (sem valores ou completo) |

---

## 6. Estrutura das Views

### 6.1 `edit-fechamento.php` (Main)

```
┌─────────────────────────────────────────────────────────────────┐
│  [VTab Nav]                                                     │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ ▶ Colaboradores                                            │ │
│  │ ▶ Fornecedores                                             │ │
│  │ ▶ Fotos & PDF                                              │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  VPANE 0: Colaboradores                                    │ │
│  │  VPANE 1: Fornecedores                                     │ │
│  │  VPANE 2: Fotos & PDF                                      │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Tab 1 — Colaboradores (`edit-fechamento-colaboradores.php`)

```
┌─ Card: Colaboradores do Evento ───────────────────────────────┐
│  Tabela:                                                       │
│  ┌────────┬────────┬──────────┬──────┬─────────┬──────────┐   │
│  │ Nome   │ Função │ Período  │ Dias │ Presença│ Valor    │   │
│  ├────────┼────────┼──────────┼──────┼─────────┼──────────┤   │
│  │ João   │ Téc.   │ 01-03/05 │  3   │ Completo│ R$ 750   │   │
│  │ Maria  │ Som    │ 01-03/05 │  3   │ 2/3     │ R$ 600   │   │
│  └────────┴────────┴──────────┴──────┴─────────┴──────────┘   │
│                                                                 │
│  [Enviar Selecionados para Pagamento]                          │
└─────────────────────────────────────────────────────────────────┘
┌─ Total Colaboradores ──────────────────────────────────────────┐
│  Total: R$ 1.350,00     [Enviar Todos para Pagamento]          │
└─────────────────────────────────────────────────────────────────┘
```

**Campos por colaborador:**
- Nome (colaborador.nome)
- Função (evento_colaboradores.funcao)
- Período (data_inicio a data_fim)
- Dias (DATEDIFF + 1)
- Presença (badge: Completo/Parcial/Ausente — baseado em evento_colaborador_presencas)
- Valor/Dia (valor_diaria)
- Valor Total (dias × valor_diaria)
- Status Pagamento (badge: Enviado/Pendente)
- Ação: Botão "Enviar" (cria conta em contas_pagar)

### 6.3 Tab 2 — Fornecedores (`edit-fechamento-fornecedores.php`)

```
┌─ Card: Fornecedores — Propostas Vencedoras ───────────────────┐
│  Tabela:                                                       │
│  ┌────────────┬─────────────┬──────┬─────────┬──────┬───────┐ │
│  │ Fornecedor │ Serviço     │ Sala │ Valor   │ NF   │ Venc. │ │
│  ├────────────┼─────────────┼──────┼─────────┼──────┼───────┤ │
│  │ Auratec    │ Sonorização │ Sala │ R$ 2.500│ [  ] │ [date]│ │
│  │ Lumitech   │ Iluminação  │ Sala2│ R$ 1.800│ [  ] │ [date]│ │
│  └────────────┴─────────────┴──────┴─────────┴──────┴───────┘ │
│                                                                 │
│  [Enviar Todos para Pagamento]                                  │
└─────────────────────────────────────────────────────────────────┘
┌─ Total Fornecedores ───────────────────────────────────────────┐
│  Total: R$ 4.300,00     [Enviar Todos para Pagamento]          │
└─────────────────────────────────────────────────────────────────┘
```

**Campos por fornecedor:**
- Nome (fornecedores.nome_fantasia)
- Serviço/Produto (produtos_evento.produto)
- Sala (salas.nome_sala)
- Valor (item_cotacoes.valor_proposto)
- NF (input editável — numero_nf)
- Vencimento (input date — data_vencimento, padrão +30 dias)
- Status (badge: Pendente/Enviado/Pago)
- Ação: Botão "Enviar" (cria conta em contas_pagar)

### 6.4 Tab 3 — Fotos & PDF (`edit-fechamento-fotos.php`)

```
┌─ Card: Fotos por Sala ────────────────────────────────────────┐
│  ▼ Sala Principal                    [2 fotos]                 │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  [Upload Fotos]                                          │ │
│  │  [thumb] [thumb] [thumb] ...                             │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ▶ Sala VIP                            [0 fotos]                │
│  ▶ Sala Externa                        [5 fotos]                │
└─────────────────────────────────────────────────────────────────┘

┌─ Card: Fotos Gerais do Evento ────────────────────────────────┐
│  [Upload Fotos Gerais]                                         │
│  [thumb] [thumb] [thumb] ...                                   │
└─────────────────────────────────────────────────────────────────┘

┌─ Card: Relatórios de Fechamento ──────────────────────────────┐
│  [📄 Relatório para Cliente (sem valores)]                     │
│  [📊 Relatório Completo (com custos e lucro)]                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. JavaScript — `public/js/eventos/fechamento.js`

### 7.1 Padrão Técnico

- IIFE: `(function() { 'use strict'; ... })();`
- Variáveis globais: `EVENTO_ID`, `CSRF_TOKEN`, `SALAS`
- AJAX: `fetch` API (sem jQuery)
- CSRF: `_csrf_token` no body form-encoded ou FormData
- Feedback: `showToast(type, title, message)`
- Sem refresh de página

### 7.2 Funções Principais

| Função | Tipo | Descrição |
|--------|------|-----------|
| `loadColaboradores()` | AJAX GET | Busca e renderiza tabela de colaboradores |
| `renderColaboradores()` | DOM | Renderiza HTML da tabela de colaboradores |
| `enviarColaboradorPagamento(id, valor, nome)` | AJAX POST | Envia 1 colaborador para pagamento |
| `enviarTodosColaboradoresPagamento()` | AJAX POST | Envia todos pendentes |
| `loadFornecedores()` | AJAX GET | Busca e renderiza tabela de fornecedores |
| `renderFornecedores()` | DOM | Renderiza HTML da tabela de fornecedores |
| `enviarFornecedorPagamento(id, nome)` | AJAX POST | Envia 1 fornecedor para pagamento |
| `enviarTodosFornecedoresPagamento()` | AJAX POST | Envia todos pendentes |
| `loadFotos()` | AJAX GET | Busca fotos e contagem por sala |
| `renderFotos()` | DOM | Atualiza grids de fotos |
| `uploadFoto(salaId, input)` | AJAX POST FormData | Upload de foto(s) |
| `removerFoto(fotoId, btn)` | AJAX POST | Remove foto |
| `loadTotais()` | AJAX GET | Busca totais financeiros |
| `toggleSalaFotos(salaId)` | DOM | Toggle accordion de fotos da sala |

### 7.3 Payloads AJAX

**Enviar colaborador para pagamento:**
```
POST /fechamento/colaborador/pagamento
Body: _csrf_token=X&evento_id=1&id_alocacao=6&valor_final=750.00

Response: { "success": true, "conta_id": 1, "valor": 750.00 }
```

**Enviar fornecedor para pagamento:**
```
POST /fechamento/fornecedor/pagamento
Body: _csrf_token=X&evento_id=1&id_cotacao=11&numero_nf=12345&data_vencimento=2026-05-27

Response: { "success": true, "conta_id": 2, "valor": 2500.00, "fornecedor": "AURATEC" }
```

**Upload foto:**
```
POST /fechamento/foto/upload (FormData)
_fields: _csrf_token, evento_id, sala_id, foto (File)

Response: { "success": true, "data": { "id": 1, "caminho": "uploads/eventos/1/foto_xxx.jpg", "nome": "foto.jpg" } }
```

---

## 8. PDF de Fechamento

### 8.1 Parâmetros

| URL | Descrição |
|-----|-----------|
| `/fechamento/pdf/{id}?sem_valores=1` | Relatório para cliente (sem valores financeiros) |
| `/fechamento/pdf/{id}` | Relatório completo interno (com custos e lucro) |

### 8.2 Conteúdo — Versão Sem Valores (Cliente)

1. **Cabeçalho**: Nome do evento, cliente, local, datas
2. **Salas e Itens**: Nome da sala, lista de produtos (sem valores)
3. **Fotos**: Fotos por sala + fotos gerais (embed base64)
4. **Colaboradores**: Nome, função, período (sem valores)
5. **Fornecedores**: Nome, serviço (sem valores)
6. **Observações**: observacoes_fechamento do evento

### 8.3 Conteúdo — Versão Completa (Interno)

1. **Cabeçalho**: Nome do evento, cliente, local, datas
2. **Cards de Totais**: Receita, Custo Total, Lucro, Margem
3. **Salas e Itens**: Nome da sala, lista de produtos (com valores)
4. **Fotos**: Fotos por sala + fotos gerais
5. **Colaboradores**: Nome, função, período, dias, valor/dia, total
6. **Fornecedores**: Nome, serviço, valor
7. **Observações**: observacoes_fechamento

### 8.4 Implementação do PDF

- Baseado em `PdfGeneratorService::setupHeaderFooter()` existente
- Usa Mpdf (biblioteca já instalada)
- Fotos embedadas via base64: `data:image/jpeg;base64,...`
- CSS inline para compatibilidade com Mpdf

---

## 9. Rotas — `public/index.php`

```php
// FECHAMENTO DE EVENTOS
$app->router()->group('/fechamento', function($router) {
    // Dados
    $router->get('/colaboradores/{id}', [App\Controllers\FechamentoController::class, 'colaboradores']);
    $router->get('/fornecedores/{id}', [App\Controllers\FechamentoController::class, 'fornecedores']);
    $router->get('/totais/{id}', [App\Controllers\FechamentoController::class, 'totais']);
    
    // Pagamento
    $router->post('/colaborador/pagamento', [App\Controllers\FechamentoController::class, 'enviarColaboradorPagamento']);
    $router->post('/fornecedor/pagamento', [App\Controllers\FechamentoController::class, 'enviarFornecedorPagamento']);
    
    // Fotos
    $router->get('/fotos/{id}', [App\Controllers\FechamentoController::class, 'listarFotos']);
    $router->post('/foto/upload', [App\Controllers\FechamentoController::class, 'uploadFoto']);
    $router->post('/foto/remover', [App\Controllers\FechamentoController::class, 'removerFoto']);
    
    // PDF
    $router->get('/pdf/{id}', [App\Controllers\FechamentoController::class, 'pdfFechamento']);
}, [\App\Http\Middleware\AuthMiddleware::class]);
```

---

## 10. RBAC — `src/Auth/Rbac.php`

Adicionar ao array `$permissions`:

```php
// Fechamento de Eventos
'fechamento' => ['administrador', 'produtor'],
'fechamento.visualizar' => ['administrador', 'produtor'],
'fechamento.enviar_pagamento' => ['administrador'],
'fechamento.gerar_pdf' => ['administrador', 'produtor'],

// Contas a Pagar (existente, garantir que está registrado)
'contas_pagar' => ['administrador', 'produtor'],
'contas_pagar.listar' => ['administrador', 'produtor'],
'contas_pagar.pagar' => ['administrador'],
```

---

## 11. CSS — Adicionar ao `public/css/styles.css`

```css
/* ==========================================
   FECHAMENTO - Tabs e Componentes
   ========================================== */

/* Accordion de fotos por sala */
.fechamento-sala-foto { border: 1px solid var(--bg-border-sub); border-radius: 8px; margin-bottom: 8px; overflow: hidden; }
.fechamento-sala-header { display: flex; align-items: center; gap: 8px; padding: 10px 14px; cursor: pointer; background: var(--bg-surface); transition: background .15s; }
.fechamento-sala-header:hover { background: var(--bg-elevated); }
.fechamento-sala-header span:first-child { font-weight: 600; font-size: 13px; flex: 1; }
.fechamento-chevron { width: 16px; height: 16px; transition: transform .2s; color: var(--text-3); }
.fechamento-sala-header.active .fechamento-chevron { transform: rotate(180deg); }
.fechamento-sala-content { padding: 12px; }

/* Upload area */
.fechamento-upload-area { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding: 12px; background: var(--bg-elevated); border-radius: 8px; border: 2px dashed var(--bg-border); }

/* Grid de fotos */
.fechamento-foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
.fechamento-foto-item { position: relative; border-radius: 6px; overflow: hidden; border: 1px solid var(--bg-border-sub); }
.fechamento-foto-item img { width: 100%; height: 100px; object-fit: cover; display: block; }
.fechamento-foto-item button { position: absolute; top: 4px; right: 4px; padding: 2px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }

/* Responsivo */
@media (max-width: 768px) {
  .fechamento-foto-grid { grid-template-columns: repeat(2, 1fr); }
}
```

---

## 12. Migration SQL Completa

```sql
-- database/migrations/019_criar_tables_fechamento.sql

-- 1. Adaptar contas_pagar para suportar eventos e múltiplos tipos
ALTER TABLE contas_pagar 
  ADD COLUMN evento_id INT NULL AFTER id,
  ADD COLUMN tipo ENUM('colaborador','fornecedor','outro') NOT NULL DEFAULT 'outro' AFTER evento_id,
  ADD COLUMN referencia_id INT NULL AFTER tipo,
  ADD CONSTRAINT fk_contas_pagar_evento 
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL;

-- 2. Criar tabela de fotos do evento
CREATE TABLE evento_fotos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evento_id INT NOT NULL,
  sala_id INT NULL,
  caminho_arquivo VARCHAR(500) NOT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
  FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE SET NULL,
  INDEX idx_evento_sala (evento_id, sala_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 13. Checklist de Implementação

- [ ] Executar migration SQL (criar evento_fotos + alterar contas_pagar)
- [ ] Criar `src/Repository/EventoFotoRepository.php`
- [ ] Criar `src/Repository/FechamentoRepository.php`
- [ ] Criar `src/Service/EventoFotoService.php`
- [ ] Criar `src/Service/FechamentoService.php`
- [ ] Criar `src/Controllers/FechamentoController.php`
- [ ] Reescrever `views/evento/partials/edit-fechamento.php`
- [ ] Criar `views/evento/partials/edit-fechamento-colaboradores.php`
- [ ] Criar `views/evento/partials/edit-fechamento-fornecedores.php`
- [ ] Criar `views/evento/partials/edit-fechamento-fotos.php`
- [ ] Reescrever `public/js/eventos/fechamento.js`
- [ ] Adicionar CSS ao `public/css/styles.css`
- [ ] Adicionar rotas ao `public/index.php`
- [ ] Adicionar permissões ao `src/Auth/Rbac.php`
- [ ] Adicionar script em `views/evento/edit.php`
- [ ] Criar diretório `public/uploads/eventos/` com permissão de escrita
- [ ] Testar fluxo completo: colaboradores → pagamento, fornecedores → pagamento, fotos → PDF

---

## 14. Regras de Negócio

### 14.1 Colaboradores

1. **Listagem**: Buscar de `evento_colaboradores` JOIN `colaboradores` WHERE `id_evento = X` AND `status = 'A'`
2. **Presença**: Contar dias com `status IN ('parcial', 'completo')` em `evento_colaborador_presencas`
3. **Valor Total**: `(DATEDIFF(data_fim, data_inicio) + 1) * valor_diaria`
4. **Enviar Pagamento**: 
   - Criar registro em `contas_pagar` com `tipo='colaborador'`, `referencia_id=id_alocacao`
   - Atualizar `evento_colaboradores.enviar_pagamento = 'S'`
   - **Não permitir duplicata**: verificar se já existe conta para o mesmo `referencia_id`
5. **Vencimento padrão**: 30 dias a partir da data atual

### 14.2 Fornecedores

1. **Listagem**: Buscar de `item_cotacoes` WHERE `vencedor='S'` AND `id_evento=X` JOIN `fornecedores` JOIN `produtos_evento`
2. **NF e Vencimento**: Campos editáveis pelo usuário antes de enviar
3. **Enviar Pagamento**:
   - Criar registro em `contas_pagar` com `tipo='fornecedor'`, `referencia_id=id_cotacao`
   - Incluir `numero_nf` se preenchido
   - **Não permitir duplicata**: verificar se já existe conta para o mesmo `referencia_id`

### 14.3 Fotos

1. **Upload**: 
   - Validar tipo: `image/jpeg`, `image/png`, `image/webp`
   - Validar tamanho: máximo 10MB
   - Nome único: `foto_{timestamp}_{random}.{ext}`
   - Organizar em: `public/uploads/eventos/{evento_id}/`
2. **Remoção**: 
   - Verificar pertencimento (`evento_id` da foto == evento atual)
   - Remover arquivo do disco + registro do banco
3. **Exibição**: 
   - Grid responsivo (3 colunas → 2 em mobile)
   - Thumbnail com clique para abrir em nova aba
   - Botão de remover em cada thumbnail

### 14.4 PDF

1. **Sem valores**: Omitir todos os campos financeiros (valores, totais, lucro)
2. **Com valores**: Incluir cards de totais, valores por item/colaborador/fornecedor
3. **Fotos**: Embed como base64 para funcionar offline no PDF
4. **Page break**: `page-break-inside: avoid` em seções

---

## 15. Ordem de Execução

1. **Database** — Executar migration
2. **Repository** — Criar EventoFotoRepository + FechamentoRepository
3. **Service** — Criar EventoFotoService + FechamentoService
4. **Controller** — Criar FechamentoController
5. **Views** — Criar edit-fechamento.php + partials
6. **JavaScript** — Reescrever fechamento.js
7. **CSS** — Adicionar estilos
8. **Rotas** — Registrar em index.php
9. **RBAC** — Adicionar permissões
10. **Teste** — Verificar fluxo completo

---

## 16. Possíveis Problemas e Soluções

| Problema | Causa | Solução |
|----------|-------|---------|
| `contas_pagar` sem coluna `evento_id` | Migration não executada | Executar ALTER TABLE |
| Fotos não aparecem no PDF | Caminho errado ou arquivo não existe | Verificar `__DIR__` relativo ao controller |
| PDF não gera com fotos | Base64 muito grande para Mpdf | Limitar tamanho ou reduzir qualidade |
| Duplicata em contas_pagar | Verificação de existência falhou | Usar UNIQUE KEY em (evento_id, tipo, referencia_id) |
| Upload falha | Permissão do diretório | `chmod 777 public/uploads/eventos` |
| Colaborador sem presença | Nenhuma presença registrada | Mostrar "Ausente" como padrão |
| Fornecedor não aparece | `vencedor != 'S'` ou `id_evento` errado | Verificar query e dados no banco |

---

## 17. Referências de Código Existente

| Padrão | Arquivo de Referência |
|--------|----------------------|
| AJAX com fetch | `public/js/eventos/fornecedores.js` |
| IIFE pattern | `public/js/eventos/salas-produtos.js` |
| Toast feedback | Todos os JS de eventos |
| CSRF validation | `src/Controllers/EventoController.php` |
| PDF generation | `src/Controllers/EventoController.php::gerarPdf()` |
| Upload de arquivo | `src/Controllers/CotacaoController.php::uploadAnexo()` |
| Tabs verticais | `views/evento/partials/edit-dados.php` |
| Componentes Design System | `docs/layout/branco/assets/components/` |

---

**FIM DO PLANEJAMENTO**

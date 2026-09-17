# Backlog de Refatorações

Registro de refatorações concluídas e pendentes. Cada item tem contexto suficiente para retomar sem re-análise.

---

## ✅ Concluídas

### [2026-06-29] TD-015 — Split de rotas de `public/index.php`
- **Antes:** `public/index.php` com 530 LOC contendo todas as rotas
- **Depois:** `public/index.php` 37 LOC (bootstrap puro); 4 arquivos em `routes/`
  - `routes/publico.php` — sem auth: `/auth`, `/presenca`, `/cadastro-*`, `/categorias`, `/proxy`
  - `routes/cron.php` — `CronAuthMiddleware`: `/cron` + webhook
  - `routes/api.php` — `ApiKeyMiddleware`: `/api/v1`
  - `routes/web.php` — `AuthMiddleware+CsrfMiddleware`: tudo autenticado
- **Técnica:** `require_once` no mesmo escopo, `$app` disponível em todos os arquivos sem passar por parâmetro
- **Commit:** `acec473`

### [2026-06-29] TD-027 — EventoController (1427 → 364 LOC)
- **Antes:** EventoController tinha 1427 LOC com CRUD de evento + items + PDF + RH + fechamento
- **Extraídos em sessões anteriores:** `FechamentoController` (711 LOC), `EventoRHController` (345 LOC), `EventoPdfController` (307 LOC), `ProdutoEventoController` (160 LOC)
- **Sessão 13 (2026-06-29):** Movidos 5 métodos AJAX de produto-evento para `ProdutoEventoController`:
  - `adicionarItem()` — cria item com lookup de planilha para potencia_w/horas_uso
  - `excluirItem()` — deleta via `item_id` no POST body
  - `listarItensPlanilha()` — lista planilhas para autocomplete
  - `salvarObsItem($id)` — salva observação de montagem
  - `atualizarCampo($id)` — update inline de campos com whitelist
- **Rotas atualizadas** em `routes/web.php` e `/api/eventos` para apontar para `ProdutoEventoController`
- **Resultado:** `EventoController` 364 LOC, `ProdutoEventoController` 300 LOC
- **Commit:** `488c54c`

### [2026-06-29] TD-004 — RBAC via banco de dados
- **Antes:** `Rbac.php` com array `$permissions` hardcoded (75 permissões)
- **Depois:** `Rbac::check()` lê de `role_permissoes JOIN permissoes` via DB; cache estático por request
- **Migration:** `046_seed_rbac.sql` — seed de 17 módulos, 75 permissões, 141 role_permissoes
- **Gestão:** UI em `/configuracoes` aba Permissões — checkboxes por role, AJAX
- **Commit:** `d891cdd`

### [2026-06-29] EventoPdfController (852 → 307 LOC)
- **Antes:** 3 métodos privados de geração HTML com string concatenation: `gerarFechamentoHtml` (181 LOC), `gerarEventoHtml` (225 LOC), `gerarMontagemHtml` (168 LOC)
- **Depois:** Templates PHP em `views/pdf/fechamento.php`, `views/pdf/evento.php`, `views/pdf/montagem.php` com `<?= ?>` syntax
- **Técnica:** Método `renderPdfTemplate(string $view, array $vars): string` usando `ob_start()`/`ob_get_clean()` + `extract($vars, EXTR_SKIP)`
- **Commit:** `0a3f881`

### [2026-06-29] TD-028 — Split de fechamento.js (1949 LOC)
- Dividido em 4 módulos de domínio (sessão 12)

---

## 🔵 Pendente — Alta Prioridade

### TD-039 — ContasPagarController: extrair WhatsApp para controller próprio
- **Arquivo:** `src/Controllers/ContasPagarController.php` — 569 LOC
- **Problema:** 4 métodos de WhatsApp embutidos num controller de contas a pagar:
  - `enviarComprovante($id)` — linhas ~328-417 (90 LOC) — busca conta, resolve telefone do fornecedor, monta mensagem com link do comprovante, chama `WhatsAppService::sendMessage()`
  - `whatsappStatus()` — 16 LOC — delega para `WhatsAppService::getStatus()`
  - `whatsappRestart()` — ~25 LOC — delega para `WhatsAppService::restart()`
  - `whatsappLogout()` — ~20 LOC — delega para `WhatsAppService::logout()`
- **Plano:**
  1. Criar `src/Controllers/WhatsAppController.php` com os 4 métodos
  2. Atualizar rotas em `routes/web.php` (as 4 rotas do grupo `/contas-pagar` que apontam para esses métodos)
  3. `ContasPagarController` fica com ~430 LOC — mais focado
- **Impacto:** Zero no comportamento. Apenas reorganização. URLs permanecem iguais (só muda o controller na rota).
- **Dependências:** `WhatsAppService`, `FornecedorService` (para buscar telefone em `enviarComprovante`)
- **Rotas a alterar:** grep em `routes/web.php` por `whatsappStatus|whatsappRestart|whatsappLogout|enviarComprovante`

---

## 🟡 Pendente — Média Prioridade

### TD-013 — Refatorar EmailService (440 LOC)
- **Arquivo:** `src/Service/EmailService.php` — 440 LOC
- **Problema:** Um único serviço com múltiplos templates de e-mail embutidos como strings HTML (similar ao padrão do `EventoPdfController` antes da sessão 13)
- **Plano (a verificar antes de executar):**
  1. Identificar quantos templates HTML estão inline no serviço
  2. Extrair cada template para `views/email/nome-template.php`
  3. Usar `ob_start()`/`ob_get_clean()` + `extract()` (mesmo padrão do PDF)
- **Pré-requisito:** Ler o arquivo e confirmar que é de fato o padrão de concatenação (pode ter mudado)

### TD-040 — FechamentoService: analisar para split
- **Arquivo:** `src/Service/FechamentoService.php` — 483 LOC
- **Contexto:** Maior service do projeto. Inclui lógica de colaboradores, fornecedores, outros custos, totais financeiros, geração de PDF (delega para controller)
- **Plano (a verificar antes de executar):**
  1. Grep pelos métodos (`grep -n "public function" src/Service/FechamentoService.php`)
  2. Verificar se há responsabilidades mistas (ex: lógica de relatório misturada com CRUD)
  3. Se sim: extrair `FechamentoRelatorioService` ou similar
- **Risco:** FechamentoController (711 LOC) depende fortemente desse service — qualquer split deve manter compatibilidade de interface

### TD-041 — DashboardController: extrair queries de stats para service
- **Arquivo:** `src/Controllers/DashboardController.php` — 435 LOC
- **Problema provável:** Queries de contagem/stats diretamente no controller, sem service
- **Pré-requisito:** Ler o arquivo e confirmar (pode já ter sido refatorado)

---

## 🟢 Pendente — Baixa Prioridade / Cosmético

### TD-042 — ProdutoEventoController: consolidar store() e adicionarItem()
- **Contexto:** `ProdutoEventoController` agora tem dois métodos que criam items de evento:
  - `store()` — endpoint REST simples, usado pelo app mobile ou por chamadas diretas
  - `adicionarItem()` — endpoint da UI web com lógica extra (lookup de planilha para potencia_w/horas_uso, normalização de nomes de campo)
- **Opção A:** Manter separados (2 frontends com contratos diferentes — é aceitável)
- **Opção B:** Unificar em `store()` com detecção de formato de entrada
- **Recomendação:** Opção A — não unificar. A diferença de contrato (`item` vs `produto`, `quantidade` vs `qtd`) é deliberada e eliminar isso quebraria chamadores existentes. Documentado para não "resolver" acidentalmente.

### Limpeza de docblocks em ProdutoEventoController
- **Arquivo:** `src/Controllers/ProdutoEventoController.php`
- **Problema:** Métodos `indexBySala`, `indexByEvento`, `store`, `update`, `delete`, `autocomplete` ainda têm docblocks multi-linha
- **Fix:** Remover todos (são comments do tipo "explica o que" — já evidente pelo nome do método)
- **Impacto:** ~10 linhas

---

## Padrões Estabelecidos

### Template de extração de HTML (padrão PDF/Email)
Quando um método privado constrói HTML por concatenação de string, extrair para view PHP:
```php
// Controller
private function renderTemplate(string $view, array $vars): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/../../views/' . $view . '.php';
    return (string)ob_get_clean();
}
```
View recebe variáveis via `extract()` e usa `<?= ?>` em vez de `$html .=`.

### Mover métodos de controller (padrão item-evento)
Quando métodos em controller A usam `$this->serviceB` e o controller B já existe para esse service:
1. Copiar métodos para B, trocando `$this->serviceA->metodo()` por `$this->service->metodo()`
2. Atualizar rotas para apontar para B
3. Remover de A

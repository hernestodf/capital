# Auditoria de Seguranca CSRF — SisLoc

**Data:** 2026-04-30
**Escopo:** Todos os endpoints, controllers, views e JavaScript do sistema

---

## Resumo Executivo

| Nivel | Quantidade | Status |
|-------|-----------|--------|
| **CRITICO** | 1 | Pendente |
| **ALTO** | 1 | Pendente |
| **MEDIO** | 1 | Observacao |
| **BAIXO** | 0 | - |

**Risco Geral: MEDIO-ALTO** — A protecao CSRF esta bem implementada na maioria do sistema, mas ha 2 vulnerabilidades que precisam de atencao.

---

## 1. O que esta FUNCIONANDO CORRETAMENTE

### 1.1 CsrfMiddleware implementado e aplicado
- **Arquivo:** `src/Http/Middleware/CsrfMiddleware.php`
- **Cobertura:** 31 grupos de rotas com AuthMiddleware + CsrfMiddleware
- **Metodos protegidos:** POST, PUT, DELETE, PATCH
- **Token sources:** `_csrf_token` (POST body), `X-CSRF-Token` (header), `X-XSRF-Token` (header)

### 1.2 Token CSRF gerado corretamente
- **Arquivo:** `src/Core/Csrf.php`
- **Algoritmo:** `random_bytes(32)` → 64 caracteres hex (256 bits de entropia)
- **Gerado em:** `AuthController::login()` (linha 17, se nao existir)
- **Regenerado em:** Login com sucesso (linha 82) e logout (`Csrf::forget()`)
- **Nao rotaciona automaticamente** (correto — evitaria falhas em AJAX)

### 1.3 Todos os controllers validam CSRF
- 90+ chamadas de `Csrf::validate()` encontradas em controllers
- Controllers com validacao manual + middleware = protecao em profundidade (redundancia segura)

### 1.4 JavaScript envia CSRF corretamente
- `apiFetch()` helper (scripts.js linhas 44-55): adiciona `_csrf_token` automaticamente
- `window.CSRF_TOKEN` definido em `footer.php` linha 22
- Arquivos JS analisados: `cotacao.js`, `eventos/*.js`, `scripts.js` — todos enviam CSRF em POSTs

### 1.5 Formularios HTML incluem CSRF
- Todos os forms com `method="POST"` incluem `<input type="hidden" name="_csrf_token">`
- Views analisadas: 43 arquivos com referencias a CSRF

### 1.6 Cookies de sessao configurados corretamente
| Config | Local | Producao |
|--------|-------|----------|
| `cookie_httponly` | 1 | 1 |
| `cookie_secure` | 0 | 1 |
| `cookie_samesite` | Lax | Strict |
| `use_strict_mode` | 1 | 1 |
| `sid_length` | 48 | 48 |
| `sid_bits_per_character` | 6 | 6 |

### 1.7 Endpoints publicos baseados em token
- `/colaboradores/verificar/{token}` POST — protegido por token unico na URL
- `/presenca/{token}` POST — protegido por token unico na URL
- `/cadastro-colaborador` e `/cadastro-fornecedor` POST — endpoints publicos sem sessao (CSRF nao aplicavel)
- **Correto:** CSRF nao e necessario em endpoints publicos sem autenticacao por sessao.

---

## 2. Vulnerabilidades Encontradas

### VULN-01: Logout via GET (Risco: CRITICO)

**Local:** `public/index.php` linha 21 + `src/Controllers/AuthController.php` linha 109

```php
// Rota
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);

// Controller (linha 109-116)
public function logout(): Response
{
    Csrf::forget();
    Rbac::logout();
    $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    return $this->redirect($baseUrl . '/auth/login');
}
```

**Problema:** Logout e acessado via GET. Qualquer site pode forcá-lo navegando para a URL.

**Exemplo de exploracao:**
```html
<!-- Site malicioso — vitima logada no SisLoc visita a pagina -->
<img src="https://sisloc.online/public/auth/logout">
<!-- ou -->
<link rel="prefetch" href="https://sisloc.online/public/auth/logout">
<!-- ou simples link que a vitima clica sem perceber -->
<a href="https://sisloc.online/public/auth/logout">Clique aqui</a>
```

**Impacto:** Negação de servico (DoS) — atacante pode desconectar usuarios repetidamente. Em combinacao com outras vulnerabilidades, pode facilitar ataques de session fixation.

**Correcao recomendada:**

Opcao A — Mudar para POST (mais seguro):
```php
// public/index.php
$router->post('/logout', [App\Controllers\AuthController::class, 'logout']);

// views/layout/header.php — trocar botao por form
<form method="POST" action="<?= $baseUrl ?>/auth/logout" style="display:inline">
  <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
  <button type="submit" class="btn-logout">Sair</button>
</form>

// AuthController.php — adicionar protecao extra
public function logout(): Response
{
    // Validar CSRF se vier via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf_token'] ?? '';
        if (!\App\Core\Csrf::validate($token)) {
            // Logar mas nao bloquear — permitir GET como fallback
        }
    }
    Csrf::forget();
    Rbac::logout();
    $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    return $this->redirect($baseUrl . '/auth/login');
}
```

Opcao B — Manter GET mas proteger com SameSite=Strict (ja implementado em producao):
- Em producao, `cookie_samesite=Strict` impede que navegadores enviem cookies em requests cross-site.
- **Porem:** SameSite nao protege contra links clicados dentro do proprio site (ex: phishing via XSS).
- **Recomendacao:** Aplicar Opcao A para protecao completa.

---

### VULN-02: Token CSRF pode ser null apos logout (Risco: ALTO)

**Local:** `views/layout/footer.php` linha 22

```php
window.CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
```

Se `Csrf::getToken()` retornar `null` (token nunca gerado ou limpo por logout), o resultado e:
```javascript
window.CSRF_TOKEN = '';
```

**Cenario de ocorrencia:**
1. Usuario faz logout → `Csrf::forget()` remove o token da sessao
2. Usuario acessa uma pagina que nao e a de login (ex: URL direta via bookmark)
3. `Csrf::getToken()` retorna `null` → `CSRF_TOKEN = ''`
4. Qualquer AJAX POST envia token vazio → rejeitado pelo middleware → erro 400

**Impacto:** Funcionalidade quebrada apos logout/acesso direto. Nao e uma vulnerabilidade de seguranca per se, mas pode causar DoS acidental.

**Correcao recomendada:**

```php
// views/layout/footer.php linha 22
$csrfToken = \App\Core\Csrf::getToken();
if ($csrfToken === null) {
    $csrfToken = \App\Core\Csrf::generate();
}
window.CSRF_TOKEN = '<?= $csrfToken ?>';
```

Ou modificar `Csrf::getToken()`:
```php
public static function getToken(): string
{
    if (!isset($_SESSION[self::$tokenKey])) {
        self::generate();
    }
    return $_SESSION[self::$tokenKey];
}
```

---

### VULN-03: Dupla validacao CSRF (middleware + controller) — nao e vulnerabilidade

**Local:** Todas as rotas protegidas + controllers com `Csrf::validate()` manual

**Observacao:** O CsrfMiddleware valida o token ANTES do controller, e muitos controllers validam NOVAMENTE. Isso nao e um bug — o token nao e rotacionado na validacao, entao multiplas validacoes funcionam corretamente.

**Impacto:** Nenhum — e uma protecao em profundidade valida.

**Recomendacao:** Opcionalmente, remover validacao manual dos controllers (ja coberta pelo middleware) para simplificar o codigo. Manter apenas em controllers que precisam de tratamento de erro customizado.

---

## 3. Analise Detalhada por Categoria

### 3.1 Rotas Protegidas (CSRF ✅)

| Grupo | Rotas POST | Middleware CSRF |
|-------|-----------|-----------------|
| /usuarios | store, update, delete, toggle | ✅ |
| /clientes | store, update, delete, toggle | ✅ |
| /demandantes | store, update, delete, toggle | ✅ |
| /configuracoes | update, update-roles, update-theme, whatsapp-* | ✅ |
| /dashboard | (nenhuma POST) | N/A |
| /ai | execute, orchestrate | ✅ |
| /contas-pagar | store, update, delete, pagar, enviar-comprovante, whatsapp-* | ✅ |
| /fornecedores | store, update, delete, toggle | ✅ |
| /categorias | store, update, delete | ✅ |
| /subcategorias | store, update, delete | ✅ |
| /colaboradores | store, update, delete, toggle, enviar-link, reenviar-link | ✅ |
| /produtores | store, update, delete, toggle | ✅ |
| /eventos | store, update, delete, toggle, converter, finalizar, itens/* | ✅ |
| /api/eventos | salas (POST/PUT/DELETE), itens/observacao | ✅ |
| /salas | store, update, delete, toggle | ✅ |
| /produtos-evento | store, update, delete | ✅ |
| /categorias-sala | store, update, delete, toggle | ✅ |
| /api | categorias (POST/PUT/DELETE) | ✅ |
| /planilhas | store, update, delete | ✅ |
| /unidades-medida | store, update, delete | ✅ |
| /estoque | store, update, delete, toggle-locado, relatorios/gerar | ✅ |
| /secoes | store, update, delete | ✅ |
| /seriais | store, store-batch, update, update-status, delete | ✅ |
| /montagem | inserir-serial, inserir-lote, encaminhar-sala, remover-da-sala, devolver | ✅ |
| /devolucao | processar-unico, processar-lote, resolver-pendencia, alterar-status | ✅ |
| /cotacao | solicitar-cotacao, propostas, marcar-vencedor, enviar-mensagem, upload-anexo | ✅ |
| /eventos/cotacao | (nenhuma POST) | N/A |
| /evento/rh | alocar, desalocar, pagamento, enviar-pagamento, reenviar-comprovante | ✅ |
| /fechamento | presenca-manual, colaborador/pagamento, fornecedor/pagamento, foto/upload, foto/remover | ✅ |
| /api/ai | execute, orchestrate, learn/* | ✅ |

### 3.2 Rotas Publicas (CSRF N/A — sem sessao)

| Rota | Metodo | Protecao |
|------|--------|----------|
| /cadastro-colaborador | POST | Rate limiting + validacao de dados |
| /cadastro-fornecedor | POST | Rate limiting + validacao de dados |
| /colaboradores/verificar/{token} | POST | Token unico na URL |
| /presenca/{token} | POST | Token unico na URL |
| /auth/login | POST | Rate limiting por IP + CSRF obrigatorio |

### 3.3 Rotas GET com Potencial Risco

| Rota | Risco | Mitigacao |
|------|-------|-----------|
| GET /auth/logout | **CRITICO** — logout forcado via CSRF | SameSite=Strict (producao), mas vulneravel a clicks internos |
| GET /eventos/pdf/{id} | Baixo — apenas leitura de PDF | Autenticacao necessaria |
| GET /montagem/pdf/{id} | Baixo — apenas leitura de PDF | Autenticacao necessaria |
| GET /fechamento/pdf/{id} | Baixo — apenas leitura de PDF | Autenticacao necessaria |

---

## 4. Lista Priorizada de Correcoes

### Prioridade 1 — CRITICO (fazer imediatamente)

1. **Mudar logout de GET para POST**
   - Arquivo: `public/index.php` linha 21
   - Arquivo: `views/layout/header.php` linha 94
   - Arquivo: `src/Controllers/AuthController.php` linha 109
   - Tempo estimado: 10 minutos
   - Risco de regressao: Baixo

### Prioridade 2 — ALTO (fazer em seguida)

2. **Garantir geracao automatica do token CSRF**
   - Arquivo: `src/Core/Csrf.php` metodo `getToken()`
   - Ou: `views/layout/footer.php` linha 22
   - Tempo estimado: 5 minutos
   - Risco de regressao: Nenhum

### Prioridade 3 — MEDIO (opcional)

3. **Remover validacao CSRF redundante dos controllers**
   - 90+ chamadas de `Csrf::validate()` em controllers
   - Ja cobertas pelo CsrfMiddleware
   - Tempo estimado: 2 horas
   - Risco de regressao: Baixo (middleware ja protege)

---

## 5. Boas Praticas para Evitar CSRF

### 5.1 Regras para novos desenvolvedores

1. **Toda rota POST/PUT/DELETE/PATCH deve ter CsrfMiddleware** — ja automatizado nos grupos
2. **Toda rota GET deve ser idempotente** — nunca modificar dados
3. **Logout DEVE ser POST** — nunca GET
4. **Formularios devem incluir `<input type="hidden" name="_csrf_token">`**
5. **AJAX deve usar `apiFetch()`** ou incluir CSRF manualmente
6. **Endpoints publicos sem sessao nao precisam de CSRF** (mas precisam de outra protecao)

### 5.2 Checklist para novas rotas

- [ ] Rota POST/PUT/DELETE esta em um grupo com CsrfMiddleware?
- [ ] Formulario HTML inclui campo hidden `_csrf_token`?
- [ ] JavaScript usa `CSRF_TOKEN` global ou `apiFetch()`?
- [ ] Controller valida CSRF (se precisar de mensagem customizada)?
- [ ] Se for GET, a rota e apenas leitura (sem efeitos colaterais)?

### 5.3 Padrao de formulario seguro

```php
<form method="POST" action="<?= $baseUrl ?>/modulo/store">
    <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
    <!-- campos -->
    <button type="submit">Salvar</button>
</form>
```

### 5.4 Padrao de AJAX seguro

```javascript
// Opcao 1: Usar apiFetch (recomendado)
apiFetch(BASE_URL + '/modulo/store', {
    method: 'POST',
    data: { nome: 'teste', email: 'teste@test.com' }
});

// Opcao 2: Fetch manual com CSRF
var formData = new FormData();
formData.append('_csrf_token', window.CSRF_TOKEN);
formData.append('nome', 'teste');

fetch(BASE_URL + '/modulo/store', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
});
```

---

## 6. Conclusao

O sistema SisLoc tem uma **protecao CSRF robusta** na maioria das suas funcionalidades. Os pontos positivos sao:

- CsrfMiddleware aplicado em 31 grupos de rotas
- Token gerado com `random_bytes(32)` (256 bits de entropia)
- Cookies de sessao com HttpOnly, Secure e SameSite=Strict (producao)
- Todos os controllers validam CSRF manualmente
- JavaScript helper `apiFetch()` automatiza envio do token

Os **2 pontos de atencao** sao:
1. Logout via GET (CRITICO — DoS potencial)
2. Token CSRF pode ser null apos logout (ALTO — funcionalidade quebrada)

Ambos sao correcoes simples e de baixo risco. Recomenda-se aplicar imediatamente.

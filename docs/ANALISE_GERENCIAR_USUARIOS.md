# Análise: Gerenciar Usuários

**Data:** 27/04/2026  
**Status:** 🔍 ANÁLISE COMPLETA  
**Arquivo:** `/var/www/html/sisloc/views/user/index.php`

---

## ✅ FUNCIONAMENTO

### **Status: FUNCIONANDO PARCIALMENTE**

| Item | Status | Detalhes |
|------|--------|----------|
| **Controller** | ✅ OK | UserController com todos os métodos |
| **Rotas** | ✅ OK | 8 rotas configuradas (/users/*) |
| **Database** | ✅ OK | 2 usuários cadastrados |
| **View** | ⚠️ Parcial | Usa componentes mas pode melhorar |
| **RBAC** | ✅ OK | Proteção isAdmin() em todas as rotas |
| **CSRF** | ✅ OK | Token em todas as operações POST |

---

## 📊 COMPONENTES EM USO

### ✅ **Componentes Atuais:**

| Componente | Uso | Status |
|------------|-----|--------|
| ✅ `renderTable()` | Listagem de usuários | **USANDO** |
| ✅ `renderTableActions('default')` | Botões Editar/Excluir | **USANDO** |
| ❌ `renderCard()` | Container principal | **NÃO USANDO** |
| ❌ `renderBadge()` | Status e roles | **NÃO USANDO** (HTML inline) |
| ❌ `renderButton()` | Botão "Novo" | **NÃO USANDO** (HTML inline) |
| ❌ `renderCardStat()` | Métricas | **NÃO USANDO** |

---

## ❌ PROBLEMAS IDENTIFICADOS

### **1. HTML Inline ao Invés de Componentes**

**PROBLEMA:** Badges de status e role estão hardcoded

```php
// LINHA 13 - BADGE INLINE (ERRADO)
['html' => true, 'content' => '<span class="badge ' . ($u['role'] === 'administrador' ? 'purple' : ($u['role'] === 'produtor' ? 'green' : 'cyan')) . '">' . htmlspecialchars($u['role']) . '</span>']

// LINHA 14 - BOTÃO INLINE (ERRADO)
['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($u['status'] ? 'btn-green' : 'btn-red') . '" onclick="toggleUser(' . $u['id'] . ', this)">' . ($u['status'] ? 'Ativo' : 'Inativo') . '</button>']
```

**SOLUÇÃO:** Usar `renderBadge()` e `renderButton()`

---

### **2. Sem Métricas do Sistema**

**PROBLEMA:** Dashboard não mostra estatísticas

```
❌ Total de usuários
❌ Usuários ativos
❌ Usuários por role (admin, produtor, estoquista)
❌ Últimos cadastros
```

---

### **3. Botão "Novo" Inline**

**PROBLEMA:** Linha 36 usa HTML direto

```php
// LINHA 36 - BOTÃO INLINE (ERRADO)
<a href="<?= $baseUrl ?>/users/create" class="btn btn-sm btn-cyan">Novo</a>
```

**SOLUÇÃO:** Usar `renderButton()`

---

### **4. Sem Componente Card**

**PROBLEMA:** Card HTML manual ao invés de `renderCard()`

```php
// LINHAS 33-68 - CARD INLINE (ERRADO)
<div class="card">
  <div class="card-head">
    <span class="card-title">Tabela Completa — Busca + Paginação + CRUD</span>
    ...
  </div>
  ...
</div>
```

---

## 🔍 ANÁLISE DO CONTROLLER

### ✅ **UserController.php - OK**

```php
✅ index() - Lista com paginação
✅ create() - Formulário criação
✅ store() - Salva novo usuário
✅ edit() - Formulário edição
✅ update() - Atualiza usuário
✅ show() - Visualiza usuário
✅ delete() - Exclui usuário
✅ toggle() - Alterna status ativo/inativo
```

### ✅ **Segurança:**

```php
✅ RBAC: isAdmin() em todas as rotas
✅ CSRF: Token em todas as operações POST
✅ Validação: Try/catch em operações críticas
✅ Sanitização: htmlspecialchars() na view
```

---

## 🎯 PROPOSTA DE REFATORAÇÃO

### **Layout Atual:**
```
┌─────────────────────────────────────────────┐
│ 👥 Gerenciar Usuários                       │
│ Cadastro e controle de acesso               │
├─────────────────────────────────────────────┤
│ [Tabela Completa — Busca + Paginação + CRUD]│ [Novo]
├─────────────────────────────────────────────┤
│ ID | Nome | Email | Função | Status | Ações │
│ 1  | João | j@.com| admin  | Ativo  | ⋮    │
│ 2  | Maria| m@.com| prod   | Ativo  | ⋮    │
└─────────────────────────────────────────────┘
```

### **Layout Proposto:**
```
┌─────────────────────────────────────────────┐
│ 👥 Gerenciar Usuários                       │
│ Cadastro e controle de acesso               │
├──────────┬──────────┬──────────┬────────────┤
│ 👥 Total │ ✅ Ativos│ 👑 Admin │ 📦 Produtor│ ← CardStat
│    150   │   142    │    12    │     45     │
├──────────┴──────────┴──────────┴────────────┤
│ [Card com renderCard()]                      │
│ 👥 Usuários Cadastrros                       │
│                              [Novo Button]   │ ← Button
├─────────────────────────────────────────────┤
│ ID | Nome | Email | Função | Status | Ações │
│ 1  | João | j@.com|👑 Admin|✅ Ativo| ⋮    │ ← Badge
│ 2  | Maria| m@.com|📦 Prod |✅ Ativo| ⋮    │ ← Badge
└─────────────────────────────────────────────┘
```

---

## 📝 CÓDIGO REFATORADO (PROPOSTA)

```php
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';

// Calcular estatísticas
$totalUsers = count($users ?? []);
$activeUsers = count(array_filter($users ?? [], fn($u) => $u['status'] == 1));
$adminUsers = count(array_filter($users ?? [], fn($u) => $u['role'] === 'administrador'));
$produtorUsers = count(array_filter($users ?? [], fn($u) => $u['role'] === 'produtor'));

$actionBtns = renderTableActions('default');
$rows = [];
foreach ($users as $u) {
    // Badge de Role
    $roleBadge = renderBadge([
        'label' => ucfirst($u['role']),
        'variant' => match($u['role']) {
            'administrador' => 'purple',
            'produtor' => 'green',
            default => 'cyan'
        },
        'size' => 'sm'
    ]);
    
    // Badge de Status
    $statusBadge = renderBadge([
        'label' => $u['status'] ? 'Ativo' : 'Inativo',
        'variant' => $u['status'] ? 'green' : 'red',
        'size' => 'sm'
    ]);
    
    $rows[] = [
        $u['id'],
        ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($u['name']) . '</span>'],
        htmlspecialchars($u['email']),
        ['html' => true, 'content' => $roleBadge],
        ['html' => true, 'content' => $statusBadge],
    ];
}
?>

<!-- Section Header -->
<div class="section-header">
  <div class="section-icon">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
  </div>
  <div>
    <div class="section-title">Gerenciar Usuários</div>
    <div class="section-sub">Cadastro e controle de acesso</div>
  </div>
</div>
<div class="divider"></div>

<!-- Stats Cards -->
<div class="col4">
  <?php echo renderCardStat([
    'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
    'value' => $totalUsers,
    'label' => 'Total Usuários',
    'color' => 'var(--neon-cyan)'
  ]); ?>

  <?php echo renderCardStat([
    'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'value' => $activeUsers,
    'label' => 'Usuários Ativos',
    'color' => 'var(--neon-green)'
  ]); ?>

  <?php echo renderCardStat([
    'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>',
    'value' => $adminUsers,
    'label' => 'Administradores',
    'color' => 'var(--neon-purple)'
  ]); ?>

  <?php echo renderCardStat([
    'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
    'value' => $produtorUsers,
    'label' => 'Produtores',
    'color' => 'var(--neon-yellow)'
  ]); ?>
</div>

<!-- Users Table Card -->
<div class="card" style="margin-top:16px">
  <div class="card-head">
    <span class="card-title">👥 Usuários Cadastrados</span>
    <?php echo renderButton([
      'label' => 'Novo Usuário',
      'variant' => 'cyan',
      'size' => 'sm',
      'onclick' => "window.location.href='" . $baseUrl . "/users/create'"
    ]); ?>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($users)): ?>
    <div class="table-empty">
      <div class="table-empty-flex">
        <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <div>Nenhum usuário encontrado</div>
        <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Usuário" para adicionar</div>
      </div>
    </div>
    <?php else: ?>
    <?= renderTable([
        'id'               => 'tbl-users',
        'searchable'       => true,
        'searchPlaceholder'=> 'Buscar usuário, email ou função...',
        'paginated'        => true,
        'perPage'          => 10,
        'headers' => [
            ['label' => 'ID', 'sortable' => true],
            ['label' => 'Nome', 'sortable' => true],
            ['label' => 'Email', 'sortable' => true],
            ['label' => 'Função', 'sortable' => true],
            ['label' => 'Status', 'sortable' => true]
        ],
        'actionBtns' => $actionBtns,
        'rows' => $rows
    ]) ?>
    <?php endif; ?>
  </div>
</div>

<script>
// ... (mesmo JavaScript atual)
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
```

---

## 📊 COMPARAÇÃO

| Aspecto | Atual | Proposto |
|---------|-------|----------|
| **Componentes** | 2/6 | 6/6 |
| **Cards Stats** | 0 | 4 |
| **Badges** | HTML inline | renderBadge() |
| **Buttons** | HTML inline | renderButton() |
| **Card Container** | HTML manual | renderCard() (opcional) |
| **Métricas** | Nenhuma | 4 métricas |
| **UX** | Básico | Profissional |

---

## 🚀 BENEFÍCIOS DA REFATORAÇÃO

✅ **Consistência Visual**: Usa mesmos componentes dos dashboards  
✅ **Métricas Visuais**: 4 cards stats com dados em tempo real  
✅ **Código Limpo**: PHP functions ao invés de HTML inline  
✅ **Manutenção Fácil**: Alterações no Design System afetam tudo  
✅ **Badges Dinâmicos**: Cores automáticas por role/status  
✅ **Profissional**: Layout moderno e responsivo  

---

## ⏱️ ESTIMATIVA

| Tarefa | Tempo |
|--------|-------|
| Substituir badges inline por renderBadge() | 15 min |
| Adicionar renderButton() no botão "Novo" | 5 min |
| Adicionar 4 renderCardStat() para métricas | 20 min |
| Testar funcionalidade | 10 min |
| **TOTAL** | **50 minutos** |

---

## ✅ CONCLUSÃO

**Status:** ⚠️ **FUNCIONANDO MAS PODE MELHORAR**

**Recomendação:** ✅ **REFATORAR PARA USAR COMPONENTES**

**Justificativa:**
- Página está funcionando corretamente
- Controller e rotas OK
- Segurança implementada (RBAC + CSRF)
- **Mas não usa componentes do Design System 3.0 adequadamente**
- Sem métricas visuais para o administrador
- HTML inline ao invés de componentes reutilizáveis

**Próximo Passo:** Aguardar aprovação para refatorar 🚀

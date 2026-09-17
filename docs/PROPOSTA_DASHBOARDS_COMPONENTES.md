# Proposta: Reconstrução dos Dashboards com Componentes Design System 3.0

**Data:** 27/04/2026  
**Status:** 📋 PROPOSTA PARA ANÁLISE  
**Objetivo:** Padronizar dashboards usando componentes existentes do Design System 3.0

---

## 📊 COMPONENTES DISPONÍVEIS

### ✅ Já Existentes e Testados

| Componente | Função PHP | Uso nos Dashboards |
|------------|------------|-------------------|
| **Card Stat** | `renderCardStat()` | ✅ Métricas principais (cards coloridos) |
| **Card** | `renderCard()` | ✅ Containers de seções |
| **Alert** | `renderAlert()` | ⚠️ Alertas críticos |
| **Badge** | `renderBadge()` | ✅ Status (pendente, concluído, etc.) |
| **Table** | `renderTable()` | ✅ Listagens (eventos, contas, produtos) |
| **Timeline** | `renderTimeline()` | ✅ Atividades recentes |
| **Progress** | `renderProgress()` | 📊 Barras de progresso |
| **Button** | `renderButton()` | ✅ Ações rápidas |
| **Tabs** | `renderTabsColor()` | 🔄 Abas por período |

---

## 🎯 PROPOSTA POR DASHBOARD

### 1️⃣ DASHBOARD ADMINISTRADOR

#### Layout Atual (PROBLEMAS):
```
❌ HTML inline para cards de estatísticas
❌ Tabelas construídas manualmente
❌ Sem uso de componentes de alerta
❌ Timeline com dados hardcoded
❌ Sem filtros de período
```

#### Layout Proposto (SOLUÇÃO):

```php
<?php
// Componentes necessários
require_once 'components/card/card.php';
require_once 'components/alert/alert.php';
require_once 'components/badge/badge.php';
require_once 'components/table/table.php';
require_once 'components/timeline/timeline.php';
require_once 'components/button/button.php';
require_once 'components/tabs/tabs.php';
?>

<!-- SEÇÃO 1: FILTROS DE PERÍODO (NOVO) -->
<?php echo renderTabsColor([
    'id' => 'periodo-filter',
    'tabs' => [
        ['label' => 'Hoje', 'active' => true],
        ['label' => 'Semana', 'onclick' => 'filtrarPeriodo("semana")'],
        ['label' => 'Mês', 'onclick' => 'filtrarPeriodo("mes")'],
    ]
]); ?>

<!-- SEÇÃO 2: MÉTRICAS PRINCIPAIS (4 cards) -->
<div class="col4">
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['eventos_hoje'],
        'label' => 'Eventos Hoje',
        'color' => 'var(--neon-cyan)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['contas_vencidas'],
        'label' => 'Contas Vencidas',
        'color' => $stats['contas_vencidas'] > 0 ? 'var(--neon-red)' : 'var(--neon-green)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => 'R$ ' . number_format($stats['total_pago_mes'], 2, ',', '.'),
        'label' => 'Pago no Mês',
        'color' => 'var(--neon-green)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['cotacoes_abertas'],
        'label' => 'Cotações Abertas',
        'color' => 'var(--neon-yellow)'
    ]); ?>
</div>

<!-- SEÇÃO 3: ALERTAS CRÍTICOS (NOVO - usando renderAlert) -->
<?php if ($stats['contas_vencidas'] > 0): ?>
<div class="card" style="margin-top:16px">
    <div class="card-body">
        <?php echo renderAlert([
            'variant' => 'red',
            'title' => '⚠️ Contas Vencidas',
            'message' => "Existem {$stats['contas_vencidas']} conta(s) vencida(s) pendente(s) de pagamento.",
            'dismissible' => true
        ]); ?>
    </div>
</div>
<?php endif; ?>

<?php if ($stats['montagens_pendentes'] > 0): ?>
<div class="card" style="margin-top:16px">
    <div class="card-body">
        <?php echo renderAlert([
            'variant' => 'yellow',
            'title' => '🏗️ Montagens Pendentes',
            'message' => "{$stats['montagens_pendentes']} montagem(ns) aguardando execução.",
            'dismissible' => true
        ]); ?>
    </div>
</div>
<?php endif; ?>

<!-- SEÇÃO 4: PRÓXIMOS EVENTOS (usando renderTable) -->
<div class="col2" style="margin-top:16px">
    <div class="card">
        <div class="card-head">
            <span class="card-title">📅 Próximos Eventos</span>
            <?php echo renderButton([
                'label' => 'Ver Todos',
                'variant' => 'cyan',
                'size' => 'sm',
                'onclick' => "window.location.href='/eventos'"
            ]); ?>
        </div>
        <div class="card-body" style="padding:0">
            <?php
            $rows = [];
            foreach ($proximosEventos as $evento) {
                $statusBadge = renderBadge([
                    'label' => ucfirst(str_replace('_', ' ', $evento['status'])),
                    'variant' => match($evento['status']) {
                        'em_andamento' => 'cyan',
                        'concluido' => 'green',
                        default => 'yellow'
                    },
                    'size' => 'sm'
                ]);
                
                $rows[] = [
                    htmlspecialchars($evento['nome']),
                    date('d/m/Y', strtotime($evento['data_inicio'])),
                    ['html' => true, 'content' => $statusBadge]
                ];
            }
            
            echo renderTable([
                'id' => 'tbl-eventos',
                'headers' => [['label' => 'Evento'], ['label' => 'Início'], ['label' => 'Status']],
                'rows' => $rows,
                'searchable' => false,
                'paginated' => false
            ]);
            ?>
        </div>
    </div>
    
    <!-- SEÇÃO 5: CONTAS PRÓXIMO VENCIMENTO -->
    <div class="card">
        <div class="card-head">
            <span class="card-title">💰 Contas Próximo Vencimento</span>
        </div>
        <div class="card-body" style="padding:0">
            <?php
            $rows = [];
            foreach ($contasProxVencimento as $conta) {
                $statusBadge = renderBadge([
                    'label' => ucfirst($conta['status']),
                    'variant' => match($conta['status']) {
                        'pago' => 'green',
                        'atrasado' => 'red',
                        default => 'cyan'
                    },
                    'size' => 'sm'
                ]);
                
                $rows[] = [
                    htmlspecialchars(substr($conta['descricao'], 0, 30)),
                    'R$ ' . number_format($conta['valor'], 2, ',', '.'),
                    date('d/m/Y', strtotime($conta['data_vencimento'])),
                    ['html' => true, 'content' => $statusBadge]
                ];
            }
            
            echo renderTable([
                'id' => 'tbl-contas',
                'headers' => [
                    ['label' => 'Descrição'],
                    ['label' => 'Valor'],
                    ['label' => 'Vencimento'],
                    ['label' => 'Status']
                ],
                'rows' => $rows
            ]);
            ?>
        </div>
    </div>
</div>

<!-- SEÇÃO 6: ATIVIDADES RECENTES (usando renderTimeline) -->
<div class="card" style="margin-top:16px">
    <div class="card-head">
        <span class="card-title">📊 Atividades Recentes</span>
    </div>
    <div class="card-body">
        <?php
        $timelineItems = [];
        foreach ($atividadesRecentes as $activity) {
            $timelineItems[] = [
                'time' => $activity['tempo'],
                'title' => $activity['descricao'],
                'color' => match($activity['tipo']) {
                    'create' => 'green',
                    'update' => 'cyan',
                    default => 'cyan'
                }
            ];
        }
        
        echo renderTimeline(['items' => $timelineItems]);
        ?>
    </div>
</div>

<!-- SEÇÃO 7: AÇÕES RÁPIDAS (NOVO - usando renderButton) -->
<div class="card" style="margin-top:16px">
    <div class="card-head">
        <span class="card-title">⚡ Ações Rápidas</span>
    </div>
    <div class="card-body">
        <div class="btn-row">
            <?php echo renderButton([
                'label' => 'Novo Evento',
                'variant' => 'cyan',
                'size' => 'sm',
                'onclick' => "window.location.href='/eventos/create'"
            ]); ?>
            
            <?php echo renderButton([
                'label' => 'Nova Cotação',
                'variant' => 'green',
                'size' => 'sm',
                'onclick' => "window.location.href='/contas-pagar/create'"
            ]); ?>
            
            <?php echo renderButton([
                'label' => 'Ver Colaboradores',
                'variant' => 'purple',
                'size' => 'sm',
                'onclick' => "window.location.href='/colaboradores'"
            ]); ?>
        </div>
    </div>
</div>
```

---

### 2️⃣ DASHBOARD PRODUTOR

#### Melhorias Propostas:

```php
<!-- SEÇÃO 1: MÉTRICAS DE OPERAÇÃO -->
<div class="col4">
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['eventos_hoje'],
        'label' => 'Eventos Hoje',
        'color' => 'var(--neon-cyan)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['montagens_pendentes'],
        'label' => 'Montagens Pendentes',
        'color' => 'var(--neon-orange)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['devolucoes_pendentes'],
        'label' => 'Devoluções Pendentes',
        'color' => 'var(--neon-pink)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['colaboradores_alocados_hoje'],
        'label' => 'Colaboradores Hoje',
        'color' => 'var(--neon-purple)'
    ]); ?>
</div>

<!-- SEÇÃO 2: ALERTAS DE OPERAÇÃO -->
<?php if ($stats['montagens_pendentes'] > 0): ?>
<div class="card" style="margin-top:16px">
    <div class="card-body">
        <?php echo renderAlert([
            'variant' => 'yellow',
            'title' => '🏗️ Montagens Pendentes',
            'message' => "Você tem {$stats['montagens_pendentes']} montagem(s) para executar.",
            'dismissible' => true
        ]); ?>
    </div>
</div>
<?php endif; ?>

<!-- SEÇÃO 3: OPERAÇÕES PENDENTES (usando renderTable + renderBadge) -->
<div class="col2" style="margin-top:16px">
    <div class="card">
        <div class="card-head">
            <span class="card-title">🏗️ Montagens Pendentes</span>
        </div>
        <div class="card-body" style="padding:0">
            <?php
            $rows = [];
            foreach ($montagensPendentes as $montagem) {
                $rows[] = [
                    htmlspecialchars($montagem['evento_nome']),
                    ['html' => true, 'content' => renderBadge([
                        'label' => 'Pendente',
                        'variant' => 'yellow',
                        'size' => 'sm'
                    ])]
                ];
            }
            
            echo renderTable([
                'id' => 'tbl-montagens',
                'headers' => [['label' => 'Evento'], ['label' => 'Status']],
                'rows' => $rows
            ]);
            ?>
        </div>
    </div>
    
    <!-- SEÇÃO 4: AÇÕES RÁPIDAS DO PRODUTOR -->
    <div class="card">
        <div class="card-body">
            <div class="btn-row">
                <?php echo renderButton([
                    'label' => 'Registrar Montagem',
                    'variant' => 'cyan',
                    'size' => 'sm'
                ]); ?>
                
                <?php echo renderButton([
                    'label' => 'Registrar Devolução',
                    'variant' => 'green',
                    'size' => 'sm'
                ]); ?>
            </div>
        </div>
    </div>
</div>
```

---

### 3️⃣ DASHBOARD ESTOQUISTA

#### Melhorias Propostas:

```php
<!-- SEÇÃO 1: MÉTRICAS DE ESTOQUE -->
<div class="col4">
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['total_produtos'],
        'label' => 'Total Produtos',
        'color' => 'var(--neon-cyan)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['total_seriais'],
        'label' => 'Total Seriais',
        'color' => 'var(--neon-green)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['seriais_em_evento'],
        'label' => 'Seriais em Evento',
        'color' => 'var(--neon-yellow)'
    ]); ?>
    
    <?php echo renderCardStat([
        'icon' => '<svg>...</svg>',
        'value' => $stats['total_salas'],
        'label' => 'Total Salas',
        'color' => 'var(--neon-purple)'
    ]); ?>
</div>

<!-- SEÇÃO 2: DISTRIBUIÇÃO POR SEÇÃO (usando renderProgress) -->
<div class="card" style="margin-top:16px">
    <div class="card-head">
        <span class="card-title">📦 Produtos por Seção</span>
    </div>
    <div class="card-body">
        <?php
        $totalProdutos = array_sum(array_column($produtosPorSecao, 'total_produtos'));
        $progressItems = [];
        
        foreach ($produtosPorSecao as $secao) {
            $percent = $totalProdutos > 0 ? ($secao['total_produtos'] / $totalProdutos * 100) : 0;
            $progressItems[] = [
                'label' => htmlspecialchars($secao['nome']),
                'value' => "{$secao['total_produtos']} produtos",
                'percent' => round($percent),
                'variant' => 'cyan'
            ];
        }
        
        echo renderProgressGroup($progressItems);
        ?>
    </div>
</div>

<!-- SEÇÃO 3: ÚLTIMOS PRODUTOS (usando renderTable) -->
<div class="col2" style="margin-top:16px">
    <div class="card">
        <div class="card-head">
            <span class="card-title">🆕 Últimos Produtos</span>
        </div>
        <div class="card-body" style="padding:0">
            <?php
            $rows = [];
            foreach ($ultimosProdutos as $produto) {
                $rows[] = [
                    htmlspecialchars($produto['nome']),
                    htmlspecialchars($produto['secao_nome'] ?? '-'),
                    'R$ ' . number_format($produto['custo'], 2, ',', '.')
                ];
            }
            
            echo renderTable([
                'id' => 'tbl-produtos',
                'headers' => [
                    ['label' => 'Produto'],
                    ['label' => 'Seção'],
                    ['label' => 'Custo']
                ],
                'rows' => $rows
            ]);
            ?>
        </div>
    </div>
    
    <!-- SEÇÃO 4: SALAS COM PRODUTOS -->
    <div class="card">
        <div class="card-head">
            <span class="card-title">🏢 Salas</span>
        </div>
        <div class="card-body" style="padding:0">
            <?php
            $rows = [];
            foreach ($salasComProdutos as $sala) {
                $rows[] = [
                    htmlspecialchars($sala['nome']),
                    $sala['total_produtos']
                ];
            }
            
            echo renderTable([
                'id' => 'tbl-salas',
                'headers' => [['label' => 'Sala'], ['label' => 'Produtos']],
                'rows' => $rows
            ]);
            ?>
        </div>
    </div>
</div>

<!-- SEÇÃO 5: AÇÕES RÁPIDAS -->
<div class="card" style="margin-top:16px">
    <div class="card-body">
        <div class="btn-row">
            <?php echo renderButton([
                'label' => 'Novo Produto',
                'variant' => 'cyan',
                'size' => 'sm',
                'onclick' => "window.location.href='/estoque/create'"
            ]); ?>
            
            <?php echo renderButton([
                'label' => 'Gerenciar Seriais',
                'variant' => 'green',
                'size' => 'sm'
            ]); ?>
        </div>
    </div>
</div>
```

---

## 📋 BENEFÍCIOS DA PROPOSTA

### ✅ Vantagens

1. **Consistência Visual**: Todos os dashboards usam os mesmos componentes
2. **Manutenção Fácil**: Alterações no CSS afetam todos os dashboards
3. **Código Limpo**: PHP functions ao invés de HTML inline
4. **Reutilização**: Componentes testados e aprovados
5. **Responsividade**: Grid system já implementado (col2, col3, col4)
6. **Acessibilidade**: Componentes seguem padrões do Design System

### 📊 Componentes Utilizados

| Componente | Admin | Produtor | Estoquista |
|------------|-------|----------|------------|
| renderCardStat() | ✅ 8x | ✅ 4x | ✅ 4x |
| renderCard() | ✅ 5x | ✅ 4x | ✅ 4x |
| renderTable() | ✅ 2x | ✅ 2x | ✅ 2x |
| renderAlert() | ✅ 2x | ✅ 1x | ❌ |
| renderBadge() | ✅ 6x | ✅ 2x | ❌ |
| renderTimeline() | ✅ 1x | ❌ | ❌ |
| renderProgress() | ❌ | ❌ | ✅ 1x |
| renderButton() | ✅ 3x | ✅ 2x | ✅ 2x |
| renderTabsColor() | ✅ 1x | ❌ | ❌ |

---

## 🎨 EXEMPLO VISUAL PROPOSTO

### Dashboard Administrador (Layout Proposto):

```
┌─────────────────────────────────────────────────────────┐
│ [Hoje] [Semana] [Mês]                                   │ ← renderTabsColor
├─────────────┬─────────────┬─────────────┬───────────────┤
│ 📅 Eventos  │ ⚠️ Contas   │ 💰 Pago     │ 📊 Cotações   │
│    Hoje     │  Vencidas   │   no Mês    │   Abertas     │
│      3      │      2      │ R$ 15.450   │      5        │ ← renderCardStat
├─────────────┴─────────────┴─────────────┴───────────────┤
│ ⚠️ Alerta: 2 contas vencidas aguardando pagamento       │ ← renderAlert (red)
├────────────────────────────┬────────────────────────────┤
│ 📅 Próximos Eventos        │ 💰 Contas Vencimento       │
│ ┌──────────────────────┐   │ ┌──────────────────────┐   │
│ │ Evento A | 28/04 | P  │   │ │ Conta X | R$ 500   │   │
│ │ Evento B | 29/04 | E  │   │ │ Conta Y | R$ 300   │   │ ← renderTable
│ └──────────────────────┘   │ └──────────────────────┘   │
├────────────────────────────┴────────────────────────────┤
│ 📊 Atividades Recentes                                   │
│ • Cotação criada - R$ 150,00 - 2 horas atrás            │ ← renderTimeline
│ • Conta paga - R$ 500,00 - 5 horas atrás                │
├─────────────────────────────────────────────────────────┤
│ ⚡ Ações Rápidas: [Novo Evento] [Nova Cotação] [Colab]  │ ← renderButton
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 PLANO DE IMPLEMENTAÇÃO

### Fase 1: Administrador (4-6 horas)
1. Substituir HTML inline por componentes
2. Adicionar alertas críticos
3. Adicionar ações rápidas
4. Adicionar filtro de período (tabs)

### Fase 2: Produtor (2-3 horas)
1. Reconstruir com componentes
2. Adicionar alertas de operação
3. Melhorar tabela de montagens

### Fase 3: Estoquista (2-3 horas)
1. Reconstruir com componentes
2. Adicionar barras de progresso
3. Melhorar visualização de salas

### Total Estimado: 8-12 horas

---

## 📝 CONCLUSÃO

**Recomendação:** ✅ **APROVADO PARA IMPLEMENTAÇÃO**

**Justificativa:**
- Usa componentes já testados e aprovados
- Melhora significativamente a UX
- Mantém consistência visual em todo o sistema
- Fácil manutenção futura
- Segue padrões do Design System 3.0

**Próximo Passo:** Aguardar aprovação para iniciar implementação.

<?php
/**
 * Dashboard Administrador - Baseado no demo.php
 */

// Carregar componentes
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/timeline/timeline.php';

use App\Auth\Rbac;

// Incluir layout
require_once __DIR__ . '/../layout/header.php';
?>

<!-- Section Header -->
<div class="section-header">
  <div class="section-icon">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
  </div>
  <div>
    <div class="section-title">Dashboard Administrador</div>
    <div class="section-sub">Bem-vindo, <?= htmlspecialchars(Rbac::getUser()['name'] ?? 'Admin') ?>! Visão geral do sistema.</div>
  </div>
</div>
<div class="divider"></div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px" class="dashboard-stats-grid">
  <div class="card-stat" style="--stat-color:var(--neon-cyan)">
    <div class="card-stat-val" style="color:var(--neon-cyan)"><?= $stats['eventos_hoje'] ?? 0 ?></div>
    <div class="card-stat-lbl">Eventos Hoje</div>
  </div>
  <div class="card-stat" style="--stat-color:<?= ($stats['contas_vencidas'] ?? 0) > 0 ? 'var(--neon-red)' : 'var(--neon-green)' ?>">
    <div class="card-stat-val" style="color:<?= ($stats['contas_vencidas'] ?? 0) > 0 ? 'var(--neon-red)' : 'var(--neon-green)' ?>"><?= $stats['contas_vencidas'] ?? 0 ?></div>
    <div class="card-stat-lbl">Contas Vencidas</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-green)">
    <div class="card-stat-val" style="color:var(--neon-green)">R$ <?= number_format($stats['total_pago_mes'] ?? 0, 2, ',', '.') ?></div>
    <div class="card-stat-lbl">Pago no Mês</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-yellow)">
    <div class="card-stat-val" style="color:var(--neon-yellow)"><?= $stats['cotacoes_abertas'] ?? 0 ?></div>
    <div class="card-stat-lbl">Cotações Abertas</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="dashboard-stats-grid">
  <div class="card-stat" style="--stat-color:var(--neon-purple)">
    <div class="card-stat-val" style="color:var(--neon-purple)"><?= $stats['colaboradores_ativos'] ?? 0 ?></div>
    <div class="card-stat-lbl">Colaboradores Ativos</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-blue)">
    <div class="card-stat-val" style="color:var(--neon-blue)"><?= $stats['colaboradores_alocados_hoje'] ?? 0 ?></div>
    <div class="card-stat-lbl">Alocados Hoje</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-orange)">
    <div class="card-stat-val" style="color:var(--neon-orange)"><?= $stats['montagens_pendentes'] ?? 0 ?></div>
    <div class="card-stat-lbl">Montagens Pendentes</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-pink)">
    <div class="card-stat-val" style="color:var(--neon-pink)"><?= $stats['devolucoes_pendentes'] ?? 0 ?></div>
    <div class="card-stat-lbl">Devoluções Pendentes</div>
  </div>
</div>

<style>
  @media (max-width:1024px){.dashboard-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.dashboard-stats-grid{grid-template-columns:1fr!important}}
</style>

<!-- Próximos Eventos e Contas Próximo Vencimento -->
<div class="col2" style="margin-top:16px">
  <!-- Próximos Eventos -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Proximos Eventos</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $eventoRows = [];
      foreach ($proximosEventos ?? [] as $evento) {
        $statusConfig = match($evento['status'] ?? 'planejado') {
          'em_andamento' => ['variant' => 'cyan', 'label' => 'Em Andamento'],
          'concluido' => ['variant' => 'green', 'label' => 'Concluído'],
          'cancelado' => ['variant' => 'red', 'label' => 'Cancelado'],
          default => ['variant' => 'yellow', 'label' => 'Planejado']
        };
        $eventoRows[] = [
          htmlspecialchars($evento['nome']),
          date('d/m/Y', strtotime($evento['data_inicio'])),
          ['html' => true, 'content' => renderBadge(['label' => $statusConfig['label'], 'variant' => $statusConfig['variant'], 'size' => 'sm'])]
        ];
      }
      
      if (empty($eventoRows)) {
        $eventoRows = [['<em style="color:var(--text-3)">Nenhum evento próximo</em>', '', '']];
      }
      
      echo renderTable([
        'id' => 'tbl-eventos',
        'headers' => [
          ['label' => 'Evento'],
          ['label' => 'Início'],
          ['label' => 'Status'],
        ],
        'rows' => $eventoRows
      ]);
      ?>
    </div>
  </div>

  <!-- Contas Próximo Vencimento -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Contas Proximo Vencimento</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $contaRows = [];
      foreach ($contasProxVencimento ?? [] as $conta) {
        $statusConfig = match($conta['status'] ?? 'pendente') {
          'pago' => ['variant' => 'green', 'label' => 'Pago'],
          'atrasado' => ['variant' => 'red', 'label' => 'Atrasado'],
          'parcial' => ['variant' => 'yellow', 'label' => 'Parcial'],
          default => ['variant' => 'cyan', 'label' => 'Pendente']
        };
        $contaRows[] = [
          htmlspecialchars(substr($conta['descricao'] ?? 'Conta', 0, 30)),
          'R$ ' . number_format($conta['valor'] ?? 0, 2, ',', '.'),
          date('d/m/Y', strtotime($conta['data_vencimento'])),
          ['html' => true, 'content' => renderBadge(['label' => $statusConfig['label'], 'variant' => $statusConfig['variant'], 'size' => 'sm'])]
        ];
      }
      
      if (empty($contaRows)) {
        $contaRows = [['<em style="color:var(--text-3)">Nenhuma conta pendente</em>', '', '', '']];
      }
      
      echo renderTable([
        'id' => 'tbl-contas',
        'headers' => [
          ['label' => 'Descrição'],
          ['label' => 'Valor'],
          ['label' => 'Vencimento'],
          ['label' => 'Status'],
        ],
        'rows' => $contaRows
      ]);
      ?>
    </div>
  </div>
</div>

<!-- Atividades Recentes -->
<div class="card mt-4">
  <div class="card-head">
    <span class="card-title">Atividades Recentes</span>
  </div>
  <div class="card-body" style="padding:0; overflow:hidden; height:420px; position:relative;">
    <iframe src="https://docs.google.com/spreadsheets/d/e/2PACX-1vRVWRxIqJYc8G_jyDktjos5kTMb67eRnvSPvLLq8D4mgWiQkGCF82Dc6RziRs3BCg/pubhtml?widget=true&amp;headers=false" style="position:absolute; top:0; left:0; width:128%; height:128%; border:none; transform:scale(0.78); transform-origin:0 0;"></iframe>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

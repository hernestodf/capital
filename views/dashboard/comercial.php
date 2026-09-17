<?php
/**
 * Dashboard Comercial
 *
 * @var array $stats
 * @var array $producoes
 * @var array $produtos
 */

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';

use App\Auth\Rbac;

require_once __DIR__ . '/../layout/header.php';
?>

<!-- Section Header -->
<div class="section-header">
  <div class="section-icon">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
  </div>
  <div>
    <div class="section-title">Dashboard Comercial</div>
    <div class="section-sub">Bem-vindo, <?= htmlspecialchars(Rbac::getUser()['name'] ?? 'Comercial') ?>! Gestão comercial.</div>
  </div>
</div>
<div class="divider"></div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px" class="dashboard-stats-grid">
  <div class="card-stat" style="--stat-color:var(--neon-cyan)">
    <div class="card-stat-val" style="color:var(--neon-cyan)"><?= $stats['producoes_hoje'] ?? 0 ?></div>
    <div class="card-stat-lbl">Produções Hoje</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-purple)">
    <div class="card-stat-val" style="color:var(--neon-purple)"><?= $stats['produtos_ativos'] ?? 0 ?></div>
    <div class="card-stat-lbl">Produtos Ativos</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-green)">
    <div class="card-stat-val" style="color:var(--neon-green)"><?= $stats['concluidas'] ?? 0 ?></div>
    <div class="card-stat-lbl">Concluídas</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-yellow)">
    <div class="card-stat-val" style="color:var(--neon-yellow)"><?= $stats['em_andamento'] ?? 0 ?></div>
    <div class="card-stat-lbl">Em Andamento</div>
  </div>
</div>

<style>
  @media (max-width:1024px){.dashboard-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.dashboard-stats-grid{grid-template-columns:1fr!important}}
</style>

<!-- Main Content -->
<div class="col2" style="margin-top:16px">
  <!-- Produções Recentes -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Produções Recentes</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $prodRows = [];
      foreach ($producoes ?? [] as $producao) {
        $statusConfig = match($producao['status'] ?? 'pendente') {
          'concluida' => ['variant' => 'green', 'label' => 'Concluída'],
          'em_andamento' => ['variant' => 'cyan', 'label' => 'Em Andamento'],
          'pausada' => ['variant' => 'yellow', 'label' => 'Pausada'],
          'cancelada' => ['variant' => 'red', 'label' => 'Cancelada'],
          default => ['variant' => 'gray', 'label' => 'Pendente']
        };
        $prodRows[] = [
          htmlspecialchars($producao['nome'] ?? 'Produção #' . $producao['id']),
          htmlspecialchars($producao['produto_nome'] ?? ''),
          $producao['quantidade'] ?? 0,
          ['html' => true, 'content' => renderBadge(['label' => $statusConfig['label'], 'variant' => $statusConfig['variant'], 'size' => 'sm'])]
        ];
      }

      if (empty($prodRows)) {
        $prodRows = [['<em style="color:var(--text-3)">Nenhuma produção registrada</em>', '', '', '']];
      }

      echo renderTable([
        'id' => 'tbl-producoes',
        'headers' => [
          ['label' => 'Nome'],
          ['label' => 'Produto'],
          ['label' => 'Qtd'],
          ['label' => 'Status'],
        ],
        'rows' => $prodRows
      ]);
      ?>
    </div>
  </div>

  <!-- Produtos em Estoque -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Produtos em Estoque</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $estRows = [];
      foreach ($produtos ?? [] as $produto) {
        $estoque = $produto['quantidade'] ?? 0;
        $minimo = $produto['estoque_minimo'] ?? 10;
        $cor = $estoque <= $minimo ? 'red' : ($estoque <= $minimo * 2 ? 'yellow' : 'green');
        $estRows[] = [
          htmlspecialchars($produto['nome']),
          $estoque,
          ['html' => true, 'content' => renderBadge(['label' => $estoque <= $minimo ? 'Baixo' : 'OK', 'variant' => $cor, 'size' => 'sm'])]
        ];
      }

      if (empty($estRows)) {
        $estRows = [['<em style="color:var(--text-3)">Nenhum produto cadastrado</em>', '', '']];
      }

      echo renderTable([
        'id' => 'tbl-estoque',
        'headers' => [
          ['label' => 'Produto'],
          ['label' => 'Estoque'],
          ['label' => 'Status'],
        ],
        'rows' => $estRows
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

<!-- Ações Rápidas -->
<div class="card" style="margin-top:16px">
  <div class="card-head">
    <span class="card-title">Ações Rápidas</span>
  </div>
  <div class="card-body">
    <div class="btn-row">
      <?php echo renderButton(['label' => 'Novo Evento', 'variant' => 'cyan', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/eventos/create'"]); ?>
      <?php echo renderButton(['label' => 'Ver Estoque', 'variant' => 'purple', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/estoque'"]); ?>
      <?php echo renderButton(['label' => 'Consultar Alocação', 'variant' => 'green', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/estoque'"]); ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

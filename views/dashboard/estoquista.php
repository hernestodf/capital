<?php
/**
 * Dashboard Estoquista - Baseado no demo.php
 *
 * @var array $stats
 * @var array $produtosPorSecao
 * @var array $ultimosProdutos
 * @var array $salasComProdutos
 */

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/progress/progress.php';

use App\Auth\Rbac;

require_once __DIR__ . '/../layout/header.php';
?>

<!-- Section Header -->
<div class="section-header">
  <div class="section-icon">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
  </div>
  <div>
    <div class="section-title">Dashboard Estoquista</div>
    <div class="section-sub">Bem-vindo, <?= htmlspecialchars(Rbac::getUser()['name'] ?? 'Estoquista') ?>! Gestão de estoque.</div>
  </div>
</div>
<div class="divider"></div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px" class="dashboard-stats-grid">
  <div class="card-stat" style="--stat-color:var(--neon-cyan)">
    <div class="card-stat-val" style="color:var(--neon-cyan)"><?= $stats['total_produtos'] ?? 0 ?></div>
    <div class="card-stat-lbl">Produtos</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-green)">
    <div class="card-stat-val" style="color:var(--neon-green)"><?= $stats['produtos_ativos'] ?? 0 ?></div>
    <div class="card-stat-lbl">Produtos Ativos</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-yellow)">
    <div class="card-stat-val" style="color:var(--neon-yellow)"><?= $stats['total_seriais'] ?? 0 ?></div>
    <div class="card-stat-lbl">Seriais</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--neon-orange)">
    <div class="card-stat-val" style="color:var(--neon-orange)"><?= $stats['seriais_em_evento'] ?? 0 ?></div>
    <div class="card-stat-lbl">Seriais em Evento</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(1,1fr);gap:16px" class="dashboard-stats-grid">
  <div class="card-stat" style="--stat-color:var(--neon-purple)">
    <div class="card-stat-val" style="color:var(--neon-purple)"><?= $stats['total_salas'] ?? 0 ?></div>
    <div class="card-stat-lbl">Salas</div>
  </div>
</div>

<style>
  @media (max-width:1024px){.dashboard-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.dashboard-stats-grid{grid-template-columns:1fr!important}}
</style>

<!-- Produtos por Seção -->
<?php if (!empty($produtosPorSecao)): ?>
<div class="card" style="margin-top:16px">
  <div class="card-head">
    <span class="card-title">Produtos por Secao</span>
  </div>
  <div class="card-body">
    <?php
    $totalProdutos = array_sum(array_column($produtosPorSecao, 'total_produtos'));
    $progressItems = [];
    $colors = ['cyan', 'green', 'yellow', 'purple', 'blue', 'pink'];
    
    foreach ($produtosPorSecao as $index => $secao) {
      $percent = $totalProdutos > 0 ? ($secao['total_produtos'] / $totalProdutos * 100) : 0;
      $progressItems[] = [
        'label' => htmlspecialchars($secao['nome']),
        'value' => "{$secao['total_produtos']} produtos (" . round($percent) . "%)",
        'percent' => round($percent),
        'variant' => $colors[$index % count($colors)]
      ];
    }
    
    echo renderProgressGroup($progressItems);
    ?>
  </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="col2" style="margin-top:16px">
  <!-- Últimos Produtos Cadastrados -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Ultimos Produtos</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $prodRows = [];
      foreach ($ultimosProdutos ?? [] as $produto) {
        $prodRows[] = [
          htmlspecialchars($produto['nome'] ?? 'Produto'),
          htmlspecialchars($produto['secao_nome'] ?? '-'),
          'R$ ' . number_format($produto['custo'] ?? 0, 2, ',', '.')
        ];
      }

      if (empty($prodRows)) {
        $prodRows = [['<em style="color:var(--text-3)">Nenhum produto cadastrado</em>', '', '']];
      }

      echo renderTable([
        'id' => 'tbl-produtos',
        'headers' => [
          ['label' => 'Produto'],
          ['label' => 'Seção'],
          ['label' => 'Custo'],
        ],
        'rows' => $prodRows
      ]);
      ?>
    </div>
  </div>

  <!-- Salas com Produtos -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Salas</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
      $salaRows = [];
      foreach ($salasComProdutos ?? [] as $sala) {
        $salaRows[] = [
          htmlspecialchars($sala['nome'] ?? 'Sala'),
          htmlspecialchars($sala['secao_nome'] ?? '-'),
          $sala['total_produtos'] ?? 0
        ];
      }

      if (empty($salaRows)) {
        $salaRows = [['<em style="color:var(--text-3)">Nenhuma sala cadastrada</em>', '', '']];
      }

      echo renderTable([
        'id' => 'tbl-salas',
        'headers' => [
          ['label' => 'Sala'],
          ['label' => 'Seção'],
          ['label' => 'Produtos'],
        ],
        'rows' => $salaRows
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
      <?php echo renderButton(['label' => 'Novo Produto', 'variant' => 'cyan', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/estoque/create'"]); ?>
      <?php echo renderButton(['label' => 'Seriais', 'variant' => 'green', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/estoque'"]); ?>
      <?php echo renderButton(['label' => 'Salas', 'variant' => 'purple', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/salas'"]); ?>
      <?php echo renderButton(['label' => 'Seções', 'variant' => 'yellow', 'size' => 'sm', 'onclick' => "window.location.href='{$baseUrl}/secoes'"]); ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

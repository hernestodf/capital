<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/accordion/accordion.php';

// Opcoes de filtro
$fornecedorOptions = '';
if (!empty($fornecedores)) {
    foreach ($fornecedores as $f) {
        $sel = (isset($filters['id_fornecedor']) && $filters['id_fornecedor'] == $f['id']) ? 'selected' : '';
        $fornecedorOptions .= '<option value="' . $f['id'] . '" ' . $sel . '>' . htmlspecialchars($f['nome_fantasia'] ?? $f['razao_social'] ?? '-') . '</option>';
    }
}
$colaboradorOptions = '<option value="">Todos</option>';
if (!empty($colaboradores)) {
    foreach ($colaboradores as $c) {
        $sel = (isset($filters['id_colaborador']) && $filters['id_colaborador'] == $c['id']) ? 'selected' : '';
        $colaboradorOptions .= '<option value="' . $c['id'] . '" ' . $sel . '>' . htmlspecialchars($c['nome']) . '</option>';
    }
}
$eventoOptions = '<option value="">Todos</option>';
if (!empty($eventos)) {
    foreach ($eventos as $e) {
        $sel = (isset($filters['evento_id']) && $filters['evento_id'] == $e['id']) ? 'selected' : '';
        $eventoOptions .= '<option value="' . $e['id'] . '" ' . $sel . '>' . htmlspecialchars($e['nome']) . '</option>';
    }
}
$statusOptions = '<option value="">Todos</option>';
foreach (['PENDENTE' => 'Pendente', 'PAGO' => 'Pago', 'VENCIDO' => 'Vencido', 'PARCIAL' => 'Parcial'] as $k => $l) {
    $sel = (isset($filters['status']) && $filters['status'] === $k) ? 'selected' : '';
    $statusOptions .= '<option value="' . $k . '" ' . $sel . '>' . $l . '</option>';
}
$tipoOptions = '<option value="">Todos</option>';
foreach (['fornecedor' => 'Fornecedor', 'colaborador' => 'Colaborador', 'outro' => 'Outro'] as $k => $l) {
    $sel = (isset($filters['tipo']) && $filters['tipo'] === $k) ? 'selected' : '';
    $tipoOptions .= '<option value="' . $k . '" ' . $sel . '>' . $l . '</option>';
}

// Totais
$totalPendente = $totalPago = $totalVencido = $totalParcial = 0;
$valorPendente = $valorPago = $valorVencido = 0;
if (!empty($totais)) {
    foreach ($totais as $t) {
        switch ($t['status']) {
            case 'PENDENTE': $totalPendente = (int)$t['quantidade']; $valorPendente = (float)$t['total_valor']; break;
            case 'PAGO':     $totalPago     = (int)$t['quantidade']; $valorPago     = (float)$t['total_valor']; break;
            case 'VENCIDO':  $totalVencido  = (int)$t['quantidade']; $valorVencido  = (float)$t['total_valor']; break;
            case 'PARCIAL':  $totalParcial  = (int)$t['quantidade']; break;
        }
    }
}
?>

<section class="section active" id="sec-contas-pagar">
  <div class="section-header">
    <div class="section-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
    </div>
    <div>
      <div class="section-title">Contas a Pagar</div>
      <div class="section-sub">Gestão de pagamentos a fornecedores e colaboradores</div>
    </div>
  </div>
  <div class="divider"></div>

  <!-- Cards de resumo -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="cp-stats-grid">
    <div class="card-stat">
      <div class="card-stat-val"><?= $totalPendente ?></div>
      <div class="card-stat-lbl">Pendentes</div>
    </div>
    <div class="card-stat" style="--stat-color:var(--neon-red)">
      <div class="card-stat-val" style="color:var(--neon-red)"><?= $totalVencido ?></div>
      <div class="card-stat-lbl">Vencidas</div>
    </div>
    <div class="card-stat" style="--stat-color:var(--neon-green)">
      <div class="card-stat-val" style="color:var(--neon-green)"><?= $totalPago ?></div>
      <div class="card-stat-lbl">Pagas</div>
    </div>
    <div class="card-stat" style="--stat-color:var(--neon-yellow)">
      <div class="card-stat-val" style="color:var(--neon-yellow)">R$ <?= number_format($valorPendente + $valorVencido, 2, ',', '.') ?></div>
      <div class="card-stat-lbl">Total em aberto</div>
    </div>
  </div>
  <style>
    @media(max-width:1024px){.cp-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
    @media(max-width:640px){.cp-stats-grid{grid-template-columns:1fr!important}}
  </style>

  <!-- Filtros -->
  <?php
  $filterContent = '<form method="GET" action="' . $baseUrl . '/contas-pagar">
    <div class="col3" style="margin-bottom:12px">
      <div class="fg"><div class="fl">Fornecedor</div>
        <select name="id_fornecedor" class="fi" onchange="this.form.submit()">
          <option value="">Todos</option>' . $fornecedorOptions . '
        </select></div>
      <div class="fg"><div class="fl">Colaborador</div>
        <select name="id_colaborador" class="fi" onchange="this.form.submit()">' . $colaboradorOptions . '</select></div>
      <div class="fg"><div class="fl">Tipo</div>
        <select name="tipo" class="fi" onchange="this.form.submit()">' . $tipoOptions . '</select></div>
    </div>
    <div class="col3" style="margin-bottom:12px">
      <div class="fg"><div class="fl">Evento</div>
        <select name="evento_id" class="fi" onchange="this.form.submit()">' . $eventoOptions . '</select></div>
      <div class="fg"><div class="fl">Status</div>
        <select name="status" class="fi" onchange="this.form.submit()">' . $statusOptions . '</select></div>
      <div class="fg"><div class="fl">Buscar</div>
        <input type="text" name="search" class="fi" placeholder="Descrição, nome..." value="' . htmlspecialchars($search) . '" onchange="this.form.submit()"/></div>
    </div>
    <div class="col4" style="margin-bottom:12px">
      <div class="fg"><div class="fl">Vencimento De</div>
        <input type="date" name="data_vencimento_inicio" class="fi" value="' . htmlspecialchars($filters['data_vencimento_inicio'] ?? '') . '" onchange="this.form.submit()"/></div>
      <div class="fg"><div class="fl">Vencimento Até</div>
        <input type="date" name="data_vencimento_fim" class="fi" value="' . htmlspecialchars($filters['data_vencimento_fim'] ?? '') . '" onchange="this.form.submit()"/></div>
      <div class="fg"><div class="fl">Valor Min</div>
        <input type="text" name="valor_min" class="fi" placeholder="0,00" value="' . htmlspecialchars($filters['valor_min'] ?? '') . '" onchange="this.form.submit()"/></div>
      <div class="fg"><div class="fl">Valor Max</div>
        <input type="text" name="valor_max" class="fi" placeholder="0,00" value="' . htmlspecialchars($filters['valor_max'] ?? '') . '" onchange="this.form.submit()"/></div>
    </div>
    <div style="text-align:right">
      <a href="' . $baseUrl . '/contas-pagar" class="btn btn-sm btn-gray">Limpar Filtros</a>
    </div>
  </form>';
  echo renderAccordion([
      'id' => 'accordion-filtros',
      'items' => [[
          'title'   => 'Filtros',
          'iconBg'  => 'var(--neon-cyan)',
          'icon'    => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>',
          'content' => $filterContent,
          'open'    => !empty(array_filter($filters)),
      ]]
  ]);
  ?>

  <!-- Alertas de vencimento -->
  <?php if (!empty($vencidas)): ?>
  <div class="card" style="margin-bottom:16px;border:1px solid var(--neon-red)">
    <div class="card-head">
      <span class="card-title" style="color:var(--neon-red)">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Vencidas (<?= count($vencidas) ?>)
      </span>
    </div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table class="tbl" width="100%">
          <thead><tr><th>Quem</th><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Ação</th></tr></thead>
          <tbody>
            <?php foreach ($vencidas as $v): ?>
            <tr>
              <td><?= htmlspecialchars($v['fornecedor_nome'] ?? $v['colaborador_nome'] ?? 'Outros custos') ?></td>
              <td><?= htmlspecialchars(substr($v['descricao'], 0, 50)) ?></td>
              <td style="font-weight:700;color:var(--neon-red)">R$ <?= number_format((float)$v['valor'], 2, ',', '.') ?></td>
              <td style="color:var(--neon-red)"><?= date('d/m/Y', strtotime($v['data_vencimento'])) ?></td>
              <td><a href="<?= $baseUrl ?>/contas-pagar/edit/<?= $v['id'] ?>" class="btn btn-sm btn-green">Registrar Pgto</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($vencemHoje)): ?>
  <div class="card" style="margin-bottom:16px;border:1px solid var(--neon-yellow)">
    <div class="card-head">
      <span class="card-title" style="color:var(--neon-yellow)">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Vencem Hoje (<?= count($vencemHoje) ?>)
      </span>
    </div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table class="tbl" width="100%">
          <thead><tr><th>Quem</th><th>Descrição</th><th>Valor</th><th>Ação</th></tr></thead>
          <tbody>
            <?php foreach ($vencemHoje as $h): ?>
            <tr>
              <td><?= htmlspecialchars($h['fornecedor_nome'] ?? $h['colaborador_nome'] ?? 'Outros custos') ?></td>
              <td><?= htmlspecialchars(substr($h['descricao'], 0, 50)) ?></td>
              <td style="font-weight:700">R$ <?= number_format((float)$h['valor'], 2, ',', '.') ?></td>
              <td><a href="<?= $baseUrl ?>/contas-pagar/edit/<?= $h['id'] ?>" class="btn btn-sm btn-green">Registrar Pgto</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Tabela principal -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Todas as contas</span>
      <?php if (\App\Auth\Rbac::check('contas_pagar.excluir')): ?>
      <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-conta">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Excluir (<span id="bulk-count">0</span>)
      </button>
      <?php endif; ?>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($contas)): ?>
      <div style="text-align:center;padding:40px;color:var(--text-3)">Nenhuma conta encontrada.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="tbl" width="100%">
          <thead>
            <tr>
              <th><input type="checkbox" id="select-all"></th>
              <th>Quem</th>
              <th>Evento / Descrição</th>
              <th>Valor</th>
              <th>Vencimento</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contas as $c): ?>
            <?php
              $nome = htmlspecialchars($c['fornecedor_nome'] ?? $c['colaborador_nome'] ?? 'Outros custos');
              if ($c['tipo'] === 'colaborador')  $tipoBadge = renderBadge(['label'=>'Colaborador','variant'=>'blue','size'=>'sm']);
              elseif ($c['tipo'] === 'fornecedor') $tipoBadge = renderBadge(['label'=>'Fornecedor','variant'=>'cyan','size'=>'sm']);
              else                                 $tipoBadge = renderBadge(['label'=>'Outros custos','variant'=>'gray','size'=>'sm']);

              switch ($c['status']) {
                  case 'PAGO':    $statusBadge = renderBadge(['label'=>'Pago',    'variant'=>'green', 'size'=>'sm']); break;
                  case 'VENCIDO': $statusBadge = renderBadge(['label'=>'Vencido', 'variant'=>'red',   'size'=>'sm']); break;
                  case 'PARCIAL': $statusBadge = renderBadge(['label'=>'Parcial', 'variant'=>'purple','size'=>'sm']); break;
                  default:        $statusBadge = renderBadge(['label'=>'Pendente','variant'=>'yellow','size'=>'sm']);
              }

              $popId = 'pop-cp-' . $c['id'];
            ?>
            <tr>
              <td><input type="checkbox" class="row-check" value="<?= $c['id'] ?>"></td>
              <td>
                <div class="font-bold" style="color:var(--text-1)"><?= $nome ?></div>
                <div style="margin-top:4px"><?= $tipoBadge ?></div>
              </td>
              <td>
                <?php if ($c['evento_nome']): ?>
                <div style="color:var(--cyan);font-weight:600;">
                  <?= htmlspecialchars($c['evento_nome']) ?> 
                  <span style="font-size:11px;color:var(--text-3);font-weight:normal">
                    (OS: <?= $c['evento_id'] ?><?= !empty($c['evento_os_cliente']) ? ' / ' . htmlspecialchars($c['evento_os_cliente']) : '' ?>)
                  </span>
                  <?php if (!empty($c['evento_local'])): ?>
                    <span style="color:var(--text-3);font-weight:normal"> - <?= htmlspecialchars($c['evento_local']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="text-xs" style="color:var(--text-3);margin-bottom:4px">
                  Periodo: <?= date('d/m/Y', strtotime($c['evento_data_inicio'])) ?> a <?= date('d/m/Y', strtotime($c['evento_data_fim'])) ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($c['item_nome'])): ?>
                <div class="text-sm font-medium" style="color:var(--text-1)">
                  Item: <?= htmlspecialchars($c['item_nome']) ?> 
                  <?php if (!empty($c['item_dias'])): ?>
                    <span style="font-size:11px;color:var(--text-2)"> (<?= $c['item_dias'] ?> dia<?= $c['item_dias'] > 1 ? 's' : '' ?>)</span>
                  <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="text-xs" style="color:var(--text-3);margin-top:2px"><?= htmlspecialchars($c['descricao']) ?></div>

                <?php if (!empty($c['numero_nf']) || !empty($c['nota_fiscal'])): ?>
                <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                  <?php if (!empty($c['numero_nf'])): ?>
                    <span class="text-xs font-semibold" style="color:var(--text-2);background:var(--bg-secondary);border:1px solid var(--bg-border-sub);padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:4px">
                      NF: <span style="color:var(--text-1)"><?= htmlspecialchars($c['numero_nf']) ?></span>
                    </span>
                  <?php endif; ?>
                  
                  <?php if (!empty($c['nota_fiscal'])): ?>
                    <a href="/<?= htmlspecialchars(ltrim($c['nota_fiscal'], '/')) ?>" target="_blank" class="btn btn-sm btn-cyan" style="padding:2px 8px;font-size:10px;height:auto;display:inline-flex;align-items:center;gap:4px;border-radius:4px">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      Ver Nota Fiscal
                    </a>
                  <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($c['observacao'])): ?>
                <div class="text-xs" style="color:var(--text-2);background:rgba(255, 165, 0, 0.05);border-left:3px solid var(--neon-yellow);padding:4px 8px;border-radius:0 4px 4px 0;max-width:500px;line-height:1.3" title="<?= htmlspecialchars($c['observacao']) ?>">
                  <strong>Financeiro:</strong> <?= htmlspecialchars(mb_strimwidth($c['observacao'], 0, 90, '...')) ?>
                </div>
                <?php endif; ?>
              </td>
              <td style="font-weight:700">R$ <?= number_format((float)$c['valor'], 2, ',', '.') ?>
                <?php if (!empty($c['valor_pago']) && $c['status'] === 'PAGO'): ?>
                <div class="text-xs" style="color:var(--neon-green)">Pago: R$ <?= number_format((float)$c['valor_pago'], 2, ',', '.') ?></div>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($c['data_vencimento'])) ?>
                <?php if (!empty($c['data_pagamento']) && $c['status'] === 'PAGO'): ?>
                <div class="text-xs" style="color:var(--text-3)">Pago: <?= date('d/m/Y', strtotime($c['data_pagamento'])) ?></div>
                <?php endif; ?>
              </td>
              <td><?= $statusBadge ?></td>
              <td>
                <div class="td-actions"><div class="td-act-menu">
                  <button type="button" class="btn btn-sm td-act-toggle" data-action="toggle-pop" data-target="<?= $popId ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                  </button>
                  <div class="td-act-dropdown" id="<?= $popId ?>">
                    <a class="td-act-item td-act-cyan" href="<?= $baseUrl ?>/contas-pagar/edit/<?= $c['id'] ?>"><span><?= $c['status'] === 'PAGO' ? 'Ver / Editar' : 'Registrar Pgto' ?></span></a>
                    <?php if (\App\Auth\Rbac::check('contas_pagar.excluir')): ?>
                    <button type="button" class="td-act-item td-act-red" data-action="delete-conta" data-id="<?= $c['id'] ?>"><span>Excluir</span></button>
                    <?php endif; ?>
                  </div>
                </div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
function toggleConta(id, btn) {
  fetch(BASE_URL + '/contas-pagar/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Conta ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editConta(id) {
  window.location.href = BASE_URL + '/contas-pagar/edit/' + id;
}

function deleteConta(id) {
  if (!confirm('Excluir este conta?')) return;
  fetch(BASE_URL + '/contas-pagar/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Conta excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeleteContas() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' conta(s)?')) return;
  fetch(BASE_URL + '/contas-pagar/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' conta(s) excluido(s)');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.registerActions === 'function') {
    window.registerActions({
      'toggle-conta': (el) => { toggleConta(el.dataset.id, el); },
      'edit-conta': (el) => { editConta(el.dataset.id); },
      'delete-conta': (el) => { deleteConta(el.dataset.id); },
      'bulk-delete-conta': () => { bulkDeleteContas(); },
    });
  }

  const selectAll = document.getElementById('select-all');
  const btnBulk = document.getElementById('btn-bulk-delete');
  const bulkCount = document.getElementById('bulk-count');
  if (selectAll && btnBulk) {
    function updateBulk() {
      const n = document.querySelectorAll('.row-check:checked').length;
      bulkCount.textContent = n;
      btnBulk.style.display = n > 0 ? 'inline-flex' : 'none';
    }
    selectAll.addEventListener('change', () => {
      document.querySelectorAll('.row-check').forEach(cb => { cb.checked = selectAll.checked; });
      updateBulk();
    });
    document.addEventListener('change', (e) => { if (e.target.classList.contains('row-check')) updateBulk(); });
  }
});
</script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

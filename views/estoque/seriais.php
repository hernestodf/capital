<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';

$rows = [];
if (!empty($seriais)) {
    $actionBtns = renderTableActions('default', 'serial', \App\Auth\Rbac::check('estoque.editar'), \App\Auth\Rbac::check('estoque.excluir'));
    $actionBtns[] = [
        'label' => 'Historico',
        'variant' => 'purple',
        'size' => 'sm',
        'action' => 'historico-serial',
        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ];
    foreach ($seriais as $s) {
        $statusBadge = '';
        if ($s['status'] === 'ATIVO') {
            $statusBadge = renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm']);
        } elseif ($s['status'] === 'MANUTENCAO') {
            $statusBadge = renderBadge(['label' => 'Manutencao', 'variant' => 'yellow', 'size' => 'sm']);
        } else {
            $statusBadge = renderBadge(['label' => 'Vender', 'variant' => 'cyan', 'size' => 'sm']);
        }

        $rows[] = [
            'data-id' => $s['id'],
            ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($s['serial']) . '</span>'],
            ['html' => true, 'content' => $statusBadge],
            htmlspecialchars($s['motivo'] ?? '-'),
        ];
    }
}

$baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
?>

    <section class="section active" id="sec-seriais">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Códigos de Barras - <?= htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($produto['produto'] ?? ''))) ?></div>
          <div class="section-sub">Gestao de códigos de barras do produto</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Resumo de Status -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="seriais-stats-grid">
        <div class="card-stat">
          <div class="card-stat-val"><?= $seriais_count['total'] ?? 0 ?></div>
          <div class="card-stat-lbl">Total</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--green)">
          <div class="card-stat-val" style="color:var(--green)"><?= $seriais_count['ativos'] ?? 0 ?></div>
          <div class="card-stat-lbl">Ativos</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--yellow)">
          <div class="card-stat-val" style="color:var(--yellow)"><?= $seriais_count['manutencao'] ?? 0 ?></div>
          <div class="card-stat-lbl">Manutencao</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--cyan)">
          <div class="card-stat-val" style="color:var(--cyan)"><?= $seriais_count['vender'] ?? 0 ?></div>
          <div class="card-stat-lbl">Vender</div>
        </div>
      </div>

      <style>
        @media (max-width:1024px){.seriais-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
        @media (max-width:640px){.seriais-stats-grid{grid-template-columns:1fr!important}}
      </style>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Códigos de Barras Cadastrados</span>
          <div style="display:flex;gap:8px">
            <a href="<?= $baseUrl ?>/estoque" class="btn btn-sm btn-gray">Voltar</a>
            <button type="button" class="btn btn-sm btn-cyan" data-action="abrir-modal-serial">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Novo Código
            </button>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($seriais)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
              </svg>
              <div>Nenhum código cadastrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Código" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-seriais',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar código de barras ou motivo...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => 'Código de Barras', 'sortable' => true],
                  ['label' => 'Status', 'sortable' => true],
                  ['label' => 'Motivo', 'sortable' => true]
              ],
              'actionBtns' => $actionBtns,
              'rows' => $rows
          ]) ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- MODAL: Adicionar/Editar Código -->
    <?= renderModal([
        'id' => 'modal-serial',
        'variant' => 'form',
        'title' => 'Adicionar Código de Barras',
        'subtitle' => 'Cadastrar novo código de barras para o produto',
        'body' => '<div class="fg"><div class="fl">Código de Barras</div><input type="text" id="modal-serial-serial" class="fi" placeholder="Ex: SN123456789"/></div>
                   <div class="fg" style="margin-top:12px"><div class="fl">Status</div><select id="modal-serial-status" class="fi" onchange="toggleMotivo()"><option value="ATIVO">Ativo</option><option value="MANUTENCAO">Manutencao</option><option value="VENDER">Vender</option></select></div>
                   <div class="fg" style="margin-top:12px;display:none" id="modal-serial-motivo-group"><div class="fl">Motivo (obrigatorio para manutencao)</div><input type="text" id="modal-serial-motivo" class="fi" placeholder="Motivo da manutencao"/></div>
                   <input type="hidden" id="modal-serial-id" value=""/>
                   <input type="hidden" id="modal-serial-produto-id" value="' . $produto['id'] . '"/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-serial">Cancelar</button><button class="btn btn-cyan" data-action="salvar-serial">Salvar</button>'
    ]) ?>

    <!-- MODAL: Alterar Status -->
    <?= renderModal([
        'id' => 'modal-status',
        'variant' => 'form',
        'title' => 'Alterar Status do Código',
        'subtitle' => 'Atualizar status do código de barras',
        'body' => '<input type="hidden" id="status-serial-id" value=""/>
                   <div class="fg"><div class="fl">Novo Status</div><select id="status-serial-novo-status" class="fi" onchange="toggleMotivoStatus()"><option value="ATIVO">Ativo</option><option value="MANUTENCAO">Manutencao</option><option value="VENDER">Vender</option></select></div>
                   <div class="fg" style="margin-top:12px;display:none" id="status-serial-motivo-group"><div class="fl">Motivo (obrigatorio para manutencao)</div><input type="text" id="status-serial-motivo" class="fi" placeholder="Motivo da alteracao"/></div>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-status">Cancelar</button><button class="btn btn-cyan" data-action="atualizar-status-serial">Atualizar</button>'
    ]) ?>

    <!-- MODAL: Historico de Devolucoes -->
    <?= renderModal([
        'id' => 'modal-historico-serial',
        'size' => 'lg',
        'variant' => 'info',
        'title' => 'Historico de Devolucoes',
        'subtitle' => '<span id="historico-serial-label"></span>',
        'body' => '<div id="historico-kpis" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
            <div class="card-stat"><div class="card-stat-val" id="hist-total">0</div><div class="card-stat-lbl">Total</div></div>
            <div class="card-stat" style="--stat-color:var(--green)"><div class="card-stat-val" style="color:var(--green)" id="hist-aprovadas">0</div><div class="card-stat-lbl">Aprovadas</div></div>
            <div class="card-stat" style="--stat-color:var(--red)"><div class="card-stat-val" style="color:var(--red)" id="hist-pendencias">0</div><div class="card-stat-lbl">Pendencias</div></div>
            <div class="card-stat" style="--stat-color:var(--purple)"><div class="card-stat-val" style="color:var(--purple)" id="hist-solucionadas">0</div><div class="card-stat-lbl">Solucionadas</div></div>
        </div>
        <div id="historico-tabela" style="min-height:100px"></div>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-historico-serial">Fechar</button>'
    ]) ?>

<script>
const produtoId = <?= $produto['id'] ?>;

// Registrado via window.registerActions (nao document.addEventListener) pra nao duplicar
// o dispatch de clique: scripts.js ja delega [data-action] globalmente em document.body
// e cai num fallback window[camelCase(action)] pra acoes nao reconhecidas — registrar
// aqui E TAMBEM ouvir 'click' localmente disparava cada acao 2x (bug-XXX).
document.addEventListener('DOMContentLoaded', function() {
  window.registerActions({
    'abrir-modal-serial': function() { abrirModalSerial(); },
    'salvar-serial': function() { salvarSerial(); },
    'atualizar-status-serial': function() { atualizarStatusSerial(); },
    'edit-serial': function(el) { const id = el.dataset.id; if (id) editSerial(id); },
    'delete-serial': function(el) { const id = el.dataset.id; if (id) deleteSerial(id); },
    'historico-serial': function(el) { const id = el.dataset.id; if (id) historicoSerial(id, el.dataset.nome || ''); },
  });
});

function abrirModalSerial(id, serial, status, motivo) {
  if (id) {
    document.getElementById('modal-serial-id').value = id;
    document.getElementById('modal-serial-serial').value = serial || '';
    document.getElementById('modal-serial-status').value = status || 'ATIVO';
    document.getElementById('modal-serial-motivo').value = motivo || '';
    document.querySelector('#modal-serial .modal-title').textContent = 'Editar Código de Barras';
  } else {
    document.getElementById('modal-serial-id').value = '';
    document.getElementById('modal-serial-serial').value = '';
    document.getElementById('modal-serial-status').value = 'ATIVO';
    document.getElementById('modal-serial-motivo').value = '';
    document.querySelector('#modal-serial .modal-title').textContent = 'Adicionar Código de Barras';
  }

  toggleMotivo();
  openModal('modal-serial');
}

function salvarSerial() {
  const id = document.getElementById('modal-serial-id').value;
  const serial = document.getElementById('modal-serial-serial').value.trim();
  const status = document.getElementById('modal-serial-status').value;
  const motivo = document.getElementById('modal-serial-motivo').value.trim();
  const produtoId = document.getElementById('modal-serial-produto-id').value;

  if (!serial) {
    showToast('yellow', 'Atencao', 'Código de barras é obrigatório');
    return;
  }

  if (status === 'MANUTENCAO' && !motivo) {
    showToast('yellow', 'Atencao', 'Motivo e obrigatorio para status de manutencao');
    return;
  }

  if (id) {
    fetch(BASE_URL + '/seriais/update/' + id, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN + '&serial=' + encodeURIComponent(serial) + '&status=' + status + '&motivo=' + encodeURIComponent(motivo)
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Código atualizado!');
        closeModal('modal-serial');
        setTimeout(() => { window.location.reload(); }, 1000);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao atualizar código');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  } else {
    fetch(BASE_URL + '/seriais/store', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN + '&id_produto=' + produtoId + '&serial=' + encodeURIComponent(serial) + '&status=' + status + '&motivo=' + encodeURIComponent(motivo)
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Código adicionado!');
        closeModal('modal-serial');
        setTimeout(() => { window.location.reload(); }, 1000);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao adicionar código');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  }
}

function abrirModalStatus(id, statusAtual) {
  document.getElementById('status-serial-id').value = id;
  document.getElementById('status-serial-novo-status').value = statusAtual || 'ATIVO';
  document.getElementById('status-serial-motivo').value = '';
  toggleMotivoStatus();
  openModal('modal-status');
}

function atualizarStatusSerial() {
  const id = document.getElementById('status-serial-id').value;
  const status = document.getElementById('status-serial-novo-status').value;
  const motivo = document.getElementById('status-serial-motivo').value.trim();

  if (status === 'MANUTENCAO' && !motivo) {
    showToast('yellow', 'Atencao', 'Motivo e obrigatorio para status de manutencao');
    return;
  }

  fetch(BASE_URL + '/seriais/update-status/' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN + '&status=' + status + '&motivo=' + encodeURIComponent(motivo)
  })
  .then(r => r.json())
  .then(function(data) {
    if (data.success) {
      showToast('green', 'Sucesso', 'Status atualizado!');
      closeModal('modal-status');
      setTimeout(() => { window.location.reload(); }, 1000);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao atualizar status');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function excluirSerial(serialId) {
  if (confirm('Tem certeza que deseja excluir este código de barras?')) {
    fetch(BASE_URL + '/seriais/delete/' + serialId, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('green', 'Excluido', 'Código excluído com sucesso');
        setTimeout(() => { window.location.reload(); }, 1500);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao excluir código');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  }
}

function toggleMotivo() {
  const status = document.getElementById('modal-serial-status').value;
  const group = document.getElementById('modal-serial-motivo-group');
  if (group) {
    group.style.display = status === 'MANUTENCAO' ? 'block' : 'none';
  }
}

function toggleMotivoStatus() {
  const status = document.getElementById('status-serial-novo-status').value;
  const group = document.getElementById('status-serial-motivo-group');
  if (group) {
    group.style.display = status === 'MANUTENCAO' ? 'block' : 'none';
  }
}

function editSerial(serialId) {
  fetch(BASE_URL + '/seriais/get-serial/' + serialId)
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data) {
        abrirModalSerial(data.data.id, data.data.serial, data.data.status, data.data.motivo || '');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao carregar dados do código'));
}

function deleteSerial(serialId) {
  excluirSerial(serialId);
}

function historicoSerial(serialId, serialNome) {
  document.getElementById('historico-serial-label').textContent = 'Serial: ' + serialNome;
  document.getElementById('hist-total').textContent = '...';
  document.getElementById('hist-aprovadas').textContent = '...';
  document.getElementById('hist-pendencias').textContent = '...';
  document.getElementById('hist-solucionadas').textContent = '...';
  document.getElementById('historico-tabela').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3)">Carregando...</div>';
  openModal('modal-historico-serial');

  fetch(BASE_URL + '/devolucao/historico-serial?id_serial=' + serialId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success && data.data) {
        popularHistorico(data.data);
      } else {
        document.getElementById('historico-tabela').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3)">' + (data.error || 'Erro ao carregar historico') + '</div>';
      }
    })
    .catch(function() {
      document.getElementById('historico-tabela').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3)">Erro ao carregar historico</div>';
    });
}

function popularHistorico(devolucoes) {
  var total = devolucoes.length;
  var aprovadas = devolucoes.filter(function(d) { return d.status === 'A'; }).length;
  var pendencias = devolucoes.filter(function(d) { return d.status === 'P'; }).length;
  var solucionadas = devolucoes.filter(function(d) { return d.status === 'S'; }).length;

  document.getElementById('hist-total').textContent = total;
  document.getElementById('hist-aprovadas').textContent = aprovadas;
  document.getElementById('hist-pendencias').textContent = pendencias;
  document.getElementById('hist-solucionadas').textContent = solucionadas;

  var container = document.getElementById('historico-tabela');
  if (total === 0) {
    container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3)">Nenhuma devolucao registrada para este serial</div>';
    return;
  }

  var html = '<table class="data-table striped hoverable">';
  html += '<thead><tr><th>Evento</th><th>Data</th><th>Status</th><th>Detalhes</th></tr></thead>';
  html += '<tbody>';
  devolucoes.forEach(function(d) {
    var badge = '';
    if (d.status === 'A') {
      badge = '<span class="badge sm green">Aprovado</span>';
    } else if (d.status === 'P') {
      badge = '<span class="badge sm red">Pendencia</span>';
    } else if (d.status === 'S') {
      badge = '<span class="badge sm purple">Solucionado</span>';
    } else {
      badge = '<span class="badge sm gray">-</span>';
    }

    var detalhes = '';
    if (d.status === 'P' && d.motivo_pendencia) {
      detalhes = '<div style="font-size:11px;color:var(--text-3)"><strong>Motivo:</strong> ' + escapeHtml(d.motivo_pendencia) + '</div>';
    }
    if (d.status === 'S' && d.solucao_pendencia) {
      detalhes += '<div style="font-size:11px;color:var(--text-3)"><strong>Solucao:</strong> ' + escapeHtml(d.solucao_pendencia) + '</div>';
    }
    if (d.usuario_devolucao) {
      detalhes += '<div style="font-size:11px;color:var(--text-3)"><strong>Por:</strong> ' + escapeHtml(d.usuario_devolucao) + '</div>';
    }

    var dataFormatada = d.data_devolucao ? new Date(d.data_devolucao).toLocaleDateString('pt-BR') : '-';

    html += '<tr>';
    html += '<td>' + escapeHtml(d.nome_evento || '-') + '</td>';
    html += '<td>' + dataFormatada + '</td>';
    html += '<td>' + badge + '</td>';
    html += '<td>' + (detalhes || '-') + '</td>';
    html += '</tr>';
  });
  html += '</tbody></table>';
  container.innerHTML = html;
}

function escapeHtml(str) {
  if (!str) return '';
  var div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('tbl-seriais');
  if (!table) return;

  const rows = table.querySelectorAll('tbody tr');
  rows.forEach(function(row) {
    const serialId = row.getAttribute('data-id');
    if (!serialId) return;
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

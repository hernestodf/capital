<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

$rows = [];
if (!empty($produtos)) {
    $actionBtns = renderTableActions('default', 'produto', \App\Auth\Rbac::check('estoque.editar'), \App\Auth\Rbac::check('estoque.excluir'));
    foreach ($produtos as $p) {
        $custo = !empty($p['custo']) ? 'R$ ' . number_format($p['custo'], 2, ',', '.') : '-';
        $locadoBadge = ($p['pode_ser_locado'] ?? 'N') === 'S'
            ? '<button type="button" class="btn btn-sm btn-green" data-action="toggle-locado" data-id="' . $p['id'] . '">Sim</button>'
            : '<button type="button" class="btn btn-sm btn-red" data-action="toggle-locado" data-id="' . $p['id'] . '">Nao</button>';

        $nomeProduto = preg_replace('/\s*-\s*\d+$/', '', (string)($p['produto'] ?? ''));
        $total       = (int)($p['total_codigos'] ?? 0);
        $emCampo     = (int)($p['em_campo']      ?? 0);
        $disponiveis = $total - $emCampo;

        $totalCell = $total > 0
            ? '<span style="font-weight:600">' . $total . '</span>'
            : '<span style="color:var(--text-4)">0</span>';

        $dispCell = $disponiveis > 0
            ? '<span style="color:#22c55e;font-weight:600">' . $disponiveis . '</span>'
            : '<span style="color:var(--text-4)">0</span>';

        $naRuaCell = $emCampo > 0
            ? '<button type="button" class="badge-campo-btn" data-action="ver-em-campo" data-id="' . $p['id'] . '" data-nome="' . htmlspecialchars($nomeProduto, ENT_QUOTES) . '">'
              . '<span class="badge-campo">&#9679; ' . $emCampo . ' na rua</span>'
              . '</button>'
            : '<span style="color:var(--text-4);font-size:12px">—</span>';

        $rows[] = [
            'data-id' => $p['id'],
            ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $p['id'] . '">'],
            ['html' => true, 'content' => (string)$p['id']],
            ['html' => true, 'content' => htmlspecialchars($nomeProduto)],
            ['html' => true, 'content' => htmlspecialchars($p['secao_nome'] ?? '-')],
            ['html' => true, 'content' => $custo],
            ['html' => true, 'content' => $locadoBadge],
            ['html' => true, 'content' => $totalCell],
            ['html' => true, 'content' => $dispCell],
            ['html' => true, 'content' => $naRuaCell],
        ];
    }
}
?>

    <section class="section active" id="sec-estoque">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Estoque</div>
          <div class="section-sub">Produtos, secoes e gestao de codigos de barras</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Produtos Cadastrados</span>
          <div style="display:flex;gap:8px">
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-produto">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/estoque/relatorios" class="btn btn-sm btn-yellow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Relatorios
            </a>
            <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-secao">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Secao
            </button>
            <a href="<?= $baseUrl ?>/estoque/create" class="btn btn-sm btn-cyan">Novo Produto</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($produtos)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              <div>Nenhum produto encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Produto" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-estoque',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar produto ou observacao...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                  ['label' => 'ID', 'sortable' => true],
                  ['label' => 'Produto', 'sortable' => true],
                  ['label' => 'Secao', 'sortable' => true],
                  ['label' => 'Custo', 'sortable' => true],
                  ['label' => 'Pode ser Locado'],
                  ['label' => 'Total', 'sortable' => true],
                  ['label' => 'Disponíveis', 'sortable' => true],
                  ['label' => 'Na Rua'],
              ],
              'actionBtns' => $actionBtns,
              'rows' => $rows
          ]) ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- MODAL: Códigos na Rua -->
    <?= renderModal([
        'id' => 'modal-em-campo',
        'variant' => 'form',
        'title' => 'Códigos na Rua',
        'subtitle' => '<span id="modal-campo-subtitle">Carregando...</span>',
        'body' => '<div id="modal-campo-body" style="min-height:80px">
                     <div style="text-align:center;padding:24px;color:var(--text-3)">Carregando...</div>
                   </div>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-em-campo">Fechar</button>'
    ]) ?>

    <!-- MODAL: Gerenciar Secoes -->
    <?= renderModal([
        'id' => 'modal-secao',
        'variant' => 'form',
        'title' => 'Gerenciar Secoes',
        'subtitle' => 'Adicionar, editar ou excluir secoes',
        'body' => '<div id="modal-secao-list" style="max-height:300px;overflow-y:auto;margin-bottom:16px"></div>
                   <div class="divider" style="margin:12px 0"></div>
                   <div class="fg"><div class="fl">Nome da Secao</div><input type="text" id="modal-secao-nome" class="fi" placeholder="Ex: Eletricos, Hidraulicos, etc"/></div>
                   <input type="hidden" id="modal-secao-id" value=""/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-secao">Fechar</button><button class="btn btn-cyan" data-action="salvar-secao">Salvar</button>'
    ]) ?>

<script>
// =====================================================
// ESTOQUE - Toggle Locado, Edit, Delete
// =====================================================
function toggleLocado(produtoId, btn) {
  fetch(BASE_URL + '/estoque/toggle-locado/' + produtoId, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.pode_ser_locado === 'S' ? 'btn-green' : 'btn-red');
      btn.textContent = data.pode_ser_locado === 'S' ? 'Sim' : 'Nao';
      showToast('green', 'Atualizado', 'Produto ' + (data.pode_ser_locado === 'S' ? 'pode' : 'nao pode') + ' ser locado');
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao alterar status');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function editProduto(produtoId) {
  window.location.href = BASE_URL + '/estoque/edit/' + produtoId;
}

function deleteProduto(produtoId) {
  if (confirm('Tem certeza que deseja excluir este produto?')) {
    fetch(BASE_URL + '/estoque/delete/' + produtoId, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('green', 'Excluido', 'Produto excluido com sucesso');
        setTimeout(() => { window.location.reload(); }, 1500);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao excluir produto');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  }
}

// Registrado via window.registerActions (nao document.addEventListener) pra nao duplicar
// o dispatch de clique: scripts.js ja delega [data-action] globalmente em document.body
// e cai num fallback window[camelCase(action)] pra acoes nao reconhecidas — registrar
// aqui E TAMBEM ouvir 'click' localmente disparava cada acao 2x (bug-XXX).
document.addEventListener('DOMContentLoaded', function() {
  window.registerActions({
    'toggle-locado': function(el) { const id = el.dataset.id; if (id) toggleLocado(id, el); },
    'ver-em-campo': function(el) { const id = el.dataset.id; if (id) verEmCampo(id, el.dataset.nome || ''); },
    'salvar-secao': function() { salvarSecao(); },
    'editar-secao': function(el) { editarSecao(el.dataset.id, el.dataset.nome || ''); },
    'excluir-secao': function(el) { const id = el.dataset.id; if (id) excluirSecao(id); },
    'edit-produto': function(el) { const id = el.dataset.id; if (id) editProduto(id); },
    'delete-produto': function(el) { const id = el.dataset.id; if (id) deleteProduto(id); },
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('tbl-estoque');
  if (!table) return;

  carregarSecoesModal();
});

const modalSecaoObserver = new MutationObserver(function(mutations) {
  mutations.forEach(function(mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
      const modal = document.getElementById('modal-secao');
      if (modal && modal.classList.contains('open')) {
        carregarSecoesModal();
      }
    }
  });
});

const modalSecaoEl = document.getElementById('modal-secao');
if (modalSecaoEl) {
  modalSecaoObserver.observe(modalSecaoEl, { attributes: true });
}

// =====================================================
// SECOES - CRUD no Modal
// =====================================================
function carregarSecoesModal() {
  var baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : (window.BASE_URL || '');

  fetch(baseUrl + '/secoes/list')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data && data.success && data.data) {
        renderizarListaSecoes(data.data);
      }
    })
    .catch(function(err) {
      console.error('Erro ao carregar secoes:', err);
    });
}

function renderizarListaSecoes(secoes) {
  const container = document.getElementById('modal-secao-list');
  if (!container) return;

  if (secoes.length === 0) {
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma secao cadastrada</div>';
    return;
  }

  let html = '<table style="width:100%;border-collapse:collapse">';
  html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">ID</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right">Acoes</th>';
  html += '</tr></thead><tbody>';

  secoes.forEach(function(sec) {
    html += '<tr style="border-bottom:1px solid var(--bg-border)">';
    html += '<td style="padding:8px;font-size:13px">' + sec.id + '</td>';
    html += '<td style="padding:8px;font-size:13px">' + escapeHtml(sec.secao) + '</td>';
    html += '<td style="padding:8px;text-align:right">';
    html += '<button type="button" class="btn btn-sm btn-cyan" data-action="editar-secao" data-id="' + sec.id + '" data-nome="' + escapeHtml(sec.secao) + '" style="margin-right:4px">Editar</button>';
    html += '<button type="button" class="btn btn-sm btn-red" data-action="excluir-secao" data-id="' + sec.id + '">Excluir</button>';
    html += '</td></tr>';
  });

  html += '</tbody></table>';
  container.innerHTML = html;
}

function salvarSecao() {
  const nome = document.getElementById('modal-secao-nome').value.trim();
  const id = document.getElementById('modal-secao-id').value;

  if (!nome) {
    showToast('yellow', 'Atencao', 'Nome da secao e obrigatorio');
    return;
  }

  const url = id ? BASE_URL + '/secoes/update/' + id : BASE_URL + '/secoes/store';
  const body = '_csrf_token=' + CSRF_TOKEN + '&secao=' + encodeURIComponent(nome);

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: body
  })
  .then(r => r.json())
  .then(function(data) {
    if (data.success) {
      showToast('green', 'Sucesso', id ? 'Secao atualizada!' : 'Secao criada!');
      document.getElementById('modal-secao-nome').value = '';
      document.getElementById('modal-secao-id').value = '';
      carregarSecoesModal();
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao salvar secao');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function editarSecao(id, nome) {
  document.getElementById('modal-secao-nome').value = nome;
  document.getElementById('modal-secao-id').value = id;
  document.getElementById('modal-secao-nome').focus();
}

function excluirSecao(id) {
  if (!confirm('Tem certeza que deseja excluir esta secao?')) return;

  fetch(BASE_URL + '/secoes/delete/' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Secao excluida com sucesso');
      carregarSecoesModal();
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir secao');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function escapeHtml(text) {
  const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// =====================================================
// CÓDIGOS NA RUA — modal com detalhes por evento/sala
// =====================================================
function verEmCampo(produtoId, nomeProduto) {
  document.getElementById('modal-campo-subtitle').textContent = nomeProduto;
  document.getElementById('modal-campo-body').innerHTML =
    '<div style="text-align:center;padding:24px;color:var(--text-3)">Carregando...</div>';
  openModal('modal-em-campo');

  fetch(BASE_URL + '/estoque/em-campo?id_produto=' + produtoId)
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success) {
        document.getElementById('modal-campo-body').innerHTML =
          '<div style="text-align:center;padding:24px;color:var(--red)">Erro ao carregar dados</div>';
        return;
      }
      renderEmCampo(res.data, nomeProduto);
    })
    .catch(function() {
      document.getElementById('modal-campo-body').innerHTML =
        '<div style="text-align:center;padding:24px;color:var(--red)">Erro de conexão</div>';
    });
}

function renderEmCampo(itens, nomeProduto) {
  const container = document.getElementById('modal-campo-body');
  if (!itens || itens.length === 0) {
    container.innerHTML =
      '<div style="text-align:center;padding:32px;color:var(--text-4)">' +
      '<div style="font-size:32px;margin-bottom:8px"></div>' +
      '<div>Nenhum código fora do estoque no momento</div></div>';
    return;
  }

  // Agrupa por evento
  const eventos = {};
  itens.forEach(function(it) {
    const key = it.evento_id;
    if (!eventos[key]) {
      eventos[key] = {
        evento_id: it.evento_id,
        nome_evento: it.nome_evento,
        data_inicio: it.data_inicio,
        data_fim: it.data_fim,
        local_evento: it.local_evento,
        salas: {}
      };
    }
    const sala = it.nome_sala || 'Pendente (sem sala)';
    if (!eventos[key].salas[sala]) eventos[key].salas[sala] = [];
    eventos[key].salas[sala].push({ serial: it.serial, status: it.status_montagem });
  });

  let html = '<div style="display:flex;flex-direction:column;gap:12px">';
  Object.values(eventos).forEach(function(ev) {
    const dataInicio = ev.data_inicio ? new Date(ev.data_inicio + 'T00:00:00').toLocaleDateString('pt-BR') : '—';
    const dataFim    = ev.data_fim    ? new Date(ev.data_fim    + 'T00:00:00').toLocaleDateString('pt-BR') : '—';

    html += '<div style="border:1px solid var(--bg-border);border-radius:8px;padding:14px;background:var(--bg-2)">';
    html += '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">';
    html += '<span style="font-weight:600;font-size:14px">' + escapeHtml(ev.nome_evento) + '</span>';
    html += '<a href="' + BASE_URL + '/eventos/edit/' + ev.evento_id + '" target="_blank" style="font-size:12px;color:var(--cyan);text-decoration:none;white-space:nowrap;margin-left:12px">Ver →</a>';
    html += '</div>';
    if (ev.local_evento) {
      html += '<div style="font-size:12px;color:var(--text-3);margin-bottom:4px">' + escapeHtml(ev.local_evento) + '</div>';
    }
    html += '<div style="font-size:12px;color:var(--text-3);margin-bottom:10px">' + dataInicio + ' → ' + dataFim + '</div>';

    // Por sala
    Object.entries(ev.salas).forEach(function([sala, seriais]) {
      const isPendente = sala.startsWith('Pendente');
      html += '<div style="margin-bottom:8px">';
      html += '<div style="font-size:12px;font-weight:600;color:' + (isPendente ? '#f59e0b' : 'var(--text-2)') + ';margin-bottom:4px">';
      html += (isPendente ? '...' : '') + ' ' + escapeHtml(sala);
      html += '</div>';
      html += '<div style="display:flex;flex-wrap:wrap;gap:4px">';
      seriais.forEach(function(s) {
        const cor = s.status === 'montado' ? '#22c55e22' : '#f59e0b22';
        const textCor = s.status === 'montado' ? '#22c55e' : '#f59e0b';
        html += '<span style="font-family:monospace;font-size:11px;background:' + cor + ';color:' + textCor + ';border-radius:4px;padding:2px 7px;border:1px solid ' + textCor + '44">';
        html += escapeHtml(s.serial);
        html += '</span>';
      });
      html += '</div></div>';
    });

    html += '</div>';
  });
  html += '</div>';
  container.innerHTML = html;
}

</script>

<style>
.badge-campo-btn {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}
.badge-campo {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #f59e0b;
  background: #f59e0b18;
  border-radius: 20px;
  padding: 3px 10px;
  transition: background 0.15s;
}
.badge-campo-btn:hover .badge-campo {
  background: #f59e0b30;
}
</style>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

<script>
// Register data-action handlers
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.registerActions === 'function') {
    window.registerActions({
    'toggle-locado': (el) => { toggleLocado(el.dataset.id, el); },
    'edit-produto': (el) => { editProduto(el.dataset.id); },
    'delete-produto': (el) => { deleteProduto(el.dataset.id); },
    'bulk-delete-produto': () => { bulkDeleteProdutos(); },
    });
  }
});

// Bulk selection
document.addEventListener('DOMContentLoaded', function() {
  const selectAll = document.getElementById('select-all');
  const btnBulk = document.getElementById('btn-bulk-delete');
  const bulkCount = document.getElementById('bulk-count');
  
  if (!selectAll || !btnBulk) return;
  
  function updateBulkButton() {
    const checked = document.querySelectorAll('.row-check:checked');
    const count = checked.length;
    bulkCount.textContent = count;
    btnBulk.style.display = count > 0 ? 'inline-flex' : 'none';
  }
  
  selectAll.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => { cb.checked = selectAll.checked; });
    updateBulkButton();
  });
  
  document.addEventListener('change', function(e) {
    if (e.target.classList.contains('row-check')) {
      updateBulkButton();
    }
  });
});

function bulkDeleteProdutos() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  
  if (!ids.length) return;
  if (!confirm('Tem certeza que deseja excluir ' + ids.length + ' produto(s)?')) return;
  
  fetch(BASE_URL + '/estoque/bulk-delete', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluído', ids.length + ' produto(s) excluído(s) com sucesso');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir produtos');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}
</script>

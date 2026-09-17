<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';

$rows = [];
if (!empty($planilhas)) {
    $actionBtns = renderTableActions('default', 'planilha', \App\Auth\Rbac::check('planilhas.editar'), \App\Auth\Rbac::check('planilhas.excluir'));
    foreach ($planilhas as $p) {
        $valor = !empty($p['valor']) ? 'R$ ' . number_format($p['valor'], 2, ',', '.') : '-';

        $rows[] = [
            'data-id' => $p['id'],
            ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $p['id'] . '">'],
            htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($p['item'] ?? '-'))),
            htmlspecialchars($p['unidademedida'] ?? '-'),
            htmlspecialchars($p['descricao'] ?? '-'),
            $valor,
        ];
    }
}
?>

    <section class="section active" id="sec-planilhas">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Planilhas</div>
          <div class="section-sub">Itens, descricoes, unidades de medida e valores</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Planilhas Cadastradas</span>
          <div style="display:flex;gap:8px">
            <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-unidademedida">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Unidade Medida
            </button>
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-planilha">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/planilhas/create" class="btn btn-sm btn-cyan">Nova Planilha</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($planilhas)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <div>Nenhuma planilha encontrada</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Nova Planilha" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-planilhas',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar item ou descricao...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                                    ['label' => 'Item', 'sortable' => true],
                  ['label' => 'Unidade Medida', 'sortable' => true],
                  ['label' => 'Descricao', 'sortable' => true],
                  ['label' => 'Valor', 'sortable' => true],
              ],
              'actionBtns' => $actionBtns,
              'rows' => $rows
          ]) ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- MODAL: Gerenciar Unidades de Medida -->
    <?= renderModal([
        'id' => 'modal-unidademedida',
        'variant' => 'form',
        'title' => 'Gerenciar Unidades de Medida',
        'subtitle' => 'Adicionar, editar ou excluir unidades de medida',
        'body' => '<div id="modal-unidademedida-list" style="max-height:300px;overflow-y:auto;margin-bottom:16px"></div>
                   <div class="divider" style="margin:12px 0"></div>
                   <div class="fg"><div class="fl">Unidade de Medida</div><input type="text" id="modal-unidademedida-nome" class="fi" placeholder="Ex: UN, KG, M, L"/></div>
                   <input type="hidden" id="modal-unidademedida-id" value=""/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-unidademedida">Fechar</button><button class="btn btn-cyan" data-action="salvar-unidade-medida">Salvar</button>'
    ]) ?>

<script>
function togglePlanilha(id, btn) {
  fetch(BASE_URL + '/planilhas/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Planilha ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editPlanilha(id) {
  window.location.href = BASE_URL + '/planilhas/edit/' + id;
}

function deletePlanilha(id) {
  if (!confirm('Excluir este planilha?')) return;
  fetch(BASE_URL + '/planilhas/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Planilha excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeletePlanilhas() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' planilha(s)?')) return;
  fetch(BASE_URL + '/planilhas/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' planilha(s) excluido(s)');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

// ─── CRUD Unidade de Medida (modal) ───────────────────────
function escapeHtml(text) {
  const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function carregarUnidadesMedidaModal() {
  fetch(BASE_URL + '/unidades-medida/list')
    .then(r => r.json())
    .then(data => { if (data && data.success && data.data) renderizarListaUnidadesMedida(data.data); })
    .catch(err => console.error('Erro ao carregar unidades de medida:', err));
}

function renderizarListaUnidadesMedida(unidades) {
  const container = document.getElementById('modal-unidademedida-list');
  if (!container) return;
  if (unidades.length === 0) {
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma unidade de medida cadastrada</div>';
    return;
  }
  let html = '<table style="width:100%;border-collapse:collapse">';
  html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Unidade</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right">Ações</th>';
  html += '</tr></thead><tbody>';
  unidades.forEach(function(um) {
    html += '<tr style="border-bottom:1px solid var(--bg-border)">';
    html += '<td style="padding:8px;font-size:13px">' + escapeHtml(um.unidademedida) + '</td>';
    html += '<td style="padding:8px;text-align:right">';
    html += '<button type="button" class="btn btn-sm btn-cyan" data-action="editar-unidade-medida" data-id="' + um.id + '" data-nome="' + escapeHtml(um.unidademedida) + '" style="margin-right:4px">Editar</button>';
    html += '<button type="button" class="btn btn-sm btn-red" data-action="excluir-unidade-medida" data-id="' + um.id + '">Excluir</button>';
    html += '</td></tr>';
  });
  html += '</tbody></table>';
  container.innerHTML = html;
}

function salvarUnidadeMedida() {
  const nome = document.getElementById('modal-unidademedida-nome').value.trim();
  const id   = document.getElementById('modal-unidademedida-id').value;
  if (!nome) { showToast('yellow', 'Atenção', 'Nome da unidade de medida é obrigatório'); return; }
  const url = id ? BASE_URL + '/unidades-medida/update/' + id : BASE_URL + '/unidades-medida/store';
  fetch(url, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN + '&unidademedida=' + encodeURIComponent(nome)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Sucesso', id ? 'Unidade de medida atualizada!' : 'Unidade de medida criada!');
      document.getElementById('modal-unidademedida-nome').value = '';
      document.getElementById('modal-unidademedida-id').value   = '';
      carregarUnidadesMedidaModal();
    } else {
      showToast('red', 'Erro', data.message || data.error || 'Erro ao salvar unidade de medida');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

function editarUnidadeMedida(id, nome) {
  document.getElementById('modal-unidademedida-nome').value = nome;
  document.getElementById('modal-unidademedida-id').value   = id;
  document.getElementById('modal-unidademedida-nome').focus();
}

function excluirUnidadeMedida(id) {
  if (!confirm('Tem certeza que deseja excluir esta unidade de medida?')) return;
  fetch(BASE_URL + '/unidades-medida/delete/' + id, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluído', 'Unidade de medida excluída com sucesso');
      carregarUnidadesMedidaModal();
    } else {
      showToast('red', 'Erro', data.message || data.error || 'Erro ao excluir unidade de medida');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

// Carrega a lista assim que o modal abre
const modalUnidadeMedidaEl = document.getElementById('modal-unidademedida');
if (modalUnidadeMedidaEl) {
  new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class' && modalUnidadeMedidaEl.classList.contains('open')) {
        carregarUnidadesMedidaModal();
      }
    });
  }).observe(modalUnidadeMedidaEl, { attributes: true });
}

document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.registerActions === 'function') {
    window.registerActions({
      'toggle-planilha': (el) => { togglePlanilha(el.dataset.id, el); },
      'edit-planilha': (el) => { editPlanilha(el.dataset.id); },
      'delete-planilha': (el) => { deletePlanilha(el.dataset.id); },
      'bulk-delete-planilha': () => { bulkDeletePlanilhas(); },
      'salvar-unidade-medida': () => { salvarUnidadeMedida(); },
      'editar-unidade-medida': (el) => { editarUnidadeMedida(el.dataset.id, el.dataset.nome); },
      'excluir-unidade-medida': (el) => { excluirUnidadeMedida(el.dataset.id); },
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

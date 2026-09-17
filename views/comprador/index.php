<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';

$rows = [];
if (!empty($demandantes)) {
    $actionBtns = renderTableActions('default', 'demandante', \App\Auth\Rbac::check('demandantes.editar'), \App\Auth\Rbac::check('demandantes.excluir'));
    foreach ($demandantes as $d) {
        $rows[] = [
            'data-id' => $d['id'],
            ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $d['id'] . '">'],
            ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($d['nome'] ?? '-') . '</span>'],
            htmlspecialchars($d['cliente_nome'] ?? '-'),
            htmlspecialchars($d['telefone'] ?? '-'),
            htmlspecialchars($d['email'] ?? '-'),
            ['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($d['status'] ? 'btn-green' : 'btn-red') . '" data-action="toggle-demandante" data-id="' . $d['id'] . '">' . ($d['status'] ? 'Ativo' : 'Inativo') . '</button>'],
        ];
    }
}

$baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
$search = $_GET['search'] ?? '';
?>

    <section class="section active" id="sec-demandantes">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Compradores</div>
          <div class="section-sub">Cadastro de compradores vinculados a clientes</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Compradores</span>
          <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-demandante">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/compradores/create" class="btn btn-sm btn-cyan">Novo</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($demandantes)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <div>Nenhum comprador encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-demandantes',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar comprador, cliente ou email...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                                    ['label' => 'Nome', 'sortable' => true],
                  ['label' => 'Cliente', 'sortable' => true],
                  ['label' => 'Telefone', 'sortable' => true],
                  ['label' => 'Email', 'sortable' => true],
                  ['label' => 'Status']
              ],
              'actionBtns' => $actionBtns,
              'rows' => $rows
          ]) ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

<script>
function toggleDemandante(id, btn) {
  fetch(BASE_URL + '/compradores/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Comprador ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editDemandante(id) {
  window.location.href = BASE_URL + '/compradores/edit/' + id;
}

function deleteDemandante(id) {
  if (!confirm('Excluir este comprador?')) return;
  fetch(BASE_URL + '/compradores/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Comprador excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeleteDemandantes() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' comprador(es)?')) return;
  fetch(BASE_URL + '/compradores/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' comprador(es) excluido(s)');
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
      'toggle-demandante': (el) => { toggleDemandante(el.dataset.id, el); },
      'edit-demandante': (el) => { editDemandante(el.dataset.id); },
      'delete-demandante': (el) => { deleteDemandante(el.dataset.id); },
      'bulk-delete-demandante': () => { bulkDeleteDemandantes(); },
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

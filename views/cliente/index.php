<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';

$actionBtns = renderTableActions('default', 'cliente', \App\Auth\Rbac::check('clientes.editar'), \App\Auth\Rbac::check('clientes.excluir'));
$rows = [];
foreach ($clientes as $c) {
    $rows[] = [
        'data-id' => $c['id'],
        ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $c['id'] . '">'],
        ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($c['nome_fantasia'] ?? $c['razao_social'] ?? '-') . '</span>'],
        htmlspecialchars($c['cpf_cnpj'] ?? '-'),
        htmlspecialchars($c['email'] ?? '-'),
        htmlspecialchars($c['cidade'] ?? '-'),
        ['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($c['status'] ? 'btn-green' : 'btn-red') . '" data-action="toggle-cliente" data-id="' . $c['id'] . '">' . ($c['status'] ? 'Ativo' : 'Inativo') . '</button>'],
    ];
}
?>

    <section class="section active" id="sec-clientes">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Clientes</div>
          <div class="section-sub">Cadastro de clientes com CEP/CNPJ</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Clientes</span>
          <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-cliente">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/clientes/create" class="btn btn-sm btn-cyan">Novo</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($clientes)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <div>Nenhum cliente encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-clientes',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar cliente, CNPJ ou email...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                  ['label' => 'Nome', 'sortable' => true],
                  ['label' => 'CNPJ/CPF', 'sortable' => true],
                  ['label' => 'Email', 'sortable' => true],
                  ['label' => 'Cidade', 'sortable' => true],
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
function toggleCliente(id, btn) {
  fetch(BASE_URL + '/clientes/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Status Atualizado', 'Cliente ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro desconhecido');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function editCliente(id) {
  window.location.href = BASE_URL + '/clientes/edit/' + id;
}

function deleteCliente(id) {
  if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
  fetch(BASE_URL + '/clientes/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Cliente excluido com sucesso');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir cliente');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function bulkDeleteClientes() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' cliente(s)?')) return;
  fetch(BASE_URL + '/clientes/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' cliente(s) excluido(s)');
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
      'toggle-cliente': (el) => { toggleCliente(el.dataset.id, el); },
      'edit-cliente': (el) => { editCliente(el.dataset.id); },
      'delete-cliente': (el) => { deleteCliente(el.dataset.id); },
      'bulk-delete-cliente': () => { bulkDeleteClientes(); },
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

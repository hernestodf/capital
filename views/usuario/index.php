<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

$rows = [];
if (!empty($usuarios)) {
    $actionBtns = renderTableActions('default', 'usuario', \App\Auth\Rbac::check('usuarios.edit'), \App\Auth\Rbac::check('usuarios.delete'));
    foreach ($usuarios as $u) {
        // Badge de Role
        $roleBadge = renderBadge([
            'label' => ucfirst($u['role']),
            'variant' => match($u['role']) {
                'administrativo' => 'purple',
                'comercial' => 'green',
                default => 'cyan'
            },
            'size' => 'sm'
        ]);
        
        $rows[] = [
            'data-id' => $u['id'],
            ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $u['id'] . '">'],
            ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($u['name'] ?? '-') . '</span>'],
            htmlspecialchars($u['email'] ?? '-'),
            ['html' => true, 'content' => $roleBadge],
            ['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($u['status'] ? 'btn-green' : 'btn-red') . '" data-action="toggle-usuario" data-id="' . $u['id'] . '">' . ($u['status'] ? 'Ativo' : 'Inativo') . '</button>'],
        ];
    }
}

$search = $search ?? '';
?>

    <section class="section active" id="sec-usuarios">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Usuários</div>
          <div class="section-sub">Cadastro e controle de acesso</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Usuarios Cadastrados</span>
          <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-usuario">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/usuarios/create" class="btn btn-sm btn-cyan">Novo Usuário</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($usuarios)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              <div>Nenhum usuário encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Usuário" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-usuarios',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar usuário, email ou função...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                                    ['label' => 'Nome', 'sortable' => true],
                  ['label' => 'Email', 'sortable' => true],
                  ['label' => 'Função', 'sortable' => true],
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
function toggleUsuario(id, btn) {
  fetch(BASE_URL + '/usuarios/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Usuario ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editUsuario(id) {
  window.location.href = BASE_URL + '/usuarios/edit/' + id;
}

function deleteUsuario(id) {
  if (!confirm('Excluir este usuario?')) return;
  fetch(BASE_URL + '/usuarios/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Usuario excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeleteUsuarios() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' usuario(s)?')) return;
  fetch(BASE_URL + '/usuarios/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' usuario(s) excluido(s)');
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
      'toggle-usuario': (el) => { toggleUsuario(el.dataset.id, el); },
      'edit-usuario': (el) => { editUsuario(el.dataset.id); },
      'delete-usuario': (el) => { deleteUsuario(el.dataset.id); },
      'bulk-delete-usuario': () => { bulkDeleteUsuarios(); },
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

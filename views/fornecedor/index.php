<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';

// Preparar opções de categoria para os modais
$categoriaOptions = '';
$categoriaOptionsModal = '';
if (!empty($categorias)) {
    foreach ($categorias as $cat) {
        $catHtml = htmlspecialchars($cat['categoria']);
        $categoriaOptions .= '<option value="' . $cat['id'] . '">' . $catHtml . '</option>';
        $categoriaOptionsModal .= '<option value="' . $cat['id'] . '">' . $catHtml . '</option>';
    }
}

$rows = [];
if (!empty($fornecedores)) {
    $actionBtns = renderTableActions('default', 'fornecedor', \App\Auth\Rbac::check('fornecedores.editar'), \App\Auth\Rbac::check('fornecedores.excluir'));
    foreach ($fornecedores as $f) {
        $rows[] = [
            'data-id' => $f['id'],
            ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $f['id'] . '">'],
            ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($f['nome_fantasia'] ?? $f['razao_social'] ?? '-') . '</span>'],
            htmlspecialchars($f['cpf_cnpj'] ?? '-'),
            htmlspecialchars($f['categoria_nome'] ?? '-'),
            htmlspecialchars($f['subcategoria_nome'] ?? '-'),
            htmlspecialchars($f['cidade'] ?? '-'),
            ['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($f['status'] ? 'btn-green' : 'btn-red') . '" data-action="toggle-fornecedor" data-id="' . $f['id'] . '">' . ($f['status'] ? 'Ativo' : 'Inativo') . '</button>'],
        ];
    }
}
?>

    <section class="section active" id="sec-fornecedores">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Fornecedores</div>
          <div class="section-sub">Cadastro de fornecedores com categoria e subcategoria</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Fornecedores Cadastrados</span>
          <div style="display:flex;gap:8px">
            <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-categoria">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Categoria
            </button>
            <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-subcategoria">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Subcategoria
            </button>
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-fornecedor">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/fornecedores/create" class="btn btn-sm btn-cyan">Novo Fornecedor</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($fornecedores)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
              </svg>
              <div>Nenhum fornecedor encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Fornecedor" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'               => 'tbl-fornecedores',
              'searchable'       => true,
              'searchPlaceholder'=> 'Buscar fornecedor, CNPJ ou email...',
              'paginated'        => true,
              'perPage'          => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'html' => true],
                                    ['label' => 'Nome Fantasia', 'sortable' => true],
                  ['label' => 'CNPJ/CPF', 'sortable' => true],
                  ['label' => 'Categoria', 'sortable' => true],
                  ['label' => 'Subcategoria', 'sortable' => true],
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

    <!-- MODAL: Gerenciar Categorias -->
    <?= renderModal([
        'id' => 'modal-categoria',
        'variant' => 'form',
        'title' => 'Gerenciar Categorias',
        'subtitle' => 'Adicionar, editar ou excluir categorias',
        'body' => '<div id="modal-categoria-list" style="max-height:300px;overflow-y:auto;margin-bottom:16px"></div>
                   <div class="divider" style="margin:12px 0"></div>
                   <div class="fg"><div class="fl">Nome da Categoria</div><input type="text" id="modal-categoria-nome" class="fi" placeholder="Ex: Materiais de Escritório"/></div>
                   <input type="hidden" id="modal-categoria-id" value=""/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-categoria">Fechar</button><button class="btn btn-cyan" data-action="save-categoria">Salvar</button>'
    ]) ?>

    <!-- MODAL: Gerenciar Subcategorias -->
    <?= renderModal([
        'id' => 'modal-subcategoria',
        'variant' => 'form',
        'title' => 'Gerenciar Subcategorias',
        'subtitle' => 'Adicionar, editar ou excluir subcategorias',
        'body' => '<div id="modal-subcategoria-list" style="max-height:300px;overflow-y:auto;margin-bottom:16px"></div>
                   <div class="divider" style="margin:12px 0"></div>
                   <div class="fg"><div class="fl">Categoria</div><select id="modal-subcategoria-categoria" class="fi" onchange="carregarSubcategoriasModal()"><option value="">Selecione uma categoria</option>' . $categoriaOptionsModal . '</select></div>
                   <div class="fg" style="margin-top:12px"><div class="fl">Nome da Subcategoria</div><input type="text" id="modal-subcategoria-nome" class="fi" placeholder="Ex: Papel Sulfite"/></div>
                   <input type="hidden" id="modal-subcategoria-id" value=""/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-subcategoria">Fechar</button><button class="btn btn-cyan" data-action="save-subcategoria">Salvar</button>'
    ]) ?>

<script>
function toggleFornecedor(id, btn) {
  fetch(BASE_URL + '/fornecedores/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Fornecedor ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editFornecedor(id) {
  window.location.href = BASE_URL + '/fornecedores/edit/' + id;
}

function deleteFornecedor(id) {
  if (!confirm('Excluir este fornecedor?')) return;
  fetch(BASE_URL + '/fornecedores/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Fornecedor excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeleteFornecedors() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' fornecedor(s)?')) return;
  fetch(BASE_URL + '/fornecedores/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' fornecedor(s) excluido(s)');
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
      'toggle-fornecedor': (el) => { toggleFornecedor(el.dataset.id, el); },
      'edit-fornecedor': (el) => { editFornecedor(el.dataset.id); },
      'delete-fornecedor': (el) => { deleteFornecedor(el.dataset.id); },
      'bulk-delete-fornecedor': () => { bulkDeleteFornecedors(); },
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

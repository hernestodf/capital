<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';

$rows = [];
if (!empty($demandantes)) {
    $actionBtns = renderTableActions('default');
    foreach ($demandantes as $d) {
        $rows[] = [
            $d['id'],
            ['html' => true, 'content' => '<span class="td-name">' . htmlspecialchars($d['nome'] ?? '-') . '</span>'],
            htmlspecialchars($d['cliente_nome'] ?? '-'),
            htmlspecialchars($d['telefone'] ?? '-'),
            htmlspecialchars($d['email'] ?? '-'),
            ['html' => true, 'content' => '<button type="button" class="btn btn-sm ' . ($d['status'] ? 'btn-green' : 'btn-red') . '" onclick="toggleDemandante(' . $d['id'] . ', this)">' . ($d['status'] ? 'Ativo' : 'Inativo') . '</button>'],
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
          <a href="<?= $baseUrl ?>/demandantes/create" class="btn btn-sm btn-cyan">Novo</a>
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
                  ['label' => 'ID', 'sortable' => true],
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
// Toggle status
function toggleDemandante(id, btn) {
  fetch(BASE_URL + '/demandantes/toggle/' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Status Atualizado', 'Comprador ' + (data.status ? 'ativado' : 'desativado') + ' com sucesso');
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao alterar status');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

// Handlers nomeados para ações
function editDemandante(demandanteId) {
  window.location.href = BASE_URL + '/demandantes/edit/' + demandanteId;
}

function deleteDemandante(demandanteId) {
  if (confirm('Tem certeza que deseja excluir este comprador?')) {
    fetch(BASE_URL + '/demandantes/delete/' + demandanteId, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('green', 'Excluído', 'Comprador excluído com sucesso');
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao excluir comprador');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
  }
}

// Adiciona onclick handlers para os botões do dropdown após a tabela renderizar
document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('tbl-demandantes');
  if (!table) return;

  const rows = table.querySelectorAll('tbody tr');
  rows.forEach(function(row, idx) {
    const demandanteId = row.querySelector('td:first-child')?.textContent?.trim();
    if (!demandanteId) return;

    const inlineButtons = row.querySelectorAll('.td-actions .btn-sm:not(.td-act-toggle)');
    inlineButtons.forEach(function(btn) {
      const text = btn.textContent?.trim();
      if (text === 'Editar') {
        btn.setAttribute('onclick', 'editDemandante(' + demandanteId + ')');
        btn.style.cursor = 'pointer';
      } else if (text === 'Excluir') {
        btn.setAttribute('onclick', 'deleteDemandante(' + demandanteId + ')');
        btn.style.cursor = 'pointer';
      }
    });

    const dropdown = row.querySelector('.td-act-dropdown');
    if (dropdown) {
      const dropButtons = dropdown.querySelectorAll('.td-act-item');
      dropButtons.forEach(function(btn) {
        const label = btn.querySelector('span')?.textContent?.trim() || btn.textContent?.trim();
        if (label === 'Editar') {
          btn.setAttribute('onclick', 'editDemandante(' + demandanteId + ')');
        } else if (label === 'Excluir') {
          btn.setAttribute('onclick', 'deleteDemandante(' + demandanteId + ')');
        }
      });
    }
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

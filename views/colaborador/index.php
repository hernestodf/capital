<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/table/table.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

$baseUrl = \App\Core\Env::get('BASE_URL', '');

// ── Utilitário: iniciais do nome ──────────────────────────────────────────────
function colaboradorInitials(string $nome): string {
    $parts = array_filter(explode(' ', trim($nome)));
    if (count($parts) >= 2) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }
    return mb_strtoupper(mb_substr($nome, 0, 2));
}

// ── Cor de avatar por ID (paleta interna — sem global) ────────────────────────
function avatarColor(int $id): string {
    $c = ['#0ea5e9','#8b5cf6','#ec4899','#f59e0b','#10b981','#ef4444','#6366f1','#14b8a6'];
    return $c[$id % count($c)];
}

// Preparar rows da tabela
$rows = [];
foreach ($colaboradores as $c) {
    $verificacaoBadge = '';
    if (!empty($c['verificacao'])) {
        $status = $c['verificacao']['status'];
        $statusMap = [
            'pendente'   => ['label' => 'Pendente',   'variant' => 'yellow'],
            'acessado'   => ['label' => 'Acessado',   'variant' => 'blue'],
            'verificado' => ['label' => 'Verificado', 'variant' => 'green'],
            'expirado'   => ['label' => 'Expirado',   'variant' => 'red'],
        ];
        $s = $statusMap[$status] ?? ['label' => ucfirst($status), 'variant' => 'yellow'];
        $verificacaoBadge = renderBadge(['label' => $s['label'], 'variant' => $s['variant'], 'size' => 'sm']);
    } else {
        $verificacaoBadge = renderBadge(['label' => 'Sem envio', 'variant' => 'yellow', 'size' => 'sm']);
    }

    $tipoBadge = renderBadge([
        'label'   => $c['tipo'] === 'FUNCIONARIO' ? 'Funcionario' : 'Freelance',
        'variant' => $c['tipo'] === 'FUNCIONARIO' ? 'cyan' : 'purple',
        'size'    => 'sm',
    ]);

    $statusBtn = '<button type="button" class="btn btn-sm ' . ($c['ativo'] ? 'btn-green' : 'btn-red') . '" data-action="toggle-colaborador" data-id="' . $c['id'] . '">'
        . ($c['ativo'] ? 'Ativo' : 'Inativo') . '</button>';

    $enviarLinkBtn = '';
    if (!empty($c['verificacao'])) {
        if (in_array($c['verificacao']['status'], ['pendente', 'verificado'])) {
            $enviarLinkBtn = '<button type="button" class="btn btn-sm btn-yellow" data-action="reenviar-link" data-id="' . $c['id'] . '">Reenviar Email</button>';
        } else {
$enviarLinkBtn = '<button type="button" class="btn btn-sm btn-cyan" data-action="enviar-link" data-id="' . $c['id'] . '">Enviar Email</button>';
        }
    } else {
        $enviarLinkBtn = '<button type="button" class="btn btn-sm btn-cyan" data-action="enviar-link" data-id="' . $c['id'] . '">Enviar Email</button>';
    }

    $estadoBadge = !empty($c['estado_para_trabalho'])
        ? renderBadge(['label' => $c['estado_para_trabalho'], 'variant' => ($c['estado_para_trabalho'] === 'Todos' ? 'green' : 'cyan'), 'size' => 'sm'])
        : '<span style="color:var(--text-4);font-size:12px">—</span>';

    // ── Avatar thumbnail ──────────────────────────────────────────────────────
    $temFoto    = !empty($c['foto']);
    $initials   = colaboradorInitials($c['nome'] ?? '?');
    $bgColor    = avatarColor((int)$c['id']);
    $fotoUrl    = $baseUrl . '/colaboradores/foto/' . $c['id'];
    $nomeEsc    = htmlspecialchars($c['nome'] ?? '', ENT_QUOTES);

    if ($temFoto) {
        $avatarHtml = '<div class="colab-avatar-wrap" data-action="lb-open" data-url="' . $fotoUrl . '" data-nome="' . $nomeEsc . '" title="Ampliar foto de ' . $nomeEsc . '">'
            . '<img src="' . $fotoUrl . '" alt="' . $nomeEsc . '" class="colab-avatar-img" loading="lazy" onerror="this.parentElement.innerHTML=\'<div class=&quot;colab-avatar-ph&quot; style=&quot;background:' . $bgColor . '&quot;>' . $initials . '</div>\'">'
            . '<div class="colab-avatar-zoom"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></div>'
            . '</div>';
    } else {
        $avatarHtml = '<div class="colab-avatar-ph" style="background:' . $bgColor . '">' . $initials . '</div>';
    }

    // ── Célula principal: avatar + info ───────────────────────────────────────
    $colaboradorInfo  = '<div style="display:flex;align-items:center;gap:12px">';
    $colaboradorInfo .= $avatarHtml;
    $colaboradorInfo .= '<div style="line-height:1.6">';
    $colaboradorInfo .= '<span class="td-name" style="font-weight:600">' . htmlspecialchars($c['nome'] ?? '-') . '</span><br>';
    $colaboradorInfo .= '<span style="font-size:12px;color:var(--text-3)">' . htmlspecialchars($c['email'] ?? '-') . '</span><br>';
    $colaboradorInfo .= '<span style="font-size:12px;color:var(--text-3)">' . htmlspecialchars($c['telefone'] ?? '-') . '</span>';
    if (!empty($c['atua_como'])) {
        $colaboradorInfo .= '<br>';
        foreach (explode(',', $c['atua_como']) as $atua) {
            $t = trim($atua);
            if ($t) $colaboradorInfo .= '<span class="badge sm cyan">' . htmlspecialchars($t) . '</span>';
        }
    }
    $colaboradorInfo .= '</div></div>';

    $rows[] = [
        'data-id' => $c['id'],
        ['html' => true, 'content' => '<input type="checkbox" class="row-check" value="' . $c['id'] . '">'],
        ['html' => true, 'content' => $colaboradorInfo],
        ['html' => true, 'content' => $tipoBadge],
        ['html' => true, 'content' => $estadoBadge],
        ['html' => true, 'content' => $verificacaoBadge],
        ['html' => true, 'content' => $statusBtn],
        ['html' => true, 'content' => $enviarLinkBtn],
    ];
}
?>

<!-- ── Estilos: avatar + lightbox ──────────────────────────────────────────── -->
<style>
/* Avatar */
.colab-avatar-wrap {
  position: relative;
  width: 52px; height: 52px;
  border-radius: 10px;
  overflow: hidden;
  flex-shrink: 0;
  cursor: zoom-in;
  border: 2px solid var(--bg-border, #e2e8f0);
}
.colab-avatar-img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .25s ease;
}
.colab-avatar-wrap:hover .colab-avatar-img { transform: scale(1.08); }
.colab-avatar-zoom {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.45);
  display: flex; align-items: center; justify-content: center;
  opacity: 0;
  transition: opacity .2s;
}
.colab-avatar-wrap:hover .colab-avatar-zoom { opacity: 1; }

/* Placeholder de iniciais */
.colab-avatar-ph {
  width: 52px; height: 52px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px; font-weight: 700; color: #fff;
  flex-shrink: 0;
  user-select: none;
  letter-spacing: .5px;
}

/* Lightbox overlay */
#lb-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.85);
  align-items: center; justify-content: center;
  padding: 24px;
  animation: lbFadeIn .2s ease;
}
#lb-overlay.open { display: flex; }
@keyframes lbFadeIn { from { opacity:0 } to { opacity:1 } }

#lb-img-wrap {
  position: relative;
  max-width: min(520px, 90vw);
  max-height: 90vh;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 32px 80px rgba(0,0,0,.7);
  animation: lbZoomIn .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes lbZoomIn { from { transform:scale(.88);opacity:0 } to { transform:scale(1);opacity:1 } }

#lb-img {
  display: block;
  max-width: 100%; max-height: 80vh;
  object-fit: contain;
  background: #111;
}

#lb-caption {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 10px 16px;
  background: linear-gradient(transparent, rgba(0,0,0,.7));
  color: #fff;
  font-size: 13px; font-weight: 500;
  text-align: center;
}

#lb-close {
  position: absolute; top: -44px; right: 0;
  width: 36px; height: 36px;
  background: rgba(255,255,255,.12);
  border: none; border-radius: 50%;
  color: #fff; font-size: 20px; line-height: 1;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
#lb-close:hover { background: rgba(255,255,255,.25); }
</style>

<!-- ── Lightbox HTML ────────────────────────────────────────────────────────── -->
<div id="lb-overlay" data-action="lb-close-on-bg">
  <div id="lb-img-wrap">
    <button id="lb-close" data-action="lb-close" title="Fechar (Esc)">×</button>
    <img id="lb-img" src="" alt="">
    <div id="lb-caption"></div>
  </div>
</div>

    <section class="section active" id="sec-colaboradores">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Gerenciar Colaboradores</div>
          <div class="section-sub">Funcionários e freelancers com verificação via email</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Colaboradores Cadastrados</span>
          <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="btn btn-sm btn-red" id="btn-bulk-delete" style="display:none" data-action="bulk-delete-colaborador">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Excluir (<span id="bulk-count">0</span>)
            </button>
            <a href="<?= $baseUrl ?>/colaboradores/create" class="btn btn-sm btn-cyan">Novo Colaborador</a>
          </div>
        </div>
        <div class="card-body" style="padding:0">
          <?php if (empty($colaboradores)): ?>
          <div class="table-empty">
            <div class="table-empty-flex">
              <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <div>Nenhum colaborador encontrado</div>
              <div style="font-size:12px;color:var(--text-4)">Clique em "Novo Colaborador" para adicionar</div>
            </div>
          </div>
          <?php else: ?>
          <?= renderTable([
              'id'                => 'tbl-colaboradores',
              'searchable'        => true,
              'searchPlaceholder' => 'Buscar colaborador, email ou telefone...',
              'paginated'         => true,
              'perPage'           => 15,
              'headers' => [
                  ['label' => '<input type="checkbox" id="select-all">', 'sortable' => false],
                  ['label' => 'Colaborador',  'sortable' => true],
                  ['label' => 'Tipo',         'sortable' => true],
                  ['label' => 'Estado',       'sortable' => true],
                  ['label' => 'Verificação',  'sortable' => true],
                  ['label' => 'Status',       'sortable' => true],
                  ['label' => 'Email',        'sortable' => false],
              ],
              'rows'       => $rows,
              'actionBtns' => renderTableActions('default', 'colaborador', \App\Auth\Rbac::check('colaboradores.editar'), \App\Auth\Rbac::check('colaboradores.excluir')),
          ]) ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

<script>
function toggleColaborador(id, btn) {
  fetch(BASE_URL + '/colaboradores/toggle/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.className = 'btn btn-sm ' + (data.status ? 'btn-green' : 'btn-red');
      btn.textContent = data.status ? 'Ativo' : 'Inativo';
      showToast('green', 'Atualizado', 'Colaborador ' + (data.status ? 'ativado' : 'desativado'));
    } else {
      showToast('red', 'Erro', data.error || data.message || 'Erro');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function editColaborador(id) {
  window.location.href = BASE_URL + '/colaboradores/edit/' + id;
}

function deleteColaborador(id) {
  if (!confirm('Excluir este colaborador?')) return;
  fetch(BASE_URL + '/colaboradores/delete/' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Colaborador excluido');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar'));
}

function bulkDeleteColaboradors() {
  const checked = document.querySelectorAll('.row-check:checked');
  const ids = Array.from(checked).map(cb => cb.value);
  if (!ids.length) return;
  if (!confirm('Excluir ' + ids.length + ' colaborador(s)?')) return;
  fetch(BASE_URL + '/colaboradores/bulk-delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf_token=' + CSRF_TOKEN + '&ids=' + JSON.stringify(ids)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', ids.length + ' colaborador(s) excluido(s)');
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
      'toggle-colaborador': (el) => { toggleColaborador(el.dataset.id, el); },
      'edit-colaborador': (el) => { editColaborador(el.dataset.id); },
      'delete-colaborador': (el) => { deleteColaborador(el.dataset.id); },
      'bulk-delete-colaborador': () => { bulkDeleteColaboradors(); },
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

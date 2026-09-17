<?php
$pageStyles = '
.subloc-section { margin-bottom: 28px; }
.subloc-fornecedor-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: var(--surface2, #f3f4f6);
  border-radius: 8px 8px 0 0;
  border-left: 4px solid var(--cyan, #06b6d4);
}
.subloc-fornecedor-name { font-weight: 600; font-size: 15px; }
.subloc-badge-count {
  background: var(--cyan, #06b6d4);
  color: #fff;
  border-radius: 999px;
  padding: 1px 9px;
  font-size: 12px;
  font-weight: 600;
}
.subloc-table { width: 100%; border-collapse: collapse; }
.subloc-table th {
  background: var(--surface1, #f9fafb);
  text-align: left;
  padding: 9px 14px;
  font-size: 12px;
  font-weight: 600;
  color: var(--muted, #6b7280);
  text-transform: uppercase;
  letter-spacing: .04em;
  border-bottom: 1px solid var(--border, #e5e7eb);
}
.subloc-table td {
  padding: 10px 14px;
  font-size: 13.5px;
  border-bottom: 1px solid var(--border, #e5e7eb);
  vertical-align: middle;
}
.subloc-table tr:last-child td { border-bottom: none; }
.subloc-table tbody tr:hover { background: var(--surface2, #f9fafb); }
.subloc-wrap { border: 1px solid var(--border, #e5e7eb); border-radius: 0 0 8px 8px; }
.badge-pago     { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
.badge-pendente { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
.badge-devolvido { background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
.row-devolvido { opacity: .55; }
.row-devolvido td { text-decoration: line-through; text-decoration-color: rgba(0,0,0,.15); }
.evento-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--surface2, #f3f4f6);
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 6px;
  padding: 3px 10px;
  font-size: 12.5px;
  cursor: pointer;
  transition: background .15s;
  max-width: 220px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  user-select: none;
}
.evento-badge:hover { background: var(--surface3, #e5e7eb); }
.evento-badge svg { flex-shrink: 0; }
.popover-box {
  display: none;
  position: fixed;
  z-index: 9999;
  background: #fff;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 10px;
  box-shadow: 0 8px 28px rgba(0,0,0,.16);
  padding: 14px 16px;
  min-width: 240px;
  max-width: 300px;
  pointer-events: none;
}
.popover-box.aberto { display: block; pointer-events: auto; }
.popover-title { font-weight: 700; font-size: 13.5px; margin-bottom: 8px; color: var(--text, #111827); }
.popover-row { display: flex; gap: 6px; font-size: 12px; color: var(--muted, #6b7280); margin-bottom: 5px; }
.popover-row:last-child { margin-bottom: 0; }
.popover-row strong { color: var(--text, #374151); min-width: 60px; }
.btn-devolver {
  background: none;
  border: 1px solid var(--cyan, #06b6d4);
  color: var(--cyan, #06b6d4);
  border-radius: 6px;
  padding: 4px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: background .15s, color .15s;
  white-space: nowrap;
}
.btn-devolver:hover { background: var(--cyan, #06b6d4); color: #fff; }
.btn-devolver:disabled { opacity: .5; cursor: default; }
.btn-devolver:disabled:hover { background: none; color: var(--cyan, #06b6d4); }
.subloc-empty {
  text-align: center;
  padding: 48px 0;
  color: var(--muted, #9ca3af);
  font-size: 15px;
}
.subloc-empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: .4; display: block; margin-left: auto; margin-right: auto; }
.prod-nome { font-weight: 500; font-size: 13.5px; }
.prod-sub { font-size: 11.5px; color: var(--muted, #6b7280); margin-top: 2px; }
.prod-sub span { font-style: italic; }
.prod-label { font-weight: 600; font-style: normal; margin-right: 3px; }
.prod-serial { font-size: 11px; color: var(--muted, #9ca3af); margin-top: 2px; font-family: monospace; }
/* Filtros */
.subloc-filtros {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 16px;
  background: var(--surface1, #f9fafb);
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 8px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.subloc-filtros label {
  font-size: 12px;
  font-weight: 600;
  color: var(--muted, #6b7280);
  text-transform: uppercase;
  letter-spacing: .03em;
}
.subloc-filtros select {
  height: 30px;
  font-size: 12.5px;
  padding: 0 8px;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 6px;
  background: #fff;
  color: var(--text, #111827);
  cursor: pointer;
}
.subloc-acao-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  background: rgba(6,182,212,0.06);
  border: 1px solid rgba(6,182,212,0.2);
  border-radius: 8px;
  margin-bottom: 20px;
}
.subloc-acao-bar:empty { display: none; }
.subloc-acao-bar .btn { font-size: 12px; padding: 5px 14px; }
';
?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

    <section class="section active" id="sec-sublocacoes">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Sublocações</div>
          <div class="section-sub">Itens de fornecedores sublocados</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Filtros independentes -->
      <div class="subloc-filtros">
        <label for="filtro-devolucao">Devolução:</label>
        <select id="filtro-devolucao" onchange="sublocFiltrar()">
          <option value="todos" selected>Todos</option>
          <option value="aberto">Em Aberto</option>
          <option value="devolvido">Devolvidos</option>
        </select>
        <label for="filtro-pagamento" style="margin-left:8px">Pagamento:</label>
        <select id="filtro-pagamento" onchange="sublocFiltrar()">
          <option value="todos" selected>Todos</option>
          <option value="pendente">Pendente</option>
          <option value="pago">Pago</option>
        </select>
      </div>

      <!-- Barra de ação em massa -->
      <div class="subloc-acao-bar" id="subloc-acao-bar" style="display:none">
        <span id="subloc-selecionados-count" style="font-size:12.5px;font-weight:600;color:var(--text-1)">0 selecionados</span>
        <button type="button" class="btn btn-cyan" id="btn-devolver-em-massa" data-action="devolver-em-massa">
          Marcar devolvidos
        </button>
      </div>

      <?php if (empty($fornecedores)): ?>
      <div class="subloc-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>Nenhum item sublocado.</div>
      </div>
      <?php else: ?>

      <?php foreach ($fornecedores as $fid => $grupo): ?>
      <?php
        $totalItens      = count($grupo['itens']);
        $naoDevolvidos   = array_values(array_filter($grupo['itens'], fn($it) => $it['devolvido_em'] === null));
        $totalPendentes  = count(array_values(array_filter($naoDevolvidos, fn($it) => $it['status_pagamento'] !== 'pago')));
      ?>
      <div class="subloc-section" id="grupo-<?= $fid ?>">
        <div class="subloc-fornecedor-header">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:18px;height:18px;color:var(--cyan)">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
          </svg>
          <span class="subloc-fornecedor-name"><?= htmlspecialchars($grupo['fornecedor_nome']) ?></span>
          <span class="subloc-badge-count"><?= $totalItens ?> <?= $totalItens === 1 ? 'item' : 'itens' ?></span>
          <?php if ($totalPendentes > 0): ?>
          <span style="font-size:12px;color:var(--muted,#6b7280)"><?= $totalPendentes ?> pendente<?= $totalPendentes === 1 ? '' : 's' ?></span>
          <?php endif; ?>
        </div>
        <div class="subloc-wrap">
          <table class="subloc-table">
            <thead>
              <tr>
                <th style="width:36px"><input type="checkbox" class="subloc-check-all" data-grupo="<?= $fid ?>" onchange="sublocToggleGrupo(<?= $fid ?>, this.checked)"></th>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Pagamento</th>
                <th>Devolução</th>
                <th>Evento</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($grupo['itens'] as $item): ?>
            <?php
              $nomeEvento       = $item['nome_evento']          ?? '';
              $localEvento      = $item['local_evento']         ?? '';
              $nomeProduto      = $item['produto_evento']       ?? '';
              $nomeSublocacao   = $item['produto_sublocacao']   ?? '';
              $nomeFornecedor   = $item['produto_fornecedor']   ?? '';
              $codigoBarras     = $item['codigo_barras']         ?? '';
              $serial           = $item['serial_fornecedor']    ?? '';
              $dtInicio = $item['data_inicio'] ? date('d/m/Y', strtotime($item['data_inicio'])) : '—';
              $dtFim    = $item['data_fim']    ? date('d/m/Y', strtotime($item['data_fim']))    : '—';
              $jaDevolvido = $item['devolvido_em'] !== null;
              $rowClass = $jaDevolvido ? 'row-devolvido' : '';
            ?>
            <tr id="item-<?= $item['id'] ?>" class="<?= $rowClass ?>"
                data-devolvido="<?= $jaDevolvido ? '1' : '0' ?>"
                data-pagamento="<?= htmlspecialchars($item['status_pagamento']) ?>">
              <td style="text-align:center">
                <?php if (!$jaDevolvido): ?>
                <input type="checkbox" class="subloc-check" data-id="<?= $item['id'] ?>" data-grupo="<?= $fid ?>" onchange="sublocAtualizarAcao()">
                <?php endif; ?>
              </td>
              <td>
                <div class="prod-nome"><?= htmlspecialchars($nomeProduto ?: '—') ?></div>
                <?php if ($nomeSublocacao && $nomeSublocacao !== $nomeProduto): ?>
                <div class="prod-sub"><span class="prod-label">Subloc.:</span><span><?= htmlspecialchars($nomeSublocacao) ?></span></div>
                <?php endif; ?>
                <?php if ($nomeFornecedor): ?>
                <div class="prod-sub"><span class="prod-label">Forn.:</span><span><?= htmlspecialchars($nomeFornecedor) ?></span></div>
                <?php endif; ?>
                <?php if ($codigoBarras): ?>
                <div class="prod-sub"><span class="prod-label">Cód. barras:</span><span><?= htmlspecialchars($codigoBarras) ?></span></div>
                <?php endif; ?>
                <?php if ($serial && $serial !== $codigoBarras): ?>
                <div class="prod-sub"><span class="prod-label">Serial:</span><span><?= htmlspecialchars($serial) ?></span></div>
                <?php endif; ?>
              </td>
              <td><?= number_format((float)$item['quantidade'], 0) ?></td>
              <td>
                <?php if ($item['status_pagamento'] === 'pago'): ?>
                <span class="badge-pago">Pago</span>
                <?php else: ?>
                <span class="badge-pendente">Pendente</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($jaDevolvido): ?>
                <span class="badge-devolvido">Devolvido</span>
                <?php else: ?>
                <span style="font-size:11px;color:var(--muted,#9ca3af)">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="evento-badge" data-action="abrir-popover-evento">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <?= htmlspecialchars($nomeEvento) ?>
                </div>
                <div class="popover-box"
                     data-nome="<?= htmlspecialchars($nomeEvento, ENT_QUOTES) ?>"
                     data-local="<?= htmlspecialchars($localEvento ?: '—', ENT_QUOTES) ?>"
                     data-inicio="<?= htmlspecialchars($dtInicio, ENT_QUOTES) ?>"
                     data-fim="<?= htmlspecialchars($dtFim, ENT_QUOTES) ?>">
                  <div class="popover-title"></div>
                  <div class="popover-row"><strong>Local</strong><span class="p-local"></span></div>
                  <div class="popover-row"><strong>Início</strong><span class="p-inicio"></span></div>
                  <div class="popover-row"><strong>Fim</strong><span class="p-fim"></span></div>
                </div>
              </td>
              <td style="white-space:nowrap">
                <?php if (!$jaDevolvido): ?>
                <button class="btn-devolver"
                        data-action="marcar-devolvido" data-id="<?= $item['id'] ?>" data-fornecedor="<?= $fid ?>">
                  Devolvido
                </button>
                <?php else: ?>
                <span style="font-size:11px;color:var(--muted,#9ca3af)">✓</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>

      <?php endif; ?>
    </section>

<script>
const CSRF_SUBLOC = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_SUBLOC = '<?= rtrim(\App\Core\Env::get('BASE_URL', ''), '/') ?>';

let _popoverAberto = null;

function abrirPopoverEvento(badge) {
  const box = badge.nextElementSibling;

  if (_popoverAberto && _popoverAberto !== box) {
    _popoverAberto.classList.remove('aberto');
    _popoverAberto = null;
  }

  if (box.classList.contains('aberto')) {
    box.classList.remove('aberto');
    _popoverAberto = null;
    return;
  }

  box.querySelector('.popover-title').textContent  = box.dataset.nome;
  box.querySelector('.p-local').textContent        = box.dataset.local;
  box.querySelector('.p-inicio').textContent       = box.dataset.inicio;
  box.querySelector('.p-fim').textContent          = box.dataset.fim;

  box.classList.add('aberto');
  _popoverAberto = box;

  const rect = badge.getBoundingClientRect();
  const margin = 6;
  let top  = rect.bottom + margin + window.scrollY;
  let left = rect.left   + window.scrollX;

  box.style.top  = top  + 'px';
  box.style.left = left + 'px';

  requestAnimationFrame(() => {
    const bw = box.offsetWidth;
    const vw = window.innerWidth;
    if (left + bw > vw - 8) {
      box.style.left = Math.max(8, vw - bw - 8) + 'px';
    }
  });
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.evento-badge') && !e.target.closest('.popover-box')) {
    if (_popoverAberto) {
      _popoverAberto.classList.remove('aberto');
      _popoverAberto = null;
    }
  }
});

window.addEventListener('scroll', function() {
  if (_popoverAberto) {
    _popoverAberto.classList.remove('aberto');
    _popoverAberto = null;
  }
}, { passive: true });

// ---- FILTROS ----
function sublocFiltrar() {
  var devFiltro   = document.getElementById('filtro-devolucao').value;
  var pagFiltro   = document.getElementById('filtro-pagamento').value;
  var todos       = document.querySelectorAll('.subloc-section');
  var visibleSec = 0;

  todos.forEach(function(sec) {
    var trs = sec.querySelectorAll('tbody tr');
    var visibleRows = 0;

    trs.forEach(function(tr) {
      var isDev = tr.getAttribute('data-devolvido') === '1';
      var pag   = tr.getAttribute('data-pagamento');

      var matchDev = (devFiltro === 'todos') ||
                     (devFiltro === 'aberto' && !isDev) ||
                     (devFiltro === 'devolvido' && isDev);

      var matchPag = (pagFiltro === 'todos') ||
                     (pagFiltro === 'pendente' && pag !== 'pago') ||
                     (pagFiltro === 'pago' && pag === 'pago');

      if (matchDev && matchPag) {
        tr.style.display = '';
        visibleRows++;
      } else {
        tr.style.display = 'none';
      }
    });

    sec.style.display = visibleRows > 0 ? '' : 'none';
    if (visibleRows > 0) visibleSec++;

    // Atualiza badge do fornecedor
    var badge = sec.querySelector('.subloc-badge-count');
    if (badge) badge.textContent = visibleRows + (visibleRows === 1 ? ' item' : ' itens');
  });

  // Empty state se tudo filtrado
  var emptyEl = document.getElementById('subloc-filtros-empty');
  if (visibleSec === 0 && !emptyEl) {
    var secEl = document.getElementById('sec-sublocacoes');
    var div = document.createElement('div');
    div.id = 'subloc-filtros-empty';
    div.className = 'subloc-empty';
    div.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><div>Nenhum item encontrado para os filtros selecionados.</div>';
    secEl.appendChild(div);
  } else if (visibleSec > 0 && emptyEl) {
    emptyEl.remove();
  }

  sublocAtualizarAcao();
}

// ---- CHECKBOXES / AÇÃO EM MASSA ----
function sublocToggleGrupo(fid, checked) {
  var sec = document.getElementById('grupo-' + fid);
  if (!sec) return;
  sec.querySelectorAll('.subloc-check').forEach(function(cb) {
    if (!cb.closest('tr').classList.contains('row-devolvido')) {
      cb.checked = checked;
    }
  });
  sublocAtualizarAcao();
}

function sublocAtualizarAcao() {
  var checked = document.querySelectorAll('.subloc-check:checked');
  var bar     = document.getElementById('subloc-acao-bar');
  var countEl = document.getElementById('subloc-selecionados-count');
  var n       = checked.length;

  if (n > 0) {
    bar.style.display = 'flex';
    countEl.textContent = n + ' selecionado' + (n === 1 ? '' : 's');
  } else {
    bar.style.display = 'none';
  }
}

function devolverEmMassa() {
  var checked = document.querySelectorAll('.subloc-check:checked');
  if (!checked.length) return;

  var ids = [];
  checked.forEach(function(cb) { ids.push(parseInt(cb.dataset.id)); });

  if (!confirm('Marcar ' + ids.length + ' item(ns) como devolvido(s)?')) return;

  var btn = document.getElementById('btn-devolver-em-massa');
  if (btn) { btn.disabled = true; btn.textContent = 'Processando...'; }

  var promessas = ids.map(function(id) {
    var row = document.getElementById('item-' + id);
    var btnDev = row ? row.querySelector('.btn-devolver') : null;
    return marcarDevolvidoSilencioso(id, btnDev);
  });

  Promise.all(promessas).then(function(resultados) {
    var sucesso = resultados.filter(Boolean).length;
    var falhas  = resultados.length - sucesso;
    if (btn) { btn.disabled = false; btn.textContent = 'Marcar devolvidos'; }
    if (falhas === 0) {
      showToast('green', 'Sucesso', sucesso + ' item(ns) devolvido(s)!');
    } else {
      showToast('yellow', 'Atenção', sucesso + ' devolvido(s), ' + falhas + ' falharam.');
    }
    sublocAtualizarAcao();
  });
}

function marcarDevolvidoSilencioso(id, btn) {
  if (btn) { btn.disabled = true; btn.textContent = '...'; }

  return fetch(BASE_SUBLOC + '/sublocacoes/devolver/' + id, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: '_csrf_token=' + encodeURIComponent(CSRF_SUBLOC),
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.success) {
      if (btn) { btn.disabled = false; btn.textContent = 'Devolvido'; }
      return false;
    }
    _aplicarDevolucaoVisual(id, data.data && data.data.devolvido_em);
    return true;
  })
  .catch(function() {
    if (btn) { btn.disabled = false; btn.textContent = 'Devolvido'; }
    return false;
  });
}

function marcarDevolvido(id, idFornecedor, btn) {
  marcarDevolvidoSilencioso(id, btn).then(function(ok) {
    if (!ok) alert('Erro ao registrar devolução');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  // scripts.js (window.registerAction) carrega depois deste script no HTML;
  // registrar so apos DOMContentLoaded garante que ja esta disponivel.
  if (window.registerAction) {
    window.registerAction('marcar-devolvido', function(el) {
      marcarDevolvido(el.dataset.id, el.dataset.fornecedor, el);
    });
  }
});

function _aplicarDevolucaoVisual(id, devolvidoEm) {
  var row = document.getElementById('item-' + id);
  if (!row) return;

  row.classList.add('row-devolvido');
  row.setAttribute('data-devolvido', '1');

  // Remove checkbox entirely
  var cb = row.querySelector('.subloc-check');
  if (cb) cb.remove();

  // Troca botão Devolvido por checkmark
  var tdAcao = row.querySelector('td:last-child');
  var btnDev = tdAcao ? tdAcao.querySelector('.btn-devolver') : null;
  if (btnDev) {
    btnDev.outerHTML = '<span style="font-size:11px;color:var(--muted,#9ca3af)">✓</span>';
  }

  // Adiciona badge "Devolvido" na coluna devolução
  var tds = row.querySelectorAll('td');
  if (tds[4]) {
    tds[4].innerHTML = '<span class="badge-devolvido">Devolvido</span>';
  }

  // Atualiza contagem do fornecedor
  var sec = row.closest('.subloc-section');
  if (sec) {
    var badge = sec.querySelector('.subloc-badge-count');
    var trsVisiveis = sec.querySelectorAll('tbody tr:not([style*="display: none"])');
    var total = trsVisiveis.length;
    if (badge) badge.textContent = total + (total === 1 ? ' item' : ' itens');
  }
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

/**
 * JavaScript - Tab Switching (Horizontal + Vertical) + Popover
 * Modulo: Eventos > Editar
 * Usado por todas as tabs
 */

// Popover para salas/produtos
let _openPopSala = null;
function togglePopSala(id) {
  const el = document.getElementById(id);
  if (!el) return;
  if (_openPopSala && _openPopSala !== el) {
    _openPopSala.classList.remove('open');
    const prevWrap = _openPopSala.closest('.popover-wrap');
    if (prevWrap) prevWrap.classList.remove('popover-active');
    _openPopSala = null;
  }
  el.classList.toggle('open');
  const wrap = el.closest('.popover-wrap');
  if (wrap) {
    if (el.classList.contains('open')) {
      wrap.classList.add('popover-active');
    } else {
      wrap.classList.remove('popover-active');
    }
  }
  if (el.classList.contains('open')) {
    _openPopSala = el;
  } else {
    _openPopSala = null;
  }
}

// Tab horizontal switch
function switchHTab(idx, el) {
  document.querySelectorAll('.loc-htab').forEach((t) => { t.classList.remove('active'); });
  document.querySelectorAll('.loc-hpane').forEach((p) => { p.classList.remove('active'); });
  el.classList.add('active');
  const pane = document.getElementById('loc-hpane-' + idx);
  if (pane) { pane.classList.add('active'); }

  // Carregar conteudo da aba Fornecedores via AJAX quando ativada
  if (idx === 4 && pane) {
    refreshFornecedoresTab();
  }
}

// Recarregar conteudo da aba Fornecedores via AJAX
function refreshFornecedoresTab() {
  const container = document.getElementById('fornecedores-container');
  if (!container) return;
  const eventoId = window.EVENTO_ID;
  if (!eventoId) return;

  const baseUrl = window.BASE_URL;
  fetch(baseUrl + '/eventos/fornecedores-html/' + eventoId, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin'
  })
  .then((r) => { return r.json(); })
  .then((data) => {
    if (data.success && data.html) {
      container.innerHTML = data.html;
    }
  })
  .catch(() => {});
}

// Tab vertical switch
function switchVTab(idx, el) {
  document.querySelectorAll('.loc-vtab').forEach((t) => { t.classList.remove('active'); });
  document.querySelectorAll('.loc-vpane').forEach((p) => { p.classList.remove('active'); });
  el.classList.add('active');
  const pane = document.getElementById('loc-vpane-' + idx);
  if (pane) { pane.classList.add('active'); }
}

// Register actions for event delegation
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.registerActions === 'function') {
    window.registerActions({
      'switch-h-tab': (el) => { switchHTab(el.dataset.tab, el); },
      'switch-v-tab': (el) => { switchVTab(el.dataset.tab, el); },
      'toggle-pop': (el) => { togglePopSala(el.dataset.popId); },
      'toggle-pop-sala': (el) => { togglePopSala(el.dataset.popId); },
    });
  }
});

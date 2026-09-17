/**
 * JavaScript - Tab Switching (Horizontal + Vertical) + Popover
 * Modulo: Eventos > Editar
 * Usado por todas as tabs
 * 
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST
 * - window.SALAS_MAP: Mapa de salas do evento
 */

// Popover para salas/produtos
var _openPopSala = null;
function togglePopSala(id) {
  var el = document.getElementById(id);
  if (!el) return;
  if (_openPopSala && _openPopSala !== el) {
    _openPopSala.classList.remove('open');
    var prevWrap = _openPopSala.closest('.popover-wrap');
    if (prevWrap) prevWrap.classList.remove('popover-active');
    _openPopSala = null;
  }
  el.classList.toggle('open');
  var wrap = el.closest('.popover-wrap');
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
  document.querySelectorAll('.loc-htab').forEach(function(t){ t.classList.remove('active'); });
  document.querySelectorAll('.loc-hpane').forEach(function(p){ p.classList.remove('active'); });
  el.classList.add('active');
  var pane = document.getElementById('loc-hpane-' + idx);
  if (pane) { pane.classList.add('active'); }

  // Carregar conteudo da aba Fornecedores via AJAX quando ativada
  if (idx === 4 && pane) {
    refreshFornecedoresTab();
  }
}

// Recarregar conteudo da aba Fornecedores via AJAX
function refreshFornecedoresTab() {
  var container = document.getElementById('fornecedores-container');
  if (!container) return;
  var eventoId = typeof EVENTO_ID !== 'undefined' ? EVENTO_ID : (window.EVENTO_ID || 0);
  if (!eventoId) return;

  var baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : (window.BASE_URL || '');
  fetch(baseUrl + '/eventos/fornecedores-html/' + eventoId, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin'
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success && data.html) {
      container.innerHTML = data.html;
    }
  })
  .catch(function() {});
}

// Tab vertical switch
function switchVTab(idx, el) {
  document.querySelectorAll('.loc-vtab').forEach(function(t){ t.classList.remove('active'); });
  document.querySelectorAll('.loc-vpane').forEach(function(p){ p.classList.remove('active'); });
  el.classList.add('active');
  var pane = document.getElementById('loc-vpane-' + idx);
  if (pane) { pane.classList.add('active'); }
}

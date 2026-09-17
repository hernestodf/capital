// ============ INTERCEPTOR GLOBAL DE SESSÃO EXPIRADA ============
(function() {
  var originalFetch = window.fetch;
  window.fetch = function(url, options) {
    options = options || {};
    options.credentials = options.credentials || 'same-origin';
    options.headers = options.headers || {};
    if (!options.headers['X-Requested-With'] && !(options.headers instanceof Headers)) {
      options.headers['X-Requested-With'] = 'XMLHttpRequest';
    }

    return originalFetch(url, options).then(function(response) {
      if (response.status === 401) {
        handleSessionExpired();
      }
      return response;
    });
  };
})();

// ============ API FETCH HELPER ============
/**
 * Helper para chamadas API com CSRF, timeout e tratamento de erro padronizado
 * 
 * @param {string} url - Endpoint da API
 * @param {Object} options - Opções do fetch
 * @param {string} options.method - Método HTTP (GET, POST, PUT, DELETE)
 * @param {Object|FormData} options.data - Dados a enviar
 * @param {boolean} options.showLoading - Mostrar loading (default: true)
 * @param {boolean} options.showToast - Mostrar toast de erro (default: true)
 * @returns {Promise<Object>} Response JSON
 * 
 * Exemplo de uso:
 * apiFetch('/api/eventos/itens', { method: 'GET' })
 *   .then(data => console.log(data))
 *   .catch(err => console.error(err));
 */
window.apiFetch = function(url, options = {}) {
  var method = options.method || 'GET';
  var data = options.data || null;
  var showLoading = options.showLoading !== false;
  var showToast = options.showToast !== false;
  var timeout = options.timeout || 30000; // 30 segundos default

  // Montar headers
  var headers = {
    'X-Requested-With': 'XMLHttpRequest'
  };

  // Se não for FormData, adicionar Content-Type
  if (data && !(data instanceof FormData)) {
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
    
    // Converter objeto para query string
    var params = [];
    for (var key in data) {
      if (data.hasOwnProperty(key)) {
        params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
      }
    }
    data = params.join('&');
  }

  // Adicionar CSRF token se disponível
  if (typeof CSRF_TOKEN !== 'undefined') {
    if (data instanceof FormData) {
      data.append('_csrf_token', CSRF_TOKEN);
    } else if (method === 'POST' || method === 'PUT' || method === 'DELETE') {
      if (data) {
        data += '&_csrf_token=' + encodeURIComponent(CSRF_TOKEN);
      } else {
        data = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN);
      }
    }
  }

  // Montar options do fetch
  var fetchOptions = {
    method: method,
    headers: headers,
    credentials: 'same-origin'
  };

  if (data) {
    fetchOptions.body = data;
  }

  // Criar promise com timeout
  return new Promise(function(resolve, reject) {
    var timeoutId = setTimeout(function() {
      reject(new Error('Timeout: A requisição demorou muito'));
    }, timeout);

    // Mostrar loading se habilitado
    if (showLoading && typeof showGlobalLoading === 'function') {
      showGlobalLoading();
    }

    fetch(url, fetchOptions)
      .then(function(response) {
        clearTimeout(timeoutId);
        
        // Esconder loading
        if (showLoading && typeof hideGlobalLoading === 'function') {
          hideGlobalLoading();
        }

        // Verificar se é JSON
        var contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Resposta inválida do servidor');
        }

        return response.json();
      })
      .then(function(data) {
        // Verificar se há erro na resposta
        if (data.error || data.success === false) {
          var errorMsg = data.error || data.message || 'Erro na requisição';

          // Sessão expirada: redirecionar para login
          if (data.error === 'Unauthorized' || errorMsg === 'Unauthorized') {
            handleSessionExpired();
            return;
          }
          
          if (showToast && typeof showToast === 'function') {
            showToast('red', 'Erro', errorMsg);
          }
          
          reject(new Error(errorMsg));
        } else {
          resolve(data);
        }
      })
      .catch(function(error) {
        clearTimeout(timeoutId);
        
        // Esconder loading
        if (showLoading && typeof hideGlobalLoading === 'function') {
          hideGlobalLoading();
        }

        // Mostrar toast de erro
        if (showToast && typeof showToast === 'function') {
          showToast('red', 'Erro de Conexão', error.message);
        }

        console.error('apiFetch error:', error);
        reject(error);
      });
  });
};

// ============ SIDEBAR ============
function toggleSidebar() {
  document.body.classList.toggle('mini');
}
function openSidebar() {
  document.body.classList.remove('mini');
}
function closeSidebar() {
  document.body.classList.add('mini');
}

// ============ SUBMENU ============
function toggleSub(id, el) {
  if (document.body.classList.contains('mini')) return;
  var sub = document.getElementById('sub-' + id),
      chv = el.querySelector('.chv'),
      isOpen = sub.classList.contains('open');
  document.querySelectorAll('.sub').forEach(function(s) { s.classList.remove('open'); });
  document.querySelectorAll('.chv').forEach(function(c) { c.classList.remove('open'); });
  if (!isOpen) { sub.classList.add('open'); chv.classList.add('open'); }
}

// ============ SECTION NAVIGATION ============
function showSection(id, el) {
  document.querySelectorAll('.section').forEach(function(s) { s.classList.remove('active'); });
  document.querySelectorAll('.ni,.si').forEach(function(n) { n.classList.remove('active'); });
  var sec = document.getElementById(id) || document.getElementById('sec-' + id);
  if (sec) sec.classList.add('active');
  if (el) el.classList.add('active');
  var titles = {
    'sec-overview':'Visão Geral','sec-buttons':'Buttons','sec-tabs':'Tabs','sec-modals':'Modals',
    'sec-toasts':'Toasts','sec-forms':'Forms','sec-cards':'Cards','sec-tables':'Tables',
    'sec-alerts':'Alerts','sec-progress':'Progress','sec-spinners':'Spinners',
    'sec-accordion':'Accordion','sec-timeline':'Timeline','sec-stepper':'Stepper','sec-misc':'Avatares & Mais',
    dashboard:'Dashboard', accordions:'Accordions', alerts:'Alerts', buttons:'Buttons',
    badges:'Badges', cards:'Cards', carousel:'Carousel', icons:'Icons', listitems:'List Items',
    modals:'Modals', progress:'Progress', popovers:'Popovers', tabs:'Tabs', tooltips:'Tooltips',
    typography:'Typography', forminputs:'Form Inputs', checkboxradio:'Checkbox & Radio',
    fileinput:'File Input', validations:'Validations', datetime:'Date Time',
    invoice:'Invoice', calendar:'Calendar', spinners:'Spinners', stepper:'Stepper',
    timeline:'Timeline', ratings:'Ratings', avatars:'Avatars', tables:'Tables',
    chips:'Chips & Tags', skeleton:'Skeleton'
  };
  document.getElementById('breadcrumb-cur').textContent = titles[id] || id;
  if (id === 'progress' || id === 'sec-progress') initProgressBars();
}

// ============ MODALS ============
function openModal(id) {
  // Fechar todos os popovers abertos antes de abrir modal
  document.querySelectorAll('.popover.open').forEach(function(pop) {
    pop.classList.remove('open');
    var wrap = pop.closest('.popover-wrap');
    if (wrap) wrap.classList.remove('popover-active');
  });
  openPop = null;
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
  // Disparar evento de abertura do modal
  window.dispatchEvent(new CustomEvent('modalOpen', { detail: id }));
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
function closeModalOutside(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

// ============ POPOVERS ============
var openPop = null;
function togglePop(id) {
  var el = document.getElementById(id);
  if (openPop && openPop !== el) {
    openPop.classList.remove('open');
    var prevWrap = openPop.closest('.popover-wrap');
    if (prevWrap) prevWrap.classList.remove('popover-active');
    if (openPop.classList.contains('td-act-dropdown')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; }
    openPop = null;
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
    openPop = el;
    // Table action dropdowns: position fixed to escape overflow clipping
    if (el.classList.contains('td-act-dropdown')) {
      var btn = el.parentElement.querySelector('.td-act-toggle');
      var r = btn.getBoundingClientRect();
      el.style.position = 'fixed';
      el.style.top = (r.bottom + 6) + 'px';
      el.style.left = (r.left + r.width/2 - 80) + 'px';
    }
  } else {
    if (el.classList.contains('td-act-dropdown')) { el.style.position=''; el.style.top=''; el.style.left=''; }
    openPop = null;
  }
}
document.addEventListener('click', function(e) {
  if (openPop && !e.target.closest('.popover-wrap') && !e.target.closest('.td-act-menu')) {
    openPop.classList.remove('open');
    if (openPop.classList.contains('td-act-dropdown')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; }
    openPop = null;
  }
});
window.addEventListener('scroll', function() {
  if (openPop && openPop.classList.contains('td-act-dropdown')) {
    var btn = openPop.parentElement.querySelector('.td-act-toggle');
    var r = btn.getBoundingClientRect();
    openPop.style.top = (r.bottom + 6) + 'px';
    openPop.style.left = (r.left + r.width/2 - 80) + 'px';
  }
}, true);

// ============ TOASTS — FIXED ============
var TOAST_ICONS = {
  red:    '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  green:  '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  cyan:   '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  yellow: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
  purple: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
  orange: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
};

function showToast(type, title, msg, duration) {
  var container = document.getElementById('toast-container');
  if (!container) { container = document.createElement('div'); container.id = 'toast-container'; document.body.appendChild(container); }
  var t = document.createElement('div');
  t.className = 'toast ' + (type || 'cyan');

  // Toast icon (safe SVG from constant)
  var iconDiv = document.createElement('div');
  iconDiv.className = 'toast-icon';
  iconDiv.innerHTML = TOAST_ICONS[type] || TOAST_ICONS.cyan;

  // Toast body with safe textContent for dynamic data
  var bodyDiv = document.createElement('div');
  bodyDiv.className = 'toast-body';
  var titleDiv = document.createElement('div');
  titleDiv.className = 'toast-title';
  titleDiv.textContent = title || 'Notificação';
  bodyDiv.appendChild(titleDiv);
  if (msg) {
    var msgDiv = document.createElement('div');
    msgDiv.className = 'toast-msg';
    msgDiv.textContent = msg;
    bodyDiv.appendChild(msgDiv);
  }

  // Close button
  var closeBtn = document.createElement('button');
  closeBtn.className = 'toast-close';
  closeBtn.onclick = function() { dismissToast(t); };
  closeBtn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

  t.appendChild(iconDiv);
  t.appendChild(bodyDiv);
  t.appendChild(closeBtn);
  container.prepend(t);
  requestAnimationFrame(function() { requestAnimationFrame(function() { t.classList.add('show'); }); });
  t._timer = setTimeout(function() { dismissToast(t); }, duration || 4500);
}

function dismissToast(t) {
  if (!t || t._dismissed) return;
  t._dismissed = true;
  clearTimeout(t._timer);
  t.classList.remove('show');
  t.classList.add('hide');
  setTimeout(function() { if (t.parentElement) t.parentElement.removeChild(t); }, 400);
}

// ============ COLORED TABS ============
function switchH(g, i, el, color) {
  var parent = el.parentElement;
  parent.querySelectorAll('[class^="ht-"]').forEach(function(t) {
    t.classList.remove('active');
    ['red','cyan','green','yellow','purple'].forEach(function(c) { t.classList.remove(c); });
  });
  el.classList.add('active');
  if (color) el.classList.add(color);
  var pfx = { c: 'hc' }[g];
  parent.querySelectorAll('[class^="ht-"]').forEach(function(j) {
    var p = document.getElementById(pfx + '-' + Array.from(parent.querySelectorAll('[class^="ht-"]')).indexOf(j));
    if (p) p.classList.remove('active');
  });
  var tgt = document.getElementById(pfx + '-' + i);
  if (tgt) tgt.classList.add('active');
}

// ============ CAROUSEL ============
var carouselIndex = 0;
function moveCarousel(dir) { carouselIndex = (carouselIndex + dir + 3) % 3; updateCarousel(); }
function goToSlide(i) { carouselIndex = i; updateCarousel(); }
function updateCarousel() {
  document.querySelector('#demo-carousel .carousel-track').style.transform = 'translateX(-' + (carouselIndex * 100) + '%)';
  document.querySelectorAll('#demo-carousel .carousel-dot').forEach(function(d, i) { d.classList.toggle('active', i === carouselIndex); });
}

// ============ LEFT OFF-CANVAS ============
function openLeft() { document.getElementById('left-canvas').classList.add('open'); document.getElementById('overlay-left').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeLeft() { document.getElementById('left-canvas').classList.remove('open'); document.getElementById('overlay-left').classList.remove('active'); document.body.style.overflow = ''; }
function selectChip(el) { var g = el.parentElement; g.querySelectorAll('.lc-chip').forEach(function(c) { c.classList.remove('active'); }); el.classList.add('active'); }

// ============ RIGHT OFF-CANVAS ============
function openRight() { document.getElementById('right-canvas').classList.add('open'); document.getElementById('overlay-right').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeRight() { document.getElementById('right-canvas').classList.remove('open'); document.getElementById('overlay-right').classList.remove('active'); document.body.style.overflow = ''; }
function switchRcTab(id, el) {
  document.querySelectorAll('.rc-tab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.rc-pane').forEach(function(p) { p.classList.remove('active'); });
  el.classList.add('active');
  document.getElementById('rc-' + id).classList.add('active');
}

// ============ PROGRESS BARS ============
function initProgressBars() {
  document.querySelectorAll('.prog-bar[data-w]').forEach(function(b) {
    b.style.transition = 'none';
    b.style.width = '0';
    requestAnimationFrame(function() {
      setTimeout(function() {
        b.style.transition = 'width 1.1s cubic-bezier(.25,.8,.25,1)';
        b.style.width = b.dataset.w;
      }, 80);
    });
  });
}

// ============ STEPPER ============
var stepperCurrent = 1;
var stepperTotal = 4;

function stepNext() {
  if (stepperCurrent < stepperTotal) { stepperCurrent++; updateStepper(); }
  else { showToast('green','Stepper Concluído!','Todos os passos foram completados.'); stepperCurrent = 1; updateStepper(); }
}

function stepPrev() { if (stepperCurrent > 1) { stepperCurrent--; updateStepper(); } }

function updateStepper() {
  document.querySelectorAll('.step-item').forEach(function(item, idx) {
    var n = idx + 1;
    item.classList.remove('active','done');
    if (n < stepperCurrent) item.classList.add('done');
    else if (n === stepperCurrent) item.classList.add('active');
  });
  document.querySelectorAll('.step-pane').forEach(function(p, idx) {
    p.classList.toggle('active', idx + 1 === stepperCurrent);
  });
  var prevBtn = document.getElementById('step-prev-btn');
  var nextBtn = document.getElementById('step-next-btn');
  if (prevBtn) prevBtn.disabled = stepperCurrent === 1;
  if (nextBtn) nextBtn.textContent = stepperCurrent === stepperTotal ? 'Concluir ✓' : 'Próximo →';
}

// ============ RATING ============
function setRating(el, val) {
  var group = el.closest('.rating-group');
  group.querySelectorAll('.star').forEach(function(s, i) { s.classList.toggle('active', i < val); });
  group.dataset.rating = val;
}
function hoverRating(el, val) {
  var group = el.closest('.rating-group');
  group.querySelectorAll('.star').forEach(function(s, i) { s.classList.toggle('hover', i < val); });
}
function leaveRating(el) { el.closest('.rating-group').querySelectorAll('.star').forEach(function(s) { s.classList.remove('hover'); }); }

// ============ TABLE SORT ============
function sortTable(colIdx, btn) {
  var table = btn.closest('table');
  var tbody = table.querySelector('tbody');
  var rows = Array.from(tbody.querySelectorAll('tr'));
  var asc = btn.dataset.sort !== 'asc';
  table.querySelectorAll('.th-sort').forEach(function(b) { b.dataset.sort = ''; b.querySelector('.sort-ico').textContent = '↕'; });
  btn.dataset.sort = asc ? 'asc' : 'desc';
  btn.querySelector('.sort-ico').textContent = asc ? '↑' : '↓';
  rows.sort(function(a, b) {
    var va = a.cells[colIdx].textContent.trim(), vb = b.cells[colIdx].textContent.trim();
    var na = parseFloat(va.replace(/[^0-9.,]/g,'').replace(',','.')), nb = parseFloat(vb.replace(/[^0-9.,]/g,'').replace(',','.'));
    if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
    return asc ? va.localeCompare(vb,'pt-BR') : vb.localeCompare(va,'pt-BR');
  });
  rows.forEach(function(r) { tbody.appendChild(r); });
  // Re-apply current view (search + pagination)
  var id = table.id;
  if (id) tblApplyView(id);
}

// ============ TABLE SEARCH & PAGINATION ============
var tblState = {};

function tblInit(id) {
  var table = document.getElementById(id);
  if (!table) return;
  var perPage = parseInt(table.dataset.perPage) || 5;
  tblState[id] = { page: 1, perPage: perPage, searchTerm: '' };
  tblApplyView(id);
}

function normalizarTexto(s) {
  return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
}

function tblSearch(id, term) {
  if (!tblState[id]) tblInit(id);
  tblState[id].searchTerm = normalizarTexto(term);
  tblState[id].page = 1;
  tblApplyView(id);
}

function tblSetPerPage(id, val) {
  if (!tblState[id]) tblInit(id);
  tblState[id].perPage = parseInt(val) || 5;
  tblState[id].page = 1;
  tblApplyView(id);
}

function tblGoToPage(id, page) {
  if (!tblState[id]) tblInit(id);
  tblState[id].page = page;
  tblApplyView(id);
}

function tblApplyView(id) {
  var table = document.getElementById(id);
  if (!table || !tblState[id]) return;
  var state = tblState[id];
  var rows = Array.from(table.querySelectorAll('tbody tr'));
  var container = document.getElementById(id + '-container');
  var noResults = document.getElementById(id + '-no-results');

  // Filter by search
  var visibleRows = rows;
  if (state.searchTerm) {
    visibleRows = rows.filter(function(r) {
      var text = normalizarTexto(r.textContent);
      return text.indexOf(state.searchTerm) !== -1;
    });
  }

  // Mark all rows hidden first
  rows.forEach(function(r) { r.classList.add('tbl-hidden'); });

  // Calculate pagination
  var total = visibleRows.length;
  var isPaginated = table.dataset.paginated === 'true';
  var totalPages = isPaginated ? Math.max(1, Math.ceil(total / state.perPage)) : 1;
  if (state.page > totalPages) state.page = totalPages;

  var start = isPaginated ? (state.page - 1) * state.perPage : 0;
  var end = isPaginated ? start + state.perPage : total;
  var pageRows = visibleRows.slice(start, end);

  // Show visible rows
  pageRows.forEach(function(r) { r.classList.remove('tbl-hidden'); });

  // Info text
  var infoEl = document.getElementById(id + '-info');
  if (infoEl) {
    if (total === 0) {
      infoEl.textContent = 'Nenhum registro';
    } else {
      var showStart = start + 1;
      var showEnd = Math.min(end, total);
      infoEl.textContent = 'Mostrando ' + showStart + '-' + showEnd + ' de ' + total;
    }
  }

  // Pagination buttons
  var pagesEl = document.getElementById(id + '-pages');
  if (pagesEl && isPaginated) {
    pagesEl.innerHTML = '';
    // Prev button
    var prevBtn = document.createElement('button');
    prevBtn.className = 'tbl-page-btn';
    prevBtn.disabled = state.page <= 1;
    prevBtn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>';
    prevBtn.onclick = function() { tblGoToPage(id, state.page - 1); };
    pagesEl.appendChild(prevBtn);

    // Page numbers (show max 5 pages with ellipsis)
    var maxVisible = 5;
    var startPage = Math.max(1, state.page - Math.floor(maxVisible / 2));
    var endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (startPage > 1) {
      pagesEl.appendChild(tblMakePageBtn(id, 1));
      if (startPage > 2) {
        var dots = document.createElement('span');
        dots.textContent = '...';
        dots.style.cssText = 'padding:0 4px;color:var(--text-4);font-size:13px';
        pagesEl.appendChild(dots);
      }
    }
    for (var i = startPage; i <= endPage; i++) {
      pagesEl.appendChild(tblMakePageBtn(id, i));
    }
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        var dots2 = document.createElement('span');
        dots2.textContent = '...';
        dots2.style.cssText = 'padding:0 4px;color:var(--text-4);font-size:13px';
        pagesEl.appendChild(dots2);
      }
      pagesEl.appendChild(tblMakePageBtn(id, totalPages));
    }

    // Next button
    var nextBtn = document.createElement('button');
    nextBtn.className = 'tbl-page-btn';
    nextBtn.disabled = state.page >= totalPages;
    nextBtn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
    nextBtn.onclick = function() { tblGoToPage(id, state.page + 1); };
    pagesEl.appendChild(nextBtn);
  }

  // No results message
  if (noResults) {
    noResults.style.display = (total === 0 && state.searchTerm) ? 'flex' : 'none';
  }

  // Hide table-wrap if no results from search
  var tableWrap = table.closest('.table-wrap');
  if (tableWrap) {
    tableWrap.style.display = (total === 0) ? 'none' : '';
  }
  // Also hide pagination if no results
  var pagination = container ? container.querySelector('.tbl-pagination') : null;
  if (pagination) {
    pagination.style.display = (total === 0) ? 'none' : '';
  }
}

function tblMakePageBtn(id, page) {
  var btn = document.createElement('button');
  btn.className = 'tbl-page-btn' + (page === tblState[id].page ? ' active' : '');
  btn.textContent = page;
  btn.onclick = function() { tblGoToPage(id, page); };
  return btn;
}

// ============ CHIPS ============
function toggleChipSel(el) { el.classList.toggle('selected'); }
function removeChip(btn) { btn.closest('.chip-removable').remove(); }

// ============ SKELETON LOADER ============
function loadSkeleton() {
  var btn = document.getElementById('skeleton-load-btn');
  btn.disabled = true; btn.textContent = 'Carregando...';
  document.getElementById('skeleton-demo').classList.add('loading');
  setTimeout(function() {
    document.getElementById('skeleton-demo').classList.remove('loading');
    btn.disabled = false; btn.textContent = 'Simular Carregamento';
    showToast('green','Conteúdo carregado!','Os dados foram exibidos com sucesso.');
  }, 2200);
}

// ============ GENERIC TAB SWITCHING ============
function switchTab(group, index) {
  // Deactivate all tabs in this group
  document.querySelectorAll('[data-tab-group="' + group + '"][data-tab-index]').forEach(function(el) {
    el.classList.remove('active');
  });
  // Activate clicked tab
  var tabs = document.querySelectorAll('[data-tab-group="' + group + '"][data-tab-index="' + index + '"]');
  tabs.forEach(function(el) { el.classList.add('active'); });
}

// ============ SESSÃO EXPIRADA ============
/**
 * Função global para tratar sessão expirada em qualquer módulo.
 * Mostra mensagem amigável e redireciona para login.
 * Uso: chamar nos catch/fail dos fetch quando detectar 401 ou "Unauthorized"
 */
var _sessionExpiredHandling = false;
window.handleSessionExpired = function(mensagem) {
  if (_sessionExpiredHandling) return; // Evitar duplicação
  _sessionExpiredHandling = true;

  mensagem = mensagem || 'Sua sessão expirou. Faça login novamente.';

  // Fechar modais abertos
  document.querySelectorAll('.modal.open, .modal-overlay.open').forEach(function(el) {
    el.classList.remove('open');
  });

  // Substituir conteudo visivel pela mensagem de login
  var containers = [
    'rh-table-body', 'fornecedores-container', 'propostas-list', 'emails-container',
    'fechamento-content', 'devolucao-list', 'montagem-list',
    'salas-container', 'produtos-container'
  ];
  containers.forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
      el.innerHTML = '<div style="padding:60px 20px;text-align:center">' +
        '<div style="font-size:48px;margin-bottom:16px;opacity:0.5">🔒</div>' +
        '<div style="font-size:18px;font-weight:600;color:var(--text-1);margin-bottom:8px">Sessão Expirada</div>' +
        '<div style="font-size:14px;color:var(--text-3);margin-bottom:20px">' + mensagem + '</div>' +
        '<div style="font-size:13px;color:var(--text-4)">Redirecionando para o login...</div>' +
        '</div>';
    }
  });

  // Mostrar toast
  if (typeof showToast === 'function') {
    showToast('red', 'Sessão Expirada', mensagem);
  }

  // Redirecionar para login após 2 segundos
  setTimeout(function() {
    var baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : (window.BASE_URL || '');
    window.location.href = (baseUrl ? baseUrl : '') + '/auth/login';
  }, 2000);
};

/**
 * Wrapper para fetch que detecta 401 e trata sessão expirada automaticamente.
 * Uso: fetchAuthed(url, options).then(...)
 * Em caso de 401, redireciona para login sem precisar de catch manual.
 */
window.fetchAuthed = function(url, options) {
  options = options || {};
  options.credentials = 'same-origin';
  options.headers = options.headers || {};
  options.headers['X-Requested-With'] = 'XMLHttpRequest';

  return fetch(url, options)
    .then(function(response) {
      if (response.status === 401) {
        // Tentar ler o JSON para confirmar
        return response.json().then(function(data) {
          if (data.error === 'Unauthorized') {
            handleSessionExpired();
            // Retornar uma promise que nunca resolve para evitar continuacao
            return new Promise(function() {});
          }
          throw new Error(data.error || 'Não autorizado');
        });
      }
      return response;
    });
};

// ============ INIT ============
document.addEventListener('DOMContentLoaded', function() {
  initProgressBars();
  updateStepper();
  // Init all paginated/searchable tables
  document.querySelectorAll('table[data-paginated="true"]').forEach(function(t) {
    if (t.id) tblInit(t.id);
  });
});

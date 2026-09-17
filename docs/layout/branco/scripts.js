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

// ============ SIDEBAR SUB-MENU (Nested) ============
function toggleSubMenu(el) {
  if (document.body.classList.contains('mini')) return;
  var parent = el.parentElement;
  var subMenu = parent.querySelector('.sub-menu');
  if (!subMenu) return;
  
  var isOpen = subMenu.classList.contains('open');
  
  // Close other submenus at same level
  parent.querySelectorAll(':scope > .sub-menu').forEach(function(sm) {
    sm.classList.remove('open');
  });
  parent.querySelectorAll(':scope > .ni').forEach(function(ni) {
    ni.classList.remove('active');
  });
  
  // Toggle current
  if (!isOpen) {
    subMenu.classList.add('open');
    el.classList.add('active');
  }
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
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
function closeModalOutside(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

// ============ POPOVERS ============
var openPop = null;
function togglePop(id) {
  var el = document.getElementById(id);
  if (openPop && openPop !== el) {
    openPop.classList.remove('open');
    if (openPop.classList.contains('td-act-dropdown')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; }
    if (openPop.classList.contains('popover-sala')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; openPop.style.right=''; }
    // Remove active class from wrapper
    var oldWrap = openPop.closest('.popover-wrap');
    if (oldWrap) oldWrap.classList.remove('popover-active');
    openPop = null;
  }
  el.classList.toggle('open');
  if (el.classList.contains('open')) {
    openPop = el;
    // Add active class to wrapper for z-index
    var wrap = el.closest('.popover-wrap');
    if (wrap) wrap.classList.add('popover-active');
    // Table action dropdowns: position fixed to escape overflow clipping
    if (el.classList.contains('td-act-dropdown')) {
      var btn = el.parentElement.querySelector('.td-act-toggle');
      var r = btn.getBoundingClientRect();
      el.style.position = 'fixed';
      el.style.top = (r.bottom + 6) + 'px';
      el.style.left = (r.left + r.width/2 - 80) + 'px';
    }
    // Sala item popovers: position fixed to escape overflow clipping
    if (el.classList.contains('popover-sala')) {
      var wrap2 = el.parentElement;
      var btn = wrap2.querySelector('button');
      if (btn) {
        var r = btn.getBoundingClientRect();
        el.style.position = 'fixed';
        el.style.top = (r.bottom + 8) + 'px';
        el.style.left = (r.left + r.width/2 - 140) + 'px';
        el.style.right = '';
      }
    }
  } else {
    if (el.classList.contains('td-act-dropdown')) { el.style.position=''; el.style.top=''; el.style.left=''; }
    if (el.classList.contains('popover-sala')) { el.style.position=''; el.style.top=''; el.style.left=''; el.style.right=''; }
    // Remove active class from wrapper
    var wrap3 = el.closest('.popover-wrap');
    if (wrap3) wrap3.classList.remove('popover-active');
    openPop = null;
  }
}
document.addEventListener('click', function(e) {
  if (openPop && !e.target.closest('.popover-wrap') && !e.target.closest('.td-act-menu')) {
    openPop.classList.remove('open');
    if (openPop.classList.contains('td-act-dropdown')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; }
    if (openPop.classList.contains('popover-sala')) { openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; openPop.style.right=''; }
    var wrap = openPop.closest('.popover-wrap');
    if (wrap) wrap.classList.remove('popover-active');
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
  // Sala popovers: close on scroll to avoid position jitter
  if (openPop && openPop.classList.contains('popover-sala')) {
    openPop.classList.remove('open');
    openPop.style.position=''; openPop.style.top=''; openPop.style.left=''; openPop.style.right='';
    var wrap = openPop.closest('.popover-wrap');
    if (wrap) wrap.classList.remove('popover-active');
    openPop = null;
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
  t.innerHTML =
    '<div class="toast-icon">' + (TOAST_ICONS[type] || TOAST_ICONS.cyan) + '</div>' +
    '<div class="toast-body"><div class="toast-title">' + (title || 'Notificação') + '</div>' +
    (msg ? '<div class="toast-msg">' + msg + '</div>' : '') + '</div>' +
    '<button class="toast-close" onclick="dismissToast(this.parentElement)">' +
    '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
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

function tblSearch(id, term) {
  if (!tblState[id]) tblInit(id);
  tblState[id].searchTerm = term.toLowerCase().trim();
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
      var text = r.textContent.toLowerCase();
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

// ============ INIT ============
document.addEventListener('DOMContentLoaded', function() {
  initProgressBars();
  updateStepper();
  // Init all paginated/searchable tables
  document.querySelectorAll('table[data-paginated="true"]').forEach(function(t) {
    if (t.id) tblInit(t.id);
  });
});

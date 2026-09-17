/**
 * JavaScript - Alocacao de Estoque (Aba Sublocacao)
 * Modulo: Eventos > Editar > Sublocacao
 *
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID, window.BASE_URL, window.CSRF_TOKEN
 */
(function() {
  'use strict';
  let _alocacaoItemId = null;
  const _alocacaoSeriaisCache = {};

  function abrirModalAlocarSerial(itemId, nomeProduto) {
    _alocacaoItemId = itemId;
    const elNome = document.getElementById('modal-alocar-item-nome');
    if (elNome) elNome.textContent = 'Item: ' + nomeProduto;

    carregarSeriaisDisponiveis(itemId);

    const modal = document.getElementById('modal-alocar-serial');
    if (modal) { modal.style.display = 'flex'; modal.classList.add('open'); }
  }

  function fecharModalAlocarSerial() {
    const modal = document.getElementById('modal-alocar-serial');
    if (modal) { modal.style.display = 'none'; modal.classList.remove('open'); }
    _alocacaoItemId = null;
  }

  function carregarSeriaisDisponiveis(itemId) {
    const container = document.getElementById('alocar-seriais-lista');
    if (!container) return;
    window.renderHTML(container, '<div style="text-align:center;padding:16px;color:var(--text-4)">Carregando seriais disponiveis...</div>');

    fetch(window.BASE_URL + '/api/alocacao/seriais-disponiveis/' + itemId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data) {
        renderizarSeriaisDisponiveis(data.data);
      } else {
        window.renderHTML(container, '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhum serial disponivel</div>');
      }
    })
    .catch(() => {
      window.renderHTML(container, '<div style="text-align:center;padding:16px;color:var(--text-4)">Erro ao carregar seriais</div>');
    });
  }

  function renderizarSeriaisDisponiveis(seriais) {
    const container = document.getElementById('alocar-seriais-lista');
    if (!container) return;

    if (!seriais.length) {
      window.renderHTML(container, '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhum serial disponivel no estoque</div>');
      return;
    }

    let html = '<table style="width:100%;border-collapse:collapse">';
    html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
    html += '<th style="padding:6px 8px;font-size:11px;color:var(--text-3);width:30px"><input type="checkbox" id="alocar-select-all" onchange="alocarToggleAll(this)"></th>';
    html += '<th style="padding:6px 8px;font-size:11px;color:var(--text-3)">Serial</th>';
    html += '<th style="padding:6px 8px;font-size:11px;color:var(--text-3)">Produto</th>';
    html += '</tr></thead><tbody>';

    seriais.forEach(s => {
      html += '<tr style="border-bottom:1px solid var(--bg-border)">';
      html += '<td style="padding:6px 8px"><input type="checkbox" class="alocar-serial-check" value="' + s.id + '"></td>';
      html += '<td style="padding:6px 8px;font-size:12px">' + escapeHtml(s.serial || '') + '</td>';
      html += '<td style="padding:6px 8px;font-size:12px;color:var(--text-3)">' + escapeHtml(s.nome_produto || '') + '</td>';
      html += '</tr>';
    });

    html += '</tbody></table>';
    html += '<div style="margin-top:8px;font-size:11px;color:var(--text-3)">' + seriais.length + ' serial(is) disponivel(is)</div>';
    window.renderHTML(container, html);
  }

  function alocarToggleAll(master) {
    const checks = document.querySelectorAll('#alocar-seriais-lista .alocar-serial-check');
    checks.forEach(c => { c.checked = master.checked; });
  }

  function alocarSeriaisSelecionados() {
    if (!_alocacaoItemId) { showToast('red', 'Erro', 'Item nao identificado'); return; }

    const checks = document.querySelectorAll('#alocar-seriais-lista .alocar-serial-check:checked');
    const ids = [];
    checks.forEach(c => { ids.push(c.value); });

    if (!ids.length) { showToast('red', 'Erro', 'Selecione pelo menos um serial'); return; }

    const body = '_csrf_token=' + window.CSRF_TOKEN +
      '&id_produto_evento=' + _alocacaoItemId +
      '&ids_seriais=' + encodeURIComponent(JSON.stringify(ids));

    fetch(window.BASE_URL + '/api/alocacao/alocar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: body
    })
    .then(r => r.json())
    .then(data => {
      if (data.success || data.alocados > 0) {
        showToast('green', 'Sucesso', data.alocados + ' serial(is) alocado(s) com sucesso!');
        if (data.errors && data.errors.length) {
          data.errors.forEach(e => { showToast('yellow', 'Atencao', e); });
        }
        fecharModalAlocarSerial();
        atualizarBadgeAlocacao(_alocacaoItemId);
        if (typeof atualizarBadgeSublocacao === 'function') atualizarBadgeSublocacao(_alocacaoItemId);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao alocar seriais');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  function desalocarSerial(id) {
    if (!confirm('Remover este serial do item?')) return;

    fetch(window.BASE_URL + '/api/alocacao/desalocar/' + id, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: '_csrf_token=' + window.CSRF_TOKEN
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('green', 'Removido', 'Serial desalocado');
        if (_alocacaoItemId) {
          atualizarBadgeAlocacao(_alocacaoItemId);
          if (typeof atualizarBadgeSublocacao === 'function') atualizarBadgeSublocacao(_alocacaoItemId);
          carregarAlocacoes(_alocacaoItemId);
        }
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao desalocar');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  function carregarAlocacoes(itemId) {
    const container = document.getElementById('alocacao-lista');
    if (!container) return;
    window.renderHTML(container, '<div style="text-align:center;padding:8px;color:var(--text-4);font-size:12px">Carregando...</div>');

    fetch(window.BASE_URL + '/api/alocacao/list/' + itemId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data) {
        renderizarAlocacoes(data.data);
      } else {
        window.renderHTML(container, '<div style="text-align:center;padding:8px;color:var(--text-4);font-size:12px">Nenhum serial alocado</div>');
      }
    })
    .catch(() => {
      window.renderHTML(container, '<div style="text-align:center;padding:8px;color:var(--text-4);font-size:12px">Erro ao carregar</div>');
    });
  }

  function renderizarAlocacoes(alocacoes) {
    const container = document.getElementById('alocacao-lista');
    if (!container) return;
    if (!alocacoes.length) {
      window.renderHTML(container, '<div style="text-align:center;padding:8px;color:var(--text-4);font-size:12px">Nenhum serial alocado</div>');
      return;
    }

    let html = '<table style="width:100%;border-collapse:collapse">';
    html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
    html += '<th style="padding:4px 6px;font-size:10px;color:var(--text-3)">Serial</th>';
    html += '<th style="padding:4px 6px;font-size:10px;color:var(--text-3)">Status</th>';
    html += '<th style="padding:4px 6px;font-size:10px;color:var(--text-3);text-align:right">Acao</th>';
    html += '</tr></thead><tbody>';

    alocacoes.forEach(a => {
      html += '<tr style="border-bottom:1px solid var(--bg-border)">';
      html += '<td style="padding:4px 6px;font-size:11px">' + escapeHtml(a.serial_nome || '') + '</td>';
      html += '<td style="padding:4px 6px;font-size:11px"><span class="badge sm ' + (a.status === 'alocado' ? 'cyan' : a.status === 'entregue' ? 'green' : 'gray') + '">' + escapeHtml(a.status) + '</span></td>';
      html += '<td style="padding:4px 6px;text-align:right">';
      if (a.status === 'alocado') {
        html += '<button type="button" class="btn btn-xs btn-red" data-action="desalocar-serial" data-id="' + a.id + '">Remover</button>';
      }
      html += '</td></tr>';
    });

    html += '</tbody></table>';
    window.renderHTML(container, html);
  }

  function atualizarBadgeAlocacao(itemId) {
    const badge = document.querySelector('[data-item-id="' + itemId + '"] .sala-item-aloc-badge');
    if (!badge) return;

    fetch(window.BASE_URL + '/api/alocacao/list/' + itemId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data && data.data.length > 0) {
        const alocados = data.data.filter(a => a.status === 'alocado').length;
        badge.textContent = 'Estoque: ' + alocados;
        badge.style.display = 'inline';
      } else {
        badge.style.display = 'none';
      }
    })
    .catch(() => { badge.style.display = 'none'; });
  }

  function carregarTodosBadgesAlocacao() {
    document.querySelectorAll('#sublocacao-container .sala-item-aloc-badge[data-item-id]').forEach(badge => {
      const itemId = badge.dataset.itemId;
      if (itemId) atualizarBadgeAlocacao(itemId);
    });
  }



  // Expor funcoes para onclick nos templates
  window.abrirModalAlocarSerial = abrirModalAlocarSerial;
  window.fecharModalAlocarSerial = fecharModalAlocarSerial;
  window.alocarToggleAll = alocarToggleAll;
  window.alocarSeriaisSelecionados = alocarSeriaisSelecionados;
  window.desalocarSerial = desalocarSerial;
  window.atualizarBadgeAlocacao = atualizarBadgeAlocacao;
  window.esc = esc;

})();

/**
 * JavaScript - Tab: Fechamento (modulo principal)
 * Modulo: Eventos > Editar
 *
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST
 * - window.SALAS_MAP: Mapa de salas do evento
 * - window.BASE_URL: URL base do sistema
 *
 * Sub-modulos (carregados apos este arquivo):
 * - fechamento-colaboradores.js
 * - fechamento-fornecedores.js
 * - fechamento-outros.js
 */
(function() {
  'use strict';
  const EVENTO_ID = window.EVENTO_ID;
  const BASE_URL  = window.BASE_URL || '';

  // ========================================
  // Helper: fetch com tratamento de sessao expirada
  // ========================================
  function fetchJson(url, options) {
    options = options || {};
    options.headers = options.headers || {};
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    return fetch(url, options)    .then((r) => {
      if (r.status === 401) {
        if (typeof showToast === 'function') {
          showToast('yellow', 'Sessão expirada', 'Redirecionando para o login...');
        }
        setTimeout(() => {
          window.location.href = BASE_URL + '/auth/login';
        }, 1500);
        throw new Error('Sessão expirada (401)');
      }
      return r.json();
    });
  }

  // ========================================
  // Totais
  // ========================================
  function setMoney(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = 'R$ ' + formatMoney(val || 0);
  }

  function loadTotais() {
    if (!EVENTO_ID) return;
    fetchJson(BASE_URL + '/fechamento/totais/' + EVENTO_ID)
    .then((data) => {
      if (!data.success) return;
      const d = data.data;
      setMoney('ft-receita', d.receita_total || d.receita);
      setMoney('ft-colab',   d.total_colaboradores);
      setMoney('ft-forn',    d.total_fornecedores);
      setMoney('ft-outros',  d.total_outros);
      setMoney('ft-custo',   d.custo_total);
      setMoney('ft-lucro',   d.lucro);
      const lucroEl = document.getElementById('ft-lucro');
      if (lucroEl) lucroEl.style.color = (d.lucro >= 0) ? 'var(--neon-green)' : 'var(--neon-red)';
    })
    .catch(() => {});
  }

  // ========================================
  // Locacao
  // ========================================
  function loadLocacao() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-locacao');
    fetchJson(BASE_URL + '/fechamento/locacao/' + EVENTO_ID)
    .then((data) => {
      hideLoading('fechamento-locacao');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-locacao');
        return;
      }
      renderLocacao(data.data);
      showContent('fechamento-locacao');
    })
    .catch((err) => {
      hideLoading('fechamento-locacao');
    });
  }

  function renderLocacao(itens) {
    const salas = {};
    const salaOrder = [];
    itens.forEach((item) => {
      const sKey = item.sala_id || 'sem-sala';
      const sNome = item.sala_nome || 'Sem Sala';
      if (!salas[sKey]) {
        salas[sKey] = { nome: sNome, itens: [], total: 0 };
        salaOrder.push(sKey);
      }
      salas[sKey].itens.push(item);
      salas[sKey].total += parseFloat(item.total_item) || 0;
    });

    const totalGeral = itens.reduce((s, i) => { return s + (parseFloat(i.total_item) || 0); }, 0);

    let html = '';
    salaOrder.forEach((sKey) => {
      const sala = salas[sKey];
      html += '<div style="margin-bottom:18px">';
      html += '<div style="display:flex;align-items:center;justify-content:space-between;'
            + 'padding:8px 12px;background:var(--bg-surface);border-radius:8px 8px 0 0;'
            + 'border-left:4px solid var(--neon-cyan);margin-bottom:0">';
      html += '<span style="font-weight:600;font-size:13.5px">' + escapeHtml(sala.nome) + '</span>';
      html += '<span style="font-weight:600;color:var(--neon-cyan);font-size:13px">R$ ' + formatMoney(sala.total) + '</span>';
      html += '</div>';
      html += '<table style="width:100%;border-collapse:collapse;font-size:13px;'
            + 'border:1px solid var(--bg-border-sub);border-top:none;border-radius:0 0 8px 8px;overflow:hidden">';
      html += '<thead><tr style="background:var(--bg-surface)">'
            + '<th style="text-align:left;padding:7px 10px;font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--bg-border-sub)">Produto</th>'
            + '<th style="text-align:right;padding:7px 10px;font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--bg-border-sub)">Qtd</th>'
            + '<th style="text-align:right;padding:7px 10px;font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--bg-border-sub)">Vlr Unit</th>'
            + '<th style="text-align:right;padding:7px 10px;font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--bg-border-sub)">Dias</th>'
            + '<th style="text-align:right;padding:7px 10px;font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--bg-border-sub)">Total</th>'
            + '</tr></thead><tbody>';
      sala.itens.forEach((item, idx) => {
        const bg = idx % 2 === 0 ? '' : 'background:rgba(0,0,0,0.02)';
        html += '<tr style="' + bg + '">'
              + '<td style="padding:8px 10px;border-bottom:1px solid var(--bg-border-sub)">' + escapeHtml(item.produto) + '</td>'
              + '<td style="padding:8px 10px;text-align:right;border-bottom:1px solid var(--bg-border-sub)">' + fmtQtd(item.qtd) + '</td>'
              + '<td style="padding:8px 10px;text-align:right;border-bottom:1px solid var(--bg-border-sub)">R$ ' + formatMoney(item.valor_unit) + '</td>'
              + '<td style="padding:8px 10px;text-align:right;border-bottom:1px solid var(--bg-border-sub)">' + (item.dias || 1) + '</td>'
              + '<td style="padding:8px 10px;text-align:right;font-weight:600;border-bottom:1px solid var(--bg-border-sub);color:var(--neon-green)">R$ ' + formatMoney(item.total_item) + '</td>'
              + '</tr>';
      });
      html += '</tbody></table></div>';
    });

    html += '<div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;'
          + 'padding:12px 16px;background:var(--bg-surface);border-radius:8px;border:1px solid var(--bg-border-sub)">'
          + '<span style="font-size:13px;color:var(--text-3)">Total Geral da Locação</span>'
          + '<span style="font-size:18px;font-weight:700;color:var(--neon-green)">R$ ' + formatMoney(totalGeral) + '</span>'
          + '</div>';

    const content = document.getElementById('fechamento-locacao-content');
    if (content) window.renderHTML(content, html);

    setMoney('ft-receita', totalGeral);
  }



  function fmtQtd(v) {
    const n = parseFloat(v) || 0;
    return n % 1 === 0 ? String(Math.round(n)) : n.toFixed(2).replace('.', ',');
  }

  // ========================================
  // PDF
  // ========================================
  window.gerarPdfCliente = () => {
    window.open(BASE_URL + '/fechamento/pdf/' + EVENTO_ID + '?sem_valores=1', '_blank');
  };

  window.gerarPdfInterno = () => {
    window.open(BASE_URL + '/fechamento/pdf/' + EVENTO_ID, '_blank');
  };

  // ========================================
  // Helpers de UI
  // ========================================
  function showLoading(prefix) {
    const loading = document.getElementById(prefix + '-loading');
    const content = document.getElementById(prefix + '-content');
    const empty = document.getElementById(prefix + '-empty');
    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';
    if (empty) empty.style.display = 'none';
  }

  function hideLoading(prefix) {
    const loading = document.getElementById(prefix + '-loading');
    if (loading) loading.style.display = 'none';
  }

  function showContent(prefix) {
    const loading = document.getElementById(prefix + '-loading');
    const content = document.getElementById(prefix + '-content');
    if (loading) loading.style.display = 'none';
    if (content) content.style.display = 'block';
  }

  function showEmpty(prefix) {
    const loading = document.getElementById(prefix + '-loading');
    const empty = document.getElementById(prefix + '-empty');
    if (loading) loading.style.display = 'none';
    if (empty) empty.style.display = 'block';
  }

  function formatMoney(val) {
    return Number(val || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }



  function formatDateTimeBR(dateTimeStr) {
    if (!dateTimeStr) return '-';
    const parts = dateTimeStr.split(' ');
    const datePart = parts[0].split('-');
    const timePart = parts[1] ? parts[1].substring(0, 5) : '';
    if (datePart.length === 3) return datePart[2] + '/' + datePart[1] + '/' + datePart[0] + (timePart ? ' ' + timePart : '');
    return dateTimeStr;
  }



  function escapeJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\n/g,'\\n');
  }

  // ========================================
  // Mascara de Moeda (R$)
  // ========================================
  window.mascaraMoeda = (input) => {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
      input.value = '';
      return;
    }
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = value;
  };

  // ========================================
  // Utilitarios compartilhados com sub-modulos
  // ========================================
  window._FechUtils = {
    fetch:        fetchJson,
    money:        formatMoney,
    get dateBR()  { return typeof formatDateBR === 'function' ? formatDateBR : (s) => { return s || '-'; }; },
    dateTimeBR:   formatDateTimeBR,
    get html()    { return typeof escapeHtml === 'function' ? escapeHtml : (s) => { return s || ''; }; },
    js:           escapeJs,
    showLoading:  showLoading,
    hideLoading:  hideLoading,
    showContent:  showContent,
    showEmpty:    showEmpty
  };

  // ========================================
  // Public API — sub-modulos estendem via Object.assign
  // ========================================
  window.Fechamento = {
    loadLocacao: loadLocacao,
    loadTotais:  loadTotais
  };

  // ========================================
  // Init — diferido para sub-modulos registrarem antes de rodar
  // ========================================
  function init() {
    loadLocacao();
    loadTotais();
    if (window.Fechamento.loadColaboradores) window.Fechamento.loadColaboradores();
    if (window.Fechamento.loadFornecedores)  window.Fechamento.loadFornecedores();
    if (window.Fechamento.loadFotos)         window.Fechamento.loadFotos();
    if (window.Fechamento.loadOutros)        window.Fechamento.loadOutros();

    const inputGeral = document.getElementById('input-foto-geral');
    if (inputGeral) {
      inputGeral.addEventListener('change', function() {
        if (window.Fechamento.uploadFoto) window.Fechamento.uploadFoto(null, this);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 0);
  }
})();

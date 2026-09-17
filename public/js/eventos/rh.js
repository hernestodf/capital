/**
 * JavaScript - Tab: Recursos Humanos
 * Modulo: Eventos > Editar
 *
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento
 * - window.CSRF_TOKEN: Token CSRF
 * - window.BASE_URL: URL base
 * - window.RH_COLABORADORES_LIST: Lista de colaboradores para select
 */
(function() {
  'use strict';
  const EVENTO_ID = window.EVENTO_ID;
  const CSRF_TOKEN = window.CSRF_TOKEN;
  const BASE_URL = window.BASE_URL;
  let COLABORADORES_LIST = [];

  function carregarColaboradoresList() {
    if (COLABORADORES_LIST.length > 0) return Promise.resolve(COLABORADORES_LIST);
    return fetch(BASE_URL + '/colaboradores/listar-json', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && Array.isArray(data.data)) {
        COLABORADORES_LIST = data.data;
      }
      return COLABORADORES_LIST;
    })
    .catch(() => {
      return [];
    });
  }

  const alocacaoIdPagamento = null;
  let initInProgress = false;
  let lastInitTime = 0;

  // =====================================================
  // INICIALIZACAO
  // =====================================================
  function init() {
    const now = Date.now();
    if (initInProgress || (now - lastInitTime) < 300) return;
    initInProgress = true;
    lastInitTime = now;

    // Clone para evitar listeners duplicados
    const btnAlocar = document.getElementById('btn-confirmar-alocacao');
    if (btnAlocar) {
      const newBtn = btnAlocar.cloneNode(true);
      btnAlocar.parentNode.replaceChild(newBtn, btnAlocar);
      newBtn.addEventListener('click', confirmarAlocacao);
    }

    carregarDados();
    setTimeout(() => { initInProgress = false; }, 500);
  }

  // =====================================================
  // CARREGAR DADOS
  // =====================================================
  function carregarDados() {
    fetch(BASE_URL + '/evento/rh/listar/' + EVENTO_ID, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) {
        if (r.status === 401) { handleSessionExpired(); return; }
        throw new Error('HTTP ' + r.status);
      }
      return r.json();
    })
    .then(data => {
      if (!data) return;
      if (data.success) {
        renderTabela(data.data || []);
        atualizarKPIs(data.stats || {});
        atualizarBadge((data.data || []).length);
      } else {
        if (data.error === 'Unauthorized') { handleSessionExpired(); return; }
        window.renderHTML(document.getElementById('rh-table-body'),
          '<div style="text-align:center;padding:40px;color:var(--red)">Erro: ' + escapeHtml(data.error || 'Erro desconhecido') + '</div>');
      }
    })
    .catch(err => {
      if (err.message && (err.message.indexOf('401') !== -1 || err.message.indexOf('Unauthorized') !== -1)) {
        handleSessionExpired(); return;
      }
      window.renderHTML(document.getElementById('rh-table-body'),
        '<div style="text-align:center;padding:40px;color:var(--red)">Erro ao carregar dados: ' + escapeHtml(err.message) + '</div>');
    });
  }

  // =====================================================
  // RENDERIZAR TABELA
  // =====================================================
  function renderTabela(colaboradores) {
    const container = document.getElementById('rh-table-body');
    if (!container) return;

    if (colaboradores.length === 0) {
      window.renderHTML(container, '<div style="text-align:center;padding:40px;color:var(--text-3)">Nenhum colaborador alocado</div>');
      return;
    }

const headers = [
      {label: 'Colaborador'},
      {label: 'Dias'},
      {label: 'Valor Total'},
      {label: 'Aceite do Termo'},
      {label: 'Presenca Hoje'},
      {label: 'Acoes'}
    ];

    const perPage = 15;
    const tableId = 'tbl-rh-colaboradores';

    // Build HTML table directly (copy of PHP renderTable structure)
    let html = '<div class="tbl-container" id="' + tableId + '-container">';

    // Toolbar with search and per-page selector
    html += '<div class="tbl-toolbar">';
    html += '<div class="tbl-search">';
    html += '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>';
    html += '<input type="text" class="tbl-search-input" placeholder="Buscar..." oninput="tblSearch(\'' + tableId + '\',this.value)"/>';
    html += '</div>';
    html += '<div class="tbl-per-page">';
    html += '<span>Linhas:</span>';
    html += '<select class="tbl-per-page-select" onchange="tblSetPerPage(\'' + tableId + '\',this.value)">';
    [5, 10, 25, 50].forEach(opt => {
      html += '<option value="' + opt + '"' + (opt === perPage ? ' selected' : '') + '>' + opt + '</option>';
    });
    html += '</select></div></div>';

    // Table
    html += '<div class="table-wrap">';
    html += '<table class="data-table striped hoverable" id="' + tableId + '" data-per-page="' + perPage + '" data-paginated="true">';

    // Headers
    html += '<thead><tr>';
    headers.forEach(h => {
      html += '<th>' + h.label + '</th>';
    });
    html += '</tr></thead>';

    // Rows
    html += '<tbody>';
    colaboradores.forEach(c => {
      const periodo = formatDateBR(c.data_inicio) + ' ate ' + formatDateBR(c.data_fim);
      const diaria = 'R$ ' + parseFloat(c.valor_diaria).toFixed(2);

      // Badge presenca hoje
      let presencaBadge = '<span class="badge sm gray">Aguardando</span>';
      if (c.presenca_hoje) {
        if (c.presenca_hoje.status === 'completo') {
          presencaBadge = '<span class="badge sm green">Completo</span>';
        } else if (c.presenca_hoje.status === 'parcial') {
          presencaBadge = c.presenca_hoje.hora_saida_real ?
            '<span class="badge sm green">Completo</span>' :
            '<span class="badge sm yellow">Entrada OK</span>';
        }
      }

      // Popover menu de ações (padrão componente td-act-dropdown)
      const popId = 'pop-rh-' + c.id;

      // Menu items
      let menuItems = '';
      menuItems += '<button type="button" class="td-act-item td-act-cyan" data-action="ver-presencas-rh" data-id-alocacao="' + c.id + '"><span>Presencas</span></button>';
      menuItems += '<button type="button" class="td-act-item td-act-blue" data-action="rh-marcar-presenca-direta" data-id-alocacao="' + c.id + '" data-nome="' + escapeHtml(c.colaborador_nome) + '"><span>Presenca Manual</span></button>';
      menuItems += '<button type="button" class="td-act-item td-act-red" data-action="desalocar" data-id="' + c.id + '"><span>Desalocar</span></button>';

      const acoesHtml =
        '<div class="td-actions"><div class="td-act-menu">' +
          '<button type="button" class="btn btn-sm td-act-toggle" data-action="toggle-pop" data-pop-id="' + popId + '">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' +
            '</svg>' +
          '</button>' +
          '<div class="td-act-dropdown" id="' + popId + '">' +
            menuItems +
          '</div>' +
        '</div></div>';

      // Badge de confirmação de escala (disponibilidade)
      let escalaBadge = '';
      if (c.confirmado === 'P') {
        escalaBadge = '<span class="badge sm yellow">Pendente</span>';
      } else if (c.confirmado === 'C') {
        escalaBadge = '<span class="badge sm green">Aceito</span>';
      } else if (c.confirmado === 'R') {
        escalaBadge = '<span class="badge sm red">Recusado</span>';
      } else {
        escalaBadge = '<span class="badge sm yellow">Pendente</span>';
      }

      html += '<tr data-id="' + c.id + '">';
      // Coluna Colaborador: Foto + Nome + Funcao + Periodo + Diaria
      const fotoUrl = BASE_URL + '/colaboradores/foto/' + c.id_colaborador;
      html += '<td>' +
        '<div style="display:flex;align-items:center;gap:12px">' +
          '<img src="' + fotoUrl + '" onerror="this.src=\'data:image/svg+xml;utf8,<svg xmlns=\\\'http://www.w3.org/2000/svg\\\' fill=\\\'none\\\' viewBox=\\\'0 0 24 24\\\' stroke=\\\'%239ca3af\\\'><path stroke-linecap=\\\'round\\\' stroke-linejoin=\\\'round\\\' stroke-width=\\\'2\\\' d=\\\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\\\'/></svg>\'" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:1px solid var(--bg-border-sub);background:var(--bg-surface);flex-shrink:0;"/>' +
          '<div style="line-height:1.6">' +
            '<div style="font-weight:700;color:var(--text-1)">' + escapeHtml(c.colaborador_nome) + '</div>' +
            '<div style="font-size:12px;color:var(--text-3)">' + escapeHtml(c.funcao) + '</div>' +
            '<div style="font-size:12px;color:var(--text-2);margin-top:4px">' + periodo + ' &middot; ' + diaria + '</div>' +
          '</div>' +
        '</div>' +
      '</td>';
      // Coluna Dias
      html += '<td style="text-align:center;font-weight:600">' + (c.dias || 1) + '</td>';
      // Coluna Valor Total
      const valTotal = parseFloat(c.total_bruto || 0);
      html += '<td style="text-align:center"><span class="badge sm cyan">R$ ' + valTotal.toFixed(2).replace('.', ',') + '</span></td>';
      html += '<td style="text-align:center">' + escalaBadge + '</td>';
      html += '<td style="text-align:center">' + presencaBadge + '</td>';
      html += '<td>' + acoesHtml + '</td>';
      html += '</tr>';
    });
    html += '</tbody></table></div>';

    // Pagination footer
    html += '<div class="tbl-pagination">';
    html += '<div class="tbl-info" id="' + tableId + '-info">Mostrando 0 de 0</div>';
    html += '<div class="tbl-pages" id="' + tableId + '-pages"></div>';
    html += '</div>';

    // No results message
    html += '<div class="tbl-no-results" id="' + tableId + '-no-results" style="display:none">';
    html += '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>';
    html += '<span>Nenhum resultado encontrado</span>';
    html += '</div>';

    html += '</div>';

    window.renderHTML(container, html);
  }

  // =====================================================
  // ATUALIZAR KPIs
  // =====================================================
  function atualizarKPIs(stats) {
    const el1 = document.getElementById('rh-total-alocados');
    const el2 = document.getElementById('rh-presencas-hoje');
    const el3 = document.getElementById('rh-total-valor');
    const el4 = document.getElementById('rh-total-comissao');
    if (el1) el1.textContent = stats.total_alocados || '0';
    if (el2) el2.textContent = stats.presencas_hoje || '0';
    if (el3) el3.textContent = 'R$ ' + parseFloat(stats.total_diarias || 0).toFixed(2).replace('.', ',');
    if (el4) {
      let comissao = parseFloat(stats.total_comissao || 0);
      if (!comissao && (stats.total_venda || 0)) {
        comissao = parseFloat(stats.total_venda) * 0.1;
      }
      el4.textContent = 'R$ ' + comissao.toFixed(2).replace('.', ',');
    }
  }

  function atualizarBadge(count) {
    const badge = document.getElementById('rh-badge-count');
    if (badge) badge.textContent = count + ' item(s)';
  }

  // =====================================================
  // MODAL ALOCAR
  // =====================================================

  function fallbackLocalFuncoes() {
    const filterSelect = document.getElementById('modal-filter-funcao');
    if (!filterSelect) return;
    const funcoes = {};
    COLABORADORES_LIST.forEach(c => {
      if (c.atua_como) {
        const funcTrim = c.atua_como.trim();
        if (funcTrim) funcoes[funcTrim] = true;
      }
    });
    const uniqueFuncoes = Object.keys(funcoes).sort();
    let filterHtml = '<option value="">Todas as Funções</option>';
    uniqueFuncoes.forEach(f => {
      filterHtml += '<option value="' + escapeHtml(f) + '">' + escapeHtml(f) + '</option>';
    });
    window.renderHTML(filterSelect, filterHtml);
  }

  window.abrirModalAlocar = function() {
    // Limpar selecao anterior
    const hiddenInput = document.getElementById('modal-select-colaborador');
    if (hiddenInput) hiddenInput.value = '';
    
    const searchInput = document.getElementById('modal-search-colab');
    if (searchInput) searchInput.value = '';
    
    // Carregar lista lazy (uma vez) e depois popular modal
    carregarColaboradoresList().then(() => {
      return new Promise(resolve => {
        // Popular dropdown de Funcoes da API central (profoxnetworks.com.br)
        const filterSelect = document.getElementById('modal-filter-funcao');
        if (filterSelect) {
          window.renderHTML(filterSelect, '<option value="">Carregando...</option>');
          
          fetch(BASE_URL + '/funcoes')
            .then(r => r.json())
            .then(res => {
              if (res && res.success && Array.isArray(res.data)) {
                let filterHtml = '<option value="">Todas as Funções</option>';
                res.data.forEach(f => {
                  if (f.nome) {
                    filterHtml += '<option value="' + escapeHtml(f.nome.trim()) + '">' + escapeHtml(f.nome.trim()) + '</option>';
                  }
                });
                window.renderHTML(filterSelect, filterHtml);
              } else {
                fallbackLocalFuncoes();
              }
              resolve();
            })
            .catch(err => {
              fallbackLocalFuncoes();
              resolve();
            });
        } else {
          resolve();
        }
      });
    }).then(() => {
      window.filtrarColaboradores();

      // Preencher datas padrao
      const evento = window.RH_EVENTO_DATA || {};
      if (evento.data_inicio) document.getElementById('modal-data-inicio').value = evento.data_inicio;
      if (evento.data_fim) document.getElementById('modal-data-fim').value = evento.data_fim;
      if (evento.hora_inicio) document.getElementById('modal-hora-inicio').value = evento.hora_inicio;
      if (evento.hora_fim) document.getElementById('modal-hora-fim').value = evento.hora_fim;

      openModal('modal-alocar-colaborador');
    });
  };

  window.selecionarColaboradorAlocacao = function(id, element) {
    // Remove active styles from all rows
    const rows = document.querySelectorAll('.colab-select-row');
    rows.forEach(row => {
      row.style.borderColor = 'var(--bg-border-sub)';
      row.style.background = 'var(--bg-card)';
      row.style.boxShadow = 'none';
    });
    
    // Add active styling to the selected row
    element.style.borderColor = 'var(--neon-cyan)';
    element.style.background = 'rgba(11,110,140,0.06)';
    element.style.boxShadow = '0 0 10px var(--neon-cyan-glow)';
    
    // Set hidden input value
    const hiddenInput = document.getElementById('modal-select-colaborador');
    if (hiddenInput) {
      hiddenInput.value = id;
    }
    
    // Pre-populate Function in Event field if it is empty
    const colab = COLABORADORES_LIST.find(c => c.id == id);
    const inputFuncao = document.getElementById('modal-funcao');
    if (colab && inputFuncao && !inputFuncao.value.trim()) {
      inputFuncao.value = colab.atua_como || '';
    }
  };

  window.filtrarColaboradores = function() {
    let searchVal = '';
    const searchInput = document.getElementById('modal-search-colab');
    if (searchInput) searchVal = searchInput.value.toLowerCase().trim();
    
    let filterFunc = '';
    const filterSelect = document.getElementById('modal-filter-funcao');
    if (filterSelect && filterSelect.value !== 'Carregando...') {
      filterFunc = filterSelect.value;
    }
    
    const filtrados = COLABORADORES_LIST.filter(c => {
      // Filter by function
      if (filterFunc && (!c.atua_como || c.atua_como.trim() !== filterFunc)) {
        return false;
      }
      
      // Filter by search text (name or phone)
      if (searchVal) {
        const nomeMatch = c.nome && c.nome.toLowerCase().indexOf(searchVal) !== -1;
        const telMatch = c.telefone && c.telefone.indexOf(searchVal) !== -1;
        if (!nomeMatch && !telMatch) {
          return false;
        }
      }
      
      return true;
    });
    
    renderListaColaboradores(filtrados);
  };

  function renderListaColaboradores(filtrados) {
    const container = document.getElementById('modal-colaboradores-lista-container');
    if (!container) return;
    
    if (!filtrados || filtrados.length === 0) {
      window.renderHTML(container, '<div style="text-align:center;padding:20px;color:var(--text-3);font-size:13px;">Nenhum colaborador encontrado</div>');
      return;
    }
    
    let html = '';
    filtrados.forEach(c => {
      const fotoUrl = BASE_URL + '/colaboradores/foto/' + c.id;
      html += 
        '<div class="colab-select-row" data-id="' + c.id + '" style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;cursor:pointer;margin-bottom:6px;border:1.5px solid var(--bg-border-sub);background:var(--bg-card);transition:all 0.2s;" data-action="selecionar-colaborador-alocacao" data-colaborador-id="' + c.id + '">' +
        '  <img src="' + fotoUrl + '" onerror="this.src=\'data:image/svg+xml;utf8,<svg xmlns=\\\'http://www.w3.org/2000/svg\\\' fill=\\\'none\\\' viewBox=\\\'0 0 24 24\\\' stroke=\\\'%239ca3af\\\'><path stroke-linecap=\\\'round\\\' stroke-linejoin=\\\'round\\\' stroke-width=\\\'2\\\' d=\\\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\\\'/></svg>\'" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:1px solid var(--bg-border-sub);background:var(--bg-surface);flex-shrink:0;"/>' +
        '  <div style="flex:1;min-width:0;">' +
        '    <div style="font-weight:600;color:var(--text-1);font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(c.nome) + '</div>' +
        '    <div style="font-size:11.5px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">' +
        '      <span class="badge sm purple" style="font-size:10px;padding:1px 6px;margin-right:6px;">' + escapeHtml(c.atua_como || 'Sem função') + '</span>' +
        '      <span>' + escapeHtml(c.telefone || 'Sem tel') + '</span>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    });
    
    window.renderHTML(container, html);
  }

  function confirmarAlocacao() {
    const idColaborador = document.getElementById('modal-select-colaborador').value;
    const funcao = document.getElementById('modal-funcao').value.trim();
    const dataInicio = document.getElementById('modal-data-inicio').value;
    const dataFim = document.getElementById('modal-data-fim').value;
    const horaInicio = document.getElementById('modal-hora-inicio').value;
    const horaFim = document.getElementById('modal-hora-fim').value;
    const valorDiaria = document.getElementById('modal-valor-diaria').value;
    const vencimento = document.getElementById('modal-vencimento').value;

    if (!idColaborador) { showToast('red', 'Erro', 'Selecione um colaborador'); return; }
    if (!funcao) { showToast('red', 'Erro', 'Informe a funcao'); return; }
    if (!dataInicio || !dataFim) { showToast('red', 'Erro', 'Informe o periodo'); return; }
    if (!horaInicio || !horaFim) { showToast('red', 'Erro', 'Informe os horarios'); return; }

    const btn = document.getElementById('btn-confirmar-alocacao');
    btn.disabled = true;
    btn.textContent = 'Processando...';

    let body = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN) +
      '&id_evento=' + EVENTO_ID +
      '&id_colaborador=' + idColaborador +
      '&funcao=' + encodeURIComponent(funcao) +
      '&data_inicio=' + dataInicio +
      '&data_fim=' + dataFim +
      '&hora_inicio=' + horaInicio +
      '&hora_fim=' + horaFim +
      '&valor_diaria=' + valorDiaria;
    if (vencimento) body += '&data_vencimento_pagamento=' + vencimento;

    fetch(BASE_URL + '/evento/rh/alocar', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Colaborador alocado com sucesso');
        closeModal('modal-alocar-colaborador');
        // Limpar formulario
        document.getElementById('modal-select-colaborador').value = '';
        document.getElementById('modal-funcao').value = '';
        document.getElementById('modal-data-inicio').value = '';
        document.getElementById('modal-data-fim').value = '';
        document.getElementById('modal-hora-inicio').value = '';
        document.getElementById('modal-hora-fim').value = '';
        document.getElementById('modal-valor-diaria').value = '';
        document.getElementById('modal-vencimento').value = '';
        carregarDados();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao alocar');
      }
    })
    .catch(err => {
      showToast('red', 'Erro', 'Erro de conexao: ' + err.message);
    })
    .finally(() => {
      btn.disabled = false;
      btn.textContent = 'Alocar';
    });
  }

  // =====================================================
  // DESALOCAR
  // =====================================================
  window.desalocar = function(id, btn) {
    if (!confirm('Deseja desalocar este colaborador do evento?')) return;

    btn.disabled = true;
    btn.textContent = '...';

    fetch(BASE_URL + '/evento/rh/desalocar/' + id, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Colaborador desalocado');
        carregarDados();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao desalocar');
        btn.disabled = false;
        btn.textContent = 'X';
      }
    })
    .catch(err => {
      showToast('red', 'Erro', 'Erro de conexao: ' + err.message);
      btn.disabled = false;
      btn.textContent = 'X';
    });
  };

  // =====================================================
  // VER PRESENCAS
  // =====================================================
  window.verPresencas = function(idAlocacao) {
    // Buscar nome do colaborador
    const row = document.querySelector('#rh-table-body tr[data-id="' + idAlocacao + '"]');
    const nome = row ? (row.querySelector('td:first-child strong')?.textContent || 'Colaborador') : 'Colaborador';

    document.getElementById('rh-presenca-id-alocacao').value = idAlocacao;
    document.getElementById('rh-presenca-nome').textContent = nome;
    
    const contentEl = document.getElementById('modal-presencas-content');
    window.renderHTML(contentEl, '<div style="text-align:center;padding:40px;color:var(--text-3)">Carregando...</div>');

    openModal('modal-ver-presencas');

    fetch(BASE_URL + '/evento/rh/presencas/' + idAlocacao, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (data.success) {
        renderPresencas(data.data);
      } else {
        window.renderHTML(contentEl,
          '<div style="text-align:center;padding:20px;color:var(--red)">' + escapeHtml(data.error || 'Erro desconhecido') + '</div>');
      }
    })
    .catch(err => {
      window.renderHTML(contentEl,
        '<div style="text-align:center;padding:20px;color:var(--red)">Erro ao carregar presencas: ' + escapeHtml(err.message) + '</div>');
    });
  };

  // =====================================================
  // MARCAR PRESENCA MANUAL (RH)
  // =====================================================
  window.rhAbrirModalPresencaManual = function() {
    const idAlocacao = document.getElementById('rh-presenca-id-alocacao').value;
    const nome = document.getElementById('rh-presenca-nome').textContent;
    rhAbrirModalPresencaManualDireta(idAlocacao, nome);
  };

  window.rhMarcarPresencaDireta = function(idAlocacao, nome) {
    rhAbrirModalPresencaManualDireta(idAlocacao, nome);
  };

  function rhAbrirModalPresencaManualDireta(idAlocacao, nome) {
    // Reset form
    document.getElementById('rh-manual-presenca-id-alocacao').value = idAlocacao;
    document.getElementById('rh-manual-presenca-nome').textContent = nome;
    document.getElementById('rh-manual-presenca-data').value = '';
    document.getElementById('rh-manual-presenca-hora-entrada').value = '';
    document.getElementById('rh-manual-presenca-hora-saida').value = '';
    document.getElementById('rh-manual-presenca-foto-entrada').value = '';
    document.getElementById('rh-manual-presenca-foto-saida').value = '';
    document.getElementById('rh-manual-presenca-status').value = 'parcial';
    document.getElementById('rh-manual-presenca-obs').value = '';

    closeModal('modal-ver-presencas');
    openModal('modal-rh-presenca-manual');
  }

  window.rhSalvarPresencaManual = function() {
    const idAlocacao = document.getElementById('rh-manual-presenca-id-alocacao').value;
    const data = document.getElementById('rh-manual-presenca-data').value;
    const horaEntrada = document.getElementById('rh-manual-presenca-hora-entrada').value;
    const horaSaida = document.getElementById('rh-manual-presenca-hora-saida').value;
    const status = document.getElementById('rh-manual-presenca-status').value;
    const obs = document.getElementById('rh-manual-presenca-obs').value;
    const fotoEntradaInput = document.getElementById('rh-manual-presenca-foto-entrada');
    const fotoSaidaInput = document.getElementById('rh-manual-presenca-foto-saida');

    // Validacoes
    if (!data) { showToast('yellow', 'Atencao', 'Informe a data da presenca'); return; }
    if (!horaEntrada && !horaSaida) { showToast('yellow', 'Atencao', 'Informe pelo menos o horario de entrada ou saida'); return; }
    if (status === 'completo' && !horaSaida) { showToast('yellow', 'Atencao', 'Para status completo, informe o horario de saida'); return; }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_alocacao', idAlocacao);
    formData.append('data', data);
    formData.append('hora_entrada', horaEntrada || '');
    formData.append('hora_saida', horaSaida || '');
    formData.append('status', status);
    formData.append('observacao', obs || '');
    formData.append('manual', 'S');

    // Adicionar fotos se existirem
    if (fotoEntradaInput.files.length > 0) {
      formData.append('foto_entrada', fotoEntradaInput.files[0]);
    }
    if (fotoSaidaInput.files.length > 0) {
      formData.append('foto_saida', fotoSaidaInput.files[0]);
    }

    closeModal('modal-rh-presenca-manual');
    showToast('cyan', 'Processando', 'Registrando presenca manual...');

    fetch(BASE_URL + '/fechamento/presenca/manual', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Presenca manual registrada com sucesso');
        // Recarregar dados para atualizar badges
        carregarDados();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao registrar presenca manual');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  };

  window.ampliarFoto = function(src) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.background = 'rgba(0,0,0,0.95)';
    overlay.style.zIndex = '99999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.cursor = 'zoom-out';
    
    const img = document.createElement('img');
    img.src = src;
    img.style.maxWidth = '90vw';
    img.style.maxHeight = '90vh';
    img.style.objectFit = 'contain';
    img.style.borderRadius = '8px';
    img.style.border = '2px solid #333';
    
    overlay.appendChild(img);
    overlay.onclick = () => {
      document.body.removeChild(overlay);
    };
    document.body.appendChild(overlay);
  };

  function renderPresencas(dados) {
    const aloc = dados.alocacao;
    const presencas = dados.presencas || [];
    const totais = dados.totais || {};

    let html = '<div style="margin-bottom:16px;padding:12px;background:var(--bg-elevated);border-radius:8px">' +
      '<div style="font-weight:700">' + escapeHtml(aloc.colaborador_nome) + '</div>' +
      '<div style="font-size:12px;color:var(--text-3)">' + escapeHtml(aloc.funcao) + ' | ' +
      formatDateBR(aloc.data_inicio) + ' ate ' + formatDateBR(aloc.data_fim) + '</div>' +
      '</div>';

    if (presencas.length === 0) {
      html += '<div style="text-align:center;padding:20px;color:var(--text-3)">Nenhuma presenca registrada</div>';
    } else {
      html += '<table class="tbl"><thead><tr><th>Data</th><th>Status</th><th>Entrada</th><th>Saida</th><th>Manual</th></tr></thead><tbody>';
      presencas.forEach(p => {
        const statusColor = p.status === 'completo' ? 'green' : (p.status === 'parcial' ? 'yellow' : 'gray');
        const manualBadge = p.registro_manual === 'S' 
          ? '<span class="badge sm purple">Manual</span>' 
          : '<span style="color:var(--text-3);font-size:11px">Auto</span>';
          
        let entradaHtml = '-';
        if (p.hora_entrada_real) {
          const timeStr = formatDateTimeBR(p.hora_entrada_real);
          let photoHtml = '';
          if (p.foto_entrada) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + BASE_URL + '/' + p.foto_entrada + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" data-action="ampliar-foto" data-src="' + BASE_URL + '/' + p.foto_entrada + '" /></div>';
          }
          let mapHtml = '';
          if (p.geo_entrada_lat && parseFloat(p.geo_entrada_lat) !== 0) {
            mapHtml = '<div style="margin-top:4px;"><a href="https://www.google.com/maps/search/?api=1&query=' + p.geo_entrada_lat + ',' + p.geo_entrada_lng + '" target="_blank" class="badge sm info" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-size:10px;padding:2px 6px;">🗺️ Mapa</a></div>';
          }
          entradaHtml = '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            '<span>' + timeStr + '</span>' +
            photoHtml +
            mapHtml +
            '</div>';
        }

        let saidaHtml = '-';
        if (p.hora_saida_real) {
          const timeStr = formatDateTimeBR(p.hora_saida_real);
          let photoHtml = '';
          if (p.foto_saida) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + BASE_URL + '/' + p.foto_saida + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" data-action="ampliar-foto" data-src="' + BASE_URL + '/' + p.foto_saida + '" /></div>';
          }
          let mapHtml = '';
          if (p.geo_saida_lat && parseFloat(p.geo_saida_lat) !== 0) {
            mapHtml = '<div style="margin-top:4px;"><a href="https://www.google.com/maps/search/?api=1&query=' + p.geo_saida_lat + ',' + p.geo_saida_lng + '" target="_blank" class="badge sm info" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-size:10px;padding:2px 6px;">🗺️ Mapa</a></div>';
          }
          saidaHtml = '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            '<span>' + timeStr + '</span>' +
            photoHtml +
            mapHtml +
            '</div>';
        }

        html += '<tr>' +
          '<td>' + formatDateBR(p.data) + '</td>' +
          '<td><span class="badge sm ' + statusColor + '">' + p.status + '</span></td>' +
          '<td>' + entradaHtml + '</td>' +
          '<td>' + saidaHtml + '</td>' +
          '<td>' + manualBadge + '</td>' +
          '</tr>';
      });
      html += '</tbody></table>';
    }

    // Totais
    html += '<div style="margin-top:16px;padding:12px;background:var(--bg-elevated);border-radius:8px;text-align:center">' +
      '<div style="font-size:12px;color:var(--text-3)">Total de Diarias Completas</div>' +
      '<div style="font-size:24px;font-weight:700;color:var(--green)">' + (totais.total_presencas_completas || 0) + '</div>' +
      '<div style="font-size:12px;color:var(--text-3)">Valor Total: R$ ' + (parseFloat(totais.total_a_pagar || 0)).toFixed(2) + '</div>' +
      '</div>';

    window.renderHTML(document.getElementById('modal-presencas-content'), html);
  }

  // =====================================================
  // HELPERS
  // =====================================================


  function formatDateTimeBR(dateTimeStr) {
    if (!dateTimeStr) return '-';
    const parts = dateTimeStr.split(' ');
    const datePart = parts[0].split('-');
    const timePart = parts[1] ? parts[1].substring(0, 5) : '';
    if (datePart.length === 3) return datePart[2] + '/' + datePart[1] + '/' + datePart[0] + (timePart ? ' ' + timePart : '');
    return dateTimeStr;
  }

  // =====================================================
  // MUTATION OBSERVER
  // =====================================================
  const pane = document.getElementById('loc-hpane-3');
  if (pane) {
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        if (mutation.attributeName === 'class' && pane.classList.contains('active')) {
          setTimeout(init, 150);
        }
      });
    });
    observer.observe(pane, { attributes: true });
    if (pane.classList.contains('active')) {
      setTimeout(init, 200);
    }
  } else {
    if (document.readyState !== 'loading') { init(); }
    else { document.addEventListener('DOMContentLoaded', init); }
  }

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel.
    // "ver-presencas-rh" -> camelCase "verPresencasRh", mas a funcao real e
    // "verPresencas" (sem "Rh") -- botao "Presencas" da aba RH nunca funcionou.
    if (window.registerAction) {
      window.registerAction('ver-presencas-rh', function(el) {
        window.verPresencas(el.dataset.idAlocacao);
      });
      window.registerAction('desalocar', function(el) {
        window.desalocar(el.dataset.id, el);
      });
      window.registerAction('rh-marcar-presenca-direta', function(el) {
        window.rhMarcarPresencaDireta(el.dataset.idAlocacao, el.dataset.nome);
      });
      window.registerAction('selecionar-colaborador-alocacao', function(el) {
        window.selecionarColaboradorAlocacao(el.dataset.colaboradorId, el);
      });
    }
  });
})();

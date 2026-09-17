/**
 * Modulo: Fechamento - Colaboradores, Presencas e Horas Extras
 * Depende de: fechamento.js (deve ser carregado antes deste arquivo)
 */
(function() {
  'use strict';
  const EVENTO_ID  = window.EVENTO_ID;
  const CSRF_TOKEN = window.CSRF_TOKEN;
  const BASE_URL   = window.BASE_URL;
  const U          = window._FechUtils;

  const fetchJson        = U.fetch;
  const formatMoney      = U.money;

  const formatDateTimeBR = U.dateTimeBR;
  const escapeHtml       = U.html;
  const escapeJs         = U.js;
  const showLoading      = U.showLoading;
  const hideLoading      = U.hideLoading;
  const showContent      = U.showContent;
  const showEmpty        = U.showEmpty;

  // ========================================
  // Colaboradores
  // ========================================
  function loadColaboradores() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-colaboradores');
    fetchJson(BASE_URL + '/fechamento/colaboradores/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((data) => {
      hideLoading('fechamento-colaboradores');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-colaboradores');
        return;
      }
      renderColaboradores(data.data);
      showContent('fechamento-colaboradores');
    })
    .catch((err) => {
      hideLoading('fechamento-colaboradores');
      showToast('red', 'Erro', 'Erro ao carregar colaboradores');
    });
  }

  function renderColaboradores(lista) {
    const container = document.getElementById('fechamento-colaboradores-content');
    if (!container) return;

    const defaultVenc = new Date();
    defaultVenc.setDate(defaultVenc.getDate() + 30);
    const vencDefault = defaultVenc.toISOString().split('T')[0];

    let html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Colaborador</th>' +
      '<th>Presenca</th>' +
      '<th>Horas Extras</th>' +
      '<th>Valores</th>' +
      '<th>Vencimento</th>' +
      '<th>Pagamento</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    lista.forEach((c) => {
      const periodo = formatDateBR(c.data_inicio) + ' a ' + formatDateBR(c.data_fim);
      let presencaBtn = '';
      if (c.dias_presentes >= c.dias_total && c.dias_total > 0) {
        presencaBtn = '<button type="button" class="btn btn-sm btn-green" data-action="ver-presencas" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '" title="Ver presencas">Completo</button>';
      } else if (c.dias_presentes > 0) {
        presencaBtn = '<button type="button" class="btn btn-sm btn-yellow" data-action="ver-presencas" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '" title="Ver presencas">' + c.dias_presentes + '/' + c.dias_total + '</button>';
      } else {
        presencaBtn = '<button type="button" class="btn btn-sm btn-red" data-action="ver-presencas" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '" title="Ver presencas">Ausente</button>';
      }

      const totalHE = parseFloat(c.total_horas_extras) || 0;
      let heBadge = '';
      if (totalHE > 0) {
        heBadge = '<button type="button" class="btn btn-sm btn-purple" data-action="abrir-modal-horas-extras" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '" title="Gerenciar Horas Extras">' +
          'R$ ' + formatMoney(totalHE) +
          '</button>';
      } else {
        heBadge = '<button type="button" class="btn btn-sm btn-cyan" data-action="abrir-modal-horas-extras" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '">Lançar</button>';
      }

      const pagoBadge = c.ja_enviado_pagamento == 1
        ? '<span class="badge sm green">Enviado</span>'
        : '<span class="badge sm yellow">Pendente</span>';

      const popId = 'pop-fech-' + c.id_alocacao;
      const acoesHtml =
        '<div class="td-actions"><div class="td-act-menu">' +
          '<button type="button" class="btn btn-sm td-act-toggle" data-action="toggle-pop" data-pop-id="' + popId + '">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' +
            '</svg>' +
          '</button>' +
          '<div class="td-act-dropdown" id="' + popId + '">' +
            '<button type="button" class="td-act-item td-act-cyan" data-action="ver-presencas" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '"><span>Presencas</span></button>' +
            '<button type="button" class="td-act-item td-act-purple" data-action="abrir-modal-horas-extras" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '"><span>Horas Extras</span></button>' +
            (c.ja_enviado_pagamento != 1
              ? '<button type="button" class="td-act-item td-act-green" data-action="abrir-modal-colaborador" data-id-alocacao="' + c.id_alocacao + '" data-nome="' + escapeJs(c.nome) + '" data-valor="' + c.valor_total + '"><span>Enviar Pgto</span></button>'
              : '<button type="button" class="td-act-item td-act-gray" disabled><span>Ja Enviado</span></button>') +
          '</div>' +
        '</div></div>';

      const vencimentoInput = c.ja_enviado_pagamento != 1
        ? '<input type="date" class="fi" id="colab-venc-' + c.id_alocacao + '" value="' + (c.data_vencimento_pagamento || vencDefault) + '" style="width:140px">'
        : (c.data_vencimento_pagamento ? formatDateBR(c.data_vencimento_pagamento) : '-');

      const colaboradorHtml =
        '<div style="line-height:1.6">' +
          '<div style="font-weight:700;color:var(--text-1)">' + escapeHtml(c.nome) + '</div>' +
          '<div style="font-size:12px;color:var(--text-3)">' + escapeHtml(c.funcao) + '</div>' +
          '<div style="font-size:12px;color:var(--text-2);margin-top:4px">' + periodo + ' &middot; <strong>' + c.dias + ' dias</strong></div>' +
        '</div>';

      const extrasHtml = totalHE > 0
        ? '<div style="font-size:12px;color:var(--neon-purple);font-weight:600">Extras: R$ ' + formatMoney(totalHE) + '</div>'
        : '';
      const valoresHtml =
        '<div style="line-height:1.6">' +
          '<div style="font-size:12px;color:var(--text-3)">Diária: R$ ' + formatMoney(c.valor_diaria) + '</div>' +
          extrasHtml +
          '<div style="font-weight:700;color:var(--text-1)">Total: R$ ' + formatMoney(c.valor_total) + '</div>' +
        '</div>';

      html += '<tr>' +
        '<td>' + colaboradorHtml + '</td>' +
        '<td style="text-align:center">' + presencaBtn + '</td>' +
        '<td style="text-align:center">' + heBadge + '</td>' +
        '<td>' + valoresHtml + '</td>' +
        '<td>' + vencimentoInput + '</td>' +
        '<td>' + pagoBadge + '</td>' +
        '<td>' + acoesHtml + '</td></tr>';
    });

    html += '</tbody></table>';
    window.renderHTML(container, '<div class="table-wrap">' + html + '</div>');
  }

  // ========================================
  // Presencas
  // ========================================
  function verPresencas(idAlocacao, nome) {
    document.getElementById('modal-presenca-id-alocacao').value = idAlocacao;
    document.getElementById('modal-presenca-nome').textContent = nome;

    const listaEl = document.getElementById('modal-presenca-lista');
    window.renderHTML(listaEl, '<div style="text-align:center;padding:20px">' + renderSpinner({variant:'dots',size:'sm'}) + '</div>');

    openModal('modal-presencas');

    fetch(window.BASE_URL + '/fechamento/presencas/' + idAlocacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (!data.success || !data.data || data.data.length === 0) {
        window.renderHTML(listaEl, '<div style="text-align:center;padding:20px;color:var(--text-3)">Nenhuma presenca registrada</div>');
        return;
      }
      if (!window.ampliarFoto) {
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
          overlay.onclick = () => { document.body.removeChild(overlay); };
          document.body.appendChild(overlay);
        };
      }

      let html = '<table class="tbl" width="100%"><thead><tr><th>Data</th><th>Status</th><th>Entrada</th><th>Saida</th></tr></thead><tbody>';
      data.data.forEach((p) => {
        const statusBadge = p.status === 'completo' ? '<span class="badge sm green">Completo</span>'
          : p.status === 'parcial' ? '<span class="badge sm yellow">Parcial</span>'
          : '<span class="badge sm gray">Aguardando</span>';

        let entradaHtml = '-';
        if (p.hora_entrada_real) {
          const timeStr = formatDateTimeBR(p.hora_entrada_real);
          let photoHtml = '';
          if (p.foto_entrada) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + window.BASE_URL + '/' + p.foto_entrada + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" data-action="ampliar-foto" data-src="' + window.BASE_URL + '/' + p.foto_entrada + '" /></div>';
          }
          let mapHtml = '';
          if (p.geo_entrada_lat && parseFloat(p.geo_entrada_lat) !== 0) {
            mapHtml = '<div style="margin-top:4px;"><a href="https://www.google.com/maps/search/?api=1&query=' + p.geo_entrada_lat + ',' + p.geo_entrada_lng + '" target="_blank" class="badge sm info" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-size:10px;padding:2px 6px;">🗺️ Mapa</a></div>';
          }
          entradaHtml = '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            '<span>' + timeStr + '</span>' + photoHtml + mapHtml + '</div>';
        }

        let saidaHtml = '-';
        if (p.hora_saida_real) {
          const timeStr = formatDateTimeBR(p.hora_saida_real);
          let photoHtml = '';
          if (p.foto_saida) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + window.BASE_URL + '/' + p.foto_saida + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" data-action="ampliar-foto" data-src="' + window.BASE_URL + '/' + p.foto_saida + '" /></div>';
          }
          let mapHtml = '';
          if (p.geo_saida_lat && parseFloat(p.geo_saida_lat) !== 0) {
            mapHtml = '<div style="margin-top:4px;"><a href="https://www.google.com/maps/search/?api=1&query=' + p.geo_saida_lat + ',' + p.geo_saida_lng + '" target="_blank" class="badge sm info" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-size:10px;padding:2px 6px;">🗺️ Mapa</a></div>';
          }
          saidaHtml = '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            '<span>' + timeStr + '</span>' + photoHtml + mapHtml + '</div>';
        }

        html += '<tr>' +
          '<td>' + formatDateBR(p.data) + '</td>' +
          '<td>' + statusBadge + '</td>' +
          '<td>' + entradaHtml + '</td>' +
          '<td>' + saidaHtml + '</td>' +
          '</tr>';
      });
      html += '</tbody></table>';
      window.renderHTML(listaEl, html);
    })
    .catch(() => { window.renderHTML(listaEl, '<div style="text-align:center;padding:20px;color:var(--neon-red)">Erro ao carregar presencas</div>'); });
  }

  // ========================================
  // Modal Colaborador - Pagamento
  // ========================================
  function abrirModalColaborador(idAlocacao, nome, valor) {
    document.getElementById('modal-colab-id-alocacao').value = idAlocacao;
    document.getElementById('modal-colab-nome').textContent = nome;
    document.getElementById('modal-colab-valor-original').value = valor;
    document.getElementById('modal-colab-valor-calculado').textContent = 'R$ ' + formatMoney(valor);
    const inputValor = document.getElementById('modal-colab-valor');
    inputValor.value = formatMoney(valor);
    const vencEl = document.getElementById('colab-venc-' + idAlocacao);
    const vencDefault = vencEl ? vencEl.value : '';
    const defaultVenc = new Date();
    defaultVenc.setDate(defaultVenc.getDate() + 30);
    document.getElementById('modal-colab-vencimento').value = vencDefault || defaultVenc.toISOString().split('T')[0];
    openModal('modal-colaborador-pagamento');
  }

  function enviarColaboradorPagamento() {
    const idAlocacao = document.getElementById('modal-colab-id-alocacao').value;
    const nome = document.getElementById('modal-colab-nome').textContent;
    const vencimento = document.getElementById('modal-colab-vencimento').value;
    const valorFormatado = document.getElementById('modal-colab-valor').value;
    const valorEditado = valorFormatado.replace(/\./g, '').replace(',', '.');

    if (!vencimento) {
      showToast('yellow', 'Atencao', 'Informe a data de vencimento');
      return;
    }
    if (!valorEditado || parseFloat(valorEditado) <= 0) {
      showToast('yellow', 'Atencao', 'Informe um valor valido para pagamento');
      return;
    }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('id_alocacao', idAlocacao);
    formData.append('data_vencimento', vencimento);
    formData.append('valor_pagamento', valorEditado);

    fetch(window.BASE_URL + '/fechamento/colaborador/pagamento', {
      method: 'POST',
      body: formData
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', nome + ' enviado para pagamento');
        closeModal('modal-colaborador-pagamento');
        loadColaboradores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao enviar');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  // ========================================
  // Horas Extras
  // ========================================
  function abrirModalHorasExtras(idAlocacao, nome) {
    document.getElementById('modal-he-id-alocacao').value = idAlocacao;
    document.getElementById('modal-he-nome').textContent = nome;

    document.getElementById('form-lancar-he').reset();

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('he-data').value = today;

    const listaEl = document.getElementById('he-lista');
    window.renderHTML(listaEl, '<div style="text-align:center;padding:20px">' + renderSpinner({variant:'dots',size:'sm'}) + '</div>');

    openModal('modal-horas-extras');

    fetch(window.BASE_URL + '/fechamento/horas-extras/' + idAlocacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (!data.success || !data.data || data.data.length === 0) {
        window.renderHTML(listaEl, '<div style="text-align:center;padding:20px;color:var(--text-4);font-size:13px">Nenhuma hora extra registrada para este colaborador.</div>');
        return;
      }
      let tblHtml = '<table class="tbl" width="100%"><thead><tr>' +
        '<th>Data</th><th>Horas</th><th>Valor</th><th>Motivo</th><th style="width:50px">Excluir</th>' +
        '</tr></thead><tbody>';

      data.data.forEach((he) => {
        tblHtml += '<tr>' +
          '<td>' + formatDateBR(he.data) + '</td>' +
          '<td>' + escapeHtml(he.horas) + '</td>' +
          '<td>R$ ' + formatMoney(he.valor) + '</td>' +
          '<td>' + escapeHtml(he.motivo) + '</td>' +
          '<td style="text-align:center">' +
            '<button type="button" class="btn btn-sm btn-red" data-action="deletar-hora-extra" data-id="' + he.id + '" data-id-alocacao="' + idAlocacao + '" data-nome="' + escapeJs(nome) + '" style="padding:6px 10px;" title="Excluir Hora Extra">' +
              '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
            '</button>' +
          '</td>' +
          '</tr>';
      });

      tblHtml += '</tbody></table>';
      window.renderHTML(listaEl, '<div class="table-wrap">' + tblHtml + '</div>');
    })
    .catch((err) => {
      window.renderHTML(listaEl, '<div style="text-align:center;padding:20px;color:var(--neon-red)">Erro ao carregar horas extras</div>');
    });
  }

  function salvarHoraExtra(event) {
    event.preventDefault();

    const idAlocacao = document.getElementById('modal-he-id-alocacao').value;
    const nome = document.getElementById('modal-he-nome').textContent;
    const data = document.getElementById('he-data').value;
    const horas = document.getElementById('he-horas').value;
    const valor = document.getElementById('he-valor').value;
    const motivo = document.getElementById('he-motivo').value;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_alocacao', idAlocacao);
    formData.append('data', data);
    formData.append('horas', horas);
    formData.append('valor', valor);
    formData.append('motivo', motivo);

    fetch(window.BASE_URL + '/fechamento/horas-extras/lancar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Hora extra lançada com sucesso');
        abrirModalHorasExtras(idAlocacao, nome);
        loadColaboradores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao lançar hora extra');
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro na requisição');
    });
  }

  function deletarHoraExtra(idHe, idAlocacao, nome) {
    if (!confirm('Deseja realmente excluir esta hora extra?')) return;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(window.BASE_URL + '/fechamento/horas-extras/deletar/' + idHe, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Hora extra excluída com sucesso');
        abrirModalHorasExtras(idAlocacao, nome);
        loadColaboradores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir hora extra');
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro na requisição');
    });
  }

  function renderSpinner(opts) {
    opts = opts || {};
    const size = opts.size === 'sm' ? '16px' : opts.size === 'lg' ? '40px' : '24px';
    const color = opts.variant === 'cyan' ? 'var(--neon-cyan)' : 'var(--text-3)';
    return '<div style="display:inline-block;width:' + size + ';height:' + size + ';border:3px solid ' + color + ';border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite"></div>';
  }

  // ========================================
  // Estender Public API
  // ========================================
  Object.assign(window.Fechamento, {
    verPresencas:          verPresencas,
    abrirModalColaborador: abrirModalColaborador,
    enviarColaborador:     enviarColaboradorPagamento,
    loadColaboradores:     loadColaboradores,
    abrirModalHorasExtras: abrirModalHorasExtras,
    salvarHoraExtra:       salvarHoraExtra,
    deletarHoraExtra:      deletarHoraExtra
  });

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel. O
    // dispatcher generico so busca window[fnName] plano, nao window.Fechamento[fnName].
    if (window.registerAction) {
      window.registerAction('abrir-modal-colaborador', function(el) {
        abrirModalColaborador(el.dataset.idAlocacao, el.dataset.nome, el.dataset.valor);
      });
      window.registerAction('abrir-modal-horas-extras', function(el) {
        abrirModalHorasExtras(el.dataset.idAlocacao, el.dataset.nome);
      });
      window.registerAction('deletar-hora-extra', function(el) {
        deletarHoraExtra(el.dataset.id, el.dataset.idAlocacao, el.dataset.nome);
      });
      window.registerAction('enviar-colaborador', function() { enviarColaboradorPagamento(); });
      window.registerAction('ver-presencas', function(el) { verPresencas(el.dataset.idAlocacao, el.dataset.nome); });
    }
  });
})();

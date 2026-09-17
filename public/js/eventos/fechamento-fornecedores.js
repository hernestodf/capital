/**
 * Modulo: Fechamento - Fornecedores, Parcelas e Contas a Pagar
 * Depende de: fechamento.js (deve ser carregado antes deste arquivo)
 */
(function() {
  'use strict';
  const EVENTO_ID  = window.EVENTO_ID;
  const CSRF_TOKEN = window.CSRF_TOKEN;
  const BASE_URL   = window.BASE_URL;
  const U          = window._FechUtils;

  const fetchJson    = U.fetch;
  const formatMoney  = U.money;

  const escapeHtml   = U.html;
  const escapeJs     = U.js;
  const showLoading  = U.showLoading;
  const hideLoading  = U.hideLoading;
  const showContent  = U.showContent;
  const showEmpty    = U.showEmpty;

  let FORNECEODRES_LIST_RAW = [];
  let lastEntrada = null;
  let lastNumParcelas = null;

  // ========================================
  // Fornecedores
  // ========================================
  function loadFornecedores() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-fornecedores');
    fetchJson(BASE_URL + '/fechamento/fornecedores/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((data) => {
      hideLoading('fechamento-fornecedores');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-fornecedores');
        return;
      }
      renderFornecedores(data.data);
      showContent('fechamento-fornecedores');
    })
    .catch((err) => {
      hideLoading('fechamento-fornecedores');
      showToast('red', 'Erro', 'Erro ao carregar fornecedores');
    });
  }

  function renderFornecedores(lista) {
    FORNECEODRES_LIST_RAW = lista;
    const container = document.getElementById('fechamento-fornecedores-content');
    if (!container) return;

    const rows = [];
    lista.forEach((f) => {
      if (f.ja_enviado_pagamento == 1 && f.contas_pagar_info && f.contas_pagar_info.length > 0) {
        f.contas_pagar_info.forEach((conta) => {
          rows.push({
            tipo: 'parcela',
            id_conta: conta.id,
            id_cotacao: f.id_cotacao,
            nome_fantasia: f.nome_fantasia,
            servico: f.servico,
            sala: f.sala,
            descricao: conta.descricao,
            valor: parseFloat(conta.valor),
            valor_pago: conta.valor_pago ? parseFloat(conta.valor_pago) : null,
            data_vencimento: conta.data_vencimento,
            data_pagamento: conta.data_pagamento,
            status: conta.status,
            tipo_pagamento: conta.tipo_pagamento,
            parcelas_qtd: conta.parcelas_qtd,
            ja_enviado_pagamento: 1
          });
        });
      } else {
        rows.push({
          tipo: 'pendente',
          id_cotacao: f.id_cotacao,
          nome_fantasia: f.nome_fantasia,
          servico: f.servico,
          sala: f.sala,
          valor: parseFloat(f.valor_proposto),
          ja_enviado_pagamento: 0
        });
      }
    });

    rows.sort((a, b) => {
      const statusOrder = {'VENCIDO': 1, 'PENDENTE': 2, 'PARCIAL': 3, 'PAGO': 4};
      const aOrder = a.status ? statusOrder[a.status] : 0;
      const bOrder = b.status ? statusOrder[b.status] : 0;
      if (aOrder !== bOrder) return aOrder - bOrder;
      if (a.data_vencimento && b.data_vencimento) return a.data_vencimento.localeCompare(b.data_vencimento);
      return 0;
    });

    let html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Fornecedor</th>' +
      '<th>Servico</th>' +
      '<th>Descricao</th>' +
      '<th>Valor</th>' +
      '<th>Vencimento</th>' +
      '<th>Status</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    rows.forEach((r) => {
      if (r.tipo === 'pendente') {
        html += '<tr>' +
          '<td><strong>' + escapeHtml(r.nome_fantasia) + '</strong></td>' +
          '<td>' + escapeHtml(r.servico) + (r.sala ? '<br><span style="font-size:11px;color:var(--text-3)">' + escapeHtml(r.sala) + '</span>' : '') + '</td>' +
          '<td style="color:var(--text-3)">-</td>' +
          '<td><strong>R$ ' + formatMoney(r.valor) + '</strong></td>' +
          '<td>-</td>' +
          '<td><span class="badge sm yellow">Pendente</span></td>' +
          '<td><button type="button" class="btn btn-sm btn-purple" data-action="abrir-modal-fornecedor" data-id-cotacao="' + r.id_cotacao + '" data-nome="' + escapeJs(r.nome_fantasia) + '" data-valor="' + r.valor + '">Enviar</button></td>' +
        '</tr>';
      } else {
        const isPago = r.status === 'PAGO';
        const isVencido = r.status === 'VENCIDO';
        const statusBadge = isPago ? '<span class="badge sm green">Pago</span>'
          : isVencido ? '<span class="badge sm red">Vencido</span>'
          : r.status === 'PARCIAL' ? '<span class="badge sm purple">Parcial</span>'
          : '<span class="badge sm yellow">Pendente</span>';

        let vencDisplay = r.data_vencimento ? formatDateBR(r.data_vencimento) : '-';
        if (isPago && r.data_pagamento) {
          vencDisplay += '<br><span style="font-size:10px;color:var(--text-3)">Pago: ' + formatDateBR(r.data_pagamento) + '</span>';
        }

        let valorDisplay = '<strong>R$ ' + formatMoney(r.valor) + '</strong>';
        if (r.valor_pago && isPago) {
          valorDisplay += '<br><span style="font-size:11px;color:var(--neon-green)">Pago: R$ ' + formatMoney(r.valor_pago) + '</span>';
        }

        let tipoLabel = '';
        if (r.tipo_pagamento === 'entrada') {
          tipoLabel = '<span class="badge sm cyan">Entrada</span>';
        } else if (r.tipo_pagamento === 'parcela') {
          tipoLabel = '<span class="badge sm blue">' + escapeHtml(r.descricao.match(/Parcela \d+\/\d+/) ? r.descricao.match(/Parcela \d+\/\d+/)[0] : 'Parcela') + '</span>';
        }

        const acoesBtn = isPago
          ? '<span class="badge sm green">Pago</span>'
          : '<button type="button" class="btn btn-sm btn-cyan" data-action="editar-parcela" data-id-conta="' + r.id_conta + '" data-valor="' + r.valor + '" data-vencimento="' + (r.data_vencimento || '') + '" title="Editar valor e data">Editar</button>';

        html += '<tr' + (isVencido ? ' style="background:rgba(214,43,43,0.04)"' : '') + '>' +
          '<td><strong>' + escapeHtml(r.nome_fantasia) + '</strong></td>' +
          '<td>' + escapeHtml(r.servico) + (r.sala ? '<br><span style="font-size:11px;color:var(--text-3)">' + escapeHtml(r.sala) + '</span>' : '') + '</td>' +
          '<td>' + (tipoLabel ? tipoLabel + '<br>' : '') + '<span style="font-size:12px;color:var(--text-2)">' + escapeHtml(r.descricao) + '</span></td>' +
          '<td>' + valorDisplay + '</td>' +
          '<td>' + vencDisplay + '</td>' +
          '<td>' + statusBadge + '</td>' +
          '<td>' + acoesBtn + '</td>' +
        '</tr>';
      }
    });

    html += '</tbody></table>';
    window.renderHTML(container, '<div class="table-wrap">' + html + '</div>');
  }

  function pagarParcela(idConta, valor) {
    openPagarModalFechamento(idConta, valor);
  }

  function openPagarModalFechamento(idConta, valor) {
    let modal = document.getElementById('modal-pagar-fechamento');
    if (!modal) {
      const modalHtml = '<div class="modal-overlay" id="modal-pagar-fechamento" data-action="close-modal-overlay" data-modal-id="modal-pagar-fechamento">' +
        '<div class="modal modal-md">' +
          '<div class="modal-header">' +
            '<div class="modal-title">Registrar Pagamento</div>' +
            '<button class="modal-close" data-action="close-modal" data-modal-id="modal-pagar-fechamento">' +
              '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
          '</div>' +
          '<div class="modal-body">' +
            '<div class="fg"><div class="fl">Data de Pagamento</div>' +
            '<input type="date" id="fpagar-data" class="fi" value="' + new Date().toISOString().split('T')[0] + '"/></div>' +
            '<div class="fg" style="margin-top:12px"><div class="fl">Valor Pago</div>' +
            '<input type="text" id="fpagar-valor" class="fi" placeholder="0,00" oninput="mascaraMoeda(this)"/></div>' +
            '<div class="fg" style="margin-top:12px"><div class="fl">Tipo de Pagamento</div>' +
            '<select id="fpagar-tipo" class="fi">' +
              '<option value="">Selecione</option>' +
              '<option value="PIX">PIX</option>' +
              '<option value="Transferencia">Transferencia</option>' +
              '<option value="Dinheiro">Dinheiro</option>' +
              '<option value="Boleto">Boleto</option>' +
              '<option value="Cartao">Cartao</option>' +
            '</select></div>' +
            '<input type="hidden" id="fpagar-id" value=""/>' +
          '</div>' +
          '<div class="modal-footer">' +
            '<button type="button" class="btn btn-gray" data-action="close-modal" data-modal-id="modal-pagar-fechamento">Cancelar</button>' +
            '<button type="button" class="btn btn-green" data-action="confirmar-pagamento-parcela">Confirmar Pagamento</button>' +
          '</div>' +
        '</div>' +
      '</div>';
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      modal = document.getElementById('modal-pagar-fechamento');
    }

    document.getElementById('fpagar-id').value = idConta;
    document.getElementById('fpagar-valor').value = formatMoney(valor);
    document.getElementById('fpagar-data').value = new Date().toISOString().split('T')[0];
    document.getElementById('fpagar-tipo').value = '';
    openModal('modal-pagar-fechamento');
  }

  function confirmarPagamentoParcela() {
    const idConta = document.getElementById('fpagar-id').value;
    const dataPagamento = document.getElementById('fpagar-data').value;
    const valorRaw = document.getElementById('fpagar-valor').value || '0';
    const valorPago = valorRaw.replace(/\./g, '').replace(',', '.');
    const tipoPagamento = document.getElementById('fpagar-tipo').value;

    if (!dataPagamento) { showToast('yellow', 'Atencao', 'Informe a data de pagamento'); return; }
    if (!valorPago || parseFloat(valorPago) <= 0) { showToast('yellow', 'Atencao', 'Informe um valor valido'); return; }
    if (!tipoPagamento) { showToast('yellow', 'Atencao', 'Selecione o tipo de pagamento'); return; }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('data_pagamento', dataPagamento);
    formData.append('valor_pago', valorPago);
    formData.append('tipo_pagamento', tipoPagamento);

    fetch(BASE_URL + '/contas-pagar/pagar/' + idConta, {
      method: 'POST',
      body: formData
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Pagamento registrado com sucesso');
        closeModal('modal-pagar-fechamento');
        loadFornecedores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao registrar pagamento');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  window.toggleFormaPagamento = function() {
    const tipo = document.getElementById('modal-forn-tipo-pagamento').value;
    document.getElementById('forn-avista-fields').style.display = tipo === 'avista' ? 'block' : 'none';
    document.getElementById('forn-parcelado-fields').style.display = tipo === 'parcelado' ? 'block' : 'none';
    if (tipo !== 'parcelado') {
      document.getElementById('forn-parcelas-preview').style.display = 'none';
    } else {
      atualizarPreviewParcelas();
    }
  };

  window.calcularParcelas = function() {
    const valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    const entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    const entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    const numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 1);

    if (entrada > valorTotal) {
      document.getElementById('modal-forn-entrada').style.borderColor = 'var(--neon-red)';
      document.getElementById('modal-forn-entrada').style.boxShadow = '0 0 0 3px var(--neon-red-glow)';
    } else {
      document.getElementById('modal-forn-entrada').style.borderColor = '';
      document.getElementById('modal-forn-entrada').style.boxShadow = '';
    }

    atualizarPreviewParcelas();
  };

  function atualizarPreviewParcelas() {
    const previewDiv = document.getElementById('forn-parcelas-preview');
    const tbody = document.getElementById('forn-parcelas-preview-body');
    if (!previewDiv || !tbody) return;

    const tipoPagamento = document.getElementById('modal-forn-tipo-pagamento').value;
    if (tipoPagamento !== 'parcelado') {
      previewDiv.style.display = 'none';
      return;
    }

    const valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    const entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    const entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    const numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 0);
    const primeiraParcelaVenc = document.getElementById('modal-forn-primeira-parcela').value;
    const intervalo = parseInt(document.getElementById('modal-forn-intervalo').value || 30);

    if (numParcelas < 1 || !primeiraParcelaVenc) {
      previewDiv.style.display = 'none';
      return;
    }

    const valoresEditados = {};
    const datasEditadas = {};

    const entradaChanged = (lastEntrada !== null && lastEntrada !== entrada);
    const numParcelasChanged = (lastNumParcelas !== null && lastNumParcelas !== numParcelas);
    lastEntrada = entrada;
    lastNumParcelas = numParcelas;

    if (!entradaChanged && !numParcelasChanged) {
      tbody.querySelectorAll('.forn-prev-valor').forEach((el) => {
        valoresEditados[el.dataset.type + '_' + (el.dataset.index || '0')] = el.value;
      });
      tbody.querySelectorAll('.forn-prev-data').forEach((el) => {
        datasEditadas[el.dataset.type + '_' + (el.dataset.index || '0')] = el.value;
      });
    }

    previewDiv.style.display = 'block';
    let html = '';
    let rowNum = 1;
    const restante = Math.max(0, valorTotal - entrada);
    const valorParcela = numParcelas > 0 ? restante / numParcelas : 0;
    const primeiraDate = new Date(primeiraParcelaVenc + 'T00:00:00');

    if (entrada > 0) {
      const today = new Date().toISOString().split('T')[0];
      const vEntrada = formatMoney(entrada);
      const dEntrada = datasEditadas['entrada_0'] || today;
      html += '<tr>'
        + '<td style="font-weight:600;text-align:center;width:32px">' + rowNum + '</td>'
        + '<td><span class="badge sm cyan">Entrada</span></td>'
        + '<td><input type="text" class="forn-prev-valor entrada" data-row="' + rowNum + '" data-type="entrada" data-index="0" value="' + vEntrada + '" oninput="mascaraMoeda(this)" placeholder="0,00"></td>'
        + '<td><input type="date" class="forn-prev-data" data-row="' + rowNum + '" data-type="entrada" data-index="0" value="' + dEntrada + '"></td>'
        + '</tr>';
      rowNum++;
    }

    for (let i = 1; i <= numParcelas; i++) {
      const parcelDate = new Date(primeiraDate.getTime() + (i - 1) * intervalo * 24 * 60 * 60 * 1000);
      let dateStr = '';
      try {
        if (!isNaN(parcelDate.getTime())) {
          dateStr = parcelDate.toISOString().split('T')[0];
        }
      } catch (e) {
      }
      const vParcela = valoresEditados['parcela_' + i] || formatMoney(valorParcela);
      const dParcela = datasEditadas['parcela_' + i] || dateStr;

      html += '<tr>'
        + '<td style="font-weight:600;text-align:center;width:32px">' + rowNum + '</td>'
        + '<td><span class="badge sm blue">Parcela ' + i + '/' + numParcelas + '</span></td>'
        + '<td><input type="text" class="forn-prev-valor" data-row="' + rowNum + '" data-type="parcela" data-index="' + i + '" value="' + vParcela + '" oninput="mascaraMoeda(this)" placeholder="0,00"></td>'
        + '<td><input type="date" class="forn-prev-data" data-row="' + rowNum + '" data-type="parcela" data-index="' + i + '" value="' + dParcela + '"></td>'
        + '</tr>';
      rowNum++;
    }

    window.renderHTML(tbody, html);

    tbody.querySelectorAll('.forn-prev-valor, .forn-prev-data').forEach((input) => {
      input.addEventListener('change', function() {
        this.style.borderColor = 'var(--neon-cyan)';
        if (this.classList.contains('forn-prev-valor')) {
          ajustarParcelasSubsequentes(this.dataset.type, this.dataset.index);
        }
        verificarSomaParcelas();
      });
      input.addEventListener('input', function() {
        if (this.classList.contains('forn-prev-valor')) {
          if (this.dataset.type === 'entrada') {
            const mainEntrada = document.getElementById('modal-forn-entrada');
            if (mainEntrada) {
              mainEntrada.value = this.value;
              const parsedEnt = parseFloat(this.value.replace(/\./g, '').replace(',', '.') || 0);
              lastEntrada = parsedEnt;
            }
          }
          ajustarParcelasSubsequentes(this.dataset.type, this.dataset.index);
        }
        verificarSomaParcelas();
      });
    });

    verificarSomaParcelas();
  }

  function ajustarParcelasSubsequentes(editType, editIndex) {
    const valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    const entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    const entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    const restante = Math.max(0, valorTotal - entrada);

    const tbody = document.getElementById('forn-parcelas-preview-body');
    if (!tbody) return;

    const numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 0);

    if (editType === 'entrada') {
      const valorParcela = numParcelas > 0 ? restante / numParcelas : 0;
      for (let k = 1; k <= numParcelas; k++) {
        const el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
        if (el) el.value = formatMoney(valorParcela);
      }
      return;
    }

    const idx = parseInt(editIndex);
    if (idx >= numParcelas) return;

    let somaAteIdx = 0;
    for (let k = 1; k <= idx; k++) {
      const el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
      if (el) somaAteIdx += parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
    }

    const saldoDisponivel = Math.max(0, restante - somaAteIdx);

    let somaPosteriores = 0;
    for (let k = idx + 2; k <= numParcelas; k++) {
      const el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
      if (el) somaPosteriores += parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
    }

    const novoValNext = saldoDisponivel - somaPosteriores;
    const nextEl = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + (idx + 1) + '"]');
    if (novoValNext < 0) {
      if (nextEl) nextEl.value = formatMoney(0);
      ajustarParcelasSubsequentes('parcela', idx + 1);
    } else {
      if (nextEl) nextEl.value = formatMoney(novoValNext);
    }
  }

  function verificarSomaParcelas() {
    const valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    const entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    const entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    const restante = Math.max(0, valorTotal - entrada);

    let somaParcelas = 0;
    const tbody = document.getElementById('forn-parcelas-preview-body');
    if (!tbody) return;

    tbody.querySelectorAll('.forn-prev-valor[data-type="parcela"]').forEach((el) => {
      const val = parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
      somaParcelas += val;
    });

    const aviso = document.getElementById('forn-preview-aviso');
    if (aviso) {
      aviso.style.display = (Math.abs(somaParcelas - restante) > 0.02) ? 'inline-block' : 'none';
    }
  }

  function abrirModalFornecedor(idCotacao, nome, valor) {
    document.getElementById('modal-forn-id-cotacao').value = idCotacao;
    document.getElementById('modal-forn-nome').textContent = nome;
    document.getElementById('modal-forn-valor').textContent = 'R$ ' + formatMoney(valor);
    document.getElementById('modal-forn-valor-total-hidden').value = valor;

    const evNome = window.EVENTO_NOME || 'Evento';
    const evLocal = window.EVENTO_LOCAL || 'Nao informado';
    const evInicio = window.RH_EVENTO_DATA ? formatDateBR(window.RH_EVENTO_DATA.data_inicio) : '';
    const evFim = window.RH_EVENTO_DATA ? formatDateBR(window.RH_EVENTO_DATA.data_fim) : '';
    const evDias = window.EVENTO_DIAS || 1;
    const evOs = window.EVENTO_OS_CLIENTE || 'Nao informada';

    window.renderHTML(document.getElementById('modal-forn-evento-cabecalho'),
      '<strong>' + escapeHtml(evNome) + '</strong><br>' +
      '<strong>OS Cliente:</strong> ' + escapeHtml(evOs) + '<br>' +
      '<strong>Periodo:</strong> ' + evInicio + ' ate ' + evFim + ' &middot; <strong>' + evDias + ' dias</strong><br>' +
      '<strong>Local:</strong> ' + escapeHtml(evLocal));

    const item = FORNECEODRES_LIST_RAW.find((f) => { return f.id_cotacao == idCotacao; }) || {};
    document.getElementById('modal-forn-item-nome').textContent = item.servico || 'Nao informado';
    document.getElementById('modal-forn-item-dias').textContent = item.dias || evDias;
    document.getElementById('modal-forn-item-obs').textContent = item.observacao_montagem || 'Nenhuma';

    const dadosPagamento = item.dados_pagamento || 'Nenhum dado cadastrado para este fornecedor';
    document.getElementById('modal-forn-pagamento-dados').textContent = dadosPagamento;

    document.getElementById('modal-forn-tipo-pagamento').value = 'parcelado';
    document.getElementById('modal-forn-nf').value = '';
    document.getElementById('modal-forn-entrada').value = '';
    document.getElementById('modal-forn-observacao-financeiro').value = '';
    const fileInput = document.getElementById('modal-forn-documento');
    if (fileInput) fileInput.value = '';

    const defaultVenc = new Date();
    defaultVenc.setDate(defaultVenc.getDate() + 30);
    document.getElementById('modal-forn-vencimento').value = defaultVenc.toISOString().split('T')[0];
    document.getElementById('modal-forn-primeira-parcela').value = defaultVenc.toISOString().split('T')[0];
    document.getElementById('modal-forn-numero-parcelas').value = 1;
    document.getElementById('modal-forn-intervalo').value = 30;

    toggleFormaPagamento();
    document.getElementById('forn-parcelas-preview').style.display = 'block';
    calcularParcelas();

    openModal('modal-fornecedor-pagamento');
  }

  function enviarFornecedorPagamento() {
    const idCotacao = document.getElementById('modal-forn-id-cotacao').value;
    const nome = document.getElementById('modal-forn-nome').textContent;
    const tipoPagamento = document.getElementById('modal-forn-tipo-pagamento').value;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('id_cotacao', idCotacao);
    formData.append('tipo_pagamento', tipoPagamento);
    formData.append('observacao', document.getElementById('modal-forn-observacao-financeiro').value);

    const fileInput2 = document.getElementById('modal-forn-documento');
    if (fileInput2 && fileInput2.files && fileInput2.files[0]) {
      formData.append('documento_anexo', fileInput2.files[0]);
    }

    if (tipoPagamento === 'avista') {
      formData.append('numero_nf', document.getElementById('modal-forn-nf').value);
      formData.append('data_vencimento', document.getElementById('modal-forn-vencimento').value);
    } else {
      const previewValorInputs = document.querySelectorAll('#forn-parcelas-preview-body .forn-prev-valor');
      const previewDataInputs  = document.querySelectorAll('#forn-parcelas-preview-body .forn-prev-data');
      const previewRows = {};

      previewValorInputs.forEach((input) => {
        const key = input.dataset.type + '_' + (input.dataset.index || '0');
        if (!previewRows[key]) previewRows[key] = { type: input.dataset.type, index: parseInt(input.dataset.index || 0) };
        previewRows[key].valor = input.value.replace(/\./g, '').replace(',', '.');
      });
      previewDataInputs.forEach((input) => {
        const key = input.dataset.type + '_' + (input.dataset.index || '0');
        if (!previewRows[key]) previewRows[key] = { type: input.dataset.type, index: parseInt(input.dataset.index || 0) };
        previewRows[key].vencimento = input.value;
      });

      let entradaData = null;
      const parcelasDatas = [];
      const parcelasValores = [];
      const valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
      let somaValoresPreview = 0;

      for (let k in previewRows) {
        const row = previewRows[k];
        if (row.type === 'entrada') {
          entradaData = row.vencimento;
        } else if (row.type === 'parcela') {
          parcelasDatas.push({ index: row.index, vencimento: row.vencimento });
          parcelasValores.push({ index: row.index, valor: row.valor });
          somaValoresPreview += parseFloat(row.valor || 0);
        }
      }

      const entradaRaw = document.getElementById('modal-forn-entrada').value || '0';
      const entrada    = entradaRaw.replace(/\./g, '').replace(',', '.');
      const entradaNum = parseFloat(entrada) || 0;

      if (entradaNum > valorTotal) {
        showToast('red', 'Erro', 'O valor da entrada não pode ser maior que o total (R$ ' + formatMoney(valorTotal) + ')');
        return;
      }

      const somaEsperada = valorTotal - entradaNum;
      if (Math.abs(somaValoresPreview - somaEsperada) > 0.05) {
        if (!confirm('A soma das parcelas (R$ ' + formatMoney(somaValoresPreview) + ') difere do valor restante (R$ ' + formatMoney(somaEsperada) + ').\nDeseja continuar mesmo assim?')) {
          return;
        }
      }

      if (entradaNum > 0 && !entradaData) {
        showToast('yellow', 'Atencao', 'Informe a data da entrada na tabela de preview');
        return;
      }
      for (let i = 0; i < parcelasDatas.length; i++) {
        if (!parcelasDatas[i].vencimento) {
          showToast('yellow', 'Atencao', 'Informe a data da parcela ' + parcelasDatas[i].index);
          return;
        }
      }

      formData.append('entrada_valor', entrada);
      formData.append('entrada_vencimento', entradaData || '');
      formData.append('parcelas_qtd', document.getElementById('modal-forn-numero-parcelas').value);
      formData.append('parcelas_datas', JSON.stringify(parcelasDatas));
      formData.append('parcelas_valores', JSON.stringify(parcelasValores));
      formData.append('intervalo_parcelas', document.getElementById('modal-forn-intervalo').value);
    }


    fetch(BASE_URL + '/fechamento/fornecedor/pagamento', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then((r) => {
      return r.json();
    })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', nome + ' enviado para pagamento');
        closeModal('modal-fornecedor-pagamento');
        loadFornecedores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao enviar para pagamento');
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro de conexao');
    });
  }

  // ========================================
  // Ver Contas a Pagar do Fornecedor
  // ========================================
  function verContasPagar(idCotacao, nome) {
    const container = document.getElementById('fechamento-fornecedores-content');
    if (!container) {
      showToast('red', 'Erro', 'Dados nao encontrados');
      return;
    }

    fetch(BASE_URL + '/fechamento/fornecedor/contas/' + idCotacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (!data.success || !data.data || data.data.length === 0) {
        showToast('yellow', 'Atencao', 'Nenhuma conta a pagar encontrada para este fornecedor');
        return;
      }

      const contas = data.data;
      let modalBody = document.getElementById('modal-contas-pagar-body');
      if (!modalBody) {
        criarModalContasPagar(contas, nome);
        modalBody = document.getElementById('modal-contas-pagar-body');
      }
      renderContasPagarModal(modalBody, contas, nome);
      openModal('modal-contas-pagar');
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro ao carregar contas a pagar');
    });
  }

  function renderContasPagarModal(container, contas, nome) {
    let html = '<div style="margin-bottom:16px">' +
      '<div style="font-weight:700;color:var(--text-1);font-size:15px">' + escapeHtml(nome) + '</div>' +
      '<div style="font-size:12px;color:var(--text-3)">Contas a Pagar</div>' +
      '</div>';

    html += '<table class="data-table striped" style="width:100%">';
    html += '<thead><tr>' +
      '<th>Descricao</th>' +
      '<th>Valor</th>' +
      '<th>Vencimento</th>' +
      '<th>Status</th>' +
      '</tr></thead><tbody>';

    contas.forEach((conta) => {
      const statusBadge = '';
      if (conta.status === 'PAGO') {
        statusBadge = '<span class="badge sm green">Pago</span>';
      } else if (conta.status === 'VENCIDO') {
        statusBadge = '<span class="badge sm red">Vencido</span>';
      } else {
        statusBadge = '<span class="badge sm yellow">Pendente</span>';
      }

      html += '<tr>' +
        '<td>' + escapeHtml(conta.descricao) + '</td>' +
        '<td><strong>R$ ' + formatMoney(conta.valor) + '</strong></td>' +
        '<td>' + formatDateBR(conta.data_vencimento) + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '</tr>';
    });

    html += '</tbody></table>';
    window.renderHTML(container, html);
  }

  function criarModalContasPagar(contas, nome) {
    const modalHtml = '<div class="modal-overlay" id="modal-contas-pagar" data-action="close-modal-overlay" data-modal-id="modal-contas-pagar">' +
      '<div class="modal modal-lg">' +
        '<div class="modal-header">' +
          '<div class="modal-title">Contas a Pagar</div>' +
          '<button class="modal-close" data-action="close-modal" data-modal-id="modal-contas-pagar">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="modal-body" id="modal-contas-pagar-body"></div>' +
        '<div class="modal-footer">' +
          '<button type="button" class="btn btn-gray" data-action="close-modal" data-modal-id="modal-contas-pagar">Fechar</button>' +
        '</div>' +
      '</div>' +
    '</div>';

    document.body.insertAdjacentHTML('beforeend', modalHtml);
  }

  // ========================================
  // Editar Parcela (fornecedor ja enviado)
  // ========================================
  function editarParcela(idConta, valor, vencimento) {
    let modal = document.getElementById('modal-editar-parcela');
    if (!modal) {
      const html = '<div class="modal-overlay" id="modal-editar-parcela" data-action="close-modal-overlay" data-modal-id="modal-editar-parcela">' +
        '<div class="modal modal-md">' +
          '<div class="modal-header">' +
            '<div class="modal-title">Editar Parcela</div>' +
            '<button class="modal-close" data-action="close-modal" data-modal-id="modal-editar-parcela">' +
              '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
          '</div>' +
          '<div class="modal-body">' +
            '<div class="fg"><div class="fl">Valor (R$)</div>' +
            '<input type="text" id="ep-valor" class="fi" oninput="mascaraMoeda(this)" style="font-weight:700;font-size:16px"></div>' +
            '<div class="fg" style="margin-top:12px"><div class="fl">Data Vencimento</div>' +
            '<input type="date" id="ep-vencimento" class="fi"></div>' +
            '<input type="hidden" id="ep-id-conta" value="">' +
          '</div>' +
          '<div class="modal-footer">' +
            '<button type="button" class="btn btn-gray" data-action="close-modal" data-modal-id="modal-editar-parcela">Cancelar</button>' +
            '<button type="button" class="btn btn-purple" data-action="salvar-edicao-parcela">Salvar</button>' +
          '</div>' +
        '</div>' +
      '</div>';
      document.body.insertAdjacentHTML('beforeend', html);
      modal = document.getElementById('modal-editar-parcela');
    }

    document.getElementById('ep-id-conta').value = idConta;
    document.getElementById('ep-valor').value = formatMoney(valor);
    document.getElementById('ep-vencimento').value = vencimento || '';
    openModal('modal-editar-parcela');
  }

  function salvarEdicaoParcela() {
    const idConta = document.getElementById('ep-id-conta').value;
    const valorRaw = document.getElementById('ep-valor').value || '0';
    const vencimento = document.getElementById('ep-vencimento').value;
    const valor = valorRaw.replace(/\./g, '').replace(',', '.');

    if (!valor || parseFloat(valor) <= 0) {
      showToast('yellow', 'Atencao', 'Informe um valor valido');
      return;
    }
    if (!vencimento) {
      showToast('yellow', 'Atencao', 'Informe a data de vencimento');
      return;
    }

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);
    fd.append('valor', valor);
    fd.append('data_vencimento', vencimento);

    fetch(BASE_URL + '/fechamento/fornecedor/parcela/update/' + idConta, {
      method: 'POST',
      body: fd
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Parcela atualizada');
        closeModal('modal-editar-parcela');
        loadFornecedores();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao atualizar parcela');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  // ========================================
  // Estender Public API
  // ========================================
  Object.assign(window.Fechamento, {
    abrirModalFornecedor:      abrirModalFornecedor,
    enviarFornecedor:          enviarFornecedorPagamento,
    pagarParcela:              pagarParcela,
    confirmarPagamentoParcela: confirmarPagamentoParcela,
    verContasPagar:            verContasPagar,
    loadFornecedores:          loadFornecedores,
    editarParcela:             editarParcela,
    salvarEdicaoParcela:       salvarEdicaoParcela
  });

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel. O
    // dispatcher generico so busca window[fnName] plano, nao window.Fechamento[fnName].
    if (window.registerAction) {
      window.registerAction('abrir-modal-fornecedor', function(el) {
        abrirModalFornecedor(el.dataset.idCotacao, el.dataset.nome, el.dataset.valor);
      });
      window.registerAction('confirmar-pagamento-parcela', function() {
        confirmarPagamentoParcela();
      });
      window.registerAction('editar-parcela', function(el) {
        editarParcela(el.dataset.idConta, el.dataset.valor, el.dataset.vencimento);
      });
      window.registerAction('salvar-edicao-parcela', function() {
        salvarEdicaoParcela();
      });
      window.registerAction('enviar-fornecedor', function() { enviarFornecedorPagamento(); });
    }
  });
})();

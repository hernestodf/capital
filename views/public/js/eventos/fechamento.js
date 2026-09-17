/**
 * JavaScript - Tab: Fechamento
 * Modulo: Eventos > Editar
 *
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST
 * - window.SALAS_MAP: Mapa de salas do evento
 * - window.BASE_URL: URL base do sistema
 */
(function() {
  'use strict';

  var EVENTO_ID = window.EVENTO_ID;
  var CSRF_TOKEN = window.CSRF_TOKEN;
  var BASE_URL = window.BASE_URL || '';
  var SALAS_MAP = window.SALAS_MAP || {};
  var FORNECEODRES_LIST_RAW = [];
  var lastEntrada = null;
  var lastNumParcelas = null;

  // ========================================
  // Helper: fetch com tratamento de sessão expirada
  // ========================================
  function fetchJson(url, options) {
    options = options || {};
    options.headers = options.headers || {};
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    return fetch(url, options).then(function(r) {
      if (r.status === 401) {
        if (typeof showToast === 'function') {
          showToast('yellow', 'Sessão expirada', 'Redirecionando para o login...');
        }
        setTimeout(function() {
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
  function loadTotais() {
    if (!EVENTO_ID) return;
    fetchJson(BASE_URL + '/fechamento/totais/' + EVENTO_ID)
    .then(function(data) {
      if (!data.success) return;
      var d = data.data;
      var vals = document.querySelectorAll('#fechamento-totais .card-stat-val');
      if (vals.length >= 10) {
        vals[0].textContent = 'R$ ' + formatMoney(d.receita_total || d.receita);
        vals[1].textContent = 'R$ ' + formatMoney(d.venda_tabela2 || 0);
        vals[2].textContent = 'R$ ' + formatMoney(d.comissao_tabela2 || 0);
        vals[3].textContent = 'R$ ' + formatMoney(d.venda_tabela3 || 0);
        vals[4].textContent = 'R$ ' + formatMoney(d.comissao_tabela3 || 0);
        vals[5].textContent = 'R$ ' + formatMoney(d.total_colaboradores);
        vals[6].textContent = 'R$ ' + formatMoney(d.total_fornecedores);
        vals[7].textContent = 'R$ ' + formatMoney(d.total_outros);
        vals[8].textContent = 'R$ ' + formatMoney(d.custo_total);
        vals[9].textContent = 'R$ ' + formatMoney(d.lucro);
        var statEls = document.querySelectorAll('#fechamento-totais .card-stat');
        if (statEls.length >= 10) {
          statEls[9].style.setProperty('--stat-color', d.lucro >= 0 ? 'var(--neon-green)' : 'var(--neon-red)');
          vals[9].style.color = d.lucro >= 0 ? 'var(--neon-green)' : 'var(--neon-red)';
        }
      }
    })
    .catch(function(err) { console.warn('[Fechamento] totais:', err.message); });
  }

  // ========================================
  // Colaboradores
  // ========================================
  function loadColaboradores() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-colaboradores');
    fetchJson(BASE_URL + '/fechamento/colaboradores/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(data) {
      hideLoading('fechamento-colaboradores');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-colaboradores');
        return;
      }
      renderColaboradores(data.data);
      showContent('fechamento-colaboradores');
    })
    .catch(function(err) {
      hideLoading('fechamento-colaboradores');
      showToast('red', 'Erro', 'Erro ao carregar colaboradores');
      console.error(err);
    });
  }

  function renderColaboradores(lista) {
    var container = document.getElementById('fechamento-colaboradores-content');
    if (!container) return;

    var defaultVenc = new Date();
    defaultVenc.setDate(defaultVenc.getDate() + 30);
    var vencDefault = defaultVenc.toISOString().split('T')[0];

    var html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Colaborador</th>' +
      '<th>Presenca</th>' +
      '<th>Horas Extras</th>' +
      '<th>Valores</th>' +
      '<th>Vencimento</th>' +
      '<th>Pagamento</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    lista.forEach(function(c) {
      var periodo = formatDateBR(c.data_inicio) + ' a ' + formatDateBR(c.data_fim);
      var presencaBtn = '';
      if (c.dias_presentes >= c.dias_total && c.dias_total > 0) {
        presencaBtn = '<button type="button" class="btn btn-sm btn-green" onclick="window.Fechamento.verPresencas(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')" title="Ver presencas">Completo</button>';
      } else if (c.dias_presentes > 0) {
        presencaBtn = '<button type="button" class="btn btn-sm btn-yellow" onclick="window.Fechamento.verPresencas(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')" title="Ver presencas">' + c.dias_presentes + '/' + c.dias_total + '</button>';
      } else {
        presencaBtn = '<button type="button" class="btn btn-sm btn-red" onclick="window.Fechamento.verPresencas(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')" title="Ver presencas">Ausente</button>';
      }

      var totalHE = parseFloat(c.total_horas_extras) || 0;
      var heBadge = '';
      if (totalHE > 0) {
        heBadge = '<button type="button" class="btn btn-sm btn-purple" onclick="window.Fechamento.abrirModalHorasExtras(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')" title="Gerenciar Horas Extras">' +
          'R$ ' + formatMoney(totalHE) +
          '</button>';
      } else {
        heBadge = '<button type="button" class="btn btn-sm btn-cyan" onclick="window.Fechamento.abrirModalHorasExtras(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')">Lançar</button>';
      }

      var pagoBadge = c.ja_enviado_pagamento == 1
        ? '<span class="badge sm green">Enviado</span>'
        : '<span class="badge sm yellow">Pendente</span>';

      var popId = 'pop-fech-' + c.id_alocacao;
      var acoesHtml =
        '<div class="td-actions"><div class="td-act-menu">' +
          '<button type="button" class="btn btn-sm td-act-toggle" onclick="togglePop(\'' + popId + '\')">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' +
            '</svg>' +
          '</button>' +
          '<div class="td-act-dropdown" id="' + popId + '">' +
            '<button type="button" class="td-act-item td-act-cyan" onclick="window.Fechamento.verPresencas(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')"><span>Presencas</span></button>' +
            '<button type="button" class="td-act-item td-act-purple" onclick="window.Fechamento.abrirModalHorasExtras(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\')"><span>Horas Extras</span></button>' +
            (c.ja_enviado_pagamento != 1
              ? '<button type="button" class="td-act-item td-act-green" onclick="window.Fechamento.abrirModalColaborador(' + c.id_alocacao + ',\'' + escapeJs(c.nome) + '\',' + c.valor_total + ')"><span>Enviar Pgto</span></button>'
              : '<button type="button" class="td-act-item td-act-gray" disabled><span>Ja Enviado</span></button>') +
          '</div>' +
        '</div></div>';

      var vencimentoInput = c.ja_enviado_pagamento != 1
        ? '<input type="date" class="fi" id="colab-venc-' + c.id_alocacao + '" value="' + (c.data_vencimento_pagamento || vencDefault) + '" style="width:140px">'
        : (c.data_vencimento_pagamento ? formatDateBR(c.data_vencimento_pagamento) : '-');

      // Coluna Colaborador: Nome + Funcao + Periodo + Dias
      var colaboradorHtml =
        '<div style="line-height:1.6">' +
          '<div style="font-weight:700;color:var(--text-1)">' + escapeHtml(c.nome) + '</div>' +
          '<div style="font-size:12px;color:var(--text-3)">' + escapeHtml(c.funcao) + '</div>' +
          '<div style="font-size:12px;color:var(--text-2);margin-top:4px">' + periodo + ' &middot; <strong>' + c.dias + ' dias</strong></div>' +
        '</div>';

      // Coluna Valores: Diária + Extras + Total
      var extrasHtml = totalHE > 0
        ? '<div style="font-size:12px;color:var(--neon-purple);font-weight:600">Extras: R$ ' + formatMoney(totalHE) + '</div>'
        : '';
      var valoresHtml =
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
    container.innerHTML = '<div class="table-wrap">' + html + '</div>';
  }

  // ========================================
  // Presencas - Ver
  // ========================================
  function verPresencas(idAlocacao, nome) {
    document.getElementById('modal-presenca-id-alocacao').value = idAlocacao;
    document.getElementById('modal-presenca-nome').textContent = nome;

    var listaEl = document.getElementById('modal-presenca-lista');
    listaEl.innerHTML = '<div style="text-align:center;padding:20px">' + renderSpinner({variant:'dots',size:'sm'}) + '</div>';

    openModal('modal-presencas');

    fetch(BASE_URL + '/fechamento/presencas/' + idAlocacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success || !data.data || data.data.length === 0) {
        listaEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-3)">Nenhuma presenca registrada</div>';
        return;
      }
      if (!window.ampliarFoto) {
        window.ampliarFoto = function(src) {
          var overlay = document.createElement('div');
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
          
          var img = document.createElement('img');
          img.src = src;
          img.style.maxWidth = '90vw';
          img.style.maxHeight = '90vh';
          img.style.objectFit = 'contain';
          img.style.borderRadius = '8px';
          img.style.border = '2px solid #333';
          
          overlay.appendChild(img);
          overlay.onclick = function() {
            document.body.removeChild(overlay);
          };
          document.body.appendChild(overlay);
        };
      }

      var html = '<table class="tbl" width="100%"><thead><tr><th>Data</th><th>Status</th><th>Entrada</th><th>Saida</th></tr></thead><tbody>';
      data.data.forEach(function(p) {
        var statusBadge = p.status === 'completo' ? '<span class="badge sm green">Completo</span>'
          : p.status === 'parcial' ? '<span class="badge sm yellow">Parcial</span>'
          : '<span class="badge sm gray">Aguardando</span>';
          
        var entradaHtml = '-';
        if (p.hora_entrada_real) {
          var timeStr = formatDateTimeBR(p.hora_entrada_real);
          var photoHtml = '';
          if (p.foto_entrada) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + BASE_URL + '/' + p.foto_entrada + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" onclick="window.ampliarFoto(\'' + BASE_URL + '/' + p.foto_entrada + '\')" /></div>';
          }
          var mapHtml = '';
          if (p.geo_entrada_lat && parseFloat(p.geo_entrada_lat) !== 0) {
            mapHtml = '<div style="margin-top:4px;"><a href="https://www.google.com/maps/search/?api=1&query=' + p.geo_entrada_lat + ',' + p.geo_entrada_lng + '" target="_blank" class="badge sm info" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-size:10px;padding:2px 6px;">🗺️ Mapa</a></div>';
          }
          entradaHtml = '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            '<span>' + timeStr + '</span>' +
            photoHtml +
            mapHtml +
            '</div>';
        }

        var saidaHtml = '-';
        if (p.hora_saida_real) {
          var timeStr = formatDateTimeBR(p.hora_saida_real);
          var photoHtml = '';
          if (p.foto_saida) {
            photoHtml = '<div style="margin-top:6px;"><img src="' + BASE_URL + '/' + p.foto_saida + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #444;cursor:pointer" onclick="window.ampliarFoto(\'' + BASE_URL + '/' + p.foto_saida + '\')" /></div>';
          }
          var mapHtml = '';
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
          '<td>' + statusBadge + '</td>' +
          '<td>' + entradaHtml + '</td>' +
          '<td>' + saidaHtml + '</td>' +
          '</tr>';
      });
      html += '</tbody></table>';
      listaEl.innerHTML = html;
    })
    .catch(function() { listaEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--neon-red)">Erro ao carregar presencas</div>'; });
  }

  function abrirModalColaborador(idAlocacao, nome, valor) {
    document.getElementById('modal-colab-id-alocacao').value = idAlocacao;
    document.getElementById('modal-colab-nome').textContent = nome;
    
    // Armazenar valor original
    document.getElementById('modal-colab-valor-original').value = valor;
    
    // Mostrar valor calculado (somente visualizacao)
    document.getElementById('modal-colab-valor-calculado').textContent = 'R$ ' + formatMoney(valor);
    
    // Preencher campo editavel com mascara de moeda
    var inputValor = document.getElementById('modal-colab-valor');
    inputValor.value = formatMoney(valor);

    var vencEl = document.getElementById('colab-venc-' + idAlocacao);
    var vencDefault = vencEl ? vencEl.value : '';

    var defaultVenc = new Date();
    defaultVenc.setDate(defaultVenc.getDate() + 30);
    document.getElementById('modal-colab-vencimento').value = vencDefault || defaultVenc.toISOString().split('T')[0];

    openModal('modal-colaborador-pagamento');
  }

  function enviarColaboradorPagamento() {
    var idAlocacao = document.getElementById('modal-colab-id-alocacao').value;
    var nome = document.getElementById('modal-colab-nome').textContent;
    var vencimento = document.getElementById('modal-colab-vencimento').value;
    var valorFormatado = document.getElementById('modal-colab-valor').value;
    
    // Converter valor formatado (1.500,00) para float (1500.00)
    var valorEditado = valorFormatado.replace(/\./g, '').replace(',', '.');

    if (!vencimento) {
      showToast('yellow', 'Atencao', 'Informe a data de vencimento');
      return;
    }

    if (!valorEditado || parseFloat(valorEditado) <= 0) {
      showToast('yellow', 'Atencao', 'Informe um valor valido para pagamento');
      return;
    }

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('id_alocacao', idAlocacao);
    formData.append('data_vencimento', vencimento);
    formData.append('valor_pagamento', valorEditado);

    fetch(BASE_URL + '/fechamento/colaborador/pagamento', {
      method: 'POST',
      body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', nome + ' enviado para pagamento');
        closeModal('modal-colaborador-pagamento');
        loadColaboradores();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao enviar');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  // ========================================
  // Fornecedores
  // ========================================
  function loadFornecedores() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-fornecedores');
    fetchJson(BASE_URL + '/fechamento/fornecedores/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(data) {
      hideLoading('fechamento-fornecedores');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-fornecedores');
        return;
      }
      renderFornecedores(data.data);
      showContent('fechamento-fornecedores');
    })
    .catch(function(err) {
      hideLoading('fechamento-fornecedores');
      showToast('red', 'Erro', 'Erro ao carregar fornecedores');
      console.error(err);
    });
  }

  function renderFornecedores(lista) {
    FORNECEODRES_LIST_RAW = lista;
    var container = document.getElementById('fechamento-fornecedores-content');
    if (!container) return;

    // Expandir fornecedores parcelados em linhas individuais
    var rows = [];
    lista.forEach(function(f) {
      if (f.ja_enviado_pagamento == 1 && f.contas_pagar_info && f.contas_pagar_info.length > 0) {
        // Fornecedor com parcelas — criar uma linha por conta
        f.contas_pagar_info.forEach(function(conta) {
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
        // Fornecedor pendente — linha unica
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

    // Ordenar: nao pagos primeiro por vencimento ASC, pagos no final
    rows.sort(function(a, b) {
      var statusOrder = {'VENCIDO': 1, 'PENDENTE': 2, 'PARCIAL': 3, 'PAGO': 4};
      var aOrder = a.status ? statusOrder[a.status] : 0; // pendentes = 0
      var bOrder = b.status ? statusOrder[b.status] : 0;
      if (aOrder !== bOrder) return aOrder - bOrder;
      if (a.data_vencimento && b.data_vencimento) return a.data_vencimento.localeCompare(b.data_vencimento);
      return 0;
    });

    var html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Fornecedor</th>' +
      '<th>Servico</th>' +
      '<th>Descricao</th>' +
      '<th>Valor</th>' +
      '<th>Vencimento</th>' +
      '<th>Status</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    rows.forEach(function(r) {
      if (r.tipo === 'pendente') {
        // Fornecedor ainda nao enviado
        html += '<tr>' +
          '<td><strong>' + escapeHtml(r.nome_fantasia) + '</strong></td>' +
          '<td>' + escapeHtml(r.servico) + (r.sala ? '<br><span style="font-size:11px;color:var(--text-3)">' + escapeHtml(r.sala) + '</span>' : '') + '</td>' +
          '<td style="color:var(--text-3)">-</td>' +
          '<td><strong>R$ ' + formatMoney(r.valor) + '</strong></td>' +
          '<td>-</td>' +
          '<td><span class="badge sm yellow">Pendente</span></td>' +
          '<td><button type="button" class="btn btn-sm btn-purple" onclick="window.Fechamento.abrirModalFornecedor(' + r.id_cotacao + ',\'' + escapeJs(r.nome_fantasia) + '\',' + r.valor + ')">Enviar</button></td>' +
        '</tr>';
      } else {
        // Parcela individual
        var isPago = r.status === 'PAGO';
        var isVencido = r.status === 'VENCIDO';
        var statusBadge = isPago ? '<span class="badge sm green">Pago</span>'
          : isVencido ? '<span class="badge sm red">Vencido</span>'
          : r.status === 'PARCIAL' ? '<span class="badge sm purple">Parcial</span>'
          : '<span class="badge sm yellow">Pendente</span>';

        var vencDisplay = r.data_vencimento ? formatDateBR(r.data_vencimento) : '-';
        if (isPago && r.data_pagamento) {
          vencDisplay += '<br><span style="font-size:10px;color:var(--text-3)">Pago: ' + formatDateBR(r.data_pagamento) + '</span>';
        }

        var valorDisplay = '<strong>R$ ' + formatMoney(r.valor) + '</strong>';
        if (r.valor_pago && isPago) {
          valorDisplay += '<br><span style="font-size:11px;color:var(--neon-green)">Pago: R$ ' + formatMoney(r.valor_pago) + '</span>';
        }

        // Tipo label
        var tipoLabel = '';
        if (r.tipo_pagamento === 'entrada') {
          tipoLabel = '<span class="badge sm cyan">Entrada</span>';
        } else if (r.tipo_pagamento === 'parcela') {
          tipoLabel = '<span class="badge sm blue">' + escapeHtml(r.descricao.match(/Parcela \d+\/\d+/) ? r.descricao.match(/Parcela \d+\/\d+/)[0] : 'Parcela') + '</span>';
        }

        var acoesBtn = isPago
          ? '<span class="badge sm green">Pago</span>'
          : '<button type="button" class="btn btn-sm btn-cyan" onclick="window.Fechamento.editarParcela(' + r.id_conta + ', \'' + r.valor + '\', \'' + (r.data_vencimento || '') + '\')" title="Editar valor e data">Editar</button>';

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
    container.innerHTML = '<div class="table-wrap">' + html + '</div>';
  }

  function pagarParcela(idConta, valor) {
    openPagarModalFechamento(idConta, valor);
  }

  function openPagarModalFechamento(idConta, valor) {
    var modal = document.getElementById('modal-pagar-fechamento');
    if (!modal) {
      var modalHtml = '<div class="modal-overlay" id="modal-pagar-fechamento" onclick="if(event.target===this)closeModal(\'modal-pagar-fechamento\')">' +
        '<div class="modal modal-md">' +
          '<div class="modal-header">' +
            '<div class="modal-title">Registrar Pagamento</div>' +
            '<button class="modal-close" onclick="closeModal(\'modal-pagar-fechamento\')">' +
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
            '<button type="button" class="btn btn-gray" onclick="closeModal(\'modal-pagar-fechamento\')">Cancelar</button>' +
            '<button type="button" class="btn btn-green" onclick="window.Fechamento.confirmarPagamentoParcela()">Confirmar Pagamento</button>' +
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
    var idConta = document.getElementById('fpagar-id').value;
    var dataPagamento = document.getElementById('fpagar-data').value;
    var valorRaw = document.getElementById('fpagar-valor').value || '0';
    var valorPago = valorRaw.replace(/\./g, '').replace(',', '.');
    var tipoPagamento = document.getElementById('fpagar-tipo').value;

    if (!dataPagamento) { showToast('yellow', 'Atencao', 'Informe a data de pagamento'); return; }
    if (!valorPago || parseFloat(valorPago) <= 0) { showToast('yellow', 'Atencao', 'Informe um valor valido'); return; }
    if (!tipoPagamento) { showToast('yellow', 'Atencao', 'Selecione o tipo de pagamento'); return; }

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('data_pagamento', dataPagamento);
    formData.append('valor_pago', valorPago);
    formData.append('tipo_pagamento', tipoPagamento);

    fetch(BASE_URL + '/contas-pagar/pagar/' + idConta, {
      method: 'POST',
      body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Pagamento registrado com sucesso');
        closeModal('modal-pagar-fechamento');
        loadFornecedores();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao registrar pagamento');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  window.toggleFormaPagamento = function() {
    var tipo = document.getElementById('modal-forn-tipo-pagamento').value;
    document.getElementById('forn-avista-fields').style.display = tipo === 'avista' ? 'block' : 'none';
    document.getElementById('forn-parcelado-fields').style.display = tipo === 'parcelado' ? 'block' : 'none';
    if (tipo !== 'parcelado') {
      document.getElementById('forn-parcelas-preview').style.display = 'none';
    } else {
      atualizarPreviewParcelas();
    }
  };

  window.calcularParcelas = function() {
    var valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    var entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    var entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    var numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 1);

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
    var previewDiv = document.getElementById('forn-parcelas-preview');
    var tbody = document.getElementById('forn-parcelas-preview-body');
    if (!previewDiv || !tbody) return;

    var tipoPagamento = document.getElementById('modal-forn-tipo-pagamento').value;
    if (tipoPagamento !== 'parcelado') {
      previewDiv.style.display = 'none';
      return;
    }

    var valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    var entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    var entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    var numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 0);
    var primeiraParcelaVenc = document.getElementById('modal-forn-primeira-parcela').value;
    var intervalo = parseInt(document.getElementById('modal-forn-intervalo').value || 30);

    if (numParcelas < 1 || !primeiraParcelaVenc) {
      previewDiv.style.display = 'none';
      return;
    }

    // Coletar valores ja editados pelo usuario para preservar
    var valoresEditados = {};
    var datasEditadas = {};

    var entradaChanged = (lastEntrada !== null && lastEntrada !== entrada);
    var numParcelasChanged = (lastNumParcelas !== null && lastNumParcelas !== numParcelas);
    lastEntrada = entrada;
    lastNumParcelas = numParcelas;

    // So preserva valores editados se entrada ou quantidade nao mudaram
    if (!entradaChanged && !numParcelasChanged) {
      tbody.querySelectorAll('.forn-prev-valor').forEach(function(el) {
        valoresEditados[el.dataset.type + '_' + (el.dataset.index || '0')] = el.value;
      });
      tbody.querySelectorAll('.forn-prev-data').forEach(function(el) {
        datasEditadas[el.dataset.type + '_' + (el.dataset.index || '0')] = el.value;
      });
    }

    previewDiv.style.display = 'block';
    var html = '';
    var rowNum = 1;
    var restante = Math.max(0, valorTotal - entrada);
    var valorParcela = numParcelas > 0 ? restante / numParcelas : 0;
    var primeiraDate = new Date(primeiraParcelaVenc + 'T00:00:00');

    // Linha da entrada
    if (entrada > 0) {
      var today = new Date().toISOString().split('T')[0];
      var vEntrada = formatMoney(entrada); // Sempre sincronizado com modal-forn-entrada
      var dEntrada = datasEditadas['entrada_0'] || today;
      html += '<tr>'
        + '<td style="font-weight:600;text-align:center;width:32px">' + rowNum + '</td>'
        + '<td><span class="badge sm cyan">Entrada</span></td>'
        + '<td><input type="text" class="forn-prev-valor entrada" data-row="' + rowNum + '" data-type="entrada" data-index="0" value="' + vEntrada + '" oninput="mascaraMoeda(this)" placeholder="0,00"></td>'
        + '<td><input type="date" class="forn-prev-data" data-row="' + rowNum + '" data-type="entrada" data-index="0" value="' + dEntrada + '"></td>'
        + '</tr>';
      rowNum++;
    }

    // Linhas das parcelas
    for (var i = 1; i <= numParcelas; i++) {
      var parcelDate = new Date(primeiraDate.getTime() + (i - 1) * intervalo * 24 * 60 * 60 * 1000);
      var dateStr = '';
      try {
        if (!isNaN(parcelDate.getTime())) {
          dateStr = parcelDate.toISOString().split('T')[0];
        }
      } catch (e) {
        console.warn('Erro ao formatar data da parcela:', e);
      }
      var vParcela = valoresEditados['parcela_' + i] || formatMoney(valorParcela);
      var dParcela = datasEditadas['parcela_' + i] || dateStr;

      html += '<tr>'
        + '<td style="font-weight:600;text-align:center;width:32px">' + rowNum + '</td>'
        + '<td><span class="badge sm blue">Parcela ' + i + '/' + numParcelas + '</span></td>'
        + '<td><input type="text" class="forn-prev-valor" data-row="' + rowNum + '" data-type="parcela" data-index="' + i + '" value="' + vParcela + '" oninput="mascaraMoeda(this)" placeholder="0,00"></td>'
        + '<td><input type="date" class="forn-prev-data" data-row="' + rowNum + '" data-type="parcela" data-index="' + i + '" value="' + dParcela + '"></td>'
        + '</tr>';
      rowNum++;
    }

    tbody.innerHTML = html;

    // Listeners: destacar campo editado + verificar soma + ajustar subsequentes
    tbody.querySelectorAll('.forn-prev-valor, .forn-prev-data').forEach(function(input) {
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
            var mainEntrada = document.getElementById('modal-forn-entrada');
            if (mainEntrada) {
              mainEntrada.value = this.value;
              var parsedEnt = parseFloat(this.value.replace(/\./g, '').replace(',', '.') || 0);
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
    var valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    var entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    var entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    var restante = Math.max(0, valorTotal - entrada);

    var tbody = document.getElementById('forn-parcelas-preview-body');
    if (!tbody) return;

    var numParcelas = parseInt(document.getElementById('modal-forn-numero-parcelas').value || 0);

    // Se o usuario editou a entrada, redistribuir o restante igualmente nas parcelas
    if (editType === 'entrada') {
      var valorParcela = numParcelas > 0 ? restante / numParcelas : 0;
      for (var k = 1; k <= numParcelas; k++) {
        var el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
        if (el) {
          el.value = formatMoney(valorParcela);
        }
      }
      return;
    }

    // Se editou uma parcela i, ajustar as subsequentes
    var idx = parseInt(editIndex);
    if (idx >= numParcelas) {
      // Se for a ultima parcela, nao ha subsequente para ajustar.
      return;
    }

    // Calcular a soma das parcelas de 1 ate idx
    var somaAteIdx = 0;
    for (var k = 1; k <= idx; k++) {
      var el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
      if (el) {
        somaAteIdx += parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
      }
    }

    // O valor que precisa ser distribuido nas parcelas de idx+1 ate numParcelas
    var saldoDisponivel = Math.max(0, restante - somaAteIdx);

    // Ajustar a proxima parcela (idx + 1) para absorver a diferenca
    var somaPosteriores = 0;
    for (var k = idx + 2; k <= numParcelas; k++) {
      var el = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + k + '"]');
      if (el) {
        somaPosteriores += parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
      }
    }

    var novoValNext = saldoDisponivel - somaPosteriores;
    if (novoValNext < 0) {
      var nextEl = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + (idx + 1) + '"]');
      if (nextEl) nextEl.value = formatMoney(0);
      ajustarParcelasSubsequentes('parcela', idx + 1);
    } else {
      var nextEl = tbody.querySelector('.forn-prev-valor[data-type="parcela"][data-index="' + (idx + 1) + '"]');
      if (nextEl) {
        nextEl.value = formatMoney(novoValNext);
      }
    }
  }

  function verificarSomaParcelas() {
    var valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
    var entradaRaw = document.getElementById('modal-forn-entrada').value || '';
    var entrada = parseFloat(entradaRaw.replace(/\./g, '').replace(',', '.') || 0);
    var restante = Math.max(0, valorTotal - entrada);

    var somaParcelas = 0;
    var tbody = document.getElementById('forn-parcelas-preview-body');
    if (!tbody) return;

    tbody.querySelectorAll('.forn-prev-valor[data-type="parcela"]').forEach(function(el) {
      var val = parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
      somaParcelas += val;
    });

    var aviso = document.getElementById('forn-preview-aviso');
    if (aviso) {
      if (Math.abs(somaParcelas - restante) > 0.02) {
        aviso.style.display = 'inline-block';
      } else {
        aviso.style.display = 'none';
      }
    }
  }

  function abrirModalFornecedor(idCotacao, nome, valor) {
    document.getElementById('modal-forn-id-cotacao').value = idCotacao;
    document.getElementById('modal-forn-nome').textContent = nome;
    document.getElementById('modal-forn-valor').textContent = 'R$ ' + formatMoney(valor);
    document.getElementById('modal-forn-valor-total-hidden').value = valor;

    // Cabecalho do Evento
    var evNome = window.EVENTO_NOME || 'Evento';
    var evLocal = window.EVENTO_LOCAL || 'Nao informado';
    var evInicio = window.RH_EVENTO_DATA ? formatDateBR(window.RH_EVENTO_DATA.data_inicio) : '';
    var evFim = window.RH_EVENTO_DATA ? formatDateBR(window.RH_EVENTO_DATA.data_fim) : '';
    var evDias = window.EVENTO_DIAS || 1;
    var evOs = window.EVENTO_OS_CLIENTE || 'Nao informada';

    document.getElementById('modal-forn-evento-cabecalho').innerHTML =
      '<strong>' + escapeHtml(evNome) + '</strong><br>' +
      '<strong>OS Cliente:</strong> ' + escapeHtml(evOs) + '<br>' +
      '<strong>Periodo:</strong> ' + evInicio + ' ate ' + evFim + ' &middot; <strong>' + evDias + ' dias</strong><br>' +
      '<strong>Local:</strong> ' + escapeHtml(evLocal);

    // Detalhes do Item
    var item = FORNECEODRES_LIST_RAW.find(function(f) { return f.id_cotacao == idCotacao; }) || {};
    document.getElementById('modal-forn-item-nome').textContent = item.servico || 'Nao informado';
    document.getElementById('modal-forn-item-dias').textContent = item.dias || evDias;
    document.getElementById('modal-forn-item-obs').textContent = item.observacao_montagem || 'Nenhuma';

    // Dados de Pagamento do Fornecedor
    var dadosPagamento = item.dados_pagamento || 'Nenhum dado cadastrado para este fornecedor';
    document.getElementById('modal-forn-pagamento-dados').textContent = dadosPagamento;

    // Reset fields
    document.getElementById('modal-forn-tipo-pagamento').value = 'parcelado';
    document.getElementById('modal-forn-nf').value = '';
    document.getElementById('modal-forn-entrada').value = '';
    document.getElementById('modal-forn-observacao-financeiro').value = '';
    var fileInput = document.getElementById('modal-forn-documento');
    if (fileInput) fileInput.value = '';

    var defaultVenc = new Date();
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
    var idCotacao = document.getElementById('modal-forn-id-cotacao').value;
    var nome = document.getElementById('modal-forn-nome').textContent;
    var tipoPagamento = document.getElementById('modal-forn-tipo-pagamento').value;

    console.log('[Fechamento] Iniciando envio fornecedor:', {
      idCotacao: idCotacao,
      nome: nome,
      tipoPagamento: tipoPagamento
    });

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('id_cotacao', idCotacao);
    formData.append('tipo_pagamento', tipoPagamento);
    formData.append('observacao', document.getElementById('modal-forn-observacao-financeiro').value);

    var fileInput = document.getElementById('modal-forn-documento');
    if (fileInput && fileInput.files && fileInput.files[0]) {
      formData.append('documento_anexo', fileInput.files[0]);
    }

    if (tipoPagamento === 'avista') {
      formData.append('numero_nf', document.getElementById('modal-forn-nf').value);
      formData.append('data_vencimento', document.getElementById('modal-forn-vencimento').value);
      console.log('[Fechamento] Modo avista - vencimento:', document.getElementById('modal-forn-vencimento').value);
    } else {
      // Coletar valores e datas da tabela de preview (novas classes)
      var previewValorInputs = document.querySelectorAll('#forn-parcelas-preview-body .forn-prev-valor');
      var previewDataInputs  = document.querySelectorAll('#forn-parcelas-preview-body .forn-prev-data');
      var previewRows = {};

      previewValorInputs.forEach(function(input) {
        var key = input.dataset.type + '_' + (input.dataset.index || '0');
        if (!previewRows[key]) previewRows[key] = { type: input.dataset.type, index: parseInt(input.dataset.index || 0) };
        previewRows[key].valor = input.value.replace(/\./g, '').replace(',', '.');
      });
      previewDataInputs.forEach(function(input) {
        var key = input.dataset.type + '_' + (input.dataset.index || '0');
        if (!previewRows[key]) previewRows[key] = { type: input.dataset.type, index: parseInt(input.dataset.index || 0) };
        previewRows[key].vencimento = input.value;
      });

      var entradaData = null;
      var parcelasDatas = [];
      var parcelasValores = [];
      var valorTotal = parseFloat(document.getElementById('modal-forn-valor-total-hidden').value || 0);
      var somaValoresPreview = 0;

      for (var k in previewRows) {
        var row = previewRows[k];
        if (row.type === 'entrada') {
          entradaData = row.vencimento;
        } else if (row.type === 'parcela') {
          parcelasDatas.push({ index: row.index, vencimento: row.vencimento });
          parcelasValores.push({ index: row.index, valor: row.valor });
          somaValoresPreview += parseFloat(row.valor || 0);
        }
      }

      var entradaRaw = document.getElementById('modal-forn-entrada').value || '0';
      var entrada    = entradaRaw.replace(/\./g, '').replace(',', '.');
      var entradaNum = parseFloat(entrada) || 0;

      if (entradaNum > valorTotal) {
        showToast('red', 'Erro', 'O valor da entrada não pode ser maior que o total (R$ ' + formatMoney(valorTotal) + ')');
        return;
      }

      // Se soma divergir mais que R$ 0,05 avisa mas nao bloqueia
      var somaEsperada = valorTotal - entradaNum;
      if (Math.abs(somaValoresPreview - somaEsperada) > 0.05) {
        if (!confirm('A soma das parcelas (R$ ' + formatMoney(somaValoresPreview) + ') difere do valor restante (R$ ' + formatMoney(somaEsperada) + ').\nDeseja continuar mesmo assim?')) {
          return;
        }
      }

      if (entradaNum > 0 && !entradaData) {
        showToast('yellow', 'Atencao', 'Informe a data da entrada na tabela de preview');
        return;
      }
      for (var i = 0; i < parcelasDatas.length; i++) {
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


    console.log('[Fechamento] Enviando requisição para:', BASE_URL + '/fechamento/fornecedor/pagamento');

    fetch(BASE_URL + '/fechamento/fornecedor/pagamento', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(function(r) {
      console.log('[Fechamento] Resposta recebida, status:', r.status);
      return r.json();
    })
    .then(function(data) {
      console.log('[Fechamento] Resposta envio fornecedor:', data);
      if (data.success) {
        showToast('green', 'Sucesso', nome + ' enviado para pagamento');
        closeModal('modal-fornecedor-pagamento');
        loadFornecedores();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao enviar para pagamento');
      }
    })
    .catch(function(err) {
      console.error('[Fechamento] Erro envio fornecedor:', err);
      showToast('red', 'Erro', 'Erro de conexao');
    });
  }

  // ========================================
  // Fotos
  // ========================================
  function loadFotos() {
    if (!EVENTO_ID) return;
    var loadingEl = document.getElementById('fechamento-fotos-salas-loading');
    if (loadingEl) loadingEl.style.display = 'block';

    fetchJson(BASE_URL + '/fechamento/fotos/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(data) {
      if (loadingEl) loadingEl.style.display = 'none';
      if (!data.success) return;
      renderFotos(data.data);
    })
    .catch(function(err) {
      if (loadingEl) loadingEl.style.display = 'none';
      console.error('Erro ao carregar fotos:', err);
    });
  }

  function renderFotos(data) {
    // Fotos por sala - mostra TODAS as salas (mesmo sem fotos)
    var salasContainer = document.getElementById('fechamento-fotos-salas-content');
    if (salasContainer) {
      if (!data.por_sala || data.por_sala.length === 0) {
        salasContainer.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-3)">' +
          '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;margin:0 auto 8px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>' +
          '<div style="font-weight:600;margin-bottom:4px">Nenhuma sala encontrada</div>' +
          '<div style="font-size:12px">Adicione produtos ao evento por sala primeiro</div></div>';
        salasContainer.style.display = 'block';
      } else {
        var html = '';
        data.por_sala.forEach(function(sala) {
          var hasFotos = sala.fotos && sala.fotos.length > 0;
          var badgeHtml = hasFotos ? '<span class="badge sm cyan">' + sala.total + ' foto' + (sala.total > 1 ? 's' : '') + '</span>' : '<span class="badge sm gray">Sem fotos</span>';
          var fotosHtml = '';

          if (hasFotos) {
            fotosHtml = '<div class="fechamento-foto-grid">';
            sala.fotos.forEach(function(foto) {
              var fotoPath = escapeHtml(foto.caminho_arquivo);
              if (!fotoPath.startsWith('http') && !fotoPath.startsWith('/')) {
                fotoPath = BASE_URL + '/' + fotoPath;
              }
              fotosHtml += '<div class="fechamento-foto-item">' +
                '<a href="' + fotoPath + '" target="_blank">' +
                  '<img src="' + fotoPath + '" alt="' + escapeHtml(foto.nome_arquivo) + '">' +
                '</a>' +
                '<button type="button" class="btn btn-sm btn-red" onclick="window.Fechamento.removerFoto(' + foto.id + ', this)" title="Remover">&times;</button>' +
              '</div>';
            });
            fotosHtml += '</div>';
          } else {
            fotosHtml = '<div style="text-align:center;padding:20px;color:var(--text-3);font-size:13px">Nenhuma foto nesta sala ainda. Faca upload abaixo.</div>';
          }

          html += '<div class="fechamento-sala-foto">' +
            '<div class="fechamento-sala-header" onclick="window.Fechamento.toggleSalaFotos(' + sala.sala_id + ')">' +
              '<svg class="fechamento-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>' +
              '<span style="font-weight:600">' + escapeHtml(sala.nome_sala) + '</span>' +
              badgeHtml +
              '<label class="btn btn-cyan btn-sm" style="cursor:pointer;margin-left:auto" onclick="event.stopPropagation()">' +
                '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0-3v12"/></svg>' +
                'Upload' +
                '<input type="file" accept="image/jpeg,image/png,image/webp" multiple ' +
                'onchange="window.Fechamento.uploadFoto(' + sala.sala_id + ', this)" style="display:none">' +
              '</label>' +
            '</div>' +
            '<div class="fechamento-sala-content" id="fech-sala-fotos-' + sala.sala_id + '" style="display:none">' +
              fotosHtml +
            '</div></div>';
        });
        salasContainer.innerHTML = html;
        salasContainer.style.display = 'block';
      }
    }

    // Fotos gerais
    var geraisContainer = document.getElementById('fechamento-fotos-gerais-content');
    if (geraisContainer) {
      if (!data.gerais || data.gerais.length === 0) {
        geraisContainer.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-3)">Nenhuma foto geral</div>';
      } else {
        var html = '<div class="fechamento-foto-grid">';
        data.gerais.forEach(function(foto) {
          var fotoPath = escapeHtml(foto.caminho_arquivo);
          if (!fotoPath.startsWith('http') && !fotoPath.startsWith('/')) {
            fotoPath = BASE_URL + '/' + fotoPath;
          }
          html += '<div class="fechamento-foto-item">' +
            '<a href="' + fotoPath + '" target="_blank">' +
              '<img src="' + fotoPath + '" alt="' + escapeHtml(foto.nome_arquivo) + '">' +
            '</a>' +
            '<button type="button" class="btn btn-sm btn-red" onclick="window.Fechamento.removerFoto(' + foto.id + ', this)" title="Remover">&times;</button>' +
          '</div>';
        });
        html += '</div>';
        geraisContainer.innerHTML = html;
      }
    }
  }

  function uploadFoto(salaId, input) {
    if (!input.files || input.files.length === 0) return;

    Array.from(input.files).forEach(function(file) {
      var formData = new FormData();
      formData.append('_csrf_token', CSRF_TOKEN);
      formData.append('evento_id', EVENTO_ID);
      if (salaId) formData.append('sala_id', salaId);
      formData.append('foto', file);

      fetch(BASE_URL + '/fechamento/foto/upload', {
        method: 'POST',
        body: formData
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          showToast('green', 'Sucesso', 'Foto enviada com sucesso');
          loadFotos();
        } else {
          showToast('red', 'Erro', data.error || 'Erro ao enviar foto');
        }
      })
      .catch(function() { showToast('red', 'Erro', 'Erro de conexao'); });
    });

    input.value = '';
  }

  function removerFoto(fotoId, btn) {
    if (!confirm('Remover esta foto?')) return;

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('foto_id', fotoId);

    fetch(BASE_URL + '/fechamento/foto/remover', {
      method: 'POST',
      body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Foto removida');
        loadFotos();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  function toggleSalaFotos(salaId) {
    var content = document.getElementById('fech-sala-fotos-' + salaId);
    if (!content) return;
    var isVisible = content.style.display !== 'none';
    content.style.display = isVisible ? 'none' : 'block';
  }

  // ========================================
  // PDF
  // ========================================
  window.gerarPdfCliente = function() {
    window.open(BASE_URL + '/fechamento/pdf/' + EVENTO_ID + '?sem_valores=1', '_blank');
  };

  window.gerarPdfInterno = function() {
    window.open(BASE_URL + '/fechamento/pdf/' + EVENTO_ID, '_blank');
  };

  // ========================================
  // Outros Custos (aba 4)
  // ========================================
  function loadOutros() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-outros');
    fetchJson(BASE_URL + '/fechamento/outros/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(data) {
      hideLoading('fechamento-outros');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-outros');
        return;
      }
      renderOutros(data.data);
      showContent('fechamento-outros');
    })
    .catch(function(err) {
      hideLoading('fechamento-outros');
      showToast('red', 'Erro', 'Erro ao carregar outros custos');
      console.error(err);
    });
  }

  function renderOutros(lista) {
    var container = document.getElementById('fechamento-outros-content');
    if (!container) return;

    var html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Descricao</th>' +
      '<th>Valor</th>' +
      '<th>Vencimento</th>' +
      '<th>Status</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    lista.forEach(function(o) {
      var enviado = o.enviado_contas_pagar === 'S';
      var statusPago   = o.status === 'PAGO';
      var statusVencido = o.status === 'VENCIDO';

      var statusBadge = '';
      if (!enviado) {
        statusBadge = '<span class="badge sm yellow">Pendente</span>';
      } else if (statusPago) {
        statusBadge = '<span class="badge sm green">Pago</span>';
      } else if (statusVencido) {
        statusBadge = '<span class="badge sm red">Vencido</span>';
      } else {
        statusBadge = '<span class="badge sm blue">Enviado</span>';
      }

      var obs = o.observacao
        ? '<br><small style="font-size:11px;color:var(--text-3)">' + escapeHtml(o.observacao) + '</small>'
        : '';
      var nfLink = o.nota_fiscal
        ? '<br><a href="/' + escapeHtml(o.nota_fiscal) + '" target="_blank" style="font-size:11px;color:var(--primary)">&#128206; Ver NF</a>'
        : '';

      var popId = 'pop-outro-' + o.id;
      var acoesHtml =
        '<div class="td-actions"><div class="td-act-menu">' +
          '<button type="button" class="btn btn-sm td-act-toggle" onclick="togglePop(\'' + popId + '\')">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' +
            '</svg>' +
          '</button>' +
          '<div class="td-act-dropdown" id="' + popId + '">';

      if (!enviado) {
        acoesHtml +=
          '<button type="button" class="td-act-item td-act-cyan" onclick="togglePop(\'' + popId + '\');window.Fechamento.editarOutroCusto(' + o.id + ')"><span>Editar</span></button>' +
          '<button type="button" class="td-act-item td-act-red"  onclick="togglePop(\'' + popId + '\');window.Fechamento.excluirOutroCusto(' + o.id + ')"><span>Excluir</span></button>' +
          '<button type="button" class="td-act-item td-act-purple" onclick="togglePop(\'' + popId + '\');window.Fechamento.enviarOutroCusto(' + o.id + ')"><span>Enviar Pgto</span></button>';
      } else {
        acoesHtml +=
          '<button type="button" class="td-act-item td-act-gray" disabled><span>Enviado p/ Pgto</span></button>';
      }

      acoesHtml += '</div></div></div>';

      html += '<tr>' +
        '<td>' + escapeHtml(o.descricao || '-') + obs + nfLink + '</td>' +
        '<td style="font-weight:600;color:var(--neon-red)">R$ ' + formatMoney(o.valor) + '</td>' +
        '<td>' + formatDateBR(o.data_vencimento) + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>' + acoesHtml + '</td>' +
        '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = '<div class="table-wrap">' + html + '</div>';
  }

  function adicionarOutroCusto() {
    var modal = document.getElementById('modal-outro-custo');
    if (!modal) { showToast('red', 'Erro', 'Modal nao encontrado'); return; }

    document.getElementById('modal-outro-id').value = '0';
    document.getElementById('modal-outro-descricao').value = '';
    document.getElementById('modal-outro-valor').value = '';
    document.getElementById('modal-outro-vencimento').value = '';
    document.getElementById('modal-outro-obs').value = '';
    var nfInput = document.getElementById('modal-outro-nf');
    if (nfInput) nfInput.value = '';
    var nfNome = document.getElementById('modal-outro-nf-nome');
    if (nfNome) nfNome.textContent = 'Nenhum arquivo selecionado';
    var nfAtual = document.getElementById('modal-outro-nf-atual');
    if (nfAtual) nfAtual.innerHTML = '';

    var titleEl = modal.querySelector('.modal-title');
    if (titleEl) titleEl.textContent = 'Novo Outro Custo';

    openModal('modal-outro-custo');
  }

  function editarOutroCusto(id) {
    fetchJson(BASE_URL + '/fechamento/outros/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(resp) {
      if (!resp.success || !resp.data) return;
      var item = resp.data.find(function(x) { return parseInt(x.id) === parseInt(id); });
      if (!item) { showToast('red', 'Erro', 'Custo nao encontrado'); return; }

      document.getElementById('modal-outro-id').value = item.id;
      document.getElementById('modal-outro-descricao').value = item.descricao || '';
      var v = parseFloat(item.valor || 0);
      document.getElementById('modal-outro-valor').value = v.toFixed(2).replace('.', ',');
      document.getElementById('modal-outro-vencimento').value = item.data_vencimento || '';
      document.getElementById('modal-outro-obs').value = item.observacao || '';

      // NF: limpar input, resetar nome e mostrar arquivo atual se houver
      var nfInput = document.getElementById('modal-outro-nf');
      if (nfInput) nfInput.value = '';
      var nfNome = document.getElementById('modal-outro-nf-nome');
      if (nfNome) nfNome.textContent = 'Nenhum arquivo selecionado';
      var nfAtual = document.getElementById('modal-outro-nf-atual');
      if (nfAtual) {
        nfAtual.innerHTML = item.nota_fiscal
          ? '📎 NF atual: <a href="/' + escapeHtml(item.nota_fiscal) + '" target="_blank">ver arquivo</a> — envie outro para substituir'
          : '';
      }

      var modal = document.getElementById('modal-outro-custo');
      var titleEl = modal ? modal.querySelector('.modal-title') : null;
      if (titleEl) titleEl.textContent = 'Editar Outro Custo';

      openModal('modal-outro-custo');
    })
    .catch(function() { showToast('red', 'Erro', 'Falha ao carregar dados'); });
  }

  function salvarOutroCusto() {
    var id = document.getElementById('modal-outro-id').value;
    var isNew = (!id || id === '0');
    var url = isNew
      ? BASE_URL + '/fechamento/outro/store'
      : BASE_URL + '/fechamento/outro/update/' + id;

    var fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);
    fd.append('evento_id', EVENTO_ID);
    fd.append('descricao', document.getElementById('modal-outro-descricao').value.trim());
    fd.append('valor', document.getElementById('modal-outro-valor').value);
    fd.append('data_vencimento', document.getElementById('modal-outro-vencimento').value);
    fd.append('observacao', document.getElementById('modal-outro-obs').value.trim());
    var nfInput = document.getElementById('modal-outro-nf');
    if (nfInput && nfInput.files && nfInput.files[0]) {
      fd.append('nota_fiscal', nfInput.files[0]);
    }

    fetch(url, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        closeModal('modal-outro-custo');
        showToast('green', 'Sucesso', isNew ? 'Custo adicionado' : 'Custo atualizado');
        loadOutros();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao salvar');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro ao processar requisicao'); });
  }

  function excluirOutroCusto(id) {
    if (!confirm('Tem certeza que deseja excluir este custo?')) return;

    var fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/fechamento/outro/delete/' + id, {
      method: 'POST',
      body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Excluido', 'Custo removido com sucesso');
        loadOutros();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao excluir');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro ao processar'); });
  }

  function selecionarNF(input) {
    var nome = document.getElementById('modal-outro-nf-nome');
    if (nome) {
      nome.textContent = (input.files && input.files[0]) ? input.files[0].name : 'Nenhum arquivo selecionado';
    }
  }

  function enviarOutroCusto(id) {
    if (!confirm('Enviar este custo para pagamento em Contas a Pagar?\nApós o envio, edite ou exclua diretamente em /contas-pagar.')) return;

    var fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/fechamento/outro/enviar/' + id, {
      method: 'POST',
      body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Enviado', 'Custo enviado para Contas a Pagar com sucesso');
        loadOutros();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao enviar para pagamento');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro ao processar requisicao'); });
  }

  // ========================================
  // Helpers
  // ========================================
  function showLoading(prefix) {
    var loading = document.getElementById(prefix + '-loading');
    var content = document.getElementById(prefix + '-content');
    var empty = document.getElementById(prefix + '-empty');
    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';
    if (empty) empty.style.display = 'none';
  }

  function hideLoading(prefix) {
    var loading = document.getElementById(prefix + '-loading');
    if (loading) loading.style.display = 'none';
  }

  function showContent(prefix) {
    var loading = document.getElementById(prefix + '-loading');
    var content = document.getElementById(prefix + '-content');
    if (loading) loading.style.display = 'none';
    if (content) content.style.display = 'block';
  }

  function showEmpty(prefix) {
    var loading = document.getElementById(prefix + '-loading');
    var empty = document.getElementById(prefix + '-empty');
    if (loading) loading.style.display = 'none';
    if (empty) empty.style.display = 'block';
  }

  function formatMoney(val) {
    return Number(val || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function formatDateBR(dateStr) {
    if (!dateStr) return '-';
    var parts = dateStr.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return dateStr;
  }

  function formatDateTimeBR(dateTimeStr) {
    if (!dateTimeStr) return '-';
    var parts = dateTimeStr.split(' ');
    var datePart = parts[0].split('-');
    var timePart = parts[1] ? parts[1].substring(0, 5) : '';
    if (datePart.length === 3) return datePart[2] + '/' + datePart[1] + '/' + datePart[0] + (timePart ? ' ' + timePart : '');
    return dateTimeStr;
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function escapeJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\n/g,'\\n');
  }

  // ========================================
  // Mascara de Moeda (R$)
  // ========================================
  window.mascaraMoeda = function(input) {
    var value = input.value.replace(/\D/g, '');
    if (value === '') {
      input.value = '';
      return;
    }
    
    // Converter para centavos e formatar
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    input.value = value;
  };

  // ========================================
  // Ver Contas a Pagar do Fornecedor
  // ========================================
  function verContasPagar(idCotacao, nome) {
    // Buscar dados do fornecedor na lista carregada
    var container = document.getElementById('fechamento-fornecedores-content');
    if (!container) {
      showToast('red', 'Erro', 'Dados nao encontrados');
      return;
    }

    // Fazer fetch para buscar dados atualizados
    fetch(BASE_URL + '/fechamento/fornecedor/contas/' + idCotacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success || !data.data || data.data.length === 0) {
        showToast('yellow', 'Atencao', 'Nenhuma conta a pagar encontrada para este fornecedor');
        return;
      }

      var contas = data.data;
      var modalBody = document.getElementById('modal-contas-pagar-body');
      if (!modalBody) {
        criarModalContasPagar(contas, nome);
        modalBody = document.getElementById('modal-contas-pagar-body');
      }
      renderContasPagarModal(modalBody, contas, nome);
      openModal('modal-contas-pagar');
    })
    .catch(function(err) {
      showToast('red', 'Erro', 'Erro ao carregar contas a pagar');
      console.error(err);
    });
  }

  function renderContasPagarModal(container, contas, nome) {
    var html = '<div style="margin-bottom:16px">' +
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

    contas.forEach(function(conta) {
      var statusBadge = '';
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
    container.innerHTML = html;
  }

  function criarModalContasPagar(contas, nome) {
    var modalHtml = '<div class="modal-overlay" id="modal-contas-pagar" onclick="if(event.target===this)closeModal(\'modal-contas-pagar\')">' +
      '<div class="modal modal-lg">' +
        '<div class="modal-header">' +
          '<div class="modal-title">Contas a Pagar</div>' +
          '<button class="modal-close" onclick="closeModal(\'modal-contas-pagar\')">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="modal-body" id="modal-contas-pagar-body"></div>' +
        '<div class="modal-footer">' +
          '<button type="button" class="btn btn-gray" onclick="closeModal(\'modal-contas-pagar\')">Fechar</button>' +
        '</div>' +
      '</div>' +
    '</div>';

    document.body.insertAdjacentHTML('beforeend', modalHtml);
  }

  function abrirModalHorasExtras(idAlocacao, nome) {
    document.getElementById('modal-he-id-alocacao').value = idAlocacao;
    document.getElementById('modal-he-nome').textContent = nome;

    // Limpar form
    document.getElementById('form-lancar-he').reset();
    
    // Set data default to today
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('he-data').value = today;

    // Buscar lista
    var listaEl = document.getElementById('he-lista');
    listaEl.innerHTML = '<div style="text-align:center;padding:20px">' + renderSpinner({variant:'dots',size:'sm'}) + '</div>';

    openModal('modal-horas-extras');

    fetch(BASE_URL + '/fechamento/horas-extras/' + idAlocacao, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success || !data.data || data.data.length === 0) {
        listaEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-4);font-size:13px">Nenhuma hora extra registrada para este colaborador.</div>';
        return;
      }
      var tblHtml = '<table class="tbl" width="100%"><thead><tr>' +
        '<th>Data</th><th>Horas</th><th>Valor</th><th>Motivo</th><th style="width:50px">Excluir</th>' +
        '</tr></thead><tbody>';

      data.data.forEach(function(he) {
        tblHtml += '<tr>' +
          '<td>' + formatDateBR(he.data) + '</td>' +
          '<td>' + escapeHtml(he.horas) + '</td>' +
          '<td>R$ ' + formatMoney(he.valor) + '</td>' +
          '<td>' + escapeHtml(he.motivo) + '</td>' +
          '<td style="text-align:center">' +
            '<button type="button" class="btn btn-sm btn-red" onclick="window.Fechamento.deletarHoraExtra(' + he.id + ',' + idAlocacao + ',\'' + escapeJs(nome) + '\')" style="padding:6px 10px;" title="Excluir Hora Extra">' +
              '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
            '</button>' +
          '</td>' +
          '</tr>';
      });

      tblHtml += '</tbody></table>';
      listaEl.innerHTML = '<div class="table-wrap">' + tblHtml + '</div>';
    })
    .catch(function(err) {
      listaEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--neon-red)">Erro ao carregar horas extras</div>';
      console.error(err);
    });
  }

  function salvarHoraExtra(event) {
    event.preventDefault();

    var idAlocacao = document.getElementById('modal-he-id-alocacao').value;
    var nome = document.getElementById('modal-he-nome').textContent;
    var data = document.getElementById('he-data').value;
    var horas = document.getElementById('he-horas').value;
    var valor = document.getElementById('he-valor').value;
    var motivo = document.getElementById('he-motivo').value;

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_alocacao', idAlocacao);
    formData.append('data', data);
    formData.append('horas', horas);
    formData.append('valor', valor);
    formData.append('motivo', motivo);

    fetch(BASE_URL + '/fechamento/horas-extras/lancar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Hora extra lançada com sucesso');
        abrirModalHorasExtras(idAlocacao, nome);
        loadColaboradores(); // Recarregar tabela principal em background
        loadTotais(); // Recarregar totais em background
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao lançar hora extra');
      }
    })
    .catch(function(err) {
      showToast('red', 'Erro', 'Erro na requisição');
      console.error(err);
    });
  }

  function deletarHoraExtra(idHe, idAlocacao, nome) {
    if (!confirm('Deseja realmente excluir esta hora extra?')) return;

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/fechamento/horas-extras/deletar/' + idHe, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Hora extra excluída com sucesso');
        abrirModalHorasExtras(idAlocacao, nome);
        loadColaboradores(); // Recarregar tabela principal em background
        loadTotais(); // Recarregar totais em background
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir hora extra');
      }
    })
    .catch(function(err) {
      showToast('red', 'Erro', 'Erro na requisição');
      console.error(err);
    });
  }

  // Helper para spinner (caso nao esteja disponivel globalmente)
  function renderSpinner(opts) {
    opts = opts || {};
    var size = opts.size === 'sm' ? '16px' : opts.size === 'lg' ? '40px' : '24px';
    var color = opts.variant === 'cyan' ? 'var(--neon-cyan)' : 'var(--text-3)';
    return '<div style="display:inline-block;width:' + size + ';height:' + size + ';border:3px solid ' + color + ';border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite"></div>';
  }

  // ========================================
  // Editar Parcela (fornecedor já enviado)
  // ========================================
  function editarParcela(idConta, valor, vencimento) {
    var modal = document.getElementById('modal-editar-parcela');
    if (!modal) {
      var html = '<div class="modal-overlay" id="modal-editar-parcela" onclick="if(event.target===this)closeModal(\'modal-editar-parcela\')">' +
        '<div class="modal modal-md">' +
          '<div class="modal-header">' +
            '<div class="modal-title">Editar Parcela</div>' +
            '<button class="modal-close" onclick="closeModal(\'modal-editar-parcela\')">' +
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
            '<button type="button" class="btn btn-gray" onclick="closeModal(\'modal-editar-parcela\')">Cancelar</button>' +
            '<button type="button" class="btn btn-purple" onclick="window.Fechamento.salvarEdicaoParcela()">Salvar</button>' +
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
    var idConta = document.getElementById('ep-id-conta').value;
    var valorRaw = document.getElementById('ep-valor').value || '0';
    var vencimento = document.getElementById('ep-vencimento').value;
    var valor = valorRaw.replace(/\./g, '').replace(',', '.');

    if (!valor || parseFloat(valor) <= 0) {
      showToast('yellow', 'Atencao', 'Informe um valor valido');
      return;
    }
    if (!vencimento) {
      showToast('yellow', 'Atencao', 'Informe a data de vencimento');
      return;
    }

    var fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);
    fd.append('valor', valor);
    fd.append('data_vencimento', vencimento);

    fetch(BASE_URL + '/fechamento/fornecedor/parcela/update/' + idConta, {
      method: 'POST',
      body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Parcela atualizada');
        closeModal('modal-editar-parcela');
        loadFornecedores();
        loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao atualizar parcela');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  // ========================================
  // Public API
  // ========================================
  window.Fechamento = {
    verPresencas: verPresencas,
    abrirModalColaborador: abrirModalColaborador,
    enviarColaborador: enviarColaboradorPagamento,
    abrirModalFornecedor: abrirModalFornecedor,
    enviarFornecedor: enviarFornecedorPagamento,
    pagarParcela: pagarParcela,
    confirmarPagamentoParcela: confirmarPagamentoParcela,
    verContasPagar: verContasPagar,
    uploadFoto: uploadFoto,
    removerFoto: removerFoto,
    toggleSalaFotos: toggleSalaFotos,
    loadColaboradores: loadColaboradores,
    loadFornecedores: loadFornecedores,
    loadFotos: loadFotos,
    loadOutros: loadOutros,
    loadTotais: loadTotais,
    adicionarOutroCusto: adicionarOutroCusto,
    editarOutroCusto: editarOutroCusto,
    salvarOutroCusto: salvarOutroCusto,
    excluirOutroCusto: excluirOutroCusto,
    selecionarNF: selecionarNF,
    enviarOutroCusto: enviarOutroCusto,
    abrirModalHorasExtras: abrirModalHorasExtras,
    salvarHoraExtra: salvarHoraExtra,
    deletarHoraExtra: deletarHoraExtra,
    editarParcela: editarParcela,
    salvarEdicaoParcela: salvarEdicaoParcela
  };

  // ========================================
  // Init
  // ========================================
  function init() {
    loadColaboradores();
    loadFornecedores();
    loadFotos();
    loadOutros();
    loadTotais();

    // Foto geral upload handler
    var inputGeral = document.getElementById('input-foto-geral');
    if (inputGeral) {
      inputGeral.addEventListener('change', function() {
        uploadFoto(null, this);
      });
    }
  }

  if (document.readyState !== 'loading') { init(); }
  else { document.addEventListener('DOMContentLoaded', init); }
})();

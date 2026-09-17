/**
 * JavaScript - Tab: Salas e Produtos (VTab 1)
 * Modulo: Eventos > Editar
 * Refatorado: SEM REFRESH DE PAGINA - Tudo via AJAX/DOM
 * 
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado (USADO)
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST (USADO)
 * - window.SALAS_MAP: Mapa de salas do evento (USADO)
 */
(function() {
  'use strict';

  var CSRF_TOKEN = window.CSRF_TOKEN;
  var EVENTO_ID = window.EVENTO_ID;
  var SALAS_MAP = window.SALAS_MAP || {};
  var planilhaItens = [];

  var state = {
    items: [],
    totalVenda: 0,
    totalCusto: 0,
    dirty: false
  };

  // ==========================================
  // UTILITARIOS
  // ==========================================

  function parseBrMoney(val) {
    if (!val) return 0;
    return parseFloat(val.toString().replace(/\./g, '').replace(',', '.')) || 0;
  }

  function formatBrl(n) {
    return (n || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }

  function formatCurrencyInput(input) {
    var num = parseBrMoney(input.value);
    input.value = num ? formatBrl(num) : '';
  }

  function stripCurrencyOnFocus(input) {
    input.value = input.value.replace(/\./g, '').replace(',', '.');
  }

  var debounceTimers = {};
  function debounce(fn, delay, key) {
    if (debounceTimers[key]) clearTimeout(debounceTimers[key]);
    debounceTimers[key] = setTimeout(fn, delay);
  }

  // ==========================================
  // GERENCIAMENTO DE ESTADO VAZIO
  // ==========================================

  function mostrarEstadoVazio() {
    var cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;
    if (cardBody.querySelector('.sala-item[data-item-id]')) return;
    if (cardBody.querySelector('.estado-vazio-msg')) return;

    cardBody.querySelectorAll('.sala-bloco').forEach(function(bloco) {
      if (!bloco.querySelector('.sala-item[data-item-id]')) {
        bloco.remove();
      }
    });

    var emptyDiv = document.createElement('div');
    emptyDiv.className = 'estado-vazio-msg';
    emptyDiv.style.cssText = 'text-align:center;padding:60px 20px;color:var(--text-3)';
    emptyDiv.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:48px;height:48px;margin-bottom:12px;opacity:0.4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5m0-5h-2.5M4 18h16"/></svg>' +
                         '<p style="font-size:14px;font-weight:600;margin-bottom:4px">Nenhum item adicionado</p>' +
                         '<p style="font-size:12px">Use o formulario acima para adicionar produtos</p>';
    cardBody.appendChild(emptyDiv);
  }

  function esconderEstadoVazio() {
    var cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;
    cardBody.querySelectorAll('.estado-vazio-msg').forEach(function(el) {
      el.remove();
    });
    var emptyById = document.getElementById('estado-vazio-salas');
    if (emptyById) emptyById.remove();
    cardBody.querySelectorAll('.sala-bloco').forEach(function(bloco) {
      if (!bloco.querySelector('.sala-item[data-item-id]')) {
        bloco.remove();
      }
    });
  }

  // ==========================================
  // CALCULOS EM TEMPO REAL
  // ==========================================

  function calcularItem(div) {
    if (!div) return;
    var quantidade = parseInt(div.querySelector('.quantidade-item-input')?.value) || 1;
    var valorUnit = parseBrMoney(div.querySelector('.valor-item-input')?.value);
    var dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
    var total = quantidade * valorUnit * dias;

    var totalEl = div.querySelector('.sala-field-total .tag, .sala-field-total .salas-produtos-total-display');
    if (totalEl) totalEl.textContent = 'R$ ' + formatBrl(total);

    var salaBloco = div.closest('.sala-bloco');
    if (salaBloco) atualizarSubtotalSala(salaBloco);
    calcularTotal();
  }

  function atualizarSubtotalSala(salaBloco) {
    if (!salaBloco) return;
    var venda = 0, custo = 0;
    salaBloco.querySelectorAll('.sala-item[data-item-id]').forEach(function(div) {
      var qtd = parseInt(div.querySelector('.quantidade-item-input')?.value) || 0;
      var val = parseBrMoney(div.querySelector('.valor-item-input')?.value);
      var cus = parseBrMoney(div.querySelector('.custo-fornecedor')?.value);
      var dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
      venda += qtd * val * dias;
      custo += cus;
    });

    var lucro = venda - custo;
    var margem = venda > 0 ? (lucro / venda) * 100 : 0;

    var headerBadges = salaBloco.querySelector('.sala-header-right');
    if (headerBadges) {
      var tags = headerBadges.querySelectorAll('.tag');
      if (tags[0]) tags[0].textContent = 'Venda: R$ ' + formatBrl(venda);
      if (tags[1]) tags[1].textContent = 'Custo: R$ ' + formatBrl(custo);
      if (tags[2]) {
        tags[2].textContent = 'Lucro: R$ ' + formatBrl(lucro) + ' (' + margem.toFixed(1).replace('.', ',') + '%)';
        tags[2].className = 'tag ' + (lucro >= 0 ? 'green' : 'red');
      }
    }

    var subtotal = salaBloco.querySelector('.sala-subtotal');
    if (subtotal) {
      var subBadges = subtotal.querySelectorAll('.badge');
      if (subBadges[0]) subBadges[0].textContent = 'Venda: R$ ' + formatBrl(venda);
      if (subBadges[1]) subBadges[1].textContent = 'Custo: R$ ' + formatBrl(custo);
      if (subBadges[2]) subBadges[2].textContent = 'Lucro: R$ ' + formatBrl(lucro) + ' (' + margem.toFixed(1).replace('.', ',') + '%)';
    }
  }

  var cachedColaboradores = null;

  function loadColaboradores() {
    var elColab = document.getElementById('total-colaboradores');
    if (!elColab) return;

    fetch(BASE_URL + '/fechamento/totais/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(data) {
      if (data.success && data.data) {
        cachedColaboradores = parseFloat(data.data.total_colaboradores) || 0;
        elColab.textContent = 'R$ ' + formatBrl(cachedColaboradores);
        elColab.style.color = 'var(--neon-cyan)';
        calcularTotal();
      } else {
        elColab.textContent = 'R$ 0,00';
      }
    })
    .catch(function() {
      elColab.textContent = 'R$ 0,00';
      console.warn('Nao foi possivel carregar custo de colaboradores');
    });
  }

  function calcularTotal() {
    var venda = 0, custo = 0;
    document.querySelectorAll('.sala-item[data-item-id]').forEach(function(div) {
      var qtd = parseInt(div.querySelector('.quantidade-item-input')?.value) || 0;
      var val = parseBrMoney(div.querySelector('.valor-item-input')?.value);
      var cus = parseBrMoney(div.querySelector('.custo-fornecedor')?.value);
      var dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
      venda += qtd * val * dias;
      custo += cus;
    });
    var colaboradores = (cachedColaboradores !== null ? cachedColaboradores : 0);
    var lucro = venda - custo - colaboradores;
    var margem = venda > 0 ? (lucro / venda) * 100 : 0;
    var elVenda = document.getElementById('total-venda');
    var elCusto = document.getElementById('total-custo');
    var elLucro = document.getElementById('total-lucro');
    if (elVenda) elVenda.textContent = 'R$ ' + formatBrl(venda);
    if (elCusto) elCusto.textContent = 'R$ ' + formatBrl(custo);
    if (elLucro) {
      elLucro.textContent = 'R$ ' + formatBrl(lucro) + ' (' + margem.toFixed(1).replace('.', ',') + '%)';
      elLucro.style.color = lucro > 0 ? 'var(--neon-green)' : 'var(--neon-red)';
    }
  }

  // ==========================================
  // RENDERIZACAO DE ITENS
  // ==========================================

  function addItemRow(item) {
    var qtd = parseInt(item.quantidade) || parseInt(item.qtd) || 1;
    var valor = parseFloat(item.valor_unitario) || parseFloat(item.valor_unit) || 0;
    var custo = parseFloat(item.valor_pago_fornecedor) || parseFloat(item.custo_unit) || 0;
    var dias = parseInt(item.dias_locacao) || parseInt(item.dias) || 1;
    var totalItem = qtd * valor * dias;
    var nomeProduto = item.item || item.produto || '';
    var desc = item.planilha_descricao || '';
    var catNome = item.categoria_nome || '';
    var salaKey = item.sala_id || item.id_sala || 'sem_sala';
    salaKey = String(salaKey);

    esconderEstadoVazio();

    var cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;

    var salaBloco = cardBody.querySelector('.sala-bloco[data-sala-id="' + salaKey + '"]');
    if (!salaBloco) {
      salaBloco = criarBlocoSala(salaKey);
      cardBody.appendChild(salaBloco);
    }

    var itensWrap = salaBloco.querySelector('.sala-itens-wrap');
    if (!itensWrap) return;

    var div = criarElementoItem(item, qtd, valor, custo, dias, totalItem, nomeProduto, desc, catNome);

    var inserido = false;
    var itensExistentes = itensWrap.querySelectorAll('.sala-item[data-item-id]');
    for (var i = 0; i < itensExistentes.length; i++) {
      var itemExistente = itensExistentes[i];
      var catExistente = itemExistente.querySelector('.sala-item-categoria');
      var catExistenteNome = catExistente ? catExistente.textContent : '';

      if (catNome && !catExistenteNome) {
        itensWrap.insertBefore(div, itemExistente);
        inserido = true;
        break;
      }
      if (catNome && catExistenteNome && catNome.localeCompare(catExistenteNome, 'pt-BR') < 0) {
        itensWrap.insertBefore(div, itemExistente);
        inserido = true;
        break;
      }
      if (catNome === catExistenteNome) {
        var nomeExistente = itemExistente.querySelector('.sala-item-nome');
        var nomeExistenteTexto = nomeExistente ? nomeExistente.textContent : '';
        if (nomeProduto.localeCompare(nomeExistenteTexto, 'pt-BR') < 0) {
          itensWrap.insertBefore(div, itemExistente);
          inserido = true;
          break;
        }
      }
    }
    if (!inserido) itensWrap.appendChild(div);

    atualizarSubtotalSala(salaBloco);
    calcularTotal();

    state.items.push({
      id: item.id,
      sala_id: salaKey,
      produto: nomeProduto,
      quantidade: qtd,
      valor_unit: valor,
      custo_unit: custo,
      dias: dias,
      categoria: catNome
    });
    state.dirty = true;
  }

  function criarBlocoSala(salaKey) {
    var salaNome = 'Sem Sala';
    var salaObs = '';
    if (salaKey !== 'sem_sala' && SALAS_MAP[salaKey]) {
      salaNome = SALAS_MAP[salaKey].nome;
      salaObs = SALAS_MAP[salaKey].obs || '';
    } else if (salaKey !== 'sem_sala') {
      var salaSelect = document.getElementById('id_sala');
      if (salaSelect) {
        for (var i = 0; i < salaSelect.options.length; i++) {
          if (salaSelect.options[i].value == salaKey) {
            salaNome = salaSelect.options[i].text;
            break;
          }
        }
      }
    }

    var obsHtml = '';
    if (salaObs) {
      obsHtml = '<div class="sala-obs-row">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' +
        '<span>' + esc(salaObs) + '</span>' +
      '</div>';
    }

    var editBtnHtml = '';
    if (salaKey !== 'sem_sala') {
      editBtnHtml = '<button type="button" class="btn btn-icon-xs btn-purple" data-tip="Editar Sala" onclick="editarSala(' + parseInt(salaKey) + ', \'' + esc(salaNome).replace(/'/g, "\\'") + '\', \'' + esc(salaObs).replace(/'/g, "\\'") + '\')" title="Editar Sala">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' +
        '</button>';
    }

    var bloco = document.createElement('div');
    bloco.className = 'sala-bloco';
    bloco.setAttribute('data-sala-id', salaKey);
    bloco.innerHTML =
      '<div class="sala-header">' +
        '<div class="sala-header-left"><span class="sala-nome">' + esc(salaNome) + '</span>' + editBtnHtml + '</div>' +
        '<div class="sala-header-right">' +
          '<span class="tag cyan">Venda: R$ 0,00</span>' +
          '<span class="tag red">Custo: R$ 0,00</span>' +
          '<span class="tag green">Lucro: R$ 0,00 (0,0%)</span>' +
        '</div>' +
      '</div>' +
      obsHtml +
      '<div class="sala-itens-wrap"></div>';

    return bloco;
  }

  function criarElementoItem(item, qtd, valor, custo, dias, totalItem, nomeProduto, desc, catNome) {
    var div = document.createElement('div');
    div.className = 'sala-item';
    div.setAttribute('data-item-id', item.id);

    var popId = 'pop-item-' + item.id;
    var escDesc = desc ? esc(desc) : '';
    var escNome = esc(nomeProduto);
    var descHtml = '<div class="popover-wrap">' +
      '<button type="button" class="btn btn-icon-xs btn-blue" data-tip="Detalhes do Item" onclick="togglePopSala(\'' + popId + '\')" title="Ver Detalhes">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
      '</button>' +
      '<div class="popover pop-bottom popover-sala" id="' + popId + '" style="min-width:280px">' +
        '<div class="popover-title">' + escNome + '</div>' +
        (escDesc ? '<div class="popover-text">' + escDesc.replace(/\n/g, '<br>') + '</div>' : '<div class="popover-text" style="color:var(--text-3)">Sem descricao</div>') +
      '</div>' +
    '</div>';

    var totalTag = '<span class="tag green">R$ ' + formatBrl(totalItem) + '</span>';

    div.innerHTML =
      '<div class="sala-item-top">' +
        '<div class="sala-item-info">' +
          '<span class="sala-item-nome">' + esc(nomeProduto) + '</span>' +
          (catNome ? '<span class="sala-item-categoria">' + esc(catNome) + '</span>' : '') +
          descHtml +
        '</div>' +
        '<div class="sala-item-actions">' +
          '<button type="button" class="btn btn-icon-xs btn-cyan" data-tip="Observacao de Montagem" onclick="editarObsItem(' + item.id + ', \'' + esc(nomeProduto).replace(/'/g, "\\'") + '\', \'' + esc(item.observacao_montagem || '').replace(/'/g, "\\'") + '\')" title="Observacao de Montagem"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>' +
          '<button type="button" class="btn btn-icon-xs btn-red" data-tip="Excluir Item" data-delete-item="' + item.id + '" title="Excluir Item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>' +
        '</div>' +
      '</div>' +
      '<div class="sala-item-fields">' +
        '<div class="sala-field-group sala-field-qtd">' +
          '<label class="sala-field-label">Qtd</label>' +
          '<input type="text" class="fi fi-sm quantidade-item-input" value="' + qtd + '" data-item-id="' + item.id + '">' +
        '</div>' +
        '<div class="sala-field-group sala-field-valor">' +
          '<label class="sala-field-label">Valor Unit.</label>' +
          '<div class="fi-prefix-group"><span class="fi-prefix">R$</span><input type="text" class="fi fi-sm valor-item-input fi-disabled" value="' + formatBrl(valor) + '" data-item-id="' + item.id + '" disabled></div>' +
        '</div>' +
        '<div class="sala-field-group sala-field-dias">' +
          '<label class="sala-field-label">Dias</label>' +
          '<input type="text" class="fi fi-sm dias-locacao-input" value="' + dias + '" data-item-id="' + item.id + '">' +
        '</div>' +
        '<div class="sala-field-group sala-field-total">' +
          '<label class="sala-field-label">Total Item</label>' +
          totalTag +
        '</div>' +
        '<div class="sala-field-group sala-field-custo">' +
          '<label class="sala-field-label">Custo Total</label>' +
          '<div class="fi-prefix-group"><span class="fi-prefix">R$</span><input type="text" class="fi fi-sm custo-fornecedor" value="' + formatBrl(custo) + '" data-item-id="' + item.id + '" oninput="if(window.mascaraMoeda)mascaraMoeda(this)"></div>' +
        '</div>' +
      '</div>';

    return div;
  }

  // ==========================================
  // ADICIONAR/ATUALIZAR/DELETAR ITENS (AJAX)
  // ==========================================

  function adicionarItem() {
    var inputItem = document.getElementById('item_proposta');
    var inputQtd = document.getElementById('qtd_item');
    var inputDias = document.getElementById('dias_locacao');
    var inputValor = document.getElementById('valor_item');
    var inputCusto = document.getElementById('valor_custo_fornecedor');
    var inputSala = document.getElementById('id_sala');
    var inputCategoria = document.getElementById('id_categoria');

    if (!inputItem || !inputItem.value.trim()) {
      showToast('red', 'Atencao', 'Nome do produto e obrigatorio');
      return;
    }

    var valorNum = parseBrMoney(inputValor ? inputValor.value : '');
    if (valorNum <= 0) {
      showToast('red', 'Atencao', 'Preencha o Valor Unit. (ex: 100,00)');
      if (inputValor) inputValor.focus();
      return;
    }

    var salaId = inputSala ? inputSala.value || '' : '';
    if (!salaId) {
      if (inputSala && inputSala.options.length <= 1) {
        showToast('red', 'Atencao', 'Nenhuma Sala cadastrada. Clique em "+ Salas" para criar uma sala antes de adicionar itens.');
      } else {
        showToast('red', 'Atencao', 'Por favor, selecione uma Sala para adicionar o item.');
      }
      if (inputSala) inputSala.focus();
      return;
    }

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('item', inputItem.value);
    formData.append('quantidade', parseInt(inputQtd ? inputQtd.value : '1') || 1);
    formData.append('dias', parseInt(inputDias ? inputDias.value : '1') || 1);
    formData.append('valor_unit', valorNum);
    formData.append('custo_unit', parseBrMoney(inputCusto ? inputCusto.value : ''));
    formData.append('id_sala', salaId);
    formData.append('id_categoria', inputCategoria ? inputCategoria.value || '' : '');
    var hiddenPlanilhaId = document.getElementById('planilha_id_hidden');
    if (hiddenPlanilhaId && hiddenPlanilhaId.value) {
      formData.append('id_planilha', hiddenPlanilhaId.value);
    }

    fetch(BASE_URL + '/eventos/itens/adicionar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin',
    })
    .then(function(r) {
      return r.json().then(function(data) {
        if (!r.ok) {
          throw new Error(data.error || ('HTTP ' + r.status));
        }
        return data;
      });
    })
    .then(function(data) {
      if (data.ok && data.item) {
        addItemRow(data.item);
        showToast('green', 'Sucesso', 'Item adicionado!');
        inputItem.value = '';
        if (inputValor) inputValor.value = '';
        if (inputCusto) inputCusto.value = '';
        if (inputCategoria) inputCategoria.value = '';
        inputItem.focus();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao adicionar item');
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro ao adicionar item: ' + err.message); });
  }

  function salvarAlteracaoItem(itemId, campo, valor) {
    var itemState = state.items.find(function(it) { return it.id === itemId; });
    if (itemState) {
      if (campo === 'quantidade') itemState.quantidade = parseFloat(valor) || 0;
      else if (campo === 'valor_unit') itemState.valor_unit = parseFloat(valor) || 0;
      else if (campo === 'custo_unit') itemState.custo_unit = parseFloat(valor) || 0;
      else if (campo === 'dias_locacao') itemState.dias = parseInt(valor) || 1;
      state.dirty = true;
    }

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append(campo, valor);

    fetch(BASE_URL + '/eventos/itens/' + itemId + '/atualizar-campo', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.ok) {
        showToast('yellow', 'Atencao', 'Alteracao aplicada na pagina mas nao foi salva no servidor', 2500);
      }
    })
    .catch(function(err) {
      console.warn('Erro ao salvar campo:', err);
    });
  }

  function excluirItem(itemId) {
    if (!confirm('Tem certeza que deseja excluir este item?')) return;

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('item_id', itemId);

    fetch(BASE_URL + '/eventos/itens/excluir', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin',
    })
    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function(data) {
      if (data.ok) {
        var itemDiv = document.querySelector('.sala-item[data-item-id="' + itemId + '"]');
        var salaBloco = itemDiv ? itemDiv.closest('.sala-bloco') : null;
        if (itemDiv) itemDiv.remove();

        if (salaBloco && !salaBloco.querySelector('.sala-item[data-item-id]')) {
          salaBloco.remove();
          salaBloco = null;
        }

        if (!document.querySelector('.sala-item[data-item-id]')) {
          mostrarEstadoVazio();
        }

        if (salaBloco) atualizarSubtotalSala(salaBloco);
        calcularTotal();

        state.items = state.items.filter(function(it) { return it.id !== itemId; });
        state.dirty = true;

        showToast('green', 'Sucesso', 'Item removido');
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover item');
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro ao remover item'); });
  }

  // ==========================================
  // PLANILHA E AUTOCOMPLETE
  // ==========================================

  var _planilhaItens = [];

  function loadPlanilha() {
    if (_planilhaItens.length > 0) return;
    fetch(BASE_URL + '/api/eventos/itens', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success && data.itens) {
        _planilhaItens = data.itens;
      }
    })
    .catch(function(err) { console.error('Erro ao carregar planilha:', err); });
  }

  function setupAutocomplete() {
    var input = document.getElementById('item_proposta');
    if (!input) return;

    // Criar dropdown
    var dropdown = document.createElement('div');
    dropdown.className = 'autocomplete-dropdown';
    dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;max-height:280px;overflow-y:auto;background:var(--bg-card);border:1px solid var(--bg-border-sub);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:999;display:none;font-size:13px';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(dropdown);

    function normalizar(s) {
      return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\n/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function filtrar(termo) {
      if (!termo || termo.length < 2) { dropdown.style.display = 'none'; return []; }
      var nt = normalizar(termo);
      return _planilhaItens.filter(function(item) {
        return normalizar(item.item).indexOf(nt) !== -1;
      }).slice(0, 15);
    }

    var _dropdownItens = [];
    var selectedIdx = -1;

    function renderDropdown(itens, termo) {
      if (!itens.length) { dropdown.style.display = 'none'; return; }
      _dropdownItens = itens;
      var nt = normalizar(termo);
      dropdown.innerHTML = itens.map(function(item, idx) {
        var nome = item.item;
        var idxMatch = normalizar(nome).indexOf(nt);
        var highlighted = '';
        if (idxMatch !== -1) {
          var before = esc(nome.slice(0, idxMatch));
          var match = esc(nome.slice(idxMatch, idxMatch + termo.length));
          var after = esc(nome.slice(idxMatch + termo.length));
          highlighted = before + '<strong style="background:rgba(59,130,246,0.15);color:var(--neon-cyan)">' + match + '</strong>' + after;
        } else {
          highlighted = esc(nome);
        }
        var valor = parseFloat(item.valor || 0);
        return '<div class="aci-item" data-idx="' + idx + '" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--bg-border-sub);display:flex;justify-content:space-between;align-items:center;transition:background .1s" onmouseover="this.style.background=\'var(--bg-hover)\'" onmouseout="this.style.background=\'\'">' +
          '<span style="flex:1;line-height:1.3">' + highlighted + '</span>' +
          '<span style="margin-left:16px;font-weight:600;color:var(--neon-green);white-space:nowrap;font-size:12px">R$ ' + valor.toFixed(2) + '</span>' +
          '</div>';
      }).join('');
      dropdown.style.display = 'block';
    }

    function selecionar(idx) {
      var item = _dropdownItens[idx];
      if (!item) return;
      input.value = item.item;
      dropdown.style.display = 'none';
      var hid = document.getElementById('planilha_id_hidden');
      if (!hid) {
        hid = document.createElement('input');
        hid.type = 'hidden';
        hid.id = 'planilha_id_hidden';
        hid.name = 'planilha_id_hidden';
        input.parentNode.insertBefore(hid, input.nextSibling);
      }
      hid.value = item.id || '';
      var valorEl = document.getElementById('valor_item');
      if (valorEl && item.valor) valorEl.value = formatBrl(parseFloat(item.valor));
    }

    input.addEventListener('input', function() {
      var itens = filtrar(this.value);
      renderDropdown(itens, this.value);
      selectedIdx = -1;
    });

    input.addEventListener('keydown', function(e) {
      var items = dropdown.querySelectorAll('.aci-item');
      if (!items.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (selectedIdx < items.length - 1) selectedIdx++;
        items.forEach(function(el, i) { el.style.background = i === selectedIdx ? 'var(--bg-hover)' : ''; });
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (selectedIdx > 0) selectedIdx--;
        items.forEach(function(el, i) { el.style.background = i === selectedIdx ? 'var(--bg-hover)' : ''; });
      } else if (e.key === 'Enter' && selectedIdx >= 0) {
        e.preventDefault();
        selecionar(selectedIdx);
      } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
      }
    });

    input.addEventListener('blur', function() {
      setTimeout(function() { dropdown.style.display = 'none'; }, 200);
    });

    dropdown.addEventListener('mousedown', function(e) {
      var item = e.target.closest('.aci-item');
      if (item) {
        var idx = parseInt(item.dataset.idx);
        selecionar(idx);
      }
    });
  }

  // ==========================================
  // SALAS (MODAL)
  // ==========================================

  window.salvarSala = function() {
    var elNome = document.getElementById('nome_sala');
    var elObs = document.getElementById('observacao_sala');
    var elEditId = document.getElementById('editando_sala_id');
    if (!elNome || !elEditId) return;
    var nomeSala = elNome.value.trim();
    if (!nomeSala) { showToast('red', 'Erro', 'Nome da sala e obrigatorio'); return; }

    var editandoId = elEditId.value;
    var isEdit = editandoId !== '';

    var url = isEdit ? BASE_URL + '/api/eventos/salas/' + editandoId : BASE_URL + '/api/eventos/salas';
    var method = isEdit ? 'PUT' : 'POST';
    var obsVal = elObs ? elObs.value : '';
    var body = '_csrf_token=' + CSRF_TOKEN + '&nome_sala=' + encodeURIComponent(nomeSala) + '&observacao_montagem=' + encodeURIComponent(obsVal);
    if (!isEdit) body += '&evento_id=' + encodeURIComponent(EVENTO_ID);

    fetch(url, { method: method, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function(data) {
      if (data.ok) {
        showToast('green', 'Sucesso', isEdit ? 'Sala atualizada' : 'Sala criada');
        cancelarEdicaoSala();
        atualizarListaSalasModal();
        atualizarSelectSalas();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao salvar sala');
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro ao salvar sala'); });
  };

  function atualizarListaSalasModal() {
    fetch(BASE_URL + '/api/eventos/salas?evento_id=' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(salas) {
      if (salas.success) {
        var container = document.getElementById('modal-sala-list');
        if (!container) return;
        var html = '<table style="width:100%;border-collapse:collapse">';
        html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Obs. Montagem</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
        html += '</tr></thead><tbody>';
        if (salas.data.length === 0) {
          html += '<tr><td colspan="3" style="padding:16px;text-align:center;color:var(--text-4)">Nenhuma sala cadastrada</td></tr>';
        } else {
          salas.data.forEach(function(s) {
            html += '<tr style="border-bottom:1px solid var(--bg-border)">';
            html += '<td style="padding:8px;font-size:13px">' + (s.nome_sala || '') + '</td>';
            html += '<td style="padding:8px;font-size:13px;color:var(--text-3)">' + (s.orientacoes_montagem || '') + '</td>';
            html += '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
            html += '<button type="button" class="btn btn-xs btn-purple" onclick="editarSalaModal(' + s.id + ', \'' + (s.nome_sala || '').replace(/'/g, "\\'") + '\', \'' + (s.orientacoes_montagem || '').replace(/'/g, "\\'") + '\')" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
            html += '<button type="button" class="btn btn-xs btn-red" onclick="excluirSala(' + s.id + ', \'' + (s.nome_sala || '').replace(/'/g, "\\'") + '\')" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
            html += '</div></td></tr>';
          });
        }
        html += '</tbody></table>';
        container.innerHTML = html;
      }
    })
    .catch(function() {});
  }

  function atualizarSelectSalas() {
    fetch(BASE_URL + '/api/eventos/salas?evento_id=' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(salas) {
      if (salas.success) {
        var select = document.getElementById('id_sala');
        if (!select) return;
        var valorAnterior = select.value;
        select.innerHTML = '<option value="">Sem Sala</option>';
        salas.data.forEach(function(s) {
          var opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.nome_sala;
          select.appendChild(opt);
        });
        select.value = valorAnterior;
      }
    })
    .catch(function() {});
  }

  window.editarSala = function(id, nome, obs) {
    openModal('modalSala');
    editarSalaModal(id, nome, obs);
  };

  window.editarSalaModal = function(id, nome, obs) {
    var elId = document.getElementById('editando_sala_id');
    var elNome = document.getElementById('nome_sala');
    var elObs = document.getElementById('observacao_sala');
    var elBtn = document.getElementById('btnSalvarSalaText');
    var elCancel = document.getElementById('btnCancelarEdicaoSala');
    if (!elId || !elNome || !elBtn) return;
    elId.value = id;
    elNome.value = nome || '';
    if (elObs) elObs.value = obs || '';
    elBtn.textContent = 'Atualizar Sala';
    if (elCancel) elCancel.style.display = 'inline-flex';
    elNome.focus();
  };

  window.cancelarEdicaoSala = function() {
    var elId = document.getElementById('editando_sala_id');
    var elNome = document.getElementById('nome_sala');
    var elObs = document.getElementById('observacao_sala');
    var elBtn = document.getElementById('btnSalvarSalaText');
    var elCancel = document.getElementById('btnCancelarEdicaoSala');
    if (elId) elId.value = '';
    if (elNome) elNome.value = '';
    if (elObs) elObs.value = '';
    if (elBtn) elBtn.textContent = 'Salvar Sala';
    if (elCancel) elCancel.style.display = 'none';
  };

  window.excluirSala = function(id, nome) {
    if (!confirm('Excluir a sala "' + nome + '"? Esta acao nao pode ser desfeita.')) return;
    fetch(BASE_URL + '/api/eventos/salas/' + id, {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: '_csrf_token=' + CSRF_TOKEN,
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok) {
        showToast('green', 'Sucesso', 'Sala excluida');
        atualizarListaSalasModal();
        atualizarSelectSalas();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir sala');
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro ao excluir sala'); });
  };

  // ==========================================
  // CATEGORIAS (MODAL)
  // ==========================================

  window.editarCategoriaModal = function(id, nome) {
    var elId = document.getElementById('editando_categoria_id');
    var elNome = document.getElementById('nome_categoria');
    var elBtn = document.getElementById('btnSalvarCategoriaText');
    var elCancel = document.getElementById('btnCancelarEdicaoCategoria');
    if (!elId || !elNome || !elBtn) return;
    elId.value = id;
    elNome.value = nome || '';
    elBtn.textContent = 'Atualizar Categoria';
    if (elCancel) elCancel.style.display = 'inline-flex';
    elNome.focus();
  };

  window.cancelarEdicaoCategoria = function() {
    var elId = document.getElementById('editando_categoria_id');
    var elNome = document.getElementById('nome_categoria');
    var elBtn = document.getElementById('btnSalvarCategoriaText');
    var elCancel = document.getElementById('btnCancelarEdicaoCategoria');
    if (elId) elId.value = '';
    if (elNome) elNome.value = '';
    if (elBtn) elBtn.textContent = 'Salvar Categoria';
    if (elCancel) elCancel.style.display = 'none';
  };

  window.salvarCategoria = function() {
    var elNome = document.getElementById('nome_categoria');
    var elEditId = document.getElementById('editando_categoria_id');
    if (!elNome || !elEditId) return;
    var nome = elNome.value.trim();
    if (!nome) { showToast('red', 'Erro', 'Nome da categoria e obrigatorio'); return; }

    var editandoId = elEditId.value;
    var isEdit = editandoId !== '';

    var url = isEdit ? BASE_URL + '/categorias-sala/update/' + editandoId : BASE_URL + '/categorias-sala/store';
    var body = '_csrf_token=' + CSRF_TOKEN + '&nome=' + encodeURIComponent(nome);

    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function(data) {
      if (data.ok || data.success) {
        showToast('green', 'Sucesso', isEdit ? 'Categoria atualizada' : 'Categoria criada');
        cancelarEdicaoCategoria();
        atualizarListaCategoriasModal();
        atualizarSelectCategorias();
      } else {
        showToast('red', 'Erro', data.error || (isEdit ? 'Erro ao atualizar categoria' : 'Erro ao criar categoria'));
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro de conexao'); });
  };

  function atualizarListaCategoriasModal() {
    fetch(BASE_URL + '/api/categorias', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.ok && res.data) {
        var container = document.getElementById('modal-categoria-list');
        if (!container) return;
        var html = '<table style="width:100%;border-collapse:collapse">';
        html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
        html += '</tr></thead><tbody>';
        if (res.data.length === 0) {
          html += '<tr><td colspan="2" style="padding:16px;text-align:center;color:var(--text-4)">Nenhuma categoria cadastrada</td></tr>';
        } else {
          res.data.forEach(function(c) {
            html += '<tr style="border-bottom:1px solid var(--bg-border)">';
            html += '<td style="padding:8px;font-size:13px">' + (c.nome_categoria || '') + '</td>';
            html += '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
            html += '<button type="button" class="btn btn-xs btn-purple" onclick="editarCategoriaModal(' + c.id + ', \'' + (c.nome_categoria || '').replace(/'/g, "\\'") + '\')" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
            html += '<button type="button" class="btn btn-xs btn-red" onclick="excluirCategoria(' + c.id + ', \'' + (c.nome_categoria || '').replace(/'/g, "\\'") + '\')" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
            html += '</div></td></tr>';
          });
        }
        html += '</tbody></table>';
        container.innerHTML = html;
      }
    })
    .catch(function() {});
  }

  function atualizarSelectCategorias() {
    fetch(BASE_URL + '/api/categorias', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.ok && res.data) {
        var select = document.getElementById('id_categoria');
        if (!select) return;
        var valorAnterior = select.value;
        select.innerHTML = '<option value="">Selecione...</option>';
        res.data.forEach(function(c) {
          var opt = document.createElement('option');
          opt.value = c.id;
          opt.textContent = c.nome_categoria;
          select.appendChild(opt);
        });
        select.value = valorAnterior;
      }
    })
    .catch(function() {});
  };

  window.excluirCategoria = function(id, nome) {
    if (!confirm('Excluir a categoria "' + nome + '"? Esta acao nao pode ser desfeita.')) return;
    fetch(BASE_URL + '/categorias-sala/delete/' + id, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: '_csrf_token=' + CSRF_TOKEN,
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok) {
        showToast('green', 'Sucesso', 'Categoria excluida');
        atualizarListaCategoriasModal();
        atualizarSelectCategorias();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir categoria');
      }
    })
    .catch(function(err) { showToast('red', 'Erro', 'Erro ao excluir categoria'); });
  };

  // ==========================================
  // OBSERVACAO DE MONTAGEM (INLINE SEM RELOAD)
  // ==========================================

  var currentItemObsId = null;

  window.editarObsItem = function(itemId, itemName, obs) {
    currentItemObsId = itemId;
    var elName = document.getElementById('modal-item-name');
    var elObs = document.getElementById('observacao_item');
    if (elName) elName.textContent = itemName;
    if (elObs) elObs.value = obs || '';
    var modal = document.getElementById('modal-item-obs');
    if (modal) { modal.style.display = 'flex'; modal.classList.add('open'); }
  };

  window.fecharModalItemObs = function() {
    var modal = document.getElementById('modal-item-obs');
    if (modal) { modal.style.display = 'none'; modal.classList.remove('open'); }
    currentItemObsId = null;
  };

  window.salvarObsItem = function() {
    if (!currentItemObsId) {
      showToast('red', 'Erro', 'ID do item nao identificado');
      return;
    }
    var elObs = document.getElementById('observacao_item');
    var obs = elObs ? elObs.value.trim() : '';
    var itemId = currentItemObsId;

    var itemState = state.items.find(function(it) { return it.id === itemId; });
    if (itemState) {
      itemState.observacao_montagem = obs;
      state.dirty = true;
    }

    var itemDiv = document.querySelector('.sala-item[data-item-id="' + itemId + '"]');
    if (itemDiv) {
      var itemInfo = itemDiv.querySelector('.sala-item-info');
      if (itemInfo) {
        var existingBadge = itemInfo.querySelector('.sala-item-obs-badge');
        if (existingBadge) existingBadge.remove();

        if (obs) {
          var itemName = itemInfo.querySelector('.sala-item-nome');
          if (itemName) {
            var badge = document.createElement('span');
            badge.className = 'sala-item-obs-badge';
            badge.textContent = 'Obs';
            badge.title = 'Ver observacao';
            badge.onclick = function() { editarObsItem(itemId, itemName.textContent, obs); };
            itemName.insertAdjacentElement('afterend', badge);
          }
        }
      }
    }

    fecharModalItemObs();

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('observacao_montagem', obs);

    fetch(BASE_URL + '/api/eventos/itens/' + itemId + '/observacao', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.ok) console.error('Erro ao salvar observacao:', data.error);
    })
    .catch(function(err) {
      console.error('Erro de conexao ao salvar observacao:', err);
    });
  };

  window.getSalasState = function() { return state; };
  window.resetSalasState = function() { state.dirty = false; };

  // ==========================================
  // INICIALIZACAO E EVENT LISTENERS
  // ==========================================

  function init() {
    loadPlanilha();
    setupAutocomplete();

    var btnAdicionar = document.getElementById('btn-adicionar-item');
    if (btnAdicionar) btnAdicionar.addEventListener('click', adicionarItem);

    document.addEventListener('input', function(e) {
      if (e.target.matches('.quantidade-item-input') ||
          e.target.matches('.valor-item-input') ||
          e.target.matches('.dias-locacao-input') ||
          e.target.matches('.custo-fornecedor')) {

        var div = e.target.closest('.sala-item[data-item-id]');
        if (div) {
          calcularItem(div);

          var itemId = e.target.dataset.itemId;
          var campo = '';
          if (e.target.matches('.quantidade-item-input')) campo = 'quantidade';
          else if (e.target.matches('.valor-item-input')) campo = 'valor_unit';
          else if (e.target.matches('.dias-locacao-input')) campo = 'dias';
          else if (e.target.matches('.custo-fornecedor')) campo = 'custo_unit';

          if (campo && itemId) {
            var valor = e.target.matches('.valor-item-input') || e.target.matches('.custo-fornecedor')
              ? parseBrMoney(e.target.value)
              : e.target.value;

            debounce(function() {
              salvarAlteracaoItem(itemId, campo, valor);
            }, 500, 'item-' + itemId + '-' + campo);
          }
        }
      }
    });

    document.addEventListener('click', function(e) {
      var btn = e.target.closest('[data-delete-item]');
      if (btn) excluirItem(parseInt(btn.dataset.deleteItem));
    });

    ['valor_item', 'valor_custo_fornecedor'].forEach(function(id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('blur', function() { formatCurrencyInput(this); });
        el.addEventListener('focus', function() { stripCurrencyOnFocus(this); });
      }
    });

    function calcularDiasEvento() {
      var inputInicio = document.getElementById('data_inicio');
      var inputFim = document.getElementById('data_fim');
      var inputDias = document.getElementById('dias_locacao');
      if (!inputInicio || !inputFim || !inputDias) return;

      var inicio = inputInicio.value;
      var fim = inputFim.value;
      if (inicio && fim) {
        var dtInicio = new Date(inicio + 'T00:00:00');
        var dtFim = new Date(fim + 'T00:00:00');
        if (dtFim >= dtInicio) {
          var diffDias = Math.floor((dtFim - dtInicio) / (1000 * 60 * 60 * 24)) + 1;
          inputDias.value = diffDias;
          document.querySelectorAll('.sala-item[data-item-id] .dias-locacao-input').forEach(function(input) {
            input.value = diffDias;
            var div = input.closest('.sala-item');
            if (div) calcularItem(div);
          });
        }
      }
    }

    var inputInicio = document.getElementById('data_inicio');
    var inputFim = document.getElementById('data_fim');
    if (inputInicio) inputInicio.addEventListener('change', calcularDiasEvento);
    if (inputFim) inputFim.addEventListener('change', calcularDiasEvento);

    var modalSalaEl = document.getElementById('modalSala');
    if (modalSalaEl) {
      new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            if (!modalSalaEl.classList.contains('open')) atualizarListaSalasModal();
          }
        });
      }).observe(modalSalaEl, { attributes: true });
    }

    var modalCategoriaEl = document.getElementById('modalCategoria');
    if (modalCategoriaEl) {
      new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            if (!modalCategoriaEl.classList.contains('open')) atualizarListaCategoriasModal();
          }
        });
      }).observe(modalCategoriaEl, { attributes: true });
    }

    loadColaboradores();
    calcularTotal();
    if (!document.querySelector('.sala-item[data-item-id]')) mostrarEstadoVazio();
  }

  if (document.readyState !== 'loading') { init(); }
  else { document.addEventListener('DOMContentLoaded', init); }
})();

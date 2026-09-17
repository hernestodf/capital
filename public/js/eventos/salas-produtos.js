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
  const CSRF_TOKEN = window.CSRF_TOKEN;
  const EVENTO_ID = window.EVENTO_ID;
  const BASE_URL = window.BASE_URL;
  const SALAS_MAP = window.SALAS_MAP || {};
  const planilhaItens = [];

  const state = {
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

  function escapeHtml(text) {
    const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
    return String(text == null ? '' : text).replace(/[&<>"']/g, m => map[m]);
  }



  function formatCurrencyInput(input) {
    const num = parseBrMoney(input.value);
    input.value = num ? formatBrl(num) : '';
  }

  function stripCurrencyOnFocus(input) {
    input.value = input.value.replace(/\./g, '').replace(',', '.');
  }

  const debounceTimers = {};
  function debounce(fn, delay, key) {
    if (debounceTimers[key]) clearTimeout(debounceTimers[key]);
    debounceTimers[key] = setTimeout(fn, delay);
  }

  // ==========================================
  // GERENCIAMENTO DE ESTADO VAZIO
  // ==========================================

  function mostrarEstadoVazio() {
    const cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;
    if (cardBody.querySelector('.sala-item[data-item-id]')) return;
    if (cardBody.querySelector('.estado-vazio-msg')) return;

    cardBody.querySelectorAll('.sala-bloco').forEach(bloco => {
      if (!bloco.querySelector('.sala-item[data-item-id]')) {
        bloco.remove();
      }
    });

    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'estado-vazio-msg';
    emptyDiv.style.cssText = 'text-align:center;padding:60px 20px;color:var(--text-3)';
    window.renderHTML(emptyDiv, '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:48px;height:48px;margin-bottom:12px;opacity:0.4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5m0-5h-2.5M4 18h16"/></svg>' +
                         '<p style="font-size:14px;font-weight:600;margin-bottom:4px">Nenhum item adicionado</p>' +
                         '<p style="font-size:12px">Use o formulario acima para adicionar produtos</p>');
    cardBody.appendChild(emptyDiv);
  }

  function esconderEstadoVazio() {
    const cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;
    cardBody.querySelectorAll('.estado-vazio-msg').forEach(el => el.remove());
    const emptyById = document.getElementById('estado-vazio-salas');
    if (emptyById) emptyById.remove();
    cardBody.querySelectorAll('.sala-bloco').forEach(bloco => {
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
    const quantidade = parseFloat(div.querySelector('.quantidade-item-input')?.value) || 1;
    const valorUnit = parseBrMoney(div.querySelector('.valor-item-input')?.value);
    const dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
    const total = quantidade * valorUnit * dias;

    const totalEl = div.querySelector('.sala-field-total .tag, .sala-field-total .salas-produtos-total-display');
    if (totalEl) totalEl.textContent = 'R$ ' + formatBrl(total);

    // Energia
    const potW = parseFloat(div.querySelector('.potencia-w-input')?.value) || 0;
    const horas = parseFloat(div.querySelector('.horas-uso-input')?.value) || 20;
    const kwh = potW > 0 ? quantidade * potW * horas / 1000 : 0;
    const kva = kwh * 1.25;
    const kwhEl = div.querySelector('.energia-kwh-display');
    const kvaEl  = div.querySelector('.energia-kva-display');
    if (kwhEl) kwhEl.textContent = kwh > 0 ? fmtEnergia(kwh) : '—';
    if (kvaEl)  kvaEl.textContent  = kva  > 0 ? fmtEnergia(kva)  : '—';

    const salaBloco = div.closest('.sala-bloco');
    if (salaBloco) atualizarSubtotalSala(salaBloco);
    calcularTotal();
  }

  function fmtEnergia(v) {
    return v.toLocaleString('pt-BR', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
  }

  function atualizarSubtotalSala(salaBloco) {
    if (!salaBloco) return;
    let venda = 0, custo = 0, kwh = 0;
    salaBloco.querySelectorAll('.sala-item[data-item-id]').forEach(div => {
      const qtd  = parseFloat(div.querySelector('.quantidade-item-input')?.value) || 0;
      const val  = parseBrMoney(div.querySelector('.valor-item-input')?.value);
      const cus  = parseBrMoney(div.querySelector('.custo-fornecedor')?.value);
      const dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
      const potW = parseFloat(div.querySelector('.potencia-w-input')?.value) || 0;
      const horas = parseFloat(div.querySelector('.horas-uso-input')?.value) || 20;
      venda += qtd * val * dias;
      custo += cus;
      if (potW > 0) kwh += qtd * potW * horas / 1000;
    });

    const lucro = venda - custo;
    const margem = venda > 0 ? (lucro / venda) * 100 : 0;
    const kva = kwh * 1.25;

    const headerBadges = salaBloco.querySelector('.sala-header-right');
    if (headerBadges) {
      const tags = headerBadges.querySelectorAll('.tag');
      if (tags[0]) tags[0].textContent = 'Venda: R$ ' + formatBrl(venda);
      if (tags[1]) tags[1].textContent = 'Custo: R$ ' + formatBrl(custo);
      if (tags[2]) {
        tags[2].textContent = 'Lucro: R$ ' + formatBrl(lucro) + ' (' + margem.toFixed(1).replace('.', ',') + '%)';
        tags[2].className = 'tag ' + (lucro >= 0 ? 'green' : 'red');
      }
      // Badge energia da sala (4º tag, criado dinamicamente se necessário)
      if (kwh > 0) {
        let energiaTag = headerBadges.querySelector('.tag-energia-sala');
        if (!energiaTag) {
          energiaTag = document.createElement('span');
          energiaTag.className = 'tag purple tag-energia-sala';
          headerBadges.appendChild(energiaTag);
        }
        energiaTag.textContent = fmtEnergia(kwh) + ' kWh / ' + fmtEnergia(kva) + ' kVA';
      } else {
        const et = headerBadges.querySelector('.tag-energia-sala');
        if (et) et.remove();
      }
    }

    const subtotal = salaBloco.querySelector('.sala-subtotal');
    if (subtotal) {
      const subBadges = subtotal.querySelectorAll('.badge');
      if (subBadges[0]) subBadges[0].textContent = 'Venda: R$ ' + formatBrl(venda);
      if (subBadges[1]) subBadges[1].textContent = 'Custo: R$ ' + formatBrl(custo);
      if (subBadges[2]) subBadges[2].textContent = 'Lucro: R$ ' + formatBrl(lucro) + ' (' + margem.toFixed(1).replace('.', ',') + '%)';
    }
  }

  let cachedColaboradores = null;

  function loadColaboradores() {
    const elColab = document.getElementById('total-colaboradores');
    if (!elColab) return;

    fetch(BASE_URL + '/fechamento/totais/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (data.success && data.data) {
        cachedColaboradores = parseFloat(data.data.total_colaboradores) || 0;
        elColab.textContent = 'R$ ' + formatBrl(cachedColaboradores);
        elColab.style.color = 'var(--neon-cyan)';
        calcularTotal();
      } else {
        elColab.textContent = 'R$ 0,00';
      }
    })
    .catch(() => {
      elColab.textContent = 'R$ 0,00';
    });
  }

  function calcularTotal() {
    let venda = 0, custo = 0, kwhTotal = 0;
    document.querySelectorAll('.sala-item[data-item-id]').forEach(div => {
      const qtd  = parseFloat(div.querySelector('.quantidade-item-input')?.value) || 0;
      const val  = parseBrMoney(div.querySelector('.valor-item-input')?.value);
      const cus  = parseBrMoney(div.querySelector('.custo-fornecedor')?.value);
      const dias = parseInt(div.querySelector('.dias-locacao-input')?.value) || 1;
      const potW = parseFloat(div.querySelector('.potencia-w-input')?.value) || 0;
      const horas = parseFloat(div.querySelector('.horas-uso-input')?.value) || 20;
      venda += qtd * val * dias;
      custo += cus;
      if (potW > 0) kwhTotal += qtd * potW * horas / 1000;
    });

    // Atualizar total global de energia
    const kvaTotal = kwhTotal * 1.25;
    const energiaBox = document.getElementById('energia-total-global');
    if (energiaBox) {
      energiaBox.style.display = kwhTotal > 0 ? '' : 'none';
      const elKwh = document.getElementById('energia-total-kwh');
      const elKva  = document.getElementById('energia-total-kva');
      if (elKwh) elKwh.textContent = fmtEnergia(kwhTotal) + ' kWh';
      if (elKva)  elKva.textContent  = fmtEnergia(kvaTotal)  + ' kVA';
    }
    const colaboradores = (cachedColaboradores !== null ? cachedColaboradores : 0);
    const lucro = venda - custo - colaboradores;
    const margem = venda > 0 ? (lucro / venda) * 100 : 0;
    const elVenda = document.getElementById('total-venda');
    const elCusto = document.getElementById('total-custo');
    const elLucro = document.getElementById('total-lucro');
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
    const qtd = parseInt(item.quantidade) || parseInt(item.qtd) || 1;
    const valor = parseFloat(item.valor_unitario) || parseFloat(item.valor_unit) || 0;
    const custo = parseFloat(item.valor_pago_fornecedor) || parseFloat(item.custo_unit) || 0;
    const dias = parseInt(item.dias_locacao) || parseInt(item.dias) || 1;
    const totalItem = qtd * valor * dias;
    const nomeProduto = item.item || item.produto || '';
    const desc = item.planilha_descricao || '';
    const catNome = item.categoria_nome || '';
    let salaKey = item.sala_id || item.id_sala || 'sem_sala';
    salaKey = String(salaKey);

    esconderEstadoVazio();

    const cardBody = document.querySelector('.custom-card > .custom-card-body.card-no-padding');
    if (!cardBody) return;

    let salaBloco = cardBody.querySelector('.sala-bloco[data-sala-id="' + salaKey + '"]');
    if (!salaBloco) {
      salaBloco = criarBlocoSala(salaKey);
      cardBody.appendChild(salaBloco);
    }

    const itensWrap = salaBloco.querySelector('.sala-itens-wrap');
    if (!itensWrap) return;

    const div = criarElementoItem(item, qtd, valor, custo, dias, totalItem, nomeProduto, desc, catNome);

    let inserido = false;
    const itensExistentes = itensWrap.querySelectorAll('.sala-item[data-item-id]');
    for (let i = 0; i < itensExistentes.length; i++) {
      const itemExistente = itensExistentes[i];
      const catExistente = itemExistente.querySelector('.sala-item-categoria');
      const catExistenteNome = catExistente ? catExistente.textContent : '';

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
        const nomeExistente = itemExistente.querySelector('.sala-item-nome');
        const nomeExistenteTexto = nomeExistente ? nomeExistente.textContent : '';
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
    let salaNome = 'Sem Sala';
    let salaObs = '';
    if (salaKey !== 'sem_sala' && SALAS_MAP[salaKey]) {
      salaNome = SALAS_MAP[salaKey].nome;
      salaObs = SALAS_MAP[salaKey].obs || '';
    } else if (salaKey !== 'sem_sala') {
      const salaSelect = document.getElementById('id_sala');
      if (salaSelect) {
        for (let i = 0; i < salaSelect.options.length; i++) {
          if (salaSelect.options[i].value == salaKey) {
            salaNome = salaSelect.options[i].text;
            break;
          }
        }
      }
    }

    let obsHtml = '';
    if (salaObs) {
      obsHtml = '<div class="sala-obs-row">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' +
        '<span>' + escapeHtml(salaObs) + '</span>' +
      '</div>';
    }

    let editBtnHtml = '';
    if (salaKey !== 'sem_sala') {
      editBtnHtml = '<button type="button" class="btn btn-icon-xs btn-purple" data-tip="Editar Sala" data-action="editar-sala" data-id="' + parseInt(salaKey) + '" data-nome="' + escapeHtml(salaNome).replace(/'/g, "\\'") + '" data-obs="' + escapeHtml(salaObs).replace(/'/g, "\\'") + '" title="Editar Sala">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' +
        '</button>';
    }

    const bloco = document.createElement('div');
    bloco.className = 'sala-bloco';
    bloco.setAttribute('data-sala-id', salaKey);
    window.renderHTML(bloco,
      '<div class="sala-header">' +
        '<div class="sala-header-left"><span class="sala-nome">' + escapeHtml(salaNome) + '</span>' + editBtnHtml + '</div>' +
        '<div class="sala-header-right">' +
          '<span class="tag cyan">Venda: R$ 0,00</span>' +
          '<span class="tag red">Custo: R$ 0,00</span>' +
          '<span class="tag green">Lucro: R$ 0,00 (0,0%)</span>' +
        '</div>' +
      '</div>' +
      obsHtml +
      '<div class="sala-itens-wrap"></div>');

    return bloco;
  }

  function criarElementoItem(item, qtd, valor, custo, dias, totalItem, nomeProduto, desc, catNome) {
    const div = document.createElement('div');
    div.className = 'sala-item';
    div.setAttribute('data-item-id', item.id);

    const popId = 'pop-item-' + item.id;
    const escDesc = desc ? escapeHtml(desc) : '';
    const escNome = escapeHtml(nomeProduto);
    const descHtml = '<div class="popover-wrap">' +
      '<button type="button" class="btn btn-icon-xs btn-blue" data-tip="Detalhes do Item" data-action="toggle-pop-sala" data-pop-id="' + popId + '" title="Ver Detalhes">' +
        '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
      '</button>' +
      '<div class="popover pop-bottom popover-sala" id="' + popId + '" style="min-width:280px">' +
        '<div class="popover-title">' + escNome + '</div>' +
        (escDesc ? '<div class="popover-text">' + escDesc.replace(/\n/g, '<br>') + '</div>' : '<div class="popover-text" style="color:var(--text-3)">Sem descricao</div>') +
      '</div>' +
    '</div>';

    const totalTag = '<span class="tag green">R$ ' + formatBrl(totalItem) + '</span>';

    // Energia — herdada da planilha (id_planilha) quando o item foi selecionado via autocomplete
    const potW = parseFloat(item.potencia_w) || 0;
    const horasU = parseFloat(item.horas_uso) || 20;
    const kwh = potW > 0 ? qtd * potW * horasU / 1000 : 0;
    const kva = kwh > 0 ? kwh * 1.25 : 0;
    const energiaBtnClass = potW > 0 ? 'btn-yellow' : 'btn-outline';
    const energiaRowStyle = potW > 0 ? '' : 'display:none';

    window.renderHTML(div,
      '<div class="sala-item-top">' +
        '<div class="sala-item-info">' +
          '<span class="sala-item-nome">' + escapeHtml(nomeProduto) + '</span>' +
          (catNome ? '<span class="sala-item-categoria">' + escapeHtml(catNome) + '</span>' : '') +
          descHtml +
        '</div>' +
        '<div class="sala-item-actions">' +
          '<button type="button" class="btn btn-icon-xs ' + energiaBtnClass + ' btn-energia-toggle" data-tip="Consumo de Energia" title="Consumo de Energia" data-action="toggle-energia-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></button>' +
          '<button type="button" class="btn btn-icon-xs btn-cyan" data-tip="Observacao de Montagem" data-action="editar-obs-item" data-item-id="' + item.id + '" data-nome="' + escapeHtml(nomeProduto).replace(/'/g, "\\'") + '" data-obs="' + escapeHtml(item.observacao_montagem || '').replace(/'/g, "\\'") + '" title="Observacao de Montagem"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>' +
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
          '<div class="fi-prefix-group"><span class="fi-prefix">R$</span><input type="text" class="fi fi-sm valor-item-input" value="' + formatBrl(valor) + '" data-item-id="' + item.id + '" oninput="if(window.mascaraMoeda)mascaraMoeda(this)"></div>' +
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
      '</div>' +
      '<div class="sala-item-fields sala-item-energia" style="' + energiaRowStyle + '" data-energia>' +
        '<div class="sala-field-group">' +
          '<label class="sala-field-label">Potência (W)</label>' +
          '<div class="fi-prefix-group"><span class="fi-prefix">W</span><input type="number" class="fi fi-sm potencia-w-input" min="0" step="1" value="' + (potW > 0 ? potW : '') + '" placeholder="0" data-item-id="' + item.id + '"></div>' +
        '</div>' +
        '<div class="sala-field-group">' +
          '<label class="sala-field-label">Horas de Uso</label>' +
          '<div class="fi-prefix-group"><span class="fi-prefix">h</span><input type="number" class="fi fi-sm horas-uso-input" min="0" step="0.5" value="' + horasU + '" data-item-id="' + item.id + '"></div>' +
        '</div>' +
        '<div class="sala-field-group">' +
          '<label class="sala-field-label">Consumo (kWh)</label>' +
          '<span class="fi fi-sm energia-kwh-display" style="background:rgba(6,182,212,0.08);color:var(--neon-cyan);font-weight:600;display:flex;align-items:center;min-width:70px">' + (kwh > 0 ? fmtEnergia(kwh) : '—') + '</span>' +
        '</div>' +
        '<div class="sala-field-group">' +
          '<label class="sala-field-label">kVA</label>' +
          '<span class="fi fi-sm energia-kva-display" style="background:rgba(168,85,247,0.08);color:var(--neon-purple);font-weight:600;display:flex;align-items:center;min-width:70px">' + (kva > 0 ? fmtEnergia(kva) : '—') + '</span>' +
        '</div>' +
      '</div>');

    return div;
  }

  // ==========================================
  // ADICIONAR/ATUALIZAR/DELETAR ITENS (AJAX)
  // ==========================================

  function adicionarItem() {
    const inputItem = document.getElementById('item_proposta');
    const inputQtd = document.getElementById('qtd_item');
    const inputDias = document.getElementById('dias_locacao');
    const inputValor = document.getElementById('valor_item');
    const inputCusto = document.getElementById('valor_custo_fornecedor');
    const inputSala = document.getElementById('id_sala');
    const inputCategoria = document.getElementById('id_categoria');

    if (!inputItem || !inputItem.value.trim()) {
      showToast('red', 'Atencao', 'Nome do produto e obrigatorio');
      return;
    }

    const valorNum = parseBrMoney(inputValor ? inputValor.value : '');
    if (valorNum <= 0) {
      showToast('red', 'Atencao', 'Preencha o Valor Unit. (ex: 100,00)');
      if (inputValor) inputValor.focus();
      return;
    }

    const salaId = inputSala ? inputSala.value || '' : '';
    if (!salaId) {
      if (inputSala && inputSala.options.length <= 1) {
        showToast('red', 'Atencao', 'Nenhuma Sala cadastrada. Clique em "+ Salas" para criar uma sala antes de adicionar itens.');
      } else {
        showToast('red', 'Atencao', 'Por favor, selecione uma Sala para adicionar o item.');
      }
      if (inputSala) inputSala.focus();
      return;
    }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('item', inputItem.value);
    formData.append('quantidade', parseInt(inputQtd ? inputQtd.value : '1') || 1);
    formData.append('dias', parseInt(inputDias ? inputDias.value : '1') || 1);
    formData.append('valor_unit', valorNum);
    formData.append('custo_unit', parseBrMoney(inputCusto ? inputCusto.value : ''));
    formData.append('id_sala', salaId);
    formData.append('id_categoria', inputCategoria ? inputCategoria.value || '' : '');
    const hiddenPlanilhaId = document.getElementById('planilha_id_hidden');
    if (hiddenPlanilhaId && hiddenPlanilhaId.value) {
      formData.append('id_planilha', hiddenPlanilhaId.value);
    }

    fetch(BASE_URL + '/eventos/itens/adicionar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin',
    })
    .then(r => {
      return r.json().then(data => {
        if (!r.ok) {
          throw new Error(data.error || ('HTTP ' + r.status));
        }
        return data;
      });
    })
    .then(data => {
      if ((data.ok || data.success) && data.item) {
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
    .catch(err => showToast('red', 'Erro', 'Erro ao adicionar item: ' + err.message));
  }

  function salvarAlteracaoItem(itemId, campo, valor) {
    const itemState = state.items.find(it => it.id === itemId);
    if (itemState) {
      if (campo === 'quantidade') itemState.quantidade = parseFloat(valor) || 0;
      else if (campo === 'valor_unit') itemState.valor_unit = parseFloat(valor) || 0;
      else if (campo === 'custo_unit') itemState.custo_unit = parseFloat(valor) || 0;
      else if (campo === 'dias_locacao') itemState.dias = parseInt(valor) || 1;
      state.dirty = true;
    }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append(campo, valor);

    fetch(BASE_URL + '/eventos/itens/' + itemId + '/atualizar-campo', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (!data.ok && !data.success) {
        showToast('yellow', 'Atencao', 'Alteracao aplicada na pagina mas nao foi salva no servidor', 2500);
      }
    })
    .catch(err => {
    });
  }

  function excluirItem(itemId) {
    if (!confirm('Tem certeza que deseja excluir este item?')) return;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('item_id', itemId);

    fetch(BASE_URL + '/eventos/itens/excluir', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin',
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (data.ok || data.success) {
        const itemDiv = document.querySelector('.sala-item[data-item-id="' + itemId + '"]');
        let salaBloco = itemDiv ? itemDiv.closest('.sala-bloco') : null;
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

        state.items = state.items.filter(it => it.id !== itemId);
        state.dirty = true;

        showToast('green', 'Sucesso', 'Item removido');
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover item');
      }
    })
    .catch(err => showToast('red', 'Erro', 'Erro ao remover item'));
  }

  // ==========================================
  // PLANILHA E AUTOCOMPLETE
  // ==========================================

  let _planilhaItens = [];

  function loadPlanilha() {
    if (_planilhaItens.length > 0) return;
    fetch(BASE_URL + '/api/eventos/itens', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.itens) {
        _planilhaItens = data.itens;
      }
    })
    .catch(err => console.error('[salas-produtos] loadPlanilha erro', err));
  }

  function setupAutocomplete() {
    const input = document.getElementById('item_proposta');
    if (!input) return;

    // Criar dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'autocomplete-dropdown';
    dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;max-height:280px;overflow-y:auto;background:var(--bg-card);border:1px solid var(--bg-border-sub);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:999;display:none;font-size:13px';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(dropdown);

    function normalizar(s) {
      return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\n/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function filtrar(termo) {
      if (!termo || termo.length < 2) { dropdown.style.display = 'none'; return []; }
      const nt = normalizar(termo);
      return _planilhaItens.filter(item => normalizar(item.item).indexOf(nt) !== -1).slice(0, 15);
    }

    let _dropdownItens = [];
    let selectedIdx = -1;

    function renderDropdown(itens, termo) {
      if (!itens.length) { dropdown.style.display = 'none'; return; }
      _dropdownItens = itens;
      const nt = normalizar(termo);
      const htmlArr = itens.map((item, idx) => {
        const nome = item.item;
        const idxMatch = normalizar(nome).indexOf(nt);
        let highlighted = '';
        if (idxMatch !== -1) {
          const before = escapeHtml(nome.slice(0, idxMatch));
          const match = escapeHtml(nome.slice(idxMatch, idxMatch + termo.length));
          const after = escapeHtml(nome.slice(idxMatch + termo.length));
          highlighted = before + '<strong style="background:rgba(59,130,246,0.15);color:var(--neon-cyan)">' + match + '</strong>' + after;
        } else {
          highlighted = escapeHtml(nome);
        }
        const valor = parseFloat(item.valor || 0);
        return '<div class="aci-item" data-idx="' + idx + '" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--bg-border-sub);display:flex;justify-content:space-between;align-items:center;transition:background .1s" onmouseover="this.style.background=\'var(--bg-hover)\'" onmouseout="this.style.background=\'\'">' +
          '<span style="flex:1;line-height:1.3">' + highlighted + '</span>' +
          '<span style="margin-left:16px;font-weight:600;color:var(--neon-green);white-space:nowrap;font-size:12px">R$ ' + valor.toFixed(2) + '</span>' +
          '</div>';
      });
      window.renderHTML(dropdown, htmlArr.join(''));
      dropdown.style.display = 'block';
    }

    function selecionar(idx) {
      const item = _dropdownItens[idx];
      if (!item) return;
      input.value = item.item;
      dropdown.style.display = 'none';
      let hid = document.getElementById('planilha_id_hidden');
      if (!hid) {
        hid = document.createElement('input');
        hid.type = 'hidden';
        hid.id = 'planilha_id_hidden';
        hid.name = 'planilha_id_hidden';
        input.parentNode.insertBefore(hid, input.nextSibling);
      }
      hid.value = item.id || '';
      const valorEl = document.getElementById('valor_item');
      if (valorEl && item.valor) valorEl.value = formatBrl(parseFloat(item.valor));
    }

    input.addEventListener('input', function() {
      const itens = filtrar(this.value);
      renderDropdown(itens, this.value);
      selectedIdx = -1;
    });

    input.addEventListener('keydown', e => {
      const items = dropdown.querySelectorAll('.aci-item');
      if (!items.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (selectedIdx < items.length - 1) selectedIdx++;
        items.forEach((el, i) => el.style.background = i === selectedIdx ? 'var(--bg-hover)' : '');
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (selectedIdx > 0) selectedIdx--;
        items.forEach((el, i) => el.style.background = i === selectedIdx ? 'var(--bg-hover)' : '');
      } else if (e.key === 'Enter' && selectedIdx >= 0) {
        e.preventDefault();
        selecionar(selectedIdx);
      } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
      }
    });

    input.addEventListener('blur', () => {
      setTimeout(() => { dropdown.style.display = 'none'; }, 200);
    });

    dropdown.addEventListener('mousedown', e => {
      const item = e.target.closest('.aci-item');
      if (item) {
        const idx = parseInt(item.dataset.idx);
        selecionar(idx);
      }
    });
  }

  // ==========================================
  // SALAS (MODAL)
  // ==========================================

  window.salvarSala = function() {
    const elNome = document.getElementById('nome_sala');
    const elObs = document.getElementById('observacao_sala');
    const elEditId = document.getElementById('editando_sala_id');
    if (!elNome || !elEditId) return;
    const nomeSala = elNome.value.trim();
    if (!nomeSala) { showToast('red', 'Erro', 'Nome da sala e obrigatorio'); return; }

    const editandoId = elEditId.value;
    const isEdit = editandoId !== '';

    const url = isEdit ? BASE_URL + '/api/eventos/salas/' + editandoId : BASE_URL + '/api/eventos/salas';
    const method = isEdit ? 'PUT' : 'POST';
    const obsVal = elObs ? elObs.value : '';
    let body = '_csrf_token=' + CSRF_TOKEN + '&nome_sala=' + encodeURIComponent(nomeSala) + '&observacao_montagem=' + encodeURIComponent(obsVal);
    if (!isEdit) body += '&evento_id=' + encodeURIComponent(EVENTO_ID);

    fetch(url, { method: method, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (data.ok) {
        showToast('green', 'Sucesso', isEdit ? 'Sala atualizada' : 'Sala criada');
        cancelarEdicaoSala();
        atualizarListaSalasModal();
        atualizarSelectSalas();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao salvar sala');
      }
    })
    .catch(err => showToast('red', 'Erro', 'Erro ao salvar sala'));
  };

  function atualizarListaSalasModal() {
    fetch(BASE_URL + '/api/eventos/salas?evento_id=' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(salas => {
      if (salas.success) {
        const container = document.getElementById('modal-sala-list');
        if (!container) return;
        let html = '<table style="width:100%;border-collapse:collapse">';
        html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Obs. Montagem</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
        html += '</tr></thead><tbody>';
        if (salas.data.length === 0) {
          html += '<tr><td colspan="3" style="padding:16px;text-align:center;color:var(--text-4)">Nenhuma sala cadastrada</td></tr>';
        } else {
          salas.data.forEach(s => {
            html += '<tr style="border-bottom:1px solid var(--bg-border)">';
            html += '<td style="padding:8px;font-size:13px">' + (s.nome_sala || '') + '</td>';
            html += '<td style="padding:8px;font-size:13px;color:var(--text-3)">' + (s.orientacoes_montagem || '') + '</td>';
            html += '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
            html += '<button type="button" class="btn btn-xs btn-purple" data-action="editar-sala-modal" data-id="' + s.id + '" data-nome="' + (s.nome_sala || '').replace(/'/g, "\\'") + '" data-obs="' + (s.orientacoes_montagem || '').replace(/'/g, "\\'") + '" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
            html += '<button type="button" class="btn btn-xs btn-red" data-action="excluir-sala" data-id="' + s.id + '" data-nome="' + (s.nome_sala || '').replace(/'/g, "\\'") + '" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
            html += '</div></td></tr>';
          });
        }
        html += '</tbody></table>';
        window.renderHTML(container, html);
      }
    })
    .catch(() => {});
  }

  function atualizarSelectSalas() {
    fetch(BASE_URL + '/api/eventos/salas?evento_id=' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(salas => {
      if (salas.success) {
        const select = document.getElementById('id_sala');
        if (!select) return;
        const valorAnterior = select.value;
        window.renderHTML(select, '<option value="">Sem Sala</option>');
        salas.data.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.nome_sala;
          select.appendChild(opt);
        });
        select.value = valorAnterior;
      }
    })
    .catch(() => {});
  }

  window.editarSala = function(id, nome, obs) {
    openModal('modalSala');
    editarSalaModal(id, nome, obs);
  };

  window.editarSalaModal = function(id, nome, obs) {
    const elId = document.getElementById('editando_sala_id');
    const elNome = document.getElementById('nome_sala');
    const elObs = document.getElementById('observacao_sala');
    const elBtn = document.getElementById('btnSalvarSalaText');
    const elCancel = document.getElementById('btnCancelarEdicaoSala');
    if (!elId || !elNome || !elBtn) return;
    elId.value = id;
    elNome.value = nome || '';
    if (elObs) elObs.value = obs || '';
    elBtn.textContent = 'Atualizar Sala';
    if (elCancel) elCancel.style.display = 'inline-flex';
    elNome.focus();
  };

  window.cancelarEdicaoSala = function() {
    const elId = document.getElementById('editando_sala_id');
    const elNome = document.getElementById('nome_sala');
    const elObs = document.getElementById('observacao_sala');
    const elBtn = document.getElementById('btnSalvarSalaText');
    const elCancel = document.getElementById('btnCancelarEdicaoSala');
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
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        showToast('green', 'Sucesso', 'Sala excluida');
        atualizarListaSalasModal();
        atualizarSelectSalas();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir sala');
      }
    })
    .catch(err => showToast('red', 'Erro', 'Erro ao excluir sala'));
  };

  // ==========================================
  // CATEGORIAS (MODAL)
  // ==========================================

  window.editarCategoriaModal = function(id, nome) {
    const elId = document.getElementById('editando_categoria_id');
    const elNome = document.getElementById('nome_categoria');
    const elBtn = document.getElementById('btnSalvarCategoriaText');
    const elCancel = document.getElementById('btnCancelarEdicaoCategoria');
    if (!elId || !elNome || !elBtn) return;
    elId.value = id;
    elNome.value = nome || '';
    elBtn.textContent = 'Atualizar Categoria';
    if (elCancel) elCancel.style.display = 'inline-flex';
    elNome.focus();
  };

  window.cancelarEdicaoCategoria = function() {
    const elId = document.getElementById('editando_categoria_id');
    const elNome = document.getElementById('nome_categoria');
    const elBtn = document.getElementById('btnSalvarCategoriaText');
    const elCancel = document.getElementById('btnCancelarEdicaoCategoria');
    if (elId) elId.value = '';
    if (elNome) elNome.value = '';
    if (elBtn) elBtn.textContent = 'Salvar Categoria';
    if (elCancel) elCancel.style.display = 'none';
  };

  window.salvarCategoria = function() {
    const elNome = document.getElementById('nome_categoria');
    const elEditId = document.getElementById('editando_categoria_id');
    if (!elNome || !elEditId) return;
    const nome = elNome.value.trim();
    if (!nome) { showToast('red', 'Erro', 'Nome da categoria e obrigatorio'); return; }

    const editandoId = elEditId.value;
    const isEdit = editandoId !== '';

    const url = isEdit ? BASE_URL + '/categorias-sala/update/' + editandoId : BASE_URL + '/categorias-sala/store';
    const body = '_csrf_token=' + CSRF_TOKEN + '&nome=' + encodeURIComponent(nome);

    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, credentials: 'same-origin' })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (data.ok || data.success) {
        showToast('green', 'Sucesso', isEdit ? 'Categoria atualizada' : 'Categoria criada');
        cancelarEdicaoCategoria();
        atualizarListaCategoriasModal();
        atualizarSelectCategorias();
      } else {
        showToast('red', 'Erro', data.error || (isEdit ? 'Erro ao atualizar categoria' : 'Erro ao criar categoria'));
      }
    })
    .catch(err => showToast('red', 'Erro', 'Erro de conexao'));
  };

  function atualizarListaCategoriasModal() {
    fetch(BASE_URL + '/api/categorias', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
      if (res.ok && res.data) {
        const container = document.getElementById('modal-categoria-list');
        if (!container) return;
        let html = '<table style="width:100%;border-collapse:collapse">';
        html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
        html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
        html += '</tr></thead><tbody>';
        if (res.data.length === 0) {
          html += '<tr><td colspan="2" style="padding:16px;text-align:center;color:var(--text-4)">Nenhuma categoria cadastrada</td></tr>';
        } else {
          res.data.forEach(c => {
            html += '<tr style="border-bottom:1px solid var(--bg-border)">';
            html += '<td style="padding:8px;font-size:13px">' + (c.nome_categoria || '') + '</td>';
            html += '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
            html += '<button type="button" class="btn btn-xs btn-purple" data-action="editar-categoria-modal" data-id="' + c.id + '" data-nome="' + (c.nome_categoria || '').replace(/'/g, "\\'") + '" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
            html += '<button type="button" class="btn btn-xs btn-red" data-action="excluir-categoria" data-id="' + c.id + '" data-nome="' + (c.nome_categoria || '').replace(/'/g, "\\'") + '" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
            html += '</div></td></tr>';
          });
        }
        html += '</tbody></table>';
        window.renderHTML(container, html);
      }
    })
    .catch(() => {});
  }

  function atualizarSelectCategorias() {
    fetch(BASE_URL + '/api/categorias', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
      if (res.ok && res.data) {
        const select = document.getElementById('id_categoria');
        if (!select) return;
        const valorAnterior = select.value;
        window.renderHTML(select, '<option value="">Selecione...</option>');
        res.data.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.id;
          opt.textContent = c.nome_categoria;
          select.appendChild(opt);
        });
        select.value = valorAnterior;
      }
    })
    .catch(() => {});
  };

  window.excluirCategoria = function(id, nome) {
    if (!confirm('Excluir a categoria "' + nome + '"? Esta acao nao pode ser desfeita.')) return;
    fetch(BASE_URL + '/categorias-sala/delete/' + id, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: '_csrf_token=' + CSRF_TOKEN,
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        showToast('green', 'Sucesso', 'Categoria excluida');
        atualizarListaCategoriasModal();
        atualizarSelectCategorias();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao excluir categoria');
      }
    })
    .catch(err => showToast('red', 'Erro', 'Erro ao excluir categoria'));
  };

  // ==========================================
  // OBSERVACAO DE MONTAGEM (INLINE SEM RELOAD)
  // ==========================================

  let currentItemObsId = null;

  window.editarObsItem = function(itemId, itemName, obs) {
    currentItemObsId = itemId;
    const elName = document.getElementById('modal-item-name');
    const elObs = document.getElementById('observacao_item');
    if (elName) elName.textContent = itemName;
    if (elObs) elObs.value = obs || '';
    const modal = document.getElementById('modal-item-obs');
    if (modal) { modal.style.display = 'flex'; modal.classList.add('open'); }
  };

  window.fecharModalItemObs = function() {
    const modal = document.getElementById('modal-item-obs');
    if (modal) { modal.style.display = 'none'; modal.classList.remove('open'); }
    currentItemObsId = null;
  };

  window.salvarObsItem = function() {
    if (!currentItemObsId) {
      showToast('red', 'Erro', 'ID do item nao identificado');
      return;
    }
    const elObs = document.getElementById('observacao_item');
    const obs = elObs ? elObs.value.trim() : '';
    const itemId = currentItemObsId;

    const itemState = state.items.find(it => it.id === itemId);
    if (itemState) {
      itemState.observacao_montagem = obs;
      state.dirty = true;
    }

    const itemDiv = document.querySelector('.sala-item[data-item-id="' + itemId + '"]');
    if (itemDiv) {
      const itemInfo = itemDiv.querySelector('.sala-item-info');
      if (itemInfo) {
        const existingBadge = itemInfo.querySelector('.sala-item-obs-badge');
        if (existingBadge) existingBadge.remove();

        if (obs) {
          const itemName = itemInfo.querySelector('.sala-item-nome');
          if (itemName) {
            const badge = document.createElement('span');
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

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('observacao_montagem', obs);

    fetch(BASE_URL + '/api/eventos/itens/' + itemId + '/observacao', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
    })
    .catch(err => {
    });
  };

  window.getSalasState = function() { return state; };
  window.resetSalasState = function() { state.dirty = false; };

  // ==========================================
  // INICIALIZACAO E EVENT LISTENERS
  // ==========================================

  // Toggle linha de energia por item (global — chamado via onclick)
  window.toggleEnergiaItem = function(btn) {
    const item = btn.closest('.sala-item[data-item-id]');
    if (!item) return;
    const linha = item.querySelector('[data-energia]');
    if (!linha) return;
    const visivel = linha.style.display !== 'none';
    linha.style.display = visivel ? 'none' : '';
    btn.classList.toggle('btn-yellow', !visivel);
    btn.classList.toggle('btn-outline', visivel);
    if (!visivel) {
      const inp = linha.querySelector('.potencia-w-input');
      if (inp) inp.focus();
    }
  };

  function init() {
    loadPlanilha();
    setupAutocomplete();

    const btnAdicionar = document.getElementById('btn-adicionar-item');
    if (btnAdicionar) btnAdicionar.addEventListener('click', adicionarItem);

    document.addEventListener('input', e => {
      if (e.target.matches('.quantidade-item-input') ||
          e.target.matches('.valor-item-input') ||
          e.target.matches('.dias-locacao-input') ||
          e.target.matches('.custo-fornecedor') ||
          e.target.matches('.potencia-w-input') ||
          e.target.matches('.horas-uso-input')) {

        const div = e.target.closest('.sala-item[data-item-id]');
        if (div) {
          calcularItem(div);

          const itemId = e.target.dataset.itemId;
          let campo = '';
          if (e.target.matches('.quantidade-item-input')) campo = 'quantidade';
          else if (e.target.matches('.valor-item-input')) campo = 'valor_unit';
          else if (e.target.matches('.dias-locacao-input')) campo = 'dias';
          else if (e.target.matches('.custo-fornecedor')) campo = 'custo_unit';
          else if (e.target.matches('.potencia-w-input')) campo = 'potencia_w';
          else if (e.target.matches('.horas-uso-input')) campo = 'horas_uso';

          if (campo && itemId) {
            const valor = e.target.matches('.valor-item-input') || e.target.matches('.custo-fornecedor')
              ? parseBrMoney(e.target.value)
              : e.target.value;

            debounce(() => {
              salvarAlteracaoItem(itemId, campo, valor);
            }, 500, 'item-' + itemId + '-' + campo);
          }
        }
      }
    });

    document.addEventListener('click', e => {
      const btn = e.target.closest('[data-delete-item]');
      if (btn) excluirItem(parseInt(btn.dataset.deleteItem));
    });

    // Inicializa totais de energia ao carregar
    calcularTotal();

    ['valor_item', 'valor_custo_fornecedor'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('blur', function() { formatCurrencyInput(this); });
        el.addEventListener('focus', function() { stripCurrencyOnFocus(this); });
      }
    });

    function calcularDiasEvento() {
      const inputInicio = document.getElementById('data_inicio');
      const inputFim = document.getElementById('data_fim');
      const inputDias = document.getElementById('dias_locacao');
      if (!inputInicio || !inputFim || !inputDias) return;

      const inicio = inputInicio.value;
      const fim = inputFim.value;
      if (inicio && fim) {
        const dtInicio = new Date(inicio + 'T00:00:00');
        const dtFim = new Date(fim + 'T00:00:00');
        if (dtFim >= dtInicio) {
          const diffDias = Math.floor((dtFim - dtInicio) / (1000 * 60 * 60 * 24)) + 1;
          inputDias.value = diffDias;
          document.querySelectorAll('.sala-item[data-item-id] .dias-locacao-input').forEach(input => {
            input.value = diffDias;
            const div = input.closest('.sala-item');
            if (div) calcularItem(div);
          });
        }
      }
    }

    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');
    if (inputInicio) inputInicio.addEventListener('change', calcularDiasEvento);
    if (inputFim) inputFim.addEventListener('change', calcularDiasEvento);

    const modalSalaEl = document.getElementById('modalSala');
    if (modalSalaEl) {
      new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            if (!modalSalaEl.classList.contains('open')) atualizarListaSalasModal();
          }
        });
      }).observe(modalSalaEl, { attributes: true });
    }

    const modalCategoriaEl = document.getElementById('modalCategoria');
    if (modalCategoriaEl) {
      new MutationObserver(mutations => {
        mutations.forEach(mutation => {
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

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel.
    if (window.registerAction) {
      window.registerAction('editar-sala', function(el) {
        window.editarSala(el.dataset.id, el.dataset.nome, el.dataset.obs);
      });
      window.registerAction('editar-sala-modal', function(el) {
        window.editarSalaModal(el.dataset.id, el.dataset.nome, el.dataset.obs);
      });
      window.registerAction('excluir-sala', function(el) {
        window.excluirSala(el.dataset.id, el.dataset.nome);
      });
      window.registerAction('editar-categoria-modal', function(el) {
        window.editarCategoriaModal(el.dataset.id, el.dataset.nome);
      });
      window.registerAction('excluir-categoria', function(el) {
        window.excluirCategoria(el.dataset.id, el.dataset.nome);
      });
      window.registerAction('editar-obs-item', function(el) {
        window.editarObsItem(el.dataset.itemId, el.dataset.nome, el.dataset.obs);
      });
    }
  });
})();

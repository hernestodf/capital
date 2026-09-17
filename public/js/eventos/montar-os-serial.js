(function() {
  'use strict';
  const EVENTO_ID = window.EVENTO_ID;
  const CSRF_TOKEN = window.CSRF_TOKEN;
  const BASE_URL = window.BASE_URL;

  let serialInput = null;
  let btnInserir = null;

  function getSalasMap() { return window.SALAS_MAP || {}; }

  function maskProduto(name) {
    return (name || '').replace(/\s*-\s*\d+$/, '');
  }

  function buildAlert(variant, title, message) {
    const colors = {
      'red': { bg: 'rgba(244,63,94,0.08)', border: 'var(--neon-red)', color: 'var(--neon-red)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
      'green': { bg: 'rgba(34,197,94,0.08)', border: 'var(--neon-green)', color: 'var(--neon-green)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
      'yellow': { bg: 'rgba(245,158,11,0.08)', border: 'var(--neon-yellow)', color: 'var(--neon-yellow)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.337-2.5L13.732 4c-.665-.932-2.075-.932-2.74 0L4.07 16.5c-1.165.833-.204 2.5 1.337 2.5z"/>' }
    };
    const c = colors[variant] || colors['red'];
    return '<div style="background:' + c.bg + ';border-left:3px solid ' + c.border + ';padding:10px 14px;border-radius:8px">' +
      '<div style="font-weight:600;color:' + c.color + ';font-size:13px">' + title + '</div>' +
      '<div style="font-size:12px;color:var(--text-2);margin-top:2px">' + escapeHtml(message) + '</div></div>';
  }

  function buildEmptyState(title, subtitle) {
    return '<div class="table-empty"><div class="table-empty-flex">' +
      '<svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
      '<div>' + escapeHtml(title || 'Nenhum item') + '</div>' +
      '<div style="font-size:12px;color:var(--text-4)">' + escapeHtml(subtitle || '') + '</div>' +
      '</div></div>';
  }

  function buildSalaOptions() {
    const SALAS_MAP = getSalasMap();
    let html = '<option value="">Selecione a sala</option>';
    for (let salaId in SALAS_MAP) {
      if (SALAS_MAP.hasOwnProperty(salaId)) {
        const sala = SALAS_MAP[salaId];
        html += '<option value="' + salaId + '">' + escapeHtml(sala.nome) + '</option>';
      }
    }
    return html;
  }

  function getNomeProdutoById(produtoEventoId) {
    const SALAS_MAP = getSalasMap();
    for (let salaId in SALAS_MAP) {
      if (SALAS_MAP.hasOwnProperty(salaId) && SALAS_MAP[salaId].produtos) {
        const produtos = SALAS_MAP[salaId].produtos;
        for (let i = 0; i < produtos.length; i++) {
          if (parseInt(produtos[i].id) === parseInt(produtoEventoId)) {
            return produtos[i].produto || '';
          }
        }
      }
    }
    return '';
  }

  function buildSalaItemHtml(serial, obsItem, idMontagem) {
    return '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px">' +
      '<div style="flex:1;min-width:0">' +
      '<span style="font-weight:700;font-size:13px;color:var(--text-1)">' + escapeHtml(serial) + '</span>' +
      '<span style="font-size:12px;color:var(--text-3);margin-left:8px">' + escapeHtml(maskProduto(obsItem) || '-') + '</span>' +
      '</div>' +
      '<div style="display:flex;gap:4px;flex-shrink:0">' +
      '<button type="button" class="btn btn-sm btn-yellow" data-action="remover-da-sala" data-id-montagem="' + idMontagem + '" title="Voltar para pendentes">Pendente</button>' +
      '<button type="button" class="btn btn-sm btn-red" data-action="remover-do-evento" data-id-montagem="' + idMontagem + '" title="Remover do evento">Remover</button>' +
      '</div></div>';
  }

  function carregarSalasDoServidor(callback) {
    fetch(BASE_URL + '/api/eventos/salas/' + EVENTO_ID, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success && data.data) {
        const novoSalasMap = {};
        data.data.forEach((sala) => {
          novoSalasMap[sala.id] = {
            nome: sala.nome_sala || sala.nome,
            obs: sala.orientacoes_montagem || '',
            produtos: sala.produtos || []
          };
        });
        window.SALAS_MAP = novoSalasMap;
        if (callback) callback(window.SALAS_MAP);
      } else {
        if (callback) callback(getSalasMap());
      }
    })
    .catch((err) => {
      if (callback) callback(getSalasMap());
    });
  }

  function initSerialInputs() {
    serialInput = document.getElementById('serial-codigo-evento');
    btnInserir = document.getElementById('btn-inserir-serial');

    if (!serialInput || !btnInserir) {
      return;
    }

    const newBtn = btnInserir.cloneNode(true);
    const newInput = serialInput.cloneNode(true);
    btnInserir.parentNode.replaceChild(newBtn, btnInserir);
    serialInput.parentNode.replaceChild(newInput, serialInput);
    btnInserir = newBtn;
    serialInput = newInput;

    btnInserir.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      enviarSerial();
    });

    serialInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        enviarSerial();
      }
    });

    serialInput.addEventListener('input', () => {
    });

    let btnLote = document.getElementById('btn-inserir-lote');
    if (btnLote) {
      const newBtnLote = btnLote.cloneNode(true);
      btnLote.parentNode.replaceChild(newBtnLote, btnLote);
      btnLote = newBtnLote;
      btnLote.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        enviarLote();
      });
    }

    const formLote = document.getElementById('form-lote-evento');
    if (formLote) {
      formLote.addEventListener('submit', (e) => {
        e.preventDefault();
        e.stopPropagation();
        return false;
      });
    }

    serialInput.focus();
  }

  function atualizarItensDaSala(salaSelect) {
    const SALAS_MAP = getSalasMap();
    const salaId = parseInt(salaSelect.value);
    const montagemId = salaSelect.getAttribute('data-montagem-id');

    if (!salaId) {
      return;
    }

    const row = document.querySelector('tr[data-id="' + montagemId + '"]');
    if (!row) {
      return;
    }

    const itemSelect = row.querySelector('.item-destino-select');
    const qtdInput = row.querySelector('.qtd-necessaria');

    if (!itemSelect) {
      return;
    }

    const sala = SALAS_MAP[salaId];

    window.renderHTML(itemSelect, '<option value="">Selecione o item</option>');

    if (!sala || !sala.produtos || sala.produtos.length === 0) {
      itemSelect.style.display = 'none';
      if (qtdInput) qtdInput.style.display = 'none';
      return;
    }

    sala.produtos.forEach((produto) => {
      const qtdNecessaria = parseInt(produto.qtd) || 0;
      const option = document.createElement('option');
      option.value = produto.id;
      option.textContent = produto.produto + ' (Qtd: ' + qtdNecessaria + ')';
      option.setAttribute('data-qtd', qtdNecessaria);
      itemSelect.appendChild(option);
    });

    itemSelect.style.display = 'inline-block';

  }

  function enviarSerial() {
    const serial = serialInput ? serialInput.value.trim() : '';

    if (!serial) {
      showToast('yellow', 'Atencao', 'Digite um serial');
      return;
    }

    const infoDiv = document.getElementById('serial-info-evento');
    btnInserir.disabled = true;
    if (serialInput) serialInput.disabled = true;
    btnInserir.textContent = 'Processando...';
    if (infoDiv) {
      window.renderHTML(infoDiv, '<div style="text-align:center;padding:8px;color:var(--text-3)">Processando...</div>');
    }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('serial', serial);

    fetch(BASE_URL + '/montagem/inserir-serial', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then((data) => {
      if (data.success && data.data) {
        showToast('green', 'Sucesso', data.message || 'Serial incluido!');
        adicionarRowPendentes(data.data.id, data.data.produto, data.data.serial);
        serialInput.value = '';
        if (infoDiv) infoDiv.textContent = '';
        carregarSalasDoServidor();
      } else {
        if (infoDiv) {
          window.renderHTML(infoDiv, buildAlert('red', 'Erro', data.error || 'Erro ao incluir'));
        }
        showToast('red', 'Erro', data.error || 'Erro ao incluir');
      }
    })
    .catch((err) => {
      if (infoDiv) {
        window.renderHTML(infoDiv, buildAlert('red', 'Erro', 'Erro: ' + err.message));
      }
      showToast('red', 'Erro', 'Erro: ' + err.message);
    })
    .finally(() => {
      btnInserir.disabled = false;
      if (serialInput) serialInput.disabled = false;
      btnInserir.textContent = 'Inserir Serial';
      setTimeout(() => {
        serialInput.focus();
      }, 50);
    });
  }

  function enviarLote() {
    const seriaisInput = document.getElementById('lote-seriais-evento');

    const seriais = seriaisInput ? seriaisInput.value.trim() : '';

    if (!seriais) {
      showToast('yellow', 'Atencao', 'Insira seriais');
      return;
    }

    const resultadoDiv = document.getElementById('lote-resultado-evento');

    if (resultadoDiv) {
      window.renderHTML(resultadoDiv, '<div style="text-align:center;padding:8px;color:var(--text-3)">Processando...</div>');
    }

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('seriais', seriais);

    fetch(BASE_URL + '/montagem/inserir-lote', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => {
      return r.json();
    })
    .then((data) => {
      if (data.success) {
        let msg = data.message || 'Lote processado!';
        if (data.detalhes) msg += ' | Sucesso: ' + data.detalhes.sucesso + ' | Erros: ' + data.detalhes.erros;
        if (resultadoDiv) {
          window.renderHTML(resultadoDiv, buildAlert('green', 'Sucesso', msg));
        }
        showToast('green', 'Sucesso', 'Lote processado!');
        if (seriaisInput) seriaisInput.value = '';

        if (data.seriais_inseridos && data.seriais_inseridos.length > 0) {
          for (let i = 0; i < data.seriais_inseridos.length; i++) {
            const item = data.seriais_inseridos[i];
            adicionarRowPendentes(item.id, item.produto, item.serial);
          }
        }
        carregarSalasDoServidor();
      } else {
        if (resultadoDiv) {
          window.renderHTML(resultadoDiv, buildAlert('red', 'Erro', data.error || 'Erro ao processar lote'));
        }
        showToast('red', 'Erro', data.error || 'Erro ao processar lote');
      }
    })
    .catch((err) => {
      if (resultadoDiv) {
        window.renderHTML(resultadoDiv, buildAlert('red', 'Erro', 'Erro de conexao'));
      }
      showToast('red', 'Erro', 'Erro ao processar');
    });
  }

  function encaminharParaSala(montagemId, btn) {
    const row = document.querySelector('tr[data-id="' + montagemId + '"]');
    if (!row) {
      showToast('red', 'Erro', 'Linha não encontrada');
      return;
    }

    const salaSelect = row.querySelector('.sala-destino-select');
    const itemSelect = row.querySelector('.item-destino-select');

    const salaId = parseInt(salaSelect ? salaSelect.value : 0);
    const produtoEventoId = parseInt(itemSelect ? itemSelect.value : 0);

    if (!salaId) {
      showToast('yellow', 'Aviso', 'Selecione uma sala');
      return;
    }

    if (!produtoEventoId) {
      showToast('yellow', 'Aviso', 'Selecione um item');
      return;
    }

    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('id_sala', salaId);
    formData.append('id_produto_evento', produtoEventoId);
    formData.append('qtd_necessaria', 1);
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/encaminhar-sala/' + montagemId, {
      method: 'POST',
      body: formData
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial encaminhado!');

        const tds = row.querySelectorAll('td');
        const serial = tds[1] ? tds[1].textContent.trim() : '';

        const produto = getNomeProdutoById(produtoEventoId);

        row.style.opacity = '0';
        row.style.transition = 'opacity 0.3s';
        setTimeout(() => {
          row.remove();

          const tbody = document.querySelector('#tbl-pendentes tbody');
          if (tbody) {
            const count = tbody.querySelectorAll('tr').length;
            if (count === 0) {
              const card = document.getElementById('tbl-pendentes').closest('.card');
              if (card) {
                const cardBody = card.querySelector('.card-body');
                if (cardBody) {
                  window.renderHTML(cardBody, buildEmptyState('Nenhum serial pendente', 'Todos os seriais foram encaminhados'));
                }
              }
            }
            atualizarBadgePendentes(count);
          }

          adicionarSerialAoItem(salaId, produtoEventoId, serial, produto, montagemId);
        }, 300);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao encaminhar');
        if (btn) btn.disabled = false;
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro na requisição');
      if (btn) btn.disabled = false;
    });
  }

  function removerDaSala(idMontagem, btn) {
    if (!confirm('Remover este serial da sala? Ele voltara para a lista de pendentes.')) return;

    const montagemSection = document.getElementById('montagem-salas-container');
    const salaItem = montagemSection ? montagemSection.querySelector('.sala-item[data-montagem-id="' + idMontagem + '"]') : null;

    if (!salaItem) {
      showToast('red', 'Erro', 'Item nao encontrado na sala');
      return;
    }

    const spans = salaItem.querySelectorAll('span');
    const serial = spans[0] ? spans[0].textContent.trim() : '';
    const produto = spans[1] ? spans[1].textContent.trim() : '';

    btn.disabled = true;
    window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>');

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/remover-da-sala/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial voltou para pendentes!');
        const salaCard = salaItem.closest ? salaItem.closest('.sala-bloco') : document.querySelector('#montagem-salas-container .sala-bloco[data-sala-id]');
        salaItem.style.opacity = '0';
        salaItem.style.transition = 'opacity 0.3s';
        setTimeout(() => {
          salaItem.remove();
          if (salaCard) {
            const itensWrap = salaCard.querySelector('.sala-itens-wrap');
            const itemsCount = itensWrap ? itensWrap.querySelectorAll('.sala-item').length : 0;
            if (itemsCount === 0) {
                  window.renderHTML(itensWrap, '<div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">Nenhum serial nesta sala</div>');
            }
            const badge = salaCard.querySelector('.sala-header .badge');
            if (badge) badge.textContent = itemsCount + ' item(s)';
          }
          if (data.data) {
            adicionarRowPendentes(data.data.id, data.data.produto, data.data.serial);
          } else {
            adicionarRowPendentes(idMontagem, produto, serial);
          }
        }, 300);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover da sala');
        btn.disabled = false;
        window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 01-8 8H3m0 0h3m-3 0v3m0-3V10m12-4a2 2 0 012 2v0a2 2 0 01-2 2h0a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>');
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro ao processar: ' + err.message);
      btn.disabled = false;
      window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 01-8 8H3m0 0h3m-3 0v3m0-3V10m12-4a2 2 0 012 2v0a2 2 0 01-2 2h0a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>');
    });
  }

  function removerDoEvento(idMontagem, btn) {
    if (!confirm('Tem certeza que deseja remover este serial do evento?')) return;

    const montagemSection = document.getElementById('montagem-salas-container');
    const salaItem = montagemSection ? montagemSection.querySelector('.sala-item[data-montagem-id="' + idMontagem + '"]') : null;

    let serial = '';
    let produto = '';
    let isPendente = false;

    if (!salaItem) {
      const tblPendentes = document.getElementById('tbl-pendentes');
      if (tblPendentes) {
        const pendenteRow = tblPendentes.querySelector('tr[data-id="' + idMontagem + '"]');
        if (pendenteRow) {
          isPendente = true;
          const tds = pendenteRow.querySelectorAll('td');
          produto = tds[0] ? tds[0].textContent.trim() : '';
          serial = tds[1] ? tds[1].textContent.trim() : '';
        }
      }
    } else {
      const spans = salaItem.querySelectorAll('span');
      serial = spans[0] ? spans[0].textContent.trim() : '';
      produto = spans[1] ? spans[1].textContent.trim() : '';
    }

    if (!salaItem && !isPendente) {
      showToast('red', 'Erro', 'Item nao encontrado');
      return;
    }

    btn.disabled = true;
    window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>');

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/devolver/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial removido do evento!');

        if (isPendente) {
          const tblPendentes = document.getElementById('tbl-pendentes');
          if (tblPendentes) {
            const pendenteRow = tblPendentes.querySelector('tr[data-id="' + idMontagem + '"]');
            if (pendenteRow) {
              const tbody = pendenteRow.closest('tbody');
              pendenteRow.style.opacity = '0';
              pendenteRow.style.transition = 'opacity 0.3s';
              ((row, tb) => {
                setTimeout(() => {
                  row.remove();
                  if (tb && tb.querySelectorAll('tr').length === 0) {
                    const card = tblPendentes.closest('.card');
                    if (card) {
                      const cardBody = card.querySelector('.card-body');
                      if (cardBody) {
                        window.renderHTML(cardBody, buildEmptyState('Nenhum serial pendente', 'Todos os seriais foram encaminhados'));
                      }
                    }
                  }
                  atualizarBadgePendentes(tb ? tb.querySelectorAll('tr').length : 0);
                }, 300);
              })(pendenteRow, tbody);
            }
          }
        } else {
          salaItem.style.opacity = '0';
          salaItem.style.transition = 'opacity 0.3s';
          setTimeout(() => {
            salaItem.remove();
            const allBlocos = document.querySelectorAll('#montagem-salas-container .sala-bloco');
            for (let b = 0; b < allBlocos.length; b++) {
              const wrap = allBlocos[b].querySelector('.sala-itens-wrap');
              if (wrap) {
                const itemsCount = wrap.querySelectorAll('.sala-item').length;
                if (itemsCount === 0) {
                  window.renderHTML(wrap, '<div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">Nenhum serial nesta sala</div>');
                }
                const badge = allBlocos[b].querySelector('.sala-header .badge');
                if (badge) badge.textContent = itemsCount + ' item(s)';
              }
            }
          }, 300);
        }
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
        btn.disabled = false;
        window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>');
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro ao processar: ' + err.message);
      btn.disabled = false;
      window.renderHTML(btn, '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>');
    });
  }

  function adicionarRowPendentes(idMontagem, produto, serial) {
    let tblPendentes = document.getElementById('tbl-pendentes');
    let card = null;

    if (!tblPendentes) {
      const allCards = document.querySelectorAll('.card');
      for (let i = 0; i < allCards.length; i++) {
        const title = allCards[i].querySelector('.card-title');
        const titleText = title ? title.textContent : '';
        if (title && (titleText.indexOf('Pendentes') !== -1 || titleText.indexOf('pendentes') !== -1)) {
          card = allCards[i];
          break;
        }
      }
      if (!card) {
        return;
      }

      const tableHtml = '<div class="tbl-container"><table class="data-table striped hoverable" id="tbl-pendentes"><thead><tr>' +
        '<th>Produto</th><th>Serial</th><th style="min-width:320px">Acao</th>' +
        '</tr></thead><tbody></tbody></table></div>';
      const cardBody = card.querySelector('.card-body');
      if (cardBody) {
        const emptyDiv = cardBody.querySelector('.table-empty');
        if (emptyDiv) {
          emptyDiv.remove();
        }
        window.renderHTML(cardBody, tableHtml);
      }
      tblPendentes = document.getElementById('tbl-pendentes');
    }

    if (!tblPendentes) return;
    const tbody = tblPendentes.querySelector('tbody');
    if (!tbody) return;

    const optionsHtml = buildSalaOptions();

    const tr = document.createElement('tr');
    tr.setAttribute('data-id', idMontagem);
    window.renderHTML(tr, '<td>' + escapeHtml(maskProduto(produto)) + '</td>' +
      '<td>' + escapeHtml(serial) + '</td>' +
      '<td>' +
      '<select class="fi fi-sm sala-destino-select" data-montagem-id="' + idMontagem + '" style="width:180px;display:inline-block;vertical-align:middle">' +
      optionsHtml + '</select>' +
      '<select class="fi fi-sm item-destino-select" data-montagem-id="' + idMontagem + '" style="width:180px;display:none;vertical-align:middle"><option>Selecione o item</option></select>' +
      '<button type="button" class="btn btn-sm btn-cyan btn-encaminhar" style="display:inline-block;vertical-align:middle;margin-left:4px" data-montagem-id="' + idMontagem + '">Encaminhar</button>' +
      '<button type="button" class="btn btn-sm btn-red btn-remover" style="display:inline-block;vertical-align:middle;margin-left:4px" data-montagem-id="' + idMontagem + '" title="Remover do Evento">' +
      '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
      '</button></td>');

    tr.style.opacity = '0';
    tr.style.transform = 'translateY(-8px)';
    tr.style.transition = 'opacity 0.3s, transform 0.3s';
    tbody.appendChild(tr);
    void tr.offsetHeight;
    tr.style.opacity = '1';
    tr.style.transform = 'translateY(0)';

    const novoSelect = tr.querySelector('.sala-destino-select');
    if (novoSelect) {
      novoSelect.addEventListener('change', function() {
        atualizarItensDaSala(this);
      });
    }

    const btnEncaminhar = tr.querySelector('.btn-encaminhar');
    if (btnEncaminhar) {
      btnEncaminhar.addEventListener('click', function() {
        encaminharParaSala(idMontagem, this);
      });
    }

    const btnRemover = tr.querySelector('.btn-remover');
    if (btnRemover) {
      btnRemover.addEventListener('click', function() {
        removerDoEvento(idMontagem, this);
      });
    }

    atualizarBadgePendentes(tbody.querySelectorAll('tr').length);
  }

  function atualizarBadgePendentes(count) {
    const tbl = document.getElementById('tbl-pendentes');
    if (!tbl) return;
    const card = tbl.closest('.card');
    if (!card) return;
    const badge = card.querySelector('.card-head .badge');
    if (badge) {
      badge.textContent = count + ' pendente(s)';
    }
  }

  function adicionarSerialAoItem(salaId, produtoEventoId, serial, produto, idMontagem) {
    const SALAS_MAP = getSalasMap();
    const montagemSection = document.getElementById('montagem-salas-container');
    if (!montagemSection) {
      return;
    }

    let salaBloco = montagemSection.querySelector('.sala-bloco[data-sala-id="' + salaId + '"]');

    if (!salaBloco) {
      let salaNome = 'Sala';
      if (SALAS_MAP[salaId]) {
        salaNome = SALAS_MAP[salaId].nome;
      }

      salaBloco = document.createElement('div');
      salaBloco.className = 'sala-bloco';
      salaBloco.setAttribute('data-sala-id', salaId);
      salaBloco.style.cssText = 'margin-bottom:16px;background:white;border-radius:10px;border:1px solid var(--bg-border-sub);overflow:hidden';

      window.renderHTML(salaBloco,
        '<div class="sala-header" style="background:var(--bg-secondary);padding:14px 16px;border-bottom:1px solid var(--bg-border-sub)">' +
        '<div style="display:flex;align-items:center;gap:10px">' +
        '<span style="font-weight:700;font-size:15px;color:var(--text-1)">' + escapeHtml(salaNome) + '</span>' +
        '<span class="sala-badge-items" style="font-size:11px;padding:4px 8px;background:rgba(8,145,178,0.2);border-radius:4px;color:var(--neon-cyan);font-weight:600">0 itens</span>' +
        '</div></div>' +
        '<div class="sala-itens-wrap" data-sala-itens="' + salaId + '" style="padding:12px 0"></div>');

      const emptyCard = document.getElementById('card-empty-montagem');
      if (emptyCard) emptyCard.remove();

      montagemSection.appendChild(salaBloco);
    }

    const itensWrap = salaBloco.querySelector('.sala-itens-wrap');

    let itemCard = itensWrap.querySelector('[data-produto-evento-id="' + produtoEventoId + '"]');

    if (!itemCard) {
      itemCard = document.createElement('div');
      itemCard.className = 'sala-item sala-item-card';
      itemCard.setAttribute('data-produto-evento-id', produtoEventoId);
      itemCard.style.cssText = 'margin:0 12px 12px;padding:12px;border-radius:8px;background:var(--bg-surface);border:1px solid var(--bg-border-sub);border-left:4px solid var(--neon-cyan);transition:border-color .2s';
      window.renderHTML(itemCard,
        '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px">' +
        '<div style="flex:1"><div style="font-weight:700;font-size:13px;color:var(--text-1);line-height:1.3">' + escapeHtml(maskProduto(produto)) + '</div></div>' +
        '<div style="display:flex;align-items:center;gap:8px;flex-shrink:0">' +
        '<span class="item-serial-badge" style="font-size:11px;padding:4px 10px;background:rgba(8,145,178,0.15);border-radius:4px;color:var(--neon-cyan);font-weight:600;white-space:nowrap">0 seriais</span>' +
        '<button type="button" class="btn btn-sm btn-purple" data-action="abrir-modal-sublocacao" data-produto-evento-id="' + produtoEventoId + '" data-produto="' + escapeHtml(maskProduto(produto)).replace(/'/g, "\\'") + '">Sublocar</button>' +
        '</div></div>' +
        '<div class="item-seriais-list" style="display:flex;flex-direction:column;gap:4px"></div>');
      itensWrap.appendChild(itemCard);
    }

    const serialsList = itemCard.querySelector('.item-seriais-list');
    if (serialsList) { serialsList.style.display = 'flex'; serialsList.style.flexDirection = 'column'; }
    itemCard.style.borderLeftColor = 'var(--neon-cyan)';
    const badgeEl = itemCard.querySelector('.item-serial-badge');
    if (badgeEl) { badgeEl.style.background = 'rgba(8,145,178,0.15)'; badgeEl.style.color = 'var(--neon-cyan)'; }

    const serialRow = document.createElement('div');
    serialRow.className = 'serial-row';
    serialRow.setAttribute('data-montagem-id', idMontagem);
    serialRow.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid rgba(0,0,0,0.05);transition:all 0.2s';
    window.renderHTML(serialRow,
      '<div style="flex:1;display:flex;align-items:center;gap:8px">' +
      '<span style="width:5px;height:5px;background:var(--neon-cyan);border-radius:50%;flex-shrink:0"></span>' +
      '<span style="font-size:11px;color:var(--text-3);white-space:nowrap">' + escapeHtml(maskProduto(produto)) + '</span>' +
      '<span style="font-weight:600;font-size:13px;color:var(--text-1);font-family:monospace">- ' + escapeHtml(serial) + '</span>' +
      '</div>' +
      '<div style="display:flex;gap:4px;flex-shrink:0">' +
      '<button type="button" class="btn btn-xs btn-yellow" data-action="remover-serial-do-item" data-id-montagem="' + idMontagem + '" title="Voltar para pendentes">Pendente</button>' +
      '<button type="button" class="btn btn-xs btn-red" data-action="remover-serial-do-evento" data-id-montagem="' + idMontagem + '" title="Remover do evento">✕</button>' +
      '</div>');

    if (serialsList) serialsList.appendChild(serialRow);

    const serialCount = serialsList ? serialsList.querySelectorAll('[data-montagem-id]').length : 1;
    const badge = itemCard.querySelector('.item-serial-badge');
    if (badge) badge.textContent = serialCount + ' serial(is)';

    const headerDiv = itemCard.querySelector('div[style*="justify-content:space-between"]');
    if (headerDiv) headerDiv.style.marginBottom = '12px';

  }

  function removerSerialDoItem(idMontagem, btn) {
    if (!confirm('Remover este serial do item? Ele voltará para pendentes.')) return;

    btn.disabled = true;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/remover-da-sala/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial voltou para pendentes!');

        const serialRow = btn.closest('[data-montagem-id]');
        if (serialRow) {
          serialRow.style.opacity = '0';
          serialRow.style.transition = 'opacity 0.3s';
          setTimeout(() => {
            const itemCard = serialRow.closest('[data-produto-evento-id]');
            serialRow.remove();

            if (itemCard) {
              atualizarEstadoItemCard(itemCard);
            }

            if (data.data) {
              adicionarRowPendentes(data.data.id, data.data.produto, data.data.serial);
            }
          }, 300);
        }
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
        btn.disabled = false;
      }
    })
    .catch((err) => {
      showToast('red', 'Erro', 'Erro ao processar');
      btn.disabled = false;
    });
  }

  function removerSerialDoEvento(idMontagem, btn) {
    if (!confirm('Remover este serial do evento? Esta ação não pode ser desfeita.')) return;

    btn.disabled = true;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/devolver/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then((r) => { return r.json(); })
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial removido do evento!');

        const serialRow = btn.closest('[data-montagem-id]');
        if (serialRow) {
          serialRow.style.opacity = '0';
          serialRow.style.transition = 'opacity 0.3s';
          setTimeout(() => {
            const itemCard = serialRow.closest('[data-produto-evento-id]');
            serialRow.remove();
            if (itemCard) atualizarEstadoItemCard(itemCard);
          }, 300);
        }
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
        btn.disabled = false;
      }
    })
    .catch(() => {
      showToast('red', 'Erro', 'Erro ao processar');
      btn.disabled = false;
    });
  }

  function atualizarEstadoItemCard(itemCard) {
    const serialsList = itemCard.querySelector('.item-seriais-list');
    const serialCount = serialsList ? serialsList.querySelectorAll('[data-montagem-id]').length : 0;
    const badge = itemCard.querySelector('.item-serial-badge');
    if (badge) badge.textContent = serialCount + ' serial(is)';

    if (serialCount === 0) {
      if (serialsList) serialsList.style.display = 'none';
      itemCard.style.borderLeftColor = 'var(--bg-border)';
      if (badge) { badge.style.background = 'var(--bg-secondary)'; badge.style.color = 'var(--text-4)'; }
      const headerDiv = itemCard.querySelector('div[style*="justify-content:space-between"]');
      if (headerDiv) headerDiv.style.marginBottom = '0';
    }
  }

  function adicionarRowSala(salaId, serial, obsItem, idMontagem) {
    const SALAS_MAP = getSalasMap();
    const montagemSection = document.getElementById('montagem-salas-container');
    const salaCard = montagemSection ? montagemSection.querySelector('.sala-bloco[data-sala-id="' + salaId + '"]') : null;

    if (!salaCard) {
      if (!montagemSection) {
        return;
      }

      const emptyCardEl = document.getElementById('card-empty-montagem');
      if (emptyCardEl) {
        emptyCardEl.remove();
      }

      let salaNome = 'Sala';
      if (SALAS_MAP[salaId]) {
        salaNome = SALAS_MAP[salaId].nome;
      }

      let salaObs = '';
      if (SALAS_MAP[salaId] && SALAS_MAP[salaId].obs) {
        salaObs = SALAS_MAP[salaId].obs;
      }

      const newCard = document.createElement('div');
      newCard.className = 'sala-bloco';
      newCard.setAttribute('data-sala-id', salaId);

      const headerHtml =
        '<div class="sala-header">' +
        '<div class="sala-header-left">' +
        '<span class="sala-nome">' + escapeHtml(salaNome) + '</span>' +
        '<span class="badge sm cyan">1 item(s)</span>' +
        '</div></div>';

      let obsHtml = '';
      if (salaObs) {
        obsHtml = '<div class="sala-obs-row">' +
          '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' +
          '<span>' + escapeHtml(salaObs) + '</span>' +
          '</div>';
      }

      const itensHtml =
        '<div class="sala-itens-wrap" data-sala-itens="' + salaId + '">' +
        '<div class="sala-item" data-montagem-id="' + idMontagem + '">' +
        buildSalaItemHtml(serial, obsItem, idMontagem) +
        '</div></div>';

      window.renderHTML(newCard, headerHtml + obsHtml + itensHtml);

      montagemSection.appendChild(newCard);
      return;
    }

    const itensWrap = salaCard.querySelector('.sala-itens-wrap');

    if (!itensWrap) {
      return;
    }

    const emptyMsg = itensWrap.querySelector('div[style*="text-align:center"]');
    if (emptyMsg) {
      emptyMsg.remove();
    }

    const newItem = document.createElement('div');
    newItem.className = 'sala-item';
    newItem.setAttribute('data-montagem-id', idMontagem);
    window.renderHTML(newItem, buildSalaItemHtml(serial, obsItem, idMontagem));

    newItem.style.opacity = '0';
    newItem.style.transform = 'translateY(-8px)';
    newItem.style.transition = 'opacity 0.3s, transform 0.3s';
    itensWrap.appendChild(newItem);
    void newItem.offsetHeight;
    newItem.style.opacity = '1';
    newItem.style.transform = 'translateY(0)';

    const itemsCount = itensWrap.querySelectorAll('.sala-item').length;
    const badge = salaCard.querySelector('.sala-header .badge');
    if (badge) {
      badge.textContent = itemsCount + ' item(s)';
    }
  }

  function atualizarCardProdutoAlocado(salaId, produtoEventoId, serialNumber) {
    const salaBloco = document.querySelector('.sala-bloco[data-sala-id="' + salaId + '"]');
    if (!salaBloco) {
      return;
    }

    const cardProduto = salaBloco.querySelector('.sala-item[data-produto-evento-id="' + produtoEventoId + '"]');
    if (!cardProduto) {
      return;
    }

    const statsDivs = cardProduto.querySelectorAll('span');
    let alocadoDiv = null;
    let faltamDiv = null;

    statsDivs.forEach((span) => {
      if (span.textContent.indexOf('Alocado:') !== -1) alocadoDiv = span;
      if (span.textContent.indexOf('Faltam:') !== -1) faltamDiv = span;
    });

    if (alocadoDiv && faltamDiv) {
      const alocadoMatch = alocadoDiv.textContent.match(/(\d+)/);
      const faltamMatch = faltamDiv.textContent.match(/(\d+)/);

      const alocadoAtual = alocadoMatch ? parseInt(alocadoMatch[1]) : 0;
      const faltamAtual = faltamMatch ? parseInt(faltamMatch[1]) : 0;

      const novoAlocado = alocadoAtual + 1;
      const novoFalta = Math.max(0, faltamAtual - 1);

      window.renderHTML(alocadoDiv, 'Alocado: <strong style="color:var(--neon-green)">' + novoAlocado + '</strong>');
      const statusIcon = novoFalta === 0 ? '✅' : '⚠️';
      const statusColor = novoFalta === 0 ? 'var(--neon-green)' : 'var(--neon-yellow)';
      window.renderHTML(faltamDiv, 'Faltam: <strong style="color:' + statusColor + '">' + novoFalta + '</strong> ' + statusIcon);

    }

    let seriaisWrap = cardProduto.querySelector('div[style*="display:flex;flex-wrap:wrap"]');
    if (!seriaisWrap) {
      const allDivs = cardProduto.querySelectorAll('div');
      let nenhumDiv = null;
      allDivs.forEach((d) => {
        if (d.textContent.indexOf('Nenhum serial alocado ainda') !== -1 && !d.querySelector('div')) {
          nenhumDiv = d;
        }
      });
      if (nenhumDiv) {
        seriaisWrap = document.createElement('div');
        seriaisWrap.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px';
        nenhumDiv.replaceWith(seriaisWrap);
      }
    }

    if (seriaisWrap) {
      const serialBadge = document.createElement('div');
      serialBadge.style.cssText = 'background:var(--bg-surface);padding:4px 8px;border-radius:3px;font-size:11px;font-weight:600;border-left:3px solid var(--neon-cyan)';
      window.renderHTML(serialBadge, serialNumber +
        '<button type="button" class="btn btn-xs btn-red" style="margin-left:4px;padding:0 4px;font-size:9px" title="Remover">x</button>');
      seriaisWrap.appendChild(serialBadge);

    }
  }

  window.MontarOsSerial = {
    initSerialInputs: initSerialInputs,
    enviarSerial: enviarSerial,
    enviarLote: enviarLote,
    encaminharParaSala: encaminharParaSala,
    removerDaSala: removerDaSala,
    removerDoEvento: removerDoEvento,
    adicionarRowPendentes: adicionarRowPendentes,
    atualizarBadgePendentes: atualizarBadgePendentes,
    adicionarSerialAoItem: adicionarSerialAoItem,
    removerSerialDoItem: removerSerialDoItem,
    removerSerialDoEvento: removerSerialDoEvento,
    atualizarEstadoItemCard: atualizarEstadoItemCard,
    adicionarRowSala: adicionarRowSala,
    atualizarCardProdutoAlocado: atualizarCardProdutoAlocado,
    atualizarItensDaSala: atualizarItensDaSala,
    maskProduto: maskProduto,
    buildSalaOptions: buildSalaOptions,
    getNomeProdutoById: getNomeProdutoById
  };

  window.encaminharParaSala = encaminharParaSala;
  window.removerDaSala = removerDaSala;
  window.removerDoEvento = removerDoEvento;
  window.removerSerialDoItem = removerSerialDoItem;
  window.removerSerialDoEvento = removerSerialDoEvento;

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel.
    if (window.registerAction) {
      window.registerAction('remover-da-sala', function(el) {
        removerDaSala(el.dataset.idMontagem, el);
      });
      window.registerAction('remover-do-evento', function(el) {
        removerDoEvento(el.dataset.idMontagem, el);
      });
      window.registerAction('remover-serial-do-item', function(el) {
        removerSerialDoItem(el.dataset.idMontagem, el);
      });
      window.registerAction('remover-serial-do-evento', function(el) {
        removerSerialDoEvento(el.dataset.idMontagem, el);
      });
      window.registerAction('encaminhar-para-sala', function(el) {
        encaminharParaSala(el.dataset.montagemId, el);
      });
    }
  });

})();

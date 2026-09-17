/**
 * JavaScript - Tab: Montar OS
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
  
  // Capturar variaveis globais
  var EVENTO_ID = window.EVENTO_ID;
  var CSRF_TOKEN = window.CSRF_TOKEN;
  var BASE_URL = window.BASE_URL || '';
  var SALAS_MAP = window.SALAS_MAP || {};
  
  // Elementos DOM
  var serialInput = null;
  var btnInserir = null;
  
  // Helper: construir HTML de options para salas a partir de SALAS_MAP
  function buildSalaOptions() {
    var html = '<option value="">Selecione a sala</option>';
    for (var salaId in SALAS_MAP) {
      if (SALAS_MAP.hasOwnProperty(salaId)) {
        var sala = SALAS_MAP[salaId];
        html += '<option value="' + salaId + '">' + escapeHtml(sala.nome) + '</option>';
      }
    }
    return html;
  }
  
  // =====================================================
  // INICIALIZACAO (executa quando pane fica visivel)
  // =====================================================
  function init() {
    serialInput = document.getElementById('serial-codigo-evento');
    btnInserir = document.getElementById('btn-inserir-serial');
    
    console.log('[MONTAGEM] Init - serialInput:', serialInput ? 'ENCONTRADO' : 'NAO ENCONTRADO');
    console.log('[MONTAGEM] Init - btnInserir:', btnInserir ? 'ENCONTRADO' : 'NAO ENCONTRADO');
    
    if (!serialInput || !btnInserir) {
      console.warn('[MONTAGEM] Elementos nao encontrados, abortando init.');
      return;
    }
    
    // Clonar elementos para remover listeners antigos antes de adicionar novos
    // Isso evita listeners duplicados quando o init() e chamado multiplas vezes
    var newBtn = btnInserir.cloneNode(true);
    var newInput = serialInput.cloneNode(true);
    btnInserir.parentNode.replaceChild(newBtn, btnInserir);
    serialInput.parentNode.replaceChild(newInput, serialInput);
    btnInserir = newBtn;
    serialInput = newInput;
    
    // Serial Individual - Botao
    btnInserir.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      enviarSerial();
    });
    
    // Serial Individual - Enter no input
    serialInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        enviarSerial();
      }
    });
    
    // Debug input
    serialInput.addEventListener('input', function() {
      console.log('[MONTAGEM] Input value:', serialInput.value);
    });
    
    // Form Lote - Botao
    var btnLote = document.getElementById('btn-inserir-lote');
    console.log('[MONTAGEM] Init - btnLote:', btnLote ? 'ENCONTRADO' : 'NAO ENCONTRADO');
    if (btnLote) {
      var newBtnLote = btnLote.cloneNode(true);
      btnLote.parentNode.replaceChild(newBtnLote, btnLote);
      btnLote = newBtnLote;
      btnLote.addEventListener('click', function(e) {
        console.log('[MONTAGEM LOTE] Botao clicado!');
        e.preventDefault();
        e.stopPropagation();
        enviarLote();
      });
      console.log('[MONTAGEM] btnLote listener adicionado');
    }
    
    // Form Lote - Previne submit acidental com Enter
    var formLote = document.getElementById('form-lote-evento');
    if (formLote) {
      formLote.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      });
    }
    
    serialInput.focus();
    console.log('[MONTAGEM] Inicializado com sucesso');
  }
  
  // =====================================================
  // ENVIAR SERIAL INDIVIDUAL
  // =====================================================
  function enviarSerial() {
    var serial = serialInput ? serialInput.value.trim() : '';
    console.log('[MONTAGEM] enviarSerial:', serial);
    
    if (!serial) {
      showToast('yellow', 'Atencao', 'Digite um serial');
      return;
    }
    
    var infoDiv = document.getElementById('serial-info-evento');
    btnInserir.disabled = true;
    btnInserir.textContent = 'Processando...';
    if (infoDiv) {
      infoDiv.innerHTML = '<div style="text-align:center;padding:8px;color:var(--text-3)">Processando...</div>';
    }
    
    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('serial', serial);
    
    fetch(BASE_URL + '/montagem/inserir-serial', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(data) {
      if (data.success && data.data) {
        showToast('green', 'Sucesso', data.message || 'Serial incluido!');
        adicionarRowPendentes(data.data.id, data.data.produto, data.data.serial);
        serialInput.value = '';
        if (infoDiv) infoDiv.innerHTML = '';
      } else {
        if (infoDiv) {
          infoDiv.innerHTML = buildAlert('red', 'Erro', data.error || 'Erro ao incluir');
        }
        showToast('red', 'Erro', data.error || 'Erro ao incluir');
      }
    })
    .catch(function(err) {
      if (infoDiv) {
        infoDiv.innerHTML = buildAlert('red', 'Erro', 'Erro: ' + err.message);
      }
      showToast('red', 'Erro', 'Erro: ' + err.message);
    })
    .finally(function() {
      btnInserir.disabled = false;
      btnInserir.textContent = 'Inserir Serial';
      setTimeout(function() {
        serialInput.focus();
      }, 50);
    });
  }
  
  // =====================================================
  // ENVIAR LOTE
  // =====================================================
  function enviarLote() {
    console.log('[MONTAGEM LOTE] enviarLote chamada');
    
    var seriaisInput = document.getElementById('lote-seriais-evento');
    console.log('[MONTAGEM LOTE] seriaisInput encontrado:', seriaisInput ? 'SIM' : 'NAO');
    
    var seriais = seriaisInput ? seriaisInput.value.trim() : '';
    console.log('[MONTAGEM LOTE] seriais:', seriais);
    
    if (!seriais) {
      showToast('yellow', 'Atencao', 'Insira seriais');
      return;
    }
    
    var resultadoDiv = document.getElementById('lote-resultado-evento');
    console.log('[MONTAGEM LOTE] resultadoDiv encontrado:', resultadoDiv ? 'SIM' : 'NAO');
    
    if (resultadoDiv) {
      resultadoDiv.innerHTML = '<div style="text-align:center;padding:8px;color:var(--text-3)">Processando...</div>';
    }
    
    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', EVENTO_ID);
    formData.append('seriais', seriais);
    
    console.log('[MONTAGEM LOTE] Enviando para:', BASE_URL + '/montagem/inserir-lote');
    
    fetch(BASE_URL + '/montagem/inserir-lote', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) {
      console.log('[MONTAGEM LOTE] Response status:', r.status);
      return r.json();
    })
    .then(function(data) {
      console.log('[MONTAGEM LOTE] Response data:', data);
      
      if (data.success) {
        var msg = data.message || 'Lote processado!';
        if (data.detalhes) msg += ' | Sucesso: ' + data.detalhes.sucesso + ' | Erros: ' + data.detalhes.erros;
        if (resultadoDiv) {
          resultadoDiv.innerHTML = buildAlert('green', 'Sucesso', msg);
        }
        showToast('green', 'Sucesso', 'Lote processado!');
        if (seriaisInput) seriaisInput.value = '';
        
        // Auto-update: adicionar seriais inseridos na tabela de pendentes
        if (data.seriais_inseridos && data.seriais_inseridos.length > 0) {
          for (var i = 0; i < data.seriais_inseridos.length; i++) {
            var item = data.seriais_inseridos[i];
            adicionarRowPendentes(item.id, item.produto, item.serial);
          }
        }
      } else {
        if (resultadoDiv) {
          resultadoDiv.innerHTML = buildAlert('red', 'Erro', data.error || 'Erro ao processar lote');
        }
        showToast('red', 'Erro', data.error || 'Erro ao processar lote');
      }
    })
    .catch(function(err) {
      console.error('[MONTAGEM LOTE] Erro:', err);
      if (resultadoDiv) {
        resultadoDiv.innerHTML = buildAlert('red', 'Erro', 'Erro de conexao');
      }
      showToast('red', 'Erro', 'Erro ao processar');
    });
  }
  
  // =====================================================
  // ENCAMINHAR PARA SALA
  // =====================================================
  function encaminharParaSala(idMontagem, btn) {
    var row = btn.closest('tr');
    var select = row.querySelector('.sala-destino-select');
    var idSala = select.value;
    
    if (!idSala) {
      showToast('yellow', 'Atencao', 'Selecione uma sala');
      return;
    }
    
    // Capturar dados do row ANTES de remover
    var tds = row.querySelectorAll('td');
    var produto = tds[0] ? tds[0].textContent.trim() : '';
    var serial = tds[1] ? tds[1].textContent.trim() : '';
    var salaId = parseInt(idSala);
    
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    
    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_sala', idSala);
    
    fetch(BASE_URL + '/montagem/encaminhar-sala/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial encaminhado para a sala!');
        // Remover da lista de pendentes com animacao
        var tbody = row.closest('tbody'); // Pegar tbody ANTES de remover
        var pendentesCount = tbody ? tbody.querySelectorAll('tr').length - 1 : 0; // Calcular nova contagem
        
        row.style.opacity = '0';
        row.style.transition = 'opacity 0.3s';
        setTimeout(function() { 
          row.remove();
          // Verificar se tabela de pendentes ficou vazia
          if (tbody && tbody.querySelectorAll('tr').length === 0) {
            var tblPendentes = document.getElementById('tbl-pendentes');
            if (tblPendentes) {
              var card = tblPendentes.closest('.card');
              if (card) {
                var cardBody = card.querySelector('.card-body');
                if (cardBody) {
                  cardBody.innerHTML = buildEmptyState('Nenhum serial pendente', 'Todos os seriais foram encaminhados');
                }
              }
            }
          }
          // Atualizar badge de pendentes
          atualizarBadgePendentes(pendentesCount);
        }, 300);
        // Usar dados do response se disponiveis, senao usar os capturados do DOM
        var serialData = (data.data && data.data.serial) ? data.data.serial : serial;
        var produtoData = (data.data && data.data.produto) ? data.data.produto : produto;
        // Adicionar na lista da sala sem refresh (produto vai como obsItem)
        adicionarRowSala(salaId, serialData, produtoData, idMontagem);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao encaminhar');
        btn.disabled = false;
        btn.textContent = 'Encaminhar';
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao processar');
      btn.disabled = false;
      btn.textContent = 'Encaminhar';
    });
  }
  window.encaminharParaSala = encaminharParaSala;
  
  // =====================================================
  // REMOVER DA SALA (VOLTA PARA PENDENTES)
  // =====================================================
  function removerDaSala(idMontagem, btn) {
    if (!confirm('Remover este serial da sala? Ele voltara para a lista de pendentes.')) return;
    
    // Usar data-montagem-id para encontrar o item correto (evita pegar sala-item dos produtos de referencia)
    var montagemSection = document.getElementById('montagem-salas-container');
    var salaItem = montagemSection ? montagemSection.querySelector('.sala-item[data-montagem-id="' + idMontagem + '"]') : null;
    
    if (!salaItem) {
      console.error('[MONTAGEM] sala-item nao encontrado para id:', idMontagem);
      showToast('red', 'Erro', 'Item nao encontrado na sala');
      return;
    }
    
    // Capturar serial e produto do novo formato (spans sem classes especificas)
    var spans = salaItem.querySelectorAll('span');
    var serial = spans[0] ? spans[0].textContent.trim() : '';
    var produto = spans[1] ? spans[1].textContent.trim() : '';
    
    btn.disabled = true;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
    
    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    
    fetch(BASE_URL + '/montagem/remover-da-sala/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial voltou para pendentes!');
        salaItem.style.opacity = '0';
        salaItem.style.transition = 'opacity 0.3s';
        setTimeout(function() {
          salaItem.remove();
          // Encontrar o sala-bloco pai e atualizar
          var salaCard = salaItem.closest ? salaItem.closest('.sala-bloco') : document.querySelector('#montagem-salas-container .sala-bloco[data-sala-id]');
          if (salaCard) {
            var itensWrap = salaCard.querySelector('.sala-itens-wrap');
            var itemsCount = itensWrap ? itensWrap.querySelectorAll('.sala-item').length : 0;
            if (itemsCount === 0) {
              // Mostrar mensagem de vazio
              itensWrap.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">Nenhum serial nesta sala</div>';
            }
            var badge = salaCard.querySelector('.sala-header .badge');
            if (badge) badge.textContent = itemsCount + ' item(s)';
          }
          // Usar dados do response se disponiveis, senao usar os capturados do DOM
          if (data.data) {
            adicionarRowPendentes(data.data.id, data.data.produto, data.data.serial);
          } else {
            adicionarRowPendentes(idMontagem, produto, serial);
          }
        }, 300);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover da sala');
        btn.disabled = false;
        btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 01-8 8H3m0 0h3m-3 0v3m0-3V10m12-4a2 2 0 012 2v0a2 2 0 01-2 2h0a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>';
      }
    })
    .catch(function(err) {
      showToast('red', 'Erro', 'Erro ao processar: ' + err.message);
      btn.disabled = false;
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 01-8 8H3m0 0h3m-3 0v3m0-3V10m12-4a2 2 0 012 2v0a2 2 0 01-2 2h0a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>';
    });
  }
  window.removerDaSala = removerDaSala;
  
  // =====================================================
  // REMOVER DO EVENTO (DEVOLVER)
  // =====================================================
  function removerDoEvento(idMontagem, btn) {
    if (!confirm('Tem certeza que deseja remover este serial do evento?')) return;

    // Buscar o item — pode estar na sala (sala-bloco) ou na tabela de pendentes
    var montagemSection = document.getElementById('montagem-salas-container');
    var salaItem = montagemSection ? montagemSection.querySelector('.sala-item[data-montagem-id="' + idMontagem + '"]') : null;

    // Capturar dados ANTES de qualquer manipulacao
    var serial = '';
    var produto = '';
    var isPendente = false;

    if (!salaItem) {
      // Tentar encontrar na tabela de pendentes
      var tblPendentes = document.getElementById('tbl-pendentes');
      if (tblPendentes) {
        var pendenteRow = tblPendentes.querySelector('tr[data-id="' + idMontagem + '"]');
        if (pendenteRow) {
          isPendente = true;
          var tds = pendenteRow.querySelectorAll('td');
          produto = tds[0] ? tds[0].textContent.trim() : '';
          serial = tds[1] ? tds[1].textContent.trim() : '';
        }
      }
    } else {
      // Item esta em uma sala — capturar dados
      var spans = salaItem.querySelectorAll('span');
      serial = spans[0] ? spans[0].textContent.trim() : '';
      produto = spans[1] ? spans[1].textContent.trim() : '';
    }

    if (!salaItem && !isPendente) {
      console.error('[MONTAGEM] Item nao encontrado (nem sala nem pendentes) para id:', idMontagem);
      showToast('red', 'Erro', 'Item nao encontrado');
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/montagem/devolver/' + idMontagem, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial removido do evento!');

        if (isPendente) {
          // Remover row da tabela de pendentes
          var tblPendentes = document.getElementById('tbl-pendentes');
          if (tblPendentes) {
            var pendenteRow = tblPendentes.querySelector('tr[data-id="' + idMontagem + '"]');
            if (pendenteRow) {
              var tbody = pendenteRow.closest('tbody');
              pendenteRow.style.opacity = '0';
              pendenteRow.style.transition = 'opacity 0.3s';
              (function(row, tb) {
                setTimeout(function() {
                  row.remove();
                  if (tb && tb.querySelectorAll('tr').length === 0) {
                    var card = tblPendentes.closest('.card');
                    if (card) {
                      var cardBody = card.querySelector('.card-body');
                      if (cardBody) {
                        cardBody.innerHTML = buildEmptyState('Nenhum serial pendente', 'Todos os seriais foram encaminhados');
                      }
                    }
                  }
                  atualizarBadgePendentes(tb ? tb.querySelectorAll('tr').length : 0);
                }, 300);
              })(pendenteRow, tbody);
            }
          }
        } else {
          // Remover item da sala e atualizar badge
          salaItem.style.opacity = '0';
          salaItem.style.transition = 'opacity 0.3s';
          setTimeout(function() {
            salaItem.remove();
            var allBlocos = document.querySelectorAll('#montagem-salas-container .sala-bloco');
            for (var b = 0; b < allBlocos.length; b++) {
              var wrap = allBlocos[b].querySelector('.sala-itens-wrap');
              if (wrap) {
                var itemsCount = wrap.querySelectorAll('.sala-item').length;
                if (itemsCount === 0) {
                  wrap.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">Nenhum serial nesta sala</div>';
                }
                var badge = allBlocos[b].querySelector('.sala-header .badge');
                if (badge) badge.textContent = itemsCount + ' item(s)';
              }
            }
          }, 300);
        }
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
        btn.disabled = false;
        btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
      }
    })
    .catch(function(err) {
      showToast('red', 'Erro', 'Erro ao processar: ' + err.message);
      btn.disabled = false;
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
    });
  }
  window.removerDoEvento = removerDoEvento;
  
  // =====================================================
  // ADICIONAR ROW NA TABELA DE PENDENTES
  // =====================================================
  function adicionarRowPendentes(idMontagem, produto, serial) {
    var tblPendentes = document.getElementById('tbl-pendentes');
    var card = null;
    
    if (!tblPendentes) {
      // Buscar card pelo titulo
      var allCards = document.querySelectorAll('.card');
      for (var i = 0; i < allCards.length; i++) {
        var title = allCards[i].querySelector('.card-title');
        var titleText = title ? title.textContent : '';
        if (title && titleText.indexOf('Seriais Pendentes') !== -1) {
          card = allCards[i];
          break;
        }
      }
      if (!card) {
        console.error('[MONTAGEM] Card "Seriais Pendentes" nao encontrado!');
        return;
      }
      
      // Criar tabela a partir do empty state
      var tableHtml = '<div class="tbl-container"><table class="data-table striped hoverable" id="tbl-pendentes"><thead><tr>' +
        '<th>Produto</th><th>Serial</th><th style="min-width:320px">Acao</th>' +
        '</tr></thead><tbody></tbody></table></div>';
      var cardBody = card.querySelector('.card-body');
      if (cardBody) {
        var emptyDiv = cardBody.querySelector('.table-empty');
        if (emptyDiv) {
          emptyDiv.remove();
        }
        cardBody.innerHTML = tableHtml;
      }
      tblPendentes = document.getElementById('tbl-pendentes');
    }
    
    if (!tblPendentes) return;
    var tbody = tblPendentes.querySelector('tbody');
    if (!tbody) return;
    
    // Construir options a partir de SALAS_MAP (fonte oficial de dados)
    var optionsHtml = buildSalaOptions();
    
    var tr = document.createElement('tr');
    tr.setAttribute('data-id', idMontagem);
    tr.innerHTML = '<td>' + escapeHtml(maskProduto(produto)) + '</td>' +
      '<td>' + escapeHtml(serial) + '</td>' +
      '<td>' +
      '<select class="fi fi-sm sala-destino-select" style="width:180px;display:inline-block;vertical-align:middle">' +
      optionsHtml + '</select>' +
      '<button type="button" class="btn btn-sm btn-cyan" style="display:inline-block;vertical-align:middle;margin-left:4px" onclick="encaminharParaSala(' + idMontagem + ', this)">Encaminhar</button>' +
      '<button type="button" class="btn btn-sm btn-red" style="display:inline-block;vertical-align:middle;margin-left:4px" onclick="removerDoEvento(' + idMontagem + ', this)" title="Remover do Evento">' +
      '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
      '</button></td>';
    
    // Animar entrada
    tr.style.opacity = '0';
    tr.style.transform = 'translateY(-8px)';
    tr.style.transition = 'opacity 0.3s, transform 0.3s';
    tbody.appendChild(tr);
    void tr.offsetHeight;
    tr.style.opacity = '1';
    tr.style.transform = 'translateY(0)';
    
    atualizarBadgePendentes(tbody.querySelectorAll('tr').length);
  }
  
  function atualizarBadgePendentes(count) {
    var tbl = document.getElementById('tbl-pendentes');
    if (!tbl) return;
    var card = tbl.closest('.card');
    if (!card) return;
    var badge = card.querySelector('.card-head .badge');
    if (badge) {
      badge.textContent = count + ' pendente(s)';
    }
  }
  
  // =====================================================
  // ADICIONAR ITEM NA LISTA DA SALA (estilo sala-bloco)
  // =====================================================
  function adicionarRowSala(salaId, serial, obsItem, idMontagem) {
    console.log('[MONTAGEM] adicionarRowSala chamada:', {salaId, serial, obsItem, idMontagem});
    
    // Buscar o card da sala DENTRO da secao de montagem apenas
    var montagemSection = document.getElementById('montagem-salas-container');
    var salaCard = montagemSection ? montagemSection.querySelector('.sala-bloco[data-sala-id="' + salaId + '"]') : null;
    console.log('[MONTAGEM] salaCard encontrado:', salaCard ? 'SIM - ' + salaCard.className : 'NAO');
    
    if (!salaCard) {
      // Criar novo card para a sala
      console.log('[MONTAGEM] montagem-salas-container encontrado:', montagemSection ? 'SIM' : 'NAO');
      if (!montagemSection) {
        console.error('[MONTAGEM] Secao "Listagem de Produtos em Montagem" nao encontrada!');
        return;
      }
      
      // Remover empty state se existir
      var emptyCardEl = document.getElementById('card-empty-montagem');
      console.log('[MONTAGEM] empty state encontrado:', emptyCardEl ? 'SIM' : 'NAO');
      if (emptyCardEl) {
        emptyCardEl.remove();
        console.log('[MONTAGEM] empty state removido');
      }
      
      // Buscar nome da sala no SALAS_MAP
      var salaNome = 'Sala';
      if (SALAS_MAP[salaId]) {
        salaNome = SALAS_MAP[salaId].nome;
      }
      
      // Buscar observacao da sala no SALAS_MAP
      var salaObs = '';
      if (SALAS_MAP[salaId] && SALAS_MAP[salaId].obs) {
        salaObs = SALAS_MAP[salaId].obs;
      }
      
      // Criar novo sala-bloco com estrutura completa (igual ao backend)
      var newCard = document.createElement('div');
      newCard.className = 'sala-bloco';
      newCard.setAttribute('data-sala-id', salaId);
      
      var headerHtml =
        '<div class="sala-header">' +
        '<div class="sala-header-left">' +
        '<span class="sala-nome">' + escapeHtml(salaNome) + '</span>' +
        '<span class="badge sm cyan">1 item(s)</span>' +
        '</div></div>';
      
      var obsHtml = '';
      if (salaObs) {
        obsHtml = '<div class="sala-obs-row">' +
          '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' +
          '<span>' + escapeHtml(salaObs) + '</span>' +
          '</div>';
      }
      
      var itensHtml =
        '<div class="sala-itens-wrap" data-sala-itens="' + salaId + '">' +
        '<div class="sala-item" data-montagem-id="' + idMontagem + '">' +
        buildSalaItemHtml(serial, obsItem, idMontagem) +
        '</div></div>';
      
      newCard.innerHTML = headerHtml + obsHtml + itensHtml;
      
      montagemSection.appendChild(newCard);
      console.log('[MONTAGEM] novo sala-bloco criado e adicionado:', newCard);
      return;
    }
    
    // Card existe - buscar sala-itens-wrap dentro dele
    var itensWrap = salaCard.querySelector('.sala-itens-wrap');
    console.log('[MONTAGEM] itensWrap encontrado:', itensWrap ? 'SIM' : 'NAO');
    
    if (!itensWrap) {
      console.error('[MONTAGEM] sala-itens-wrap nao encontrado dentro do card:', salaCard);
      return;
    }
    
    // Remover empty state se existir dentro do wrap
    var emptyMsg = itensWrap.querySelector('div[style*="text-align:center"]');
    if (emptyMsg) {
      emptyMsg.remove();
    }
    
    // Criar e adicionar novo item
    var newItem = document.createElement('div');
    newItem.className = 'sala-item';
    newItem.setAttribute('data-montagem-id', idMontagem);
    newItem.innerHTML = buildSalaItemHtml(serial, obsItem, idMontagem);

    // Animar entrada
    newItem.style.opacity = '0';
    newItem.style.transform = 'translateY(-8px)';
    newItem.style.transition = 'opacity 0.3s, transform 0.3s';
    itensWrap.appendChild(newItem);
    void newItem.offsetHeight;
    newItem.style.opacity = '1';
    newItem.style.transform = 'translateY(0)';
    
    console.log('[MONTAGEM] item adicionado ao wrap:', newItem);
    
    // Atualizar badge da sala com a contagem correta de items
    var itemsCount = itensWrap.querySelectorAll('.sala-item').length;
    var badge = salaCard.querySelector('.sala-header .badge');
    if (badge) {
      badge.textContent = itemsCount + ' item(s)';
      console.log('[MONTAGEM] badge atualizado:', itemsCount + ' item(s)');
    }
  }
  
  // Helper: construir HTML interno de um item da sala
  function buildSalaItemHtml(serial, obsItem, idMontagem) {
    return '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px">' +
      '<div style="flex:1;min-width:0">' +
      '<span style="font-weight:700;font-size:13px;color:var(--text-1)">' + escapeHtml(serial) + '</span>' +
      '<span style="font-size:12px;color:var(--text-3);margin-left:8px">' + escapeHtml(maskProduto(obsItem) || '-') + '</span>' +
      '</div>' +
      '<div style="display:flex;gap:4px;flex-shrink:0">' +
      '<button type="button" class="btn btn-sm btn-yellow" onclick="removerDaSala(' + idMontagem + ', this)" title="Voltar para pendentes">Pendente</button>' +
      '<button type="button" class="btn btn-sm btn-red" onclick="removerDoEvento(' + idMontagem + ', this)" title="Remover do evento">Remover</button>' +
      '</div></div>';
  }
  
  // =====================================================
  // HELPERS
  // =====================================================
  function maskProduto(name) {
    return (name || '').replace(/\s*-\s*\d+$/, '');
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }
  
  function buildAlert(variant, title, message) {
    var colors = {
      'red': { bg: 'rgba(244,63,94,0.08)', border: 'var(--neon-red)', color: 'var(--neon-red)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
      'green': { bg: 'rgba(34,197,94,0.08)', border: 'var(--neon-green)', color: 'var(--neon-green)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
      'yellow': { bg: 'rgba(245,158,11,0.08)', border: 'var(--neon-yellow)', color: 'var(--neon-yellow)', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.337-2.5L13.732 4c-.665-.932-2.075-.932-2.74 0L4.07 16.5c-1.165.833-.204 2.5 1.337 2.5z"/>' }
    };
    var c = colors[variant] || colors['red'];
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
  
  // =====================================================
  // MUTATION OBSERVER - detectar quando pane fica visivel
  // =====================================================
  var pane = document.getElementById('loc-hpane-1');
  if (pane) {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.attributeName === 'class' && pane.classList.contains('active')) {
          console.log('[MONTAGEM] Pane ativada - inicializando...');
          setTimeout(init, 150);
        }
      });
    });
    observer.observe(pane, { attributes: true });
    
    // Se ja esta ativo na carga da pagina
    if (pane.classList.contains('active')) {
      setTimeout(init, 200);
    }
  } else {
    // Sem hpane: init direto (modo standalone)
    if (document.readyState !== 'loading') { init(); }
    else { document.addEventListener('DOMContentLoaded', init); }
  }
  
  // =====================================================
  // LISTENER: Evento de devolucao concluida
  // Recarrega dados do servidor e remove itens devolvidos do DOM
  // =====================================================
  document.addEventListener('devolucao-concluida', function(e) {
    console.log('[MONTAGEM] Recebido evento devolucao-concluida - recarregando dados do servidor');
    recarregarMontagens();
  });

  // Recarregar montagens apos devolucao
  function recarregarMontagens() {
    fetch(BASE_URL + '/montagem/listar/' + EVENTO_ID, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success || !data.data) return;

      var montagensAtivas = data.data; // Apenas status != 'devolvido'
      var idsAtivos = {};
      montagensAtivas.forEach(function(m) {
        idsAtivos[m.id] = true;
      });

      // Remover items (.sala-item) que nao existem mais no servidor
      var container = document.getElementById('montagem-salas-container');
      if (!container) return;

      var itens = container.querySelectorAll('.sala-item');
      var removidos = 0;
      itens.forEach(function(item) {
        var itemId = parseInt(item.getAttribute('data-montagem-id'));
        if (itemId && !idsAtivos[itemId]) {
          // Item foi devolvido — remover do DOM
          item.style.opacity = '0';
          item.style.transition = 'opacity 0.3s';
          (function(el) {
            setTimeout(function() {
              el.remove();
              // Verificar se o sala-bloco ficou sem itens
              var bloco = el.closest('.sala-bloco');
              if (bloco) {
                var wrap = bloco.querySelector('[data-sala-itens]');
                if (wrap && wrap.querySelectorAll('.sala-item').length === 0) {
                  wrap.innerHTML = '<div style="padding:12px;text-align:center;font-size:12px;color:var(--text-3)">Nenhum serial nesta sala</div>';
                }
              }
            }, 300);
          })(item);
          removidos++;
        }
      });

      // Tambem remover da tabela de pendentes se existir
      var tblPendentes = document.getElementById('tbl-pendentes');
      if (tblPendentes) {
        var tbody = tblPendentes.querySelector('tbody');
        if (tbody) {
          var rows = tbody.querySelectorAll('tr');
          rows.forEach(function(row) {
            var rowId = parseInt(row.getAttribute('data-id'));
            if (rowId && !idsAtivos[rowId]) {
              row.style.opacity = '0';
              row.style.transition = 'opacity 0.3s';
              setTimeout(function() { row.remove(); }, 300);
            }
          });
          // Verificar se ficou vazia
          setTimeout(function() {
            if (tbody.querySelectorAll('tr').length === 0) {
              var card = tblPendentes.closest('.card');
              if (card) {
                var cardBody = card.querySelector('.card-body');
                if (cardBody) {
                  cardBody.innerHTML = buildEmptyState('Nenhum serial pendente', 'Todos os seriais foram encaminhados ou devolvidos');
                }
              }
            }
          }, 350);
        }
      }

      // Atualizar badge de pendentes
      var pendentesCount = montagensAtivas.filter(function(m) { return !m.id_sala; }).length;
      atualizarBadgePendentes(pendentesCount);

      if (removidos > 0) {
        console.log('[MONTAGEM] ' + removidos + ' item(s) removido(s) apos devolucao');
      }
    })
    .catch(function() {
      console.error('[MONTAGEM] Erro ao recarregar dados apos devolucao');
    });
  }
})();

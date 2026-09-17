/**
 * Modulo: Fechamento - Outros Custos e Fotos
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
  const formatDateBR = U.dateBR;
  const escapeHtml   = U.html;
  const showLoading  = U.showLoading;
  const hideLoading  = U.hideLoading;
  const showContent  = U.showContent;
  const showEmpty    = U.showEmpty;

  // ========================================
  // Fotos
  // ========================================
  function loadFotos() {
    if (!EVENTO_ID) return;
    const loadingEl = document.getElementById('fechamento-fotos-salas-loading');
    if (loadingEl) loadingEl.style.display = 'block';

    fetchJson(BASE_URL + '/fechamento/fotos/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((data) => {
      if (loadingEl) loadingEl.style.display = 'none';
      if (!data.success) return;
      renderFotos(data.data);
    })
    .catch((err) => {
      if (loadingEl) loadingEl.style.display = 'none';
    });
  }

  function renderFotos(data) {
    const salasContainer = document.getElementById('fechamento-fotos-salas-content');
    if (salasContainer) {
      if (!data.por_sala || data.por_sala.length === 0) {
        window.renderHTML(salasContainer, '<div style="text-align:center;padding:24px;color:var(--text-3)">' +
          '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;margin:0 auto 8px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>' +
          '<div style="font-weight:600;margin-bottom:4px">Nenhuma sala encontrada</div>' +
          '<div style="font-size:12px">Adicione produtos ao evento por sala primeiro</div></div>');
        salasContainer.style.display = 'block';
      } else {
        let html = '';
        data.por_sala.forEach((sala) => {
          const hasFotos = sala.fotos && sala.fotos.length > 0;
          const badgeHtml = hasFotos ? '<span class="badge sm cyan">' + sala.total + ' foto' + (sala.total > 1 ? 's' : '') + '</span>' : '<span class="badge sm gray">Sem fotos</span>';
          let fotosHtml = '';

          if (hasFotos) {
            fotosHtml = '<div class="fechamento-foto-grid">';
            sala.fotos.forEach((foto) => {
              let fotoPath = escapeHtml(foto.caminho_arquivo);
              if (!fotoPath.startsWith('http') && !fotoPath.startsWith('/')) {
                fotoPath = BASE_URL + '/' + fotoPath;
              }
              fotosHtml += '<div class="fechamento-foto-item">' +
                '<a href="' + fotoPath + '" target="_blank">' +
                  '<img src="' + fotoPath + '" alt="' + escapeHtml(foto.nome_arquivo) + '">' +
                '</a>' +
                '<button type="button" class="btn btn-sm btn-red" data-action="remover-foto" data-id="' + foto.id + '" title="Remover">&times;</button>' +
              '</div>';
            });
            fotosHtml += '</div>';
          } else {
            fotosHtml = '<div style="text-align:center;padding:20px;color:var(--text-3);font-size:13px">Nenhuma foto nesta sala ainda. Faca upload abaixo.</div>';
          }

          html += '<div class="fechamento-sala-foto">' +
            '<div class="fechamento-sala-header" data-action="toggle-sala-fotos" data-sala-id="' + sala.sala_id + '">' +
              '<svg class="fechamento-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>' +
              '<span style="font-weight:600">' + escapeHtml(sala.nome_sala) + '</span>' +
              badgeHtml +
              '<label class="btn btn-cyan btn-sm" style="cursor:pointer;margin-left:auto" data-action="stop-propagation">' +
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
        window.renderHTML(salasContainer, html);
        salasContainer.style.display = 'block';
      }
    }

    const geraisContainer = document.getElementById('fechamento-fotos-gerais-content');
    if (geraisContainer) {
      if (!data.gerais || data.gerais.length === 0) {
        window.renderHTML(geraisContainer, '<div style="text-align:center;padding:16px;color:var(--text-3)">Nenhuma foto geral</div>');
      } else {
        let html = '<div class="fechamento-foto-grid">';
        data.gerais.forEach((foto) => {
          let fotoPath = escapeHtml(foto.caminho_arquivo);
          if (!fotoPath.startsWith('http') && !fotoPath.startsWith('/')) {
            fotoPath = BASE_URL + '/' + fotoPath;
          }
          html += '<div class="fechamento-foto-item">' +
            '<a href="' + fotoPath + '" target="_blank">' +
              '<img src="' + fotoPath + '" alt="' + escapeHtml(foto.nome_arquivo) + '">' +
            '</a>' +
            '<button type="button" class="btn btn-sm btn-red" data-action="remover-foto" data-id="' + foto.id + '" title="Remover">&times;</button>' +
          '</div>';
        });
        html += '</div>';
        window.renderHTML(geraisContainer, html);
      }
    }
  }

  function uploadFoto(salaId, input) {
    if (!input.files || input.files.length === 0) return;

    Array.from(input.files).forEach((file) => {
      const formData = new FormData();
      formData.append('_csrf_token', CSRF_TOKEN);
      formData.append('evento_id', EVENTO_ID);
      if (salaId) formData.append('sala_id', salaId);
      formData.append('foto', file);

      fetch(BASE_URL + '/fechamento/foto/upload', {
        method: 'POST',
        body: formData
      })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          showToast('green', 'Sucesso', 'Foto enviada com sucesso');
          loadFotos();
        } else {
          showToast('red', 'Erro', data.error || 'Erro ao enviar foto');
        }
      })
      .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
    });

    input.value = '';
  }

  function removerFoto(fotoId, btn) {
    if (!confirm('Remover esta foto?')) return;

    const formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('evento_id', EVENTO_ID);
    formData.append('foto_id', fotoId);

    fetch(BASE_URL + '/fechamento/foto/remover', {
      method: 'POST',
      body: formData
    })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast('green', 'Sucesso', 'Foto removida');
        loadFotos();
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao remover');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro de conexao'); });
  }

  function toggleSalaFotos(salaId) {
    const content = document.getElementById('fech-sala-fotos-' + salaId);
    if (!content) return;
    const isVisible = content.style.display !== 'none';
    content.style.display = isVisible ? 'none' : 'block';
  }

  // ========================================
  // Outros Custos
  // ========================================
  function loadOutros() {
    if (!EVENTO_ID) return;
    showLoading('fechamento-outros');
    fetchJson(BASE_URL + '/fechamento/outros/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((data) => {
      hideLoading('fechamento-outros');
      if (!data.success || !data.data || data.data.length === 0) {
        showEmpty('fechamento-outros');
        return;
      }
      renderOutros(data.data);
      showContent('fechamento-outros');
    })
    .catch((err) => {
      hideLoading('fechamento-outros');
      showToast('red', 'Erro', 'Erro ao carregar outros custos');
    });
  }

  function renderOutros(lista) {
    const container = document.getElementById('fechamento-outros-content');
    if (!container) return;

    let html = '<table class="tbl" width="100%"><thead><tr>' +
      '<th>Descricao</th>' +
      '<th>Valor</th>' +
      '<th>Vencimento</th>' +
      '<th>Status</th>' +
      '<th>Acoes</th>' +
      '</tr></thead><tbody>';

    lista.forEach((o) => {
      const enviado = o.enviado_contas_pagar === 'S';
      const statusPago   = o.status === 'PAGO';
      const statusVencido = o.status === 'VENCIDO';

      let statusBadge = '';
      if (!enviado) {
        statusBadge = '<span class="badge sm yellow">Pendente</span>';
      } else if (statusPago) {
        statusBadge = '<span class="badge sm green">Pago</span>';
      } else if (statusVencido) {
        statusBadge = '<span class="badge sm red">Vencido</span>';
      } else {
        statusBadge = '<span class="badge sm blue">Enviado</span>';
      }

      const obs = o.observacao
        ? '<br><small style="font-size:11px;color:var(--text-3)">' + escapeHtml(o.observacao) + '</small>'
        : '';
      const nfLink = o.nota_fiscal
        ? '<br><a href="/' + escapeHtml(o.nota_fiscal) + '" target="_blank" style="font-size:11px;color:var(--primary)">&#128206; Ver NF</a>'
        : '';

      const popId = 'pop-outro-' + o.id;
      let acoesHtml =
        '<div class="td-actions"><div class="td-act-menu">' +
          '<button type="button" class="btn btn-sm td-act-toggle" data-action="toggle-pop" data-pop-id="' + popId + '">' +
            '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' +
            '</svg>' +
          '</button>' +
          '<div class="td-act-dropdown" id="' + popId + '">';

      if (!enviado) {
        acoesHtml +=
          '<button type="button" class="td-act-item td-act-cyan" data-action="editar-outro-custo" data-id="' + o.id + '" data-pop-id="' + popId + '"><span>Editar</span></button>' +
          '<button type="button" class="td-act-item td-act-red"  data-action="excluir-outro-custo" data-id="' + o.id + '" data-pop-id="' + popId + '"><span>Excluir</span></button>' +
          '<button type="button" class="td-act-item td-act-purple" data-action="enviar-outro-custo" data-id="' + o.id + '" data-pop-id="' + popId + '"><span>Enviar Pgto</span></button>';
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
    window.renderHTML(container, '<div class="table-wrap">' + html + '</div>');
  }

  function adicionarOutroCusto() {
    const modal = document.getElementById('modal-outro-custo');
    if (!modal) { showToast('red', 'Erro', 'Modal nao encontrado'); return; }

    document.getElementById('modal-outro-id').value = '0';
    document.getElementById('modal-outro-descricao').value = '';
    document.getElementById('modal-outro-valor').value = '';
    document.getElementById('modal-outro-vencimento').value = '';
    document.getElementById('modal-outro-obs').value = '';
    const nfInput = document.getElementById('modal-outro-nf');
    if (nfInput) nfInput.value = '';
    const nfNome = document.getElementById('modal-outro-nf-nome');
    if (nfNome) nfNome.textContent = 'Nenhum arquivo selecionado';
    const nfAtual = document.getElementById('modal-outro-nf-atual');
    if (nfAtual) nfAtual.textContent = '';

    const titleEl = modal.querySelector('.modal-title');
    if (titleEl) titleEl.textContent = 'Novo Outro Custo';

    openModal('modal-outro-custo');
  }

  function editarOutroCusto(id) {
    fetchJson(BASE_URL + '/fechamento/outros/' + EVENTO_ID, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then((resp) => {
      if (!resp.success || !resp.data) return;
      const item = resp.data.find((x) => parseInt(x.id) === parseInt(id));
      if (!item) { showToast('red', 'Erro', 'Custo nao encontrado'); return; }

      document.getElementById('modal-outro-id').value = item.id;
      document.getElementById('modal-outro-descricao').value = item.descricao || '';
      const v = parseFloat(item.valor || 0);
      document.getElementById('modal-outro-valor').value = v.toFixed(2).replace('.', ',');
      document.getElementById('modal-outro-vencimento').value = item.data_vencimento || '';
      document.getElementById('modal-outro-obs').value = item.observacao || '';

      const nfInput = document.getElementById('modal-outro-nf');
      if (nfInput) nfInput.value = '';
      const nfNome = document.getElementById('modal-outro-nf-nome');
      if (nfNome) nfNome.textContent = 'Nenhum arquivo selecionado';
      const nfAtual = document.getElementById('modal-outro-nf-atual');
      if (nfAtual) {
        window.renderHTML(nfAtual, item.nota_fiscal
          ? '📎 NF atual: <a href="/' + escapeHtml(item.nota_fiscal) + '" target="_blank">ver arquivo</a> — envie outro para substituir'
          : '');
      }

      const modal = document.getElementById('modal-outro-custo');
      const titleEl = modal ? modal.querySelector('.modal-title') : null;
      if (titleEl) titleEl.textContent = 'Editar Outro Custo';

      openModal('modal-outro-custo');
    })
    .catch(() => { showToast('red', 'Erro', 'Falha ao carregar dados'); });
  }

  function salvarOutroCusto() {
    const id = document.getElementById('modal-outro-id').value;
    const isNew = (!id || id === '0');
    const url = isNew
      ? BASE_URL + '/fechamento/outro/store'
      : BASE_URL + '/fechamento/outro/update/' + id;

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);
    fd.append('evento_id', EVENTO_ID);
    fd.append('descricao', document.getElementById('modal-outro-descricao').value.trim());
    fd.append('valor', document.getElementById('modal-outro-valor').value);
    fd.append('data_vencimento', document.getElementById('modal-outro-vencimento').value);
    fd.append('observacao', document.getElementById('modal-outro-obs').value.trim());
    const nfInput = document.getElementById('modal-outro-nf');
    if (nfInput && nfInput.files && nfInput.files[0]) {
      fd.append('nota_fiscal', nfInput.files[0]);
    }

    fetch(url, { method: 'POST', body: fd })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        closeModal('modal-outro-custo');
        showToast('green', 'Sucesso', isNew ? 'Custo adicionado' : 'Custo atualizado');
        loadOutros();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao salvar');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro ao processar requisicao'); });
  }

  function excluirOutroCusto(id) {
    if (!confirm('Tem certeza que deseja excluir este custo?')) return;

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/fechamento/outro/delete/' + id, {
      method: 'POST',
      body: fd
    })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast('green', 'Excluido', 'Custo removido com sucesso');
        loadOutros();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao excluir');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro ao processar'); });
  }

  function selecionarNF(input) {
    const nome = document.getElementById('modal-outro-nf-nome');
    if (nome) {
      nome.textContent = (input.files && input.files[0]) ? input.files[0].name : 'Nenhum arquivo selecionado';
    }
  }

  function enviarOutroCusto(id) {
    if (!confirm('Enviar este custo para pagamento em Contas a Pagar?\nApós o envio, edite ou exclua diretamente em /contas-pagar.')) return;

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/fechamento/outro/enviar/' + id, {
      method: 'POST',
      body: fd
    })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast('green', 'Enviado', 'Custo enviado para Contas a Pagar com sucesso');
        loadOutros();
        window.Fechamento.loadTotais();
      } else {
        showToast('red', 'Erro', data.error || 'Falha ao enviar para pagamento');
      }
    })
    .catch(() => { showToast('red', 'Erro', 'Erro ao processar requisicao'); });
  }

  // ========================================
  // Estender Public API
  // ========================================
  Object.assign(window.Fechamento, {
    uploadFoto:          uploadFoto,
    removerFoto:         removerFoto,
    toggleSalaFotos:     toggleSalaFotos,
    loadFotos:           loadFotos,
    loadOutros:          loadOutros,
    adicionarOutroCusto: adicionarOutroCusto,
    editarOutroCusto:    editarOutroCusto,
    salvarOutroCusto:    salvarOutroCusto,
    excluirOutroCusto:   excluirOutroCusto,
    selecionarNF:        selecionarNF,
    enviarOutroCusto:    enviarOutroCusto
  });

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste arquivo no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel. O
    // dispatcher generico so busca window[fnName] plano, nao window.Fechamento[fnName].
    if (window.registerAction) {
      window.registerAction('adicionar-outro-custo', function() {
        adicionarOutroCusto();
      });
      window.registerAction('salvar-outro-custo', function() {
        salvarOutroCusto();
      });
      window.registerAction('editar-outro-custo', function(el) {
        editarOutroCusto(el.dataset.id);
      });
      window.registerAction('excluir-outro-custo', function(el) {
        excluirOutroCusto(el.dataset.id);
      });
      window.registerAction('enviar-outro-custo', function(el) {
        enviarOutroCusto(el.dataset.id);
      });
      window.registerAction('remover-foto', function(el) {
        removerFoto(el.dataset.id, el);
      });
    }
  });
})();

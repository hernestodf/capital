/**
 * JavaScript - Tab: Fornecedores
 * Modulo: Eventos > Editar
 *
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST
 * - window.SALAS_MAP: Mapa de salas do evento
 */
(function() {
  'use strict';

  var EVENTO_ID = window.EVENTO_ID;
  var CSRF_TOKEN = window.CSRF_TOKEN;
  var SALAS_MAP = window.SALAS_MAP || {};
  var BASE_URL = window.BASE_URL || '';

  // Abrir tela dedicada de cotacao
  window.abrirCotacao = function(idEvento, idProdutoEvento, nomeProduto) {
    // Add timestamp to prevent browser caching
    var ts = Date.now();
    window.location.href = BASE_URL + '/eventos/cotacao/' + idEvento + '/' + idProdutoEvento + '?_=' + ts;
  };

  // Abrir modal de solicitacao rapida
  window.abrirModalSolicitacao = function(idEvento, idProdutoEvento, nomeProduto) {
    var modal = document.getElementById('modal-solicitacao-cotacao');
    if (!modal) {
      console.error('Modal de solicitacao nao encontrado');
      return;
    }

    document.getElementById('sol-id-evento').value = idEvento;
    document.getElementById('sol-id-produto-evento').value = idProdutoEvento;
    document.getElementById('sol-nome-item').textContent = nomeProduto;

    // Popular lista de fornecedores
    var container = document.getElementById('sol-fornecedores-list');
    var fornecedores = window.FORNECEDORES_MAP || [];
    var html = '';

    if (fornecedores.length === 0) {
      html = '<div style="padding:12px;text-align:center;color:var(--text-3);font-size:13px">Nenhum fornecedor cadastrado</div>';
    } else {
      fornecedores.forEach(function(f) {
        html += '<label style="display:flex;align-items:center;gap:8px;padding:6px 0;cursor:pointer">' +
          '<input type="checkbox" value="' + f.id + '" data-email="' + (f.email || '') + '" data-nome="' + (f.nome_fantasia || '') + '" />' +
          '<span style="font-size:13px">' + (f.nome_fantasia || f.razao_social) + '</span>' +
          '</label>';
      });
    }

    container.innerHTML = html;
    document.getElementById('sol-mensagem').value = '';
    document.getElementById('sol-anexo').value = '';

    openModal('modal-solicitacao-cotacao');
  };

  // Enviar solicitacao em massa
  window.enviarSolicitacao = function() {
    var idEvento = document.getElementById('sol-id-evento').value;
    var idProdutoEvento = document.getElementById('sol-id-produto-evento').value;
    var nomeItem = document.getElementById('sol-nome-item').textContent;
    var mensagem = document.getElementById('sol-mensagem').value;

    var checkboxes = document.querySelectorAll('#sol-fornecedores-list input[type="checkbox"]:checked');
    if (checkboxes.length === 0) {
      showToast('red', 'Erro', 'Selecione ao menos um fornecedor');
      return;
    }

    var fornecedores = [];
    checkboxes.forEach(function(cb) {
      fornecedores.push({
        id: parseInt(cb.value),
        email: cb.getAttribute('data-email'),
        nome_fantasia: cb.getAttribute('data-nome')
      });
    });

    var btn = document.querySelector('#modal-solicitacao-cotacao .btn-cyan');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    var formData = new FormData();
    formData.append('_csrf_token', CSRF_TOKEN);
    formData.append('id_evento', idEvento);
    formData.append('id_produto_evento', idProdutoEvento);
    formData.append('nome_item', nomeItem);
    formData.append('fornecedores', JSON.stringify(fornecedores));
    formData.append('mensagem', mensagem);

    var anexo = document.getElementById('sol-anexo').files[0];
    var anexoPromise = Promise.resolve(null);

    if (anexo) {
      var anexoForm = new FormData();
      anexoForm.append('_csrf_token', CSRF_TOKEN);
      anexoForm.append('anexo', anexo);

      anexoPromise = fetch(BASE_URL + '/cotacao/upload-anexo', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        credentials: 'same-origin',
        body: anexoForm
      })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (d.success) {
          formData.append('anexo_path', d.path);
        }
      });
    }

    anexoPromise.then(function() {
      return fetch(BASE_URL + '/cotacao/solicitar-cotacao', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        credentials: 'same-origin',
        body: formData
      });
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false;
      btn.textContent = 'Enviar Solicitações';

      if (d.success) {
        closeModal('modal-solicitacao-cotacao');
        showToast('green', 'Solicitacoes Enviadas', d.enviados + ' fornecedor(es) receberam a solicitacao');
        if (d.erros && d.erros.length > 0) {
          showToast('yellow', 'Atencao', d.erros.length + ' erro(s): ' + d.erros.slice(0, 2).join(', '));
        }
      } else {
        showToast('red', 'Erro', d.error || 'Erro ao enviar solicitacoes');
      }
    })
    .catch(function() {
      btn.disabled = false;
      btn.textContent = 'Enviar Solicitações';
      showToast('red', 'Erro', 'Erro de conexao');
    });
  };

  function init() {
    console.log('Fornecedores init - Evento ID:', EVENTO_ID);
  }

  if (document.readyState !== 'loading') { init(); }
  else { document.addEventListener('DOMContentLoaded', init); }
})();

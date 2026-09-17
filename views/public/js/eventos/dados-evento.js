/**
 * JavaScript - Tab: Dados do Evento (VTab 0)
 * Modulo: Eventos > Editar
 * 
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado (USADO)
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST (USADO)
 * - window.SALAS_MAP: Mapa de salas do evento
 */

// Salvar evento via AJAX
function salvarEvento() {
  var form = document.getElementById('form-evento');
  if (!form) return;

  var btn = document.getElementById('btn-save-evento');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Salvando...';
  }

  var formData = new FormData(form);

  fetch(BASE_URL + '/eventos/update/' + window.EVENTO_ID, {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Salvar Alteracoes';
    }
    if (data.success) {
      showToast('green', 'Sucesso', 'Evento atualizado com sucesso!');
    } else if (data.error) {
      showToast('red', 'Erro', data.error);
    } else {
      showToast('red', 'Erro', 'Erro ao atualizar evento.');
    }
  })
  .catch(function(err) {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Salvar Alteracoes';
    }
    showToast('red', 'Erro', 'Erro de conexao ao servidor.');
    console.error(err);
  });
}

// Finalizar locacao via AJAX
function finalizarLocacao() {
  var obs = document.getElementById('observacoes_fechamento');
  var obsVal = obs ? obs.value : '';

  if (!confirm('Tem certeza que deseja finalizar esta locacao? Esta acao nao pode ser desfeita.')) {
    return;
  }

  var formData = new FormData();
  formData.append('_csrf_token', window.CSRF_TOKEN);
  formData.append('observacoes_fechamento', obsVal);

  fetch(BASE_URL + '/eventos/finalizar/' + window.EVENTO_ID, {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      showToast('green', 'Sucesso', 'Locacao finalizada com sucesso!');
      setTimeout(function() { window.location.reload(); }, 1500);
    } else if (data.error) {
      showToast('red', 'Erro', data.error);
    } else {
      showToast('red', 'Erro', 'Erro ao finalizar locacao.');
    }
  })
  .catch(function(err) {
    showToast('red', 'Erro', 'Erro de conexao ao servidor.');
    console.error(err);
  });
}

/**
 * JavaScript - Tab: Dados do Evento (VTab 0)
 * Modulo: Eventos > Editar
 */

// Salvar evento via AJAX
function salvarEvento() {
  const form = document.getElementById('form-evento');
  if (!form) return;

  let btn = document.getElementById('btn-save-evento');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Salvando...';
  }

  const formData = new FormData(form);

  fetch(window.BASE_URL + '/eventos/update/' + window.EVENTO_ID, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then((data) => {
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
  .catch((err) => {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Salvar Alteracoes';
    }
    showToast('red', 'Erro', 'Erro de conexao ao servidor.');
  });
}

// Finalizar locacao via AJAX
function finalizarLocacao() {
  const obs = document.getElementById('observacoes_fechamento');
  const obsVal = obs ? obs.value : '';

  if (!confirm('Tem certeza que deseja finalizar esta locacao? Esta acao nao pode ser desfeita.')) {
    return;
  }

  const formData = new FormData();
  formData.append('_csrf_token', window.CSRF_TOKEN);
  formData.append('observacoes_fechamento', obsVal);

  fetch(window.BASE_URL + '/eventos/finalizar/' + window.EVENTO_ID, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then((data) => {
    if (data.success) {
      showToast('green', 'Sucesso', 'Locacao finalizada com sucesso!');
      setTimeout(() => { window.location.reload(); }, 1500);
    } else if (data.error) {
      showToast('red', 'Erro', data.error);
    } else {
      showToast('red', 'Erro', 'Erro ao finalizar locacao.');
    }
  })
  .catch((err) => {
    showToast('red', 'Erro', 'Erro de conexao ao servidor.');
  });
}

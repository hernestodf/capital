/**
 * Helpers para Error Handling em Fetch
 * Centralizado para todos os módulos de eventos
 */

// Helper para mostrar erros
function showFetchError(title, message) {
  console.error('[FETCH ERROR] ' + title + ': ' + message);

  // Se showToast está disponível, usa
  if (typeof showToast === 'function') {
    showToast('red', title || 'Erro', message || 'Falha na requisição');
  } else {
    // Fallback: alert
    alert(title + '\n' + message);
  }
}

// Helper para processar resposta JSON com validação HTTP
function validateResponse(response, context) {
  if (!response.ok) {
    throw new Error('HTTP ' + response.status + ' — ' + (context || 'erro na requisição'));
  }
  return response.json();
}

// Padrão padrão para tratamento de erros em fetch
// Uso:
//   fetch(url, options)
//     .then(r => validateResponse(r, 'Carregando dados'))
//     .then(data => { /* handle success */ })
//     .catch(err => handleFetchError(err, 'Erro ao carregar dados'))
function handleFetchError(error, title) {
  const message = error.message || 'Erro desconhecido';
  showFetchError(title || 'Erro', message);
  console.error(error);
}

// Export para módulos que usam ES6
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { showFetchError, validateResponse, handleFetchError };
}

/**
 * JavaScript - Tab: Devolver OS
 * Modulo: Eventos > Editar
 * 
 * Variaveis globais disponiveis:
 * - window.EVENTO_ID: ID do evento sendo editado
 * - window.CSRF_TOKEN: Token CSRF para requisicoes POST
 * - window.SALAS_MAP: Mapa de salas do evento
 */
(function() {
  'use strict';
  
  // Capturar variaveis globais
  var EVENTO_ID = window.EVENTO_ID;
  var CSRF_TOKEN = window.CSRF_TOKEN;
  var SALAS_MAP = window.SALAS_MAP || {};
  
  function init() {
    // Inicializar funcionalidades da tab Devolver OS
    // Usar EVENTO_ID para requisicoes especificas do evento
    console.log('Devolver OS init - Evento ID:', EVENTO_ID);
  }

  if (document.readyState !== 'loading') { init(); }
  else { document.addEventListener('DOMContentLoaded', init); }
})();

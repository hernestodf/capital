/**
 * JavaScript - Tab: Montar OS
 * Modulo: Eventos > Editar
 */
(function() {
  'use strict';
  
  function init() {
    if (window.MontarOsSerial) {
      window.MontarOsSerial.initSerialInputs();
    }

    const salasSelects = document.querySelectorAll('.sala-destino-select');

    salasSelects.forEach((select) => {
      select.addEventListener('change', function() {
        if (window.MontarOsSerial) {
          window.MontarOsSerial.atualizarItensDaSala(this);
        }
      });
    });

  }

  const pane = document.getElementById('loc-hpane-1');
  if (pane) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class' && pane.classList.contains('active')) {
          setTimeout(init, 150);
        }
      });
    });
    observer.observe(pane, { attributes: true });
    
    if (pane.classList.contains('active')) {
      setTimeout(init, 200);
    }
  } else {
    if (document.readyState !== 'loading') { init(); }
    else { document.addEventListener('DOMContentLoaded', init); }
  }
  
})();

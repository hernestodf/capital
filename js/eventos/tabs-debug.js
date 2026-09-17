/**
 * DEBUG: Verificar se as funções de tabs estão funcionando
 * Use no console do navegador para diagnosticar problemas
 */

console.log('=== TAB FUNCTIONS DEBUG ===');

// 1. Verificar se as funções existem
console.log('switchHTab função existe?', typeof window.switchHTab !== 'undefined');
console.log('switchVTab função existe?', typeof window.switchVTab !== 'undefined');

// 2. Verificar se o DOM tem elementos de tabs
var htabs = document.querySelectorAll('.loc-htab');
var hpanes = document.querySelectorAll('.loc-hpane');
var vtabs = document.querySelectorAll('.loc-vtab');
var vpanes = document.querySelectorAll('.loc-vpane');

console.log('Elementos encontrados:');
console.log('- .loc-htab:', htabs.length);
console.log('- .loc-hpane:', hpanes.length);
console.log('- .loc-vtab:', vtabs.length);
console.log('- .loc-vpane:', vpanes.length);

// 3. Verificar se os onclick estão ligados
if (htabs.length > 0) {
  var firstHtab = htabs[0];
  console.log('Primeiro .loc-htab onclick:', firstHtab.getAttribute('onclick'));
  console.log('Primeiro .loc-htab classList:', firstHtab.className);
}

// 4. Testar manualmente uma tab
window.debugSwitchHTab = function(idx) {
  console.log('Testando switchHTab(' + idx + ')');
  if (typeof switchHTab === 'function') {
    var el = document.querySelector('.loc-htab');
    if (el) {
      switchHTab(idx, el);
      console.log('✓ switchHTab executou');
    }
  } else {
    console.error('✗ switchHTab não é uma função!');
  }
};

console.log('=== PARA TESTAR ===');
console.log('debugSwitchHTab(1)  // para trocar para aba 1');
console.log('================');

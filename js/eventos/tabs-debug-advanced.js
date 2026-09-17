/**
 * DEBUG AVANÇADO: Tabs não funcionam nas abas 1-5
 * Use no Console do navegador (F12)
 */

console.log('╔════════════════════════════════════════╗');
console.log('║   DEBUG AVANÇADO - TABS PROBLEMA      ║');
console.log('╚════════════════════════════════════════╝');
console.log('');

// 1. VERIFICAR FUNÇÃO EXISTE
console.log('1️⃣  FUNÇÃO switchHTab EXISTE?');
console.log('   ' + (typeof switchHTab === 'function' ? '✓ SIM' : '✗ NÃO'));
console.log('');

// 2. VERIFICAR ELEMENTOS HTML
console.log('2️⃣  ELEMENTOS HTML');
var htabs = document.querySelectorAll('.loc-htab');
var hpanes = document.querySelectorAll('.loc-hpane');
console.log('   Botões .loc-htab: ' + htabs.length);
console.log('   Panes .loc-hpane: ' + hpanes.length);
console.log('');

// 3. VERIFICAR IDs DAS PANES
console.log('3️⃣  IDs DAS PANES');
for (let i = 0; i < 6; i++) {
  let pane = document.getElementById('loc-hpane-' + i);
  console.log('   loc-hpane-' + i + ': ' + (pane ? '✓ EXISTE' : '✗ NÃO EXISTE'));
}
console.log('');

// 4. VERIFICAR CLASSE ACTIVE
console.log('4️⃣  CLASSE ACTIVE');
console.log('   Tabs com .active: ' + document.querySelectorAll('.loc-htab.active').length);
console.log('   Panes com .active: ' + document.querySelectorAll('.loc-hpane.active').length);
console.log('');

// 5. VERIFICAR CSS DISPLAY
console.log('5️⃣  CSS DISPLAY (inline styles)');
for (let i = 0; i < 6; i++) {
  let pane = document.getElementById('loc-hpane-' + i);
  if (pane) {
    let display = window.getComputedStyle(pane).display;
    let isActive = pane.classList.contains('active');
    console.log('   loc-hpane-' + i + ': display=' + display + ', active=' + isActive);
  }
}
console.log('');

// 6. TESTAR CLICK EM CADA TAB
console.log('6️⃣  TESTE DE CLICK');
console.log('   Clicando em cada aba...');
console.log('');

for (let i = 0; i < 6; i++) {
  (function(idx) {
    setTimeout(function() {
      console.log('   [Test ' + idx + '] Clicando em aba ' + idx);
      let tab = document.querySelectorAll('.loc-htab')[idx];
      if (tab) {
        switchHTab(idx, tab);

        // Verificar resultado
        setTimeout(function() {
          let pane = document.getElementById('loc-hpane-' + idx);
          let isActive = pane && pane.classList.contains('active');
          let display = pane ? window.getComputedStyle(pane).display : 'N/A';
          console.log('        Resultado: active=' + isActive + ', display=' + display);
        }, 100);
      }
    }, i * 500);
  })(i);
}

console.log('');
console.log('════════════════════════════════════════');
console.log('Aguarde 3 segundos para ver todos os testes...');
console.log('════════════════════════════════════════');

// 7. RESUMO FINAL
setTimeout(function() {
  console.log('');
  console.log('📊 RESUMO FINAL:');

  let allHpanes = document.querySelectorAll('.loc-hpane');
  let anyActive = false;
  let anyDisplay = false;

  for (let i = 0; i < allHpanes.length; i++) {
    let pane = allHpanes[i];
    let isActive = pane.classList.contains('active');
    let display = window.getComputedStyle(pane).display;

    if (isActive) anyActive = true;
    if (display !== 'none') anyDisplay = true;
  }

  console.log('');
  console.log('Alguma pane tem .active? ' + (anyActive ? '✓ SIM' : '✗ NÃO'));
  console.log('Alguma pane está visível (display ≠ none)? ' + (anyDisplay ? '✓ SIM' : '✗ NÃO'));
  console.log('');

  if (!anyActive || !anyDisplay) {
    console.log('⚠️  PROBLEMA ENCONTRADO:');
    if (!anyActive) console.log('   - Nenhuma pane tem classe .active');
    if (!anyDisplay) console.log('   - Nenhuma pane está visível (display: none)');
    console.log('');
    console.log('POSSÍVEIS CAUSAS:');
    console.log('1. CSS .loc-hpane { display: none } não está sendo respeitado');
    console.log('2. CSS .loc-hpane.active { display: block } não está funcionando');
    console.log('3. Há algum style inline sobrescrevendo o display');
    console.log('4. Há algum JavaScript removendo a classe .active indevidamente');
  } else {
    console.log('✓ Tabs parecem estar funcionando corretamente');
  }
}, 3500);

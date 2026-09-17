<?php
// Partial: Fechamento (HPane 5) - Main container with 4 vertical tabs (Colaboradores, Fornecedores, Fotos & PDF, Outros Custos)
// Variaveis esperadas: $estado, $statusLocacao, $evento, $csrfToken, $eventoId
?>

<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/tabs/tabs.php';

$statsGrid = '<div id="fechamento-totais" style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:16px">'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-receita" style="font-size:20px;color:var(--neon-green)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Total Locação</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-v2" style="font-size:16px;color:var(--neon-purple)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:10px">—</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-c2" style="font-size:16px;color:var(--purple)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:10px">—</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-v3" style="font-size:16px;color:var(--neon-orange)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:10px">—</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-c3" style="font-size:16px;color:var(--amber)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:10px">—</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-colab" style="font-size:20px;color:var(--neon-cyan)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Colaboradores</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-forn" style="font-size:20px;color:var(--neon-purple)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Fornecedores</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-outros" style="font-size:20px;color:var(--neon-orange)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Outros Custos</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-custo" style="font-size:20px;color:var(--neon-red)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Custo Total</div></div>'
    . '<div class="card-stat" style="padding:14px 10px;border-radius:10px"><div class="card-stat-val" id="ft-lucro" style="font-size:20px;color:var(--neon-green)">R$ 0,00</div><div class="card-stat-lbl" style="font-size:11px">Lucro</div></div>'
    . '</div><style>@media(max-width:1400px){#fechamento-totais{grid-template-columns:repeat(5,1fr)!important}}@media(max-width:1024px){#fechamento-totais{grid-template-columns:repeat(3,1fr)!important}}@media(max-width:640px){#fechamento-totais{grid-template-columns:repeat(2,1fr)!important}}@media(max-width:400px){#fechamento-totais{grid-template-columns:1fr!important}}</style>';
?>
<?php if ($estado !== 'L'): ?>
<!-- Banner: Orcamento/Pedido — informativo, nao bloqueia -->
<div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(245,158,11,0.08);border-radius:8px;border:1px solid rgba(245,158,11,0.2);margin-bottom:16px">
  <svg fill="none" viewBox="0 0 24 24" stroke="var(--neon-yellow)" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  <div>
    <div style="font-weight:600;color:var(--neon-yellow);font-size:13px">Orcamento</div>
    <div style="font-size:12px;color:var(--text-3)">Este evento ainda nao foi convertido em locacao. O fechamento esta disponivel para gestao antecipada.</div>
  </div>
</div>
<?php endif; ?>

<!-- Cards de totais (sempre visiveis) -->
<?= $statsGrid ?>

<!-- Status da locacao -->
<?php if ($estado === 'L' && $statusLocacao === 'A'): ?>
<div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(6,182,212,0.08);border-radius:8px;border:1px solid rgba(6,182,212,0.2);margin-bottom:20px">
  <svg fill="none" viewBox="0 0 24 24" stroke="var(--neon-cyan)" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <div>
    <div style="font-weight:600;color:var(--neon-cyan);font-size:13px">Locacao em Andamento</div>
    <div style="font-size:12px;color:var(--text-3)">Gerencie colaboradores, fornecedores e fotos antes de finalizar.</div>
  </div>
</div>
<?php elseif ($statusLocacao === 'F'): ?>
<div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(34,197,94,0.08);border-radius:8px;border:1px solid rgba(34,197,94,0.2);margin-bottom:20px">
  <svg fill="none" viewBox="0 0 24 24" stroke="var(--neon-green)" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
     <div style="font-weight:600;color:var(--neon-green);font-size:13px">Locacao Finalizada</div>
     <div style="font-size:12px;color:var(--text-3)">Esta locacao ja foi finalizada.</div>
   </div>
 </div>
 <?php elseif ($statusLocacao === 'T'): ?>
 <div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(16,185,129,0.08);border-radius:8px;border:1px solid rgba(16,185,129,0.2);margin-bottom:20px">
   <svg fill="none" viewBox="0 0 24 24" stroke="var(--emerald)" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
   <div>
     <div style="font-weight:600;color:var(--emerald);font-size:13px">Locacao Faturada</div>
     <div style="font-size:12px;color:var(--text-3)">Esta locacao foi finalizada e faturada.</div>
   </div>
 </div>
 <?php endif; ?>

<!-- Tabs Verticais usando componente Design System -->
<?php
ob_start();
require __DIR__ . '/edit-fechamento-locacao.php';
$locacaoContent = ob_get_clean();

ob_start();
require __DIR__ . '/edit-fechamento-colaboradores.php';
$colabContent = ob_get_clean();

ob_start();
require __DIR__ . '/edit-fechamento-fornecedores.php';
$fornecedorContent = ob_get_clean();

ob_start();
require __DIR__ . '/edit-fechamento-fotos.php';
$fotosContent = ob_get_clean();

ob_start();
require __DIR__ . '/edit-fechamento-outros.php';
$outrosContent = ob_get_clean();

echo renderTabsVertical([
    'id' => 'fechamento-tabs',
    'activeIndex' => 0,
    'tabs' => [
        [
            'label' => 'Locação',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
            'content' => $locacaoContent,
        ],
        [
            'label' => 'Colaboradores',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'content' => $colabContent,
        ],
        [
            'label' => 'Fornecedores',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
            'content' => $fornecedorContent,
        ],
        [
            'label' => 'Fotos & PDF',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
            'content' => $fotosContent,
        ],
        [
            'label' => 'Outros Custos',
            'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
            'content' => $outrosContent,
        ],
    ]
]);
?>

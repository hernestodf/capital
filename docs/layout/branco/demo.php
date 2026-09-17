<?php
// Carregar todos os componentes
require_once __DIR__ . '/assets/components/tabs/tabs.php';
require_once __DIR__ . '/assets/components/modal/modal.php';
require_once __DIR__ . '/assets/components/toast/toast.php';
require_once __DIR__ . '/assets/components/accordion/accordion.php';
require_once __DIR__ . '/assets/components/alert/alert.php';
require_once __DIR__ . '/assets/components/card/card.php';
require_once __DIR__ . '/assets/components/badge/badge.php';
require_once __DIR__ . '/assets/components/input/input.php';
require_once __DIR__ . '/assets/components/table/table.php';
require_once __DIR__ . '/assets/components/progress/progress.php';
require_once __DIR__ . '/assets/components/stepper/stepper.php';
require_once __DIR__ . '/assets/components/timeline/timeline.php';
require_once __DIR__ . '/assets/components/avatar/avatar.php';
require_once __DIR__ . '/assets/components/chip/chip.php';
require_once __DIR__ . '/assets/components/spinner/spinner.php';
require_once __DIR__ . '/assets/components/toggle/toggle.php';
require_once __DIR__ . '/assets/components/listitem/listitem.php';
require_once __DIR__ . '/assets/components/popover/popover.php';
require_once __DIR__ . '/assets/components/skeleton/skeleton.php';
require_once __DIR__ . '/assets/components/rating/rating.php';
require_once __DIR__ . '/assets/components/button/button.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Design System 3.0 — Componentes Reutilizáveis</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Open+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Lilita+One&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css"/>
</head>
<body>

<!-- OVERLAYS -->
<div class="overlay" id="overlay-left" onclick="closeLeft()"></div>
<div class="overlay" id="overlay-right" onclick="closeRight()"></div>

<!-- LEFT OFF-CANVAS -->
<aside id="left-canvas">
  <div class="lc-head">
    <div><div class="lc-title">Filtros & Navegação</div><div class="lc-sub">Refine a visualização</div></div>
    <button class="close-x" onclick="closeLeft()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="lc-body">
    <div class="lc-section">
      <div class="lc-sec-lbl">Período</div>
      <div class="lc-chips"><div class="lc-chip active" onclick="selectChip(this)">Hoje</div><div class="lc-chip" onclick="selectChip(this)">7 dias</div><div class="lc-chip" onclick="selectChip(this)">30 dias</div></div>
    </div>
  </div>
  <div class="lc-foot"><button class="lc-btn-clear" onclick="closeLeft()">Cancelar</button><button class="lc-btn-apply" onclick="closeLeft()">Aplicar</button></div>
</aside>

<!-- RIGHT OFF-CANVAS -->
<aside id="right-canvas">
  <div class="rc-head">
    <div class="rc-top"><span class="rc-title">Minha Conta</span><button class="close-x" onclick="closeRight()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
  </div>
  <div class="rc-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px">
    <div style="font-size:48px">&#128100;</div>
    <div style="color:#fff;font-size:16px;font-weight:700">Marco Antônio</div>
    <div style="color:rgba(255,255,255,0.5);font-size:13px">Administrador</div>
  </div>
</aside>

<!-- FAB -->
<button class="fab" onclick="openLeft()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h4"/></svg></button>

<!-- SIDEBAR -->
<aside id="sidebar">
  <div class="sb-head">
    <div class="logo-wrap">
      <div class="logo-ico" onclick="openSidebar()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/></svg></div>
      <div><div class="logo-name">SisLoc</div><div class="logo-ver">v3.0 · White</div></div>
    </div>
    <button class="collapse-btn" onclick="closeSidebar()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg></button>
  </div>
  <div class="sb-scroll">
    <nav style="padding:8px 0">
      <div class="ni active" onclick="showSection('sec-overview',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span class="ni-label">Visão Geral</span></div><div class="ni-tooltip">Visão Geral</div></div>
      <div class="nav-lbl">Componentes</div>
      <div class="ni" onclick="showSection('sec-buttons',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg><span class="ni-label">Buttons</span></div><div class="ni-tooltip">Buttons</div></div>
      <div class="ni" onclick="showSection('sec-tabs',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 13h6M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg><span class="ni-label">Tabs</span></div><div class="ni-tooltip">Tabs</div></div>
      <div class="ni" onclick="showSection('sec-modals',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg><span class="ni-label">Modals</span></div><div class="ni-tooltip">Modals</div></div>
      <div class="ni" onclick="showSection('sec-toasts',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg><span class="ni-label">Toasts</span></div><div class="ni-tooltip">Toasts</div></div>
      <div class="ni" onclick="showSection('sec-forms',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg><span class="ni-label">Forms</span></div><div class="ni-tooltip">Forms</div></div>
      <div class="ni" onclick="showSection('sec-cards',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg><span class="ni-label">Cards</span></div><div class="ni-tooltip">Cards</div></div>
      <div class="ni" onclick="showSection('sec-tables',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg><span class="ni-label">Tables</span></div><div class="ni-tooltip">Tables</div></div>
      <div class="nav-lbl">Feedback</div>
      <div class="ni" onclick="showSection('sec-alerts',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span class="ni-label">Alerts</span></div><div class="ni-tooltip">Alerts</div></div>
      <div class="ni" onclick="showSection('sec-progress',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span class="ni-label">Progress</span></div><div class="ni-tooltip">Progress</div></div>
      <div class="ni" onclick="showSection('sec-spinners',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg><span class="ni-label">Spinners</span></div><div class="ni-tooltip">Spinners</div></div>
      <div class="nav-lbl">Data Display</div>
      <div class="ni" onclick="showSection('sec-accordion',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg><span class="ni-label">Accordion</span></div><div class="ni-tooltip">Accordion</div></div>
      <div class="ni" onclick="showSection('sec-timeline',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="ni-label">Timeline</span></div><div class="ni-tooltip">Timeline</div></div>
      <div class="ni" onclick="showSection('sec-stepper',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg><span class="ni-label">Stepper</span></div><div class="ni-tooltip">Stepper</div></div>
      <div class="ni" onclick="showSection('sec-misc',this)"><div class="ni-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg><span class="ni-label">Avatares & Mais</span></div><div class="ni-tooltip">Avatares & Mais</div></div>
    </nav>
  </div>
  <div class="sb-foot">
    <div class="user-card" onclick="openRight()">
      <div class="avatar">MA</div>
      <div class="user-texts"><div class="u-name">Marco Antônio</div><div class="u-role">Administrador</div></div>
    </div>
  </div>
</aside>

<!-- TOPBAR -->
<header id="topbar">
  <div class="tb-left"><div class="breadcrumb"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg><span>Início</span><span class="sep">/</span><span class="cur" id="breadcrumb-cur">Visão Geral</span></div></div>
  <div class="tb-right">
    <div class="avatar" onclick="openRight()">MA</div>
  </div>
</header>

<!-- MAIN CONTENT -->
<div id="main">
  <div class="content">

    <!-- ==================== OVERVIEW ==================== -->
    <section class="section active" id="sec-overview">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></div><div><div class="section-title">Componentes Reutilizáveis</div><div class="section-sub">Todos os componentes PHP renderizados dinamicamente</div></div></div>
      <div class="divider"></div>

      <div class="col3">
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', 'value' => '21', 'label' => 'Componentes PHP', 'color' => 'var(--neon-cyan)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>', 'value' => '50+', 'label' => 'Variantes', 'color' => 'var(--neon-green)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', 'value' => '5', 'label' => 'Tipos de Tabs', 'color' => 'var(--neon-purple)']); ?>
      </div>

      <div class="card" style="margin-top:16px"><div class="card-body">
        <p style="color:var(--text-2);line-height:1.8;margin-bottom:16px">Esta página demonstra <strong>todos</strong> os componentes PHP reutilizáveis do Design System 3.0. Use o menu lateral para navegar entre as seções.</p>
        <div class="btn-row">
          <?php echo renderButton(['label' => 'Abrir Modal', 'variant' => 'cyan', 'onclick' => "openModal('demo-modal')"]); ?>
          <?php echo renderToastTrigger(['label' => 'Toast Sucesso', 'variant' => 'green', 'type' => 'green', 'title' => 'Sucesso!', 'message' => 'Componentes carregados.']); ?>
          <?php echo renderToastTrigger(['label' => 'Toast Erro', 'variant' => 'red', 'type' => 'red', 'title' => 'Erro!', 'message' => 'Algo deu errado.']); ?>
          <?php echo renderToastTrigger(['label' => 'Toast Info', 'variant' => 'cyan', 'type' => 'cyan', 'title' => 'Info', 'message' => 'Dados atualizados.']); ?>
        </div>
      </div></div>
    </section>

    <!-- ==================== BUTTONS ==================== -->
    <section class="section" id="sec-buttons">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg></div><div><div class="section-title">Buttons</div><div class="section-sub">renderButton($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-head"><span class="card-title">Botões Sólidos</span></div><div class="card-body"><div class="btn-row">
        <?php
        foreach (['red','green','cyan','blue','purple','orange','yellow'] as $v) {
          echo renderButton(['label' => ucfirst($v), 'variant' => $v]);
        }
        echo renderButton(['label' => 'Ghost', 'variant' => 'ghost']);
        ?>
      </div></div></div>

      <div class="card"><div class="card-head"><span class="card-title">Tamanhos & Estados</span></div><div class="card-body"><div class="btn-row">
        <?php echo renderButton(['label' => 'Extra Large', 'variant' => 'cyan', 'size' => 'xl']); ?>
        <?php echo renderButton(['label' => 'Large', 'variant' => 'cyan', 'size' => 'lg']); ?>
        <?php echo renderButton(['label' => 'Default', 'variant' => 'cyan']); ?>
        <?php echo renderButton(['label' => 'Small', 'variant' => 'cyan', 'size' => 'sm']); ?>
        <?php echo renderButton(['label' => 'Disabled', 'variant' => 'cyan', 'disabled' => true]); ?>
        <?php echo renderButton(['label' => 'Loading', 'variant' => 'cyan', 'loading' => true]); ?>
        <?php echo renderButton(['label' => 'Outline', 'variant' => 'outline-cyan']); ?>
      </div></div></div>

      <div class="card"><div class="card-head"><span class="card-title">Com Ícones</span></div><div class="card-body"><div class="btn-row">
        <?php echo renderButton(['label' => 'Novo', 'variant' => 'cyan', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>']); ?>
        <?php echo renderButton(['label' => 'Confirmar', 'variant' => 'green', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>']); ?>
        <?php echo renderButton(['label' => 'Excluir', 'variant' => 'red', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>']); ?>
      </div></div></div>

      <div class="card"><div class="card-head"><span class="card-title">Botões Ícone</span></div><div class="card-body">
        <div style="margin-bottom:12px;font-size:13px;color:var(--text-3);font-weight:600">16×16 · icon-xs</div>
        <div class="btn-row" style="margin-bottom:20px">
          <?php foreach (['red','green','cyan','purple','orange'] as $v) { echo renderButton(['label' => '', 'variant' => $v, 'size' => 'icon-xs', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>']); } ?>
          <?php echo renderButton(['label' => '', 'variant' => 'ghost', 'size' => 'icon-xs', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>']); ?>
        </div>
        <div style="margin-bottom:12px;font-size:13px;color:var(--text-3);font-weight:600">32×32 · icon-md</div>
        <div class="btn-row" style="margin-bottom:20px">
          <?php foreach (['red','green','cyan','purple','orange'] as $v) { echo renderButton(['label' => '', 'variant' => $v, 'size' => 'icon-md', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>']); } ?>
          <?php echo renderButton(['label' => '', 'variant' => 'ghost', 'size' => 'icon-md', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>']); ?>
        </div>
        <div style="margin-bottom:12px;font-size:13px;color:var(--text-3);font-weight:600">36×36 · icon-sm &nbsp;|&nbsp; 42×42 · icon &nbsp;|&nbsp; 52×52 · icon-lg</div>
        <div class="btn-row">
          <?php echo renderButton(['label' => '', 'variant' => 'cyan', 'size' => 'icon-sm', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>']); ?>
          <?php echo renderButton(['label' => '', 'variant' => 'cyan', 'size' => 'icon', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>']); ?>
          <?php echo renderButton(['label' => '', 'variant' => 'cyan', 'size' => 'icon-lg', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>']); ?>
        </div>
      </div></div>
    </section>

    <!-- ==================== TABS ==================== -->
    <section class="section" id="sec-tabs">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 13h6M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg></div><div><div class="section-title">Tabs</div><div class="section-sub">5 variantes: Color, Underline, Pill, Segmented, Vertical</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabs Coloridas</span></div><div class="card-body">
        <?php echo renderTabsColor([
          'id' => 'tc',
          'tabs' => [
            ['label' => 'Resumo', 'color' => 'red', 'active' => true, 'content' => '<div class="tab-demo"><strong>Resumo Executivo</strong> — KPIs principais do sistema com indicadores de performance.</div>'],
            ['label' => 'Locações', 'color' => 'cyan', 'content' => '<div class="tab-demo"><strong>Locações</strong> — Contratos ativos e pendentes com detalhamento completo.</div>'],
            ['label' => 'Clientes', 'color' => 'green', 'content' => '<div class="tab-demo"><strong>Clientes</strong> — Base de clientes ativa com histórico de locações.</div>'],
            ['label' => 'Estoque', 'color' => 'yellow', 'content' => '<div class="tab-demo"><strong>Estoque</strong> — Disponibilidade de equipamentos por categoria.</div>'],
          ]
        ]); ?>
      </div></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabs Underline</span></div><div class="card-body">
        <?php echo renderTabsUnderline([
          'id' => 'tu',
          'tabs' => [
            ['label' => 'Geral', 'active' => true, 'content' => '<div class="tab-demo"><strong>Visão Geral</strong> — Informações consolidadas do sistema.</div>'],
            ['label' => 'Detalhes', 'content' => '<div class="tab-demo"><strong>Detalhes</strong> — Informações específicas e granulares.</div>'],
            ['label' => 'Configurações', 'content' => '<div class="tab-demo"><strong>Configurações</strong> — Ajustes e preferências do usuário.</div>'],
          ]
        ]); ?>
      </div></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabs Pill</span></div><div class="card-body">
        <?php echo renderTabsPill([
          'id' => 'tp',
          'tabs' => [
            ['label' => 'Hoje', 'active' => true, 'content' => '<div class="tab-demo"><strong>Hoje</strong> — Dados do dia atual.</div>'],
            ['label' => 'Semana', 'content' => '<div class="tab-demo"><strong>Essa Semana</strong> — Acumulado semanal.</div>'],
            ['label' => 'Mês', 'content' => '<div class="tab-demo"><strong>Esse Mês</strong> — Acumulado mensal.</div>'],
          ]
        ]); ?>
      </div></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabs Segmented</span></div><div class="card-body">
        <?php echo renderTabsSegmented([
          'id' => 'ts',
          'tabs' => [
            ['label' => 'Lista', 'active' => true, 'content' => '<div class="tab-demo"><strong>Visualização em Lista</strong> — Itens organizados verticalmente.</div>'],
            ['label' => 'Grade', 'content' => '<div class="tab-demo"><strong>Visualização em Grade</strong> — Cards organizados em grid.</div>'],
            ['label' => 'Tabela', 'content' => '<div class="tab-demo"><strong>Visualização em Tabela</strong> — Dados tabulares com ordenação.</div>'],
          ]
        ]); ?>
      </div></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabs Vertical</span></div><div class="card-body">
        <?php echo renderTabsVertical([
          'id' => 'tv',
          'tabs' => [
            ['label' => 'Perfil', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>', 'active' => true, 'content' => '<div style="margin-bottom:16px"><strong>Perfil do Usuário</strong></div>' . renderInput(['label' => 'Nome', 'type' => 'text', 'name' => 'nome', 'value' => 'Marco']) . renderInput(['label' => 'Email', 'type' => 'email', 'name' => 'email', 'value' => 'marco@sisloc.com'])],
            ['label' => 'Senha', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', 'content' => '<div style="margin-bottom:16px"><strong>Alterar Senha</strong></div>' . renderInput(['label' => 'Senha Atual', 'type' => 'password', 'name' => 'senha_atual', 'placeholder' => '••••••••']) . renderInput(['label' => 'Nova Senha', 'type' => 'password', 'name' => 'senha_nova', 'placeholder' => 'Mínimo 8 caracteres'])],
            ['label' => 'Notificações', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>', 'content' => '<div style="margin-bottom:16px"><strong>Preferências de Notificação</strong></div>' . renderToggleRow(['label' => 'E-mail de alertas', 'checked' => true]) . renderToggleRow(['label' => 'Notificações no sistema', 'checked' => true]) . renderToggleRow(['label' => 'Autenticação 2FA', 'checked' => false])],
          ]
        ]); ?>
      </div></div>
    </section>

    <!-- ==================== MODALS ==================== -->
    <section class="section" id="sec-modals">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg></div><div><div class="section-title">Modals</div><div class="section-sub">renderModal() · renderModalConfirm() · renderModalSuccess() · renderModalForm()</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body"><div class="btn-row">
        <?php echo renderButton(['label' => 'Modal Info', 'variant' => 'cyan', 'onclick' => "openModal('demo-modal')"]); ?>
        <?php echo renderButton(['label' => 'Modal Perigo', 'variant' => 'red', 'onclick' => "openModal('demo-danger')"]); ?>
        <?php echo renderButton(['label' => 'Modal Sucesso', 'variant' => 'green', 'onclick' => "openModal('demo-success')"]); ?>
        <?php echo renderButton(['label' => 'Modal Form', 'variant' => 'purple', 'onclick' => "openModal('demo-form')"]); ?>
      </div></div></div>
    </section>

    <!-- ==================== TOASTS ==================== -->
    <section class="section" id="sec-toasts">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div><div><div class="section-title">Toasts</div><div class="section-sub">renderToastTrigger($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body"><div class="btn-row">
        <?php echo renderToastTrigger(['label' => 'Erro', 'variant' => 'red', 'type' => 'red', 'title' => 'Erro!', 'message' => 'Operação falhou.']); ?>
        <?php echo renderToastTrigger(['label' => 'Sucesso', 'variant' => 'green', 'type' => 'green', 'title' => 'Sucesso!', 'message' => 'Operação concluída.']); ?>
        <?php echo renderToastTrigger(['label' => 'Informação', 'variant' => 'cyan', 'type' => 'cyan', 'title' => 'Info', 'message' => 'Dados atualizados.']); ?>
        <?php echo renderToastTrigger(['label' => 'Atenção', 'variant' => 'yellow', 'type' => 'yellow', 'title' => 'Atenção!', 'message' => 'Verifique antes de continuar.']); ?>
        <?php echo renderToastTrigger(['label' => 'Destaque', 'variant' => 'purple', 'type' => 'purple', 'title' => 'Destaque!', 'message' => 'Novo recurso disponível.']); ?>
        <?php echo renderToastTrigger(['label' => 'Alerta', 'variant' => 'orange', 'type' => 'orange', 'title' => 'Alerta!', 'message' => 'Ação requer atenção.']); ?>
        <?php echo renderToastTrigger(['label' => 'Rápido 3s', 'variant' => 'green', 'type' => 'green', 'title' => 'Rápido!', 'message' => 'Desaparece em 3s.', 'duration' => 3000]); ?>
      </div></div></div>
    </section>

    <!-- ==================== FORMS ==================== -->
    <section class="section" id="sec-forms">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></div><div><div class="section-title">Forms</div><div class="section-sub">renderInput() · renderFormRow() · renderToggle()</div></div></div>
      <div class="divider"></div>

      <div class="col2">
        <div class="card"><div class="card-head"><span class="card-title">Campos Básicos</span></div><div class="card-body">
          <?php echo renderInput(['label' => 'Nome Completo', 'type' => 'text', 'name' => 'nome', 'placeholder' => 'Digite seu nome...']); ?>
          <?php echo renderInput(['label' => 'Email', 'type' => 'email', 'name' => 'email', 'placeholder' => 'email@exemplo.com']); ?>
          <?php echo renderInput(['label' => 'Senha', 'type' => 'password', 'name' => 'senha', 'placeholder' => '••••••••']); ?>
          <?php echo renderInput(['label' => 'Tipo', 'type' => 'select', 'name' => 'tipo', 'options' => ['Selecione...', 'Andaime', 'Betoneira', 'Compressor']]); ?>
          <?php echo renderInput(['label' => 'Observação', 'type' => 'textarea', 'name' => 'obs', 'placeholder' => 'Digite sua mensagem...', 'rows' => 3]); ?>
        </div></div>

        <div class="card"><div class="card-head"><span class="card-title">Estados & Validação</span></div><div class="card-body">
          <?php echo renderInput(['label' => 'Válido', 'type' => 'text', 'name' => 'v1', 'value' => 'valor válido', 'state' => 'success', 'hint' => 'Campo validado com sucesso!']); ?>
          <?php echo renderInput(['label' => 'Inválido', 'type' => 'text', 'name' => 'v2', 'value' => 'valor inválido', 'state' => 'error', 'hint' => 'Este campo é obrigatório.']); ?>
          <?php echo renderInput(['label' => 'Dica', 'type' => 'text', 'name' => 'v3', 'placeholder' => '000.000.000-00', 'state' => 'default', 'hint' => 'Use o formato: 000.000.000-00']); ?>
          <?php echo renderInput(['label' => 'Desabilitado', 'type' => 'text', 'name' => 'v4', 'value' => 'Campo desabilitado', 'disabled' => true]); ?>
          <?php echo renderInput(['label' => 'Busca', 'type' => 'search', 'name' => 'busca', 'placeholder' => 'Buscar...', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>']); ?>
        </div></div>
      </div>

      <div class="card"><div class="card-head"><span class="card-title">Toggles</span></div><div class="card-body">
        <?php echo renderToggleRow(['label' => 'Notificações por email', 'checked' => true, 'variant' => 'cyan']); ?>
        <?php echo renderToggleRow(['label' => 'Relatório semanal', 'checked' => false, 'variant' => 'cyan']); ?>
        <?php echo renderToggleRow(['label' => 'Modo escuro (red toggle)', 'checked' => false, 'variant' => 'red']); ?>
      </div></div>
    </section>

    <!-- ==================== CARDS ==================== -->
    <section class="section" id="sec-cards">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div><div><div class="section-title">Cards</div><div class="section-sub">renderCard() · renderCardStat() · renderCustomCard()</div></div></div>
      <div class="divider"></div>

      <div class="col2">
        <?php echo renderCustomCard(['title' => 'Card Padrão', 'body' => '<p style="color:var(--text-2);line-height:1.7">Conteúdo do card com informações relevantes usando renderCustomCard().</p>', 'footer' => '<button class="btn btn-sm btn-cyan">Ação</button><button class="btn btn-sm btn-gray">Cancelar</button>']); ?>
        <?php echo renderCustomCard(['title' => 'Card com Glow', 'body' => '<p style="color:var(--text-2);line-height:1.7">Card destacado com efeito neon cyan.</p>', 'footer' => '<button class="btn btn-sm btn-cyan">Ver Detalhes</button>', 'glow' => true]); ?>
      </div>

      <div class="col3" style="margin-top:16px">
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'value' => 'R$ 38,4k', 'label' => 'Receita Mensal', 'color' => 'var(--neon-red)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'value' => '47', 'label' => 'Locações Ativas', 'color' => 'var(--neon-green)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'value' => '124', 'label' => 'Equipamentos', 'color' => 'var(--neon-yellow)']); ?>
      </div>
    </section>

    <!-- ==================== TABLES ==================== -->
    <section class="section" id="sec-tables">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg></div><div><div class="section-title">Tables</div><div class="section-sub">renderTable() · Busca · Paginação · Ações</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-head"><span class="card-title">Tabela Completa — Busca + Paginação + CRUD</span></div><div class="card-body" style="padding:0">
      <?php echo renderTable([
        'id' => 'tbl-locacoes',
        'searchable' => true,
        'searchPlaceholder' => 'Buscar cliente, equipamento ou status...',
        'paginated' => true,
        'perPage' => 5,
        'headers' => [
          ['label' => 'Cliente', 'sortable' => true],
          ['label' => 'Equipamento', 'sortable' => true],
          ['label' => 'Valor', 'sortable' => true],
          ['label' => 'Status', 'sortable' => false],
        ],
        'actionBtns' => renderTableActions('crud'),
        'rows' => [
          ['Construtora Nova Era', 'Andaime Tubular 10m', ['html' => true, 'content' => '<span class="td-mono">R$ 4.200,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])]],
          ['Rodrigo Mendes', 'Betoneira 400L', ['html' => true, 'content' => '<span class="td-mono">R$ 980,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Pendente', 'variant' => 'yellow', 'size' => 'sm'])]],
          ['Engenharia Total', 'Compressor AR 300L', ['html' => true, 'content' => '<span class="td-mono">R$ 1.450,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Em uso', 'variant' => 'cyan', 'size' => 'sm'])]],
          ['Paulo Ferreira', 'Gerador 7,5kVA', ['html' => true, 'content' => '<span class="td-mono">R$ 2.100,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Atrasado', 'variant' => 'red', 'size' => 'sm'])]],
          ['Construtora Alvorada', 'Escora Metálica 3m', ['html' => true, 'content' => '<span class="td-mono">R$ 680,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])]],
          ['Mega Construção', 'Andaime Tubular 15m', ['html' => true, 'content' => '<span class="td-mono">R$ 6.300,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])]],
          ['José da Silva', 'Betoneira 400L', ['html' => true, 'content' => '<span class="td-mono">R$ 980,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Devolvido', 'variant' => 'purple', 'size' => 'sm'])]],
          ['Horizonte Engenharia', 'Compressor AR 500L', ['html' => true, 'content' => '<span class="td-mono">R$ 2.800,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Pendente', 'variant' => 'yellow', 'size' => 'sm'])]],
          ['Andrade & Filhos', 'Gerador 15kVA', ['html' => true, 'content' => '<span class="td-mono">R$ 4.500,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])]],
          ['RM Construções', 'Plataforma Elevatória', ['html' => true, 'content' => '<span class="td-mono">R$ 12.000,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Atrasado', 'variant' => 'red', 'size' => 'sm'])]],
          ['Costa Empreendimentos', 'Andaime Tubular 8m', ['html' => true, 'content' => '<span class="td-mono">R$ 3.360,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Em uso', 'variant' => 'cyan', 'size' => 'sm'])]],
          ['Lima & Associados', 'Betoneira 600L', ['html' => true, 'content' => '<span class="td-mono">R$ 1.400,00</span>'], ['html' => true, 'content' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])]],
        ]
      ]); ?>
      </div></div>

      <div class="col2" style="margin-top:16px">
        <div class="card"><div class="card-head"><span class="card-title">Confirmação</span></div><div class="card-body" style="padding:0">
        <?php echo renderTable([
          'id' => 'tbl-confirm',
          'searchable' => true,
          'searchPlaceholder' => 'Buscar solicitação...',
          'paginated' => true,
          'perPage' => 5,
          'headers' => [
            ['label' => 'Solicitação', 'sortable' => true],
            ['label' => 'Solicitante', 'sortable' => true],
            ['label' => 'Status', 'sortable' => false],
          ],
          'actionBtns' => renderTableActions('confirm'),
          'rows' => [
            ['LOC-2026-0041', 'Construtora Nova Era', ['html' => true, 'content' => renderBadge(['label' => 'Aguardando', 'variant' => 'yellow', 'size' => 'sm'])]],
            ['LOC-2026-0040', 'Engenharia Total', ['html' => true, 'content' => renderBadge(['label' => 'Aguardando', 'variant' => 'yellow', 'size' => 'sm'])]],
            ['LOC-2026-0039', 'Paulo Ferreira', ['html' => true, 'content' => renderBadge(['label' => 'Revisão', 'variant' => 'orange', 'size' => 'sm'])]],
            ['LOC-2026-0038', 'Mega Construção', ['html' => true, 'content' => renderBadge(['label' => 'Aguardando', 'variant' => 'yellow', 'size' => 'sm'])]],
            ['LOC-2026-0037', 'Andrade & Filhos', ['html' => true, 'content' => renderBadge(['label' => 'Aguardando', 'variant' => 'yellow', 'size' => 'sm'])]],
            ['LOC-2026-0036', 'RM Construções', ['html' => true, 'content' => renderBadge(['label' => 'Revisão', 'variant' => 'purple', 'size' => 'sm'])]],
          ]
        ]); ?>
        </div></div>

        <div class="card"><div class="card-head"><span class="card-title">Simples (sem busca/paginação)</span></div><div class="card-body" style="padding:0">
        <?php echo renderTable([
          'id' => 'tbl-simple',
          'headers' => [
            ['label' => 'Mês', 'sortable' => true],
            ['label' => 'Receita', 'sortable' => true],
            ['label' => 'Despesas', 'sortable' => true],
          ],
          'rows' => [
            ['Janeiro', ['html' => true, 'content' => '<span class="td-mono">R$ 38.400</span>'], ['html' => true, 'content' => '<span class="td-mono">R$ 12.000</span>']],
            ['Fevereiro', ['html' => true, 'content' => '<span class="td-mono">R$ 41.200</span>'], ['html' => true, 'content' => '<span class="td-mono">R$ 13.500</span>']],
            ['Março', ['html' => true, 'content' => '<span class="td-mono">R$ 45.600</span>'], ['html' => true, 'content' => '<span class="td-mono">R$ 11.800</span>']],
          ]
        ]); ?>
        </div></div>
      </div>
    </section>

    <!-- ==================== ALERTS ==================== -->
    <section class="section" id="sec-alerts">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div><div class="section-title">Alerts</div><div class="section-sub">renderAlert($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body">
        <?php echo renderAlert(['variant' => 'red', 'title' => 'Erro Crítico', 'message' => 'Ocorreu um erro ao processar sua solicitação.', 'dismissible' => true]); ?>
        <?php echo renderAlert(['variant' => 'green', 'title' => 'Operação Concluída', 'message' => 'Locação #3812 criada com sucesso.']); ?>
        <?php echo renderAlert(['variant' => 'yellow', 'title' => 'Atenção', 'message' => '3 equipamentos precisam de manutenção nos próximos 7 dias.']); ?>
        <?php echo renderAlert(['variant' => 'cyan', 'title' => 'Informação', 'message' => 'Sistema atualizado para a versão 3.0.']); ?>
        <?php echo renderAlert(['variant' => 'purple', 'title' => 'Promoção', 'message' => 'Ganhe 10% de desconto na primeira locação!', 'dismissible' => true]); ?>
      </div></div>
    </section>

    <!-- ==================== PROGRESS ==================== -->
    <section class="section" id="sec-progress">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div><div><div class="section-title">Progress</div><div class="section-sub">renderProgress() · renderProgressGroup()</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body">
        <?php echo renderProgressGroup([
          ['label' => '38.400 / 55.000', 'value' => '69%', 'percent' => 69, 'variant' => 'cyan'],
          ['label' => '91 / 100', 'value' => '91%', 'percent' => 91, 'variant' => 'green'],
          ['label' => '18 / 124', 'value' => '14%', 'percent' => 14, 'variant' => 'yellow'],
          ['label' => '756GB / 1TB', 'value' => '75%', 'percent' => 75, 'variant' => 'purple', 'height' => 'thick'],
        ]); ?>
      </div></div>
    </section>

    <!-- ==================== SPINNERS ==================== -->
    <section class="section" id="sec-spinners">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div><div><div class="section-title">Spinners</div><div class="section-sub">renderSpinner() · renderSpinnerDots() · renderSpinnerRing() · renderSpinnerBar()</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-head"><span class="card-title">Ring Spinners</span></div><div class="card-body"><div class="row" style="align-items:center;gap:28px;padding:10px 0">
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'sm', 'variant' => 'cyan']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">SM</div></div>
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'md', 'variant' => 'cyan']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">MD</div></div>
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'lg', 'variant' => 'cyan']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">LG</div></div>
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'lg', 'variant' => 'red']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">Erro</div></div>
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'lg', 'variant' => 'green']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">OK</div></div>
        <div style="text-align:center"><?php echo renderSpinner(['size' => 'lg', 'variant' => 'purple']); ?><div style="font-size:11px;color:var(--text-3);margin-top:8px">Roxo</div></div>
      </div></div></div>

      <div class="col2">
        <div class="card"><div class="card-head"><span class="card-title">Dots</span></div><div class="card-body" style="display:flex;justify-content:center;padding:30px"><?php echo renderSpinnerDots(); ?></div></div>
        <div class="card"><div class="card-head"><span class="card-title">Dual Ring</span></div><div class="card-body" style="display:flex;justify-content:center;padding:30px"><?php echo renderSpinnerRing(); ?></div></div>
      </div>

      <div class="card"><div class="card-head"><span class="card-title">Bar & Label</span></div><div class="card-body">
        <?php echo renderSpinnerBar(); ?>
        <div style="margin-top:20px"><?php echo renderSpinnerWithLabel(['label' => 'Carregando dados do servidor...', 'size' => 'md', 'variant' => 'cyan']); ?></div>
      </div></div>
    </section>

    <!-- ==================== ACCORDION ==================== -->
    <section class="section" id="sec-accordion">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div><div><div class="section-title">Accordion</div><div class="section-sub">renderAccordion($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body">
        <?php echo renderAccordion([
          'id' => 'faq',
          'items' => [
            ['title' => 'Como funciona o sistema?', 'iconBg' => 'var(--neon-cyan)', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'content' => 'O sistema permite gerenciar locações de equipamentos, controlar estoque e gerar relatórios detalhados.', 'open' => true],
            ['title' => 'Qual o prazo de devolução?', 'iconBg' => 'var(--neon-green)', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'content' => 'O prazo é de 15 dias úteis a partir da retirada. Atrasos incorrem em multa diária de 2%.'],
            ['title' => 'Como solicitar manutenção?', 'iconBg' => 'var(--neon-purple)', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', 'content' => 'Acesse o menu de equipamentos, selecione o item e clique em "Solicitar Manutenção".'],
          ]
        ]); ?>
      </div></div>
    </section>

    <!-- ==================== TIMELINE ==================== -->
    <section class="section" id="sec-timeline">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="section-title">Timeline</div><div class="section-sub">renderTimeline($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body">
        <?php echo renderTimeline([
          'items' => [
            ['time' => 'Hoje, 09:14', 'title' => 'Locação #3812 aprovada', 'desc' => 'Construtora Nova Era — 5x Andaime Tubular aprovados para retirada.', 'color' => 'green'],
            ['time' => 'Ontem, 16:30', 'title' => 'Equipamento devolvido', 'desc' => 'Betoneira 400L devolvida em bom estado.', 'color' => 'cyan'],
            ['time' => '28/03, 11:00', 'title' => 'Manutenção programada', 'desc' => 'Compressor de ar #C007 enviado para revisão preventiva.', 'color' => 'yellow'],
            ['time' => '25/03, 08:45', 'title' => 'Atraso na devolução', 'desc' => 'Cliente Rodrigo Mendes com 3 dias de atraso.', 'color' => 'red'],
            ['time' => '20/03, 14:20', 'title' => 'Novo contrato gerado', 'desc' => 'Fatura #INV-2026-0001 emitida.', 'color' => 'purple'],
          ]
        ]); ?>
      </div></div>
    </section>

    <!-- ==================== STEPPER ==================== -->
    <section class="section" id="sec-stepper">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div><div><div class="section-title">Stepper</div><div class="section-sub">renderStepper($config)</div></div></div>
      <div class="divider"></div>

      <div class="card"><div class="card-body">
        <?php echo renderStepper([
          'id' => 'wizard',
          'steps' => [
            ['label' => 'Dados<br/>Pessoais'],
            ['label' => 'Endereço'],
            ['label' => 'Documentos'],
            ['label' => 'Confirmação'],
          ],
          'activeIndex' => 1,
          'panes' => [
            '<div class="col2"><div class="fg"><div class="fl">Nome Completo</div><input class="fi" type="text" placeholder="Digite seu nome"/></div><div class="fg"><div class="fl">CPF</div><input class="fi" type="text" placeholder="000.000.000-00"/></div><div class="fg" style="grid-column:1/-1"><div class="fl">Email</div><input class="fi" type="email" placeholder="email@exemplo.com"/></div></div>',
            '<div class="col2"><div class="fg"><div class="fl">CEP</div><input class="fi" type="text" placeholder="00000-000"/></div><div class="fg"><div class="fl">Cidade</div><input class="fi" type="text" placeholder="São Paulo"/></div><div class="fg" style="grid-column:1/-1"><div class="fl">Endereço</div><input class="fi" type="text" placeholder="Rua, Número"/></div></div>',
            '<div class="fg"><div class="fl">RG</div><input class="fi" type="text" placeholder="00.000.000-0"/></div><div class="fg"><div class="fl">CNH (opcional)</div><input class="fi" type="text" placeholder="Número da CNH"/></div>',
            '<div style="text-align:center;padding:20px"><div style="font-size:48px;margin-bottom:12px">&#10004;</div><div style="font-size:18px;font-weight:700;margin-bottom:8px">Tudo pronto!</div><div style="color:var(--text-3)">Revise os dados e clique em Concluir.</div></div>',
          ]
        ]); ?>
      </div></div>
    </section>

    <!-- ==================== MISC: Avatar, Badges, Chips, Ratings, List Items, Popover, Skeleton ==================== -->
    <section class="section" id="sec-misc">
      <div class="section-header"><div class="section-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div><div><div class="section-title">Avatares, Badges, Chips & Mais</div><div class="section-sub">renderAvatar() · renderBadge() · renderChip() · renderTag() · renderRating() · renderListItem() · renderPopover() · renderSkeleton()</div></div></div>
      <div class="divider"></div>

      <!-- Avatars -->
      <div class="card"><div class="card-head"><span class="card-title">Avatares — renderAvatar() · renderAvatarGroup()</span></div><div class="card-body">
        <div style="margin-bottom:16px"><strong>Tamanhos:</strong></div>
        <div class="row" style="gap:16px;margin-bottom:20px">
          <?php echo renderAvatar(['initials' => 'MA', 'variant' => 'cyan', 'size' => 'sm']); ?>
          <?php echo renderAvatar(['initials' => 'MA', 'variant' => 'cyan']); ?>
          <?php echo renderAvatar(['initials' => 'MA', 'variant' => 'cyan', 'size' => 'lg']); ?>
          <?php echo renderAvatar(['initials' => 'MA', 'variant' => 'cyan', 'size' => 'xl']); ?>
        </div>
        <div style="margin-bottom:16px"><strong>Cores:</strong></div>
        <div class="row" style="gap:12px;margin-bottom:20px">
          <?php foreach (['cyan','green','red','purple','yellow','blue'] as $c) { echo renderAvatar(['initials' => strtoupper(substr($c,0,2)), 'variant' => $c]); } ?>
        </div>
        <div style="margin-bottom:16px"><strong>Status:</strong></div>
        <div class="row" style="gap:24px;margin-bottom:20px">
          <div style="text-align:center"><?php echo renderAvatar(['initials' => 'ON', 'variant' => 'cyan', 'size' => 'lg', 'status' => 'online']); ?><div style="font-size:12px;color:var(--text-3);margin-top:6px">Online</div></div>
          <div style="text-align:center"><?php echo renderAvatar(['initials' => 'OC', 'variant' => 'green', 'size' => 'lg', 'status' => 'busy']); ?><div style="font-size:12px;color:var(--text-3);margin-top:6px">Ocupado</div></div>
          <div style="text-align:center"><?php echo renderAvatar(['initials' => 'OF', 'variant' => 'purple', 'size' => 'lg', 'status' => 'offline']); ?><div style="font-size:12px;color:var(--text-3);margin-top:6px">Offline</div></div>
        </div>
        <div style="margin-bottom:8px"><strong>Avatar Group:</strong></div>
        <?php echo renderAvatarGroup(['avatars' => [['initials' => 'MA', 'variant' => 'cyan'], ['initials' => 'JB', 'variant' => 'green'], ['initials' => 'LC', 'variant' => 'red'], ['initials' => 'RF', 'variant' => 'purple'], ['initials' => 'SK', 'variant' => 'blue']], 'overflow' => 8]); ?>
      </div></div>

      <!-- Badges -->
      <div class="card"><div class="card-head"><span class="card-title">Badges — renderBadge()</span></div><div class="card-body">
        <div class="row" style="margin-bottom:16px">
          <?php foreach (['red'=>'Ativo','green'=>'Concluído','cyan'=>'Info','blue'=>'Processando','purple'=>'Especial','orange'=>'Pendente','yellow'=>'Análise'] as $v => $l) { echo renderBadge(['label' => $l, 'variant' => $v, 'pulse' => in_array($v, ['red','green','blue'])]); } ?>
        </div>
        <div class="row">
          <?php echo renderBadge(['label' => 'Small', 'variant' => 'red', 'size' => 'sm']); ?>
          <?php echo renderBadge(['label' => 'Default', 'variant' => 'green']); ?>
          <?php echo renderBadge(['label' => 'Large', 'variant' => 'cyan', 'size' => 'lg']); ?>
        </div>
      </div></div>

      <!-- Chips & Tags -->
      <div class="card"><div class="card-head"><span class="card-title">Chips & Tags — renderChip() · renderChipRemovable() · renderTag()</span></div><div class="card-body">
        <div style="margin-bottom:12px"><strong>Chips Selecionáveis:</strong></div>
        <?php echo renderChipGroup([['label' => 'Andaimes', 'variant' => 'cyan'], ['label' => 'Betoneiras', 'variant' => 'green', 'selected' => true], ['label' => 'Compressores', 'variant' => 'red'], ['label' => 'Geradores', 'variant' => 'purple'], ['label' => 'Escoras', 'variant' => 'yellow', 'selected' => true]]); ?>
        <div style="margin:16px 0 12px"><strong>Chips Removíveis:</strong></div>
        <div class="chip-wrap" style="margin-bottom:16px">
          <?php echo renderChipRemovable(['label' => 'Locação Ativa']); ?>
          <?php echo renderChipRemovable(['label' => 'Pendente']); ?>
          <?php echo renderChipRemovable(['label' => 'São Paulo']); ?>
        </div>
        <div style="margin-bottom:12px"><strong>Tags:</strong></div>
        <div class="row" style="gap:8px">
          <?php foreach (['cyan'=>'ATIVO','green'=>'APROVADO','red'=>'CANCELADO','yellow'=>'PENDENTE','purple'=>'REVISÃO'] as $v => $l) { echo renderTag(['label' => $l, 'variant' => $v]); } ?>
        </div>
      </div></div>

      <!-- Rating -->
      <div class="card"><div class="card-head"><span class="card-title">Rating — renderRating()</span></div><div class="card-body">
        <?php echo renderRating(['value' => 3, 'max' => 5, 'size' => 'lg', 'showValue' => true, 'id' => 'r1']); ?>
        <?php echo renderRating(['value' => 4, 'max' => 5, 'id' => 'r2']); ?>
        <?php echo renderRating(['value' => 5, 'max' => 5, 'size' => 'sm', 'interactive' => false, 'id' => 'r3']); ?>
      </div></div>

      <!-- List Items -->
      <div class="card"><div class="card-head"><span class="card-title">List Items — renderListItem() · renderListItemGroup()</span></div><div class="card-body" style="padding:0">
        <?php echo renderListItemGroup([
          ['title' => 'Locação #3812', 'subtitle' => 'Construtora Nova Era — R$ 4.200', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>', 'action' => renderBadge(['label' => 'Ativo', 'variant' => 'green', 'size' => 'sm', 'pulse' => true])],
          ['title' => 'Locação #3811', 'subtitle' => 'Rodrigo Mendes — R$ 980', 'iconBg' => 'var(--neon-yellow)', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'action' => renderBadge(['label' => 'Pendente', 'variant' => 'yellow', 'size' => 'sm'])],
          ['title' => 'Equipamento #E004', 'subtitle' => 'Betoneira 400L — Em manutenção', 'iconBg' => 'var(--neon-purple)', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'action' => renderBadge(['label' => 'Manutenção', 'variant' => 'purple', 'size' => 'sm'])],
        ]); ?>
      </div></div>

      <!-- Popover -->
      <div class="card"><div class="card-head"><span class="card-title">Popover — renderPopover()</span></div><div class="card-body">
        <div class="row" style="justify-content:center;padding:40px 0;gap:50px">
          <?php echo renderPopover([
            'id' => 'pop1',
            'trigger' => '<button class="btn btn-cyan">Popover Cyan</button>',
            'title' => 'Popover Info',
            'content' => '<div class="popover-text">Este é um popover renderizado via PHP.</div>'
          ]); ?>
          <?php echo renderPopover([
            'id' => 'pop2',
            'trigger' => '<button class="btn btn-gray">Popover Menu</button>',
            'title' => 'Ações Rápidas',
            'content' => '<div style="display:flex;flex-direction:column;gap:8px"><div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;cursor:pointer;transition:background .15s" onmouseover="this.style.background=\'var(--bg-surface)\'" onmouseout="this.style.background=\'transparent\'">Editar</div><div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;cursor:pointer;transition:background .15s" onmouseover="this.style.background=\'var(--bg-surface)\'" onmouseout="this.style.background=\'transparent\'">Duplicar</div><div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;cursor:pointer;transition:background .15s;color:var(--neon-red)" onmouseover="this.style.background=\'var(--bg-surface)\'" onmouseout="this.style.background=\'transparent\'">Excluir</div></div>'
          ]); ?>
        </div>
      </div></div>

      <!-- Tooltips -->
      <div class="card"><div class="card-head"><span class="card-title">Tooltips — data-tip=""</span></div><div class="card-body">
        <div style="margin-bottom:12px;font-size:13px;color:var(--text-3);font-weight:600">Padrão (hover)</div>
        <div class="btn-row" style="margin-bottom:24px">
          <button class="btn btn-gray" data-tip="Tooltip padrão">Default</button>
        </div>
        <div style="margin-bottom:12px;font-size:13px;color:var(--text-3);font-weight:600">Coloridos</div>
        <div class="btn-row">
          <button class="btn btn-red" data-tip="red">Vermelho</button>
          <button class="btn btn-cyan" data-tip="cyan">Cyan</button>
          <button class="btn btn-green" data-tip="green">Verde</button>
          <button class="btn btn-yellow" data-tip="yellow">Amarelo</button>
          <button class="btn btn-purple" data-tip="purple">Roxo</button>
        </div>
      </div></div>

      <!-- Skeleton -->
      <div class="card"><div class="card-head"><span class="card-title">Skeleton — renderSkeleton() · renderSkeletonCard()</span></div><div class="card-body">
        <div class="col2">
          <?php echo renderSkeletonCard(['lines' => 3, 'avatar' => true]); ?>
          <?php echo renderSkeletonCard(['lines' => 2, 'avatar' => true]); ?>
        </div>
      </div></div>
    </section>

  </div>
</div>

<!-- TOAST CONTAINER -->
<?php echo renderToastContainer(); ?>

<!-- MODALS renderizados via PHP -->
<?php echo renderModal([
  'id' => 'demo-modal',
  'title' => 'Informações',
  'subtitle' => 'Design System 3.0 White Rabbit',
  'body' => '<p style="color:var(--text-2);line-height:1.8">Sistema completo de gestão de locação com interface moderna em tema white rabbit com cores neon vibrantes. Todos os componentes são renderizados via PHP reutilizável.</p>'
]); ?>

<?php echo renderModalConfirm([
  'id' => 'demo-danger',
  'title' => 'Excluir item?',
  'body' => 'Esta ação não pode ser desfeita. O item será removido permanentemente.'
]); ?>

<?php echo renderModalSuccess([
  'id' => 'demo-success',
  'title' => 'Operação Concluída!',
  'body' => 'Locação criada com sucesso.'
]); ?>

<?php echo renderModalForm([
  'id' => 'demo-form',
  'title' => 'Novo Cliente',
  'subtitle' => 'Preencha os dados abaixo',
  'size' => 'lg',
  'fields' => [
    ['label' => 'Nome', 'type' => 'text', 'name' => 'nome', 'placeholder' => 'Nome completo'],
    ['label' => 'Email', 'type' => 'email', 'name' => 'email', 'placeholder' => 'email@exemplo.com'],
    ['label' => 'CPF', 'type' => 'text', 'name' => 'cpf', 'placeholder' => '000.000.000-00', 'gridColumn' => '1/-1'],
    ['label' => 'Telefone', 'type' => 'tel', 'name' => 'tel', 'placeholder' => '(00) 00000-0000'],
    ['label' => 'Cidade', 'type' => 'text', 'name' => 'cidade', 'placeholder' => 'São Paulo'],
  ],
  'col2' => true
]); ?>

<script src="scripts.js"></script>
<script>
// Off-canvas
function openLeft() { document.getElementById('left-canvas').classList.add('open'); document.getElementById('overlay-left').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeLeft() { document.getElementById('left-canvas').classList.remove('open'); document.getElementById('overlay-left').classList.remove('active'); document.body.style.overflow = ''; }
function openRight() { document.getElementById('right-canvas').classList.add('open'); document.getElementById('overlay-right').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeRight() { document.getElementById('right-canvas').classList.remove('open'); document.getElementById('overlay-right').classList.remove('active'); document.body.style.overflow = ''; }
function selectChip(el) { el.parentElement.querySelectorAll('.lc-chip').forEach(function(c) { c.classList.remove('active'); }); el.classList.add('active'); }
</script>
</body>
</html>

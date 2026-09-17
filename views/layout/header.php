<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
  <meta http-equiv="Pragma" content="no-cache"/>
  <meta http-equiv="Expires" content="0"/>
  <?php
  // Buscar nome da empresa do banco de dados para o título
  $_empresaTitle = '';
  try {
    $_db = \App\Database\Connection::get();
    $_stmt = $_db->query("SELECT nome, identificador FROM empresa LIMIT 1");
    $_empresa = $_stmt->fetch(\PDO::FETCH_ASSOC);
    if ($_empresa) {
      $_empresaTitle = $_empresa['nome'] ?: $_empresa['identificador'];
    }
  } catch (\Exception $e) {
    // Silenciar erro em produção
  }
  
  // Montar título: "Nome da Página - Nome da Empresa"
  $pageTitle = '';
  if (!empty($title)) {
    $pageTitle = $title;
    if (!empty($_empresaTitle)) {
      $pageTitle .= ' - ' . $_empresaTitle;
    }
  } elseif (!empty($appTitle)) {
    $pageTitle = $appTitle;
  } else {
    $pageTitle = $_empresaTitle ?: 'App';
  }
  ?>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($appName ?? 'App', ENT_QUOTES, 'UTF-8') ?>"/>
  
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" media="print" onload="this.media='all'"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>/css/styles.css"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>/css/utilities.css"/>
  
  <!-- CSS Variables do Tema -->
  <?php if (!empty($theme)): ?>
  <style>
    :root {
      --neon-cyan: <?= $theme['primary'] ?>;
      --neon-cyan-glow: <?= $theme['primary_glow'] ?>;
      --bg-dark: <?= $theme['sidebar_bg'] ?>;
      --bg-surface: <?= $theme['surface'] ?>;
      --bg-darkest: <?= $theme['background'] ?>;
      --text-1: <?= $theme['text'] ?>;
      --text-2: <?= $theme['text'] ?>;
      --text-3: <?= $theme['text_light'] ?>;
      --bg-hover: <?= $theme['sidebar_hover'] ?>;
    }
    .ni:hover, .si:hover { background: <?= $theme['sidebar_hover'] ?>; }
  </style>
  <?php endif; ?>
  
  <meta name="theme-color" content="<?= $theme['primary'] ?? '#0B6E8C' ?>"/>
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='<?= htmlspecialchars($theme['primary'] ?? '#0B6E8C', ENT_QUOTES, 'UTF-8') ?>'/><text x='50' y='65' font-size='50' text-anchor='middle' fill='white' font-family='Arial'><?= htmlspecialchars($appLogoText ?? 'A', ENT_QUOTES, 'UTF-8') ?></text></svg>"/>
  <?php if (!empty($pageStyles)): ?><style><?= $pageStyles ?></style><?php endif; ?>
</head>
<body>

<!-- OVERLAYS -->
<div class="overlay" id="overlay-left" data-action="close-left"></div>
<div class="overlay" id="overlay-right" data-action="close-right"></div>

<!-- LEFT OFF-CANVAS -->
<aside id="left-canvas">
  <div class="lc-head">
    <div><div class="lc-title">Filtros & Navegação</div><div class="lc-sub">Refine a visualização</div></div>
    <button class="close-x" data-action="close-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="lc-body">
    <div class="lc-section">
      <div class="lc-sec-lbl">Período</div>
      <div class="lc-chips"><div class="lc-chip active" data-action="select-chip">Hoje</div><div class="lc-chip" data-action="select-chip">7 dias</div><div class="lc-chip" data-action="select-chip">30 dias</div></div>
    </div>
  </div>
  <div class="lc-foot"><button class="lc-btn-clear" data-action="close-left">Cancelar</button><button class="lc-btn-apply" data-action="close-left">Aplicar</button></div>
</aside>

<!-- RIGHT OFF-CANVAS -->
<aside id="right-canvas">
  <div class="rc-head">
    <div class="rc-top"><span class="rc-title">Minha Conta</span><button class="close-x" data-action="close-right"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
  </div>
  <div class="rc-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px">
    <?php $user = \App\Auth\Rbac::getUser(); ?>
    <?php if ($user): ?>
    <div style="font-size:48px">&#128100;</div>
    <div style="color:#fff;font-size:16px;font-weight:700"><?= htmlspecialchars($user['name'] ?? 'Usuário') ?></div>
    <div style="color:rgba(255,255,255,0.5);font-size:13px"><?= \App\Auth\Rbac::getRoleLabel($user['role'] ?? 'user') ?></div>
    <a href="<?= $baseUrl ?>/meu-perfil" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);font-size:14px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;text-decoration:none;transition:all .15s;margin-top:4px">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      Meu Perfil
    </a>
    <form method="POST" action="<?= $baseUrl ?>/auth/logout" style="margin-top:8px">
      <button type="submit" style="padding:10px 24px;border-radius:10px;background:var(--neon-red);color:#fff;border:2px solid var(--neon-red);font-size:14px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s;box-shadow:0 4px 16px var(--neon-red-glow);display:flex;align-items:center;gap:8px">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m0 8H7m6-6a3 3 0 100-6 3 3 0 000 6z"/></svg>
        Sair
      </button>
    </form>
    <?php else: ?>
    <div style="font-size:48px">&#128100;</div>
    <div style="color:#fff;font-size:16px;font-weight:700">Visitante</div>
    <div style="color:rgba(255,255,255,0.5);font-size:13px">Faça login para continuar</div>
    <button data-action="navegar" data-url="<?= $baseUrl ?>/auth/login" style="margin-top:16px;padding:10px 24px;border-radius:10px;background:var(--neon-cyan);color:#fff;border:2px solid var(--neon-cyan);font-size:14px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s;box-shadow:0 4px 16px var(--neon-cyan-glow)">
      Entrar
    </button>
    <?php endif; ?>
  </div>
</aside>

<!-- FAB -->
<button class="fab" data-action="open-left"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h4"/></svg></button>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<!-- TOPBAR -->
<header id="topbar">
  <div class="tb-left">
    <div class="breadcrumb">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      <span>Início</span>
      <span class="sep">/</span>
      <span class="cur" id="breadcrumb-cur"><?= htmlspecialchars($breadcrumb ?? 'Dashboard') ?></span>
    </div>
  </div>
  <div class="tb-right">
    <div class="avatar" data-action="open-right">
      <?php $user = \App\Auth\Rbac::getUser(); ?>
      <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
    </div>
  </div>
</header>

<!-- MAIN CONTENT -->
<div id="main">
  <div class="content">

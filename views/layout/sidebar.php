<!-- SIDEBAR -->
<?php
try {
    $db = \App\Database\Connection::get();
    $stmt = $db->query("SELECT identificador FROM empresa LIMIT 1");
    $empresaData = $stmt->fetch(\PDO::FETCH_ASSOC);
    $identificador = $empresaData['identificador'] ?? 'SisLoc';
} catch (\Throwable $e) {
    $identificador = 'SisLoc';
}
?>
<aside id="sidebar">
  <div class="sb-head">
    <div class="logo-wrap">
      <div class="logo-ico" data-action="open-sidebar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/></svg></div>
      <div><div class="logo-name"><?= htmlspecialchars($identificador) ?></div><div class="logo-ver">v<?= $appVersion ?? '1.0.0' ?></div></div>
    </div>
    <button class="collapse-btn" data-action="close-sidebar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg></button>
  </div>
  <div class="sb-scroll">
    <nav style="padding:8px 0">
      <!-- Dashboard -->
      <div class="ni active" data-action="navegar" data-url="<?= $baseUrl . \App\Auth\Rbac::getDashboardRoute() ?>">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          <span class="ni-label">Dashboard</span>
        </div>
        <div class="ni-tooltip">Dashboard</div>
      </div>

      <!-- Grupo: Cadastro -->
      <div class="nav-lbl">Cadastro</div>
      
      <!-- Clientes (apenas administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('clientes.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/clientes">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span class="ni-label">Clientes</span>
        </div>
        <div class="ni-tooltip">Clientes</div>
      </div>
      <?php endif; ?>

      <!-- Demandantes (apenas administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('demandantes.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/compradores">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span class="ni-label">Compradores</span>
        </div>
        <div class="ni-tooltip">Compradores</div>
      </div>
      <?php endif; ?>

      <!-- Fornecedores (apenas administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('fornecedores.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/fornecedores">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
          <span class="ni-label">Fornecedores</span>
        </div>
        <div class="ni-tooltip">Fornecedores</div>
      </div>
      <?php endif; ?>

      <!-- Sublocações (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('sublocacoes.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/sublocacoes">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          <span class="ni-label">Sublocações</span>
        </div>
        <div class="ni-tooltip">Sublocações</div>
      </div>
      <?php endif; ?>

      <!-- Colaboradores (apenas administrativo) -->
      <?php if (\App\Auth\Rbac::check('colaboradores.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/colaboradores">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span class="ni-label">Colaboradores</span>
        </div>
        <div class="ni-tooltip">Colaboradores</div>
      </div>
      <?php endif; ?>

      <!-- Comercial (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('produtores.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/comercial">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span class="ni-label">Comercial</span>
        </div>
        <div class="ni-tooltip">Comercial</div>
      </div>
      <?php endif; ?>

      <!-- Planilhas (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('planilhas.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/planilhas">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span class="ni-label">Planilhas</span>
        </div>
        <div class="ni-tooltip">Planilhas</div>
      </div>
      <?php endif; ?>

      <!-- Estoque (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('estoque.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/estoque">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          <span class="ni-label">Estoque</span>
        </div>
        <div class="ni-tooltip">Estoque</div>
      </div>
      <?php endif; ?>

      <!-- Eventos (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('eventos.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/eventos">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span class="ni-label">Eventos</span>
        </div>
        <div class="ni-tooltip">Eventos</div>
      </div>
      <?php endif; ?>

      <!-- Grupo: Financeiro -->
      <div class="nav-lbl">Financeiro</div>

      <!-- Contas a Pagar (administrativo e comercial) -->
      <?php if (\App\Auth\Rbac::check('contas_pagar.listar')): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/contas-pagar">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14v6H5v-6H3l6-6 6 6h-2v6h-4v-6H9z"/></svg>
          <span class="ni-label">Contas a Pagar</span>
        </div>
        <div class="ni-tooltip">Contas a Pagar</div>
      </div>
      <?php endif; ?>



      <!-- Grupo: Configurações -->
      <?php if (\App\Auth\Rbac::isAdministrativo()): ?>
      <div class="nav-lbl">Configurações</div>

      <!-- Configurações -->
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/configuracoes">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.573-1.066z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span class="ni-label">Sistema</span>
        </div>
        <div class="ni-tooltip">Sistema</div>
      </div>
      <?php endif; ?>

      <!-- Usuários (apenas admin) -->
      <?php if (\App\Auth\Rbac::isAdministrativo()): ?>
      <div class="ni" data-action="navegar" data-url="<?= $baseUrl ?>/usuarios">
        <div class="ni-left">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span class="ni-label">Usuários</span>
        </div>
        <div class="ni-tooltip">Usuários</div>
      </div>


      <?php endif; ?>
    </nav>
  </div>
  <div class="sb-foot">
    <div class="user-card" data-action="open-right">
      <?php $user = \App\Auth\Rbac::getUser(); ?>
      <?php if ($user): ?>
      <div class="avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></div>
      <div class="user-texts">
        <div class="u-name"><?= htmlspecialchars($user['name'] ?? 'Usuário') ?></div>
        <div class="u-role"><?= \App\Auth\Rbac::getRoleLabel($user['role'] ?? 'user') ?></div>
      </div>
      <?php else: ?>
      <div class="avatar">?</div>
      <div class="user-texts">
        <div class="u-name">Visitante</div>
        <div class="u-role">Faça login</div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</aside>

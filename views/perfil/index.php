<?php
require_once __DIR__ . '/../layout/header.php';
$u = $usuario;
$roleColors = [
    'administrativo' => 'var(--neon-cyan)',
    'comercial'      => 'var(--neon-yellow)',
    'estoquista'     => 'var(--neon-green)',
];
$cor = $roleColors[$u['role'] ?? ''] ?? 'var(--neon-cyan)';
?>

<div class="section-header">
  <div class="section-icon">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
  </div>
  <div>
    <div class="section-title">Meu Perfil</div>
    <div class="section-sub"><?= htmlspecialchars($u['name'] ?? '') ?></div>
  </div>
</div>
<div class="divider"></div>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div style="background:rgba(0,255,136,0.12);border:1px solid var(--neon-green);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--neon-green);font-size:14px;display:flex;align-items:center;gap:10px">
  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;min-width:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div style="background:rgba(255,77,77,0.12);border:1px solid var(--neon-red);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--neon-red);font-size:14px;display:flex;align-items:center;gap:10px">
  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;min-width:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

  <!-- Card de identidade -->
  <div class="card" style="padding:28px;text-align:center">
    <div style="width:80px;height:80px;border-radius:50%;background:<?= $cor ?>;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#0a0a14;margin:0 auto 16px;box-shadow:0 0 24px <?= $cor ?>55">
      <?= strtoupper(mb_substr($u['name'] ?? 'U', 0, 2)) ?>
    </div>
    <div style="font-size:18px;font-weight:700;color:var(--text-1);margin-bottom:6px"><?= htmlspecialchars($u['name'] ?? '') ?></div>
    <div style="font-size:13px;color:var(--text-3);margin-bottom:14px"><?= htmlspecialchars($u['email'] ?? '') ?></div>
    <div style="display:inline-block;padding:4px 14px;border-radius:20px;border:1px solid <?= $cor ?>;color:<?= $cor ?>;font-size:12px;font-weight:600;letter-spacing:.5px">
      <?= \App\Auth\Rbac::getRoleLabel($u['role'] ?? '') ?>
    </div>
  </div>

  <!-- Formulário -->
  <div class="card" style="padding:28px">
    <form method="POST" action="<?= $baseUrl ?>/meu-perfil/salvar" autocomplete="off">
      <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

      <div style="margin-bottom:24px">
        <div style="font-size:13px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px">Dados pessoais</div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Nome</label>
          <input type="text" name="name" class="fi" value="<?= htmlspecialchars($u['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input type="email" class="fi" value="<?= htmlspecialchars($u['email'] ?? '') ?>" disabled style="opacity:.5;cursor:not-allowed">
          <div style="font-size:11px;color:var(--text-3);margin-top:4px">O e-mail só pode ser alterado por um administrador.</div>
        </div>
      </div>

      <div class="divider"></div>

      <div style="margin-top:20px">
        <div style="font-size:13px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px">Alterar senha <span style="font-weight:400;text-transform:none;font-size:12px">(opcional)</span></div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Senha atual</label>
          <input type="password" name="senha_atual" class="fi" autocomplete="current-password">
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Nova senha</label>
          <input type="password" name="nova_senha" class="fi" autocomplete="new-password" minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Confirmar nova senha</label>
          <input type="password" name="confirmar_senha" class="fi" autocomplete="new-password">
        </div>
      </div>

      <div style="margin-top:24px">
        <button type="submit" class="btn btn-cyan">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          Salvar alterações
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

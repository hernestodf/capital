<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/toggle/toggle.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $appName ?? 'SisLoc' ?> v4.0 — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg-darkest:#EDF1F7;--bg-dark:#E0E8F2;--bg-surface:#D0DCF0;--bg-card:#FFFFFF;--bg-elevated:#FFFFFF;--bg-hover:#BFD0E8;--bg-border:#9FB4CE;--bg-border-sub:#C4D4E6;
      --text-1:#0D1829;--text-2:#1E2E45;--text-3:#4A6080;--text-4:#6B82A0;
      --neon-red:#D62B2B;--neon-red-glow:rgba(214,43,43,0.28);
      --neon-orange:#D95B10;--neon-orange-glow:rgba(217,91,16,0.28);
      --neon-yellow:#C07C00;--neon-yellow-glow:rgba(192,124,0,0.28);
      --neon-green:#1F7A45;--neon-green-glow:rgba(31,122,69,0.28);
      --neon-cyan:#0B6E8C;--neon-cyan-glow:rgba(11,110,140,0.28);
      --neon-blue:#1A44A0;--neon-blue-glow:rgba(26,68,160,0.28);
      --neon-purple:#5A1A9A;--neon-purple-glow:rgba(90,26,154,0.28);
      --shadow-sm:0 2px 8px rgba(15,23,42,0.12);--shadow-md:0 4px 16px rgba(15,23,42,0.15);--shadow-lg:0 8px 32px rgba(15,23,42,0.18);--shadow-xl:0 16px 48px rgba(15,23,42,0.22);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%;font-family:'Inter',sans-serif;background:var(--bg-darkest);color:var(--text-1)}

    /* ======== LOGIN PAGE ======== */
    .login-page{display:flex;height:100vh;width:100vw}

    /* Lado esquerdo — Branding */
    .login-brand{flex:1;background:#0F172A;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 48px;position:relative;overflow:hidden}
    .login-brand::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 80%,rgba(11,110,140,0.25) 0%,transparent 60%),radial-gradient(ellipse at 70% 20%,rgba(90,26,154,0.15) 0%,transparent 60%)}
    .login-brand-content{position:relative;z-index:1;text-align:center;max-width:480px}
    .login-brand-logo{width:80px;height:80px;border-radius:22px;background:var(--neon-cyan);display:grid;place-items:center;box-shadow:0 8px 32px var(--neon-cyan-glow);margin:0 auto 28px}
    .login-brand-logo svg{width:38px;height:38px;color:#fff}
    .login-brand h1{font-size:36px;font-weight:800;color:#fff;letter-spacing:-1px;margin-bottom:8px}
    .login-brand h1 span{color:var(--neon-cyan)}
    .login-brand>p{font-size:16px;color:rgba(255,255,255,0.5);line-height:1.7;margin-bottom:40px}
    .login-brand-features{display:flex;flex-direction:column;gap:16px;text-align:left;width:100%}
    .login-brand-feat{display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);transition:all .2s}
    .login-brand-feat:hover{background:rgba(255,255,255,0.08);border-color:var(--neon-cyan);box-shadow:0 4px 20px var(--neon-cyan-glow)}
    .login-brand-feat-ico{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;flex-shrink:0}
    .login-brand-feat-ico svg{width:20px;height:20px;color:#fff}
    .login-brand-feat-text{font-size:14px;font-weight:500;color:rgba(255,255,255,0.8)}
    .login-brand-feat-text small{display:block;font-size:12px;color:rgba(255,255,255,0.4);font-weight:400;margin-top:2px}
    .login-brand-ver{position:absolute;bottom:24px;left:0;right:0;text-align:center;font-size:11px;color:rgba(255,255,255,0.25);font-family:'JetBrains Mono',monospace}

    /* Lado direito — Formulario */
    .login-form-side{width:520px;flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:48px;background:var(--bg-card);border-left:1px solid var(--bg-border-sub);position:relative}
    .login-form-wrap{width:100%;max-width:400px}
    .login-form-header{margin-bottom:36px}
    .login-form-header h2{font-size:26px;font-weight:800;color:var(--text-1);letter-spacing:-0.5px;margin-bottom:6px}
    .login-form-header p{font-size:14px;color:var(--text-3);line-height:1.6}

    /* Inputs */
    .login-fg{margin-bottom:20px}
    .login-fl{font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px}
    .login-fi{width:100%;background:var(--bg-surface);border:2px solid var(--bg-border-sub);color:var(--text-1);padding:14px 16px;border-radius:12px;font-family:'Inter',sans-serif;font-size:15px;outline:none;transition:border-color .2s,box-shadow .2s}
    .login-fi:focus{border-color:var(--neon-cyan);box-shadow:0 0 0 4px var(--neon-cyan-glow)}
    .login-fi::placeholder{color:var(--text-4)}

    /* Input com icone */
    .login-input-wrap{position:relative}
    .login-input-ico{position:absolute;left:16px;top:50%;transform:translateY(-50%);width:20px;height:20px;color:var(--text-4);pointer-events:none;transition:color .2s}
    .login-input-wrap .login-fi{padding-left:48px}
    .login-input-wrap:focus-within .login-input-ico{color:var(--neon-cyan)}

    /* Toggle olho senha */
    .login-pw-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-4);padding:4px;transition:color .15s;display:grid;place-items:center}
    .login-pw-toggle:hover{color:var(--neon-cyan)}
    .login-pw-toggle svg{width:20px;height:20px}

    /* Esqueceu senha */
    .login-forgot{font-size:13.5px;font-weight:600;color:var(--neon-cyan);text-decoration:none;transition:color .15s}
    .login-forgot:hover{color:var(--neon-blue);text-decoration:underline}

    /* Botao entrar */
    .login-btn-enter{width:100%;padding:16px;border-radius:12px;background:var(--neon-cyan);color:#fff;border:2px solid var(--neon-cyan);font-size:16px;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s;box-shadow:0 6px 24px var(--neon-cyan-glow);display:flex;align-items:center;justify-content:center;gap:10px;position:relative}
    .login-btn-enter:hover{transform:translateY(-2px);box-shadow:0 8px 32px var(--neon-cyan-glow);filter:brightness(1.05)}
    .login-btn-enter:active{transform:translateY(0)}
    .login-btn-enter.loading{color:transparent;pointer-events:none}
    .login-btn-enter .btn-spin{width:20px;height:20px;border-radius:50%;border:3px solid rgba(255,255,255,0.3);border-top-color:#fff;animation:spin .7s linear infinite;position:absolute}

    /* Divider */
    .login-divider{display:flex;align-items:center;gap:14px;margin:28px 0;color:var(--text-4);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.8px}
    .login-divider::before,.login-divider::after{content:'';flex:1;height:1px;background:var(--bg-border-sub)}

    /* Botao Google */
    .login-btn-google{width:100%;padding:13px;border-radius:12px;background:var(--bg-surface);color:var(--text-2);border:2px solid var(--bg-border-sub);font-size:14px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:10px}
    .login-btn-google:hover{border-color:var(--neon-cyan);color:var(--neon-cyan);background:var(--bg-hover)}

    /* Erro */
    .login-error{display:none;padding:12px 16px;border-radius:10px;background:rgba(214,43,43,0.1);border:2px solid var(--neon-red);color:var(--neon-red);font-size:13.5px;font-weight:600;margin-bottom:20px;align-items:center;gap:10px;box-shadow:0 4px 16px var(--neon-red-glow)}
    .login-error svg{width:18px;height:18px;flex-shrink:0}
    .login-error.show{display:flex}

    /* Spin */
    @keyframes spin{to{transform:rotate(360deg)}}
    @keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-6px)}80%{transform:translateX(6px)}}

    /* Responsividade */
    @media(max-width:1100px){.login-brand{display:none}.login-form-side{width:100%;border-left:none}}
    @media(max-width:1280px){.login-form-side{padding:36px}.login-brand{padding:40px 32px}}
  </style>
</head>
<body>

<div class="login-page">

  <!-- LADO ESQUERDO — BRANDING -->
  <div class="login-brand">
    <div class="login-brand-content">
      <div class="login-brand-logo">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/></svg>
      </div>
      <h1>Sis<span>Loc</span> v4.0</h1>
      <p style="margin-bottom:48px;line-height:1.8;font-size:17px;color:rgba(255,255,255,0.7)">Sistema de gestao de locacao de equipamentos. Gerencie contratos, estoque e clientes em uma unica plataforma.</p>

      <div class="login-brand-features" style="gap:20px">
        <div class="login-brand-feat">
          <div class="login-brand-feat-ico" style="background:var(--neon-cyan);box-shadow:0 4px 14px var(--neon-cyan-glow)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <div class="login-brand-feat-text">Gestao Completa<small>Contratos, locacoes e devolucoes</small></div>
        </div>
        <div class="login-brand-feat">
          <div class="login-brand-feat-ico" style="background:var(--neon-green);box-shadow:0 4px 14px var(--neon-green-glow)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
          <div class="login-brand-feat-text">Controle de Estoque<small>Equipamentos em tempo real</small></div>
        </div>
        <div class="login-brand-feat">
          <div class="login-brand-feat-ico" style="background:var(--neon-purple);box-shadow:0 4px 14px var(--neon-purple-glow)"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
          <div class="login-brand-feat-text">Relatorios Avancados<small>Dashboard e indicadores</small></div>
        </div>
      </div>
    </div>
    <div class="login-brand-ver">SisLoc v4.0 · Reactive Architecture · Neon Themes</div>
  </div>

  <!-- LADO DIREITO — FORMULARIO -->
  <div class="login-form-side">
    <div class="login-form-wrap">

      <div class="login-form-header">
        <h2>Bem-vindo de volta</h2>
        <p>Entre com suas credenciais para acessar o sistema.</p>
      </div>

      <!-- Mensagem de erro -->
      <div class="login-error" id="login-error">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="login-error-msg">Credenciais invalidas. Tente novamente.</span>
      </div>

      <form id="login-form" method="POST" action="<?= $baseUrl ?>/auth/login" onsubmit="handleLogin(event)">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <!-- Email -->
        <div class="login-fg">
          <div class="login-fl">Email</div>
          <div class="login-input-wrap">
            <div class="login-input-ico"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
            <input class="login-fi" type="email" name="email" id="login-email" placeholder="seu@email.com" required autocomplete="email"/>
          </div>
        </div>

        <!-- Senha -->
        <div class="login-fg">
          <div class="login-fl">Senha</div>
          <div class="login-input-wrap">
            <div class="login-input-ico"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
            <input class="login-fi" type="password" name="password" id="login-password" placeholder="••••••••" required autocomplete="current-password"/>
            <button type="button" class="login-pw-toggle" data-action="toggle-password">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" id="pw-eye-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <!-- Esqueceu -->
        <div style="text-align:right;margin-bottom:28px">
          <a href="<?= $baseUrl ?>/auth/forgot-password" class="login-forgot">Esqueceu a senha?</a>
        </div>

        <!-- Botao Entrar -->
        <button type="submit" class="login-btn-enter" id="btn-enter">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
          Entrar
        </button>
      </form>



    </div>
  </div>

</div>

<script>
function togglePassword(){
  var i=document.getElementById('login-password'),ic=document.getElementById('pw-eye-icon');
  if(i.type==='password'){i.type='text';ic.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/>'}
  else{i.type='password';ic.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'}
}
function handleLogin(e){
  e.preventDefault();
  var form=document.getElementById('login-form'),btn=document.getElementById('btn-enter'),err=document.getElementById('login-error');
  err.classList.remove('show');
  btn.classList.add('loading');
  btn.innerHTML='<div class="btn-spin"></div>';

  var formData=new FormData(form);

  fetch(form.action,{method:'POST',body:formData,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json()})
    .then(function(data){
      btn.classList.remove('loading');
      btn.innerHTML='<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg> Entrar';

      if(data.success){
        window.location.href=data.redirect||'<?= $baseUrl ?>/';
      }else{
        document.getElementById('login-error-msg').textContent=data.message||data.error||'Credenciais invalidas. Verifique seu email e senha.';
        err.classList.add('show');
        form.style.animation='shake .4s ease';
        setTimeout(function(){form.style.animation=''},400);
      }
    })
    .catch(function(){
      btn.classList.remove('loading');
      btn.innerHTML='<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg> Entrar';
      document.getElementById('login-error-msg').textContent='Erro de conexao. Tente novamente.';
      err.classList.add('show');
      form.style.animation='shake .4s ease';
      setTimeout(function(){form.style.animation=''},400);
    });
}
document.getElementById('login-email').addEventListener('input',function(){document.getElementById('login-error').classList.remove('show')});
document.getElementById('login-password').addEventListener('input',function(){document.getElementById('login-error').classList.remove('show')});
</script>

</body>
</html>

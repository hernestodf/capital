<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo ?? 'Erro') ?> - Presenca</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#f5f5f5; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.card { background:white; border-radius:12px; padding:40px; text-align:center; max-width:400px; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
.icon { width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
.icon svg { width:32px; height:32px; color:#ef4444; }
h1 { font-size:22px; color:#1f2937; margin-bottom:8px; }
p { color:#6b7280; font-size:14px; line-height:1.5; }
</style>
</head>
<body>
<div class="card">
<div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div>
<h1><?= htmlspecialchars($titulo ?? 'Erro') ?></h1>
<p><?= htmlspecialchars($mensagem ?? 'Ocorreu um erro ao processar sua solicitacao.') ?></p>
</div>
</body>
</html>

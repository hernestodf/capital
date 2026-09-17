<?php
// Rotas públicas — sem middleware de autenticação

// Raiz
$app->router()->get('/', function() use ($app) {
    if (\App\Auth\Rbac::isGuest()) {
        return $app->redirect('/auth/login');
    }
    return $app->redirect(\App\Auth\Rbac::getDashboardRoute());
});

// Autenticação
$app->router()->group('/auth', function($router) {
    $router->get('/login',        [App\Controllers\AuthController::class, 'login']);
    $router->post('/login',       [App\Controllers\AuthController::class, 'doLogin']);
    $router->get('/logout',       [App\Controllers\AuthController::class, 'logout']);
    $router->post('/logout',      [App\Controllers\AuthController::class, 'logout']);
    $router->get('/session-info', [App\Controllers\AuthController::class, 'sessionInfo']);
});

// Cadastro via site ProFox (formulários públicos) — com proteção anti-abuso
$publicCsrf = [\App\Http\Middleware\PublicOriginMiddleware::class];
$app->router()->group('/cadastro-colaborador', function($router) {
    $router->post('/', [App\Controllers\PublicoController::class, 'storeColaborador']);
}, $publicCsrf);
$app->router()->group('/cadastro-fornecedor', function($router) {
    $router->post('/', [App\Controllers\PublicoController::class, 'storeFornecedor']);
}, $publicCsrf);

// Categorias/subcategorias/funções para o site ProFox
$app->router()->get('/categorias',        [App\Controllers\PublicoCategoriaController::class, 'categorias']);
$app->router()->get('/subcategorias/{id}',[App\Controllers\PublicoCategoriaController::class, 'subcategorias']);
$app->router()->get('/funcoes',           [App\Controllers\PublicoCategoriaController::class, 'funcoes']);

// Verificação de colaborador via token (e-mail)
$app->router()->group('/colaboradores/verificar', function($router) {
    $router->get('/{token}',  [App\Controllers\VerificacaoController::class, 'mostrarFormulario']);
    $router->post('/{token}', [App\Controllers\VerificacaoController::class, 'processarVerificacao']);
});

// Presença pública via token
$app->router()->group('/presenca', function($router) {
    $router->get('/{token}',  [App\Controllers\PresencaPublicController::class, 'mostrarFormulario']);
    $router->post('/{token}', [App\Controllers\PresencaPublicController::class, 'registrarPresenca']);
    $router->get('/confirmar-alocacao/{token}',  [App\Controllers\PresencaPublicController::class, 'confirmarAlocacaoPage']);
    $router->post('/confirmar-alocacao/{token}', [App\Controllers\PresencaPublicController::class, 'confirmarAlocacaoAction']);
});

// Proxy para APIs externas (ViaCEP, BrasilAPI)
$app->router()->group('/proxy', function($router) {
    $router->get('/cnpj/{cnpj}', [App\Controllers\ProxyController::class, 'cnpj']);
    $router->get('/cep/{cep}',   [App\Controllers\ProxyController::class, 'cep']);
}, [\App\Http\Middleware\AuthMiddleware::class]);

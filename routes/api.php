<?php
// API v1 — integração externa via X-API-Key

$app->router()->group('/api/v1', function($router) {
    // Autenticação JWT
    $router->post('/auth/login',  [App\Controllers\Api\AuthApiController::class, 'login']);
    $router->get('/auth/session', [App\Controllers\Api\AuthApiController::class, 'session']);
    $router->post('/auth/logout', [App\Controllers\Api\AuthApiController::class, 'logout']);

    // Sessão cookie PHP (appmontagem)
    $router->post('/sessao/login',    [App\Controllers\Api\SessaoApiController::class, 'login']);
    $router->get('/eventos/ativos',   [App\Controllers\Api\SessaoApiController::class, 'ativos']);
    $router->get('/eventos/salas/{id}',[App\Controllers\SalaController::class, 'listByEvento']);

    // Montagem (appmontagem)
    $router->get('/montagem/listar/{idEvento}',  [App\Controllers\Api\MontagemApiController::class, 'listar']);
    $router->post('/montagem/inserir-lote',      [App\Controllers\Api\MontagemApiController::class, 'inserirLote']);
    $router->post('/montagem/inserir-lote-ext',  [App\Controllers\Api\SessaoApiController::class, 'inserirLote']);

    // Devolução (appdevolucao)
    $router->get('/devolucao/seriais/{idEvento}',  [App\Controllers\Api\DevolucaoApiController::class, 'listarSeriaisMontados']);
    $router->post('/devolucao/devolver',           [App\Controllers\Api\DevolucaoApiController::class, 'devolverSerial']);
    $router->post('/devolucao/devolver-lote',      [App\Controllers\Api\DevolucaoApiController::class, 'devolverLote']);
    $router->get('/devolucao/listar/{idEvento}',   [App\Controllers\Api\DevolucaoApiController::class, 'listarDevolucoes']);
    $router->get('/devolucao/stats/{idEvento}',    [App\Controllers\Api\DevolucaoApiController::class, 'stats']);

    // Cadastros
    $router->get('/colaboradores',  [App\Controllers\Api\ColaboradorApiController::class, 'index']);
    $router->post('/colaboradores', [App\Controllers\Api\ColaboradorApiController::class, 'store']);
    $router->get('/fornecedores',   [App\Controllers\Api\FornecedorApiController::class, 'index']);
    $router->post('/fornecedores',  [App\Controllers\Api\FornecedorApiController::class, 'store']);
    $router->get('/categorias',     [App\Controllers\Api\CategoriaApiController::class, 'index']);
    $router->post('/categorias',    [App\Controllers\Api\CategoriaApiController::class, 'store']);
    $router->get('/subcategorias',  [App\Controllers\Api\SubcategoriaApiController::class, 'index']);
    $router->post('/subcategorias', [App\Controllers\Api\SubcategoriaApiController::class, 'store']);

    // Eventos
    $router->get('/eventos', [App\Controllers\Api\EventoApiController::class, 'ativos']);

    // Seriais — cadastro em lote (appmontagem)
    $router->post('/seriais/store-batch', [App\Controllers\SerialProdutoController::class, 'storeBatchApi']);

    // PresençaApp — entradas e saídas de colaboradores
    $router->get('/presencaapp/eventos',                          [App\Controllers\Api\PresencaAppApiController::class, 'eventos']);
    $router->get('/presencaapp/evento/{idEvento}/colaboradores',  [App\Controllers\Api\PresencaAppApiController::class, 'colaboradores']);
    $router->post('/presencaapp/entrada',                         [App\Controllers\Api\PresencaAppApiController::class, 'entrada']);
    $router->post('/presencaapp/saida',                           [App\Controllers\Api\PresencaAppApiController::class, 'saida']);
}, [\App\Http\Middleware\ApiKeyMiddleware::class]);

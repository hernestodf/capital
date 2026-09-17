<?php
// Cron jobs e webhooks — autenticação própria (sem sessão)

// Cron (X-Cron-Secret header)
$app->router()->group('/cron', function($router) {
    $router->get('/presencas-diarias', [App\Controllers\CronRHController::class, 'presencasDiarias']);
    $router->get('/rh-notificacoes', function() {
        $service = new \App\Service\EventoRHNotificacaoService();
        $result = $service->enviarNotificacoes();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    });
}, [\App\Http\Middleware\CronAuthMiddleware::class]);

<?php

use App\Core\Application;

// Auto-detecta raiz do projeto: local (public/ separado) ou cPanel (tudo na raiz do subdomínio)
$appRoot = file_exists(dirname(__DIR__) . '/vendor/autoload.php')
    ? dirname(__DIR__)
    : __DIR__;
require_once $appRoot . '/vendor/autoload.php';

// CORS — hanya allow origin yang terdaftar
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$corsOrigin = '';
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = ['https://profox.sisloc.online', 'https://profoxmt.sisloc.online', 'https://profoxba.sisloc.online', 'http://localhost', 'https://localhost'];
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins)) {
        $corsOrigin = $origin;
    }
}

if (!empty($corsOrigin)) {
    header("Access-Control-Allow-Origin: $corsOrigin");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

$app = Application::getInstance();

require_once $appRoot . '/routes/publico.php';
require_once $appRoot . '/routes/cron.php';
require_once $appRoot . '/routes/api.php';
require_once $appRoot . '/routes/web.php';

$app->run();

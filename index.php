<?php

// Ponto de entrada da raiz — redireciona para public/
// Todas as rotas da aplicação estão definidas em public/index.php
$host   = $_SERVER['HTTP_HOST'] ?? 'capital.sisloc.online';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$target = $scheme . '://' . $host . '/public' . ($uri === '/' ? '/auth/login' : $uri);
header('Location: ' . $target, true, 301);
exit;

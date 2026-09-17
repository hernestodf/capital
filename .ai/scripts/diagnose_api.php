<?php
// diagnose_api.php - API de diagnóstico para chamadas remotas
// Uso: Acessar via HTTP ou CLI

header('Content-Type: application/json');

function checkPHP() {
    return ['version' => PHP_VERSION, 'status' => 'ok'];
}

function checkMySQL() {
    $host = getenv('DB_ONLINE_HOST') ?: 'localhost';
    $user = getenv('DB_ONLINE_USER') ?: 'capital';
    $pass = getenv('DB_ONLINE_PASS') ?: 'Marcelo123';
    $db = getenv('DB_ONLINE_NAME') ?: 'capital';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT VERSION() as v");
        return ['version' => $stmt->fetchColumn(), 'status' => 'ok'];
    } catch (Exception $e) {
        return ['error' => $e->getMessage(), 'status' => 'failed'];
    }
}

function checkDisk() {
    $free = disk_free_space('/var/www/html/capital');
    $total = disk_total_space('/var/www/html/capital');
    return [
        'free' => $free,
        'total' => $total,
        'percent' => round(($free/$total)*100, 2)
    ];
}

$report = [
    'timestamp' => date('c'),
    'php' => checkPHP(),
    'mysql' => checkMySQL(),
    'disk' => checkDisk(),
    'status' => 'ok'
];

echo json_encode($report, JSON_PRETTY_PRINT);
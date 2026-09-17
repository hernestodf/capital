<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE DIRETÓRIOS EM PRODUÇÃO ===\n\n";

$cwd = getcwd();
echo "CWD: " . $cwd . "\n";
echo "DocumentRoot: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n\n";

// Listar pasta atual
echo "=== Conteúdo da pasta atual (" . basename($cwd) . ") ===\n";
if ($handle = opendir($cwd)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $type = is_link($entry) ? 'LINK' : (is_dir($entry) ? 'DIR' : 'FILE');
            echo " - $entry ($type)\n";
        }
    }
    closedir($handle);
}

// Listar pasta pai
$parent = dirname($cwd);
echo "\n=== Conteúdo da pasta pai (" . basename($parent) . ") ===\n";
if ($handle = opendir($parent)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $type = is_link($parent . '/' . $entry) ? 'LINK' : (is_dir($parent . '/' . $entry) ? 'DIR' : 'FILE');
            echo " - $entry ($type)\n";
        }
    }
    closedir($handle);
}

// Listar pasta pai da pai (raiz do subdomínio se estiver em views/public)
$root = dirname($parent);
echo "\n=== Conteúdo da raiz do subdomínio (" . basename($root) . ") ===\n";
if (is_dir($root) && $handle = opendir($root)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $type = is_link($root . '/' . $entry) ? 'LINK' : (is_dir($root . '/' . $entry) ? 'DIR' : 'FILE');
            echo " - $entry ($type)\n";
        }
    }
    closedir($handle);
}

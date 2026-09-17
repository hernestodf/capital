<?php
/**
 * Toast Component
 *
 * @param string $label     - Texto do botao trigger
 * @param string $variant   - Cor do botao: red, green, cyan, yellow, purple, orange
 * @param string $type      - Tipo do toast: red, green, cyan, yellow, purple, orange
 * @param string $title     - Titulo do toast
 * @param string $message   - Mensagem do toast
 * @param int    $duration  - Duracao em ms (default: 4500)
 */

function renderToastTrigger($config) {
    $label = $config['label'] ?? 'Mostrar Toast';
    $variant = $config['variant'] ?? 'cyan';
    $type = $config['type'] ?? 'cyan';
    $title = $config['title'] ?? 'Notificacao';
    $message = $config['message'] ?? '';
    $duration = $config['duration'] ?? 4500;

    $onclick = "showToast('{$type}','" . addslashes($title) . "','" . addslashes($message) . "',{$duration})";

    return "<button class=\"btn btn-{$variant}\" onclick=\"{$onclick}\">{$label}</button>";
}

function renderToastContainer() {
    return '<div id="toast-container"></div>';
}

// Exemplos de uso:

// echo renderToastTrigger([
//     'label' => 'Toast Sucesso',
//     'variant' => 'green',
//     'type' => 'green',
//     'title' => 'Sucesso!',
//     'message' => 'Operacao concluida com exito.'
// ]);

// echo renderToastTrigger([
//     'label' => 'Toast Erro',
//     'variant' => 'red',
//     'type' => 'red',
//     'title' => 'Erro!',
//     'message' => 'Falha ao processar.',
//     'duration' => 6000
// ]);

// echo renderToastContainer();

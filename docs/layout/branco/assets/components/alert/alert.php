<?php
/**
 * Alert Component
 *
 * @param string $variant     - red, green, yellow, cyan, blue, purple
 * @param string $title       - Titulo do alert
 * @param string $message     - Descricao do alert
 * @param string $icon        - SVG customizado (opcional, auto-gerado por variant)
 * @param bool   $dismissible - Mostrar botao de fechar (default: false)
 */

function renderAlert($config) {
    $variant = $config['variant'] ?? 'cyan';
    $title = $config['title'] ?? 'Alerta';
    $message = $config['message'] ?? '';
    $dismissible = $config['dismissible'] ?? false;
    $icon = $config['icon'] ?? '';

    if (!$icon) {
        $icons = [
            'red' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'green' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'yellow' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'cyan' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'blue' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'purple' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
        ];
        $icon = $icons[$variant] ?? $icons['cyan'];
    }

    $closeBtn = '';
    if ($dismissible) {
        $closeBtn = '<button class="alert-close" onclick="this.parentElement.remove()"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
    }

    return '<div class="alert ' . $variant . '">' .
        '<div class="alert-ico">' . $icon . '</div>' .
        '<div class="alert-body"><div class="alert-title">' . $title . '</div>' .
        ($message ? '<div class="alert-desc">' . $message . '</div>' : '') .
        '</div>' .
        $closeBtn .
        '</div>';
}

// Exemplos de uso:

// echo renderAlert([
//     'variant' => 'red',
//     'title' => 'Erro Critico',
//     'message' => 'Ocorreu um erro ao processar sua solicitacao.',
//     'dismissible' => true
// ]);

// echo renderAlert([
//     'variant' => 'green',
//     'title' => 'Operacao Concluida',
//     'message' => 'Locacao criada com sucesso.'
// ]);

<?php
/**
 * Timeline Component
 *
 * @param array $items - Array de ['time', 'title', 'desc', 'color' (cyan/green/red/yellow/purple), 'icon' (SVG opcional)]
 */

function renderTimeline($config) {
    $items = $config['items'] ?? [];

    $icons = [
        'cyan' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>',
        'green' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'red' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'yellow' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        'purple' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>',
    ];

    $html = '<div class="timeline">';
    foreach ($items as $item) {
        $color = $item['color'] ?? 'cyan';
        $icon = $item['icon'] ?? ($icons[$color] ?? $icons['cyan']);

        $html .= '<div class="tl-item">' .
            '<div class="tl-left">' .
            '<div class="tl-dot ' . $color . '">' . $icon . '</div>' .
            '<div class="tl-line"></div>' .
            '</div>' .
            '<div class="tl-content">' .
            '<div class="tl-time">' . ($item['time'] ?? '') . '</div>' .
            '<div class="tl-title">' . ($item['title'] ?? '') . '</div>' .
            '<div class="tl-desc">' . ($item['desc'] ?? '') . '</div>' .
            '</div>' .
            '</div>';
    }
    $html .= '</div>';

    return $html;
}

// Exemplos de uso:

// echo renderTimeline([
//     'items' => [
//         ['time' => 'Hoje, 09:14', 'title' => 'Locacao aprovada', 'desc' => 'Construtora Nova Era - 5x Andaime Tubular', 'color' => 'green'],
//         ['time' => 'Ontem, 16:30', 'title' => 'Equipamento devolvido', 'desc' => 'Betoneira 400L devolvida em bom estado.', 'color' => 'cyan'],
//         ['time' => '28/03, 11:00', 'title' => 'Manutencao programada', 'desc' => 'Compressor de ar enviado para revisao.', 'color' => 'yellow'],
//         ['time' => '25/03, 08:45', 'title' => 'Atraso na devolucao', 'desc' => 'Cliente com 3 dias de atraso.', 'color' => 'red'],
//     ]
// ]);

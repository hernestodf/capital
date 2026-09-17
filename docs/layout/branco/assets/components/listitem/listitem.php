<?php
/**
 * List Item Component
 *
 * @param string $title    - Titulo do item
 * @param string $subtitle - Subtitulo
 * @param string $icon     - SVG opcional para icone esquerdo
 * @param string $iconBg   - Background do icone (default: var(--neon-cyan))
 * @param string $action   - HTML do lado direito (e.g. badge)
 * @param string $onclick  - Handler onclick
 */

function renderListItem($config) {
    $title = $config['title'] ?? '';
    $subtitle = $config['subtitle'] ?? '';
    $icon = $config['icon'] ?? '';
    $iconBg = $config['iconBg'] ?? 'var(--neon-cyan)';
    $action = $config['action'] ?? '';
    $onclick = $config['onclick'] ?? '';

    $onclickAttr = $onclick ? ' onclick="' . $onclick . '"' : '';

    $iconHtml = '';
    if ($icon) {
        $iconHtml = '<div class="list-item-icon" style="background:' . $iconBg . '">' . $icon . '</div>';
    }

    return '<div class="list-item"' . $onclickAttr . '>' .
        $iconHtml .
        '<div class="list-item-body"><div class="list-item-title">' . $title . '</div>' .
        ($subtitle ? '<div class="list-item-sub">' . $subtitle . '</div>' : '') .
        '</div>' .
        ($action ? '<div class="list-item-action">' . $action . '</div>' : '') .
        '</div>';
}

/**
 * Grupo de List Items com dividers
 */
function renderListItemGroup($items) {
    $html = '';
    foreach ($items as $i => $item) {
        if ($i > 0) {
            $html .= '<div class="list-item-divider"></div>';
        }
        $html .= renderListItem($item);
    }
    return $html;
}

// Exemplos de uso:

// echo renderListItemGroup([
//     [
//         'title' => 'Locacao #3812',
//         'subtitle' => 'Construtora Nova Era - R$ 4.200',
//         'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>',
//         'action' => '<span class="badge green sm"><span class="dot pulse"></span>Ativo</span>'
//     ],
//     [
//         'title' => 'Locacao #3811',
//         'subtitle' => 'Rodrigo Mendes - R$ 980',
//         'iconBg' => 'var(--neon-yellow)',
//         'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
//         'action' => '<span class="badge yellow sm">Pendente</span>'
//     ]
// ]);

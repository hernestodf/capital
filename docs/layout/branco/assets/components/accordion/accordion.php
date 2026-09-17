<?php
/**
 * Accordion Component
 *
 * @param string $id       - ID unico do accordion
 * @param array  $items    - Array de ['title', 'iconBg', 'content' (HTML), 'open' (bool)]
 * @param string $icon     - SVG opcional para icone esquerdo de cada item
 */

function renderAccordion($config) {
    $id = $config['id'] ?? 'accordion';
    $items = $config['items'] ?? [];

    $html = '';
    foreach ($items as $i => $item) {
        $title = $item['title'] ?? 'Item';
        $iconBg = $item['iconBg'] ?? 'var(--neon-cyan)';
        $content = $item['content'] ?? '';
        $isOpen = !empty($item['open']);
        $icon = $item['icon'] ?? '';

        $openClass = $isOpen ? ' open' : '';

        $iconHtml = '';
        if ($icon) {
            $iconHtml = '<div class="accordion-icon" style="background:' . $iconBg . '">' . $icon . '</div>';
        }

        $html .= '<div class="accordion-item' . $openClass . '">' .
            '<div class="accordion-header" onclick="this.parentElement.classList.toggle(\'open\')">' .
            '<div class="accordion-title">' . $iconHtml . $title . '</div>' .
            '<svg class="accordion-icon" style="background:var(--bg-surface)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>' .
            '</div>' .
            '<div class="accordion-body"><div class="accordion-content">' . $content . '</div></div>' .
            '</div>';
    }

    return $html;
}

// Exemplos de uso:

// echo renderAccordion([
//     'id' => 'faq',
//     'items' => [
//         [
//             'title' => 'Como funciona o sistema?',
//             'iconBg' => 'var(--neon-cyan)',
//             'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
//             'content' => 'O sistema permite gerenciar locacoes de equipamentos.',
//             'open' => true
//         ],
//         [
//             'title' => 'Qual o prazo de devolucao?',
//             'iconBg' => 'var(--neon-green)',
//             'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
//             'content' => 'O prazo e de 15 dias uteis a partir da retirada.'
//         ]
//     ]
// ]);

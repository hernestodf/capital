<?php
/**
 * Progress Bar Component
 *
 * @param string $label    - Texto esquerdo do header
 * @param string $value    - Texto direito do header
 * @param int    $percent  - Porcentagem (0-100)
 * @param string $variant  - red, cyan, green, yellow, purple, blue (default: cyan)
 * @param string $height   - Altura: default, thick
 */

function renderProgress($config) {
    $label = $config['label'] ?? '';
    $value = $config['value'] ?? '';
    $percent = $config['percent'] ?? 0;
    $variant = $config['variant'] ?? 'cyan';
    $height = $config['height'] ?? '';

    $trackClass = 'prog-track';
    if ($height === 'thick') {
        $trackClass .= ' thick';
    }

    $html = '<div class="prog-wrap">';
    if ($label || $value) {
        $html .= '<div class="prog-hdr"><span>' . $label . '</span><span style="color:var(--neon-' . $variant . ');font-weight:700">' . $value . '</span></div>';
    }
    $html .= '<div class="' . $trackClass . '"><div class="prog-bar ' . $variant . '" style="width:0" data-w="' . $percent . '%"></div></div>';
    $html .= '</div>';

    return $html;
}

/**
 * Grupo de Progress Bars
 */
function renderProgressGroup($items) {
    $html = '<div class="prog-group">';
    foreach ($items as $item) {
        $html .= renderProgress($item);
    }
    $html .= '</div>';
    return $html;
}

// Exemplos de uso:

// echo renderProgressGroup([
//     ['label' => '38.400 / 55.000', 'value' => '69%', 'percent' => 69, 'variant' => 'cyan'],
//     ['label' => '91 / 100', 'value' => '91%', 'percent' => 91, 'variant' => 'green'],
//     ['label' => '18 / 124', 'value' => '14%', 'percent' => 14, 'variant' => 'yellow'],
//     ['label' => '756GB / 1TB', 'value' => '75%', 'percent' => 75, 'variant' => 'purple', 'height' => 'thick'],
// ]);

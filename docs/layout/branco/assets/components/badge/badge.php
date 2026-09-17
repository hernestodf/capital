<?php
/**
 * Badge Component
 *
 * @param string $label   - Badge text
 * @param string $variant - Color variant: red, green, cyan, blue, purple, orange, yellow
 * @param string $size    - Size: sm, default (empty), lg
 * @param bool   $pulse   - Add pulsing dot indicator (default: false)
 */

function renderBadge($config) {
    $label   = $config['label'] ?? '';
    $variant = $config['variant'] ?? 'cyan';
    $size    = $config['size'] ?? '';
    $pulse   = $config['pulse'] ?? false;

    $classes = ['badge'];

    // Size
    if ($size) $classes[] = $size;

    // Variant
    $classes[] = $variant;

    $classStr = implode(' ', $classes);

    $inner = '';
    if ($pulse) {
        $inner .= '<span class="dot pulse"></span>';
    }
    $inner .= $label;

    return "<span class=\"{$classStr}\">{$inner}</span>";
}

// Exemplos de uso:

// Badge simples
// echo renderBadge([
//     'label'   => 'Info',
//     'variant' => 'cyan'
// ]);

// Badge com pulsação
// echo renderBadge([
//     'label'   => 'Ativo',
//     'variant' => 'red',
//     'pulse'   => true
// ]);

// Badge com pulsação verde
// echo renderBadge([
//     'label'   => 'Concluído',
//     'variant' => 'green',
//     'pulse'   => true
// ]);

// Badge pequeno (para tabelas)
// echo renderBadge([
//     'label'   => 'Pendente',
//     'variant' => 'orange',
//     'size'    => 'sm'
// ]);

// Badge grande (para dashboards)
// echo renderBadge([
//     'label'   => 'Processando',
//     'variant' => 'blue',
//     'size'    => 'lg'
// ]);

// Badge com pulsação em tabela
// echo renderBadge([
//     'label'   => 'Ativo',
//     'variant' => 'green',
//     'size'    => 'sm',
//     'pulse'   => true
// ]);

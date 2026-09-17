<?php
/**
 * Toggle Component
 */

function renderToggle($config) {
    $id = $config['id'] ?? 'tog-' . uniqid();
    $checked = $config['checked'] ?? false;
    $variant = $config['variant'] ?? '';
    $size = $config['size'] ?? 'default';

    $checkedAttr = $checked ? ' checked' : '';
    $variantClass = $variant ? ' ' . $variant : '';

    if ($size === 'small') {
        return '<label class="tog-s' . $variantClass . '">' .
            '<input type="checkbox"' . $checkedAttr . '/>' .
            '<div class="tog-track"></div>' .
            '<div class="tog-thumb"></div>' .
            '</label>';
    }

    return '<label class="tog' . $variantClass . '">' .
        '<input type="checkbox"' . $checkedAttr . '/>' .
        '<div class="tog-track"></div>' .
        '<div class="tog-thumb"></div>' .
        '</label>';
}

function renderToggleRow($config) {
    $label = $config['label'] ?? '';
    $toggle = renderToggle($config);

    return '<div class="lc-toggle-row">' .
        '<div>' . $label . '</div>' .
        $toggle .
        '</div>';
}

// Exemplos de uso:

// echo renderToggle(['checked' => true, 'variant' => 'cyan']);
// echo renderToggle(['checked' => false, 'variant' => 'red', 'size' => 'small']);
// echo renderToggleRow(['label' => 'Notificacoes por email', 'checked' => true]);

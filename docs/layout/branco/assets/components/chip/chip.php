<?php
/**
 * Chip & Tag Component
 */

/**
 * Chip Selecionavel
 */
function renderChip($config) {
    $label = $config['label'] ?? 'Chip';
    $variant = $config['variant'] ?? 'cyan';
    $selected = $config['selected'] ?? false;
    $onclick = $config['onclick'] ?? 'toggleChipSel(this)';

    $selectedClass = $selected ? ' selected' : '';

    return '<div class="chip ' . $variant . $selectedClass . '" onclick="' . $onclick . '">' . $label . '</div>';
}

/**
 * Chip Removivel
 */
function renderChipRemovable($config) {
    $label = $config['label'] ?? 'Chip';
    $onRemove = $config['onRemove'] ?? 'removeChip(this)';

    return '<div class="chip-removable">' . $label .
        '<button class="chip-rm-btn" onclick="' . $onRemove . '"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>' .
        '</div>';
}

/**
 * Tag / Etiqueta
 */
function renderTag($config) {
    $label = $config['label'] ?? 'TAG';
    $variant = $config['variant'] ?? 'cyan';

    return '<span class="tag ' . $variant . '">' . $label . '</span>';
}

/**
 * Grupo de Chips
 */
function renderChipGroup($chips) {
    $html = '<div class="chip-wrap">';
    foreach ($chips as $chip) {
        $html .= renderChip($chip);
    }
    $html .= '</div>';
    return $html;
}

// Exemplos de uso:

// echo renderChipGroup([
//     ['label' => 'Andaimes', 'variant' => 'cyan'],
//     ['label' => 'Betoneiras', 'variant' => 'green', 'selected' => true],
//     ['label' => 'Compressores', 'variant' => 'red'],
// ]);

// echo renderChipRemovable(['label' => 'Locacao Ativa']);
// echo renderTag(['label' => 'ATIVO', 'variant' => 'green']);

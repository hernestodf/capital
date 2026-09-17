<?php
/**
 * Rating Component
 *
 * @param string $id          - ID unico
 * @param int    $max         - Maximo de estrelas (default: 5)
 * @param int    $value       - Valor atual (default: 0)
 * @param string $size        - sm, default, lg (default: default)
 * @param bool   $interactive - Permitir click/hover (default: true)
 * @param bool   $showValue   - Mostrar valor numerico (default: false)
 */

function renderRating($config) {
    $id = $config['id'] ?? 'rating-' . uniqid();
    $max = $config['max'] ?? 5;
    $value = $config['value'] ?? 0;
    $size = $config['size'] ?? '';
    $interactive = $config['interactive'] ?? true;
    $showValue = $config['showValue'] ?? false;

    $sizeClass = $size ? ' rating-' . $size : '';

    $html = '<div class="rating-wrap">';
    $html .= '<div class="rating-group' . $sizeClass . '" data-rating="' . $value . '">';

    for ($i = 1; $i <= $max; $i++) {
        $activeClass = ($i <= $value) ? ' active' : '';
        if ($interactive) {
            $html .= '<div class="star' . $activeClass . '" onclick="setRating(this,' . $i . ')" onmouseover="hoverRating(this,' . $i . ')" onmouseout="leaveRating(this)"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div>';
        } else {
            $html .= '<div class="star' . $activeClass . '"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div>';
        }
    }

    $html .= '</div>';
    if ($showValue) {
        $html .= '<span class="rating-val">' . number_format($value, 1) . ' / ' . $max . '</span>';
    }
    $html .= '</div>';

    return $html;
}

// Exemplos de uso:

// echo renderRating(['value' => 3, 'max' => 5, 'size' => 'lg', 'showValue' => true]);
// echo renderRating(['value' => 4, 'max' => 5, 'interactive' => false]);
// echo renderRating(['value' => 0, 'size' => 'sm']);

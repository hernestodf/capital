<?php
/**
 * Spinner Component
 */

function renderSpinner($config) {
    $size = $config['size'] ?? 'md';
    $variant = $config['variant'] ?? 'cyan';

    return '<div class="spinner ' . $size . ' ' . $variant . '"></div>';
}

function renderSpinnerDots() {
    return '<div class="spinner-dots"><div class="spinner-dot"></div><div class="spinner-dot"></div><div class="spinner-dot"></div></div>';
}

function renderSpinnerRing() {
    return '<div class="spinner-ring"></div>';
}

function renderSpinnerBar() {
    return '<div class="spinner-bar" style="width:100%"><div class="spinner-bar-fill"></div></div>';
}

function renderSpinnerWithLabel($config) {
    $label = $config['label'] ?? 'Carregando...';
    $size = $config['size'] ?? 'md';
    $variant = $config['variant'] ?? 'cyan';

    return '<div class="spinner-wrap"><div class="spinner ' . $size . ' ' . $variant . '"></div><div class="spinner-label">' . $label . '</div></div>';
}

// Exemplos de uso:

// echo renderSpinner(['size' => 'lg', 'variant' => 'cyan']);
// echo renderSpinnerDots();
// echo renderSpinnerRing();
// echo renderSpinnerBar();
// echo renderSpinnerWithLabel(['label' => 'Carregando dados...', 'size' => 'md', 'variant' => 'cyan']);

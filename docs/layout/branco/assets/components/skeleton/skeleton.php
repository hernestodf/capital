<?php
/**
 * Skeleton Loader Component
 */

function renderSkeleton($config) {
    $width = $config['width'] ?? '100%';
    $height = $config['height'] ?? '14px';
    $circle = $config['circle'] ?? false;

    $style = 'width:' . $width . ';height:' . $height;
    $class = 'skel';
    if ($circle) {
        $class .= ' skel-circle';
    }

    return '<div class="' . $class . '" style="' . $style . '"></div>';
}

function renderSkeletonCard($config) {
    $lines = $config['lines'] ?? 3;
    $avatar = $config['avatar'] ?? true;

    $html = '<div class="skel-card"><div class="skel-row">';
    if ($avatar) {
        $html .= '<div class="skel skel-circle" style="width:48px;height:48px"></div>';
    }
    $html .= '<div style="flex:1">';
    $html .= '<div class="skel" style="height:14px;width:60%;margin-bottom:8px"></div>';
    $html .= '<div class="skel" style="height:12px;width:40%"></div>';
    $html .= '</div></div>';

    for ($i = 0; $i < $lines; $i++) {
        $w = ($i === $lines - 1) ? '70%' : '100%';
        $html .= '<div class="skel" style="height:12px;width:' . $w . ';margin-bottom:8px"></div>';
    }

    $html .= '</div>';
    return $html;
}

function renderSkeletonTable($rows = 4, $cols = 4) {
    $html = '<div class="skel-block">';
    for ($r = 0; $r < $rows; $r++) {
        $html .= '<div class="skel-row" style="gap:12px;margin-bottom:12px">';
        for ($c = 0; $c < $cols; $c++) {
            $w = ($c === 0) ? '25%' : (($c === $cols - 1) ? '15%' : '30%');
            $html .= '<div class="skel" style="height:12px;flex:1"></div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

// Exemplos de uso:

// echo renderSkeleton(['width' => '200px', 'height' => '14px']);
// echo renderSkeleton(['width' => '48px', 'height' => '48px', 'circle' => true]);
// echo renderSkeletonCard(['lines' => 3, 'avatar' => true]);
// echo renderSkeletonTable(4, 4);

<?php
/**
 * Avatar Component
 *
 * @param string $initials - 2 letras (e.g. 'MA')
 * @param string $variant  - cyan, green, red, purple, yellow, blue (default: cyan)
 * @param string $size     - sm, default, lg, xl
 * @param string $status   - online, busy, offline, none (default: none)
 */

function renderAvatar($config) {
    $initials = $config['initials'] ?? 'US';
    $variant = $config['variant'] ?? 'cyan';
    $size = $config['size'] ?? '';
    $status = $config['status'] ?? 'none';

    $classes = ['av', $variant];
    if ($size) $classes[] = 'av-' . $size;

    $avHtml = '<div class="' . implode(' ', $classes) . '">' . $initials . '</div>';

    if ($status === 'online') {
        return '<div class="av-online">' . $avHtml . '</div>';
    } elseif ($status === 'busy') {
        return '<div class="av-online av-busy">' . $avHtml . '</div>';
    } elseif ($status === 'offline') {
        return '<div class="av-online av-offline">' . $avHtml . '</div>';
    }

    return $avHtml;
}

/**
 * Avatar Group
 */
function renderAvatarGroup($config) {
    $avatars = $config['avatars'] ?? [];
    $overflow = $config['overflow'] ?? 0;

    $html = '<div class="avatar-group">';
    foreach ($avatars as $av) {
        $html .= renderAvatar($av);
    }
    if ($overflow > 0) {
        $html .= '<div class="av av-more">+' . $overflow . '</div>';
    }
    $html .= '</div>';

    return $html;
}

// Exemplos de uso:

// echo renderAvatar(['initials' => 'MA', 'variant' => 'cyan', 'size' => 'lg', 'status' => 'online']);
// echo renderAvatarGroup([
//     'avatars' => [
//         ['initials' => 'MA', 'variant' => 'cyan'],
//         ['initials' => 'JB', 'variant' => 'green'],
//         ['initials' => 'LC', 'variant' => 'red'],
//         ['initials' => 'RF', 'variant' => 'purple'],
//     ],
//     'overflow' => 8
// ]);

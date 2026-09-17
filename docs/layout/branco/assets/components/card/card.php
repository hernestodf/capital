<?php
/**
 * Card Component
 *
 * @param string $title   - Card title
 * @param string $tag     - Optional tag label
 * @param string $body    - HTML body content
 * @param string $footer  - Optional HTML footer
 * @param string $padding - Custom body padding (default: '')
 */

function renderCard($config) {
    $title   = $config['title'] ?? '';
    $tag     = $config['tag'] ?? '';
    $body    = $config['body'] ?? '';
    $footer  = $config['footer'] ?? '';
    $padding = $config['padding'] ?? '';

    $html = '<div class="card">';

    // Head
    if ($title || $tag) {
        $html .= '<div class="card-head">';
        if ($title) $html .= "<span class=\"card-title\">{$title}</span>";
        if ($tag)   $html .= "<span class=\"card-tag\">{$tag}</span>";
        $html .= '</div>';
    }

    // Body
    $bodyStyle = $padding ? " style=\"padding:{$padding}\"" : '';
    $html .= "<div class=\"card-body\"{$bodyStyle}>{$body}</div>";

    // Footer
    if ($footer) {
        $html .= "<div class=\"card-foot\">{$footer}</div>";
    }

    $html .= '</div>';

    return $html;
}

/**
 * Card Stat Component
 *
 * @param string $icon  - SVG string
 * @param string $value - Stat value (e.g. 'R$ 38,4k')
 * @param string $label - Stat label
 * @param string $color - Value color (default: 'var(--neon-cyan)')
 */

function renderCardStat($config) {
    $icon  = $config['icon'] ?? '';
    $value = $config['value'] ?? '';
    $label = $config['label'] ?? '';
    $color = $config['color'] ?? 'var(--neon-cyan)';

    $html = '<div class="card-stat" style="--stat-color:'.$color.'">';

    if ($icon) {
        $html .= "<div class=\"card-stat-icon\">{$icon}</div>";
    }

    $html .= "<div class=\"card-stat-val\" style=\"color:{$color}\">{$value}</div>";
    $html .= "<div class=\"card-stat-lbl\">{$label}</div>";

    $html .= '</div>';

    return $html;
}

/**
 * Custom Card Component
 *
 * @param string $title  - Card title
 * @param string $body   - HTML body content
 * @param string $footer - HTML footer content
 * @param bool   $glow   - Add card-glow effect (default: false)
 */

function renderCustomCard($config) {
    $title  = $config['title'] ?? '';
    $body   = $config['body'] ?? '';
    $footer = $config['footer'] ?? '';
    $glow   = $config['glow'] ?? false;

    $classes = ['custom-card'];
    if ($glow) $classes[] = 'card-glow';
    $classStr = implode(' ', $classes);

    $html = "<div class=\"{$classStr}\">";

    // Head
    if ($title) {
        $html .= '<div class="custom-card-head">';
        $html .= "<div class=\"custom-card-title\">{$title}</div>";
        $html .= '</div>';
    }

    // Body
    $html .= "<div class=\"custom-card-body\">{$body}</div>";

    // Foot
    if ($footer) {
        $html .= "<div class=\"custom-card-foot\">{$footer}</div>";
    }

    $html .= '</div>';

    return $html;
}

// Exemplos de uso:

// Card padrão
// echo renderCard([
//     'title' => 'Título do Card',
//     'tag'   => 'Tag',
//     'body'  => '<p>Conteúdo do card aqui.</p>'
// ]);

// Card com footer e padding customizado
// echo renderCard([
//     'title'   => 'Configurações',
//     'body'    => '<p>Conteúdo ajustado.</p>',
//     'footer'  => '<button class="btn btn-sm btn-cyan">Salvar</button>',
//     'padding' => '24px 32px'
// ]);

// Card de estatística
// echo renderCardStat([
//     'icon'  => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
//     'value' => 'R$ 38,4k',
//     'label' => 'Receita Mensal'
// ]);

// Card de estatística com cor customizada
// echo renderCardStat([
//     'icon'  => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
//     'value' => '47',
//     'label' => 'Locações Ativas',
//     'color' => 'var(--neon-green)'
// ]);

// Custom card
// echo renderCustomCard([
//     'title'  => 'Card Padrão',
//     'body'   => '<p>Conteúdo do card com informações relevantes.</p>',
//     'footer' => '<button class="btn btn-sm btn-cyan">Ação</button><button class="btn btn-sm btn-gray">Cancelar</button>'
// ]);

// Custom card com glow
// echo renderCustomCard([
//     'title'  => 'Card com Glow',
//     'body'   => '<p>Card destacado com efeito neon cyan.</p>',
//     'footer' => '<button class="btn btn-sm btn-cyan">Ver Detalhes</button>',
//     'glow'   => true
// ]);

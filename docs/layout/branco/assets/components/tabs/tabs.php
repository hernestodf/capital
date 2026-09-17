<?php
/**
 * Tabs Component
 *
 * 5 variants: Color, Underline, Pill, Segmented, Vertical
 *
 * @param string $id         - Unique ID prefix for tab group
 * @param array  $tabs       - Array of ['label', 'color' (optional), 'icon' (optional SVG), 'active' (bool), 'content']
 * @param int    $activeIndex - Default active tab index (0)
 * @param bool   $dark       - Dark variant (vertical only)
 */

/**
 * Horizontal Colored Tabs
 * Classes: htabs-color, ht-color, tab-pane
 */
function renderTabsColor($config) {
    $id = $config['id'] ?? 'tabs-color';
    $tabs = $config['tabs'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 0;

    $html = '<div class="htabs-color" data-tab-group="' . $id . '">';

    foreach ($tabs as $i => $tab) {
        $color = $tab['color'] ?? 'cyan';
        $active = ($i === $activeIndex) ? ' active' : '';
        $icon = $tab['icon'] ?? '';

        $html .= '<button type="button" class="ht-color' . $active . ' ' . $color . '"'
               . ' data-tab-group="' . $id . '"'
               . ' data-tab-index="' . $i . '"'
               . ' onclick="switchTab(\'' . $id . '\', ' . $i . ')">';

        if ($icon) {
            $html .= $icon;
        }

        $html .= htmlspecialchars($tab['label']);
        $html .= '</button>';
    }

    $html .= '</div>';

    // Tab panes
    $html .= '<div data-tab-group="' . $id . '">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $content = $tab['content'] ?? '';
        $html .= '<div class="tab-pane' . $active . '" data-tab-group="' . $id . '" data-tab-index="' . $i . '">';
        $html .= $content;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Horizontal Underline Tabs
 * Classes: htabs-underline, ht-ul, tab-pane
 */
function renderTabsUnderline($config) {
    $id = $config['id'] ?? 'tabs-underline';
    $tabs = $config['tabs'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 0;

    $html = '<div class="htabs-underline" data-tab-group="' . $id . '">';

    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $icon = $tab['icon'] ?? '';

        $html .= '<button type="button" class="ht-ul' . $active . '"'
               . ' data-tab-group="' . $id . '"'
               . ' data-tab-index="' . $i . '"'
               . ' onclick="switchTab(\'' . $id . '\', ' . $i . ')">';

        if ($icon) {
            $html .= $icon;
        }

        $html .= htmlspecialchars($tab['label']);
        $html .= '</button>';
    }

    $html .= '</div>';

    // Tab panes
    $html .= '<div data-tab-group="' . $id . '">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $content = $tab['content'] ?? '';
        $html .= '<div class="tab-pane' . $active . '" data-tab-group="' . $id . '" data-tab-index="' . $i . '">';
        $html .= $content;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Pill / Rounded Tabs
 * Classes: htabs-pill, ht-pill, tab-pane
 */
function renderTabsPill($config) {
    $id = $config['id'] ?? 'tabs-pill';
    $tabs = $config['tabs'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 0;

    $html = '<div class="htabs-pill" data-tab-group="' . $id . '">';

    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $icon = $tab['icon'] ?? '';

        $html .= '<button type="button" class="ht-pill' . $active . '"'
               . ' data-tab-group="' . $id . '"'
               . ' data-tab-index="' . $i . '"'
               . ' onclick="switchTab(\'' . $id . '\', ' . $i . ')">';

        if ($icon) {
            $html .= $icon;
        }

        $html .= htmlspecialchars($tab['label']);
        $html .= '</button>';
    }

    $html .= '</div>';

    // Tab panes
    $html .= '<div data-tab-group="' . $id . '">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $content = $tab['content'] ?? '';
        $html .= '<div class="tab-pane' . $active . '" data-tab-group="' . $id . '" data-tab-index="' . $i . '">';
        $html .= $content;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Segmented Button-Style Tabs
 * Classes: htabs-segmented, ht-seg, tab-pane
 */
function renderTabsSegmented($config) {
    $id = $config['id'] ?? 'tabs-segmented';
    $tabs = $config['tabs'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 0;

    $html = '<div class="htabs-segmented" data-tab-group="' . $id . '">';

    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $icon = $tab['icon'] ?? '';

        $html .= '<button type="button" class="ht-seg' . $active . '"'
               . ' data-tab-group="' . $id . '"'
               . ' data-tab-index="' . $i . '"'
               . ' onclick="switchTab(\'' . $id . '\', ' . $i . ')">';

        if ($icon) {
            $html .= $icon;
        }

        $html .= htmlspecialchars($tab['label']);
        $html .= '</button>';
    }

    $html .= '</div>';

    // Tab panes
    $html .= '<div data-tab-group="' . $id . '">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $content = $tab['content'] ?? '';
        $html .= '<div class="tab-pane' . $active . '" data-tab-group="' . $id . '" data-tab-index="' . $i . '">';
        $html .= $content;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Vertical Sidebar Tabs
 * Classes: vtabs, vtabs-nav, vt-item, vtabs-content, tab-pane
 * Dark variant: vtabs-dark
 */
function renderTabsVertical($config) {
    $id = $config['id'] ?? 'tabs-vertical';
    $tabs = $config['tabs'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 0;
    $dark = $config['dark'] ?? false;

    $darkClass = $dark ? ' vtabs-dark' : '';

    $html = '<div class="vtabs' . $darkClass . '" data-tab-group="' . $id . '">';

    // Navigation
    $html .= '<nav class="vtabs-nav">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $icon = $tab['icon'] ?? '';

        $html .= '<button type="button" class="vt-item' . $active . '"'
               . ' data-tab-group="' . $id . '"'
               . ' data-tab-index="' . $i . '"'
               . ' onclick="switchTab(\'' . $id . '\', ' . $i . ')">';

        if ($icon) {
            $html .= $icon;
        }

        $html .= htmlspecialchars($tab['label']);
        $html .= '</button>';
    }
    $html .= '</nav>';

    // Content area
    $html .= '<div class="vtabs-content">';
    foreach ($tabs as $i => $tab) {
        $active = ($i === $activeIndex) ? ' active' : '';
        $content = $tab['content'] ?? '';
        $html .= '<div class="tab-pane' . $active . '" data-tab-group="' . $id . '" data-tab-index="' . $i . '">';
        $html .= $content;
        $html .= '</div>';
    }
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

// =============================================
// EXEMPLOS DE USO
// =============================================

// Tabs Color
// echo renderTabsColor([
//     'id' => 'my-color-tabs',
//     'activeIndex' => 0,
//     'tabs' => [
//         ['label' => 'Geral', 'color' => 'cyan', 'active' => true, 'content' => '<p>Conteúdo geral aqui.</p>'],
//         ['label' => 'Alertas', 'color' => 'red', 'content' => '<p>Conteúdo de alertas.</p>'],
//         ['label' => 'Sucesso', 'color' => 'green', 'content' => '<p>Conteúdo de sucesso.</p>'],
//         ['label' => 'Avisos', 'color' => 'yellow', 'content' => '<p>Conteúdo de avisos.</p>'],
//         ['label' => 'Especial', 'color' => 'purple', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>', 'content' => '<p>Conteúdo especial.</p>'],
//     ]
// ]);

// Tabs Underline
// echo renderTabsUnderline([
//     'id' => 'my-underline-tabs',
//     'activeIndex' => 1,
//     'tabs' => [
//         ['label' => 'Perfil', 'content' => '<p>Informações do perfil.</p>'],
//         ['label' => 'Configurações', 'active' => true, 'content' => '<p>Ajustes do sistema.</p>'],
//         ['label' => 'Notificações', 'content' => '<p>Preferências de notificação.</p>'],
//     ]
// ]);

// Tabs Pill
// echo renderTabsPill([
//     'id' => 'my-pill-tabs',
//     'activeIndex' => 0,
//     'tabs' => [
//         ['label' => 'Dia', 'active' => true, 'content' => '<p>Visualização diária.</p>'],
//         ['label' => 'Semana', 'content' => '<p>Visualização semanal.</p>'],
//         ['label' => 'Mês', 'content' => '<p>Visualização mensal.</p>'],
//         ['label' => 'Ano', 'content' => '<p>Visualização anual.</p>'],
//     ]
// ]);

// Tabs Segmented
// echo renderTabsSegmented([
//     'id' => 'my-segmented-tabs',
//     'activeIndex' => 0,
//     'tabs' => [
//         ['label' => 'Lista', 'active' => true, 'content' => '<p>Modo lista.</p>'],
//         ['label' => 'Grid', 'content' => '<p>Modo grid.</p>'],
//         ['label' => 'Tabela', 'content' => '<p>Modo tabela.</p>'],
//     ]
// ]);

// Tabs Vertical (padrão)
// echo renderTabsVertical([
//     'id' => 'my-vertical-tabs',
//     'activeIndex' => 0,
//     'tabs' => [
//         ['label' => 'Dashboard', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>', 'active' => true, 'content' => '<p>Painel principal.</p>'],
//         ['label' => 'Usuários', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'content' => '<p>Gerenciamento de usuários.</p>'],
//         ['label' => 'Relatórios', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'content' => '<p>Relatórios e análises.</p>'],
//     ]
// ]);

// Tabs Vertical (dark)
// echo renderTabsVertical([
//     'id' => 'my-dark-tabs',
//     'activeIndex' => 0,
//     'dark' => true,
//     'tabs' => [
//         ['label' => 'Servidores', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>', 'active' => true, 'content' => '<p>Status dos servidores.</p>'],
//         ['label' => 'Logs', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'content' => '<p>Logs do sistema.</p>'],
//     ]
// ]);

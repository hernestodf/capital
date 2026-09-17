<?php
/**
 * Popover Component
 *
 * @param string $id       - ID unico
 * @param string $trigger  - HTML do elemento trigger (geralmente botao)
 * @param string $title    - Titulo do popover
 * @param string $content  - HTML do conteudo
 * @param string $position - bottom, top (default: bottom)
 * @param string $minWidth - Largura minima (default: 240px)
 */

function renderPopover($config) {
    $id = $config['id'] ?? 'pop-' . uniqid();
    $trigger = $config['trigger'] ?? '<button class="btn btn-cyan">Abrir</button>';
    $title = $config['title'] ?? '';
    $content = $config['content'] ?? '';
    $position = $config['position'] ?? 'bottom';
    $minWidth = $config['minWidth'] ?? '240px';
    $extraClass = $config['extraClass'] ?? '';

    $posClass = 'pop-' . $position;
    $classAttr = trim('popover ' . $posClass . ' ' . $extraClass);

    $innerHtml = '';
    if ($title) {
        $innerHtml .= '<div class="popover-title">' . $title . '</div>';
    }
    $innerHtml .= $content;

    // Inject togglePop into trigger's onclick
    $triggerWithPop = preg_replace(
        '/onclick="/',
        'onclick="togglePop(\'' . $id . '\');',
        $trigger,
        1,
        $count
    );
    if ($count === 0) {
        // No onclick attribute exists — add one
        $triggerWithPop = preg_replace(
            '/(<button\b)/',
            '$1 onclick="togglePop(\'' . $id . '\')"',
            $trigger,
            1
        );
    }

    return '<div class="popover-wrap">' .
        $triggerWithPop .
        '<div class="' . $classAttr . '" id="' . $id . '" style="min-width:' . $minWidth . '">' . $innerHtml . '</div>' .
        '</div>';
}

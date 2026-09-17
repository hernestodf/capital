<?php
/**
 * Button Component
 *
 * @param string $label       - Texto do botão
 * @param string $variant     - Variante de cor: cyan, green, red, blue, purple, orange, yellow, ghost, ghost-cyan, ghost-red, outline-cyan
 * @param string $size        - Tamanho: xs, sm, md (32x32), lg, xl, icon, icon-sm, icon-md (32x32), icon-xs (16x16), icon-lg (default: padrão)
 * @param string $icon        - SVG do ícone (opcional)
 * @param string $iconPosition - Posição do ícone: left, right (default: left)
 * @param bool   $loading     - Estado de loading (default: false)
 * @param bool   $disabled    - Estado disabled (default: false)
 * @param string $onclick     - Handler onclick (opcional)
 * @param string $type        - Tipo do botão: button, submit (default: button)
 * @param string $extra       - Classes extras e/ou atributos HTML (ex: 'btn-block data-action="foo" data-id="1"')
 */

function renderButton($config) {
    $label = $config['label'] ?? 'Botão';
    $variant = $config['variant'] ?? 'cyan';
    $size = $config['size'] ?? '';
    $icon = $config['icon'] ?? '';
    $iconPosition = $config['iconPosition'] ?? 'left';
    $loading = $config['loading'] ?? false;
    $disabled = $config['disabled'] ?? false;
    $onclick = $config['onclick'] ?? '';
    $type = $config['type'] ?? 'button';
    $extra = $config['extra'] ?? '';

    $classes = ['btn'];

    // Size
    if ($size) $classes[] = "btn-{$size}";

    // Variant
    $classes[] = "btn-{$variant}";

    // States
    if ($loading) $classes[] = 'loading';
    if ($disabled) $classes[] = 'disabled';

    // 'extra' e usado tanto pra classes CSS soltas ('btn-block') quanto pra
    // atributos HTML embutidos ('data-action="foo" data-id="1"'). Extrai os
    // pares chave="valor"/chave='valor' como atributos reais primeiro — do
    // contrario eles acabam concatenados dentro do valor de class="...",
    // quebrando o HTML e fazendo o atributo (ex: data-action) nunca existir
    // de fato no DOM (confirmado: getAttribute retornava null).
    $extraAttrs = '';
    if ($extra) {
        if (preg_match_all('/([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*("[^"]*"|\'[^\']*\')/', $extra, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $extraAttrs .= " {$m[1]}={$m[2]}";
            }
            $extra = trim(preg_replace('/([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*("[^"]*"|\'[^\']*\')/', '', $extra));
        }
        if ($extra !== '') $classes[] = $extra;
    }

    $classStr = implode(' ', $classes);

    $attrs = "type=\"{$type}\" class=\"{$classStr}\"{$extraAttrs}";
    if ($onclick) $attrs .= " onclick=\"{$onclick}\"";
    if ($disabled) $attrs .= " disabled";

    $innerContent = '';
    if ($loading) {
        $innerContent = '<div class="btn-spin"></div>';
    } else {
        if ($icon && $iconPosition === 'left') {
            $innerContent .= $icon;
        }
        $innerContent .= $label;
        if ($icon && $iconPosition === 'right') {
            $innerContent .= $icon;
        }
    }

    return "<button {$attrs}>{$innerContent}</button>";
}

// Exemplos de uso:

// Botão simples
// echo renderButton([
//     'label' => 'Salvar',
//     'variant' => 'cyan'
// ]);

// Botão com ícone
// echo renderButton([
//     'label' => 'Novo Cliente',
//     'variant' => 'green',
//     'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>'
// ]);

// Botão loading
// echo renderButton([
//     'label' => 'Salvando...',
//     'variant' => 'cyan',
//     'loading' => true
// ]);

// Botão cancelar (ghost)
// echo renderButton([
//     'label' => 'Cancelar',
//     'variant' => 'ghost',
//     'size' => 'sm'
// ]);

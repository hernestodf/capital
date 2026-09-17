<?php
/**
 * Modal Component
 *
 * @param string $id           - ID unico do modal (obrigatorio)
 * @param string $size         - Tamanho: sm, md, lg (default: md)
 * @param string $variant      - Variante: info, danger, success, form (default: info)
 * @param string $title        - Titulo do modal (obrigatorio)
 * @param string $subtitle     - Subtitulo (opcional)
 * @param string $icon         - SVG do icone (opcional, auto-gerado por variant)
 * @param string $body         - HTML do conteudo do body (obrigatorio)
 * @param string $footer       - HTML do footer (opcional, gera botoes padrao)
 * @param string $headerColor  - Override da cor do header (e.g. 'var(--neon-red)')
 * @param bool   $closeOnOutside - Fechar ao clicar fora (default: true)
 */

function renderModal($config) {
    $id = $config['id'] ?? 'modal';
    $size = $config['size'] ?? 'md';
    $variant = $config['variant'] ?? 'info';
    $title = $config['title'] ?? 'Modal';
    $subtitle = $config['subtitle'] ?? '';
    $icon = $config['icon'] ?? '';
    $body = $config['body'] ?? '';
    $footer = $config['footer'] ?? '';
    $headerColor = $config['headerColor'] ?? '';
    $closeOnOutside = $config['closeOnOutside'] ?? true;

    // Auto icon by variant
    if (!$icon) {
        $icons = [
            'info' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'danger' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'success' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'form' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
        ];
        $icon = $icons[$variant] ?? $icons['info'];
    }

    // Auto header color by variant
    if (!$headerColor) {
        $colors = [
            'info' => '',
            'danger' => 'var(--neon-red)',
            'success' => 'var(--neon-green)',
            'form' => 'var(--neon-purple)',
        ];
        $headerColor = $colors[$variant] ?? '';
    }

    // Default footer
    if (!$footer) {
        $footerBtns = [
            'info' => '<button class="btn btn-gray" onclick="closeModal(\'' . $id . '\')">Fechar</button><button class="btn btn-cyan" onclick="closeModal(\'' . $id . '\')">OK</button>',
            'danger' => '<button class="btn btn-gray" onclick="closeModal(\'' . $id . '\')">Cancelar</button><button class="btn btn-red" onclick="closeModal(\'' . $id . '\')">Confirmar</button>',
            'success' => '<button class="btn btn-green" onclick="closeModal(\'' . $id . '\')">OK</button>',
            'form' => '<button class="btn btn-gray" onclick="closeModal(\'' . $id . '\')">Cancelar</button><button class="btn btn-cyan">Salvar</button>',
        ];
        $footer = $footerBtns[$variant] ?? $footerBtns['info'];
    }

    $overlayAttrs = 'class="modal-overlay" id="' . $id . '"';
    if ($closeOnOutside) {
        $overlayAttrs .= ' onclick="closeModalOutside(event,\'' . $id . '\')"';
    }

    $headerStyle = '';
    if ($headerColor) {
        $headerStyle = ' style="background:' . $headerColor . '"';
    }

    $headerContent = '';
    if ($variant === 'danger' || $variant === 'success') {
        // Centered icon style
        $bigIconColor = $headerColor ?: 'var(--neon-cyan)';
        $headerContent = '<div class="modal-header"' . $headerStyle . ' style="justify-content:flex-end;border-bottom:none;padding-bottom:0;background:' . $bigIconColor . '"><button class="modal-close" onclick="closeModal(\'' . $id . '\')"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>';
        $body = '<div style="width:64px;height:64px;border-radius:16px;background:' . $bigIconColor . ';display:grid;place-items:center;margin:0 auto 20px">' . $icon . '</div>' .
                '<div style="font-size:20px;font-weight:800;margin-bottom:10px;text-align:center">' . $title . '</div>' .
                '<div style="font-size:14px;color:var(--text-2);line-height:1.7;text-align:center">' . $body . '</div>';
        $footerAlign = ' style="justify-content:center"';
    } else {
        $headerContent = '<div class="modal-header"' . $headerStyle . '>' .
            '<div class="modal-icon">' . $icon . '</div>' .
            '<div style="flex:1"><div class="modal-title">' . $title . '</div>' .
            ($subtitle ? '<div class="modal-sub">' . $subtitle . '</div>' : '') .
            '</div>' .
            '<button class="modal-close" onclick="closeModal(\'' . $id . '\')"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>' .
            '</div>';
        $footerAlign = '';
    }

    return '<div ' . $overlayAttrs . '>' .
        '<div class="modal modal-' . $size . '">' .
        $headerContent .
        '<div class="modal-body"' . (($variant === 'danger' || $variant === 'success') ? ' style="text-align:center;padding-top:8px"' : '') . '>' . $body . '</div>' .
        '<div class="modal-footer"' . $footerAlign . '>' . $footer . '</div>' .
        '</div></div>';
}

/**
 * Modal de Confirmacao Simplificado
 */
function renderModalConfirm($config) {
    $config['variant'] = 'danger';
    $config['size'] = $config['size'] ?? 'sm';
    $config['title'] = $config['title'] ?? 'Confirmar exclusao?';
    $config['body'] = $config['body'] ?? 'Esta acao nao pode ser desfeita.';
    return renderModal($config);
}

/**
 * Modal de Sucesso Simplificado
 */
function renderModalSuccess($config) {
    $config['variant'] = 'success';
    $config['size'] = $config['size'] ?? 'sm';
    $config['title'] = $config['title'] ?? 'Operacao Concluida!';
    return renderModal($config);
}

/**
 * Modal com Formulario
 */
function renderModalForm($config) {
    $config['variant'] = 'form';
    $config['size'] = $config['size'] ?? 'md';
    $fields = $config['fields'] ?? [];
    $col2 = $config['col2'] ?? false;

    $bodyHtml = '';
    if ($col2) {
        $bodyHtml .= '<div class="col2">';
    }
    foreach ($fields as $field) {
        $label = $field['label'] ?? '';
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $value = $field['value'] ?? '';
        $style = isset($field['gridColumn']) ? ' style="grid-column:' . $field['gridColumn'] . '"' : '';
        $bodyHtml .= '<div class="fg"' . $style . '><div class="fl">' . $label . '</div>';
        if ($type === 'textarea') {
            $bodyHtml .= '<textarea class="fi" name="' . $name . '" placeholder="' . $placeholder . '">' . $value . '</textarea>';
        } elseif ($type === 'select') {
            $bodyHtml .= '<select class="fi" name="' . $name . '">';
            foreach ($field['options'] ?? [] as $opt) {
                $bodyHtml .= '<option>' . $opt . '</option>';
            }
            $bodyHtml .= '</select>';
        } else {
            $bodyHtml .= '<input class="fi" type="' . $type . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"/>';
        }
        $bodyHtml .= '</div>';
    }
    if ($col2) {
        $bodyHtml .= '</div>';
    }

    $config['body'] = $bodyHtml;
    return renderModal($config);
}

// Exemplos de uso:

// Modal de Informacao
// echo renderModal([
//     'id' => 'modal-info',
//     'title' => 'Informacoes',
//     'subtitle' => 'Design System 3.0',
//     'body' => '<p>Conteudo informativo aqui.</p>'
// ]);

// Modal de Confirmacao
// echo renderModalConfirm([
//     'id' => 'modal-delete',
//     'title' => 'Excluir item?',
//     'body' => 'Esta acao nao pode ser desfeita.'
// ]);

// Modal de Sucesso
// echo renderModalSuccess([
//     'id' => 'modal-ok',
//     'title' => 'Salvo com sucesso!',
//     'body' => 'Os dados foram atualizados.'
// ]);

// Modal com Formulario
// echo renderModalForm([
//     'id' => 'modal-form',
//     'title' => 'Novo Cliente',
//     'subtitle' => 'Preencha os dados abaixo',
//     'fields' => [
//         ['label' => 'Nome', 'type' => 'text', 'name' => 'nome', 'placeholder' => 'Nome completo'],
//         ['label' => 'Email', 'type' => 'email', 'name' => 'email', 'placeholder' => 'email@exemplo.com'],
//         ['label' => 'CPF', 'type' => 'text', 'name' => 'cpf', 'placeholder' => '000.000.000-00', 'gridColumn' => '1/-1'],
//     ],
//     'col2' => true
// ]);

<?php
/**
 * Input / Form Component
 *
 * @param string $type        - text, email, password, number, tel, url, search, date, time, datetime-local, select, textarea
 * @param string $label       - Label do campo
 * @param string $name        - Name do input
 * @param string $placeholder - Placeholder
 * @param string $value       - Valor inicial
 * @param bool   $disabled    - Campo desabilitado
 * @param string $state       - default, focus, success, error
 * @param string $hint        - Texto de ajuda/validacao abaixo do campo
 * @param string $icon        - SVG para icone esquerdo
 * @param array  $options     - Opcoes para select
 * @param int    $rows        - Linhas do textarea (default: 4)
 * @param string $extra       - Classes/atributos extras
 */

function renderInput($config) {
    $type = $config['type'] ?? 'text';
    $label = $config['label'] ?? '';
    $name = $config['name'] ?? '';
    $id = $config['id'] ?? $name;
    $placeholder = $config['placeholder'] ?? '';
    $value = $config['value'] ?? '';
    $disabled = $config['disabled'] ?? false;
    $state = $config['state'] ?? 'default';
    $hint = $config['hint'] ?? '';
    $icon = $config['icon'] ?? '';
    $options = $config['options'] ?? [];
    $rows = $config['rows'] ?? 4;
    $extra = $config['extra'] ?? '';
    $required = $config['required'] ?? false;
    $autofocus = $config['autofocus'] ?? false;

    $html = '<div class="fg">';

    if ($label) {
        $html .= '<div class="fl">' . $label . '</div>';
    }

    $inputStyle = '';
    $inputClass = 'fi';

    if ($state === 'success') {
        $inputStyle = ' style="border-color:var(--neon-green);box-shadow:0 0 0 4px var(--neon-green-glow)"';
    } elseif ($state === 'error') {
        $inputStyle = ' style="border-color:var(--neon-red);box-shadow:0 0 0 4px var(--neon-red-glow)"';
    } elseif ($state === 'focus') {
        $inputStyle = ' style="border-color:var(--neon-cyan);box-shadow:0 0 0 3px var(--neon-cyan-glow)"';
    }

    $disabledAttr = $disabled ? ' disabled' : '';
    $requiredAttr = $required ? ' required' : '';
    $autofocusAttr = $autofocus ? ' autofocus' : '';
    $attrs = $disabledAttr . $requiredAttr . $autofocusAttr;

    if ($type === 'select') {
        $html .= '<select class="' . $inputClass . '" id="' . $id . '" name="' . $name . '"' . $attrs . $inputStyle . '>';
        foreach ($options as $opt) {
            $html .= '<option>' . $opt . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'textarea') {
        $html .= '<textarea class="' . $inputClass . '" id="' . $id . '" name="' . $name . '" rows="' . $rows . '" placeholder="' . $placeholder . '"' . $attrs . $inputStyle . '>' . $value . '</textarea>';
    } else {
        if ($icon) {
            $html .= '<div style="position:relative">';
            $html .= '<div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-3)">' . $icon . '</div>';
            $html .= '<input class="' . $inputClass . '" id="' . $id . '" type="' . $type . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"' . $attrs . ' style="padding-left:44px"' . ($inputStyle ? str_replace(' style="', ' style="padding-left:44px;', $inputStyle) : '') . '/>';
            $html .= '</div>';
        } else {
            $html .= '<input class="' . $inputClass . '" id="' . $id . '" type="' . $type . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"' . $attrs . $inputStyle . '/>';
        }
    }

    if ($hint) {
        $hintColor = 'var(--text-4)';
        $hintIcon = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        if ($state === 'success') {
            $hintColor = 'var(--neon-green)';
            $hintIcon = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($state === 'error') {
            $hintColor = 'var(--neon-red)';
            $hintIcon = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        }
        $html .= '<div style="font-size:12.5px;margin-top:6px;display:flex;align-items:center;gap:5px;color:' . $hintColor . '">' . $hintIcon . $hint . '</div>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Form Group com 2 colunas
 */
function renderFormRow($fields) {
    $html = '<div class="col2">';
    foreach ($fields as $field) {
        $html .= renderInput($field);
    }
    $html .= '</div>';
    return $html;
}

// Exemplos de uso:

/**
 * Input com Prefixo (ex: R$, kg, m, etc)
 *
 * @param string $prefix      - Texto prefixo (ex: 'R$', 'kg', 'm')
 * @param string $label       - Label do campo
 * @param string $name        - Name do input
 * @param string $placeholder - Placeholder
 * @param string $value       - Valor inicial
 * @param string $id          - ID do input
 * @param string $state       - default, focus, success, error
 * @param string $hint        - Texto de ajuda
 * @param string $extra       - Atributos extras (inputmode, pattern, etc)
 * @param bool   $required    - Campo obrigatorio
 */
function renderInputPrefix($config) {
    $prefix = $config['prefix'] ?? '';
    $label = $config['label'] ?? '';
    $name = $config['name'] ?? '';
    $id = $config['id'] ?? $name;
    $placeholder = $config['placeholder'] ?? '';
    $value = $config['value'] ?? '';
    $state = $config['state'] ?? 'default';
    $hint = $config['hint'] ?? '';
    $extra = $config['extra'] ?? '';
    $required = $config['required'] ?? false;

    $html = '<div class="fg">';

    if ($label) {
        $html .= '<div class="fl">' . $label . ($required ? ' <span class="req">*</span>' : '') . '</div>';
    }

    $inputClass = 'fi';
    $inputStyle = '';

    if ($state === 'success') {
        $inputStyle = 'border-color:var(--neon-green);box-shadow:0 0 0 4px var(--neon-green-glow)';
    } elseif ($state === 'error') {
        $inputStyle = 'border-color:var(--neon-red);box-shadow:0 0 0 4px var(--neon-red-glow)';
    } elseif ($state === 'focus') {
        $inputStyle = 'border-color:var(--neon-cyan);box-shadow:0 0 0 3px var(--neon-cyan-glow)';
    }

    $requiredAttr = $required ? ' required' : '';

    $html .= '<div class="fi-wrap" data-prefix>';
    $html .= '<span class="pfx-txt">' . htmlspecialchars($prefix) . '</span>';
    $html .= '<input type="text" id="' . $id . '" class="fi has-pfx" name="' . $name . '" placeholder="' . $placeholder . '" value="' . htmlspecialchars($value) . '"' . $requiredAttr . ($extra ? ' ' . $extra : '') . ($inputStyle ? ' style="' . $inputStyle . '"' : '') . '/>';
    $html .= '</div>';

    if ($hint) {
        $hintColor = 'var(--text-4)';
        $html .= '<div style="font-size:12.5px;margin-top:6px;display:flex;align-items:center;gap:5px;color:' . $hintColor . '">' . $hint . '</div>';
    }

    $html .= '</div>';
    return $html;
}

// Exemplos de uso:

// echo renderInput([
//     'label' => 'Nome Completo',
//     'type' => 'text',
//     'name' => 'nome',
//     'placeholder' => 'Digite seu nome...'
// ]);

// echo renderInput([
//     'label' => 'Email',
//     'type' => 'email',
//     'name' => 'email',
//     'placeholder' => 'email@exemplo.com',
//     'state' => 'success',
//     'hint' => 'Email validado com sucesso!'
// ]);

// echo renderInput([
//     'label' => 'CPF',
//     'type' => 'text',
//     'name' => 'cpf',
//     'placeholder' => '000.000.000-00',
//     'state' => 'error',
//     'hint' => 'CPF invalido.'
// ]);

// echo renderInput([
//     'label' => 'Tipo',
//     'type' => 'select',
//     'name' => 'tipo',
//     'options' => ['Opcao 1', 'Opcao 2', 'Opcao 3']
// ]);

// echo renderInput([
//     'label' => 'Buscar',
//     'type' => 'search',
//     'name' => 'busca',
//     'placeholder' => 'Buscar...',
//     'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>'
// ]);

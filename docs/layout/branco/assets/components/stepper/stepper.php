<?php
/**
 * Stepper Component
 *
 * @param string $id          - ID unico
 * @param array  $steps       - Array de ['label' (pode usar <br/>)]
 * @param int    $activeIndex - Step ativo atual (1-based)
 * @param array  $panes       - Array de HTML para cada step pane
 * @param string $prevLabel   - Label do botao anterior (default: '← Anterior')
 * @param string $nextLabel   - Label do botao proximo (default: 'Próximo →')
 * @param string $finishLabel - Label do botao final (default: 'Concluir ✓')
 * @param bool   $showCounter - Mostrar contador (default: true)
 */

function renderStepper($config) {
    $id = $config['id'] ?? 'stepper';
    $steps = $config['steps'] ?? [];
    $activeIndex = $config['activeIndex'] ?? 1;
    $panes = $config['panes'] ?? [];
    $prevLabel = $config['prevLabel'] ?? '&larr; Anterior';
    $nextLabel = $config['nextLabel'] ?? 'Pr&oacute;ximo &rarr;';
    $finishLabel = $config['finishLabel'] ?? 'Concluir &#10003;';
    $showCounter = $config['showCounter'] ?? true;

    $total = count($steps);

    // Stepper circles
    $stepsHtml = '<div class="stepper">';
    foreach ($steps as $i => $step) {
        $n = $i + 1;
        $class = '';
        if ($n < $activeIndex) $class = 'done';
        elseif ($n === $activeIndex) $class = 'active';
        $stepsHtml .= '<div class="step-item ' . $class . '"><div class="step-circle">' . $n . '</div><div class="step-label">' . $step['label'] . '</div></div>';
    }
    $stepsHtml .= '</div>';

    // Panes
    $panesHtml = '<div class="step-panes">';
    foreach ($panes as $i => $pane) {
        $n = $i + 1;
        $class = ($n === $activeIndex) ? ' active' : '';
        $panesHtml .= '<div class="step-pane' . $class . '">' . $pane . '</div>';
    }
    $panesHtml .= '</div>';

    // Controls
    $prevDisabled = ($activeIndex === 1) ? ' disabled' : '';
    $nextBtnText = ($activeIndex === $total) ? $finishLabel : $nextLabel;

    $controlsHtml = '<div class="step-controls">' .
        '<button class="btn btn-gray" id="' . $id . '-prev-btn" onclick="stepPrev()" ' . $prevDisabled . '>' . $prevLabel . '</button>';
    if ($showCounter) {
        $controlsHtml .= '<span class="step-counter">Passo <span id="' . $id . '-cur">' . $activeIndex . '</span> de ' . $total . '</span>';
    }
    $controlsHtml .= '<button class="btn btn-cyan" id="' . $id . '-next-btn" onclick="stepNext()">' . $nextBtnText . '</button>' .
        '</div>';

    return $stepsHtml . $panesHtml . $controlsHtml;
}

// Exemplos de uso:

// echo renderStepper([
//     'id' => 'wizard',
//     'steps' => [
//         ['label' => 'Dados<br/>Pessoais'],
//         ['label' => 'Endereco'],
//         ['label' => 'Documentos'],
//         ['label' => 'Confirmacao']
//     ],
//     'activeIndex' => 1,
//     'panes' => [
//         '<div class="col2"><div class="fg"><div class="fl">Nome</div><input class="fi" type="text" placeholder="Nome"/></div><div class="fg"><div class="fl">CPF</div><input class="fi" type="text" placeholder="000.000.000-00"/></div></div>',
//         '<div class="fg"><div class="fl">Endereco</div><input class="fi" type="text" placeholder="Rua, Numero"/></div>',
//         '<div class="fg"><div class="fl">RG</div><input class="fi" type="text" placeholder="00.000.000-0"/></div>',
//         '<div style="text-align:center;padding:20px"><div style="font-size:48px;margin-bottom:12px">&#10004;</div><div style="font-size:18px;font-weight:700">Tudo pronto!</div></div>'
//     ]
// ]);

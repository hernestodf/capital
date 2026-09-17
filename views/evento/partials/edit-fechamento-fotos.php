<?php
// Partial: Fechamento - Tab Fotos & PDF
// Variaveis: $eventoId, $salas (opcional)
?>

<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/spinner/spinner.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/button/button.php';

$salasLoadingHtml = '<div style="text-align:center;padding:24px;color:var(--text-3)">'
    . renderSpinner(['variant' => 'cyan', 'size' => 'sm'])
    . '<div style="margin-top:8px">Carregando salas...</div></div>';
?>

<!-- Fotos por Sala -->
<?php
$uploadAreaHtml = '<div class="fechamento-upload-area">'
    . '<svg fill="none" viewBox="0 0 24 24" stroke="var(--neon-cyan)" stroke-width="2" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0-3v9m2-2a2 2 0 100-4 2 2 0 000 4z"/></svg>'
    . '<div style="flex:1">'
    . '<label class="btn btn-cyan btn-sm" style="cursor:pointer">Upload Foto Geral'
    . '<input type="file" id="input-foto-geral" accept="image/jpeg,image/png,image/webp" multiple style="display:none">'
    . '</label>'
    . '<div style="font-size:11px;color:var(--text-3);margin-top:4px">JPEG, PNG ou WebP (max 10MB)</div>'
    . '</div></div>'
    . '<div id="fechamento-fotos-gerais-content" style="margin-top:12px"></div>';

echo renderCard([
    'title' => 'Fotos por Sala',
    'body' => '<div id="fechamento-fotos-salas-loading">' . $salasLoadingHtml . '</div>'
           . '<div id="fechamento-fotos-salas-content" style="display:none"></div>',
]);

echo renderCard([
    'title' => 'Fotos Gerais do Evento',
    'body' => $uploadAreaHtml,
]);
?>

<!-- Relatorios PDF -->
<?php
$pdfButtons = '<div style="display:flex;gap:12px;flex-wrap:wrap">'
    . renderButton(['label' => 'Relatorio para Cliente (sem valores)', 'variant' => 'purple', 'extra' => 'data-action="gerar-pdf-cliente"'])
    . renderButton(['label' => 'Relatorio Completo (com custos e lucro)', 'variant' => 'cyan', 'extra' => 'data-action="gerar-pdf-interno"'])
    . '</div>';

echo renderCard([
    'title' => 'Relatorios de Fechamento',
    'body' => $pdfButtons,
]);
?>

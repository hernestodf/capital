<?php
// Partial: Fechamento - Tab Locação
// Variaveis: $eventoId (disponivel no escopo do edit-fechamento.php)
?>
<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/spinner/spinner.php';

$loadingHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . renderSpinner(['variant' => 'dots', 'size' => 'md'])
    . '<div style="margin-top:8px">Carregando itens de locação...</div></div>';

$emptyHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:36px;height:36px;margin:0 auto 10px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
    . '<div style="font-weight:600;margin-bottom:4px">Nenhum item de locação encontrado</div>'
    . '<small style="color:var(--text-3)">Adicione produtos na aba "Locações".</small>'
    . '</div>';

echo renderCard([
    'title' => 'Itens de Locação',
    'body'  => '<div id="fechamento-locacao-loading">' . $loadingHtml . '</div>'
             . '<div id="fechamento-locacao-content" style="display:none"></div>'
             . '<div id="fechamento-locacao-empty" style="display:none">' . $emptyHtml . '</div>',
]);
?>

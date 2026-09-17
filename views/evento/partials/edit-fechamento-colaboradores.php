<?php
// Partial: Fechamento - Tab Colaboradores
// Variaveis: $eventoId
?>
<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/spinner/spinner.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/modal/modal.php';

$loadingHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . renderSpinner(['variant' => 'dots', 'size' => 'md'])
    . '<div style="margin-top:8px">Carregando colaboradores...</div></div>';

$emptyHtml = '<div style="text-align:center;padding:24px;color:var(--text-3)">Nenhum colaborador encontrado para este evento.</div>';

echo renderCard([
    'body' => '<div id="fechamento-colaboradores-loading">' . $loadingHtml . '</div>'
           . '<div id="fechamento-colaboradores-content" style="display:none"></div>'
           . '<div id="fechamento-colaboradores-empty" style="display:none">' . $emptyHtml . '</div>',
]);
?>

<!-- Modal de Presencas -->
<?= renderModal([
    'id' => 'modal-presencas',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Presencas do Colaborador',
    'body' => '<input type="hidden" id="modal-presenca-id-alocacao" value="">'
           . '<div class="fg"><div class="fl">Nome</div>'
           . '<div id="modal-presenca-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div></div>'
           . '<div id="modal-presenca-lista" style="max-height:450px;overflow-y:auto;margin-top:16px;padding-top:12px;border-top:1px solid var(--bg-border-sub)"></div>'
]) ?>

<!-- Modal para Vencimento e Envio (colaboradores) -->
<?= renderModal([
    'id' => 'modal-colaborador-pagamento',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Enviar Colaborador para Pagamento',
    'body' => '<input type="hidden" id="modal-colab-id-alocacao" value="">'
           . '<input type="hidden" id="modal-colab-valor-original" value="">'
           . '<div class="fg"><div class="fl">Colaborador</div>'
           . '<div id="modal-colab-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '<div class="col2">'
           . '<div class="fg"><div class="fl">Valor Total Calculado (R$)</div>'
           . '<div id="modal-colab-valor-calculado" style="padding:10px 0;font-weight:600;font-size:15px;color:var(--text-3)"></div></div>'
           . '<div class="fg"><div class="fl">Data Vencimento Pagamento</div>'
           . '<input type="date" id="modal-colab-vencimento" class="fi"></div>'
           . '</div></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '<div class="fg"><div class="fl">Valor para Pagamento (R$) - Editável</div>'
           . '<input type="text" id="modal-colab-valor" class="fi" placeholder="0,00" style="font-weight:600;font-size:16px" oninput="mascaraMoeda(this)">'
           . '<div style="font-size:11px;color:var(--text-3);margin-top:4px">* Você pode ajustar o valor antes de enviar</div></div></div>',
    'footer' => '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-colaborador-pagamento">Cancelar</button>'
        . '<button type="button" class="btn btn-green" data-action="enviar-colaborador">Enviar para Pagamento</button>'
]) ?>

<!-- Modal de Horas Extras -->
<?= renderModal([
    'id' => 'modal-horas-extras',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Gerenciar Horas Extras do Colaborador',
    'body' => '<input type="hidden" id="modal-he-id-alocacao" value="">'
           . '<div class="fg"><div class="fl">Colaborador</div>'
           . '<div id="modal-he-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '  <form id="form-lancar-he" onsubmit="window.Fechamento.salvarHoraExtra(event)">'
           . '    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px">'
           . '      <div class="fg"><div class="fl">Data</div><input type="date" id="he-data" class="fi" required></div>'
           . '      <div class="fg"><div class="fl">Horas</div><input type="text" id="he-horas" class="fi" placeholder="ex: 02:00" required></div>'
           . '      <div class="fg"><div class="fl">Valor (R$)</div><input type="text" id="he-valor" class="fi" placeholder="0,00" oninput="mascaraMoeda(this)" required></div>'
           . '      <div class="fg"><div class="fl">Motivo/Obs</div><input type="text" id="he-motivo" class="fi" placeholder="ex: Horário estendido" required></div>'
           . '    </div>'
           . '    <div style="text-align:right;margin-bottom:16px">'
           . '      <button type="submit" class="btn btn-sm btn-purple">Adicionar Hora Extra</button>'
           . '    </div>'
           . '  </form>'
           . '</div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '  <div class="fl">Horas Extras Lançadas</div>'
           . '  <div id="he-lista" style="max-height:250px;overflow-y:auto;margin-top:8px"></div>'
           . '</div>',
    'footer' => '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-horas-extras">Fechar</button>'
]) ?>


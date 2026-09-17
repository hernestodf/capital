<?php
// Partial: Fechamento - Tab Outros Custos (4ª aba vertical)
// Fluxo: Registre o custo aqui → clique "Enviar Pgto" → aparece em /contas-pagar → pague lá
// Variaveis: $eventoId (disponivel no escopo do edit-fechamento.php)
?>
<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/spinner/spinner.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/badge/badge.php';

$loadingHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . renderSpinner(['variant' => 'dots', 'size' => 'md'])
    . '<div style="margin-top:8px">Carregando outros custos...</div></div>';

$emptyHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:36px;height:36px;margin:0 auto 10px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
    . '<div style="font-weight:600;margin-bottom:4px">Nenhum outro custo cadastrado</div>'
    . '<small style="color:var(--text-3)">Clique em "+ Novo Custo" para registrar despesas avulsas (transporte, alimentação, imprevistos etc).</small>'
    . '</div>';

echo renderCard([
    'title' => 'Outros Custos',
    'body'  => '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">'
             . '<div></div>'
             . '<button type="button" class="btn btn-sm btn-cyan" data-action="adicionar-outro-custo">+ Novo Custo</button>'
             . '</div>'
             . '<div id="fechamento-outros-loading">' . $loadingHtml . '</div>'
             . '<div id="fechamento-outros-content" style="display:none"></div>'
             . '<div id="fechamento-outros-empty" style="display:none">' . $emptyHtml . '</div>',
]);
?>

<!-- Modal: Criar / Editar Outro Custo -->
<?= renderModal([
    'id'      => 'modal-outro-custo',
    'variant' => 'form',
    'title'   => 'Outro Custo',
    'body'    =>
        '<input type="hidden" id="modal-outro-id" value="0">'

        // Descrição
        . '<div class="fg">'
        . '<div class="fl">Descrição <span style="color:var(--neon-red)">*</span></div>'
        . '<input type="text" id="modal-outro-descricao" class="fi" placeholder="Ex: Transporte extra da equipe" required>'
        . '</div>'

        // Valor + Vencimento
        . '<div class="col2" style="margin-top:12px">'
        . '<div class="fg">'
        . '<div class="fl">Valor (R$) <span style="color:var(--neon-red)">*</span></div>'
        . '<input type="text" id="modal-outro-valor" class="fi" placeholder="0,00" oninput="if(window.mascaraMoeda) mascaraMoeda(this)" style="font-weight:600" required>'
        . '</div>'
        . '<div class="fg">'
        . '<div class="fl">Data de Vencimento <span style="color:var(--neon-red)">*</span></div>'
        . '<input type="date" id="modal-outro-vencimento" class="fi" required>'
        . '</div>'
        . '</div>'

        // Observação
        . '<div class="fg" style="margin-top:12px">'
        . '<div class="fl">Observação <span style="font-size:11px;color:var(--text-3)">(opcional)</span></div>'
        . '<textarea id="modal-outro-obs" class="fi" rows="2" placeholder="Detalhes adicionais..."></textarea>'
        . '</div>'

        // Nota fiscal
        . '<div class="fg" style="margin-top:12px">'
        . '<div class="fl">Nota Fiscal <span style="font-size:11px;color:var(--text-3)">(opcional)</span></div>'
        . '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">'
        . '<label class="btn btn-cyan btn-sm" style="cursor:pointer">'
        . '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0-3v12"/></svg>'
        . 'Upload NF'
        . '<input type="file" id="modal-outro-nf" accept=".pdf,.jpg,.jpeg,.png" onchange="window.Fechamento.selecionarNF(this)" style="display:none">'
        . '</label>'
        . '<span id="modal-outro-nf-nome" style="font-size:12px;color:var(--text-2)">Nenhum arquivo selecionado</span>'
        . '</div>'
        . '<div id="modal-outro-nf-atual" style="margin-top:6px;font-size:12px;color:var(--text-2)"></div>'
        . '</div>',

    'footer' =>
        '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-outro-custo">Cancelar</button>'
        . '<button type="button" class="btn btn-green" data-action="salvar-outro-custo">Salvar</button>',
]) ?>

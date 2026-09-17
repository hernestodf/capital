<?php
// Partial: Fechamento - Tab Fornecedores
// Variaveis: $eventoId
?>
<?php
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/spinner/spinner.php';
require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/modal/modal.php';

$loadingHtml = '<div style="text-align:center;padding:32px;color:var(--text-3)">'
    . renderSpinner(['variant' => 'dots', 'size' => 'md'])
    . '<div style="margin-top:8px">Carregando fornecedores...</div></div>';

$emptyHtml = '<div style="text-align:center;padding:24px;color:var(--text-3)">Nenhum fornecedor vencedor encontrado para este evento.</div>';

echo renderCard([
    'body' => '<div id="fechamento-fornecedores-loading">' . $loadingHtml . '</div>'
           . '<div id="fechamento-fornecedores-content" style="display:none"></div>'
           . '<div id="fechamento-fornecedores-empty" style="display:none">' . $emptyHtml . '</div>',
]);
?>

<style>
/* ── Inputs editáveis do preview de parcelas ──────── */
.forn-prev-valor,
.forn-prev-data {
  display: block;
  width: 100%;
  box-sizing: border-box;
  background: var(--bg-elevated, #1e1e2e);
  color: var(--text-1, #e2e8f0);
  border: 1.5px solid var(--bg-border-sub, #334155);
  border-radius: 6px;
  padding: 5px 8px;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  transition: border-color .15s, box-shadow .15s;
}
.forn-prev-valor:focus,
.forn-prev-data:focus {
  outline: none;
  border-color: var(--neon-cyan, #06b6d4);
  box-shadow: 0 0 0 3px var(--neon-cyan-glow, rgba(6,182,212,.25));
}
.forn-prev-valor.entrada { color: var(--neon-green, #22c55e); }
#forn-parcelas-preview thead th,
#forn-parcelas-preview tbody td { vertical-align: middle; }
</style>

<!-- Modal para Pagamento Fornecedor -->
<?= renderModal([
    'id'      => 'modal-fornecedor-pagamento',
    'variant' => 'form',
    'size'    => 'lg',
    'title'   => 'Enviar Fornecedor para Pagamento',
    'body'    =>
        '<input type="hidden" id="modal-forn-id-cotacao" value="">'
      . '<input type="hidden" id="modal-forn-valor-total-hidden" value="">'

      /* Info Evento */
      . '<div style="background:var(--bg-secondary);border:1px solid var(--bg-border-sub);border-radius:8px;padding:12px;margin-bottom:16px">'
      .   '<div style="font-weight:700;font-size:10px;text-transform:uppercase;color:var(--text-3);margin-bottom:6px;letter-spacing:.5px">Nome do Evento</div>'
      .   '<div id="modal-forn-evento-cabecalho" style="font-size:13px;line-height:1.5;color:var(--text-2)"></div>'
      . '</div>'

      /* Info Item */
      . '<div style="background:var(--bg-secondary);border:1px solid var(--bg-border-sub);border-radius:8px;padding:12px;margin-bottom:16px">'
      .   '<div style="font-weight:700;font-size:10px;text-transform:uppercase;color:var(--text-3);margin-bottom:6px;letter-spacing:.5px">Item a Pagar</div>'
      .   '<div style="font-size:13px;line-height:1.5;color:var(--text-2)">'
      .     '<strong>Item Locado:</strong> <span id="modal-forn-item-nome"></span><br>'
      .     '<strong>Dias Locados:</strong> <span id="modal-forn-item-dias"></span> dias<br>'
      .     '<strong>Observação do Item:</strong> <span id="modal-forn-item-obs" style="font-style:italic"></span>'
      .   '</div>'
      .   '<div style="margin-top:12px;padding-top:12px;border-top:1px dashed var(--bg-border-sub)">'
      .     '<div style="font-weight:700;font-size:10px;text-transform:uppercase;color:var(--text-3);margin-bottom:6px;letter-spacing:.5px">Dados para Pagamento do Fornecedor</div>'
      .     '<div id="modal-forn-pagamento-dados" style="font-size:13px;line-height:1.5;color:var(--text-2);white-space:pre-wrap"></div>'
      .   '</div>'
      .   '<div style="margin-top:12px;padding-top:12px;border-top:1px dashed var(--bg-border-sub)">'
      .     '<div style="font-weight:700;font-size:10px;text-transform:uppercase;color:var(--text-3);margin-bottom:6px;letter-spacing:.5px">Observação para o Financeiro</div>'
      .     '<textarea id="modal-forn-observacao-financeiro" class="fi" rows="2" placeholder="Observação a ser enviada ao Contas a Pagar" style="margin-top:4px"></textarea>'
      .   '</div>'
      . '</div>'

      /* Fornecedor / Valor */
      . '<div class="fg"><div class="fl">Fornecedor</div>'
      .   '<div id="modal-forn-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div>'
      . '</div>'
      . '<div class="fg"><div class="fl">Valor Total (R$)</div>'
      .   '<div id="modal-forn-valor" style="padding:10px 0;font-weight:700;font-size:18px;color:var(--neon-cyan)"></div>'
      . '</div>'

      /* Documento */
      . '<div class="fg" style="margin-top:16px">'
      .   '<div class="fl">Anexar Documento / Nota Fiscal (PDF)</div>'
      .   '<input type="file" id="modal-forn-documento" class="fi" accept=".pdf">'
      . '</div>'

      /* Forma de Pagamento */
      . '<div style="border-top:2px solid var(--bg-border-sub);padding-top:20px;margin-top:20px">'
      .   '<div class="fg"><div class="fl" style="font-weight:600">Forma de Pagamento</div>'
      .     '<select id="modal-forn-tipo-pagamento" class="fi" onchange="toggleFormaPagamento()" style="font-weight:500">'
      .       '<option value="avista">À Vista (sem parcelas)</option>'
      .       '<option value="parcelado">Entrada + Parcelas</option>'
      .     '</select>'
      .   '</div>'

      /* À Vista */
      .   '<div id="forn-avista-fields" style="margin-top:16px">'
      .     '<div class="col2">'
      .       '<div class="fg"><div class="fl">Numero NF (opcional)</div>'
      .         '<input type="text" id="modal-forn-nf" class="fi" placeholder="Numero da nota fiscal">'
      .       '</div>'
      .       '<div class="fg"><div class="fl">Data Vencimento</div>'
      .         '<input type="date" id="modal-forn-vencimento" class="fi">'
      .       '</div>'
      .     '</div>'
      .   '</div>'

      /* Parcelado: configuração */
      .   '<div id="forn-parcelado-fields" style="display:none;margin-top:16px">'
      .     '<div style="padding:14px;background:var(--bg-elevated);border-radius:8px;margin-bottom:16px;border:1px solid var(--bg-border-sub)">'
      .       '<div style="font-weight:600;font-size:13px;color:var(--text-2);margin-bottom:12px">Configuração das Parcelas</div>'
      .       '<div class="col2">'
      .         '<div class="fg"><div class="fl">Valor Entrada (R$)</div>'
      .           '<input type="text" id="modal-forn-entrada" class="fi" placeholder="0,00" oninput="mascaraMoeda(this);calcularParcelas()" style="font-weight:600">'
      .         '</div>'
      .         '<div class="fg"><div class="fl">Número de Parcelas</div>'
      .           '<input type="number" id="modal-forn-numero-parcelas" class="fi" value="1" min="1" max="24" onchange="calcularParcelas()">'
      .         '</div>'
      .       '</div>'
      .       '<div class="col2" style="margin-top:8px">'
      .         '<div class="fg"><div class="fl">Vencimento 1ª Parcela</div>'
      .           '<input type="date" id="modal-forn-primeira-parcela" class="fi" onchange="calcularParcelas()">'
      .         '</div>'
      .         '<div class="fg"><div class="fl">Intervalo entre Parcelas (dias)</div>'
      .           '<input type="number" id="modal-forn-intervalo" class="fi" value="30" min="1" max="90" onchange="calcularParcelas()">'
      .         '</div>'
      .       '</div>'
      .     '</div>'

      /* Preview editável: valor + data */
      .     '<div id="forn-parcelas-preview" style="display:none;margin-top:8px">'
      .       '<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">'
      .         '<span style="font-weight:600;font-size:13px;color:var(--text-2)">Edite o valor e a data de cada parcela</span>'
      .         '<span id="forn-preview-aviso" style="font-size:11px;color:var(--neon-red);display:none">! Soma dos valores nao confere com o total</span>'
      .       '</div>'
      .       '<div style="max-height:300px;overflow-y:auto;border:1px solid var(--bg-border-sub);border-radius:8px">'
      .         '<table class="tbl" style="margin:0;table-layout:auto;width:100%">'
      .           '<thead><tr>'
      .             '<th style="width:32px;text-align:center">#</th>'
      .             '<th style="width:90px">Tipo</th>'
      .             '<th style="width:150px">Valor (R$)</th>'
      .             '<th style="width:160px">Vencimento</th>'
      .           '</tr></thead>'
      .           '<tbody id="forn-parcelas-preview-body"></tbody>'
      .         '</table>'
      .       '</div>'
      .     '</div>'
      .   '</div>'
      . '</div>',

    'footer' =>
        '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-fornecedor-pagamento">Cancelar</button>'
      . '<button type="button" class="btn btn-purple" data-action="enviar-fornecedor">Salvar e Enviar</button>',
]) ?>

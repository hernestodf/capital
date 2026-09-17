<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/forms/form-evento.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';

$baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
$csrfToken = \App\Core\Csrf::getToken();

$eventoId = $evento['id'] ?? 0;
$eventoNome = $evento['nome_evento'] ?? 'Evento';
$estado = $evento['estado'] ?? 'O';
$statusLocacao = $evento['status_locacao'] ?? 'A';

$salas = $salas ?? [];
$categorias = $categorias ?? [];
$itens = $produtoEventoService->findByEvento($eventoId) ?? [];
$produtosPorSala = $produtosPorSala ?? [];
$produtosEventoPorSala = $produtosEventoPorSala ?? [];
$grouped = $grouped ?? [];
$totais = $totais ?? ['total_venda' => 0, 'total_custo' => 0, 'lucro' => 0, 'total_itens' => 0];

// Calcular dias do evento
$diasEvento = 1;
if (!empty($evento['data_inicio']) && !empty($evento['data_fim'])) {
    try {
        $dtInicio = new DateTime($evento['data_inicio']);
        $dtFim = new DateTime($evento['data_fim']);
        $diasEvento = max(1, (int)$dtInicio->diff($dtFim)->days + 1);
    } catch (Exception $e) {
        $diasEvento = 1;
    }
}
?>

<script>
// Config global para JS dos eventos
window.EVENTO_ID = <?= $eventoId ?>;
window.BASE_URL = '<?= $baseUrl ?>';
window.CSRF_TOKEN = '<?= $csrfToken ?>';
window.EVENTO_NOME = <?= json_encode($eventoNome) ?>;
window.EVENTO_LOCAL = <?= json_encode($evento['local_evento'] ?? '') ?>;
window.EVENTO_DIAS = <?= (int)$diasEvento ?>;
window.EVENTO_OS_CLIENTE = <?= json_encode($evento['os_cliente'] ?? '') ?>;
window.RH_EVENTO_DATA = {
  data_inicio: '<?= $evento['data_inicio'] ?? '' ?>',
  data_fim: '<?= $evento['data_fim'] ?? '' ?>',
  hora_inicio: '<?= $evento['hora_inicio'] ?? '' ?>',
  hora_fim: '<?= $evento['hora_fim'] ?? '' ?>'
};
window.SALAS_MAP = {
  <?php foreach ($salas as $s): ?>
  <?= (int)$s['id'] ?>: { nome: <?= json_encode($s['nome_sala']) ?>, obs: <?= json_encode($s['orientacoes_montagem'] ?? '') ?>, produtos: <?= json_encode($produtosEventoPorSala[$s['id']] ?? []) ?> },
  <?php endforeach; ?>
};
</script>

<!-- Tab switching + popover -->
<script src="<?= $baseUrl ?>/js/eventos/tabs.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/tabs.js') ?>"></script>

    <section class="section active" id="sec-eventos-edit">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Evento</div>
          <div class="section-sub"><?= htmlspecialchars($eventoNome) ?></div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Horizontal Tabs -->
      <div class="loc-tabs_wrapper">
        <div class="loc-htabs" id="loc-htabs">
          <button class="loc-htab active" type="button" data-action="switch-h-tab" data-tab="0">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Locacoes
          </button>
          <button class="loc-htab" type="button" data-action="switch-h-tab" data-tab="1">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Montar OS
          </button>
          <button class="loc-htab" type="button" data-action="switch-h-tab" data-tab="2">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Devolver OS
          </button>
          <button class="loc-htab" type="button" data-action="switch-h-tab" data-tab="3">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Recursos Humanos
          </button>
          <button class="loc-htab" type="button" data-action="switch-h-tab" data-tab="5">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:-2px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Fechamento
          </button>
        </div>

        <!-- ========== PANE 0: Locacoes (com tabs verticais) ========== -->
        <div class="loc-hpane active" id="loc-hpane-0">
          <div style="display:flex;gap:0;min-height:500px">
            <!-- Tabs Verticais (esquerda) -->
            <div class="loc-vtabs" style="width:220px;flex-shrink:0;background:var(--bg-surface);border-right:1px solid var(--bg-border-sub);padding:8px 0">
              <button class="loc-vtab active" type="button" data-action="switch-v-tab" data-tab="0">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dados Evento
              </button>
              <button class="loc-vtab" type="button" data-action="switch-v-tab" data-tab="1">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Salas e Produtos
              </button>
            </div>

            <!-- Conteudo das tabs verticais (direita) -->
            <div style="flex:1;padding:16px;overflow:visible">
              <!-- VTab 0: Dados Evento -->
              <div class="loc-vpane active" id="loc-vpane-0">
                <?php require __DIR__ . '/partials/edit-dados.php'; ?>
              </div>

              <!-- VTab 1: Salas e Produtos -->
              <div class="loc-vpane" id="loc-vpane-1">
                <?php require __DIR__ . '/partials/edit-salas-produtos.php'; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- ========== PANE 1: Montar OS ========== -->
        <div class="loc-hpane" id="loc-hpane-1">
          <?php require __DIR__ . '/partials/edit-montar-os.php'; ?>
        </div>

        <!-- ========== PANE 2: Devolver OS ========== -->
        <div class="loc-hpane" id="loc-hpane-2">
          <?php require __DIR__ . '/partials/edit-devolver-os.php'; ?>
        </div>

        <!-- ========== PANE 3: Recursos Humanos ========== -->
        <div class="loc-hpane" id="loc-hpane-3">
          <?php require __DIR__ . '/partials/edit-rh.php'; ?>
        </div>

        <!-- ========== PANE 5: Fechamento ========== -->
        <div class="loc-hpane" id="loc-hpane-5">
          <?php require __DIR__ . '/partials/edit-fechamento.php'; ?>
        </div>

      </div><!-- /.loc-tabs_wrapper -->

    </section>

<style>
/* Tab horizontal custom */
.loc-tabs_wrapper{border-radius:14px;overflow:visible;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
.loc-htabs{display:flex;gap:0;border-bottom:2px solid var(--bg-border-sub);margin-bottom:0;background:var(--bg-surface);border-radius:14px 14px 0 0;overflow:hidden}
.loc-htab{flex:1;padding:14px 24px;font-size:14px;font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-3);position:relative;transition:color .15s,background .15s;font-family:'Inter',sans-serif;text-align:center}
.loc-htab:hover{background:var(--bg-hover);color:var(--text-1)}
.loc-htab.active{color:var(--neon-cyan);background:var(--bg-card);box-shadow:inset 0 -3px 0 var(--neon-cyan)}
.loc-hpane{display:none;background:var(--bg-card);border:2px solid var(--bg-border-sub);border-top:none;border-radius:0 0 14px 14px;overflow:visible;padding:20px}
.loc-hpane.active{display:block;width:100%}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Tabs verticais (dentro de Locacoes) */
.loc-vtabs{display:flex;flex-direction:column;gap:2px}
.loc-vtab{display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:transparent;color:var(--text-3);text-align:left;border-radius:8px;margin:2px 8px;transition:all .15s}
.loc-vtab:hover{background:var(--bg-hover);color:var(--text-1)}
.loc-vtab.active{color:var(--neon-cyan);background:rgba(8,145,178,0.12)}
.loc-vpane{display:none}
.loc-vpane.active{display:block}

/* Formulario Evento - Grid multi-coluna */
#form-evento .fg{margin-bottom:0}
#form-evento .fg .fl{margin-bottom:4px}
.form-row-4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px}
.form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.input-w-lg{min-width:120px}

/* Input com prefixo */
.fi-wrap[data-prefix]{display:flex;align-items:stretch;gap:0;border-radius:10px;overflow:hidden;border:1.5px solid var(--bg-border-sub);background:var(--bg-surface);transition:border-color .2s,box-shadow .2s}
.fi-wrap[data-prefix]:focus-within{border-color:var(--neon-cyan);box-shadow:0 0 0 3px var(--neon-cyan-glow)}
.pfx-txt{display:flex;align-items:center;justify-content:center;padding:0 12px;font-size:12px;font-weight:700;color:var(--text-3);background:var(--bg-secondary);border-right:1px solid var(--bg-border-sub);white-space:nowrap;min-width:42px;user-select:none}
.fi.has-pfx{border:none!important;flex:1;min-width:0;background:transparent!important;border-radius:0!important;box-shadow:none!important}
.fi.has-pfx:focus{border:none!important;box-shadow:none!important;outline:none}

/* Gap para botoes */
.btn-row{gap:8px}
.flex-gap-2,.flex-gap-2-end{gap:8px}

/* Resumo Financeiro - Cards Estatisticos */
.evento-totais{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
.total-card{background:var(--bg-surface);border:1px solid var(--bg-border-sub);border-radius:10px;padding:16px 20px;text-align:center;transition:transform .15s,box-shadow .15s}
.total-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.08)}
.total-label{font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-3);margin-bottom:6px;font-weight:600}
.total-value{font-size:22px;font-weight:700;font-family:'Inter',monospace}
.total-card-venda{border-left:3px solid var(--neon-orange)}
.total-card-custo{border-left:3px solid var(--neon-red)}
.total-card-lucro{border-left:3px solid var(--neon-green)}

/* ===== LISTA DE SALAS E PRODUTOS (novo layout) ===== */

/* Permitir popovers escaparem do card */
.custom-card.card-with-margin { overflow: visible; }
/* Card da lista de salas e produtos - permitir popovers */
.custom-card > .custom-card-body.card-no-padding,
.custom-card:has(.sala-bloco) { overflow: visible; }

/* Popover nos itens da sala */
.sala-item-info .popover-wrap { position: relative; }
.sala-item-info .popover-wrap.popover-active { z-index: 10001; }
.sala-item-info .popover.popover-sala { z-index: 10000 !important; }
/* Permitir popover-sala (detalhes de item/funcao) em QUALQUER lugar da pagina de edicao (para uso em RH, tabelas, etc) */
.popover.popover-sala { z-index: 10000 !important; }
.popover-wrap.popover-active { z-index: 10001; }
/* Garantir que popover escape de containers com overflow */
.sala-item { overflow: visible !important; }
.sala-itens-wrap { overflow: visible !important; }
.sala-item-info { overflow: visible !important; }
/* Wrapper direto no card-body tambem precisa de z-index */
.custom-card > .custom-card-body.card-no-padding { overflow: visible !important; }

/* Bloco da sala */
.sala-bloco{margin-bottom:0;border-bottom:1px solid var(--bg-border-sub);overflow:visible}
.sala-bloco:last-child{border-bottom:none}

/* Header da sala: nome com background + totais + botoes */
.sala-header{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:var(--bg-secondary);border-bottom:1px solid var(--bg-border-sub);flex-wrap:wrap;gap:8px}
.sala-header-left{display:flex;align-items:center;gap:8px}
.sala-header-right{display:flex;align-items:center;gap:6px;flex-wrap:wrap}

/* Nome da sala */
.sala-nome{font-size:13px;font-weight:700;color:var(--text-1);letter-spacing:0.3px}

/* Badges de totais no header */
.sala-total-badge{font-size:10px;font-weight:600;padding:3px 8px;border-radius:4px;background:rgba(6,182,212,0.1);color:var(--neon-cyan)}
.sala-total-badge-custo{background:rgba(244,63,94,0.1);color:var(--neon-red)}
.sala-total-badge-lucro{background:rgba(34,197,94,0.1);color:var(--neon-green)}
.sala-total-badge-prejuizo{background:rgba(244,63,94,0.1);color:var(--neon-red)}

/* Observacao de montagem (linha separada) */
.sala-obs-row{display:flex;align-items:center;gap:6px;padding:8px 16px;background:rgba(245,158,11,0.04);border-bottom:1px dashed var(--bg-border-sub);font-size:11px;color:var(--text-3);font-style:italic}

/* Wrapper dos itens */
.sala-itens-wrap{padding:12px 16px 16px 16px}

/* Item individual */
.sala-item{padding:10px 0;border-bottom:1px solid var(--bg-border-sub)}
.sala-item:last-child{border-bottom:none}

/* Linha superior do item: nome + badges + acoes */
.sala-item-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.sala-item-info{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.sala-item-nome{font-size:13px;font-weight:600;color:var(--text-1)}
.sala-item-categoria{font-size:10px;padding:2px 6px;background:rgba(8,145,178,0.1);border-radius:4px;color:var(--neon-cyan)}
.sala-item-obs-badge{font-size:9px;padding:2px 5px;background:rgba(245,158,11,0.15);border-radius:3px;color:var(--neon-yellow);cursor:pointer;font-weight:600}
.sala-item-actions{display:flex;align-items:center;gap:4px}

/* Campos editaveis do item (linha 2) */
.sala-item-fields{display:flex;align-items:flex-end;gap:10px;flex-wrap:nowrap}
.sala-field-group{display:flex;flex-direction:column;gap:2px}
.sala-field-label{font-size:9px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-3);font-weight:600}
.sala-field-total .salas-produtos-total-display{font-size:14px;font-weight:700;font-family:'Inter',monospace;color:var(--neon-green);padding:4px 8px}

/* Campos pequenos (Qtd e Dias) */
.sala-field-qtd, .sala-field-dias{flex:0 0 60px}
.sala-field-qtd .fi, .sala-field-dias .fi{width:60px;min-width:60px}

/* Campos medios (Valor e Custo) */
.sala-field-valor, .sala-field-custo{flex:0 0 110px}
.sala-field-valor .fi-wrap, .sala-field-custo .fi-wrap{width:110px}

/* Campo Total (flexivel) */
.sala-field-total{flex:1 0 auto}

/* Item categoria e obs (legado) */
.item-categoria{display:inline-block;padding:2px 6px;background:rgba(8,145,178,0.1);border-radius:4px;font-size:10px}
.item-obs-item-name{padding:8px 12px;background:var(--bg-hover);border-radius:6px}

@media(max-width:1366px){
  .loc-htab{padding:12px 16px;font-size:13px}
  .sala-item-fields{gap:8px}
  .sala-field-qtd, .sala-field-dias{flex:0 0 55px}
  .sala-field-qtd .fi, .sala-field-dias .fi{width:55px;min-width:55px}
  .sala-field-valor, .sala-field-custo{flex:0 0 100px}
  .sala-field-valor .fi-wrap, .sala-field-custo .fi-wrap{width:100px}
}
@media(max-width:1280px){
  .loc-htab{padding:10px 12px;font-size:12px}
  .evento-totais{grid-template-columns:repeat(2,1fr)}
  .sala-header{flex-direction:column;align-items:flex-start}
  .sala-item-fields{gap:6px}
  .sala-field-qtd, .sala-field-dias{flex:0 0 50px}
  .sala-field-qtd .fi, .sala-field-dias .fi{width:50px;min-width:50px}
  .sala-field-valor, .sala-field-custo{flex:0 0 95px}
  .sala-field-valor .fi-wrap, .sala-field-custo .fi-wrap{width:95px}
}
@media(max-width:768px){
  .loc-htabs{flex-wrap:wrap}
  .loc-htab{flex:none;width:50%;padding:10px 8px;font-size:11px}
  .evento-totais{grid-template-columns:1fr}
  .sala-item-fields{flex-direction:column;gap:6px}
  .sala-field-group{width:100%}
  .sala-field-group .fi{width:100%}
  .sala-field-group .fi-wrap{width:100%}
  .sala-field-qtd, .sala-field-dias{flex:0 0 auto;width:100%}
  .sala-field-qtd .fi, .sala-field-dias .fi{width:100%;min-width:auto}
  .sala-field-valor, .sala-field-custo{flex:0 0 auto;width:100%}
  .sala-field-valor .fi-wrap, .sala-field-custo .fi-wrap{width:100%}
}
</style>

<!-- Dados Evento (salvar, finalizar) -->
<script src="<?= $baseUrl ?>/js/eventos/dados-evento.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/dados-evento.js') ?>"></script>

<!-- Salas e Produtos (CRUD items, modals, calculations) -->
<script src="<?= $baseUrl ?>/js/eventos/salas-produtos.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/salas-produtos.js') ?>"></script>

<!-- Montar OS — Serial (inserir, encaminhar, remover, lote) -->
<script src="<?= $baseUrl ?>/js/eventos/montar-os-serial.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/montar-os-serial.js') ?>"></script>

<!-- Montar OS (init, sala select) -->
<script src="<?= $baseUrl ?>/js/eventos/montar-os.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/montar-os.js') ?>"></script>

<!-- Alocacao de Estoque (modal alocar serial, badges) -->
<script src="<?= $baseUrl ?>/js/eventos/alocacao-estoque.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/alocacao-estoque.js') ?>"></script>

<!-- Recursos Humanos -->
<script src="<?= $baseUrl ?>/js/eventos/rh.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/rh.js') ?>"></script>

<!-- Fechamento -->
<script src="<?= $baseUrl ?>/js/eventos/fechamento.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/fechamento.js') ?>"></script>
<script src="<?= $baseUrl ?>/js/eventos/fechamento-colaboradores.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/fechamento-colaboradores.js') ?>"></script>
<script src="<?= $baseUrl ?>/js/eventos/fechamento-fornecedores.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/fechamento-fornecedores.js') ?>"></script>
<script src="<?= $baseUrl ?>/js/eventos/fechamento-outros.js?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/eventos/fechamento-outros.js') ?>"></script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

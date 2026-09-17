<?php // Partial: Recursos Humanos (HPane 3)
$compBase = dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/';
require_once $compBase . 'card/card.php';
require_once $compBase . 'badge/badge.php';
require_once $compBase . 'button/button.php';
require_once $compBase . 'table/table.php';
require_once $compBase . 'modal/modal.php';
require_once $compBase . 'alert/alert.php';
require_once $compBase . 'input/input.php';

$baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
$eventoId = $evento['id'] ?? 0;
$eventoNome = $evento['nome_evento'] ?? 'Evento';
$csrfToken = \App\Core\Csrf::getToken();
?>

<div style="padding:20px">

<!-- Header -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:16px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:18px;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($eventoNome) ?></div>
        <div style="font-size:12px;color:var(--text-3);margin-top:2px">Recursos Humanos — Gestao de Colaboradores</div>
      </div>
    </div>
  </div>
</div>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="rh-stats-grid">
  <div class="card-stat">
    <div class="card-stat-val" id="rh-total-alocados">0</div>
    <div class="card-stat-lbl">Total Alocados</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--green)">
    <div class="card-stat-val" style="color:var(--green)" id="rh-presencas-hoje">0</div>
    <div class="card-stat-lbl">Presencas Hoje</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--cyan)">
    <div class="card-stat-val" style="color:var(--cyan);font-size:15px" id="rh-total-valor">R$ 0,00</div>
    <div class="card-stat-lbl">Valor Total Colaboradores</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--purple)">
    <div class="card-stat-val" style="color:var(--purple);font-size:15px" id="rh-total-comissao">R$ 0,00</div>
    <div class="card-stat-lbl">Comissao</div>
  </div>
</div>

<style>
  @media (max-width:1400px){.rh-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:1024px){.rh-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.rh-stats-grid{grid-template-columns:1fr!important}}
</style>

<style>
  @media (max-width:1024px){.rh-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.rh-stats-grid{grid-template-columns:1fr!important}}
</style>

<!-- Botoes de Acao -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <button type="button" class="btn btn-cyan" data-action="abrir-modal-alocar">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M12 5v14M5 12h14"/></svg>
    Alocar Colaborador
  </button>
</div>

<!-- Tabela de Colaboradores -->
<div class="card">
  <div class="card-head">
    <div class="card-title">Colaboradores Alocados</div>
    <span class="badge sm cyan" id="rh-badge-count">0 item(s)</span>
  </div>
  <div class="card-body" id="rh-table-body">
    <div style="text-align:center;padding:40px;color:var(--text-3)">Carregando...</div>
  </div>
</div>

</div>

<!-- Modal Alocar Colaborador -->
<?= renderModal([
    'id' => 'modal-alocar-colaborador',
    'variant' => 'form',
    'title' => 'Alocar Colaborador ao Evento',
    'body' => '<input type="hidden" id="modal-select-colaborador" value="">' .
        '<div class="col2">' .
        '  <div class="fg">' .
        '    <div class="fl">Atua Como (Função)</div>' .
        '    <select class="fi" id="modal-filter-funcao" onchange="window.filtrarColaboradores()"><option value="">Carregando...</option></select>' .
        '  </div>' .
        '  <div class="fg">' .
        '    <div class="fl">Buscar Colaborador</div>' .
        '    <input type="text" class="fi" id="modal-search-colab" placeholder="Nome ou telefone..." oninput="window.filtrarColaboradores()"/>' .
        '  </div>' .
        '</div>' .
        '<div class="fg" style="margin-top:14px">' .
        '  <div class="fl">Selecione o Colaborador</div>' .
        '  <div id="modal-colaboradores-lista-container" style="max-height:220px;overflow-y:auto;border:1px solid var(--bg-border-sub);border-radius:8px;padding:10px;background:var(--bg-surface);"></div>' .
        '</div>' .
        '<div class="fg" style="margin-top:12px">' .
        '<div class="fl">Funcao no Evento</div>' .
        '<input type="text" class="fi" id="modal-funcao" placeholder="Ex: Montador, Tecnico, etc"/>' .
        '</div>' .
        '<div class="col2" style="margin-top:12px">' .
        '<div class="fg"><div class="fl">Data Inicio</div><input type="date" class="fi" id="modal-data-inicio"/></div>' .
        '<div class="fg"><div class="fl">Data Fim</div><input type="date" class="fi" id="modal-data-fim"/></div>' .
        '</div>' .
        '<div class="col2" style="margin-top:12px">' .
        '<div class="fg"><div class="fl">Hora Inicio</div><input type="time" class="fi" id="modal-hora-inicio"/></div>' .
        '<div class="fg"><div class="fl">Hora Fim</div><input type="time" class="fi" id="modal-hora-fim"/></div>' .
        '</div>' .
        '<div class="fg" style="margin-top:12px">' .
        '<div class="fl">Valor Diaria (R$)</div>' .
        '<input type="number" class="fi" id="modal-valor-diaria" step="0.01" min="0" placeholder="0.00"/>' .
        '</div>' .
        '<div class="fg" style="margin-top:12px">' .
        '<div class="fl">Vencimento Pagamento</div>' .
        '<input type="date" class="fi" id="modal-vencimento"/>' .
        '</div>',
    'footer' => '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-alocar-colaborador">Cancelar</button>' .
        '<button type="button" class="btn btn-green" id="btn-confirmar-alocacao">Alocar</button>'
]) ?>

<!-- Modal Ver Presencas -->
<?= renderModal([
    'id' => 'modal-ver-presencas',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Presencas do Colaborador',
    'body' => '<input type="hidden" id="rh-presenca-id-alocacao" value="">'
        . '<div class="fg"><div class="fl">Nome</div>'
        . '<div id="rh-presenca-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div></div>'
        . '<div id="modal-presencas-content" style="max-height:70vh;overflow-y:auto">'
        . '<div style="text-align:center;padding:40px;color:var(--text-3)">Carregando...</div>'
        . '</div>',
    'footer' => '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-ver-presencas">Fechar</button>'
]) ?>

<!-- Modal para Marcar Presenca Manual (RH) -->
<?= renderModal([
    'id' => 'modal-rh-presenca-manual',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Marcar Presenca Manualmente',
    'body' => '<input type="hidden" id="rh-manual-presenca-id-alocacao" value="">'
           . '<div class="fg"><div class="fl">Colaborador</div>'
           . '<div id="rh-manual-presenca-nome" style="padding:10px 0;font-weight:600;font-size:15px"></div></div>'
           . '<div class="fg"><div class="fl">Data da Presenca *</div>'
           . '<input type="date" id="rh-manual-presenca-data" class="fi" required></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '<div style="font-weight:600;margin-bottom:12px;color:var(--text-2)">Registro de Entrada (opcional)</div>'
           . '<div class="col2">'
           . '<div class="fg"><div class="fl">Horario de Entrada</div>'
           . '<input type="time" id="rh-manual-presenca-hora-entrada" class="fi"></div>'
           . '<div class="fg"><div class="fl">Foto de Entrada</div>'
           . '<input type="file" id="rh-manual-presenca-foto-entrada" class="fi" accept="image/*"></div>'
           . '</div></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '<div style="font-weight:600;margin-bottom:12px;color:var(--text-2)">Registro de Saida (opcional)</div>'
           . '<div class="col2">'
           . '<div class="fg"><div class="fl">Horario de Saida</div>'
           . '<input type="time" id="rh-manual-presenca-hora-saida" class="fi"></div>'
           . '<div class="fg"><div class="fl">Foto de Saida</div>'
           . '<input type="file" id="rh-manual-presenca-foto-saida" class="fi" accept="image/*"></div>'
           . '</div></div>'
           . '<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">'
           . '<div class="fg"><div class="fl">Status da Presenca *</div>'
           . '<select id="rh-manual-presenca-status" class="fi" required>'
           . '<option value="parcial">Parcial (apenas entrada)</option>'
           . '<option value="completo">Completo (entrada + saida)</option>'
           . '</select></div>'
           . '<div class="fg"><div class="fl">Observacao</div>'
           . '<textarea id="rh-manual-presenca-obs" class="fi" rows="2" placeholder="Motivo da presenca manual..."></textarea></div></div>',
    'footer' => '<button type="button" class="btn btn-cyan" data-action="close-modal" data-target="modal-rh-presenca-manual">Cancelar</button>'
        . '<button type="button" class="btn btn-green" data-action="rh-salvar-presenca-manual">Salvar Presenca</button>'
]) ?>

<script>
window.RH_COLABORADORES_LIST = null;
</script>

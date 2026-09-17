<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';

$tipos = $tiposRelatorio;
$secoes = $secoesList;
$produtos = $produtosList;

// Options para selects
$secaoOptions = '<option value="">Todas as secoes</option>';
foreach ($secoes as $sec) {
    $secaoOptions .= '<option value="' . $sec['id'] . '">' . htmlspecialchars($sec['secao']) . '</option>';
}

$produtoOptions = '<option value="">Selecione um produto</option>';
foreach ($produtos as $prod) {
    $produtoOptions .= '<option value="' . $prod['id'] . '">' . htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($prod['produto'] ?? ''))) . '</option>';
}

$statusOptions = '<option value="">Todos os status</option>
<option value="ATIVO">Ativo</option>
<option value="MANUTENCAO">Manutencao</option>
<option value="VENDER">Vender</option>';
?>

    <section class="section active" id="sec-relatorios-estoque">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Relatorios de Estoque</div>
          <div class="section-sub">Selecione o tipo de relatorio e aplique filtros</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Cards de tipos de relatorio -->
      <div class="col3">
        <?php foreach ($tipos as $tipo): ?>
        <div class="card" style="cursor:pointer;border:2px solid transparent;transition:all 0.2s" 
             id="card-tipo-<?php echo $tipo['id']; ?>"
             data-action="selecionar-tipo" data-tipo="<?php echo $tipo['id']; ?>">
          <div class="card-head">
            <span class="card-title" style="display:flex;align-items:center;gap:8px">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="<?php echo $tipo['cor']; ?>" stroke-width="2">
                <?php
                switch ($tipo['icon']) {
                    case 'inventory':
                        echo '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>';
                        break;
                    case 'section':
                        echo '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>';
                        break;
                    case 'warning':
                        echo '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
                        break;
                    case 'sell':
                        echo '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                        break;
                    case 'product':
                        echo '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>';
                        break;
                }
                ?>
              </svg>
              <?php echo htmlspecialchars($tipo['nome']); ?>
            </span>
          </div>
          <div class="card-body">
            <p style="font-size:13px;color:var(--text-4);margin:0"><?php echo htmlspecialchars($tipo['descricao']); ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Area de filtros e geracao -->
      <div class="card" style="margin-top:24px;display:none" id="area-filtros">
        <div class="card-head">
          <span class="card-title">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filtros do Relatorio
          </span>
        </div>
        <div class="card-body">
          <input type="hidden" id="tipo-relatorio" value="" />

          <div class="col2">
            <!-- Filtro Secao -->
            <div class="fg" id="filtro-secao">
              <div class="fl">Secao</div>
              <select id="filtro-secao-select" class="fi">
                <?php echo $secaoOptions; ?>
              </select>
            </div>

            <!-- Filtro Produto -->
            <div class="fg" id="filtro-produto" style="display:none">
              <div class="fl">Produto</div>
              <select id="filtro-produto-select" class="fi">
                <?php echo $produtoOptions; ?>
              </select>
            </div>

            <!-- Filtro Status -->
            <div class="fg">
              <div class="fl">Status do Código de Barras</div>
              <select id="filtro-status-select" class="fi">
                <?php echo $statusOptions; ?>
              </select>
            </div>
          </div>

          <div style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end">
            <button type="button" class="btn btn-gray" data-action="navegar" data-url="<?= $baseUrl ?>/estoque">Voltar</button>
            <button type="button" class="btn btn-cyan" data-action="gerar-relatorio">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Gerar PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Info Card -->
      <div class="card" style="margin-top:24px">
        <div class="card-head">
          <span class="card-title">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Informacoes
          </span>
        </div>
        <div class="card-body">
          <ul style="margin:0;padding-left:20px;font-size:13px;color:var(--text-4);line-height:1.8">
            <li>Selecione um tipo de relatorio nos cards acima</li>
            <li>Aplique os filtros desejados (secao, produto, status)</li>
            <li>O relatorio sera gerado em formato PDF com dados da empresa</li>
            <li>Cada relatorio inclui resumo estatistico com totais por status</li>
          </ul>
        </div>
      </div>
    </section>

<script>
// Registrado via window.registerActions (nao document.addEventListener) pra nao duplicar
// o dispatch de clique: scripts.js ja delega [data-action] globalmente em document.body
// e cai num fallback window[camelCase(action)] pra acoes nao reconhecidas — registrar
// aqui E TAMBEM ouvir 'click' localmente disparava cada acao 2x (bug-XXX). 'navegar' ja
// e tratado direto pelo handler global, nao precisa registrar de novo.
document.addEventListener('DOMContentLoaded', function() {
  window.registerActions({
    'selecionar-tipo': function(el) { selecionarTipo(el.dataset.tipo); },
    'gerar-relatorio': function() { gerarRelatorio(); },
  });
});

// Selecionar tipo de relatorio
function selecionarTipo(tipo) {
  // Remover selecao anterior
  document.querySelectorAll('[id^="card-tipo-"]').forEach(function(card) {
    card.style.borderColor = 'transparent';
    card.style.boxShadow = 'none';
  });

  // Selecionar novo tipo
  const card = document.getElementById('card-tipo-' + tipo);
  if (card) {
    card.style.borderColor = '#06b6d4';
    card.style.boxShadow = '0 0 0 1px #06b6d4';
  }

  document.getElementById('tipo-relatorio').value = tipo;
  document.getElementById('area-filtros').style.display = 'block';

  // Mostrar/esconder filtros conforme o tipo
  const filtroSecao = document.getElementById('filtro-secao');
  const filtroProduto = document.getElementById('filtro-produto');

  filtroSecao.style.display = 'block';
  filtroProduto.style.display = 'none';

  if (tipo === 'por_produto') {
    filtroSecao.style.display = 'none';
    filtroProduto.style.display = 'block';
  } else if (tipo === 'manutencao' || tipo === 'vender') {
    filtroSecao.style.display = 'none';
    // Pre-select status
    document.getElementById('filtro-status-select').value = tipo.toUpperCase();
  }

  // Scroll suave para area de filtros
  document.getElementById('area-filtros').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Gerar relatorio
function gerarRelatorio() {
  const tipo = document.getElementById('tipo-relatorio').value;
  if (!tipo) {
    showToast('yellow', 'Atencao', 'Selecione um tipo de relatorio');
    return;
  }

  const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : window.BASE_URL || '';

  // Montar parametros conforme tipo
  let params = 'tipo=' + tipo;

  if (tipo === 'por_secao') {
    const secao = document.getElementById('filtro-secao-select').value;
    if (secao) {
      params += '&id_secao=' + secao;
    }
  }

  if (tipo === 'por_produto') {
    const produto = document.getElementById('filtro-produto-select').value;
    if (!produto) {
      showToast('yellow', 'Atencao', 'Selecione um produto');
      return;
    }
    params += '&id_produto=' + produto;
  }

  const status = document.getElementById('filtro-status-select').value;
  if (status) {
    params += '&status=' + status;
  }

  // Submeter formulario POST
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = baseUrl + '/estoque/relatorios/gerar';
  form.style.display = 'none';

  const paramsArray = params.split('&');
  paramsArray.forEach(function(p) {
    const parts = p.split('=');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = parts[0];
    input.value = parts[1];
    form.appendChild(input);
  });

  // CSRF token
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_csrf_token';
  csrfInput.value = typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '';
  form.appendChild(csrfInput);

  document.body.appendChild(form);
  form.submit();
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

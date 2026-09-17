<?php // Partial: Montar OS (HPane 1)
$compBase = dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/';
require_once $compBase . 'card/card.php';
require_once $compBase . 'badge/badge.php';
require_once $compBase . 'button/button.php';
require_once $compBase . 'table/table.php';
require_once $compBase . 'alert/alert.php';

$baseUrl      = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
$eventoId     = $evento['id'] ?? 0;
$eventoNome   = $evento['nome_evento'] ?? 'Evento';
$csrfToken    = \App\Core\Csrf::getToken();

$salasMontagem       = $salasMontagem ?? [];
$salasEvento         = $salas ?? [];
$produtosEventoPorSala = $produtosEventoPorSala ?? [];

// Separar pendentes e agrupar seriais por sala
// seraisPorPeId: vinculados a um produtos_evento (id_produto_evento preenchido)
// seraisOrfaos: legados sem id_produto_evento, agrupados por nome do produto
$pendentes      = [];
$seraisPorPeId  = []; // [id_sala][id_produto_evento] => [montagem...]
$seraisOrfaos   = []; // [id_sala][nome_key] => ['nome' => string, 'itens' => [...]]

foreach ($salasMontagem as $item) {
    if (empty($item['id_sala'])) {
        $pendentes[] = $item;
    } else {
        $sid  = $item['id_sala'];
        $peId = (int)($item['id_produto_evento'] ?? 0);
        if ($peId > 0) {
            $seraisPorPeId[$sid][$peId][] = $item;
        } else {
            $nomeKey = mb_strtolower(trim((string)($item['produto'] ?? '')));
            $seraisOrfaos[$sid][$nomeKey]['nome']  = preg_replace('/\s*-\s*\d+$/', '', (string)($item['produto'] ?? ''));
            $seraisOrfaos[$sid][$nomeKey]['itens'][] = $item;
        }
    }
}
?>

<div style="padding:20px">

<!-- Header do Evento -->
<?php
$demandanteAtualId   = $evento['id_demandante'] ?? '';
$demandanteAtualNome = '';
foreach (($demandantes ?? []) as $d) {
    if ((string)$d['id'] === (string)$demandanteAtualId) {
        $demandanteAtualNome = $d['nome'];
        break;
    }
}
$osCsrf = \App\Core\Csrf::getToken();

$separacaoAtualId   = $evento['id_usuario_separacao'] ?? '';
$separacaoAtualNome = '';
foreach (($usuarios ?? []) as $u) {
    if ((string)$u['id'] === (string)$separacaoAtualId) {
        $separacaoAtualNome = $u['name'] ?? $u['nome'] ?? '';
        break;
    }
}

$estadoAtual = $evento['estado'] ?? 'O';
$estadoLabel = match($estadoAtual) { 'L' => 'Locação', 'P' => 'Pedido', default => 'Orçamento' };
$osClienteAtual = $evento['os_cliente'] ?? '';
?>
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:16px 20px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <div style="flex:1;min-width:0">
        <div style="font-size:18px;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($eventoNome) ?></div>
        <div style="font-size:12px;color:var(--text-3);margin-top:2px">Montagem — Inserir códigos e encaminhar para salas</div>

        <!-- Campos inline: Comprador, Estado, Separado por, Pedido -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px 24px;margin-top:12px">

          <!-- Comprador -->
          <div>
            <div id="os-comprador-display" style="display:flex;align-items:center;gap:6px">
              <span style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.3px">Comprador:</span>
              <span id="os-comprador-nome" style="font-size:13px;font-weight:600;color:var(--text-1)">
                <?= $demandanteAtualNome ? htmlspecialchars($demandanteAtualNome) : '<span style="color:var(--text-4)">Não informado</span>' ?>
              </span>
              <button type="button" data-action="abrir-editar-comprador" style="background:none;border:1px solid var(--bg-border);border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;color:var(--text-3)">Alterar</button>
            </div>
            <div id="os-comprador-edit" style="display:none;align-items:center;gap:6px;flex-wrap:wrap">
              <select id="os-sel-comprador" class="fi" style="width:180px;height:28px;font-size:12px">
                <option value="">— Não informado —</option>
                <?php foreach (($demandantes ?? []) as $d): ?>
                <option value="<?= $d['id'] ?>"<?= (string)$d['id'] === (string)$demandanteAtualId ? ' selected' : '' ?>><?= htmlspecialchars($d['nome']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn btn-cyan" style="height:28px;padding:0 10px;font-size:11px" data-action="salvar-comprador">Salvar</button>
              <button type="button" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--text-3)" data-action="cancelar-editar-comprador">Cancelar</button>
            </div>
          </div>

          <!-- Estado -->
          <div>
            <div id="os-estado-display" style="display:flex;align-items:center;gap:6px">
              <span style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.3px">Estado:</span>
              <span id="os-estado-label" style="font-size:13px;font-weight:600;color:var(--text-1)"><?= $estadoLabel ?></span>
              <button type="button" data-action="abrir-editar-estado" style="background:none;border:1px solid var(--bg-border);border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;color:var(--text-3)">Alterar</button>
            </div>
            <div id="os-estado-edit" style="display:none;align-items:center;gap:6px">
              <select id="os-sel-estado" class="fi" style="width:130px;height:28px;font-size:12px">
                <option value="O"<?= $estadoAtual === 'O' ? ' selected' : '' ?>>Orçamento</option>
                <option value="L"<?= $estadoAtual === 'L' ? ' selected' : '' ?>>Locação</option>
                <option value="P"<?= $estadoAtual === 'P' ? ' selected' : '' ?>>Pedido</option>
              </select>
              <button type="button" class="btn btn-cyan" style="height:28px;padding:0 10px;font-size:11px" data-action="salvar-estado">Salvar</button>
              <button type="button" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--text-3)" data-action="cancelar-editar-estado">Cancelar</button>
            </div>
          </div>

          <!-- Separado por -->
          <div>
            <div id="os-separacao-display" style="display:flex;align-items:center;gap:6px">
              <span style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.3px">Separado por:</span>
              <span id="os-separacao-nome" style="font-size:13px;font-weight:600;color:var(--text-1)">
                <?= $separacaoAtualNome ? htmlspecialchars($separacaoAtualNome) : '<span style="color:var(--text-4)">Não informado</span>' ?>
              </span>
              <button type="button" data-action="abrir-editar-separacao" style="background:none;border:1px solid var(--bg-border);border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;color:var(--text-3)">Alterar</button>
            </div>
            <div id="os-separacao-edit" style="display:none;align-items:center;gap:6px;flex-wrap:wrap">
              <select id="os-sel-separacao" class="fi" style="width:180px;height:28px;font-size:12px">
                <option value="">— Ninguém —</option>
                <?php foreach (($usuarios ?? []) as $u): ?>
                <option value="<?= $u['id'] ?>"<?= (string)$u['id'] === (string)$separacaoAtualId ? ' selected' : '' ?>><?= htmlspecialchars($u['name'] ?? $u['nome'] ?? '') ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn btn-cyan" style="height:28px;padding:0 10px;font-size:11px" data-action="salvar-separacao">Salvar</button>
              <button type="button" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--text-3)" data-action="cancelar-editar-separacao">Cancelar</button>
            </div>
          </div>

          <!-- Pedido (OS Cliente) -->
          <div>
            <div id="os-pedido-display" style="display:flex;align-items:center;gap:6px">
              <span style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.3px">Pedido:</span>
              <span id="os-pedido-valor" style="font-size:13px;font-weight:600;color:var(--text-1)">
                <?= $osClienteAtual ? htmlspecialchars($osClienteAtual) : '<span style="color:var(--text-4)">Não informado</span>' ?>
              </span>
              <button type="button" data-action="abrir-editar-pedido" style="background:none;border:1px solid var(--bg-border);border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;color:var(--text-3)">Alterar</button>
            </div>
            <div id="os-pedido-edit" style="display:none;align-items:center;gap:6px">
              <input type="text" id="os-input-pedido" class="fi" style="width:160px;height:28px;font-size:12px" placeholder="Nº do pedido" value="<?= htmlspecialchars($osClienteAtual) ?>" />
              <button type="button" class="btn btn-cyan" style="height:28px;padding:0 10px;font-size:11px" data-action="salvar-pedido">Salvar</button>
              <button type="button" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--text-3)" data-action="cancelar-editar-pedido">Cancelar</button>
            </div>
          </div>

        </div>
      </div>
      <a href="<?= $baseUrl ?>/montagem/pdf/<?= $eventoId ?>" target="_blank" class="btn btn-cyan" style="flex-shrink:0;margin-top:4px">
        <svg style="width:16px;height:16px;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m0-8v8"/></svg>
        Imprimir Plano de Montagem
      </a>
    </div>
  </div>
</div>
<script>
(function() {
  var BASE_OS   = '<?= $baseUrl ?>';
  var CSRF_OS   = '<?= $osCsrf ?>';
  var EVENTO_ID = <?= (int)$eventoId ?>;

  var nomes = {};
  <?php foreach (($demandantes ?? []) as $d): ?>
  nomes[<?= (int)$d['id'] ?>] = '<?= htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') ?>';
  <?php endforeach; ?>

  var nomesUsuarios = {};
  <?php foreach (($usuarios ?? []) as $u): ?>
  nomesUsuarios[<?= (int)$u['id'] ?>] = '<?= htmlspecialchars($u['name'] ?? $u['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>';
  <?php endforeach; ?>

  function postEvento(path, body) {
    return fetch(BASE_OS + '/eventos/' + path + '/' + EVENTO_ID, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: '_csrf_token=' + encodeURIComponent(CSRF_OS) + '&' + body,
    }).then(function(r) { return r.json(); });
  }

  // --- Comprador ---
  window.abrirEditarComprador = function() {
    document.getElementById('os-comprador-display').style.display = 'none';
    document.getElementById('os-comprador-edit').style.display    = 'flex';
  };
  window.cancelarEditarComprador = function() {
    document.getElementById('os-comprador-display').style.display = 'flex';
    document.getElementById('os-comprador-edit').style.display    = 'none';
  };
  window.salvarComprador = function() {
    var id = document.getElementById('os-sel-comprador').value;
    postEvento('update-comprador', 'id_demandante=' + encodeURIComponent(id))
      .then(function(data) {
        if (data.success) {
          var el = document.getElementById('os-comprador-nome');
          el.innerHTML = id && nomes[id] ? nomes[id] : '<span style="color:var(--text-4)">Não informado</span>';
          cancelarEditarComprador();
          showToast('green', 'Salvo', 'Comprador atualizado');
        } else { showToast('red', 'Erro', data.error || 'Erro ao salvar'); }
      })
      .catch(function() { showToast('red', 'Erro', 'Erro de comunicação'); });
  };

  // --- Estado ---
  window.abrirEditarEstado = function() {
    document.getElementById('os-estado-display').style.display = 'none';
    document.getElementById('os-estado-edit').style.display    = 'flex';
  };
  window.cancelarEditarEstado = function() {
    document.getElementById('os-estado-display').style.display = 'flex';
    document.getElementById('os-estado-edit').style.display    = 'none';
  };
  window.salvarEstado = function() {
    var val = document.getElementById('os-sel-estado').value;
    postEvento('update-estado', 'estado=' + encodeURIComponent(val))
      .then(function(data) {
        if (data.success) {
          document.getElementById('os-estado-label').textContent = ({'L':'Locação','P':'Pedido'})[val] || 'Orçamento';
          cancelarEditarEstado();
          showToast('green', 'Salvo', 'Estado atualizado');
        } else { showToast('red', 'Erro', data.error || 'Erro ao salvar'); }
      })
      .catch(function() { showToast('red', 'Erro', 'Erro de comunicação'); });
  };

  // --- Separado por ---
  window.abrirEditarSeparacao = function() {
    document.getElementById('os-separacao-display').style.display = 'none';
    document.getElementById('os-separacao-edit').style.display    = 'flex';
  };
  window.cancelarEditarSeparacao = function() {
    document.getElementById('os-separacao-display').style.display = 'flex';
    document.getElementById('os-separacao-edit').style.display    = 'none';
  };
  window.salvarSeparacao = function() {
    var id = document.getElementById('os-sel-separacao').value;
    postEvento('update-separacao', 'id_usuario_separacao=' + encodeURIComponent(id))
      .then(function(data) {
        if (data.success) {
          var el = document.getElementById('os-separacao-nome');
          el.innerHTML = id && nomesUsuarios[id] ? nomesUsuarios[id] : '<span style="color:var(--text-4)">Não informado</span>';
          cancelarEditarSeparacao();
          showToast('green', 'Salvo', 'Separado por atualizado');
        } else { showToast('red', 'Erro', data.error || 'Erro ao salvar'); }
      })
      .catch(function() { showToast('red', 'Erro', 'Erro de comunicação'); });
  };

  // --- Pedido (OS Cliente) ---
  window.abrirEditarPedido = function() {
    document.getElementById('os-pedido-display').style.display = 'none';
    document.getElementById('os-pedido-edit').style.display    = 'flex';
    document.getElementById('os-input-pedido').focus();
  };
  window.cancelarEditarPedido = function() {
    document.getElementById('os-pedido-display').style.display = 'flex';
    document.getElementById('os-pedido-edit').style.display    = 'none';
  };
  window.salvarPedido = function() {
    var val = document.getElementById('os-input-pedido').value.trim();
    postEvento('update-pedido', 'os_cliente=' + encodeURIComponent(val))
      .then(function(data) {
        if (data.success) {
          var el = document.getElementById('os-pedido-valor');
          el.innerHTML = val ? val : '<span style="color:var(--text-4)">Não informado</span>';
          cancelarEditarPedido();
          showToast('green', 'Salvo', 'Pedido atualizado');
        } else { showToast('red', 'Erro', data.error || 'Erro ao salvar'); }
      })
      .catch(function() { showToast('red', 'Erro', 'Erro de comunicação'); });
  };
})();
</script>

<!-- Formularios de inserção -->
<div class="col2">
  <?php
  echo renderCard([
      'title' => 'Código de Barras Individual',
      'body'  => '<div id="serial-input-wrapper">' .
          '<div class="fg"><div class="fl">Código de Barras do Produto</div>' .
          '<input type="text" class="fi" name="serial" id="serial-codigo-evento" placeholder="Escaneie ou digite" autocomplete="off"/>' .
          '</div><div style="margin-top:16px">' .
          '<button type="button" id="btn-inserir-serial" class="btn btn-cyan" style="width:100%">Inserir Código</button>' .
          '</div></div><div id="serial-info-evento" style="margin-top:12px"></div>'
  ]);
  echo renderCard([
      'title' => 'Múltiplos Códigos (Lote)',
      'body'  => '<form id="form-lote-evento" onsubmit="return false;">' .
          '<div class="fg"><div class="fl">Códigos de Barras (um por linha)</div>' .
          '<textarea id="lote-seriais-evento" class="fi" rows="8" placeholder="Cole aqui os códigos..." style="resize:vertical;font-family:monospace"></textarea>' .
          '</div><div style="margin-top:16px">' .
          '<button type="button" class="btn btn-purple" id="btn-inserir-lote" style="width:100%">Inserir Lote</button>' .
          '</div></form><div id="lote-resultado-evento" style="margin-top:12px"></div>'
  ]);
  ?>
</div>

<!-- Seriais Pendentes -->
<?php
if (empty($pendentes)) {
    $pendentesBody = '<div class="table-empty"><div class="table-empty-flex">' .
        '<svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' .
        '<div>Nenhum código pendente</div>' .
        '<div style="font-size:12px;color:var(--text-4)">Todos os códigos foram encaminhados</div>' .
        '</div></div>';
} else {
    $pendentesRows = [];
    foreach ($pendentes as $p) {
        $salaOptions = '<option value="">Selecione a sala</option>';
        $salasVistas = [];
        foreach ($salasEvento as $s) {
            if (in_array($s['id'], $salasVistas)) continue;
            $salasVistas[] = $s['id'];
            $salaOptions .= '<option value="' . $s['id'] . '">' . htmlspecialchars($s['nome_sala']) . '</option>';
        }
        $salaSelect = '<select class="fi fi-sm sala-destino-select" data-montagem-id="' . $p['id'] . '" style="width:160px;display:inline-block;vertical-align:middle;margin-right:4px">' . $salaOptions . '</select>';
        $itemSelect = '<select class="fi fi-sm item-destino-select" data-montagem-id="' . $p['id'] . '" style="width:160px;display:none;vertical-align:middle;margin-right:4px"><option>Selecione o item</option></select>';
        $btnEnc = renderButton(['label' => 'Encaminhar', 'variant' => 'cyan', 'size' => 'sm', 'extra' => 'style="display:inline-block;vertical-align:middle;margin-left:4px" data-action="encaminhar-para-sala" data-montagem-id="' . $p['id'] . '"']);
        $btnRem = renderButton(['label' => '', 'variant' => 'red', 'size' => 'sm', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>', 'iconPosition' => 'left', 'extra' => 'style="display:inline-block;vertical-align:middle;margin-left:4px" data-action="remover-do-evento" data-montagem-id="' . $p['id'] . '"']);
        $pendentesRows[] = [
            'data-id' => $p['id'],
            ['html' => true, 'content' => htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($p['produto'] ?? '-')))],
            ['html' => true, 'content' => htmlspecialchars($p['serial'] ?? '-')],
            ['html' => true, 'content' => $salaSelect . $itemSelect . $btnEnc . $btnRem],
        ];
    }
    $pendentesBody = renderTable(['id' => 'tbl-pendentes', 'headers' => [['label' => 'Produto'], ['label' => 'Cód. Barras'], ['label' => 'Acao', 'sortable' => false]], 'rows' => $pendentesRows, 'searchable' => false, 'paginated' => false]);
}
echo renderCard(['title' => 'Códigos Pendentes — Aguardando Encaminhamento', 'tag' => '', 'body' => $pendentesBody, 'padding' => '0']);
?>

<!-- Salas com Itens e Seriais -->
<div id="montagem-salas-container" style="margin-top:20px">
  <div style="font-size:14px;font-weight:600;color:var(--text-2);margin-bottom:12px">Listagem de Produtos em Montagem</div>

  <?php if (empty($salasEvento)): ?>
  <div id="card-empty-montagem">
    <?php echo renderCard(['body' => '<div class="table-empty"><div class="table-empty-flex"><svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5m0-5h-2.5M4 18h16"/></svg><div>Nenhuma sala cadastrada</div><div style="font-size:12px;color:var(--text-4)">Adicione salas na aba Salas e Produtos</div></div></div>']); ?>
  </div>
  <?php else: ?>
    <?php foreach ($salasEvento as $sala): ?>
    <?php
    $sid         = $sala['id'];
    $itensEvento = $produtosEventoPorSala[$sid] ?? [];
    $vinculados  = $seraisPorPeId[$sid] ?? [];    // by id_produto_evento
    $orfaos      = $seraisOrfaos[$sid]  ?? [];    // by product name (legacy NULL records)

    // Total de seriais desta sala
    $totalSeriais = 0;
    foreach ($vinculados as $lista) $totalSeriais += count($lista);
    foreach ($orfaos    as $grupo)  $totalSeriais += count($grupo['itens']);
    ?>

    <!-- BLOCO DA SALA -->
    <div class="sala-bloco" data-sala-id="<?= $sid ?>">

      <!-- Header da Sala -->
      <div class="sala-header">
        <div class="sala-header-left">
          <span class="sala-nome"><?= htmlspecialchars($sala['nome_sala']) ?></span>
          <span class="badge sm cyan sala-badge-items"><?= count($itensEvento) ?> iten(s)</span>
        </div>
        <div class="sala-header-right">
          <?php if ($totalSeriais > 0): ?>
          <span class="badge sm green"><?= $totalSeriais ?> código(s)</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Observação de Montagem -->
      <?php if (!empty($sala['orientacoes_montagem'])): ?>
      <div class="sala-obs-row">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span><?= htmlspecialchars($sala['orientacoes_montagem']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Itens da Sala com Seriais -->
      <div class="sala-itens-wrap" data-sala-itens="<?= $sid ?>">
        <?php if (empty($itensEvento)): ?>
        <div style="text-align:center;padding:20px;color:var(--text-4);font-size:12px">
          Nenhum item adicionado nesta sala
        </div>
        <?php else: ?>
          <?php foreach ($itensEvento as $pe): ?>
          <?php
          $peId        = (int)$pe['id'];
          $peNome      = preg_replace('/\s*-\s*\d+$/', '', (string)($pe['produto'] ?? ''));
          $seriaisItem = $vinculados[$peId] ?? [];
          $qtdSerial   = count($seriaisItem);
          $temSerial   = $qtdSerial > 0;
          $borderColor = $temSerial ? 'var(--neon-cyan)' : 'var(--bg-border)';
          ?>
          <!-- Item Card -->
          <div class="sala-item sala-item-card" data-produto-evento-id="<?= $peId ?>"
               style="margin:0 12px 12px;padding:12px;border-radius:8px;background:var(--bg-surface);border:1px solid var(--bg-border-sub);border-left:4px solid <?= $borderColor ?>;transition:border-color .2s">

            <!-- Cabeçalho do Item -->
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;<?= $temSerial ? 'margin-bottom:12px' : '' ?>">
              <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:13px;color:var(--text-1);line-height:1.3"><?= htmlspecialchars($peNome) ?></div>
              </div>
              <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                <span class="item-serial-badge" style="font-size:11px;padding:4px 10px;background:<?= $temSerial ? 'rgba(8,145,178,0.15)' : 'var(--bg-secondary)' ?>;border-radius:4px;color:<?= $temSerial ? 'var(--neon-cyan)' : 'var(--text-4)' ?>;font-weight:600;white-space:nowrap">
                  <?= $qtdSerial ?> código(s)
                </span>
                <button type="button" class="btn btn-xs btn-purple"
                        data-action="abrir-modal-sublocacao" data-produto-evento-id="<?= $peId ?>" data-produto-nome="<?= htmlspecialchars($peNome, ENT_QUOTES, 'UTF-8') ?>">
                  Sublocar
                </button>
              </div>
            </div>

            <!-- Lista de Seriais -->
            <div class="item-seriais-list" style="display:<?= $temSerial ? 'flex' : 'none' ?>;flex-direction:column;gap:4px">
              <?php foreach ($seriaisItem as $si): ?>
              <div class="serial-row" data-montagem-id="<?= (int)$si['id'] ?>"
                   style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid rgba(0,0,0,0.05)">
                <div style="flex:1;display:flex;align-items:center;gap:8px">
                  <span style="width:5px;height:5px;background:var(--neon-cyan);border-radius:50%;flex-shrink:0"></span>
                  <span style="font-size:11px;color:var(--text-3);white-space:nowrap"><?= htmlspecialchars($si['produto'] ?? '') ?></span>
                  <span style="font-weight:600;font-size:13px;color:var(--text-1);font-family:monospace">- <?= htmlspecialchars($si['serial']) ?></span>
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0">
                  <button type="button" class="btn btn-xs btn-yellow"
                          data-action="remover-serial-do-item" data-montagem-id="<?= (int)$si['id'] ?>"
                          title="Voltar para pendentes">Pendente</button>
                  <button type="button" class="btn btn-xs btn-red"
                          data-action="remover-serial-do-evento" data-montagem-id="<?= (int)$si['id'] ?>"
                          title="Remover do evento">✕</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

          </div>
          <!-- /Item Card -->
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($orfaos)): ?>
          <?php foreach ($orfaos as $nomeKey => $grupo):
            $nomeOrfao  = $grupo['nome'];
            $itensOrfao = $grupo['itens'];
            $qtdOrfao   = count($itensOrfao);
          ?>
          <!-- Item Card Órfão (serial sem vínculo a produtos_evento) -->
          <div class="sala-item sala-item-card" data-produto-evento-id="0"
               style="margin:0 12px 12px;padding:12px;border-radius:8px;background:var(--bg-surface);border:1px solid var(--bg-border-sub);border-left:4px solid var(--neon-cyan);transition:border-color .2s">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px">
              <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:13px;color:var(--text-1);line-height:1.3"><?= htmlspecialchars($nomeOrfao) ?></div>
              </div>
              <span class="item-serial-badge" style="font-size:11px;padding:4px 10px;background:rgba(8,145,178,0.15);border-radius:4px;color:var(--neon-cyan);font-weight:600;white-space:nowrap">
                <?= $qtdOrfao ?> serial(is)
              </span>
            </div>
            <div class="item-seriais-list" style="display:flex;flex-direction:column;gap:4px">
              <?php foreach ($itensOrfao as $si): ?>
              <div class="serial-row" data-montagem-id="<?= (int)$si['id'] ?>"
                   style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid rgba(0,0,0,0.05)">
                <div style="flex:1;display:flex;align-items:center;gap:8px">
                  <span style="width:5px;height:5px;background:var(--neon-cyan);border-radius:50%;flex-shrink:0"></span>
                  <span style="font-size:11px;color:var(--text-3);white-space:nowrap"><?= htmlspecialchars($si['produto'] ?? '') ?></span>
                  <span style="font-weight:600;font-size:13px;color:var(--text-1);font-family:monospace">- <?= htmlspecialchars($si['serial']) ?></span>
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0">
                  <button type="button" class="btn btn-xs btn-yellow"
                          data-action="remover-serial-do-item" data-montagem-id="<?= (int)$si['id'] ?>"
                          title="Voltar para pendentes">Pendente</button>
                  <button type="button" class="btn btn-xs btn-red"
                          data-action="remover-serial-do-evento" data-montagem-id="<?= (int)$si['id'] ?>"
                          title="Remover do evento">✕</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- /Item Card Órfão -->
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <!-- /Itens da Sala -->

    </div>
    <!-- /BLOCO DA SALA -->
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</div>

<!-- Modal Sublocacao -->
<div class="modal-overlay" id="modal-sublocacao" style="display:none" data-action="close-on-backdrop" data-target="modal-sublocacao">
    <div class="modal" style="max-width:960px;width:100%;max-height:90vh;display:flex;flex-direction:column">
        <div class="modal-header">
            <div class="modal-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div style="flex:1">
                <div class="modal-title">Sublocação</div>
                <div class="modal-sub" id="modal-sublocacao-item-nome">Selecione um item</div>
            </div>
            <button class="modal-close" data-action="fechar-modal-sublocacao">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-body" style="overflow-y:auto;flex:1">
            <input type="hidden" id="sublocacao_item_id" value="">

            <!-- Fornecedor + Produto -->
            <div class="subloc-col2">
                <div class="fg">
                    <div class="fl">Sublocador *</div>
                    <select id="sublocacao-fornecedor" class="fi">
                        <option value="">Selecione um sublocador...</option>
                    </select>
                </div>
                <div class="fg">
                    <div class="fl">Produto do Sublocador</div>
                    <input type="text" id="sublocacao-produto-fornecedor" class="fi" placeholder="Nome do produto no fornecedor">
                </div>
            </div>

            <!-- Precos -->
            <div class="subloc-col2" style="margin-top:12px">
                <div class="fg">
                    <div class="fl">Custo Unitário (R$)</div>
                    <input type="text" id="sublocacao-custo-unit" class="fi" placeholder="0,00" inputmode="numeric" oninput="subloc_mascaraMoeda(this)">
                </div>
                <div></div>
            </div>

            <!-- Area de Seriais -->
            <div style="margin-top:20px;padding:16px;background:var(--bg-2);border-radius:10px;border:1px solid var(--bg-border)">
                <div style="font-size:13px;font-weight:700;margin-bottom:14px;color:var(--text-1)">Inserir Seriais</div>
                <div class="subloc-col2">
                    <div>
                        <div class="fl" style="margin-bottom:6px">Serial individual</div>
                        <div style="display:flex;gap:8px">
                            <input type="text" id="subloc-serial-input" class="fi"
                                   placeholder="Digite o serial e pressione Enter"
                                   style="flex:1;font-family:monospace"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();subloc_adicionarSerial();}">
                            <button type="button" class="btn btn-sm btn-purple" data-action="subloc-adicionar-serial">+ Adicionar</button>
                        </div>
                    </div>
                    <div>
                        <div class="fl" style="margin-bottom:6px">Múltiplos seriais (um por linha)</div>
                        <div style="display:flex;gap:8px;align-items:flex-start">
                            <textarea id="subloc-textarea" class="fi" rows="3"
                                      style="flex:1;resize:vertical;font-family:monospace;font-size:12px"
                                      placeholder="SERIAL001&#10;SERIAL002&#10;SERIAL003"></textarea>
                            <button type="button" class="btn btn-sm btn-gray" style="white-space:nowrap" data-action="subloc-importar-textarea">Importar</button>
                        </div>
                    </div>
                </div>

                <!-- Chips -->
                <div id="subloc-chips" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;min-height:34px;padding:6px 4px">
                    <span style="color:var(--text-4);font-size:12px;line-height:22px;padding:0 4px">Nenhum serial adicionado</span>
                </div>

                <!-- Sumario -->
                <div style="margin-top:12px;padding:10px 14px;background:rgba(139,92,246,0.08);border-radius:8px;border:1px solid rgba(139,92,246,0.2);display:flex;gap:24px;font-size:13px;flex-wrap:wrap">
                    <span><strong id="subloc-count">0</strong> serial(is) a vincular</span>
                    <span>Custo Total: <strong id="subloc-custo-total">R$ 0,00</strong></span>
                </div>
            </div>

            <!-- Vinculos existentes -->
            <div style="margin-top:20px">
                <div style="font-size:13px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px">
                    Seriais já vinculados
                    <span class="badge sm purple" id="sublocacao-badge-count">0</span>
                </div>
                <div id="sublocacao-lista-vinculos" style="max-height:260px;overflow-y:auto;border:1px solid var(--bg-border);border-radius:8px">
                    <div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">Carregando...</div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-gray" data-action="fechar-modal-sublocacao">Fechar</button>
            <button type="button" class="btn btn-purple" id="btn-vincular-lote" data-action="subloc-vincular-lote">
                Vincular <span id="btn-lote-count">0</span> Serial(is)
            </button>
        </div>
    </div>
</div>

<style>
.subloc-col2 { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.subloc-chip {
    display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
    background:rgba(139,92,246,0.15);border:1px solid rgba(139,92,246,0.3);
    border-radius:20px;font-size:12px;font-family:monospace;color:var(--text-1);font-weight:600;
}
.subloc-chip button { background:none;border:none;cursor:pointer;color:var(--text-3);padding:0 2px;line-height:1;font-size:15px;display:flex;align-items:center; }
.subloc-chip button:hover { color:var(--red-500,#ef4444); }
@media (max-width:768px) { .subloc-col2 { grid-template-columns:1fr; } }
</style>

<script>
// =====================================================
// SUBLOCACAO — Modal + injecao nos cards de item (Montar OS)
// =====================================================
var _sublocacaoItemId = null;
var _sublocacaoFornecedoresCache = null;
var _subloc_seriais = [];

// ---- Injecao nos cards de item ----

function carregarSublocacoesEvento() {
    fetch(BASE_URL + '/api/sublocacao/listar/' + EVENTO_ID, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function(r) {
        return r.text().then(function(text) {
            if (!r.ok) {
                console.error('[subloc] list-evento HTTP ' + r.status, text.slice(0, 300));
                return null;
            }
            try {
                return JSON.parse(text);
            } catch (parseErr) {
                // Resposta nao era JSON valido (ex.: pagina de erro/login em vez do
                // endpoint esperado) -- loga o inicio do corpo pra diagnostico em vez
                // de so estourar SyntaxError sem contexto.
                console.error('[subloc] list-evento resposta nao-JSON:', text.slice(0, 300));
                return null;
            }
        });
    })
    .then(function(data) {
        if (data && data.success) subloc_injetarSeriaisNoOS(data.data);
    })
    .catch(function(e) { console.error('[subloc] list-evento erro', e); });
}

function subloc_injetarSeriaisNoOS(vinculos) {
    document.querySelectorAll('.serial-row[data-sublocacao-id]').forEach(function(r) { r.remove(); });
    if (!vinculos || !vinculos.length) return;

    var porItem = {};
    vinculos.forEach(function(v) {
        if (v.status === 'cancelado') return;
        if (!porItem[v.id_produto_evento]) porItem[v.id_produto_evento] = [];
        porItem[v.id_produto_evento].push(v);
    });

    Object.keys(porItem).forEach(function(peId) {
        var card = document.querySelector('.sala-item-card[data-produto-evento-id="' + peId + '"]');
        if (!card) return;
        var list = card.querySelector('.item-seriais-list');
        if (!list) return;
        list.style.display = 'flex';

        porItem[peId].forEach(function(v) {
            var row = document.createElement('div');
            row.className = 'serial-row';
            row.setAttribute('data-sublocacao-id', v.id);
            row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid rgba(0,0,0,0.05)';
            row.innerHTML =
                '<div style="flex:1;display:flex;align-items:center;gap:8px">' +
                '<span style="width:5px;height:5px;background:#a78bfa;border-radius:50%;flex-shrink:0"></span>' +
                '<span style="font-size:11px;color:var(--text-3);white-space:nowrap">' + esc(v.fornecedor_nome || 'Sublocado') + '</span>' +
                (v.produto_fornecedor ? '<span style="font-size:11px;color:var(--text-3);white-space:nowrap">· ' + esc(v.produto_fornecedor) + '</span>' : '') +
                '<span style="font-weight:600;font-size:13px;color:var(--text-1);font-family:monospace">- ' + esc(v.serial_fornecedor || '') + '</span>' +
                '</div>' +
                '<div style="display:flex;gap:4px;flex-shrink:0">' +
                '<button type="button" class="btn btn-xs btn-red" data-action="subloc-remover-vinculo-os-row" data-vinculo-id="' + v.id + '" title="Remover sublocação">✕</button>' +
                '</div>';
            list.appendChild(row);
        });

        var badge = card.querySelector('.item-serial-badge');
        if (badge) badge.textContent = list.querySelectorAll('.serial-row').length + ' código(s)';
    });
}

function subloc_removerVinculoOSRow(id, btn) {
    if (!confirm('Remover este serial de sublocação?')) return;
    btn.disabled = true;
    fetch(BASE_URL + '/api/sublocacao/desvincular/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = btn.closest('[data-sublocacao-id]');
            if (row) {
                var list = row.parentElement;
                var card = list ? list.closest('.sala-item-card') : null;
                row.remove();
                if (card && list) {
                    var badge = card.querySelector('.item-serial-badge');
                    if (badge) badge.textContent = list.querySelectorAll('.serial-row').length + ' código(s)';
                }
            }
            showToast('green', 'Removido', 'Serial removido');
        } else {
            btn.disabled = false;
            showToast('red', 'Erro', data.error || 'Erro ao remover');
        }
    })
    .catch(function() { btn.disabled = false; showToast('red', 'Erro', 'Erro de conexão'); });
}

// ---- Modal ----

function carregarFornecedoresSublocacao(cb) {
    if (_sublocacaoFornecedoresCache) { cb(_sublocacaoFornecedoresCache); return; }
    fetch(BASE_URL + '/api/sublocacao/fornecedores', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        _sublocacaoFornecedoresCache = data.success ? data.data : [];
        cb(_sublocacaoFornecedoresCache);
    })
    .catch(function() { cb([]); });
}

document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (que define window.registerAction) carrega depois deste script no HTML,
    // por isso o registro so pode acontecer apos DOMContentLoaded (garante ordem correta).
    if (window.registerAction) {
        window.registerAction('abrir-modal-sublocacao', function(el) {
            var nome = el.dataset.produtoNome || el.dataset.produto || '';
            abrirModalSublocacao(el.dataset.produtoEventoId, nome);
        });
        // Modal de sublocacao: as funcoes usam prefixo "subloc_" (underscore), mas o
        // dispatcher generico converte data-action pra camelCase puro (sublocXxx) —
        // nunca bateram, por isso nenhum botao do modal de sublocacao funcionava.
        window.registerAction('subloc-adicionar-serial', function() { subloc_adicionarSerial(); });
        window.registerAction('subloc-importar-textarea', function() { subloc_importarTextarea(); });
        window.registerAction('subloc-vincular-lote', function() { subloc_vincularLote(); });
        window.registerAction('subloc-remover-serial-local', function(el) {
            subloc_removerSerialLocal(parseInt(el.dataset.index, 10));
        });
        window.registerAction('subloc-remover-vinculo', function(el) {
            subloc_removerVinculo(el.dataset.vinculoId);
        });
        window.registerAction('subloc-remover-vinculo-os-row', function(el) {
            subloc_removerVinculoOSRow(el.dataset.vinculoId, el);
        });
        // Botao existia desde sempre sem nenhum handler em lugar nenhum -- o
        // backend (POST /api/sublocacao/gerar-contas/{idEvento}) ja existe e
        // funciona, so faltava a chamada do frontend.
        window.registerAction('gerar-contas-sublocacao', function(el) {
            if (!confirm('Gerar contas a pagar para os itens sublocados deste evento?')) return;
            el.disabled = true;
            fetch(BASE_URL + '/api/sublocacao/gerar-contas/' + EVENTO_ID, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                el.disabled = false;
                if (data.success) {
                    showToast('green', 'Sucesso', data.message || 'Contas geradas com sucesso');
                } else {
                    showToast('red', 'Erro', data.message || data.error || 'Erro ao gerar contas');
                }
            })
            .catch(function() {
                el.disabled = false;
                showToast('red', 'Erro', 'Erro ao processar requisicao');
            });
        });
    }
});

function abrirModalSublocacao(itemId, nomeProduto) {
    _sublocacaoItemId = itemId;
    _subloc_seriais   = [];

    // Garante que o modal está no <body> — evita ser ocultado por um pai com display:none (outra tab ativa)
    var modal = document.getElementById('modal-sublocacao');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    document.getElementById('sublocacao_item_id').value           = itemId;
    document.getElementById('modal-sublocacao-item-nome').textContent = nomeProduto;
    document.getElementById('sublocacao-fornecedor').value         = '';
    document.getElementById('sublocacao-produto-fornecedor').value  = '';
    document.getElementById('sublocacao-custo-unit').value         = '';
    document.getElementById('subloc-serial-input').value           = '';
    document.getElementById('subloc-textarea').value               = '';

    subloc_renderizarChips();
    subloc_atualizarSumario();

    carregarFornecedoresSublocacao(function(lista) {
        var sel = document.getElementById('sublocacao-fornecedor');
        sel.innerHTML = '<option value="">Selecione um sublocador...</option>';
        lista.forEach(function(f) {
            var opt = document.createElement('option');
            opt.value = f.id;
            opt.textContent = f.nome_fantasia || f.razao_social || 'Fornecedor #' + f.id;
            sel.appendChild(opt);
        });
    });

    carregarVinculos(itemId);

    if (modal) { modal.style.display = 'flex'; modal.classList.add('open'); }
    setTimeout(function() {
        var inp = document.getElementById('subloc-serial-input');
        if (inp) inp.focus();
    }, 250);
}

function fecharModalSublocacao() {
    var modal = document.getElementById('modal-sublocacao');
    if (modal) { modal.style.display = 'none'; modal.classList.remove('open'); }
    _sublocacaoItemId = null;
    _subloc_seriais   = [];
}

// ---- Gestao de Seriais (formulario do modal) ----

function subloc_adicionarSerial() {
    var input = document.getElementById('subloc-serial-input');
    var val   = input.value.trim();
    if (!val) return;
    if (_subloc_seriais.indexOf(val) >= 0) {
        showToast('yellow', 'Atenção', 'Serial já adicionado: ' + val);
        return;
    }
    _subloc_seriais.push(val);
    input.value = '';
    subloc_renderizarChips();
    subloc_atualizarSumario();
    input.focus();
}

function subloc_removerSerialLocal(idx) {
    _subloc_seriais.splice(idx, 1);
    subloc_renderizarChips();
    subloc_atualizarSumario();
}

function subloc_importarTextarea() {
    var ta    = document.getElementById('subloc-textarea');
    var lines = ta.value.split('\n')
        .map(function(l) { return l.trim(); })
        .filter(function(l) { return l.length > 0; });
    var adicionados = 0;
    lines.forEach(function(serial) {
        if (_subloc_seriais.indexOf(serial) < 0) { _subloc_seriais.push(serial); adicionados++; }
    });
    ta.value = '';
    subloc_renderizarChips();
    subloc_atualizarSumario();
    if (adicionados > 0) showToast('green', 'Importado', adicionados + ' serial(is) adicionado(s)');
    else showToast('yellow', 'Atenção', 'Nenhum serial novo encontrado');
}

function subloc_renderizarChips() {
    var container = document.getElementById('subloc-chips');
    if (!_subloc_seriais.length) {
        container.innerHTML = '<span style="color:var(--text-4);font-size:12px;line-height:22px;padding:0 4px">Nenhum serial adicionado</span>';
        return;
    }
    container.innerHTML = _subloc_seriais.map(function(serial, idx) {
        return '<span class="subloc-chip">' + esc(serial) +
               '<button type="button" data-action="subloc-remover-serial-local" data-index="' + idx + '" title="Remover">×</button></span>';
    }).join('');
}

function subloc_mascaraMoeda(input) {
    var digits = input.value.replace(/\D/g, '');
    var num = (parseInt(digits, 10) || 0) / 100;
    input.value = num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    subloc_atualizarSumario();
}

function subloc_parseMoeda(val) {
    return parseFloat((val || '0').replace(/\./g, '').replace(',', '.')) || 0;
}

function subloc_atualizarSumario() {
    var count = _subloc_seriais.length;
    var custo = subloc_parseMoeda(document.getElementById('sublocacao-custo-unit').value);

    document.getElementById('subloc-count').textContent      = count;
    document.getElementById('subloc-custo-total').textContent = 'R$ ' + fmtMoney(custo * count);
    document.getElementById('btn-lote-count').textContent     = count;
}

// ---- Vincular Lote ----

function subloc_vincularLote() {
    var itemId  = parseInt(document.getElementById('sublocacao_item_id').value);
    var fornId  = parseInt(document.getElementById('sublocacao-fornecedor').value);
    var produto = document.getElementById('sublocacao-produto-fornecedor').value.trim();
    var custo   = subloc_parseMoeda(document.getElementById('sublocacao-custo-unit').value);

    if (!itemId || !fornId) { showToast('red', 'Erro', 'Selecione um sublocador'); return; }
    if (!_subloc_seriais.length) { showToast('red', 'Erro', 'Adicione pelo menos um serial'); return; }

    var btn = document.getElementById('btn-vincular-lote');
    if (btn) { btn.disabled = true; btn.textContent = 'Vinculando...'; }

    var body = '_csrf_token='        + encodeURIComponent(CSRF_TOKEN) +
               '&id_produto_evento=' + itemId +
               '&id_fornecedor='     + fornId +
               '&produto_fornecedor=' + encodeURIComponent(produto) +
               '&custo_unit='        + custo +
               '&seriais='           + encodeURIComponent(JSON.stringify(_subloc_seriais));

    fetch(BASE_URL + '/api/sublocacao/vincular-lote', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var countRestante = _subloc_seriais.length;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Vincular <span id="btn-lote-count">' + countRestante + '</span> Serial(is)';
        }
        if (data.success) {
            _subloc_seriais = [];
            subloc_renderizarChips();
            subloc_atualizarSumario();
            document.getElementById('subloc-serial-input').value = '';
            document.getElementById('subloc-textarea').value     = '';
            fecharModalSublocacao();
            carregarSublocacoesEvento();
            showToast('green', 'Sucesso', data.criados + ' serial(is) vinculado(s)!');
        } else {
            showToast('red', 'Erro', data.error || 'Erro ao vincular');
        }
    })
    .catch(function(e) {
        console.error('[subloc] vincular-lote erro', e);
        if (btn) { btn.disabled = false; btn.innerHTML = 'Vincular <span id="btn-lote-count">0</span> Serial(is)'; }
        showToast('red', 'Erro', 'Erro de conexão');
    });
}

// ---- Vinculos no Modal (do item selecionado) ----

function carregarVinculos(itemId) {
    var container = document.getElementById('sublocacao-lista-vinculos');
    if (!container) return;
    container.innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-4)">Carregando...</div>';
    fetch(BASE_URL + '/api/sublocacao/list/' + itemId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.data) renderizarVinculos(data.data);
        else container.innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-4);font-size:12px">Nenhum serial vinculado</div>';
    })
    .catch(function() {
        container.innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-4);font-size:12px">Erro ao carregar</div>';
    });
}

function renderizarVinculos(vinculos) {
    var container = document.getElementById('sublocacao-lista-vinculos');
    var badge     = document.getElementById('sublocacao-badge-count');
    if (!container) return;
    if (badge) badge.textContent = vinculos.length;
    if (!vinculos.length) {
        container.innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-4);font-size:12px">Nenhum serial vinculado</div>';
        return;
    }
    var html = '<table style="width:100%;border-collapse:collapse;font-size:12px">';
    html += '<thead><tr style="background:var(--bg-2)">';
    ['Sublocador','Produto','Serial','Custo','Venda','Status',''].forEach(function(h, i) {
        var a = (i === 3 || i === 4) ? 'right' : (i === 5 ? 'center' : 'left');
        html += '<th style="padding:5px 8px;text-align:' + a + ';color:var(--text-3)">' + h + '</th>';
    });
    html += '</tr></thead><tbody>';
    vinculos.forEach(function(v) {
        var sc = v.status === 'pago' ? 'green' : (v.status === 'cancelado' ? 'red' : 'yellow');
        html += '<tr style="border-bottom:1px solid var(--bg-border)">';
        html += '<td style="padding:5px 8px">' + esc(v.fornecedor_nome || '-') + '</td>';
        html += '<td style="padding:5px 8px">' + esc(v.produto_fornecedor || '-') + '</td>';
        html += '<td style="padding:5px 8px;font-family:monospace;font-weight:600">' + esc(v.serial_fornecedor || '-') + '</td>';
        html += '<td style="padding:5px 8px;text-align:right">R$ ' + fmtMoney(v.custo_unit) + '</td>';
        html += '<td style="padding:5px 8px;text-align:right">R$ ' + fmtMoney(v.valor_unit) + '</td>';
        html += '<td style="padding:5px 8px;text-align:center"><span class="badge sm ' + sc + '">' + esc(v.status || 'pendente') + '</span></td>';
        html += '<td style="padding:5px 8px;text-align:right"><button type="button" class="btn btn-xs btn-red" data-action="subloc-remover-vinculo" data-vinculo-id="' + v.id + '">×</button></td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    container.innerHTML = html;
}

function subloc_removerVinculo(id) {
    if (!confirm('Remover este serial?')) return;
    var itemId = parseInt(document.getElementById('sublocacao_item_id').value);
    fetch(BASE_URL + '/api/sublocacao/desvincular/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('green', 'Removido', 'Serial removido');
            if (itemId) carregarVinculos(itemId);
            carregarSublocacoesEvento();
        } else {
            showToast('red', 'Erro', data.error || 'Erro ao remover');
        }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro de conexão'); });
}

// ---- Utilidades ----

function fmtMoney(v) { return (parseFloat(v) || 0).toFixed(2).replace('.', ','); }

function esc(text) {
    var d = document.createElement('div');
    d.textContent = text || '';
    return d.innerHTML;
}

// stubs de compatibilidade com outros partials
function atualizarBadgeSublocacao(itemId) {}
function carregarTodosBadgesSublocacao() {}

document.addEventListener('DOMContentLoaded', function() {
    carregarSublocacoesEvento();
    subloc_atualizarSumario();
});

// Listener: quando devolucao acontece na aba Devolver OS, atualizar lista de montagens
document.addEventListener('devolucao-concluida', function(e) {
    var montagemContainer = document.getElementById('montagem-salas-container');
    if (!montagemContainer) return;
    if (typeof EVENTO_ID === 'undefined' || !EVENTO_ID) return;

    fetch(BASE_URL + '/montagem/listar/' + EVENTO_ID)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !data.data) return;

            var montagens = data.data;

            // Separar pendentes e agrupados por sala
            var pendentes = [];
            var porSala = {};
            montagens.forEach(function(m) {
                if (m.status === 'devolvido') return;
                if (!m.id_sala) {
                    pendentes.push(m);
                } else {
                    if (!porSala[m.id_sala]) porSala[m.id_sala] = [];
                    porSala[m.id_sala].push(m);
                }
            });

            // Reconstruir pendentes
            var pendentesList = document.getElementById('serials-pendentes-list');
            if (pendentesList) {
                if (pendentes.length === 0) {
                    pendentesList.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-4);font-size:13px">Nenhum serial pendente</div>';
                } else {
                    var html = '';
                    pendentes.forEach(function(m) {
                        html += '<div class="sala-item" data-montagem-id="' + m.id + '">' +
                            '<div style="display:flex;align-items:center;justify-content:space-between">' +
                            '<div><strong style="font-size:13px;color:var(--text-1)">' + esc(m.serial || '') + '</strong>' +
                            '<div style="font-size:11px;color:var(--text-3)">' + esc(m.produto || '') + '</div></div>' +
                            '</div></div>';
                    });
                    pendentesList.innerHTML = html;
                }
            }

            // Reconstruir blocos de sala
            var salasContainer = montagemContainer;
            var existingBlocos = salasContainer.querySelectorAll('.sala-bloco');
            existingBlocos.forEach(function(b) { b.remove(); });

            var emptyCard = document.getElementById('card-empty-montagem');

            var salasIds = Object.keys(porSala);
            if (salasIds.length === 0) {
                if (emptyCard) emptyCard.style.display = '';
                return;
            }

            if (emptyCard) emptyCard.style.display = 'none';

            salasIds.forEach(function(salaId) {
                var itens = porSala[salaId];
                var nomeSala = itens[0].sala || 'Sala';
                var bloco = document.createElement('div');
                bloco.className = 'sala-bloco';
                bloco.setAttribute('data-sala-id', salaId);
                bloco.style.cssText = 'background:var(--bg-card);border:1px solid var(--bg-border-sub);border-radius:12px;margin-bottom:16px;overflow:hidden';

                var headerHtml = '<div style="padding:12px 16px;background:var(--bg-surface);border-bottom:1px solid var(--bg-border-sub);display:flex;align-items:center;justify-content:space-between">' +
                    '<div style="font-weight:700;font-size:14px;color:var(--text-1)">' + esc(nomeSala) + '</div>' +
                    '<span class="badge sm cyan">' + itens.length + ' serial(is)</span></div>';

                var bodyHtml = '<div style="padding:12px 16px">';
                itens.forEach(function(m) {
                    bodyHtml += '<div class="sala-item" data-montagem-id="' + m.id + '" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--bg-border-sub)">' +
                        '<div><strong style="font-size:13px;color:var(--text-1)">' + esc(m.serial || '') + '</strong>' +
                        '<div style="font-size:11px;color:var(--text-3)">' + esc(m.produto || '') + '</div></div>' +
                        '</div>';
                });
                bodyHtml += '</div>';

                bloco.innerHTML = headerHtml + bodyHtml;
                salasContainer.appendChild(bloco);
            });
        })
        .catch(function() {});
});
</script>

<?php // Partial: Devolver OS (Desmontagem)
$compBase = dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/';
require_once $compBase . 'card/card.php';
require_once $compBase . 'badge/badge.php';
require_once $compBase . 'button/button.php';
require_once $compBase . 'table/table.php';
require_once $compBase . 'alert/alert.php';
require_once $compBase . 'input/input.php';
require_once $compBase . 'modal/modal.php';

$baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
$eventoId = $evento['id'] ?? 0;
$eventoNome = $evento['nome_evento'] ?? 'Evento';
$csrfToken = \App\Core\Csrf::getToken();

// Dados de devolucao
$devolucaoStats = $devolucaoStats ?? [
    'total_montados' => 0,
    'total_devolvidos' => 0,
    'pendencias_ativas' => 0,
    'total_devolvidos_pendente' => 0,
    'total_devolvidos_solucionado' => 0
];
$devolucoesPendentes = $devolucoesPendentes ?? [];
$devolucoesTodas = $devolucoesTodas ?? [];
?>

<div style="padding:20px">

<!-- Header do Evento -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:16px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:18px;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($eventoNome) ?></div>
        <div style="font-size:12px;color:var(--text-3);margin-top:2px">Devolucao — Desmontagem e retorno de seriais</div>
      </div>
    </div>
  </div>
</div>

<!-- KPIs de Devolucao -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="devolucao-stats-grid">
  <div class="card-stat">
    <div class="card-stat-val"><?= $devolucaoStats['total_montados'] ?></div>
    <div class="card-stat-lbl">Total Montados</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--green)">
    <div class="card-stat-val" style="color:var(--green)"><?= $devolucaoStats['total_devolvidos'] ?></div>
    <div class="card-stat-lbl">Devolvidos OK</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--red)">
    <div class="card-stat-val" style="color:var(--red)"><?= $devolucaoStats['pendencias_ativas'] ?></div>
    <div class="card-stat-lbl">Pendencias Ativas</div>
  </div>
  <div class="card-stat" style="--stat-color:var(--purple)">
    <div class="card-stat-val" style="color:var(--purple)"><?= $devolucaoStats['total_devolvidos_solucionado'] ?></div>
    <div class="card-stat-lbl">Solucionados</div>
  </div>
</div>

<style>
  @media (max-width:1024px){.devolucao-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
  @media (max-width:640px){.devolucao-stats-grid{grid-template-columns:1fr!important}}
</style>

<!-- Dois Formularios Lado a Lado -->
<div class="col2">
  <!-- Form Individual -->
  <?php
  echo renderCard([
      'title' => 'Serial Individual',
      'body' => '<div id="devolucao-serial-input-wrapper">' .
          '<div class="fg">' .
          '<div class="fl">Serial do Produto</div>' .
          '<input type="text" class="fi" name="devolucao_serial" id="devolucao-serial-codigo" placeholder="Escaneie ou digite o serial" autocomplete="off" />' .
          '</div>' .
          '<div class="fg" style="margin-top:12px">' .
          '<div class="fl">Status da Devolucao</div>' .
          '<div style="display:flex;gap:12px;margin-top:6px">' .
          '<label style="display:flex;align-items:center;gap:6px;cursor:pointer">' .
          '<input type="radio" name="devolucao_status" value="A" checked style="accent-color:var(--green)"/> ' .
          '<span style="font-size:13px;color:var(--text-1)">OK (Aprovado)</span>' .
          '</label>' .
          '<label style="display:flex;align-items:center;gap:6px;cursor:pointer">' .
          '<input type="radio" name="devolucao_status" value="P" style="accent-color:var(--red)"/> ' .
          '<span style="font-size:13px;color:var(--text-1)">Pendencia</span>' .
          '</label>' .
          '</div>' .
          '</div>' .
          '<div id="devolucao-motivo-field" style="margin-top:12px;display:none">' .
          '<div class="fg">' .
          '<div class="fl">Motivo da Pendencia</div>' .
          '<textarea class="fi" name="devolucao_motivo" id="devolucao-motivo" rows="3" placeholder="Descreva o motivo da pendencia"></textarea>' .
          '</div>' .
          '</div>' .
          '<div style="margin-top:16px">' .
          '<button type="button" id="btn-devolver-serial" class="btn btn-purple" style="width:100%">Devolver Serial</button>' .
          '</div></div>' .
          '<div id="devolucao-serial-info" style="margin-top:12px"></div>'
  ]);
  ?>

  <!-- Form Lote -->
  <?php
  echo renderCard([
      'title' => 'Devolucao em Lote (Sempre OK)',
      'body' => '<div id="devolucao-lote-wrapper">' .
          '<div class="fg">' .
          '<div class="fl">Seriais (um por linha)</div>' .
          '<textarea class="fi" name="devolucao_seriais" id="devolucao-seriais-lote" rows="6" placeholder="Cole os seriais aqui, um por linha"></textarea>' .
          '</div>' .
          '<div style="margin-top:16px">' .
          '<button type="button" id="btn-devolver-lote" class="btn btn-cyan" style="width:100%">Devolver Todos (OK)</button>' .
          '</div></div>' .
          '<div id="devolucao-lote-info" style="margin-top:12px"></div>'
  ]);
  ?>
</div>

<!-- Lista de Pendencias Ativas -->
<?php if (!empty($devolucoesPendentes)): ?>
<div class="card" style="margin-top:24px">
  <div class="card-head">
    <div class="card-title">Pendencias Ativas — Aguardando Solucao</div>
  </div>
  <div class="card-body">
    <?php
    $pendenciaRows = [];
    foreach ($devolucoesPendentes as $dev) {
        $pendenciaRows[] = [
            'data-id' => $dev['id'],
            ['html' => true, 'content' => '<strong>' . htmlspecialchars($dev['serial'] ?? 'N/A') . '</strong>'],
            ['html' => true, 'content' => htmlspecialchars($dev['nome_produto'] ?? '-')],
            ['html' => true, 'content' => '<strong>' . htmlspecialchars($dev['usuario_devolucao'] ?? 'Sistema') . '</strong>'],
            ['html' => true, 'content' => htmlspecialchars($dev['motivo_pendencia'] ?? '-')],
            ['html' => true, 'content' => '<button type="button" class="btn btn-sm btn-green" data-serial="' . htmlspecialchars($dev['serial'] ?? '') . '" data-produto="' . htmlspecialchars($dev['nome_produto'] ?? '') . '" data-motivo="' . htmlspecialchars($dev['motivo_pendencia'] ?? '') . '" data-action="resolver-pendencia" data-pendencia-id="' . $dev['id'] . '">Resolver</button>'],
        ];
    }
    
    echo renderTable([
        'id' => 'tbl-pendencias-devolucao',
        'searchable' => true,
        'paginated' => false,
        'headers' => [
            ['label' => 'Serial'],
            ['label' => 'Produto'],
            ['label' => 'Usuario'],
            ['label' => 'Motivo'],
            ['label' => 'Acao']
        ],
        'rows' => $pendenciaRows,
    ]);
    ?>
  </div>
</div>
<?php endif; ?>

<!-- Lista de Seriais Devolvidos -->
<div class="card" style="margin-top:24px">
  <div class="card-head">
    <div class="card-title">Seriais Devolvidos</div>
    <span class="badge sm cyan"><?= count($devolucoesTodas) ?> item(s)</span>
  </div>
  <div class="card-body">
    <?php
    if (!empty($devolucoesTodas)) {
        $devolvidosRows = [];
        foreach ($devolucoesTodas as $dev) {
            $statusBadge = match($dev['status']) {
                'A' => '<span class="badge sm green">Aprovado</span>',
                'P' => '<span class="badge sm red">Pendencia</span>',
                'S' => '<span class="badge sm purple">Solucionado</span>',
                default => '<span class="badge sm gray">Desconhecido</span>'
            };
            
        // Detalhes para itens solucionados (auditoria)
        $detalheHtml = '';
        if ($dev['status'] === 'S') {
            $detalheHtml = '<div style="font-size:11px;color:var(--text-3);line-height:1.4">' .
                '<div style="margin-bottom:2px"><span style="color:var(--text-3)">Motivo:</span> ' . htmlspecialchars($dev['motivo_pendencia'] ?? '-') . '</div>' .
                '<div><span style="color:var(--text-3)">Solucao:</span> ' . htmlspecialchars($dev['solucao_pendencia'] ?? '-') . '</div>' .
                '</div>';
        }
        
        $actionButtons = '';
        if ($dev['status'] === 'A') {
            $actionButtons = '<button type="button" class="btn btn-sm btn-yellow" title="Marcar como Pendencia" data-action="alterar-status-devolucao" data-devolucao-id="' . $dev['id'] . '" data-novo-status="P">Pendencia</button>';
        } elseif ($dev['status'] === 'P') {
            $actionButtons = '<button type="button" class="btn btn-sm btn-green" data-action="resolver-pendencia" data-pendencia-id="' . $dev['id'] . '">Resolver</button>';
        }
        
        $devolvidosRows[] = [
            'data-id' => $dev['id'],
            ['html' => true, 'content' => '<strong>' . htmlspecialchars($dev['serial'] ?? 'N/A') . '</strong>'],
            ['html' => true, 'content' => htmlspecialchars($dev['nome_produto'] ?? '-')],
            ['html' => true, 'content' => '<strong>' . htmlspecialchars($dev['usuario_devolucao'] ?? 'Sistema') . '</strong>'],
            ['html' => true, 'content' => $statusBadge],
            ['html' => true, 'content' => $detalheHtml ?: ($actionButtons ?: '-')],
        ];
        }
        
        echo renderTable([
            'id' => 'tbl-devolvidos',
            'searchable' => true,
            'paginated' => true,
            'perPage' => 15,
            'headers' => [
                ['label' => 'Serial'],
                ['label' => 'Produto'],
                ['label' => 'Usuario'],
                ['label' => 'Status'],
                ['label' => 'Detalhes/Acoes']
            ],
            'rows' => $devolvidosRows,
        ]);
    } else {
        echo '<div style="text-align:center;padding:40px;color:var(--text-3)">Nenhum serial devolvido ainda</div>';
    }
    ?>
  </div>
</div>

</div>

<!-- Modal para resolver pendencia -->
<?= renderModal([
    'id' => 'modal-resolver-pendencia',
    'variant' => 'form',
    'title' => 'Resolver Pendencia',
    'body' => '<div id="modal-pendencia-info" style="margin-bottom:16px;padding:12px;background:var(--bg-elevated);border-radius:8px;border-left:3px solid var(--red)">' .
        '<div style="font-size:12px;color:var(--text-3);margin-bottom:4px">Serial:</div>' .
        '<div id="modal-pendencia-serial" style="font-weight:700;font-size:14px;color:var(--text-1)">-</div>' .
        '<div style="font-size:12px;color:var(--text-3);margin-top:8px;margin-bottom:4px">Produto:</div>' .
        '<div id="modal-pendencia-produto" style="font-weight:700;font-size:14px;color:var(--text-1)">-</div>' .
        '<div style="font-size:12px;color:var(--text-3);margin-top:8px;margin-bottom:4px">Motivo:</div>' .
        '<div id="modal-pendencia-motivo" style="font-size:13px;color:var(--text-2);font-style:italic">-</div>' .
        '</div>' .
        '<div class="fg">' .
        '<div class="fl">Solucao da Pendencia</div>' .
        '<textarea class="fi" id="modal-solucao-text" rows="4" placeholder="Descreva a solucao aplicada"></textarea>' .
        '</div>',
    'footer' => '<button type="button" class="btn btn-gray" data-action="close-modal" data-target="modal-resolver-pendencia">Cancelar</button>' .
        '<button type="button" class="btn btn-green" id="btn-confirmar-solucao">Confirmar Solucao</button>'
]) ?>

<!-- Modal para motivo ao marcar pendencia -->
<?= renderModal([
    'id' => 'modal-marcar-pendencia',
    'variant' => 'form',
    'title' => 'Marcar como Pendencia',
    'body' => '<div class="fg">' .
        '<div class="fl">Motivo da Pendencia</div>' .
        '<textarea class="fi" id="modal-motivo-text" rows="4" placeholder="Descreva o motivo da pendencia"></textarea>' .
        '</div>',
    'footer' => '<button type="button" class="btn btn-gray" data-action="close-modal" data-target="modal-marcar-pendencia">Cancelar</button>' .
        '<button type="button" class="btn btn-yellow" id="btn-confirmar-motivo">Confirmar Pendencia</button>'
]) ?>

<script>
(function() {
  'use strict';
  
  var BASE_URL = '<?= $baseUrl ?>';
  var CSRF_TOKEN = '<?= $csrfToken ?>';
  var EVENTO_ID = <?= $eventoId ?>;
  var pendenciaIdAtual = null;
  
  // Inicializacao
  function init() {
    // Toggle campo motivo
    var statusRadios = document.querySelectorAll('input[name="devolucao_status"]');
    statusRadios.forEach(function(radio) {
      radio.addEventListener('change', toggleMotivo);
    });
    
    // Botao devolver serial
    var btnDevolver = document.getElementById('btn-devolver-serial');
    if (btnDevolver) {
      btnDevolver.addEventListener('click', devolverSerialIndividual);
    }
    
    // Botao devolver lote
    var btnLote = document.getElementById('btn-devolver-lote');
    if (btnLote) {
      btnLote.addEventListener('click', devolverLote);
    }
    
    // Botao confirmar solucao no modal
    var btnConfirmar = document.getElementById('btn-confirmar-solucao');
    if (btnConfirmar) {
      btnConfirmar.addEventListener('click', confirmarSolucao);
    }
    
    // Botao confirmar motivo no modal
    var btnMotivo = document.getElementById('btn-confirmar-motivo');
    if (btnMotivo) {
      btnMotivo.addEventListener('click', confirmarMotivo);
    }
  }
  
  // Toggle campo motivo
  function toggleMotivo() {
    var status = document.querySelector('input[name="devolucao_status"]:checked');
    var motivoField = document.getElementById('devolucao-motivo-field');
    if (motivoField) {
      motivoField.style.display = (status && status.value === 'P') ? 'block' : 'none';
    }
  }
  
  // Atualizar KPIs e listas sem reload
  function atualizarStats() {
    // Buscar stats atualizados
    fetch(BASE_URL + '/devolucao/stats/' + EVENTO_ID)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success && data.data) {
        var stats = data.data;
        // Atualizar cards KPI
        var devolverPane = document.getElementById('loc-hpane-2');
        var statValues = devolverPane ? devolverPane.querySelectorAll('.card-stat-val') : [];
        if (statValues.length >= 4) {
          statValues[0].textContent = stats.total_montados || '0';
          statValues[1].textContent = stats.total_devolvidos || '0';
          statValues[2].textContent = stats.pendencias_ativas || '0';
          statValues[3].textContent = stats.total_devolvidos_solucionado || '0';
        }
      }
    })
    .catch(function() {
      console.error('Erro ao atualizar stats');
    });
    
    // Buscar lista de pendencias atualizada
    fetch(BASE_URL + '/devolucao/listar/' + EVENTO_ID)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success && data.data) {
        atualizarTabelas(data.data);
      }
    })
    .catch(function() {
      console.error('Erro ao atualizar listas');
    });
  }
  
  // Atualizar tabelas com dados atualizados
  function atualizarTabelas(devolucoes) {
    var pendentes = devolucoes.filter(function(d) { return d.status === 'P'; });
    var todas = devolucoes;
    
    // Atualizar tabela de pendencias se existir
    var tblPendencias = document.getElementById('tbl-pendencias-devolucao');
    if (tblPendencias) {
      if (pendentes.length === 0) {
        tblPendencias.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3)">Nenhuma pendencia ativa</div>';
      } else {
        // Recriar rows da tabela
        var tbody = tblPendencias.querySelector('tbody');
        if (tbody) {
          tbody.innerHTML = '';
          pendentes.forEach(function(dev) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-id', dev.id);
            var serialHtml = '<strong>' + escapeHtml(dev.serial || 'N/A') + '</strong>';
            var produtoHtml = escapeHtml(dev.nome_produto || '-');
            var usuarioHtml = '<strong>' + escapeHtml(dev.usuario_devolucao || 'Sistema') + '</strong>';
            var motivoHtml = escapeHtml(dev.motivo_pendencia || '-');
            var btnHtml = '<button type="button" class="btn btn-sm btn-green" ' +
              'data-serial="' + escapeHtml(dev.serial || '') + '" ' +
              'data-produto="' + escapeHtml(dev.nome_produto || '') + '" ' +
              'data-motivo="' + escapeHtml(dev.motivo_pendencia || '') + '" ' +
              'data-action="resolver-pendencia" data-pendencia-id="' + dev.id + '">Resolver</button>';
            tr.innerHTML = '<td>' + serialHtml + '</td>' +
              '<td>' + produtoHtml + '</td>' +
              '<td>' + usuarioHtml + '</td>' +
              '<td>' + motivoHtml + '</td>' +
              '<td>' + btnHtml + '</td>';
            tbody.appendChild(tr);
          });
        }
      }
    }
    
    // Atualizar tabela de Seriais Devolvidos
    var tblDevolvidos = document.getElementById('tbl-devolvidos');
    if (tblDevolvidos) {
      var tbodyDevolvidos = tblDevolvidos.querySelector('tbody');
      if (tbodyDevolvidos) {
        tbodyDevolvidos.innerHTML = '';
        
        if (todas.length === 0) {
          // Mostrar mensagem de vazio
          var cardBody = tblDevolvidos.closest('.card-body');
          if (cardBody) {
            tbodyDevolvidos.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-3)">Nenhum serial devolvido ainda</td></tr>';
          }
        } else {
          // Recriar todas as rows
          todas.forEach(function(dev) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-id', dev.id);
            
            // Badge de status
            var statusBadge = '';
            if (dev.status === 'A') {
              statusBadge = '<span class="badge sm green">Aprovado</span>';
            } else if (dev.status === 'P') {
              statusBadge = '<span class="badge sm red">Pendencia</span>';
            } else if (dev.status === 'S') {
              statusBadge = '<span class="badge sm purple">Solucionado</span>';
            } else {
              statusBadge = '<span class="badge sm gray">Desconhecido</span>';
            }
            
            // Detalhes para itens solucionados (auditoria)
            var detalhesHtml = '';
            if (dev.status === 'S') {
              detalhesHtml = '<div style="font-size:11px;color:var(--text-3);line-height:1.4">' +
                '<div style="margin-bottom:2px"><span style="color:var(--text-3)">Motivo:</span> ' + escapeHtml(dev.motivo_pendencia || '-') + '</div>' +
                '<div><span style="color:var(--text-3)">Solucao:</span> ' + escapeHtml(dev.solucao_pendencia || '-') + '</div>' +
                '</div>';
            }

            // Botoes de acao
            var actionButtons = '';
            if (dev.status === 'A') {
              actionButtons = '<button type="button" class="btn btn-sm btn-yellow" title="Marcar como Pendencia" data-action="alterar-status-devolucao" data-devolucao-id="' + dev.id + '" data-novo-status="P">Pendencia</button>';
            } else if (dev.status === 'P') {
              actionButtons = '<button type="button" class="btn btn-sm btn-green" ' +
                'data-serial="' + escapeHtml(dev.serial || '') + '" ' +
                'data-produto="' + escapeHtml(dev.nome_produto || '') + '" ' +
                'data-motivo="' + escapeHtml(dev.motivo_pendencia || '') + '" ' +
                'data-action="resolver-pendencia" data-pendencia-id="' + dev.id + '">Resolver</button>';
            }
            
            var serialHtml = '<strong>' + escapeHtml(dev.serial || 'N/A') + '</strong>';
            var produtoHtml = escapeHtml(dev.nome_produto || '-');
            var usuarioHtml = '<strong>' + escapeHtml(dev.usuario_devolucao || 'Sistema') + '</strong>';
            
            tr.innerHTML = '<td>' + serialHtml + '</td>' +
              '<td>' + produtoHtml + '</td>' +
              '<td>' + usuarioHtml + '</td>' +
              '<td>' + statusBadge + '</td>' +
              '<td>' + (detalhesHtml || actionButtons || '-') + '</td>';
            tbodyDevolvidos.appendChild(tr);
          });
        }
      }
      
      // Atualizar badge de contagem no card-head
      var badge = tblDevolvidos.querySelector('.card-head .badge');
      if (badge) {
        badge.textContent = todas.length + ' item(s)';
      }
    }
  }
  
  // Devolver serial individual
  function devolverSerialIndividual() {
    var serialInput = document.getElementById('devolucao-serial-codigo');
    var statusRadio = document.querySelector('input[name="devolucao_status"]:checked');
    var motivoInput = document.getElementById('devolucao-motivo');
    var infoDiv = document.getElementById('devolucao-serial-info');
    var btnDevolver = document.getElementById('btn-devolver-serial');
    
    var serial = serialInput ? serialInput.value.trim() : '';
    var status = statusRadio ? statusRadio.value : 'A';
    var motivo = motivoInput ? motivoInput.value.trim() : '';
    
    if (!serial) {
      showToast('red', 'Erro', 'Serial e obrigatorio');
      return;
    }
    
    if (status === 'P' && !motivo) {
      showToast('red', 'Erro', 'Motivo da pendencia e obrigatorio');
      return;
    }
    
    // Desabilitar botao
    if (btnDevolver) {
      btnDevolver.disabled = true;
      btnDevolver.textContent = 'Processando...';
    }
    
    var body = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN) +
      '&id_evento=' + EVENTO_ID +
      '&serial=' + encodeURIComponent(serial) +
      '&status=' + status;
    
    if (motivo) body += '&motivo=' + encodeURIComponent(motivo);
    
    fetch(BASE_URL + '/devolucao/processar-unico', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial devolvido com sucesso');
        // Limpar formulário
        if (serialInput) {
          serialInput.value = '';
          serialInput.focus();
        }
        if (motivoInput) motivoInput.value = '';
        document.querySelector('input[name="devolucao_status"][value="A"]').checked = true;
        toggleMotivo();
        // Atualizar KPIs e listas sem reload
        atualizarStats();
        document.dispatchEvent(new CustomEvent('devolucao-concluida', { detail: { eventoId: EVENTO_ID } }));
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao devolver serial');
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao processar requisicao');
    })
    .finally(function() {
      // Reabilitar botao
      if (btnDevolver) {
        btnDevolver.disabled = false;
        btnDevolver.textContent = 'Devolver Serial';
      }
    });
  }
  
  // Devolver lote
  function devolverLote() {
    var seriaisInput = document.getElementById('devolucao-seriais-lote');
    var infoDiv = document.getElementById('devolucao-lote-info');
    var btnLote = document.getElementById('btn-devolver-lote');
    
    var seriais = seriaisInput ? seriaisInput.value.trim() : '';
    if (!seriais) {
      showToast('red', 'Erro', 'Informe pelo menos um serial');
      return;
    }
    
    // Desabilitar botao
    if (btnLote) {
      btnLote.disabled = true;
      btnLote.textContent = 'Processando...';
    }
    
    var body = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN) +
      '&id_evento=' + EVENTO_ID +
      '&seriais=' + encodeURIComponent(seriais);
    
    fetch(BASE_URL + '/devolucao/processar-lote', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success !== false) {
        var sucesso = data.sucesso || [];
        var erros = data.erros || {};
        
        if (sucesso.length > 0) {
          showToast('green', 'Sucesso', sucesso.length + ' serial(is) devolvido(s) com sucesso');
        }
        
        if (Object.keys(erros).length > 0) {
          var errosHtml = '<div style="margin-top:8px;padding:8px;background:var(--bg-elevated);border:1px solid var(--red);border-radius:6px">';
          errosHtml += '<div style="font-weight:700;font-size:12px;color:var(--red)">Erros:</div>';
          Object.keys(erros).forEach(function(serial) {
            errosHtml += '<div style="font-size:11px;color:var(--text-3);margin-top:2px">' + serial + ': ' + erros[serial] + '</div>';
          });
          errosHtml += '</div>';
          if (infoDiv) infoDiv.innerHTML = errosHtml;
          if (sucesso.length === 0) {
            showToast('red', 'Erro', 'Nenhum serial foi devolvido');
          }
        } else {
          if (infoDiv) infoDiv.innerHTML = '';
        }
        
        // Limpar formulário
        if (seriaisInput) {
          seriaisInput.value = '';
          seriaisInput.focus();
        }
        // Atualizar KPIs e listas sem reload
        atualizarStats();
        document.dispatchEvent(new CustomEvent('devolucao-concluida', { detail: { eventoId: EVENTO_ID } }));
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao devolver lote');
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao processar requisicao');
    })
    .finally(function() {
      // Reabilitar botao
      if (btnLote) {
        btnLote.disabled = false;
        btnLote.textContent = 'Devolver Todos (OK)';
      }
    });
  }
  
  // Resolver pendencia
  window.resolverPendencia = function(id, btn) {
    pendenciaIdAtual = id;
    
    // Usar data-attributes do botao (mais confiavel)
    var serial = btn.getAttribute('data-serial') || '';
    var produto = btn.getAttribute('data-produto') || '';
    var motivo = btn.getAttribute('data-motivo') || '';
    
    // Se nao tiver data-attributes, tentar pegar da linha da tabela
    if (!serial) {
      var row = btn.closest('tr');
      if (row) {
        var cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
          serial = cells[0].textContent.trim();
          produto = cells[1].textContent.trim();
          motivo = cells[3].textContent.trim();
        }
      }
    }
    
    // Popular modal com dados da pendencia
    document.getElementById('modal-pendencia-serial').textContent = serial || 'N/A';
    document.getElementById('modal-pendencia-produto').textContent = produto || '-';
    document.getElementById('modal-pendencia-motivo').innerHTML = 
      '<div style="font-size:13px;color:var(--text-2)">' + escapeHtml(motivo || '-') + '</div>' +
      '<div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--bg-border-sub);font-size:11px;color:var(--text-3)">Preencha a solucao abaixo:</div>';
    
    document.getElementById('modal-solucao-text').value = '';
    document.getElementById('modal-solucao-text').focus();
    openModal('modal-resolver-pendencia');
  };
  
  function confirmarSolucao() {
    var solucao = document.getElementById('modal-solucao-text').value.trim();
    var btnConfirmar = document.getElementById('btn-confirmar-solucao');
    
    if (!solucao) {
      showToast('red', 'Erro', 'Informe a solucao aplicada');
      return;
    }
    
    if (btnConfirmar) {
      btnConfirmar.disabled = true;
      btnConfirmar.textContent = 'Processando...';
    }
    
    var body = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN) +
      '&solucao=' + encodeURIComponent(solucao);
    
    fetch(BASE_URL + '/devolucao/resolver-pendencia/' + pendenciaIdAtual, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Pendencia resolvida com sucesso');
        
        // Atualizar modal para mostrar motivo + solucao (auditoria completa)
        var motivoOriginal = document.getElementById('modal-pendencia-motivo').textContent || '';
        document.getElementById('modal-pendencia-motivo').innerHTML = 
          '<div style="margin-bottom:6px"><span style="color:var(--text-3)">Motivo:</span> ' + escapeHtml(motivoOriginal) + '</div>' +
          '<div style="color:var(--green);font-weight:600">Solucao: ' + escapeHtml(solucao) + '</div>';
        
        // Fechar modal apos 1.5s
        setTimeout(function() {
          closeModal('modal-resolver-pendencia');
          atualizarStats();
        document.dispatchEvent(new CustomEvent('devolucao-concluida', { detail: { eventoId: EVENTO_ID } }));
        }, 1500);
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao resolver pendencia');
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao processar requisicao');
    })
    .finally(function() {
      if (btnConfirmar) {
        btnConfirmar.disabled = false;
        btnConfirmar.textContent = 'Confirmar Solucao';
      }
    });
  }
  
  // Alterar status na listagem
  window.alterarStatusDevolucao = function(id, novoStatus, btn) {
    if (novoStatus === 'P') {
      pendenciaIdAtual = id;
      document.getElementById('modal-motivo-text').value = '';
      openModal('modal-marcar-pendencia');
    }
  };

  document.addEventListener('DOMContentLoaded', function() {
    // scripts.js (window.registerAction) carrega depois deste script no HTML;
    // registrar so apos DOMContentLoaded garante que ja esta disponivel.
    if (window.registerAction) {
      window.registerAction('alterar-status-devolucao', function(el) {
        window.alterarStatusDevolucao(el.dataset.devolucaoId, el.dataset.novoStatus, el);
      });
      window.registerAction('resolver-pendencia', function(el) {
        window.resolverPendencia(el.dataset.pendenciaId, el);
      });
    }
  });
  
  function confirmarMotivo() {
    var motivo = document.getElementById('modal-motivo-text').value.trim();
    var btnMotivo = document.getElementById('btn-confirmar-motivo');
    
    if (!motivo) {
      showToast('red', 'Erro', 'Informe o motivo da pendencia');
      return;
    }
    
    if (btnMotivo) {
      btnMotivo.disabled = true;
      btnMotivo.textContent = 'Processando...';
    }
    
    var body = '_csrf_token=' + encodeURIComponent(CSRF_TOKEN) +
      '&status=P' +
      '&motivo=' + encodeURIComponent(motivo);
    
    fetch(BASE_URL + '/devolucao/alterar-status/' + pendenciaIdAtual, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Status alterado para pendencia');
        closeModal('modal-marcar-pendencia');
        atualizarStats();
        document.dispatchEvent(new CustomEvent('devolucao-concluida', { detail: { eventoId: EVENTO_ID } }));
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao alterar status');
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao processar requisicao');
    })
    .finally(function() {
      if (btnMotivo) {
        btnMotivo.disabled = false;
        btnMotivo.textContent = 'Confirmar Pendencia';
      }
    });
  }
  
  // Toast function fallback
  if (typeof showToast !== 'function') {
    window.showToast = function(type, title, msg) {
      alert(title + ': ' + msg);
    };
  }
  
  // Init quando DOM pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>

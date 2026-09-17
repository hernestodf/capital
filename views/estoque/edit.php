<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/card/card.php';

$secaoOptions = '';
if (!empty($secoes)) {
    foreach ($secoes as $sec) {
        $selected = ($sec['id'] == ($produto['id_secao'] ?? '')) ? 'selected' : '';
        $secaoOptions .= '<option value="' . $sec['id'] . '" ' . $selected . '>' . htmlspecialchars($sec['secao']) . '</option>';
    }
}

$locadoSelected = ($produto['pode_ser_locado'] ?? 'N') === 'S' ? 'S' : 'N';
?>

    <style>
    @media (max-width: 768px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 480px) {
      .stats-grid { grid-template-columns: 1fr !important; }
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    </style>

    <section class="section active" id="sec-estoque-edit">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Produto</div>
          <div class="section-sub">Atualizar dados do produto e gerenciar codigos de barras</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (isset($_GET['error'])): ?>
      <?= renderAlert(['variant' => 'red', 'title' => 'Erro', 'msg' => htmlspecialchars(urldecode($_GET['error']))]) ?>
      <?php endif; ?>

      <!-- RESUMO DOS SERIAIS - LINHA COMPLETA -->
      <div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>', 'value' => (string)($seriais_count['total'] ?? 0), 'label' => 'Total', 'color' => 'var(--neon-cyan)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'value' => (string)($seriais_count['ativos'] ?? 0), 'label' => 'Ativos', 'color' => 'var(--neon-green)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>', 'value' => (string)($seriais_count['manutencao'] ?? 0), 'label' => 'Manutencao', 'color' => 'var(--neon-yellow)']); ?>
        <?php echo renderCardStat(['icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'value' => (string)($seriais_count['vender'] ?? 0), 'label' => 'Vender', 'color' => 'var(--neon-purple)']); ?>
      </div>

      <div style="display:flex;gap:12px;justify-content:center;margin-bottom:24px">
        <button type="button" class="btn btn-cyan" data-action="abrir-modal-serial">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Novo Código de Barras
        </button>
        <button type="button" class="btn btn-green" data-action="abrir-modal-lote">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
          Adicionar em Lote
        </button>
      </div>

      <!-- DADOS DO PRODUTO | SERIAIS DO PRODUTO -->
      <div class="col2">
        <!-- FORMULARIO DO PRODUTO -->
        <div class="card">
          <div class="card-head">
            <span class="card-title">Dados do Produto</span>
          </div>
          <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/estoque/update/<?= $produto['id'] ?>" data-ajax data-redirect="<?= $baseUrl ?>/estoque">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
              
              <div class="fg">
                  <div class="fl">Nome do Produto</div>
                  <input type="text" name="produto" class="fi" value="<?= htmlspecialchars($produto['produto'] ?? '') ?>" required/>
                </div>

              <div class="col2">
                <div class="fg">
                  <div class="fl">Custo (R$)</div>
                  <input type="text" id="custo-input" name="custo" class="fi" value="<?= !empty($produto['custo']) ? number_format($produto['custo'], 2, ',', '.') : '0,00' ?>"/>
                </div>
                <div class="fg">
                  <div class="fl">Secao</div>
                  <select name="id_secao" class="fi">
                    <option value="">Selecione uma secao</option>
                    <?= $secaoOptions ?>
                  </select>
                </div>
              </div>

              <div class="fg">
                <div class="fl">Pode ser Locado?</div>
                <select name="pode_ser_locado" class="fi">
                  <option value="S" <?= $locadoSelected === 'S' ? 'selected' : '' ?>>Sim</option>
                  <option value="N" <?= $locadoSelected === 'N' ? 'selected' : '' ?>>Nao</option>
                </select>
              </div>

              <div class="fg">
                <div class="fl">Observacao</div>
                <textarea name="observacao" class="fi" rows="3"><?= htmlspecialchars($produto['observacao'] ?? '') ?></textarea>
              </div>

              <div class="divider" style="margin-top:16px"></div>
              <div style="display:flex;gap:8px;justify-content:flex-end">
                <a href="<?= $baseUrl ?>/estoque" class="btn btn-gray">Cancelar</a>
                <button type="submit" class="btn btn-cyan">Atualizar Produto</button>
              </div>
            </form>
          </div>
        </div>

        <!-- LISTA DE SERIAIS -->
        <div class="card">
          <div class="card-head">
            <span class="card-title">Códigos de Barras</span>
            <div style="display:flex;gap:8px">
              <button type="button" class="btn btn-sm btn-cyan" data-action="abrir-modal-serial">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Novo
              </button>
            </div>
          </div>
          <div class="card-body" style="padding:0">
            <?php if (empty($seriais)): ?>
            <div class="table-empty">
              <div class="table-empty-flex">
                <svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
                <div>Nenhum código de barras cadastrado</div>
                <div style="font-size:12px;color:var(--text-4)">Clique em "Novo" para adicionar</div>
              </div>
            </div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse">
              <thead>
                <tr style="border-bottom:1px solid var(--bg-border);text-align:left">
                  <th style="padding:10px;font-size:12px;color:var(--text-3)">ID</th>
                  <th style="padding:10px;font-size:12px;color:var(--text-3)">Código de Barras</th>
                  <th style="padding:10px;font-size:12px;color:var(--text-3)">Status</th>
                  <th style="padding:10px;font-size:12px;color:var(--text-3);text-align:right">Acoes</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($seriais as $s): ?>
                <tr style="border-bottom:1px solid var(--bg-border)" data-id="<?= $s['id'] ?>">
                  <td style="padding:8px;font-size:13px"><?= $s['id'] ?></td>
                  <td style="padding:8px;font-size:13px;font-weight:500"><?= htmlspecialchars($s['serial']) ?></td>
                  <td style="padding:8px">
                    <?php
                    $badgeVariant = $s['status'] === 'ATIVO' ? 'green' : ($s['status'] === 'MANUTENCAO' ? 'yellow' : 'cyan');
                    echo renderBadge(['label' => $s['status'], 'variant' => $badgeVariant, 'size' => 'sm']);
                    ?>
                  </td>
                  <td style="padding:8px;text-align:right">
                    <button type="button" class="btn btn-sm btn-cyan" data-action="abrir-modal-serial" data-id="<?= $s['id'] ?>" data-serial="<?= htmlspecialchars($s['serial']) ?>" data-status="<?= $s['status'] ?>" data-motivo="<?= htmlspecialchars($s['motivo'] ?? '') ?>" style="margin-right:4px">Editar</button>
                    <button type="button" class="btn btn-sm btn-red" data-action="excluir-serial" data-id="<?= $s['id'] ?>">Excluir</button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL: Adicionar/Editar Serial -->
    <?= renderModal([
        'id' => 'modal-serial',
        'variant' => 'form',
        'title' => 'Adicionar Código de Barras',
        'subtitle' => 'Cadastrar novo código de barras para o produto',
        'body' => '<div class="fg"><div class="fl">Código de Barras</div><input type="text" id="modal-serial-serial" class="fi" placeholder="Ex: SN123456789"/></div>
                   <div class="fg" style="margin-top:12px"><div class="fl">Status</div><select id="modal-serial-status" class="fi" onchange="toggleMotivo()"><option value="ATIVO">Ativo</option><option value="MANUTENCAO">Manutencao</option><option value="VENDER">Vender</option></select></div>
                   <div class="fg" style="margin-top:12px;display:none" id="modal-serial-motivo-group"><div class="fl">Motivo (obrigatorio para manutencao)</div><input type="text" id="modal-serial-motivo" class="fi" placeholder="Motivo da manutencao"/></div>
                   <input type="hidden" id="modal-serial-id" value=""/>
                   <input type="hidden" id="modal-serial-produto-id" value="' . $produto['id'] . '"/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-serial">Cancelar</button><button class="btn btn-cyan" data-action="salvar-serial">Salvar</button>'
    ]) ?>

    <!-- MODAL: Adicionar Seriais em Lote -->
    <?= renderModal([
        'id' => 'modal-serial-lote',
        'variant' => 'form',
        'title' => 'Adicionar Códigos de Barras em Lote',
        'subtitle' => 'Use um leitor de codigo de barras ou digitalize um por linha',
        'body' => '<div class="fg">
                     <div class="fl">Códigos de Barras (um por linha)</div>
                     <textarea id="modal-lote-seriais" class="fi" rows="12" placeholder="Cole ou digitalize os códigos de barras aqui, um por linha..." style="font-family:monospace;font-size:14px;line-height:1.6"></textarea>
                     <div style="font-size:11px;color:var(--text-3);margin-top:6px">
                       <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                       Cada linha sera salva como um código de barras com status ATIVO
                     </div>
                   </div>
                   <input type="hidden" id="modal-lote-produto-id" value="' . $produto['id'] . '"/>',
        'footer' => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-serial-lote">Cancelar</button><button class="btn btn-green" data-action="salvar-lote"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Salvar Todos</button>'
    ]) ?>

<script>
const produtoId = <?= $produto['id'] ?>;

// Registrado via window.registerActions (nao document.addEventListener) pra nao duplicar
// o dispatch de clique: scripts.js ja delega [data-action] globalmente em document.body
// e cai num fallback window[camelCase(action)] pra acoes nao reconhecidas — registrar
// aqui E TAMBEM ouvir 'click' localmente disparava cada acao 2x (bug-XXX).
document.addEventListener('DOMContentLoaded', function() {
  window.registerActions({
    'abrir-modal-serial': function(el) {
      const id = el.dataset.id;
      if (id) {
        abrirModalSerial(id, el.dataset.serial, el.dataset.status, el.dataset.motivo);
      } else {
        abrirModalSerial();
      }
    },
    'abrir-modal-lote': function() { abrirModalLote(); },
    'salvar-serial': function() { salvarSerial(); },
    'salvar-lote': function() { salvarLote(); },
    'excluir-serial': function(el) { const id = el.dataset.id; if (id) excluirSerial(id); },
  });
});

// Currency mask for custo
document.addEventListener('DOMContentLoaded', function() {
  const custoInput = document.getElementById('custo-input');
  if (!custoInput) return;

  custoInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value === '') {
      e.target.value = '';
      return;
    }
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    e.target.value = value;
  });
});

function abrirModalSerial(id, serial, status, motivo) {
  if (id) {
    document.getElementById('modal-serial-id').value = id;
    document.getElementById('modal-serial-serial').value = serial || '';
    document.getElementById('modal-serial-status').value = status || 'ATIVO';
    document.getElementById('modal-serial-motivo').value = motivo || '';
    document.querySelector('#modal-serial .modal-title').textContent = 'Editar Código de Barras';
  } else {
    document.getElementById('modal-serial-id').value = '';
    document.getElementById('modal-serial-serial').value = '';
    document.getElementById('modal-serial-status').value = 'ATIVO';
    document.getElementById('modal-serial-motivo').value = '';
    document.querySelector('#modal-serial .modal-title').textContent = 'Adicionar Código de Barras';
  }
  
  toggleMotivo();
  openModal('modal-serial');
}

function salvarSerial() {
  const id = document.getElementById('modal-serial-id').value;
  const serial = document.getElementById('modal-serial-serial').value.trim();
  const status = document.getElementById('modal-serial-status').value;
  const motivo = document.getElementById('modal-serial-motivo').value.trim();
  const produtoId = document.getElementById('modal-serial-produto-id').value;

  if (!serial) {
    showToast('yellow', 'Atencao', 'Código de barras é obrigatório');
    return;
  }

  if (status === 'MANUTENCAO' && !motivo) {
    showToast('yellow', 'Atencao', 'Motivo e obrigatorio para status de manutencao');
    return;
  }

  if (id) {
    // Update
    fetch(BASE_URL + '/seriais/update/' + id, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN + '&serial=' + encodeURIComponent(serial) + '&status=' + status + '&motivo=' + encodeURIComponent(motivo)
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial atualizado!');
        closeModal('modal-serial');
        setTimeout(() => { window.location.reload(); }, 1000);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao atualizar serial');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  } else {
    // Create
    fetch(BASE_URL + '/seriais/store', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN + '&id_produto=' + produtoId + '&serial=' + encodeURIComponent(serial) + '&status=' + status + '&motivo=' + encodeURIComponent(motivo)
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', 'Serial adicionado!');
        closeModal('modal-serial');
        setTimeout(() => { window.location.reload(); }, 1000);
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao adicionar serial');
      }
    })
    .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
  }
}

function excluirSerial(id) {
  if (!confirm('Tem certeza que deseja excluir este serial?')) return;

  fetch(BASE_URL + '/seriais/delete/' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluido', 'Serial excluido com sucesso');
      setTimeout(() => { window.location.reload(); }, 1000);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao excluir serial');
    }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisicao'));
}

function toggleMotivo() {
  const status = document.getElementById('modal-serial-status').value;
  const group = document.getElementById('modal-serial-motivo-group');
  if (group) {
    group.style.display = status === 'MANUTENCAO' ? 'block' : 'none';
  }
}

function abrirModalLote() {
  document.getElementById('modal-lote-seriais').value = '';
  openModal('modal-serial-lote');
  // Focus no textarea para o leitor de codigo de barras
  setTimeout(() => { document.getElementById('modal-lote-seriais').focus(); }, 300);
}

function salvarLote() {
  const seriais = document.getElementById('modal-lote-seriais').value.trim();
  const produtoId = document.getElementById('modal-lote-produto-id').value;

  if (!seriais) {
    showToast('yellow', 'Atencao', 'Digite ou digitalize pelo menos um código de barras');
    return;
  }

  // Contar linhas para feedback
  const linhas = seriais.split('\n').filter(l => l.trim() !== '');
  if (linhas.length === 0) {
    showToast('yellow', 'Atencao', 'Nenhum código de barras válido encontrado');
    return;
  }

  // Botao em estado de loading
  const btnSalvar = document.querySelector('#modal-serial-lote .btn-green');
  const textoOriginal = btnSalvar.innerHTML;
  btnSalvar.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spinner" style="animation:spin 1s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Processando...';
  btnSalvar.disabled = true;

  fetch(BASE_URL + '/seriais/store-batch', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN + '&id_produto=' + produtoId + '&seriais=' + encodeURIComponent(seriais)
  })
  .then(r => r.json())
  .then(function(data) {
    btnSalvar.innerHTML = textoOriginal;
    btnSalvar.disabled = false;

    if (data.success) {
      const result = data.data;
      let msg = data.message;
      
      if (result.errors && result.errors.length > 0) {
        // Mostrar detalhes dos erros
        msg += '. Erros:\n' + result.errors.slice(0, 5).join('\n');
        if (result.errors.length > 5) {
          msg += '\n...e mais ' + (result.errors.length - 5) + ' erros';
        }
      }

      showToast('green', 'Sucesso', msg);
      closeModal('modal-serial-lote');
      setTimeout(() => { window.location.reload(); }, 1500);
    } else {
      showToast('red', 'Erro', data.message || 'Erro ao adicionar seriais em lote');
    }
  })
  .catch(() => {
    btnSalvar.innerHTML = textoOriginal;
    btnSalvar.disabled = false;
    showToast('red', 'Erro', 'Erro ao processar requisicao');
  });
}

function escapeHtml(text) {
  const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

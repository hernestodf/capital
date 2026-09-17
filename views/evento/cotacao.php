<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/accordion/accordion.php';

$idEvento = $evento['id'];
$idProdutoEvento = $item['id'];
$nomeProduto = $item['produto'] ?? '';
$nomeEvento = $evento['nome_evento'] ?? '';
$dataEvento = $evento['data_inicio'] ?? '';
$localEvento = $evento['local_evento'] ?? '';
$nomeSala = $item['nome_sala'] ?? 'Sem sala';
$custoAtual = $item['custo_unit'] ?? 0;
$fornecedorVencedor = $item['fornecedor_vencedor'] ?? '';
?>

    <section class="section active" id="sec-cotacao">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
          <div class="section-title">Cotacao - <?= htmlspecialchars($nomeProduto) ?></div>
          <div class="section-sub"><?= htmlspecialchars($nomeEvento) ?> | <?= htmlspecialchars($nomeSala) ?></div>
        </div>
        <div style="margin-left:auto">
          <a href="<?= $baseUrl ?>/eventos/edit/<?= $idEvento ?>" class="btn btn-gray">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 0l11 11"/></svg>
            Voltar
          </a>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Info do Item -->
      <div class="card mb-4">
        <div class="card-head">
          <div class="card-title">Informacoes do Item</div>
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
            <div>
              <div style="font-size:11px;color:var(--text-3);text-transform:uppercase">Produto</div>
              <div style="font-weight:600"><?= htmlspecialchars($nomeProduto) ?></div>
            </div>
            <div>
              <div style="font-size:11px;color:var(--text-3);text-transform:uppercase">Sala</div>
              <div style="font-weight:600"><?= htmlspecialchars($nomeSala) ?></div>
            </div>
            <div>
              <div style="font-size:11px;color:var(--text-3);text-transform:uppercase">Custo Atual</div>
              <div style="font-weight:600;color:var(--green)" id="custo-display">R$ <?= number_format($custoAtual, 2, ',', '.') ?></div>
            </div>
            <?php if ($fornecedorVencedor): ?>
            <div>
              <div style="font-size:11px;color:var(--text-3);text-transform:uppercase">Fornecedor Vencedor</div>
              <div style="font-weight:600;color:var(--cyan)"><?= htmlspecialchars($fornecedorVencedor) ?></div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- SECAO 1: Definir Vencedor -->
      <div class="card mb-4">
        <div class="card-head">
          <div class="card-title">Definir Fornecedor Vencedor</div>
        </div>
        <div class="card-body">
          <form id="form-vencedor" onsubmit="return false;">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>" />
            <div class="col2">
              <div class="fg">
                <div class="fl">Fornecedor</div>
                <select id="vencedor-fornecedor" class="fi">
                  <option value="">Selecione um fornecedor</option>
                  <?php foreach ($fornecedores as $f): ?>
                  <option value="<?= $f['id'] ?>" data-email="<?= htmlspecialchars($f['email'] ?? '') ?>"
                    <?= (!empty($fornecedorVencedor) && $fornecedorVencedor === ($f['nome_fantasia'] ?? '')) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nome_fantasia'] ?? $f['razao_social']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="fg">
                <div class="fl">Custo Unitario (R$)</div>
                <input type="number" step="0.01" id="vencedor-custo" class="fi" placeholder="0,00"
                  value="<?= $custoAtual > 0 ? $custoAtual : '' ?>" />
              </div>
            </div>
            <div style="margin-top:12px">
              <button type="button" class="btn btn-green" onclick="marcarVencedor(event)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Marcar como Vencedor
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- SECAO 2: Propostas Recebidas -->
      <div class="card mb-4">
        <div class="card-head">
          <div class="card-title">Propostas Recebidas</div>
          <span class="badge sm cyan" id="propostas-count"><?= count($propostas) ?></span>
        </div>
        <div class="card-body" id="propostas-list">
          <?php if (empty($propostas)): ?>
          <div style="text-align:center;padding:24px;color:var(--text-3)">
            <div style="font-size:14px">Nenhuma proposta recebida ainda</div>
          </div>
          <?php else: ?>
          <?php foreach ($propostas as $p): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-bottom:1px solid var(--bg-border-sub);<?= $p['vencedor'] === 'S' ? 'background:var(--green-alpha);border-left:3px solid var(--green);' : '' ?>"
               data-cotacao-id="<?= $p['id'] ?>">
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($p['fornecedor_nome']) ?></div>
              <div style="font-size:12px;color:var(--text-3)">
                Status: <span class="badge sm <?= $p['status'] === 'vencedor_definido' ? 'green' : ($p['status'] === 'com_proposta' ? 'cyan' : 'yellow') ?>">
                  <?= $p['status'] === 'vencedor_definido' ? 'Vencedor' : ($p['status'] === 'com_proposta' ? 'Com proposta' : 'Aguardando') ?>
                </span>
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <?php if (!empty($p['created_at'])): ?>
              <div style="font-size:11px;color:var(--text-3)"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- SECAO 3: Emails / Conversas -->
      <div class="card mb-4">
        <div class="card-head">
          <div class="card-title">Emails / Conversas por Fornecedor</div>
        </div>
        <div class="card-body" id="emails-container">
          <div style="text-align:center;padding:24px;color:var(--text-3)">
            <div style="font-size:14px">Carregando...</div>
          </div>
        </div>
      </div>

      <!-- Modal: Enviar Mensagem -->
      <?= renderModal([
          'id' => 'modal-enviar-mensagem',
          'variant' => 'form',
          'title' => 'Enviar Mensagem',
          'footer' => '
              <button type="button" class="btn btn-gray" onclick="closeModal(\'modal-enviar-mensagem\')">Cancelar</button>
              <button type="button" class="btn btn-cyan" onclick="enviarMensagem()">Enviar</button>
          ',
          'body' => '
              <input type="hidden" id="msg-id-cotacao" value="" />
              <input type="hidden" id="msg-id-fornecedor" value="" />
              <input type="hidden" id="msg-in-reply-to" value="" />
              <div class="fg">
                  <div class="fl">Fornecedor</div>
                  <div id="msg-nome-fornecedor" style="font-weight:600;padding:8px 0"></div>
              </div>
              <div class="fg">
                  <div class="fl">Assunto</div>
                  <input type="text" id="msg-assunto" class="fi" />
              </div>
              <div class="fg" style="margin-top:12px">
                  <div class="fl">Mensagem</div>
                  <textarea id="msg-corpo" class="fi" rows="5"></textarea>
              </div>
              <div class="fg" style="margin-top:12px">
                  <div class="fl">Anexo (opcional)</div>
                  <input type="file" id="msg-anexo" class="fi" accept=".pdf,.doc,.docx,.jpg,.png,.xls,.xlsx" />
              </div>
          '
      ]) ?>

    </section>

<script>
window.COTACAO_DATA = {
    idEvento: <?= $idEvento ?>,
    idProdutoEvento: <?= $item['id'] ?>,
    csrfToken: '<?= \App\Core\Csrf::getToken() ?>',
    propostas: <?= json_encode($propostas) ?>,
    cotacoes: <?= json_encode($cotacoes) ?>,
    itemNome: '<?= addslashes($nomeProduto) ?>',
    fornecedores: <?= json_encode(array_map(function($f) {
        return [
            'id' => $f['id'],
            'nome' => $f['nome_fantasia'] ?? $f['razao_social'],
            'email' => $f['email'] ?? ''
        ];
    }, $fornecedores)) ?>
};
</script>

<!-- Cotacao JS -->
<script src="<?= $baseUrl ?>/js/cotacao.js"></script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

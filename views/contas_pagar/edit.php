<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

if ($conta['tipo'] === 'colaborador')  $tipoBadge = renderBadge(['label'=>'Colaborador','variant'=>'blue','size'=>'sm']);
elseif ($conta['tipo'] === 'fornecedor') $tipoBadge = renderBadge(['label'=>'Fornecedor','variant'=>'cyan','size'=>'sm']);
else                                     $tipoBadge = renderBadge(['label'=>'Outros custos','variant'=>'gray','size'=>'sm']);

$nomePessoa = htmlspecialchars($conta['fornecedor_nome'] ?? $conta['colaborador_nome'] ?? 'Outros custos');
?>

<section class="section active">
  <div class="section-header">
    <div class="section-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
    </div>
    <div>
      <div class="section-title">Conta a Pagar</div>
      <div class="section-sub" id="header-nome-sub"><?= $nomePessoa ?></div>
    </div>
  </div>
  <div class="divider"></div>

  <!-- Card: informacoes somente leitura -->
  <div class="card" style="margin-bottom:16px">
    <div class="card-head">
      <span class="card-title">Informações</span>
      <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
        <?= $tipoBadge ?>
        <a href="<?= $baseUrl ?>/contas-pagar/print/<?= $conta['id'] ?>" target="_blank" class="btn btn-sm btn-gray" title="Imprimir">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:3px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Imprimir
        </a>
        <a href="<?= $baseUrl ?>/contas-pagar/pdf/<?= $conta['id'] ?>" class="btn btn-sm btn-cyan" title="Baixar PDF" target="_blank">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:3px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          PDF
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="col4">
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Quem</div>
          <div class="font-semibold" style="color:var(--text-1)" id="info-nome"><?= $nomePessoa ?></div>
        </div>
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Valor</div>
          <div class="font-bold" style="font-size:18px;color:var(--text-1)">R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></div>
        </div>
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Vencimento</div>
          <div class="font-semibold" style="color:var(--text-1)"><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></div>
        </div>
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Numero NF</div>
          <div class="font-semibold" style="color:var(--text-1)"><?= !empty($conta['numero_nf']) ? htmlspecialchars($conta['numero_nf']) : '<span style="color:var(--text-3);font-style:italic">Nao informado</span>' ?></div>
        </div>
      </div>
      <?php if (!empty($conta['evento_nome'])): ?>
      <div class="col2" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bg-border-sub)">
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Evento</div>
          <div class="font-semibold" style="color:var(--text-1)">
            <?= htmlspecialchars($conta['evento_nome']) ?>
            <span style="font-size:12px;color:var(--text-3);font-weight:normal">
              (OS: <?= $conta['evento_id'] ?><?= !empty($conta['evento_os_cliente']) ? ' / ' . htmlspecialchars($conta['evento_os_cliente']) : '' ?>)
            </span>
          </div>
          <?php if (!empty($conta['evento_local'])): ?>
            <div style="font-size:12px;color:var(--text-2);margin-top:2px"><?= htmlspecialchars($conta['evento_local']) ?></div>
          <?php endif; ?>
          <?php if (!empty($conta['evento_data_inicio'])): ?>
            <div style="font-size:12px;color:var(--text-2);margin-top:2px">Periodo: <?= date('d/m/Y', strtotime($conta['evento_data_inicio'])) ?> a <?= date('d/m/Y', strtotime($conta['evento_data_fim'])) ?></div>
          <?php endif; ?>
        </div>
        
        <div>
          <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Item Locado / Funcao</div>
          <?php if (!empty($conta['item_nome'])): ?>
            <div class="font-semibold" style="color:var(--text-1)"><?= htmlspecialchars($conta['item_nome']) ?></div>
            <?php if (!empty($conta['item_dias'])): ?>
              <div style="font-size:12px;color:var(--text-2);margin-top:2px">Dias: <?= $conta['item_dias'] ?></div>
            <?php endif; ?>
            <?php if (!empty($conta['item_observacao'])): ?>
              <div style="font-size:12px;color:var(--text-3);margin-top:2px;font-style:italic">Obs: <?= htmlspecialchars($conta['item_observacao']) ?></div>
            <?php endif; ?>
          <?php else: ?>
            <div style="color:var(--text-3)">Nenhum item associado</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bg-border-sub)">
        <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Descricao</div>
        <div style="color:var(--text-2)"><?= htmlspecialchars($conta['descricao']) ?></div>
      </div>

      <?php if (!empty($conta['observacao'])): ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bg-border-sub)">
        <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Observacao para o Financeiro</div>
        <div style="color:var(--text-2);background:rgba(255, 165, 0, 0.05);border:1px solid rgba(255, 165, 0, 0.2);padding:10px;border-radius:6px;font-weight:500;">
          <?= nl2br(htmlspecialchars($conta['observacao'])) ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Dados para Pagamento -->
      <div id="dados-pagamento-card" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bg-border-sub);<?= (!empty($conta['dados_pagamento']) || !empty($conta['chavepix'])) ? '' : 'display:none;' ?>">
        <div class="text-xs font-bold uppercase" style="letter-spacing:.6px;color:var(--text-3);margin-bottom:4px">Dados para Pagamento</div>
        <div id="dados-pagamento-container" style="background:var(--bg-secondary);border:1px dashed var(--cyan);border-radius:6px;padding:12px;display:flex;flex-direction:column;gap:6px">
          <?php if (!empty($conta['chavepix'])): ?>
            <div><strong style="color:var(--cyan)">Pix (<?= htmlspecialchars(strtoupper($conta['tipo_chave_pix'] ?? 'chave')) ?>):</strong> <span style="font-family:monospace;font-size:13px;color:var(--text-1)"><?= htmlspecialchars($conta['chavepix']) ?></span></div>
          <?php endif; ?>
          <?php if (!empty($conta['dados_pagamento'])): ?>
            <div style="white-space:pre-wrap;color:var(--text-1);line-height:1.4"><?= htmlspecialchars($conta['dados_pagamento']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Card: pagamento -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Registrar Pagamento</span>
    </div>
    <div class="card-body">
      <?php if (!empty($error)): ?>
        <?= renderAlert(['variant'=>'red','title'=>'Erro','content'=>$error]) ?>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/contas-pagar/update/<?= $conta['id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <?php if ($conta['tipo'] === 'fornecedor' && !empty($fornecedores)): ?>
        <div class="fg" style="margin-bottom:16px">
          <div class="fl">Fornecedor</div>
          <select name="id_fornecedor" id="id_fornecedor" class="fi" onchange="updateSupplierPaymentDetails()">
            <option value="">Selecione um fornecedor</option>
            <?php foreach ($fornecedores as $f): ?>
              <option value="<?= $f['id'] ?>" <?= $f['id'] == ($conta['id_fornecedor'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($f['nome_fantasia'] ?? $f['razao_social'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div class="col2">
          <!-- Status -->
          <div class="fg">
            <div class="fl">Status</div>
            <select name="status" id="cp-status" class="fi" onchange="togglePagoFields()">
              <option value="PENDENTE" <?= $conta['status']==='PENDENTE'?'selected':'' ?>>Pendente</option>
              <option value="PAGO"     <?= $conta['status']==='PAGO'    ?'selected':'' ?>>Pago</option>
              <option value="VENCIDO"  <?= $conta['status']==='VENCIDO' ?'selected':'' ?>>Vencido</option>
            </select>
          </div>

          <!-- Número NF -->
          <div class="fg">
            <div class="fl">Número NF</div>
            <input type="text" name="numero_nf" class="fi" placeholder="Número da Nota Fiscal" value="<?= htmlspecialchars($conta['numero_nf'] ?? '') ?>"/>
          </div>
        </div>

        <!-- Campos de pagamento (visiveis quando PAGO) -->
        <div id="pago-fields" style="margin-top:16px;<?= $conta['status']==='PAGO'?'':'display:none' ?>">
          <div class="col3">
            <div class="fg">
              <div class="fl">Data do Pagamento</div>
              <input type="date" name="data_pagamento" class="fi" value="<?= htmlspecialchars($conta['data_pagamento'] ?? date('Y-m-d')) ?>"/>
            </div>
            <div class="fg">
              <div class="fl">Valor Pago (R$)</div>
              <input type="text" name="valor_pago" class="fi" placeholder="0,00"
                     value="<?= !empty($conta['valor_pago']) ? number_format((float)$conta['valor_pago'],2,',','.') : number_format((float)$conta['valor'],2,',','.') ?>"
                     oninput="if(window.mascaraMoeda)mascaraMoeda(this)" style="font-weight:600"/>
            </div>
            <div class="fg">
              <div class="fl">Forma de Pagamento</div>
              <select name="tipo_pagamento" class="fi">
                <option value="">Selecione</option>
                <?php foreach(['PIX','Transferencia','Dinheiro','Boleto','Cartao'] as $tp): ?>
                <option value="<?= $tp ?>" <?= ($conta['tipo_pagamento']==$tp)?'selected':'' ?>><?= $tp ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Comprovante de pagamento -->
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">
          <div class="col2">
            <div class="fg">
              <div class="fl">Comprovante de Pagamento</div>
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <label class="btn btn-cyan btn-sm" style="cursor:pointer">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0-3v12"/></svg>
                  Upload
                  <input type="file" name="comprovante_anexo" accept=".pdf,.jpg,.jpeg,.png" onchange="mostrarArquivo(this,'comprovante-nome')" style="display:none"/>
                </label>
                <span id="comprovante-nome" style="font-size:12px;color:var(--text-2)">
                  <?= !empty($conta['comprovante_anexo']) ? basename($conta['comprovante_anexo']) : 'Nenhum arquivo' ?>
                </span>
              </div>
              <?php if (!empty($conta['comprovante_anexo'])): ?>
              <div style="margin-top:6px">
                <a href="/<?= htmlspecialchars($conta['comprovante_anexo']) ?>" target="_blank" class="btn btn-sm btn-gray">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Ver comprovante
                </a>
              </div>
              <?php endif; ?>
            </div>

            <div class="fg">
              <div class="fl">Nota Fiscal</div>
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <label class="btn btn-cyan btn-sm" style="cursor:pointer">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0-3v12"/></svg>
                  Upload
                  <input type="file" name="nota_fiscal" accept=".pdf,.jpg,.jpeg,.png" onchange="mostrarArquivo(this,'nf-nome')" style="display:none"/>
                </label>
                <span id="nf-nome" style="font-size:12px;color:var(--text-2)">
                  <?= !empty($conta['nota_fiscal']) ? basename($conta['nota_fiscal']) : 'Nenhum arquivo' ?>
                </span>
              </div>
              <?php if (!empty($conta['nota_fiscal'])): ?>
              <div style="margin-top:6px">
                <a href="/<?= htmlspecialchars($conta['nota_fiscal']) ?>" target="_blank" class="btn btn-sm btn-gray">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Ver nota fiscal
                </a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Observação -->
        <div class="fg" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--bg-border-sub)">
          <div class="fl">Observação para o Financeiro</div>
          <textarea name="observacao" class="fi" rows="3" placeholder="Observações adicionais para o setor financeiro"><?= htmlspecialchars($conta['observacao'] ?? '') ?></textarea>
        </div>

        <div style="margin-top:24px;display:flex;gap:12px">
          <button type="submit" class="btn btn-cyan">Salvar</button>
          <a href="<?= $baseUrl ?>/contas-pagar" class="btn btn-gray">Voltar</a>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
function togglePagoFields() {
  var status = document.getElementById('cp-status').value;
  var fields = document.getElementById('pago-fields');
  fields.style.display = status === 'PAGO' ? 'block' : 'none';
}

function mostrarArquivo(input, spanId) {
  var span = document.getElementById(spanId);
  if (span) span.textContent = (input.files && input.files[0]) ? input.files[0].name : 'Nenhum arquivo';
}

window.mascaraMoeda = window.mascaraMoeda || function(input) {
  var v = input.value.replace(/\D/g,'');
  if (!v) { input.value=''; return; }
  v = (parseInt(v)/100).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  input.value = v;
};

<?php if ($conta['tipo'] === 'fornecedor' && !empty($fornecedores)): ?>
const SUPPLIER_DETAILS = <?= json_encode(array_combine(
  array_column($fornecedores, 'id'),
  array_map(function($f) {
    return [
      'cpf_cnpj' => $f['cpf_cnpj'],
      'dados_pagamento' => $f['dados_pagamento'],
    ];
  }, $fornecedores)
)) ?>;

function updateSupplierPaymentDetails() {
  const select = document.getElementById('id_fornecedor');
  if (!select) return;
  const fornId = select.value;
  const container = document.getElementById('dados-pagamento-container');
  const card = document.getElementById('dados-pagamento-card');
  const infoNome = document.getElementById('info-nome');
  const headerNomeSub = document.getElementById('header-nome-sub');
  
  if (select.selectedIndex >= 0) {
    const selectedText = select.options[select.selectedIndex].text;
    if (fornId) {
      if (infoNome) infoNome.textContent = selectedText;
      if (headerNomeSub) headerNomeSub.textContent = selectedText;
    }
  }

  if (!fornId || !SUPPLIER_DETAILS[fornId]) {
    if (card) card.style.display = 'none';
    return;
  }
  
  const details = SUPPLIER_DETAILS[fornId];
  let html = '';
  let hasData = false;
  
  if (details.dados_pagamento) {
    html += `<div style="white-space:pre-wrap;color:var(--text-1);line-height:1.4">${escapeHtml(details.dados_pagamento)}</div>`;
    hasData = true;
  }
  
  if (hasData) {
    if (container) container.innerHTML = html;
    if (card) card.style.display = 'block';
  } else {
    if (card) card.style.display = 'none';
  }
}

function escapeHtml(text) {
  if (!text) return '';
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
<?php endif; ?>
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>

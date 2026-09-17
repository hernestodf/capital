<style>
  body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
  h1 { font-size: 18px; color: #1e293b; margin: 0 0 6px 0; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
  h2 { font-size: 13px; color: #1e293b; margin: 16px 0 10px 0; font-weight: bold; border-bottom: 2px solid #1e293b; padding-bottom: 3px; }
  h3 { font-size: 12px; color: #334155; margin: 0 0 8px 0; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 4px; }
  th { background: #1e293b; color: #fff; font-weight: bold; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  td { padding: 4px 8px; border-bottom: 1px solid #e5e7eb; color: #374151; }
  tr:nth-child(even) td { background: #f9fafb; }
  .label-cell { font-weight: bold; color: #6b7280; width: 100px; }
  .section-divider { margin: 16px 0 10px 0; }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .text-muted { color: #9ca3af; }
  .badge { display: inline-block; padding: 2px 7px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; border-radius: 3px; }
  .badge-green { background: #16a34a22; color: #15803d; border: 1px solid #16a34a44; }
  .badge-red { background: #dc262622; color: #b91c1c; border: 1px solid #dc262644; }
  .badge-yellow { background: #eab30822; color: #a16207; border: 1px solid #eab30844; }
  .badge-gray { background: #64748b22; color: #475569; border: 1px solid #64748b44; }
  .badge-cyan { background: #06b6d422; color: #0891b2; border: 1px solid #06b6d444; }
  .badge-blue { background: #3b82f622; color: #2563eb; border: 1px solid #3b82f644; }
  .obs-box { font-size: 10px; color: #92400e; font-style: italic; padding: 4px 8px; background: #fef3c7; border-left: 3px solid #f59e0b; margin: 3px 0 6px 0; }
  .payment-box { font-size: 10px; color: #166534; padding: 4px 8px; background: #f0fdf4; border-left: 3px solid #22c55e; margin: 3px 0 6px 0; }
  .pix-box { font-size: 10px; color: #0e7490; padding: 4px 8px; background: #ecfeff; border-left: 3px solid #06b6d4; margin: 3px 0 6px 0; font-family: 'Courier New', monospace; }
  .total-box { text-align: right; margin-top: 8px; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; }
  .total-valor { font-size: 14px; font-weight: bold; color: #1e293b; }
  .footer-text { font-size: 9px; color: #9ca3af; text-align: center; margin-top: 16px; }
  .doc-title { font-size: 16px; font-weight: bold; color: #1e293b; text-transform: uppercase; text-align: center; letter-spacing: 1px; margin-bottom: 16px; }
</style>

<?php
$nomePessoa = $conta['fornecedor_nome'] ?? $conta['colaborador_nome'] ?? 'Outros custos';
$tipoLabel = match($conta['tipo'] ?? 'outro') {
    'colaborador' => 'Colaborador',
    'fornecedor'  => 'Fornecedor',
    default       => 'Outros custos'
};
$tipoBadgeClass = match($conta['tipo'] ?? 'outro') {
    'colaborador' => 'badge-blue',
    'fornecedor'  => 'badge-cyan',
    default       => 'badge-gray'
};
$statusBadge = match($conta['status'] ?? 'PENDENTE') {
    'PAGO'    => '<span class="badge badge-green">PAGO</span>',
    'VENCIDO' => '<span class="badge badge-red">VENCIDO</span>',
    'PARCIAL' => '<span class="badge badge-yellow">PARCIAL</span>',
    default   => '<span class="badge badge-gray">PENDENTE</span>'
};
?>

<div class="doc-title">Comprovante de Pagamento</div>

<h2>1. Identificação</h2>
<table>
  <tr>
    <td class="label-cell" style="width:12%">Quem</td>
    <td style="width:38%"><strong><?= htmlspecialchars($nomePessoa) ?></strong> <span class="badge <?= $tipoBadgeClass ?>"><?= $tipoLabel ?></span></td>
    <td class="label-cell" style="width:12%">CPF/CNPJ</td>
    <td style="width:38%"><?= !empty($conta['cpf_cnpj']) ? htmlspecialchars($conta['cpf_cnpj']) : '<span class="text-muted">—</span>' ?></td>
  </tr>
  <tr>
    <td class="label-cell">Nº NF</td>
    <td><?= !empty($conta['numero_nf']) ? htmlspecialchars($conta['numero_nf']) : '<span class="text-muted">Não informado</span>' ?></td>
    <td class="label-cell">Tipo</td>
    <td><?= $tipoLabel ?></td>
  </tr>
  <tr>
    <td class="label-cell">Descrição</td>
    <td colspan="3"><?= htmlspecialchars($conta['descricao']) ?></td>
  </tr>
</table>

<h2>2. Valores</h2>
<table>
  <tr>
    <td class="label-cell" style="width:12%">Valor</td>
    <td style="width:38%"><strong style="font-size:13px">R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></strong></td>
    <td class="label-cell" style="width:12%">Vencimento</td>
    <td style="width:38%"><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></td>
  </tr>
  <tr>
    <td class="label-cell">Status</td>
    <td><?= $statusBadge ?></td>
    <td class="label-cell">Valor Pago</td>
    <td><strong style="font-size:13px">R$ <?= number_format((float)($conta['valor_pago'] ?? $conta['valor']), 2, ',', '.') ?></strong></td>
  </tr>
  <?php if (!empty($conta['data_pagamento'])): ?>
  <tr>
    <td class="label-cell">Data Pagamento</td>
    <td><?= date('d/m/Y', strtotime($conta['data_pagamento'])) ?></td>
    <td class="label-cell">Forma</td>
    <td><?= !empty($conta['tipo_pagamento']) ? htmlspecialchars($conta['tipo_pagamento']) : '<span class="text-muted">—</span>' ?></td>
  </tr>
  <?php endif; ?>
</table>

<?php if (!empty($conta['evento_nome']) || !empty($conta['evento_id'])): ?>
<h2>3. Evento</h2>
<table>
  <tr>
    <td class="label-cell" style="width:12%">Evento</td>
    <td style="width:38%"><strong><?= htmlspecialchars($conta['evento_nome'] ?? '—') ?></strong>
      <?php if (!empty($conta['evento_os_cliente'])): ?>
      <span style="color:#64748b">(OS: <?= htmlspecialchars($conta['evento_os_cliente']) ?>)</span>
      <?php endif; ?>
    </td>
    <td class="label-cell" style="width:12%">ID</td>
    <td style="width:38%">#<?= $conta['evento_id'] ?></td>
  </tr>
  <?php if (!empty($conta['evento_local'])): ?>
  <tr>
    <td class="label-cell">Local</td>
    <td><?= htmlspecialchars($conta['evento_local']) ?></td>
    <td class="label-cell">Período</td>
    <td>
      <?php if (!empty($conta['evento_data_inicio'])): ?>
        <?= date('d/m/Y', strtotime($conta['evento_data_inicio'])) ?> a <?= date('d/m/Y', strtotime($conta['evento_data_fim'])) ?>
      <?php endif; ?>
    </td>
  </tr>
  <?php endif; ?>
</table>
<?php endif; ?>

<?php if (!empty($conta['item_nome'])): ?>
<h2>4. Item / Serviço</h2>
<table>
  <thead>
    <tr>
      <th style="width:50%">Item / Serviço</th>
      <th style="width:12%;text-align:center">Dias</th>
      <th style="width:38%">Observação</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong><?= htmlspecialchars($conta['item_nome']) ?></strong></td>
      <td class="text-center"><?= $conta['item_dias'] ?? '-' ?></td>
      <td style="font-style:italic;color:#64748b"><?= htmlspecialchars($conta['item_observacao'] ?? '-') ?></td>
    </tr>
  </tbody>
</table>
<?php endif; ?>

<?php if ($conta['status'] === 'PAGO'): ?>
<h2>5. Pagamento Realizado</h2>
<div class="payment-box">
  <table style="margin:0">
    <tr>
      <td style="border:none;background:transparent;width:33%">
        <span class="text-muted" style="font-size:9px">Data</span><br>
        <strong><?= !empty($conta['data_pagamento']) ? date('d/m/Y', strtotime($conta['data_pagamento'])) : '-' ?></strong>
      </td>
      <td style="border:none;background:transparent;width:33%">
        <span class="text-muted" style="font-size:9px">Valor Pago</span><br>
        <strong style="font-size:13px">R$ <?= number_format((float)($conta['valor_pago'] ?? $conta['valor']), 2, ',', '.') ?></strong>
      </td>
      <td style="border:none;background:transparent;width:33%">
        <span class="text-muted" style="font-size:9px">Forma</span><br>
        <strong><?= !empty($conta['tipo_pagamento']) ? htmlspecialchars($conta['tipo_pagamento']) : '-' ?></strong>
      </td>
    </tr>
  </table>
</div>
<?php endif; ?>

<?php if (!empty($conta['chavepix']) || !empty($conta['dados_pagamento'])): ?>
<h2><?= $conta['status'] === 'PAGO' ? '6' : '5' ?>. Dados Bancários / Pix</h2>
<?php if (!empty($conta['chavepix'])): ?>
<div class="pix-box">
  <strong>PIX</strong>
  <?php if (!empty($conta['tipo_chave_pix'])): ?>(<?= htmlspecialchars(strtoupper($conta['tipo_chave_pix'])) ?>)<?php endif; ?>:
  <span style="font-size:12px"><?= htmlspecialchars($conta['chavepix']) ?></span>
</div>
<?php endif; ?>
<?php if (!empty($conta['dados_pagamento'])): ?>
<div class="obs-box" style="background:#f8fafc;border-left-color:#64748b;color:#334155;font-style:normal;white-space:pre-wrap"><?= htmlspecialchars($conta['dados_pagamento']) ?></div>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty($conta['observacao'])): ?>
<h2>Observações</h2>
<div class="obs-box"><?= nl2br(htmlspecialchars($conta['observacao'])) ?></div>
<?php endif; ?>

<?php if (!empty($conta['comprovante_anexo']) || !empty($conta['nota_fiscal'])): ?>
<h2>Anexos</h2>
<table>
  <thead>
    <tr>
      <th style="width:50%">Tipo</th>
      <th style="width:50%">Arquivo</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($conta['comprovante_anexo'])): ?>
    <tr><td>Comprovante de Pagamento</td><td style="color:#2563eb"><?= basename($conta['comprovante_anexo']) ?></td></tr>
    <?php endif; ?>
    <?php if (!empty($conta['nota_fiscal'])): ?>
    <tr><td>Nota Fiscal</td><td style="color:#2563eb"><?= basename($conta['nota_fiscal']) ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>
<?php endif; ?>

<div class="total-box">
  <strong>Total:</strong> <span class="total-valor">R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></span>
</div>

<div class="footer-text">
  <?= htmlspecialchars($empresa['rodape_pdf'] ?? 'Sistema SisLoc - Todos os direitos reservados') ?>
</div>
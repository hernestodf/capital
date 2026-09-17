<?php
// Variables: evento, salas, produtosPorSala, montagemPorSala,
//            clienteNome, produtorNome, demandanteNome, separacaoNome,
//            baseCss, assinaturaHtml

$fmtDate = fn($d) => !empty($d) ? date('d/m/Y', strtotime($d)) : '-';
$fmtTime = fn($t) => !empty($t) ? date('H:i', strtotime($t)) : '-';

$eventoMontado    = ($evento['evento_montado']    ?? 0) == 1 ? 'Sim' : 'Nao';
$eventoDesmontado = ($evento['evento_desmontado'] ?? 0) == 1 ? 'Sim' : 'Nao';

// Energia (potencia/kWh/kVA) — mesma formula usada em Salas e Produtos:
// kWh = qtd * potencia_w * horas_uso / 1000 ; kVA = kWh * 1,25 (fp = 0,8)
$temEnergiaMontagem = false;
$kwhPorSalaMontagem = [];
$totalKwhMontagem = 0;
foreach ($produtosPorSala as $salaIdKey => $itensSala) {
    foreach ($itensSala as $pe) {
        $potW = (float)($pe['potencia_w'] ?? 0);
        if ($potW <= 0) continue;
        $temEnergiaMontagem = true;
        $horasU = (float)($pe['horas_uso'] ?? 20);
        $q = (float)($pe['qtd'] ?? 1);
        $kwhItem = round($q * $potW * $horasU / 1000, 3);
        if (!isset($kwhPorSalaMontagem[$salaIdKey])) $kwhPorSalaMontagem[$salaIdKey] = 0;
        $kwhPorSalaMontagem[$salaIdKey] += $kwhItem;
        $totalKwhMontagem += $kwhItem;
    }
}
$totalKvaMontagem = round($totalKwhMontagem * 1.25, 3);

echo $baseCss;
?>
<style>
    .sala-header { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 8px; margin-top: 10px; }
    .sala-nome { font-size: 13px; color: #1e293b; font-weight: bold; text-transform: uppercase; }
    .obs-sala { font-size: 10px; color: #92400e; font-style: italic; padding: 4px 8px; background: #fef3c7; border-left: 3px solid #f59e0b; margin: 3px 0 6px 0; }
    .obs-item { font-size: 9px; color: #d97706; font-style: italic; }
    .produtos-section { margin: 6px 0 8px 0; }
    .produtos-title { font-size: 10px; color: #64748b; font-weight: bold; margin-bottom: 4px; }
    .produto-ref { padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 3px; margin-bottom: 3px; background: #f8fafc; }
    .produto-ref-nome { font-size: 10px; font-weight: bold; color: #1e293b; }
    .seriais-section { margin: 6px 0 8px 0; }
    .seriais-title { font-size: 10px; color: #64748b; font-weight: bold; margin-bottom: 4px; }
    .serial-item { padding: 3px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
    .serial-num { font-weight: bold; color: #1e293b; }
    .serial-prod { color: #64748b; margin-left: 8px; }
    .subloc-section { margin: 6px 0 8px 0; }
    .subloc-title { font-size: 10px; color: #7c3aed; font-weight: bold; margin-bottom: 4px; }
    .subloc-item { padding: 3px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; border-left: 3px solid #a78bfa; margin-bottom: 2px; background: #faf5ff; }
    .subloc-serial { font-weight: bold; color: #1e293b; font-family: monospace; }
    .subloc-forn { color: #7c3aed; margin-left: 6px; }
    .subloc-prod { color: #64748b; margin-left: 6px; }
    .doc-title { font-size: 16px; font-weight: bold; color: #1e293b; text-transform: uppercase; margin-bottom: 4px; }
    .os-badge { font-size: 11px; font-weight: bold; color: #1e293b; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 3px; }
    .dados-montagem { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
    .dados-montagem td { padding: 3px 6px; border: none; font-size: 10px; }
    .label-cell { font-weight: bold; color: #64748b; width: 12%; }
    .sala-energia { font-size: 10px; color: #6b21a8; font-weight: bold; margin-top: 2px; }
    .produto-potencia { font-weight: bold; color: #6b21a8; font-size: 9px; }
    .total-energia-montagem { text-align: right; margin-top: 10px; padding: 6px 10px; background: #f5f3ff; border: 1px solid #ddd6fe; font-size: 11px; font-weight: bold; color: #6b21a8; }
</style>

<div class="doc-title">Plano de Montagem</div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
    <div style="font-size:11px;color:#475569"><?= htmlspecialchars($evento['nome_evento'] ?? '') ?></div>
    <?php if (!empty($evento['os_cliente'])): ?>
    <span class="os-badge">OS: <?= htmlspecialchars($evento['os_cliente']) ?></span>
    <?php endif ?>
</div>
<div style="border-bottom: 2px solid #1e293b; margin-bottom: 10px;"></div>

<h2>Dados do Evento</h2>
<table class="dados-montagem">
    <tr>
        <td class="label-cell">Cliente:</td>
        <td style="width:22%"><?= htmlspecialchars($clienteNome ?: '-') ?></td>
        <td class="label-cell">Comercial:</td>
        <td style="width:22%"><?= htmlspecialchars($produtorNome ?: '-') ?></td>
        <td class="label-cell">Comprador:</td>
        <td style="width:28%"><?= htmlspecialchars($demandanteNome ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label-cell">Local:</td>
        <td colspan="5"><?= htmlspecialchars($evento['local_evento'] ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label-cell">Comprador Local:</td>
        <td colspan="2"><?= htmlspecialchars($evento['demandante_local'] ?: '-') ?></td>
        <td class="label-cell">Tel. Comprador:</td>
        <td colspan="2"><?= htmlspecialchars($evento['telefone_demandantelocal'] ?: '-') ?></td>
        <td></td>
    </tr>
    <?php if ($separacaoNome !== ''): ?>
    <tr>
        <td class="label-cell">Separado por:</td>
        <td colspan="5"><?= htmlspecialchars($separacaoNome) ?></td>
    </tr>
    <?php endif ?>
    <tr>
        <td class="label-cell">Data Montagem:</td>
        <td><?= $fmtDate($evento['data_montagem']) ?></td>
        <td class="label-cell">Data Inicio:</td>
        <td><?= $fmtDate($evento['data_inicio']) ?></td>
        <td class="label-cell">Data Fim:</td>
        <td><?= $fmtDate($evento['data_fim']) ?></td>
        <td class="label-cell">Data Desmont.:</td>
        <td><?= $fmtDate($evento['data_desmontagem']) ?></td>
    </tr>
    <tr>
        <td class="label-cell">Hora Montagem:</td>
        <td><?= $fmtTime($evento['hora_montagem']) ?></td>
        <td class="label-cell">Hora Inicio:</td>
        <td><?= $fmtTime($evento['hora_inicio']) ?></td>
        <td class="label-cell">Hora Fim:</td>
        <td><?= $fmtTime($evento['hora_fim']) ?></td>
        <td class="label-cell">Hora Desmont.:</td>
        <td><?= $fmtTime($evento['hora_desmontagem']) ?></td>
    </tr>
    <tr>
        <td class="label-cell">Evento Montado:</td>
        <td><?= $eventoMontado ?></td>
        <td class="label-cell">Evento Desmont.:</td>
        <td><?= $eventoDesmontado ?></td>
        <td colspan="4"></td>
    </tr>
    <?php if (!empty($evento['observacao'])): ?>
    <tr>
        <td class="label-cell">Observacao:</td>
        <td colspan="7"><?= nl2br(htmlspecialchars($evento['observacao'])) ?></td>
    </tr>
    <?php endif ?>
</table>
<div class="section-divider"></div>

<h2>Plano de Montagem por Sala</h2>

<?php foreach ($salas as $sala):
    $salaId  = $sala['id'];
    $salaNome = $sala['nome_sala'] ?? 'Sala';
    $salaObs  = !empty($sala['orientacoes_montagem']) ? $sala['orientacoes_montagem'] : '';
    $seriais  = $montagemPorSala[$salaId] ?? [];
?>
<div class="sala-header">
    <div class="sala-nome"><?= htmlspecialchars($salaNome) ?></div>
    <?php if (!empty($kwhPorSalaMontagem[$salaId])): $salaKvaM = round($kwhPorSalaMontagem[$salaId] * 1.25, 3); ?>
    <div class="sala-energia">Consumo: <?= number_format($kwhPorSalaMontagem[$salaId], 3, ',', '.') ?> kWh / <?= number_format($salaKvaM, 3, ',', '.') ?> kVA</div>
    <?php endif ?>
</div>

<?php if (!empty($salaObs)): ?>
<div class="obs-sala">
    <strong>Obs:</strong> <?= nl2br(htmlspecialchars($salaObs)) ?>
</div>
<?php endif ?>

<?php if (!empty($produtosPorSala[$salaId])): ?>
<div class="produtos-section">
    <div class="produtos-title">Produtos Previstos:</div>
    <?php foreach ($produtosPorSala[$salaId] as $pe):
        $peQtd  = (int)($pe['qtd'] ?? 1);
        $peDias = (int)($pe['dias'] ?? 1);
        $pePotW = (float)($pe['potencia_w'] ?? 0);
        $peHorasU = (float)($pe['horas_uso'] ?? 20);
        $peKwh = $pePotW > 0 ? round($peQtd * $pePotW * $peHorasU / 1000, 3) : 0;
    ?>
    <div class="produto-ref">
        <div class="produto-ref-nome">
            <?= htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($pe['produto'] ?? ''))) ?>
            <span style="font-weight:normal;color:#64748b;font-size:9px">(Qtd: <?= $peQtd ?> | Dias: <?= $peDias ?>)</span>
            <?php if ($pePotW > 0): ?>
            <span class="produto-potencia"><?= number_format($pePotW, 0, ',', '.') ?>W · <?= number_format($peKwh, 3, ',', '.') ?> kWh</span>
            <?php endif ?>
        </div>
        <?php if (!empty($pe['observacao_montagem'])): ?>
        <div class="obs-item">Obs: <?= nl2br(htmlspecialchars($pe['observacao_montagem'])) ?></div>
        <?php endif ?>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<?php if (!empty($seriais)): ?>
<div class="seriais-section">
    <div class="seriais-title">C&#243;digos de Barras:</div>
    <?php foreach ($seriais as $item): ?>
    <div class="serial-item">
        <span class="serial-num"><?= htmlspecialchars($item['serial'] ?? '-') ?></span>
        <span class="serial-prod"> - <?= htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($item['nome_produto'] ?? ''))) ?></span>
    </div>
    <?php endforeach ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:8px;color:#9ca3af;font-style:italic;font-size:8px">Nenhum c&#243;digo de barras nesta sala</div>
<?php endif; ?>

<?php
$sublocSala = [];
if (!empty($produtosPorSala[$salaId])) {
    foreach ($produtosPorSala[$salaId] as $pe) {
        $peId = (int)$pe['id'];
        if (!empty($sublocacoesPorProduto[$peId])) {
            foreach ($sublocacoesPorProduto[$peId] as $sub) {
                $sublocSala[] = $sub;
            }
        }
    }
}
?>

<?php if (!empty($sublocSala)): ?>
<div class="subloc-section">
    <div class="subloc-title">Subloca&#231;&#227;o:</div>
    <?php foreach ($sublocSala as $sub): ?>
    <div class="subloc-item">
        <span class="subloc-prod"><?= htmlspecialchars($sub['produto_fornecedor'] ?: ($sub['produto'] ?? '')) ?></span>
        <span class="subloc-serial"> · <?= htmlspecialchars($sub['serial_fornecedor'] ?? '-') ?></span>
        <span class="subloc-forn"> — <?= htmlspecialchars($sub['fornecedor_nome'] ?? 'Fornecedor') ?></span>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<div style="margin-bottom:12px"></div>

<?php endforeach ?>

<?php if ($temEnergiaMontagem): ?>
<div class="total-energia-montagem">
    Consumo Total de Energia do Evento: <?= number_format($totalKwhMontagem, 3, ',', '.') ?> kWh / <?= number_format($totalKvaMontagem, 3, ',', '.') ?> kVA
</div>
<?php endif ?>

<?= $assinaturaHtml ?>

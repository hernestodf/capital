<?php
// Variables: evento, salas, itens, totais, totaisPorSala, semValores, isLocacao,
//            mostrarCustos, clienteNome, produtorNome, demandanteNome, separacaoNome,
//            baseCss, assinaturaHtml

$titulo = $isLocacao ? 'LOCACAO' : 'ORCAMENTO';
if ($mostrarCustos) $titulo .= ' - COM CUSTOS E VALORES';

$diasEvento = 1;
if (!empty($evento['data_inicio']) && !empty($evento['data_fim'])) {
    try {
        $dtInicio = new \DateTime($evento['data_inicio']);
        $dtFim = new \DateTime($evento['data_fim']);
        $diasEvento = max(1, (int)$dtInicio->diff($dtFim)->days + 1);
    } catch (\Exception $e) {
        $diasEvento = 1;
    }
}

$fmtDate = fn($d) => !empty($d) ? date('d/m/Y', strtotime($d)) : '-';
$fmtTime = fn($t) => !empty($t) ? date('H:i', strtotime($t)) : '-';

$eventoMontado    = ($evento['evento_montado']    ?? 0) == 1 ? 'Sim' : 'Nao';
$eventoDesmontado = ($evento['evento_desmontado'] ?? 0) == 1 ? 'Sim' : 'Nao';

$itensPorSala = [];
foreach ($itens as $item) {
    $salaKey = $item['id_sala'] ?? 'sem_sala';
    $itensPorSala[$salaKey][] = $item;
}

// Energia (potencia/kWh/kVA) — mesma formula usada em Salas e Produtos:
// kWh = qtd * potencia_w * horas_uso / 1000 ; kVA = kWh * 1,25 (fp = 0,8)
$temEnergia = false;
$kwhPorSala = [];
$totalKwh = 0;
foreach ($itens as $item) {
    $potW = (float)($item['potencia_w'] ?? 0);
    if ($potW <= 0) continue;
    $temEnergia = true;
    $horasU = (float)($item['horas_uso'] ?? 20);
    $q = (float)($item['qtd'] ?? 1);
    $kwhItem = round($q * $potW * $horasU / 1000, 3);
    $salaKey = $item['id_sala'] ?? 'sem_sala';
    if (!isset($kwhPorSala[$salaKey])) $kwhPorSala[$salaKey] = 0;
    $kwhPorSala[$salaKey] += $kwhItem;
    $totalKwh += $kwhItem;
}
$totalKva = round($totalKwh * 1.25, 3);

$salaNomes = [];
$salaObs   = [];
foreach ($salas as $s) {
    $salaNomes[$s['id']] = $s['nome_sala'];
    $salaObs[$s['id']]   = !empty($s['orientacoes_montagem']) ? $s['orientacoes_montagem'] : '';
}

$salaOrdem = array_keys($salaNomes);
if (isset($itensPorSala['sem_sala'])) $salaOrdem[] = 'sem_sala';

echo $baseCss;
?>
<style>
    .sala-header { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 8px; margin-top: 10px; }
    .sala-nome { font-size: 12px; color: #1e293b; font-weight: bold; }
    .sala-total { font-size: 12px; color: #1e293b; font-weight: bold; text-align: right; }
    .sala-energia { font-size: 10px; color: #6b21a8; font-weight: bold; text-align: right; }
    .total-energia { color: #6b21a8; }
    .obs-sala { font-size: 10px; color: #92400e; font-style: italic; padding: 4px 8px; background: #fef3c7; border-left: 3px solid #f59e0b; margin: 3px 0 6px 0; }
    .obs-item { font-size: 9px; color: #d97706; font-style: italic; }
    .total-geral { text-align: right; margin-top: 16px; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; }
    .total-valor { font-size: 14px; font-weight: bold; color: #1e293b; }
    .no-items { text-align: center; padding: 10px; color: #9ca3af; font-style: italic; font-size: 10px; }
    .evento-grid { width: 100%; margin-bottom: 12px; }
    .evento-grid td { padding: 3px 6px; border: none; }
    .os-badge { font-size: 12px; font-weight: bold; color: #1e293b; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 4px; }
    .doc-title-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
</style>

<div class="doc-title-area">
    <h1><?= htmlspecialchars($evento['nome_evento'] ?: $titulo) ?></h1>
    <?php if (!empty($evento['os_cliente'])): ?>
    <span class="os-badge">OS: <?= htmlspecialchars($evento['os_cliente']) ?></span>
    <?php endif ?>
</div>
<div style="border-bottom: 2px solid #1e293b; margin-bottom: 14px;"></div>

<h2>Dados do Evento</h2>
<table class="evento-grid">
    <tr>
        <td class="label-cell" style="width:10%">Cliente:</td>
        <td style="width:20%"><?= htmlspecialchars($clienteNome ?: '-') ?></td>
        <td class="label-cell" style="width:10%">Comercial:</td>
        <td style="width:20%"><?= htmlspecialchars($produtorNome ?: '-') ?></td>
        <td class="label-cell" style="width:10%">Comprador:</td>
        <td style="width:30%"><?= htmlspecialchars($demandanteNome ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label-cell">Local do Evento:</td>
        <td colspan="5"><?= htmlspecialchars($evento['local_evento'] ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label-cell">Comprador Local:</td>
        <td colspan="2"><?= htmlspecialchars($evento['demandante_local'] ?: '-') ?></td>
        <td class="label-cell">Tel. Comprador:</td>
        <td colspan="3"><?= htmlspecialchars($evento['telefone_demandantelocal'] ?: '-') ?></td>
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

<h2>Salas e Produtos</h2>

<?php foreach ($salaOrdem as $salaKey):
    $salaNome      = ($salaKey === 'sem_sala') ? 'Sem Sala Definida' : ($salaNomes[$salaKey] ?? 'Sala');
    $salaObservacao = ($salaKey === 'sem_sala') ? '' : ($salaObs[$salaKey] ?? '');
    $salaItens     = $itensPorSala[$salaKey] ?? [];
    $salaTotal     = $totaisPorSala[$salaKey] ?? 0;
?>
<table class="sala-header" style="padding: 5px 8px;"><tr><td>
    <div class="sala-nome"><?= htmlspecialchars($salaNome) ?></div>
    <?php if (!$semValores && $salaTotal > 0): ?>
    <div class="sala-total">Total: R$ <?= number_format($salaTotal, 2, ',', '.') ?></div>
    <?php endif ?>
    <?php if (!empty($kwhPorSala[$salaKey])): $salaKva = round($kwhPorSala[$salaKey] * 1.25, 3); ?>
    <div class="sala-energia">Consumo: <?= number_format($kwhPorSala[$salaKey], 3, ',', '.') ?> kWh / <?= number_format($salaKva, 3, ',', '.') ?> kVA</div>
    <?php endif ?>
</td></tr></table>

<?php if (!empty($salaObservacao)): ?>
<div class="obs-sala">
    <strong>Obs. Montagem:</strong> <?= nl2br(htmlspecialchars($salaObservacao)) ?>
</div>
<?php endif ?>

<?php if (!empty($salaItens)):
    $count = 1;
?>
<table style="margin-bottom: 8px;">
    <thead><tr>
        <th style="width:5%" class="text-center">#</th>
        <th style="width:30%">Produto</th>
        <th style="width:18%">Categoria</th>
        <th style="width:7%" class="text-center">Qtd</th>
        <th style="width:7%" class="text-center">Dias</th>
        <?php if (!$semValores): ?>
        <th style="width:10%" class="text-right">Valor Unit.</th>
        <th style="width:10%" class="text-right">Total Venda</th>
        <?php endif ?>
        <?php if ($mostrarCustos): ?>
        <th style="width:10%" class="text-right">Custo</th>
        <?php endif ?>
        <?php if ($temEnergia): ?>
        <th style="width:10%" class="text-right">Consumo</th>
        <?php endif ?>
    </tr></thead>
    <tbody>
    <?php foreach ($salaItens as $item):
        $q = (int)($item['qtd'] ?? 1);
        $v = (float)($item['valor_unit'] ?? 0);
        $c = (float)($item['custo_unit'] ?? 0);
        $d = (int)($item['dias'] ?? 1);
        $totalVenda = $q * $v * $d;
        $potW = (float)($item['potencia_w'] ?? 0);
        $horasU = (float)($item['horas_uso'] ?? 20);
        $kwhItem = $potW > 0 ? round($q * $potW * $horasU / 1000, 3) : 0;
    ?>
    <tr>
        <td class="text-center text-muted"><?= $count ?></td>
        <td><strong><?= htmlspecialchars($item['produto']) ?>
            <?php if (!empty($item['observacao_montagem'])): ?>
            <br><span class="obs-item">Obs: <?= nl2br(htmlspecialchars($item['observacao_montagem'])) ?></span>
            <?php endif ?>
        </strong></td>
        <td><?= htmlspecialchars($item['categoria_nome'] ?? '-') ?></td>
        <td class="text-center"><?= $q ?></td>
        <td class="text-center"><?= $d ?></td>
        <?php if (!$semValores): ?>
        <td class="text-right">R$ <?= number_format($v, 2, ',', '.') ?></td>
        <td class="text-right"><strong>R$ <?= number_format($totalVenda, 2, ',', '.') ?></strong></td>
        <?php endif ?>
        <?php if ($mostrarCustos): ?>
        <td class="text-right"><strong>R$ <?= number_format($c, 2, ',', '.') ?></strong></td>
        <?php endif ?>
        <?php if ($temEnergia): ?>
        <td class="text-right"><?= $potW > 0 ? ($potW . 'W · ' . number_format($kwhItem, 3, ',', '.') . ' kWh') : '-' ?></td>
        <?php endif ?>
    </tr>
    <?php $count++; endforeach ?>
    </tbody>
</table>
<?php else: ?>
<div class="no-items">Nenhum item nesta sala</div>
<?php endif ?>

<?php endforeach ?>

<?php if (!$semValores): ?>
<div class="total-geral">
    Total Venda: <span class="total-valor">R$ <?= number_format($totais['total_venda'], 2, ',', '.') ?></span>
    <?php if ($mostrarCustos && !empty($totais['total_custo'])): ?>
    <br>Total Custo: <span style="color:#f59e0b">R$ <?= number_format($totais['total_custo'], 2, ',', '.') ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if ($temEnergia): ?>
<div class="total-geral">
    Consumo Total de Energia: <span class="total-valor total-energia"><?= number_format($totalKwh, 3, ',', '.') ?> kWh / <?= number_format($totalKva, 3, ',', '.') ?> kVA</span>
</div>
<?php endif ?>

<?= $assinaturaHtml ?>

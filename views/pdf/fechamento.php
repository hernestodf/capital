<?php
// Variables: evento, colaboradores, fornecedores, outros, totais, semValores,
//            clienteNome, produtorNome, demandanteNome, fotos,
//            baseCss, assinaturaHtml, publicDir, baseUrl

echo $baseCss;
?>
<div class="pdf-container">

<div class="header">
    <h1>FECHAMENTO DO EVENTO</h1>
    <h2><?= htmlspecialchars($evento['nome_evento'] ?? '') ?></h2>
</div>

<div class="info-section">
    <h3>Informacoes do Evento</h3>
    <table class="info-table">
        <tr>
            <td><strong>Cliente:</strong></td><td><?= htmlspecialchars($clienteNome) ?></td>
            <td><strong>Comercial:</strong></td><td><?= htmlspecialchars($produtorNome) ?></td>
        </tr>
        <tr>
            <td><strong>Comprador:</strong></td><td><?= htmlspecialchars($demandanteNome) ?></td>
            <td><strong>Periodo:</strong></td><td><?= date('d/m/Y', strtotime($evento['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($evento['data_fim'])) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h3>Colaboradores (<?= count($colaboradores) ?>)</h3>
    <table class="data-table">
        <thead><tr>
            <th>Nome</th><th>Função</th><th>Presenças</th>
            <?php if (!$semValores): ?><th>Valor Diária</th><th>Valor Total</th><?php endif ?>
            <th>Status Pagamento</th>
        </tr></thead>
        <tbody>
        <?php foreach ($colaboradores as $col): ?>
        <tr>
            <td><?= htmlspecialchars($col['nome']) ?></td>
            <td><?= htmlspecialchars($col['funcao']) ?></td>
            <td><?= $col['total_presencas'] ?? 0 ?></td>
            <?php if (!$semValores): ?>
            <td>R$ <?= number_format($col['valor_diaria'] ?? 0, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($col['valor_total'] ?? 0, 2, ',', '.') ?></td>
            <?php endif ?>
            <td><?= $col['enviado_pagamento'] ? 'Enviado' : 'Pendente' ?></td>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php
$fornGrupos = [];
foreach ($fornecedores as $forn) {
    $key = $forn['id_fornecedor'] ?? $forn['nome_fantasia'];
    if (!isset($fornGrupos[$key])) {
        $fornGrupos[$key] = [
            'nome'        => $forn['nome_fantasia'] ?? ($forn['razao_social'] ?? ''),
            'total_itens' => 0,
            'valor_total' => 0.0,
            'status'      => 'Pendente',
        ];
    }
    $fornGrupos[$key]['total_itens']++;
    $fornGrupos[$key]['valor_total'] += (float)($forn['valor_proposto'] ?? $forn['custo_unit'] ?? 0);
    if (!empty($forn['contas_pagar_info'])) {
        $st = $forn['contas_pagar_info'][0]['status'] ?? 'PENDENTE';
        $fornGrupos[$key]['status'] = match($st) {
            'PAGO'    => 'Pago',
            'VENCIDO' => 'Vencido',
            default   => 'Pendente',
        };
    }
}
?>

<div class="section">
    <h3>Fornecedores (<?= count($fornGrupos) ?>)</h3>
    <table class="data-table">
        <thead><tr>
            <th>Fornecedor</th><th>Itens</th>
            <?php if (!$semValores): ?><th>Custo Total</th><?php endif ?>
            <th>Status</th>
        </tr></thead>
        <tbody>
        <?php foreach ($fornGrupos as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['nome']) ?></td>
            <td><?= $g['total_itens'] ?></td>
            <?php if (!$semValores): ?>
            <td>R$ <?= number_format($g['valor_total'], 2, ',', '.') ?></td>
            <?php endif ?>
            <td><?= $g['status'] ?></td>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php if (!empty($outros)): ?>
<div class="section">
    <h3>Outros Custos (<?= count($outros) ?>)</h3>
    <table class="data-table">
        <thead><tr>
            <th>Descricao</th><th>Vencimento</th><th>Status</th>
            <?php if (!$semValores): ?><th>Valor</th><?php endif ?>
        </tr></thead>
        <tbody>
        <?php foreach ($outros as $o):
            $statusLabel = match($o['status'] ?? 'PENDENTE') {
                'PAGO'    => 'Pago',
                'VENCIDO' => 'Vencido',
                default   => 'Pendente',
            };
        ?>
        <tr>
            <td><?= htmlspecialchars($o['descricao'] ?? '') ?></td>
            <td><?= !empty($o['data_vencimento']) ? date('d/m/Y', strtotime($o['data_vencimento'])) : '-' ?></td>
            <td><?= $statusLabel ?></td>
            <?php if (!$semValores): ?><td>R$ <?= number_format((float)($o['valor'] ?? 0), 2, ',', '.') ?></td><?php endif ?>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php endif ?>

<?php if (!$semValores):
    $lucro = $totais['lucro'] ?? 0;
    $lucroColor = $lucro >= 0 ? '#16a34a' : '#dc2626';
?>
<div class="totals-section">
    <h3>Totais Financeiros</h3>
    <div class="totals-grid">
        <div class="total-item"><span class="label">Total Colaboradores:</span><span class="value">R$ <?= number_format($totais['total_colaboradores'] ?? 0, 2, ',', '.') ?></span></div>
        <div class="total-item"><span class="label">Total Fornecedores:</span><span class="value">R$ <?= number_format($totais['total_fornecedores'] ?? 0, 2, ',', '.') ?></span></div>
        <div class="total-item"><span class="label">Outros Custos:</span><span class="value">R$ <?= number_format($totais['total_outros'] ?? 0, 2, ',', '.') ?></span></div>
        <div class="total-item"><span class="label">Custo Total:</span><span class="value" style="color:#dc2626">R$ <?= number_format($totais['custo_total'] ?? 0, 2, ',', '.') ?></span></div>
        <div class="total-item"><span class="label">Total Venda (Receita):</span><span class="value" style="color:#16a34a">R$ <?= number_format($totais['receita'] ?? 0, 2, ',', '.') ?></span></div>
        <div class="total-item"><span class="label">Lucro:</span><span class="value" style="color:<?= $lucroColor ?>">R$ <?= number_format($lucro, 2, ',', '.') ?></span></div>
        <div class="total-item total-final"><span class="label">Margem:</span><span class="value"><?= $totais['margem'] ?? 0 ?>%</span></div>
    </div>
</div>
<?php endif ?>

</div>

<?php if (!empty($fotos['por_sala']) || !empty($fotos['gerais'])):
    $imgSrc = function($caminho) use ($publicDir, $baseUrl) {
        $localPath = $publicDir . '/' . $caminho;
        if ($localPath && file_exists($localPath)) {
            return $localPath;
        }
        return $baseUrl . '/' . $caminho;
    };
?>
<div class="section" style="page-break-before:always">
    <h3>Fotos do Evento</h3>

    <?php if (!empty($fotos['por_sala'])): ?>
        <?php foreach ($fotos['por_sala'] as $sala): ?>
            <?php if (empty($sala['fotos'])) continue; ?>
            <h4 style="margin-top:16px;color:#0b6e8c">Sala: <?= htmlspecialchars($sala['nome_sala']) ?></h4>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px">
                <?php foreach ($sala['fotos'] as $foto): ?>
                <div style="width:240px;margin-bottom:4px">
                    <img src="<?= htmlspecialchars($imgSrc($foto['caminho_arquivo'])) ?>" style="width:240px;height:auto" />
                </div>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    <?php endif ?>

    <?php if (!empty($fotos['gerais'])): ?>
        <h4 style="margin-top:16px;color:#0b6e8c">Fotos Gerais</h4>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px">
            <?php foreach ($fotos['gerais'] as $foto): ?>
            <div style="width:240px;margin-bottom:4px">
                <img src="<?= htmlspecialchars($imgSrc($foto['caminho_arquivo'])) ?>" style="width:240px;height:auto" />
            </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
<?php endif ?>

<?= $assinaturaHtml ?>

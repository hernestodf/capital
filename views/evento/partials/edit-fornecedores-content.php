<?php
// Partial: Conteudo da aba Fornecedores (usado tanto no load inicial quanto via AJAX)
// Variaveis esperadas: $evento, $salas, $produtosPorSala

// Agrupar itens por sala
$itensPorSala = [];
foreach ($salas as $sala) {
    $salaId = $sala['id'];
    $itens = $produtosPorSala[$salaId] ?? [];
    if (!empty($itens)) {
        $itensPorSala[$salaId] = $itens;
    }
}

// Mapear sala_id -> nome_sala
$salaNomes = [];
foreach ($salas as $s) {
    $salaNomes[$s['id']] = $s['nome_sala'];
}

$idEvento = $evento['id'];
?>
<div id="fornecedores-container" style="padding:16px">
    <?php if (empty($itensPorSala)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-4)">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;margin-bottom:12px;opacity:.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <div style="font-size:14px;font-weight:600">Nenhum item adicionado</div>
        <div style="font-size:12px;margin-top:4px">Adicione produtos na aba Salas e Produtos primeiro</div>
    </div>
    <?php else: ?>

    <?php foreach ($itensPorSala as $salaId => $itens): ?>
    <div class="card" style="margin-bottom:16px" data-sala-id="<?= $salaId ?>">
        <div class="card-head">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:18px;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($salaNomes[$salaId] ?? 'Sala') ?></span>
                <span class="badge sm cyan"><?= count($itens) ?> itens</span>
            </div>
        </div>
        <div class="card-body">
            <?php foreach ($itens as $item): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-bottom:1px solid var(--bg-border-sub);<?= $item === end($itens) ? 'border-bottom:none;' : '' ?>"
                 data-produto-evento-id="<?= $item['id'] ?>"
                 data-produto-nome="<?= htmlspecialchars($item['produto']) ?>"
                 data-custo="<?= $item['custo_unit'] ?? 0 ?>">
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:14px;color:var(--text-1)"><?= htmlspecialchars($item['produto']) ?></div>
                    <?php if (!empty($item['quantidade'])): ?>
                    <div style="font-size:12px;color:var(--text-3)">Qtd: <?= (int)$item['quantidade'] ?></div>
                    <?php endif; ?>
                    <?php if (!empty($item['custo_unit']) && $item['custo_unit'] > 0): ?>
                    <div style="font-size:12px;color:var(--green)">Custo: R$ <?= number_format($item['custo_unit'], 2, ',', '.') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($item['fornecedor_vencedor'])): ?>
                    <div style="font-size:12px;color:var(--cyan)">Fornecedor: <?= htmlspecialchars($item['fornecedor_vencedor']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

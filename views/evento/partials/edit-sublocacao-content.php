<?php
// Partial: Conteúdo da aba Sublocação (usado para renderização estática e via AJAX)
// Variaveis esperadas: $evento, $salas, $produtosPorSala

// Agrupar itens por sala
$itensPorSala = [];
foreach ($salas as $sala) {
    $salaId = $sala['id'];
    $salaItens = $produtosPorSala[$salaId] ?? [];
    foreach ($salaItens as $item) {
        $itensPorSala[$salaId][] = $item;
    }
}

$salaNomes = [];
foreach ($salas as $s) {
    $salaNomes[$s['id']] = $s['nome_sala'];
}
?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div>
            <h3 style="margin:0;font-size:18px;font-weight:700">Sublocação</h3>
            <p style="margin:4px 0 0;font-size:13px;color:var(--text-3)">Vincule sublocadores aos itens do evento</p>
        </div>
        <button type="button" class="btn btn-purple" data-action="gerar-contas-sublocacao" id="btn-gerar-contas-subloc">
            <svg style="width:16px;height:16px;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Gerar Contas a Pagar dos Sublocados
        </button>
    </div>

    <?php if (empty($itensPorSala)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-4)">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;margin-bottom:12px;opacity:.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <div style="font-size:14px;font-weight:600">Nenhum item adicionado</div>
        <div style="font-size:12px;margin-top:4px">Adicione produtos na aba Salas e Produtos primeiro</div>
    </div>
    <?php else: ?>

    <?php foreach ($itensPorSala as $salaId => $salaItens): ?>
    <div class="card" style="margin-bottom:16px">
        <div class="card-head">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:18px;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($salaNomes[$salaId] ?? 'Sala') ?></span>
                <span class="badge sm cyan"><?= count($salaItens) ?> itens</span>
            </div>
        </div>
        <div class="card-body">
            <?php foreach ($salaItens as $item): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-bottom:1px solid var(--bg-border-sub);<?= $item === end($salaItens) ? 'border-bottom:none;' : '' ?>"
                 data-item-id="<?= $item['id'] ?>">
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:14px;color:var(--text-1)"><?= htmlspecialchars($item['produto']) ?></div>
                    <div style="font-size:12px;color:var(--text-3)">Qtd: <?= (int)($item['qtd'] ?? 1) ?> | R$ <?= number_format((float)($item['valor_unit'] ?? 0), 2, ',', '.') ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="sala-item-subloc-badge" data-item-id="<?= $item['id'] ?>" style="display:none;font-size:10px;padding:2px 6px;background:rgba(139,92,246,0.12);border-radius:4px;color:rgb(139,92,246);font-weight:600">Carregando...</span>
                    <button type="button" class="btn btn-sm btn-cyan" data-action="abrir-modal-sublocacao" data-produto-evento-id="<?= (int)$item['id'] ?>" data-produto-nome="<?= htmlspecialchars($item['produto'], ENT_QUOTES, 'UTF-8') ?>">Sublocar</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

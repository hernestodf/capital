<?php
// Partial: Salas e Produtos (VTab 1 dentro de Locacoes)
// Variaveis esperadas: $eventoId, $evento, $salas, $categorias, $itens, $produtosPorSala, $grouped, $totais, $csrfToken, $diasEvento
?>

<?php require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/popover/popover.php'; ?>
<?php require_once dirname(__DIR__, 3) . '/docs/layout/branco/assets/components/chip/chip.php'; ?>
<?php require_once dirname(__DIR__) . '/partials/modal-consulta-alocacao.php'; ?>

<?php
// Agrupar itens por sala
$itensPorSala = [];
foreach ($itens as $item) {
    $salaKey = $item['id_sala'] ?? 'sem_sala';
    $itensPorSala[$salaKey][] = $item;
}

// Ordenar itens de cada sala por categoria (sem categoria primeiro, depois alfabetico)
foreach ($itensPorSala as $salaKey => $salaItens) {
    usort($itensPorSala[$salaKey], function($a, $b) {
        $catA = $a['categoria_nome'] ?? '';
        $catB = $b['categoria_nome'] ?? '';
        // Sem categoria vai para o final
        if ($catA === '' && $catB !== '') return 1;
        if ($catB === '' && $catA !== '') return -1;
        // Ordenar por categoria alfabetica
        $cmp = strcasecmp($catA, $catB);
        if ($cmp !== 0) return $cmp;
        // Mesma categoria: ordenar por nome do produto
        return strcasecmp($a['produto'], $b['produto']);
    });
}

// Mapear sala_id -> nome_sala + orientacoes_montagem
$salaNomes = [];
$salaObs = [];
foreach ($salas as $s) {
    $salaNomes[$s['id']] = $s['nome_sala'];
    $salaObs[$s['id']] = !empty($s['orientacoes_montagem']) ? $s['orientacoes_montagem'] : '';
}

// Ordenar: todas as salas primeiro, "Sem Sala" por ultimo
// Inclui TODAS as salas (mesmo sem itens) para mostrar a observacao
$salaOrdem = array_keys($salaNomes);
if (isset($itensPorSala['sem_sala'])) {
    $salaOrdem[] = 'sem_sala';
}
// Garante que todas as salas do $salaNomes estejam em $salaOrdem (mesmo sem itens)
$salaOrdem = array_unique($salaOrdem);

// Cores para salas (ciclo de 6 cores)
$salaCores = [
    'bg' => 'rgba(8,145,178,0.12)',  // cyan suave
    'border' => 'rgba(8,145,178,0.3)',
];
?>

<!-- Resumo Financeiro -->
<style>.evento-totais{grid-template-columns:repeat(4,1fr)!important}.total-card{padding:10px 14px!important}.total-label{font-size:10px!important;margin-bottom:4px!important}.total-value{font-size:17px!important}.total-card-margem{border-left:3px solid var(--neon-cyan)}</style>
<div class="evento-totais">
    <div class="total-card total-card-venda">
        <div class="total-label">Total Venda</div>
        <div class="total-value" id="total-venda" style="color:var(--neon-green)">R$ <?= number_format($totais['total_venda'] ?? 0, 2, ',', '.') ?></div>
    </div>
    <div class="total-card total-card-custo">
        <div class="total-label">Custo</div>
        <div class="total-value" id="total-custo" style="color:var(--neon-red)">R$ <?= number_format($totais['total_custo'] ?? 0, 2, ',', '.') ?></div>
    </div>
    <div class="total-card total-card-margem">
        <div class="total-label">Colaboradores</div>
        <div class="total-value" id="total-colaboradores" style="color:var(--neon-cyan)">Carregando...</div>
    </div>
    <div class="total-card total-card-lucro">
        <div class="total-label">Lucro</div>
        <div class="total-value" id="total-lucro" style="color:<?= ($totais['lucro'] ?? 0) > 0 ? 'var(--neon-green)' : 'var(--neon-red)' ?>">
            R$ <?= number_format($totais['lucro'] ?? 0, 2, ',', '.') ?>
            (<?= $totais['total_venda'] > 0 ? number_format(($totais['lucro'] / $totais['total_venda']) * 100, 1, ',', '.') : '0,0' ?>%)
        </div>
    </div>
</div>
<div style="font-size:11px;color:var(--text-3);margin-top:-8px;margin-bottom:12px;text-align:right">* Custo = custo dos produtos. Colaboradores e fornecedores sao calculados no fechamento</div>

<!-- Formulario de Adicionar Item -->
<div class="custom-card card-with-margin">
    <div class="custom-card-head">
        <div class="custom-card-title">Adicionar Item</div>
    </div>
    <div class="custom-card-body">
        <div class="fg" style="position:relative">
            <div class="fl">Produto</div>
            <div style="display:flex;gap:8px">
                <input type="text" id="item_proposta" class="fi fi-primary" placeholder="Nome do produto..." autocomplete="off" style="flex:1">
                <button type="button" class="btn btn-icon" data-action="open-modal" data-target="modalConsultaAlocacao" title="Consultar alocação" style="padding:8px 12px;flex-shrink:0">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="form-row-4">
            <div class="fg">
                <div class="fl">Qtd</div>
                <input type="text" id="qtd_item" class="fi fi-primary" value="1" inputmode="numeric" pattern="[0-9]*">
            </div>
            <div class="fg">
                <div class="fl">Dias</div>
                <input type="text" id="dias_locacao" class="fi fi-primary" value="<?= $diasEvento ?>" inputmode="numeric" pattern="[0-9]*">
            </div>
            <?= renderInputPrefix(['prefix' => 'R$', 'label' => 'Valor Unit.', 'id' => 'valor_item', 'name' => 'valor_item', 'placeholder' => '0,00', 'extra' => 'inputmode="decimal"']) ?>
            <?= renderInputPrefix(['prefix' => 'R$', 'label' => 'Custo', 'id' => 'valor_custo_fornecedor', 'name' => 'valor_custo_fornecedor', 'placeholder' => '0,00', 'extra' => 'inputmode="decimal"']) ?>
        </div>
        <div class="form-row-4">
            <div class="fg">
                <div class="fl">Sala</div>
                <select id="id_sala" class="fi fi-primary fi-sel">
                    <option value="">Sem Sala</option>
                    <?php foreach ($salas as $sala): ?>
                    <option value="<?= $sala['id'] ?>"><?= htmlspecialchars($sala['nome_sala']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <div class="fl">&nbsp;</div>
                <?= renderButton(['label' => '+ Salas', 'variant' => 'purple', 'extra' => 'btn-block data-action="open-modal" data-target="modalSala"']) ?>
            </div>
            <div class="fg">
                <div class="fl">Categoria</div>
                <div style="display:flex;gap:8px;align-items:center">
                    <select id="id_categoria" class="fi fi-primary fi-sel" style="flex:1;min-width:0">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= renderButton(['label' => '+ Categorias', 'variant' => 'purple', 'extra' => 'data-action="open-modal" data-target="modalCategoria"']) ?>
                </div>
            </div>
        </div>

        <div class="flex-gap-2" style="margin-top:8px">
            <button type="button" class="btn btn-red" id="btn-adicionar-item">
                <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Adicionar Item
            </button>
        </div>
    </div>
</div>

<!-- Lista de Itens por Sala (novo layout) -->
<div class="custom-card">
    <div class="custom-card-body card-no-padding">
        <?php
        // Mostrar apenas salas que tem itens, OU "Sem Sala" se tiver itens soltos
        $salasComItens = [];
        foreach ($salaOrdem as $salaKey) {
            $salaItensCheck = $itensPorSala[$salaKey] ?? [];
            if (!empty($salaItensCheck)) {
                $salasComItens[$salaKey] = $salaItensCheck;
            }
        }
        $mostrarSalas = !empty($salasComItens);
        ?>
        <?php if (!$mostrarSalas): ?>
        <div id="estado-vazio-salas" class="estado-vazio-msg" style="text-align:center;padding:40px;color:var(--text-4)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;margin-bottom:12px;opacity:.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <div style="font-size:14px;font-weight:600">Nenhum item adicionado</div>
            <div style="font-size:12px;margin-top:4px">Use o formulario acima para adicionar itens ao evento</div>
        </div>
        <?php else: ?>
        <?php $contador = 1; foreach ($salaOrdem as $salaKey): ?>
            <?php
            // Pular salas sem itens
            $salaItens = $itensPorSala[$salaKey] ?? [];
            if (empty($salaItens)) continue;

            $salaNome = ($salaKey === 'sem_sala') ? 'Sem Sala' : ($salaNomes[$salaKey] ?? 'Sala');
            $salaObservacao = ($salaKey === 'sem_sala') ? '' : ($salaObs[$salaKey] ?? '');

            // Calcular totais da sala
            $salaVenda = 0; $salaCusto = 0; $salaKwh = 0;
            foreach ($salaItens as $si) {
                $q = (float)($si['qtd'] ?? 1);
                $v = (float)($si['valor_unit'] ?? 0);
                $c = (float)($si['custo_unit'] ?? 0);
                $d = (int)($si['dias'] ?? 1);
                $pw = (float)($si['potencia_w'] ?? 0);
                $hu = (float)($si['horas_uso'] ?? 20);
                $salaVenda += $q * $v * $d;
                $salaCusto += $c;
                if ($pw > 0) $salaKwh += $q * $pw * $hu / 1000;
            }
            $salaLucro  = $salaVenda - $salaCusto;
            $salaMargem = $salaVenda > 0 ? ($salaLucro / $salaVenda * 100) : 0;
            $salaKva    = $salaKwh * 1.25;
            ?>

            <!-- BLOCO DA SALA -->
            <div class="sala-bloco" data-sala-id="<?= htmlspecialchars($salaKey) ?>">

                <!-- Header da Sala: nome + botao editar a esquerda, totais a direita -->
                <div class="sala-header">
                    <div class="sala-header-left">
                        <span class="sala-nome"><?= htmlspecialchars($salaNome) ?></span>
                        <?php if ($salaKey !== 'sem_sala'): ?>
                        <button type="button" class="btn btn-icon-xs btn-purple" data-tip="Editar Sala" data-action="editar-sala" data-sala-key="<?= (int)$salaKey ?>" data-sala-nome="<?= htmlspecialchars($salaNome, ENT_QUOTES, 'UTF-8') ?>" data-sala-obs="<?= htmlspecialchars($salaObservacao ?? '', ENT_QUOTES, 'UTF-8') ?>" title="Editar Sala">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="sala-header-right">
                        <?= renderTag(['label' => 'Venda: R$ ' . number_format($salaVenda, 2, ',', '.'), 'variant' => 'cyan']) ?>
                        <?= renderTag(['label' => 'Custo: R$ ' . number_format($salaCusto, 2, ',', '.'), 'variant' => 'red']) ?>
                        <?= renderTag(['label' => 'Lucro: R$ ' . number_format($salaLucro, 2, ',', '.') . ' (' . number_format($salaMargem, 1, ',', '.') . '%)', 'variant' => $salaLucro >= 0 ? 'green' : 'red']) ?>
                        <?php if ($salaKwh > 0): ?>
                        <?= renderTag(['label' => number_format($salaKwh, 2, ',', '.') . ' kWh / ' . number_format($salaKva, 2, ',', '.') . ' kVA', 'variant' => 'purple']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Observacao de Montagem (linha separada, se existir) -->
                <?php if (!empty($salaObservacao)): ?>
                <div class="sala-obs-row">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;flex-shrink:0;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span><?= htmlspecialchars($salaObservacao) ?></span>
                </div>
                <?php endif; ?>

                <!-- Itens da Sala -->
                <div class="sala-itens-wrap">
                    <?php if (empty($salaItens)): ?>
                    <div style="text-align:center;padding:16px;color:var(--text-4);font-size:12px">
                        Nenhum item nesta sala
                    </div>
                    <?php endif; ?>
                    <?php foreach ($salaItens as $item): ?>
                     <?php $itemObs = $item['observacao_montagem'] ?? ''; ?>
                     <?php
                     $potW   = (float)($item['potencia_w'] ?? 0);
                     $horasU = (float)($item['horas_uso'] ?? 20);
                     $qtdI   = (float)($item['qtd'] ?? 1);
                     $kwh    = $potW > 0 ? round($qtdI * $potW * $horasU / 1000, 3) : 0;
                     $kva    = $kwh > 0 ? round($kwh * 1.25, 3) : 0;
                     ?>
                    <div class="sala-item" data-item-id="<?= $item['id'] ?>">

                        <!-- Linha 1: Nome do item + badges + botoes de acao 16x16 -->
                        <div class="sala-item-top">
                            <div class="sala-item-info">
                                <span class="sala-item-nome"><?= htmlspecialchars($item['produto']) ?></span>

                                <?php if (!empty($item['categoria_nome'])): ?>
                                <span class="sala-item-categoria"><?= htmlspecialchars($item['categoria_nome']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($itemObs)): ?>
                                <span class="sala-item-obs-badge" data-action="editar-obs-item" data-item-id="<?= (int)$item['id'] ?>" data-item-produto="<?= htmlspecialchars($item['produto'], ENT_QUOTES, 'UTF-8') ?>" data-item-obs="<?= htmlspecialchars($itemObs, ENT_QUOTES, 'UTF-8') ?>" title="Ver observacao">Obs</span>
                                <?php endif; ?>
                                <?php
                                $desc = $item['planilha_descricao'] ?? '';
                                if (!empty($desc)):
                                    echo renderPopover([
                                        'id' => 'pop-item-' . $item['id'],
                                        'trigger' => '<button type="button" class="btn btn-icon-xs btn-blue" data-tip="Detalhes do Item" title="Ver Detalhes"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>',
                                        'title' => htmlspecialchars($item['produto']),
                                        'content' => '<div class="popover-text">' . nl2br(htmlspecialchars($desc)) . '</div>',
                                        'position' => 'bottom',
                                        'minWidth' => '280px',
                                        'extraClass' => 'popover-sala',
                                    ]);
                                endif; ?>
                            </div>
                            <div class="sala-item-actions">
                                <button type="button" class="btn btn-icon-xs <?= $potW > 0 ? 'btn-yellow' : 'btn-outline' ?> btn-energia-toggle" data-tip="Consumo de Energia" title="Consumo de Energia" data-action="toggle-energia-item">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </button>
                                <button type="button" class="btn btn-icon-xs btn-cyan" data-tip="Observacao de Montagem" data-action="editar-obs-item" data-item-id="<?= (int)$item['id'] ?>" data-item-produto="<?= htmlspecialchars($item['produto'], ENT_QUOTES, 'UTF-8') ?>" data-item-obs="<?= htmlspecialchars($itemObs ?? '', ENT_QUOTES, 'UTF-8') ?>" title="Observacao de Montagem">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button" class="btn btn-icon-xs btn-red" data-tip="Excluir Item" data-delete-item="<?= $item['id'] ?>" title="Excluir Item">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Linha 2: Campos editaveis (Qtd, Valor Unit., Dias, Total Item, Custo Un.) -->
                        <div class="sala-item-fields">
                            <div class="sala-field-group sala-field-qtd">
                                <label class="sala-field-label">Qtd</label>
                                <input type="text" class="fi fi-sm quantidade-item-input" value="<?= (int)$item['qtd'] ?>" data-item-id="<?= $item['id'] ?>">
                            </div>
                            <div class="sala-field-group sala-field-valor">
                                <label class="sala-field-label">Valor Unit.</label>
                                <div class="fi-prefix-group">
                                    <span class="fi-prefix">R$</span>
                                     <input type="text" class="fi fi-sm valor-item-input" value="<?= number_format($item['valor_unit'] ?? 0, 2, ',', '.') ?>" data-item-id="<?= $item['id'] ?>" oninput="if(window.mascaraMoeda)mascaraMoeda(this)">
                                </div>
                            </div>
                            <div class="sala-field-group sala-field-dias">
                                <label class="sala-field-label">Dias</label>
                                <input type="text" class="fi fi-sm dias-locacao-input" value="<?= (int)$item['dias'] ?>" data-item-id="<?= $item['id'] ?>">
                            </div>
                            <div class="sala-field-group sala-field-total">
                                <label class="sala-field-label">Total Item</label>
                                <?= renderTag(['label' => 'R$ ' . number_format(($item['qtd'] ?? 1) * ($item['valor_unit'] ?? 0) * ($item['dias'] ?? 1), 2, ',', '.'), 'variant' => 'green']) ?>
                            </div>
                            <div class="sala-field-group sala-field-custo">
                                <label class="sala-field-label">Custo Total</label>
                                <div class="fi-prefix-group">
                                    <span class="fi-prefix">R$</span>
                                     <input type="text" class="fi fi-sm custo-fornecedor" value="<?= number_format($item['custo_unit'] ?? 0, 2, ',', '.') ?>" data-item-id="<?= $item['id'] ?>" oninput="if(window.mascaraMoeda)mascaraMoeda(this)">
                                </div>
                            </div>
                        </div>

                        <!-- Linha 3: Energia (oculta quando potencia_w = 0) -->
                        <div class="sala-item-fields sala-item-energia" style="<?= $potW > 0 ? '' : 'display:none' ?>" data-energia>
                            <div class="sala-field-group">
                                <label class="sala-field-label">Potência (W)</label>
                                <div class="fi-prefix-group">
                                    <span class="fi-prefix">W</span>
                                    <input type="number" class="fi fi-sm potencia-w-input" min="0" step="1"
                                           value="<?= $potW > 0 ? (int)$potW : '' ?>"
                                           placeholder="0"
                                           data-item-id="<?= $item['id'] ?>">
                                </div>
                            </div>
                            <div class="sala-field-group">
                                <label class="sala-field-label">Horas de Uso</label>
                                <div class="fi-prefix-group">
                                    <span class="fi-prefix">h</span>
                                    <input type="number" class="fi fi-sm horas-uso-input" min="0" step="0.5"
                                           value="<?= $horasU ?>"
                                           data-item-id="<?= $item['id'] ?>">
                                </div>
                            </div>
                            <div class="sala-field-group">
                                <label class="sala-field-label">Consumo (kWh)</label>
                                <span class="fi fi-sm energia-kwh-display" style="background:rgba(6,182,212,0.08);color:var(--neon-cyan);font-weight:600;display:flex;align-items:center;min-width:70px"><?= $kwh > 0 ? number_format($kwh,3,',','.') : '—' ?></span>
                            </div>
                            <div class="sala-field-group">
                                <label class="sala-field-label">kVA</label>
                                <span class="fi fi-sm energia-kva-display" style="background:rgba(168,85,247,0.08);color:var(--neon-purple);font-weight:600;display:flex;align-items:center;min-width:70px"><?= $kva > 0 ? number_format($kva,3,',','.') : '—' ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
            <!-- /BLOCO DA SALA -->

        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Total Global de Energia -->
<div id="energia-total-global" style="display:none;margin-top:16px;padding:14px 18px;background:rgba(168,85,247,0.07);border:1px solid rgba(168,85,247,0.2);border-radius:10px">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <svg fill="none" viewBox="0 0 24 24" stroke="var(--neon-purple)" stroke-width="2" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span style="font-weight:700;color:var(--neon-purple);font-size:13px">Consumo Total de Energia</span>
        <span class="tag purple" id="energia-total-kwh">0 kWh</span>
        <span class="tag purple" id="energia-total-kva">0 kVA</span>
        <span style="font-size:11px;color:var(--text-3);margin-left:4px">(fator de potência 0,8 — fp=0,8 → kVA = kWh × 1,25)</span>
    </div>
</div>

<!-- Botoes de Impressao -->
<?php
$estado = $evento['estado'] ?? 'O';
$isLocacao = $estado === 'L';
$titulo = $isLocacao ? 'Locacao' : 'Orcamento';
$iconSvg = '<svg style="width:16px;height:16px;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m0-8v8"/></svg>';
?>
<div class="flex-gap-2-end mb-3" style="margin-top:16px;gap:6px">
    <a href="<?= $baseUrl ?>/eventos/pdf/<?= $eventoId ?>?sem_valores=1" target="_blank" class="btn btn-sm btn-red">
        <?= $iconSvg ?>
        <?= $titulo ?>: Sem Valores
    </a>
    <a href="<?= $baseUrl ?>/eventos/pdf/<?= $eventoId ?>" target="_blank" class="btn btn-sm btn-green">
        <?= $iconSvg ?>
        <?= $titulo ?>: Com Valores
    </a>
    <a href="<?= $baseUrl ?>/eventos/pdf/<?= $eventoId ?>?com_custos=1" target="_blank" class="btn btn-sm btn-orange">
        <?= $iconSvg ?>
        Locação com custos e valores
    </a>
</div>

<!-- Modal Gerenciar Salas -->
<?php
$salaListHtml = '';
if (empty($salas)) {
    $salaListHtml = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma sala cadastrada</div>';
} else {
    $salaListHtml = '<table style="width:100%;border-collapse:collapse">';
    $salaListHtml .= '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
    $salaListHtml .= '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
    $salaListHtml .= '<th style="padding:8px;font-size:12px;color:var(--text-3)">Obs. Montagem</th>';
    $salaListHtml .= '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
    $salaListHtml .= '</tr></thead><tbody>';
    foreach ($salas as $sala) {
        $salaListHtml .= '<tr style="border-bottom:1px solid var(--bg-border)">';
        $salaListHtml .= '<td style="padding:8px;font-size:13px">' . htmlspecialchars($sala['nome_sala']) . '</td>';
        $salaListHtml .= '<td style="padding:8px;font-size:13px;color:var(--text-3)">' . htmlspecialchars($sala['orientacoes_montagem'] ?? '') . '</td>';
        $salaListHtml .= '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
        $salaListHtml .= '<button type="button" class="btn btn-xs btn-purple" data-action="editar-sala-modal" data-sala-id="' . $sala['id'] . '" data-sala-nome="' . htmlspecialchars($sala['nome_sala'], ENT_QUOTES, 'UTF-8') . '" data-sala-obs="' . htmlspecialchars($sala['orientacoes_montagem'] ?? '', ENT_QUOTES, 'UTF-8') . '" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
        $salaListHtml .= '<button type="button" class="btn btn-xs btn-red" data-action="excluir-sala" data-sala-id="' . $sala['id'] . '" data-sala-nome="' . htmlspecialchars($sala['nome_sala'], ENT_QUOTES, 'UTF-8') . '" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
        $salaListHtml .= '</div></td></tr>';
    }
    $salaListHtml .= '</tbody></table>';
}
echo renderModal([
    'id' => 'modalSala',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Gerenciar Salas',
    'subtitle' => 'Adicionar, editar ou excluir salas',
    'body' => '<div id="modal-sala-list" style="max-height:400px;overflow-y:auto;margin-bottom:16px">' . $salaListHtml . '</div>
               <div class="divider" style="margin:12px 0"></div>
               <input type="hidden" id="editando_sala_id" value="">
               <div class="col2">
                   <div class="fg"><div class="fl">Nome da Sala <span class="req">*</span></div><input type="text" id="nome_sala" class="fi" placeholder="Nome da sala..."></div>
                   <div class="fg"><div class="fl">Obs. Montagem</div><textarea id="observacao_sala" class="fi" rows="3" placeholder="Observacoes (opcional)"></textarea></div>
               </div>',
    'footer' => '<button class="btn btn-red" data-action="close-modal" data-target="modalSala">Fechar</button><button class="btn btn-cyan" data-action="salvar-sala"><span id="btnSalvarSalaText">Salvar Sala</span></button><button class="btn btn-red btn-sm" data-action="cancelar-edicao-sala" id="btnCancelarEdicaoSala" style="display:none">Cancelar Edicao</button>'
]);
?>
<style>#modalSala .modal{max-width:1000px}</style>

<!-- Modal Gerenciar Categorias -->
<?php
$catListHtml = '';
if (empty($categorias)) {
    $catListHtml = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma categoria cadastrada</div>';
} else {
    $catListHtml = '<table style="width:100%;border-collapse:collapse">';
    $catListHtml .= '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
    $catListHtml .= '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
    $catListHtml .= '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right;width:140px">Acoes</th>';
    $catListHtml .= '</tr></thead><tbody>';
    foreach ($categorias as $cat) {
        $catListHtml .= '<tr style="border-bottom:1px solid var(--bg-border)">';
        $catListHtml .= '<td style="padding:8px;font-size:13px">' . htmlspecialchars($cat['nome_categoria']) . '</td>';
        $catListHtml .= '<td style="padding:8px;text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">';
        $catListHtml .= '<button type="button" class="btn btn-xs btn-purple" data-action="editar-categoria-modal" data-categoria-id="' . $cat['id'] . '" data-categoria-nome="' . htmlspecialchars($cat['nome_categoria'], ENT_QUOTES, 'UTF-8') . '" title="Editar"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>';
        $catListHtml .= '<button type="button" class="btn btn-xs btn-red" data-action="excluir-categoria" data-categoria-id="' . $cat['id'] . '" data-categoria-nome="' . htmlspecialchars($cat['nome_categoria'], ENT_QUOTES, 'UTF-8') . '" title="Excluir"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
        $catListHtml .= '</div></td></tr>';
    }
    $catListHtml .= '</tbody></table>';
}
echo renderModal([
    'id' => 'modalCategoria',
    'variant' => 'form',
    'size' => 'lg',
    'title' => 'Gerenciar Categorias',
    'subtitle' => 'Adicionar, editar ou excluir categorias',
    'body' => '<div id="modal-categoria-list" style="max-height:400px;overflow-y:auto;margin-bottom:16px">' . $catListHtml . '</div>
               <div class="divider" style="margin:12px 0"></div>
               <input type="hidden" id="editando_categoria_id" value="">
               <div class="fg"><div class="fl">Nome da Categoria <span class="req">*</span></div><input type="text" id="nome_categoria" class="fi" placeholder="Ex: Iluminacao, Sonorizacao, Estrutura..."></div>',
    'footer' => '<button class="btn btn-red" data-action="close-modal" data-target="modalCategoria">Fechar</button><button class="btn btn-cyan" data-action="salvar-categoria"><span id="btnSalvarCategoriaText">Salvar Categoria</span></button><button class="btn btn-red btn-sm" data-action="cancelar-edicao-categoria" id="btnCancelarEdicaoCategoria" style="display:none">Cancelar Edicao</button>'
]);
?>
<style>#modalCategoria .modal{max-width:1000px}</style>

<!-- Modal Observacao do Item -->
<div class="modal-overlay" id="modal-item-obs" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Observacao de Montagem</div>
            <button type="button" class="close-x" data-action="fechar-modal-item-obs">x</button>
        </div>
        <div class="modal-body">
            <div class="font-bold text-sm text-text-1 mb-3" id="modal-item-name"></div>
            <div class="fg">
                <div class="fl">Observacao</div>
                <textarea id="observacao_item" class="fi" rows="4" placeholder="Descreva detalhes de montagem, posicionamento, configuracoes especiais..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-red" data-action="fechar-modal-item-obs">Cancelar</button>
            <button type="button" class="btn btn-cyan" id="btnSalvarItemObs" data-action="salvar-obs-item">Salvar</button>
        </div>
    </div>
</div>

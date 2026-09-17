<?php
/**
 * Componente: Lista de Salas com Produtos
 * Exibe salas agrupadas com seus produtos e calculos financeiros
 */

function renderListaSalas($config = []) {
    $idEvento = $config['id_evento'] ?? 0;
    $salas = $config['salas'] ?? [];
    $produtosPorSala = $config['produtos_por_sala'] ?? [];
    $grouped = $config['grouped'] ?? [];

    if (empty($salas)) {
        return '<div style="text-align:center;padding:40px;color:var(--text-3)">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:48px;height:48px;opacity:0.5;margin-bottom:12px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <div style="font-size:15px;font-weight:600;margin-bottom:4px">Nenhuma sala cadastrada</div>
            <div style="font-size:13px">Clique em "Nova Sala" para comecar a adicionar produtos</div>
        </div>';
    }

    $html = '<div id="lista-salas">';

    foreach ($salas as $sala) {
        $salaId = $sala['id'];
        $salaProdutos = $produtosPorSala[$salaId] ?? [];
        $salaGrouped = null;
        foreach ($grouped as $g) {
            if ($g['id_sala'] == $salaId) {
                $salaGrouped = $g;
                break;
            }
        }

        $totalVenda = $salaGrouped ? (float) $salaGrouped['total_venda'] : 0;
        $totalCusto = $salaGrouped ? (float) $salaGrouped['total_custo'] : 0;
        $lucro = $salaGrouped ? (float) $salaGrouped['lucro'] : 0;
        $lucroColor = $lucro >= 0 ? 'var(--green)' : 'var(--red)';

        // Card da sala
        $html .= '<div class="card" style="margin-bottom:16px" data-sala-id="' . $salaId . '">';
        $html .= '<div class="card-head">';

        // Nome da sala com badge de categoria
        $html .= '<div style="display:flex;align-items:center;gap:12px">';
        $html .= '<div class="card-title">' . htmlspecialchars($sala['nome_sala']);
        if (!empty($sala['nome_categoria'])) {
            $html .= ' <span style="font-size:12px;color:var(--text-3);font-weight:400">(' . htmlspecialchars($sala['nome_categoria']) . ')</span>';
        }
        $html .= '</div>';
        $html .= '</div>';

        // Botoes de acao
        $html .= '<div style="display:flex;gap:6px">';
        $html .= '<button type="button" class="btn btn-sm btn-cyan" onclick="abrirModalNovoProduto(' . $salaId . ')">+ Item</button>';
        $html .= '<button type="button" class="btn btn-sm btn-blue" onclick="editarSala(' . $salaId . ')">Editar Sala</button>';
        $html .= '<button type="button" class="btn btn-sm btn-red" onclick="deletarSala(' . $salaId . ')">Excluir</button>';
        $html .= '</div>';

        $html .= '</div>';

        // Orientacoes de montagem (inline editavel)
        if (!empty($sala['orientacoes_montagem'])) {
            $html .= '<div style="padding:8px 16px;border-top:1px solid var(--bg-border-sub);display:flex;align-items:start;gap:8px">';
            $html .= '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;color:var(--text-3);flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            $html .= '<div style="flex:1">';
            $html .= '<div style="font-size:12px;color:var(--text-3);margin-bottom:4px">Orientacoes de Montagem</div>';
            $html .= '<div class="sala-orientacoes" contenteditable="true" data-sala-id="' . $salaId . '" onblur="salvarOrientacoes(' . $salaId . ', this)">' . htmlspecialchars($sala['orientacoes_montagem']) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div style="padding:8px 16px;border-top:1px solid var(--bg-border-sub);display:flex;align-items:start;gap:8px">';
            $html .= '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;color:var(--text-3);flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
            $html .= '<div style="flex:1">';
            $html .= '<div class="sala-orientacoes" contenteditable="true" data-sala-id="' . $salaId . '" onblur="salvarOrientacoes(' . $salaId . ', this)" style="color:var(--text-3)">Clique para adicionar orientacoes de montagem...</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        // Tabela de produtos
        if (!empty($salaProdutos)) {
            $html .= '<div style="overflow-x:auto">';
            $html .= '<table class="table" style="width:100%;font-size:13px">';
            $html .= '<thead><tr>';
            $html .= '<th style="padding:10px 16px;text-align:left">Produto</th>';
            $html .= '<th style="padding:10px 16px;text-align:center;width:80px">Qtd</th>';
            $html .= '<th style="padding:10px 16px;text-align:right;width:120px">Valor Un.</th>';
            $html .= '<th style="padding:10px 16px;text-align:center;width:60px">Dias</th>';
            $html .= '<th style="padding:10px 16px;text-align:right;width:120px">Total</th>';
            $html .= '<th style="padding:10px 16px;text-align:right;width:100px">Custo</th>';
            $html .= '<th style="padding:10px 16px;text-align:center;width:120px">Acoes</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($salaProdutos as $prod) {
                $totalItem = (float) $prod['total_item'];
                $html .= '<tr data-produto-id="' . $prod['id'] . '">';
                $html .= '<td style="padding:10px 16px">';
                $html .= htmlspecialchars($prod['produto']);
                if (!empty($prod['planilha_descricao'])) {
                    $html .= ' <span class="popover-trigger" data-popover="' . htmlspecialchars($prod['planilha_descricao']) . '" style="cursor:help;color:var(--text-3);font-size:11px">[?]</span>';
                }
                if (!empty($prod['observacao_montagem'])) {
                    $html .= '<div style="font-size:11px;color:var(--text-3);margin-top:4px">Obs: ' . htmlspecialchars($prod['observacao_montagem']) . '</div>';
                }
                $html .= '</td>';
                $html .= '<td style="padding:10px 16px;text-align:center">' . $prod['qtd'] . '</td>';
                $html .= '<td style="padding:10px 16px;text-align:right">R$ ' . number_format($prod['valor_unit'], 2, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 16px;text-align:center">' . $prod['dias'] . '</td>';
                $html .= '<td style="padding:10px 16px;text-align:right;font-weight:600">R$ ' . number_format($totalItem, 2, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 16px;text-align:right">R$ ' . number_format($prod['custo_unit'], 2, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 16px;text-align:center">';
                $html .= '<button type="button" class="btn btn-sm btn-blue" onclick="editarItem(' . $prod['id'] . ')">Editar</button> ';
                $html .= '<button type="button" class="btn btn-sm btn-red" onclick="deletarItem(' . $prod['id'] . ')">Excluir</button>';
                $html .= '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';

            // Somatorio da sala
            $html .= '<div style="padding:10px 16px;border-top:1px solid var(--bg-border-sub);display:flex;gap:24px;font-size:13px;background:var(--bg-secondary)">';
            $html .= '<div><span style="color:var(--text-3)">Venda:</span> <strong>R$ ' . number_format($totalVenda, 2, ',', '.') . '</strong></div>';
            $html .= '<div><span style="color:var(--text-3)">Custo:</span> <strong style="color:var(--red)">R$ ' . number_format($totalCusto, 2, ',', '.') . '</strong></div>';
            $html .= '<div><span style="color:var(--text-3)">Lucro:</span> <strong style="color:' . $lucroColor . '">R$ ' . number_format($lucro, 2, ',', '.') . '</strong>';
            if ($totalVenda > 0) {
                $margem = ($lucro / $totalVenda) * 100;
                $html .= ' (' . number_format($margem, 1) . '%)';
            }
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div style="padding:24px;text-align:center;color:var(--text-3);font-size:13px">';
            $html .= 'Nenhum item adicionado. Clique em "+ Item" para adicionar produtos.';
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

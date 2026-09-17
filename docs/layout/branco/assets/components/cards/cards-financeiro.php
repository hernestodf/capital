<?php
/**
 * Componente: Cards Financeiros
 * Exibe total de venda, custo e lucro com porcentagens
 */

function renderCardsFinanceiro($config = []) {
    $totais = $config['totais'] ?? ['total_venda' => 0, 'total_custo' => 0, 'lucro' => 0, 'total_itens' => 0];

    $totalVenda = (float) $totais['total_venda'];
    $totalCusto = (float) $totais['total_custo'];
    $lucro = (float) $totais['lucro'];
    $totalItens = (int) $totais['total_itens'];

    // Calcula porcentagens
    $margemLucro = $totalVenda > 0 ? (($lucro / $totalVenda) * 100) : 0;
    $markupCusto = $totalCusto > 0 ? (($lucro / $totalCusto) * 100) : 0;

    // Define cores baseado no lucro/prejuizo
    $lucroColor = $lucro >= 0 ? 'var(--green)' : 'var(--red)';

    $html = '<div id="cards-financeiro" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">';

    // Card Total Venda
    $html .= '<div class="card-stat">';
    $html .= '<div class="card-stat-val">R$ ' . number_format($totalVenda, 2, ',', '.') . '</div>';
    $html .= '<div class="card-stat-lbl">Total Venda</div>';
    $html .= '</div>';

    // Card Total Custo
    $html .= '<div class="card-stat" style="--stat-color:var(--red)">';
    $html .= '<div class="card-stat-val" style="color:var(--red)">R$ ' . number_format($totalCusto, 2, ',', '.') . '</div>';
    $html .= '<div class="card-stat-lbl">Total Custo</div>';
    $html .= '</div>';

    // Card Lucro
    $html .= '<div class="card-stat" style="--stat-color:' . $lucroColor . '">';
    $html .= '<div class="card-stat-val" style="color:' . $lucroColor . '">R$ ' . number_format($lucro, 2, ',', '.') . '</div>';
    $html .= '<div class="card-stat-lbl">Lucro' . ($margemLucro != 0 ? ' (' . number_format($margemLucro, 1) . '%)' : '') . '</div>';
    $html .= '</div>';

    $html .= '</div>';

    // CSS responsivo
    $html .= '<style>';
    $html .= '@media (max-width:1024px){#cards-financeiro{grid-template-columns:repeat(2,1fr)!important}}';
    $html .= '@media (max-width:640px){#cards-financeiro{grid-template-columns:1fr!important}}';
    $html .= '</style>';

    return $html;
}

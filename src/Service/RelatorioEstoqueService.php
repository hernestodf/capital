<?php

namespace App\Service;

use App\Database\Connection;
use Mpdf\Mpdf;

class RelatorioEstoqueService
{
    private PdfGeneratorService $pdfService;
    private Mpdf $mpdf;

    public function __construct()
    {
        // Usar servico compartilhado para mPDF, empresa, header e footer
        $this->pdfService = new PdfGeneratorService();
        $this->mpdf = $this->pdfService->getMpdf();
    }

    public function getTiposRelatorio(): array
    {
        return [
            'geral' => [
                'id' => 'geral',
                'nome' => 'Relatorio Geral de Estoque',
                'descricao' => 'Todos os produtos com resumo de codigos de barras (ativos, manutencao, vender)',
                'icon' => 'inventory',
                'cor' => '#06b6d4',
            ],
            'por_secao' => [
                'id' => 'por_secao',
                'nome' => 'Produtos por Secao',
                'descricao' => 'Lista produtos de uma secao especifica com seus codigos de barras',
                'icon' => 'section',
                'cor' => '#16a34a',
            ],
            'manutencao' => [
                'id' => 'manutencao',
                'nome' => 'Equipamentos em Manutencao',
                'descricao' => 'Todos os codigos de barras com status MANUTENCAO e motivo',
                'icon' => 'warning',
                'cor' => '#ca8a04',
            ],
            'vender' => [
                'id' => 'vender',
                'nome' => 'Equipamentos para Vender',
                'descricao' => 'Todos os codigos de barras marcados para venda',
                'icon' => 'sell',
                'cor' => '#0891b2',
            ],
            'por_produto' => [
                'id' => 'por_produto',
                'nome' => 'Códigos de Barras por Produto',
                'descricao' => 'Detalhe completo de um produto com todos seus codigos de barras',
                'icon' => 'product',
                'cor' => '#7c3aed',
            ],
        ];
    }

    public function getSecoes(): array
    {
        try {
            $stmt = Connection::get()->query("SELECT id, secao FROM secao ORDER BY secao");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProdutos(): array
    {
        try {
            $stmt = Connection::get()->query("SELECT id, produto FROM produtos ORDER BY produto");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function buscarGeral(string $status = ''): array
    {
        $where = '';
        $params = [];

        if (!empty($status) && $status !== 'todos') {
            $where = "WHERE sp.status = ?";
            $params = [$status];
        }

        $sql = "SELECT p.id as produto_id, p.produto, p.custo, p.observacao, s.secao as secao_nome,
                       sp.id as serial_id, sp.serial, sp.status, sp.motivo
                FROM produtos p 
                LEFT JOIN secao s ON p.id_secao = s.id 
                LEFT JOIN seriaisproduto sp ON p.id = sp.id_produto
                {$where}
                ORDER BY p.produto, sp.serial";

        $stmt = Connection::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorSecao(int $idSecao, string $status = ''): array
    {
        $where = "WHERE p.id_secao = ?";
        $params = [$idSecao];

        if (!empty($status) && $status !== 'todos') {
            $where .= " AND sp.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT p.id as produto_id, p.produto, p.custo, p.observacao, s.secao as secao_nome,
                       sp.id as serial_id, sp.serial, sp.status, sp.motivo
                FROM produtos p 
                LEFT JOIN secao s ON p.id_secao = s.id 
                LEFT JOIN seriaisproduto sp ON p.id = sp.id_produto
                {$where}
                ORDER BY p.produto, sp.serial";

        $stmt = Connection::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorStatus(string $status): array
    {
        $sql = "SELECT p.id as produto_id, p.produto, p.custo, p.observacao, s.secao as secao_nome,
                       sp.id as serial_id, sp.serial, sp.status, sp.motivo
                FROM seriaisproduto sp
                LEFT JOIN produtos p ON sp.id_produto = p.id
                LEFT JOIN secao s ON p.id_secao = s.id
                WHERE sp.status = ?
                ORDER BY p.produto, sp.serial";

        $stmt = Connection::get()->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorProduto(int $idProduto, string $status = ''): array
    {
        $where = "WHERE sp.id_produto = ?";
        $params = [$idProduto];

        if (!empty($status) && $status !== 'todos') {
            $where .= " AND sp.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT p.id as produto_id, p.produto, p.custo, p.observacao, p.pode_ser_locado, s.secao as secao_nome,
                       sp.id as serial_id, sp.serial, sp.status, sp.motivo
                FROM seriaisproduto sp
                LEFT JOIN produtos p ON sp.id_produto = p.id
                LEFT JOIN secao s ON p.id_secao = s.id
                {$where}
                ORDER BY sp.serial";

        $stmt = Connection::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function gerarPDF(array $dados, string $tipo, array $filtros = []): void
    {
        $html = '';

        // Titulo do relatorio
        $tipos = $this->getTiposRelatorio();
        $titulo = $tipos[$tipo]['nome'] ?? 'Relatorio de Estoque';
        $html .= '<h1 style="font-size:22px; color:#333; margin:20px 0 10px 0; border-left: 4px solid #06b6d4; padding-left: 12px;">' . htmlspecialchars($titulo) . '</h1>';

        // Filtros aplicados
        if (!empty($filtros)) {
            $html .= '<div style="margin-bottom:20px; padding:12px; background:#f9fafb; border-left:3px solid #06b6d4; font-size:12px; color:#666;">';
            $html .= '<strong>Filtros aplicados:</strong><br>';
            foreach ($filtros as $label => $valor) {
                $html .= htmlspecialchars($label) . ': <strong>' . htmlspecialchars($valor) . '</strong><br>';
            }
            $html .= '</div>';
        }

        // Resumo estatistico
        $resumo = $this->gerarResumo($dados, $tipo);
        if (!empty($resumo)) {
            $html .= $resumo;
        }

        // Tabela principal
        $html .= $this->gerarTabela($dados, $tipo);

        // Configurar header e footer padrao
        $this->pdfService->setupHeaderFooter();
        
        // Gerar PDF
        $nomeArquivo = 'relatorio_estoque_' . $tipo . '_' . date('Y-m-d_His');
        $this->pdfService->download($html, $nomeArquivo);
    }

    private function gerarResumo(array $dados, string $tipo): string
    {
        $totalSeriais = 0;
        $ativos = 0;
        $manutencao = 0;
        $vender = 0;

        foreach ($dados as $d) {
            if (!empty($d['serial_id'])) {
                $totalSeriais++;
                if ($d['status'] === 'ATIVO') $ativos++;
                elseif ($d['status'] === 'MANUTENCAO') $manutencao++;
                elseif ($d['status'] === 'VENDER') $vender++;
            }
        }

        $produtosUnicos = count(array_unique(array_filter(array_column($dados, 'produto_id'))));

        $html = '<table style="width:100%; margin-bottom:20px; font-size:13px;">
            <tr>
                <td style="width:20%; background:#f0f9ff; padding:12px; text-align:center; border-radius:6px;">
                    <div style="font-size:28px; font-weight:bold; color:#06b6d4;">' . $produtosUnicos . '</div>
                    <div style="font-size:11px; color:#666;">Produtos</div>
                </td>
                <td style="width:20%; background:#f0f9ff; padding:12px; text-align:center; border-radius:6px;">
                    <div style="font-size:28px; font-weight:bold; color:#06b6d4;">' . $totalSeriais . '</div>
                    <div style="font-size:11px; color:#666;">Cód. Barras</div>
                </td>
                <td style="width:20%; background:#dcfce7; padding:12px; text-align:center; border-radius:6px;">
                    <div style="font-size:28px; font-weight:bold; color:#16a34a;">' . $ativos . '</div>
                    <div style="font-size:11px; color:#666;">Ativos</div>
                </td>
                <td style="width:20%; background:#fef9c3; padding:12px; text-align:center; border-radius:6px;">
                    <div style="font-size:28px; font-weight:bold; color:#ca8a04;">' . $manutencao . '</div>
                    <div style="font-size:11px; color:#666;">Manutencao</div>
                </td>
                <td style="width:20%; background:#cffafe; padding:12px; text-align:center; border-radius:6px;">
                    <div style="font-size:28px; font-weight:bold; color:#0891b2;">' . $vender . '</div>
                    <div style="font-size:11px; color:#666;">Vender</div>
                </td>
            </tr>
        </table>';

        return $html;
    }

    private function gerarTabela(array $dados, string $tipo): string
    {
        // Agrupar por produto
        $porProduto = [];
        foreach ($dados as $d) {
            $pid = $d['produto_id'] ?? 'sem_produto';
            if (!isset($porProduto[$pid])) {
                $porProduto[$pid] = [
                    'produto_id' => $d['produto_id'],
                    'produto' => $d['produto'] ?? 'Sem Produto',
                    'secao_nome' => $d['secao_nome'] ?? '-',
                    'custo' => $d['custo'] ?? 0,
                    'observacao' => $d['observacao'] ?? '',
                    'pode_ser_locado' => $d['pode_ser_locado'] ?? '',
                    'seriais' => [],
                ];
            }
            if (!empty($d['serial_id'])) {
                $porProduto[$pid]['seriais'][] = $d;
            }
        }

        $html = '<table style="width:100%; border-collapse: collapse; font-size:12px;">
            <thead>
                <tr style="background:#f0f9ff; border-bottom:2px solid #06b6d4;">
                    <th style="padding:8px; text-align:left; font-weight:bold; color:#333; width:8%;">ID</th>
                    <th style="padding:8px; text-align:left; font-weight:bold; color:#333; width:30%;">Produto / Cód. Barras</th>
                    <th style="padding:8px; text-align:left; font-weight:bold; color:#333; width:17%;">Secao</th>
                    <th style="padding:8px; text-align:center; font-weight:bold; color:#333; width:10%;">Total</th>
                    <th style="padding:8px; text-align:center; font-weight:bold; color:#333; width:12%;">Status</th>
                    <th style="padding:8px; text-align:left; font-weight:bold; color:#333; width:23%;">Motivo</th>
                </tr>
            </thead>
            <tbody>';

        $alt = false;
        $totalCusto = 0;
        $totalSeriaisGeral = 0;

        foreach ($porProduto as $prod) {
            $custo = !empty($prod['custo']) ? floatval($prod['custo']) : 0;
            $totalCusto += $custo;
            $bg = $alt ? '#fafafa' : '#fff';
            $totalSeriais = count($prod['seriais']);
            $totalSeriaisGeral += $totalSeriais;

            if (empty($prod['seriais'])) {
                // Produto sem seriais
                $locado = ($prod['pode_ser_locado'] ?? 'N') === 'S' ? 'Sim' : 'Nao';
                $html .= '<tr style="background:' . $bg . '; border-bottom:1px solid #eee;">
                    <td style="padding:6px 8px;">' . ($prod['produto_id'] ?? '-') . '</td>
                    <td style="padding:6px 8px; font-weight:600;">' . htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($prod['produto'] ?? ''))) . '</td>
                    <td style="padding:6px 8px;">' . htmlspecialchars($prod['secao_nome']) . '</td>
                    <td style="padding:6px 8px; text-align:center; font-weight:bold; color:#999;">0</td>
                    <td style="padding:6px 8px; text-align:center; color:#999;">-</td>
                    <td style="padding:6px 8px; color:#999; font-style:italic;">Sem cód. barras</td>
                </tr>';
            } else {
                // Contar status
                $countAtivos = 0;
                $countManut = 0;
                $countVender = 0;
                foreach ($prod['seriais'] as $s) {
                    if ($s['status'] === 'ATIVO') $countAtivos++;
                    elseif ($s['status'] === 'MANUTENCAO') $countManut++;
                    elseif ($s['status'] === 'VENDER') $countVender++;
                }

                $resumoStatus = '';
                if ($countAtivos > 0) $resumoStatus .= '<span style="color:#16a34a">' . $countAtivos . 'A</span> ';
                if ($countManut > 0) $resumoStatus .= '<span style="color:#ca8a04">' . $countManut . 'M</span> ';
                if ($countVender > 0) $resumoStatus .= '<span style="color:#0891b2">' . $countVender . 'V</span>';

                // Linha do produto (resumo)
                $html .= '<tr style="background:' . $bg . '; border-bottom:1px solid #ddd;">
                    <td style="padding:6px 8px; font-weight:600;">' . ($prod['produto_id'] ?? '-') . '</td>
                    <td style="padding:6px 8px; font-weight:600; color:#06b6d4;">' . htmlspecialchars(preg_replace('/\s*-\s*\d+$/', '', (string)($prod['produto'] ?? ''))) . '</td>
                    <td style="padding:6px 8px;">' . htmlspecialchars($prod['secao_nome']) . '</td>
                    <td style="padding:6px 8px; text-align:center; font-weight:bold;">' . $totalSeriais . '</td>
                    <td style="padding:6px 8px; text-align:center;">' . $resumoStatus . '</td>
                    <td style="padding:6px 8px; color:#666; font-size:11px;">' . htmlspecialchars($prod['observacao'] ?? '-') . '</td>
                </tr>';

                // Linhas dos seriais
                foreach ($prod['seriais'] as $serial) {
                    $statusColor = '#06b6d4';
                    if ($serial['status'] === 'ATIVO') $statusColor = '#16a34a';
                    elseif ($serial['status'] === 'MANUTENCAO') $statusColor = '#ca8a04';
                    elseif ($serial['status'] === 'VENDER') $statusColor = '#0891b2';

                    $html .= '<tr style="background:' . $bg . '; border-bottom:1px solid #eee;">
                        <td style="padding:4px 8px 4px 20px; color:#999; font-size:11px;">' . ($serial['serial_id'] ?? '') . '</td>
                        <td style="padding:4px 8px 4px 20px; font-size:11px; font-family:monospace;">' . htmlspecialchars($serial['serial']) . '</td>
                        <td style="padding:4px 8px 4px 20px; color:#999; font-size:11px;">-</td>
                        <td style="padding:4px 8px; text-align:center;">-</td>
                        <td style="padding:4px 8px; text-align:center;">
                            <span style="background:' . $statusColor . '; color:#fff; padding:2px 6px; border-radius:3px; font-size:10px;">' . $serial['status'] . '</span>
                        </td>
                        <td style="padding:4px 8px; color:#666; font-size:11px;">' . htmlspecialchars($serial['motivo'] ?? '-') . '</td>
                    </tr>';
                }
            }
            $alt = !$alt;
        }

        $html .= '<tr style="background:#f0f9ff; border-top:2px solid #06b6d4; font-weight:bold;">
            <td style="padding:8px;" colspan="3">TOTAL GERAL</td>
            <td style="padding:8px; text-align:center;">' . $totalSeriaisGeral . ' cód. barras</td>
            <td style="padding:8px;" colspan="2">Custo acumulado: R$ ' . number_format($totalCusto, 2, ',', '.') . '</td>
        </tr>';

        $html .= '</tbody></table>';

        return $html;
    }
}

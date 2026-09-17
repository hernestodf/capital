<?php

namespace App\Service;

use App\Database\Connection;

class FechamentoCalcService
{
    public function calcularTotaisEvento(int $eventoId): array
    {
        $custoColaboradores = Connection::query(
            "SELECT COALESCE(SUM(
                ((DATEDIFF(ec.data_fim, ec.data_inicio) + 1) * ec.valor_diaria) + COALESCE((
                    SELECT SUM(valor)
                    FROM evento_colaborador_horas_extras
                    WHERE id_alocacao = ec.id
                ), 0)
             ), 0) as total
             FROM evento_colaboradores ec
             WHERE ec.id_evento = ? AND ec.status = 'A'",
            [$eventoId]
        )[0]['total'] ?? 0;

        $custoFornecedores = Connection::query(
            "SELECT COALESCE(SUM(COALESCE(pes.total, pes.quantidade * pes.valor_unit)), 0) as total
             FROM produto_evento_sublocacao pes
             INNER JOIN produtos_evento pe ON pe.id = pes.id_produto_evento
             WHERE pe.id_evento = ? AND pes.status != 'cancelado'",
            [$eventoId]
        )[0]['total'] ?? 0;

        $custoOutros = Connection::query(
            "SELECT COALESCE(SUM(valor), 0) as total
             FROM evento_outros_custos
             WHERE evento_id = ?",
            [$eventoId]
        )[0]['total'] ?? 0;

        $receita = Connection::query(
            "SELECT COALESCE(SUM(total_item), 0) as total
             FROM produtos_evento
             WHERE id_evento = ?",
            [$eventoId]
        )[0]['total'] ?? 0;

        $custoTotal = (float) $custoColaboradores + (float) $custoFornecedores + (float) $custoOutros;
        $lucro = (float) $receita - $custoTotal;
        $margem = $receita > 0 ? ($lucro / $receita) * 100 : 0;

        return [
            'total_colaboradores' => (float) $custoColaboradores,
            'total_fornecedores' => (float) $custoFornecedores,
            'total_outros' => (float) $custoOutros,
            'custo_total' => $custoTotal,
            'receita' => (float) $receita,
            'lucro' => $lucro,
            'margem' => round($margem, 2),
        ];
    }

    public function calcularValorFinalColaborador(array $alocacao, ?float $valorPagamento): float
    {
        return $valorPagamento ?? (float) $alocacao['valor_total'];
    }

    public function calcularParcelasFornecedor(float $valorTotal, array $data): array
    {
        $tipoPagamento = $data['tipo_pagamento'] ?? 'avista';

        if ($tipoPagamento === 'avista') {
            return $this->calcularAvista($valorTotal, $data);
        }

        return $this->calcularParcelado($valorTotal, $data);
    }

    private function calcularAvista(float $valorTotal, array $data): array
    {
        $dataVencimento = !empty($data['data_vencimento'])
            ? $data['data_vencimento']
            : date('Y-m-d', strtotime('+30 days'));

        return [
            'tipo' => 'avista',
            'valor' => $valorTotal,
            'data_vencimento' => $dataVencimento,
            'entrada' => null,
            'parcelas' => [],
        ];
    }

    private function calcularParcelado(float $valorTotal, array $data): array
    {
        $entradaValor = (float) ($data['entrada_valor'] ?? 0);
        $parcelasQtd = (int) ($data['parcelas_qtd'] ?? 1);
        $intervalo = (int) ($data['intervalo_parcelas'] ?? 30);

        $parcelasDatasJson = !empty($data['parcelas_datas']) ? json_decode($data['parcelas_datas'], true) : null;
        $entradaVencimento = !empty($data['entrada_vencimento']) ? $data['entrada_vencimento'] : null;
        $primeiraParcelaVenc = !empty($data['primeira_parcela_vencimento'])
            ? $data['primeira_parcela_vencimento']
            : date('Y-m-d', strtotime('+30 days'));

        if ($entradaValor > $valorTotal) {
            throw new \Exception('O valor da entrada nao pode ser maior que o valor total (R$ ' . number_format($valorTotal, 2, ',', '.') . ').');
        }

        if ($parcelasQtd < 1) {
            throw new \Exception('O numero de parcelas deve ser pelo menos 1.');
        }

        $restante = $valorTotal - $entradaValor;

        $parcelasValoresJson = !empty($data['parcelas_valores']) ? json_decode($data['parcelas_valores'], true) : null;
        $usaValoresIndividuais = ($parcelasValoresJson !== null && count($parcelasValoresJson) === $parcelasQtd);

        $entrada = null;
        if ($entradaValor > 0) {
            $entrada = [
                'valor' => $entradaValor,
                'data_vencimento' => $entradaVencimento ?: $primeiraParcelaVenc,
                'parcelas_valor' => round($restante / $parcelasQtd, 2),
            ];
        }

        $parcelas = [];
        for ($i = 1; $i <= $parcelasQtd; $i++) {
            $vencParcela = $primeiraParcelaVenc;
            if ($parcelasDatasJson && isset($parcelasDatasJson[$i - 1]['vencimento'])) {
                $vencParcela = $parcelasDatasJson[$i - 1]['vencimento'];
            } else {
                $vencParcela = date('Y-m-d', strtotime($primeiraParcelaVenc . ' +' . (($i - 1) * $intervalo) . ' days'));
            }

            if ($usaValoresIndividuais && isset($parcelasValoresJson[$i - 1]['valor'])) {
                $valorParcela = (float) $parcelasValoresJson[$i - 1]['valor'];
            } else {
                $valorParcela = $restante / $parcelasQtd;
            }

            $parcelas[] = [
                'index' => $i,
                'valor' => round($valorParcela, 2),
                'data_vencimento' => $vencParcela,
                'parcelas_valor' => round($usaValoresIndividuais ? $restante / $parcelasQtd : $valorParcela, 2),
            ];
        }

        return [
            'tipo' => 'parcelado',
            'valor' => $valorTotal,
            'entrada' => $entrada,
            'parcelas' => $parcelas,
            'parcelas_qtd' => $parcelasQtd,
            'entrada_valor' => $entradaValor,
            'primeira_parcela_vencimento' => $primeiraParcelaVenc,
            'intervalo_parcelas' => $intervalo,
        ];
    }
}

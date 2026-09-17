<?php

namespace App\Service;

use App\Database\Connection;
use App\Repository\ProdutoEventoSublocacaoRepository;
use App\Repository\EventoRepository;

class SublocacaoConsolidacaoService
{
    private ProdutoEventoSublocacaoRepository $sublocacaoRepo;
    private EventoRepository $eventoRepo;

    public function __construct()
    {
        $this->sublocacaoRepo = new ProdutoEventoSublocacaoRepository();
        $this->eventoRepo = new EventoRepository();
    }

    public function vincularSublocador(
        int $idProdutoEvento,
        int $idFornecedor,
        ?int $idSublocacaoItem = null,
        float $quantidade = 1,
        float $valorUnit = 0,
        ?string $serialFornecedor = null,
        ?string $produtoFornecedor = null,
        ?float $custoUnit = null,
        ?string $produto = null,
        ?string $codigoBarras = null
    ): int {
        $total = $quantidade * $valorUnit;
        $custo = $custoUnit ?? $valorUnit;

        $id = Connection::transaction(function () use ($idProdutoEvento, $idFornecedor, $idSublocacaoItem, $quantidade, $valorUnit, $total, $serialFornecedor, $produtoFornecedor, $custo, $produto, $codigoBarras) {
            $id = $this->sublocacaoRepo->create([
                'id_produto_evento' => $idProdutoEvento,
                'id_fornecedor' => $idFornecedor,
                'id_sublocacao_item' => $idSublocacaoItem,
                'codigo_barras' => $codigoBarras,
                'produto' => $produto ?? $produtoFornecedor,
                'quantidade' => $quantidade,
                'valor_unit' => $valorUnit,
                'total' => $total,
                'serial_fornecedor' => $serialFornecedor,
                'produto_fornecedor' => $produtoFornecedor,
                'custo_unit' => $custo,
            ]);

            $qtdSublocada = $this->sublocacaoRepo->sumQtdSublocadaByProdutoEvento($idProdutoEvento);
            Connection::exec(
                "UPDATE produtos_evento SET qtd_sublocada = ? WHERE id = ?",
                [$qtdSublocada, $idProdutoEvento]
            );

            return $id;
        });

        return $id;
    }

    public function vincularLote(
        int $idProdutoEvento,
        int $idFornecedor,
        string $produtoFornecedor,
        float $valorUnit,
        float $custoUnit,
        array $seriais
    ): array {
        $seriais = array_values(array_filter(array_map('trim', $seriais), fn($s) => $s !== ''));
        if (empty($seriais)) {
            throw new \InvalidArgumentException('Nenhum serial informado');
        }

        $criados = [];
        Connection::transaction(function () use ($idProdutoEvento, $idFornecedor, $produtoFornecedor, $valorUnit, $custoUnit, $seriais, &$criados) {
            foreach ($seriais as $serial) {
                $id = $this->sublocacaoRepo->create([
                    'id_produto_evento'  => $idProdutoEvento,
                    'id_fornecedor'      => $idFornecedor,
                    'quantidade'         => 1,
                    'valor_unit'         => $custoUnit,
                    'total'              => $custoUnit,
                    'serial_fornecedor'  => $serial,
                    'produto_fornecedor' => $produtoFornecedor,
                    'custo_unit'         => $custoUnit,
                ]);
                $criados[] = $id;
            }

            $qtdSublocada = $this->sublocacaoRepo->sumQtdSublocadaByProdutoEvento($idProdutoEvento);
            Connection::exec(
                "UPDATE produtos_evento SET qtd_sublocada = ? WHERE id = ?",
                [$qtdSublocada, $idProdutoEvento]
            );
        });

        return $criados;
    }

    public function desvincularSublocador(int $id): int
    {
        $vinculo = $this->sublocacaoRepo->find($id);
        if (!$vinculo) {
            throw new \InvalidArgumentException('Vínculo não encontrado');
        }

        $idProdutoEvento = $vinculo['id_produto_evento'];

        Connection::transaction(function () use ($id, $idProdutoEvento) {
            $this->sublocacaoRepo->delete($id);

            $qtdSublocada = $this->sublocacaoRepo->sumQtdSublocadaByProdutoEvento($idProdutoEvento);
            Connection::exec(
                "UPDATE produtos_evento SET qtd_sublocada = ? WHERE id = ?",
                [$qtdSublocada, $idProdutoEvento]
            );
        });

        return 1;
    }

    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        return $this->sublocacaoRepo->findByProdutoEvento($idProdutoEvento);
    }

    public function findByEvento(int $idEvento): array
    {
        return $this->sublocacaoRepo->findByEvento($idEvento);
    }

    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        return $this->sublocacaoRepo->countByProdutoEvento($idProdutoEvento);
    }

    public function gerarContasPagar(int $idEvento): array
    {
        $evento = $this->eventoRepo->find($idEvento);
        if (!$evento) {
            throw new \InvalidArgumentException('Evento não encontrado: ' . $idEvento);
        }

        $dataFim = $evento['data_fim'] ?? date('Y-m-d');
        $dataVencimento = date('Y-m-d', strtotime($dataFim . ' +30 days'));
        $eventoNome = $evento['nome_evento'] ?? 'Evento #' . $idEvento;

        $vinculos = $this->sublocacaoRepo->findByEvento($idEvento);
        if (empty($vinculos)) {
            throw new \InvalidArgumentException('Nenhum vínculo de sublocação encontrado para este evento');
        }

        $contasCriadas = [];
        $errors = [];

        foreach ($vinculos as $v) {
            if ($v['status'] === 'cancelado' || $v['status'] === 'pago') continue;

            $valor = (float)($v['total'] ?? 0);
            if ($valor <= 0) $valor = (float)($v['custo_unit'] ?? 0);
            if ($valor <= 0) continue;

            $produtoNome = $v['produto_nome'] ?? 'Item';
            $fornecedorNome = $v['fornecedor_nome'] ?? 'Fornecedor';
            $descricao = "Sublocação: {$produtoNome} - {$fornecedorNome} - {$eventoNome}";

            try {
                Connection::transaction(function() use ($v, $idEvento, $descricao, $valor, $dataVencimento, &$contasCriadas, $fornecedorNome) {
                    Connection::exec(
                        "INSERT INTO contas_pagar (tipo, id_fornecedor, evento_id, referencia_id, descricao, valor, data_vencimento, status, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', NOW())",
                        ['fornecedor', $v['id_fornecedor'], $idEvento, $v['id'], $descricao, $valor, $dataVencimento]
                    );

                    Connection::exec(
                        "UPDATE produto_evento_sublocacao SET status = 'pago' WHERE id = ?",
                        [$v['id']]
                    );

                    $contasCriadas[] = [
                        'conta_id' => (int)Connection::lastInsertId(),
                        'fornecedor' => $fornecedorNome,
                        'valor' => $valor,
                        'vencimento' => $dataVencimento,
                    ];
                });
            } catch (\Throwable $e) {
                $errors[] = "Erro ao criar conta para {$fornecedorNome}: " . $e->getMessage();
            }
        }

        return [
            'success' => !empty($contasCriadas),
            'contas' => $contasCriadas,
            'errors' => $errors,
        ];
    }

    public function pagarFornecedor(int $idEvento, int $idFornecedor, array $data = [], ?array $file = null): array
    {
        $evento = $this->eventoRepo->find($idEvento);
        if (!$evento) throw new \InvalidArgumentException('Evento não encontrado');

        $vinculos = array_filter(
            $this->sublocacaoRepo->findByEvento($idEvento),
            fn($v) => (int)$v['id_fornecedor'] === $idFornecedor
                   && $v['status'] !== 'pago'
                   && $v['status'] !== 'cancelado'
        );
        $vinculos = array_values($vinculos);

        if (empty($vinculos)) throw new \InvalidArgumentException('Nenhum item pendente para este fornecedor');

        // Verificar duplicata: ja existe conta a pagar para algum desse vinculos?
        foreach ($vinculos as $v) {
            $exists = Connection::query(
                "SELECT id FROM contas_pagar WHERE tipo='fornecedor' AND referencia_id=? AND status IN ('PENDENTE','VENCIDO','PARCIAL') LIMIT 1",
                [$v['id']]
            );
            if (!empty($exists)) {
                throw new \InvalidArgumentException('Item já possui conta a pagar vinculada (ID: ' . $exists[0]['id'] . ')');
            }
        }

        $valorTotal = array_sum(array_map(fn($v) => (float)($v['custo_unit'] ?? 0), $vinculos));
        if ($valorTotal <= 0) throw new \InvalidArgumentException('Valor total inválido');

        $idFornecedorFirst = $idFornecedor;
        $fornecedorNome    = $vinculos[0]['fornecedor_nome'] ?? 'Fornecedor';

        $notaFiscalPath = null;
        if ($file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uploadResult = \App\Service\UploadService::upload(
                $file,
                dirname(__DIR__, 2) . '/storage/uploads/notas-fiscais/',
                'nf_subloc_forn_' . $idFornecedor . '_' . time() . '.' . $ext,
                ['pdf']
            );
            if ($uploadResult['success']) $notaFiscalPath = 'storage/uploads/notas-fiscais/' . $uploadResult['filename'];
        }

        $tipoPagamento = $data['tipo_pagamento'] ?? 'avista';
        $contasCriadas = [];
        $ids = array_column($vinculos, 'id');

        Connection::transaction(function () use (
            $idEvento, $idFornecedorFirst, $valorTotal, $tipoPagamento, $data,
            $notaFiscalPath, $ids, $fornecedorNome, &$contasCriadas
        ) {
            $descricao = 'Sublocação - ' . $fornecedorNome . ' - Evento #' . $idEvento;

            if ($tipoPagamento === 'avista') {
                $vencimento = !empty($data['data_vencimento']) ? $data['data_vencimento'] : date('Y-m-d', strtotime('+30 days'));
                $row = [
                    'tipo'             => 'fornecedor',
                    'id_fornecedor'    => $idFornecedorFirst,
                    'evento_id'        => $idEvento,
                    'referencia_id'    => $ids[0],
                    'descricao'        => $descricao,
                    'valor'            => $valorTotal,
                    'data_vencimento'  => $vencimento,
                    'status'           => 'PENDENTE',
                ];
                if (!empty($data['numero_nf']))   $row['numero_nf']  = $data['numero_nf'];
                if (!empty($data['observacao']))   $row['observacao'] = $data['observacao'];
                if ($notaFiscalPath)               $row['nota_fiscal'] = $notaFiscalPath;
                $fields = implode(', ', array_keys($row));
                $ph     = implode(', ', array_fill(0, count($row), '?'));
                Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($ph)", array_values($row));
                $contasCriadas[] = (int)Connection::lastInsertId();
            } else {
                $entradaNum   = (float)($data['entrada_valor'] ?? 0);
                $parcelasQtd  = (int)($data['parcelas_qtd'] ?? 1);
                $intervalo    = (int)($data['intervalo_parcelas'] ?? 30);
                $primeiraVenc = !empty($data['primeira_parcela_vencimento']) ? $data['primeira_parcela_vencimento'] : date('Y-m-d', strtotime('+30 days'));
                $parcelasValores = json_decode($data['parcelas_valores'] ?? '[]', true);
                if (!is_array($parcelasValores)) $parcelasValores = [];
                $restante = $valorTotal - $entradaNum;

                if ($entradaNum > 0) {
                    $row = ['tipo'=>'fornecedor','id_fornecedor'=>$idFornecedorFirst,'evento_id'=>$idEvento,'referencia_id'=>$ids[0],'descricao'=>'Entrada - '.$descricao,'valor'=>$entradaNum,'data_vencimento'=>$primeiraVenc,'status'=>'PENDENTE','tipo_pagamento'=>'entrada','parcelas_qtd'=>$parcelasQtd,'entrada_valor'=>$entradaNum];
                    if ($notaFiscalPath) $row['nota_fiscal'] = $notaFiscalPath;
                    if (!empty($data['observacao'])) $row['observacao'] = $data['observacao'];
                    $fields = implode(', ', array_keys($row)); $ph = implode(', ', array_fill(0, count($row), '?'));
                    Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($ph)", array_values($row));
                    $contasCriadas[] = (int)Connection::lastInsertId();
                }

                for ($i = 1; $i <= $parcelasQtd; $i++) {
                    $valorParcela = isset($parcelasValores[$i - 1]) ? (float)$parcelasValores[$i - 1] : round($restante / $parcelasQtd, 2);
                    $vencParcela  = date('Y-m-d', strtotime($primeiraVenc . ' +' . (($i - 1) * $intervalo) . ' days'));
                    $row = ['tipo'=>'fornecedor','id_fornecedor'=>$idFornecedorFirst,'evento_id'=>$idEvento,'referencia_id'=>$ids[0],'descricao'=>"Parcela {$i}/{$parcelasQtd} - ".$descricao,'valor'=>$valorParcela,'data_vencimento'=>$vencParcela,'status'=>'PENDENTE','tipo_pagamento'=>'parcela','parcelas_qtd'=>$parcelasQtd,'entrada_valor'=>$entradaNum];
                    if ($notaFiscalPath) $row['nota_fiscal'] = $notaFiscalPath;
                    if (!empty($data['observacao'])) $row['observacao'] = $data['observacao'];
                    $fields = implode(', ', array_keys($row)); $ph = implode(', ', array_fill(0, count($row), '?'));
                    Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($ph)", array_values($row));
                    $contasCriadas[] = (int)Connection::lastInsertId();
                }
            }

            if (!empty($ids)) {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                Connection::exec("UPDATE produto_evento_sublocacao SET status = 'pago' WHERE id IN ($ph)", $ids);
            }
        });

        return ['contas_ids' => $contasCriadas, 'quantidade' => count($contasCriadas), 'valor_total' => $valorTotal];
    }

    public function enviarPagamentoIndividual(int $idSublocacao, array $data = [], ?array $file = null): array
    {
        $vinculo = $this->sublocacaoRepo->find($idSublocacao);
        if (!$vinculo) {
            throw new \InvalidArgumentException('Vínculo de sublocação não encontrado');
        }

        if ($vinculo['status'] === 'pago') {
            throw new \InvalidArgumentException('Este item já foi enviado para pagamento');
        }

        $existente = Connection::query(
            "SELECT id FROM contas_pagar WHERE tipo = 'fornecedor' AND referencia_id = ? LIMIT 1",
            [$idSublocacao]
        );
        if (!empty($existente)) {
            throw new \InvalidArgumentException('Já existe uma conta a pagar para este item');
        }

        $valorTotal = (float)($vinculo['total'] ?? ($vinculo['quantidade'] * $vinculo['valor_unit']));
        $fornecedorNome = $vinculo['fornecedor_nome'] ?? 'Fornecedor';
        $idFornecedor = $vinculo['id_fornecedor'];
        $idEvento = $vinculo['id_evento'] ?? Connection::query(
            "SELECT pe.id_evento FROM produtos_evento pe WHERE pe.id = ?",
            [$vinculo['id_produto_evento']]
        )[0]['id_evento'] ?? 0;

        $notaFiscalPath = null;
        if ($file !== null) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uploadResult = \App\Service\UploadService::upload(
                $file,
                dirname(__DIR__, 2) . '/storage/uploads/notas-fiscais/',
                'nf_subloc_' . $idSublocacao . '_' . time() . '.' . $ext,
                ['pdf']
            );
            if ($uploadResult['success']) {
                $notaFiscalPath = 'storage/uploads/notas-fiscais/' . $uploadResult['filename'];
            }
        }

        $tipoPagamento = $data['tipo_pagamento'] ?? 'avista';
        $contasCriadas = [];

        Connection::transaction(function () use ($idSublocacao, $idEvento, $idFornecedor, $valorTotal, $tipoPagamento, $data, $notaFiscalPath, &$contasCriadas) {
            if ($tipoPagamento === 'avista') {
                $vencimento = !empty($data['data_vencimento']) ? $data['data_vencimento'] : date('Y-m-d', strtotime('+30 days'));
                $contaData = [
                    'tipo' => 'fornecedor',
                    'id_fornecedor' => $idFornecedor,
                    'evento_id' => $idEvento,
                    'referencia_id' => $idSublocacao,
                    'descricao' => 'Sublocação individual',
                    'valor' => $valorTotal,
                    'data_vencimento' => $vencimento,
                    'status' => 'PENDENTE',
                ];
                if (!empty($data['numero_nf'])) $contaData['numero_nf'] = $data['numero_nf'];
                if ($notaFiscalPath) $contaData['nota_fiscal'] = $notaFiscalPath;

                $fields = implode(', ', array_keys($contaData));
                $placeholders = implode(', ', array_fill(0, count($contaData), '?'));
                Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($placeholders)", array_values($contaData));
                $contasCriadas[] = (int)Connection::lastInsertId();
            } else {
                $entradaValor = (float)($data['entrada_valor'] ?? 0);
                $parcelasQtd = (int)($data['parcelas_qtd'] ?? 1);
                $intervalo = (int)($data['intervalo_parcelas'] ?? 30);
                $primeiraVenc = !empty($data['primeira_parcela_vencimento']) ? $data['primeira_parcela_vencimento'] : date('Y-m-d', strtotime('+30 days'));
                $restante = $valorTotal - $entradaValor;

                $parcelasValores = json_decode($data['parcelas_valores'] ?? '[]', true);
                if (!is_array($parcelasValores)) $parcelasValores = [];

                if ($entradaValor > 0) {
                    $entradaData = [
                        'tipo' => 'fornecedor',
                        'id_fornecedor' => $idFornecedor,
                        'evento_id' => $idEvento,
                        'referencia_id' => $idSublocacao,
                        'descricao' => 'Entrada - Sublocação',
                        'valor' => $entradaValor,
                        'data_vencimento' => $primeiraVenc,
                        'status' => 'PENDENTE',
                        'tipo_pagamento' => 'entrada',
                        'parcelas_qtd' => $parcelasQtd,
                        'entrada_valor' => $entradaValor,
                    ];
                    if ($notaFiscalPath) $entradaData['nota_fiscal'] = $notaFiscalPath;
                    $fields = implode(', ', array_keys($entradaData));
                    $placeholders = implode(', ', array_fill(0, count($entradaData), '?'));
                    Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($placeholders)", array_values($entradaData));
                    $contasCriadas[] = (int)Connection::lastInsertId();
                }

                for ($i = 1; $i <= $parcelasQtd; $i++) {
                    $valorParcela = isset($parcelasValores[$i - 1]) ? (float)$parcelasValores[$i - 1] : ($restante / $parcelasQtd);
                    $vencParcela = date('Y-m-d', strtotime($primeiraVenc . ' +' . (($i - 1) * $intervalo) . ' days'));
                    $parcelaData = [
                        'tipo' => 'fornecedor',
                        'id_fornecedor' => $idFornecedor,
                        'evento_id' => $idEvento,
                        'referencia_id' => $idSublocacao,
                        'descricao' => "Parcela {$i}/{$parcelasQtd} - Sublocação",
                        'valor' => round($valorParcela, 2),
                        'data_vencimento' => $vencParcela,
                        'status' => 'PENDENTE',
                        'tipo_pagamento' => 'parcela',
                        'parcelas_qtd' => $parcelasQtd,
                        'entrada_valor' => $entradaValor,
                    ];
                    if ($notaFiscalPath) $parcelaData['nota_fiscal'] = $notaFiscalPath;
                    $fields = implode(', ', array_keys($parcelaData));
                    $placeholders = implode(', ', array_fill(0, count($parcelaData), '?'));
                    Connection::exec("INSERT INTO contas_pagar ($fields) VALUES ($placeholders)", array_values($parcelaData));
                    $contasCriadas[] = (int)Connection::lastInsertId();
                }
            }

            Connection::exec("UPDATE produto_evento_sublocacao SET status = 'pago' WHERE id = ?", [$idSublocacao]);
        });

        return [
            'contas_ids' => $contasCriadas,
            'quantidade' => count($contasCriadas),
            'valor_total' => $valorTotal,
        ];
    }
}

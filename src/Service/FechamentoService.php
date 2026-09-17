<?php

namespace App\Service;

use App\Repository\FechamentoRepository;

/**
 * Service para fechamento de eventos
 */
class FechamentoService extends BaseService
{
    private FechamentoRepository $repo;
    private FechamentoCalcService $calc;
    protected string $entityName = 'Fechamento';

    public function __construct()
    {
        $this->repo = new FechamentoRepository();
        $this->calc = new FechamentoCalcService();
        parent::__construct($this->repo);
    }

    /**
     * Listar colaboradores do evento com presencas e valores
     */
    public function getColaboradores(int $eventoId): array
    {
        return $this->repo->getColaboradores($eventoId);
    }

    /**
     * Listar presencas de um colaborador
     */
    public function getPresencas(int $idAlocacao): array
    {
        return $this->repo->getPresencas($idAlocacao);
    }

    /**
     * Listar fornecedores vencedores do evento
     */
    public function getFornecedores(int $eventoId): array
    {
        return $this->repo->getFornecedores($eventoId);
    }

    public function getLocacao(int $eventoId): array
    {
        return $this->repo->getProdutosEvento($eventoId);
    }

    /**
     * Totais financeiros do evento
     */
    public function getTotais(int $eventoId): array
    {
        return $this->calc->calcularTotaisEvento($eventoId);
    }

    /**
     * Listar outros custos avulsos do evento
     */
    public function getOutrosCustos(int $eventoId): array
    {
        return $this->repo->getOutrosCustos($eventoId);
    }

    /**
     * Criar outro custo em evento_outros_custos (não entra em contas_pagar ainda)
     * O envio para pagamento é feito separadamente via enviarOutroCusto()
     */
    public function criarOutroCusto(array $data): int
    {
        return $this->repo->criarOutroCusto($data);
    }

    /**
     * Atualizar outro custo (somente se ainda não enviado para pagamento)
     */
    public function atualizarOutroCusto(int $id, array $data): int
    {
        $affected = $this->repo->atualizarOutroCusto($id, $data);
        if ($affected === 0) {
            throw new \Exception('Nao foi possivel atualizar. O custo pode ja ter sido enviado para pagamento.');
        }
        return $affected;
    }

    /**
     * Deletar outro custo (somente se ainda não enviado para pagamento)
     */
    public function deletarOutroCusto(int $id): int
    {
        $affected = $this->repo->deletarOutroCusto($id);
        if ($affected === 0) {
            throw new \Exception('Nao foi possivel excluir. O custo pode ja ter sido enviado para pagamento.');
        }
        return $affected;
    }

    /**
     * Enviar outro custo para pagamento → cria registro em contas_pagar
     * A partir deste momento aparece no módulo /contas-pagar
     */
    public function enviarOutroCusto(int $id): int
    {
        return $this->repo->enviarOutroParaPagamento($id);
    }

    /**
     * Buscar contas a pagar de um fornecedor específico
     */
    public function getFornecedorContas(int $idCotacao): array
    {
        return $this->repo->getContasPagarByReferencia($idCotacao);
    }

    /**
     * Enviar colaborador para pagamento
     */
    public function enviarColaboradorPagamento(int $eventoId, int $idAlocacao, string $dataVencimento, ?float $valorPagamento = null): array
    {
        // Verificar duplicata
        if ($this->repo->contaPagarExists('colaborador', $idAlocacao)) {
            throw new \Exception('Este colaborador ja foi enviado para pagamento.');
        }

        // Buscar dados da alocacao
        $colaboradores = $this->repo->getColaboradores($eventoId);
        $alocacao = null;
        foreach ($colaboradores as $c) {
            if ($c['id_alocacao'] == $idAlocacao) {
                $alocacao = $c;
                break;
            }
        }

        if (!$alocacao) {
            throw new \Exception('Alocacao nao encontrada.');
        }

        $valorFinal = $this->calc->calcularValorFinalColaborador($alocacao, $valorPagamento);

        // Criar conta a pagar
        $contaId = $this->repo->criarContaPagar([
            'evento_id' => $eventoId,
            'tipo' => 'colaborador',
            'referencia_id' => $idAlocacao,
            'descricao' => 'Pagamento colaborador: ' . $alocacao['nome'] . ' - Funcao: ' . $alocacao['funcao'],
            'valor' => $valorFinal,
            'data_vencimento' => $dataVencimento,
            'status' => 'PENDENTE',
        ]);

        // Marcar alocacao
        $this->repo->marcarEnvioPagamentoColaborador($idAlocacao, $dataVencimento);

        return [
            'conta_id' => $contaId,
            'valor' => $valorFinal,
            'valor_original' => $alocacao['valor_total'],
            'colaborador' => $alocacao['nome'],
        ];
    }

    /**
     * Enviar fornecedor para pagamento (avista ou parcelado)
     */
    public function enviarFornecedorPagamento(int $eventoId, int $idCotacao, array $data = [], ?array $file = null): array
    {
        // Verificar duplicata
        if ($this->repo->contaPagarExists('fornecedor', $idCotacao)) {
            throw new \Exception('Este fornecedor ja foi enviado para pagamento.');
        }

        // Upload do documento anexo se enviado
        $notaFiscalPath = null;
        if ($file !== null && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uploadResult = \App\Service\UploadService::upload(
                $file,
                dirname(__DIR__, 2) . '/storage/uploads/notas-fiscais/',
                'nf_cot_' . $idCotacao . '_' . time() . '.' . $ext,
                ['pdf']
            );
            if ($uploadResult['success']) {
                $notaFiscalPath = 'storage/uploads/notas-fiscais/' . $uploadResult['filename'];
            } else {
                throw new \Exception('Erro ao salvar o documento anexo: ' . $uploadResult['error']);
            }
        }

        // Buscar dados do fornecedor
        $fornecedores = $this->repo->getFornecedores($eventoId);
        $fornecedor = null;
        foreach ($fornecedores as $f) {
            if ($f['id_cotacao'] == $idCotacao) {
                $fornecedor = $f;
                break;
            }
        }

        if (!$fornecedor) {
            throw new \Exception('Fornecedor nao encontrado.');
        }

        $valorTotal = (float) $fornecedor['valor_proposto'];
        $calculo = $this->calc->calcularParcelasFornecedor($valorTotal, $data);

        if ($calculo['tipo'] === 'avista') {
            $contaData = [
                'evento_id' => $eventoId,
                'tipo' => 'fornecedor',
                'referencia_id' => $idCotacao,
                'id_fornecedor' => $fornecedor['id_fornecedor'],
                'descricao' => 'Fornecedor: ' . $fornecedor['nome_fantasia'] . ' - Servico: ' . $fornecedor['servico'],
                'valor' => $calculo['valor'],
                'data_vencimento' => $calculo['data_vencimento'],
                'status' => 'PENDENTE',
            ];

            if (!empty($data['numero_nf'])) {
                $contaData['numero_nf'] = $data['numero_nf'];
            }
            if ($notaFiscalPath) {
                $contaData['nota_fiscal'] = $notaFiscalPath;
            }
            if (!empty($data['observacao'])) {
                $contaData['observacao'] = $data['observacao'];
            }

            $contaId = $this->repo->criarContaPagar($contaData);
        } else {
            if ($calculo['entrada'] !== null) {
                $entradaData = [
                    'evento_id' => $eventoId,
                    'tipo' => 'fornecedor',
                    'referencia_id' => $idCotacao,
                    'id_fornecedor' => $fornecedor['id_fornecedor'],
                    'descricao' => 'Entrada - Fornecedor: ' . $fornecedor['nome_fantasia'],
                    'valor' => $calculo['entrada']['valor'],
                    'data_vencimento' => $calculo['entrada']['data_vencimento'],
                    'status' => 'PENDENTE',
                    'tipo_pagamento' => 'entrada',
                    'parcelas_qtd' => $calculo['parcelas_qtd'],
                    'entrada_valor' => $calculo['entrada_valor'],
                    'primeira_parcela_vencimento' => $calculo['primeira_parcela_vencimento'],
                    'intervalo_parcelas' => $calculo['intervalo_parcelas'],
                    'parcelas_valor' => $calculo['entrada']['parcelas_valor'],
                ];

                if (!empty($data['numero_nf'])) {
                    $entradaData['numero_nf'] = $data['numero_nf'];
                }
                if ($notaFiscalPath) {
                    $entradaData['nota_fiscal'] = $notaFiscalPath;
                }
                if (!empty($data['observacao'])) {
                    $entradaData['observacao'] = $data['observacao'];
                }

                $this->repo->criarContaPagar($entradaData);
            }

            foreach ($calculo['parcelas'] as $parcela) {
                $parcelaData = [
                    'evento_id' => $eventoId,
                    'tipo' => 'fornecedor',
                    'referencia_id' => $idCotacao,
                    'id_fornecedor' => $fornecedor['id_fornecedor'],
                    'descricao' => 'Parcela ' . $parcela['index'] . '/' . $calculo['parcelas_qtd'] . ' - Fornecedor: ' . $fornecedor['nome_fantasia'],
                    'valor' => $parcela['valor'],
                    'data_vencimento' => $parcela['data_vencimento'],
                    'status' => 'PENDENTE',
                    'tipo_pagamento' => 'parcela',
                    'parcelas_qtd' => $calculo['parcelas_qtd'],
                    'parcelas_valor' => $parcela['parcelas_valor'],
                    'entrada_valor' => $calculo['entrada_valor'],
                    'primeira_parcela_vencimento' => $calculo['primeira_parcela_vencimento'],
                    'intervalo_parcelas' => $calculo['intervalo_parcelas'],
                ];

                if (!empty($data['numero_nf'])) {
                    $parcelaData['numero_nf'] = $data['numero_nf'];
                }
                if ($notaFiscalPath) {
                    $parcelaData['nota_fiscal'] = $notaFiscalPath;
                }
                if (!empty($data['observacao'])) {
                    $parcelaData['observacao'] = $data['observacao'];
                }

                $this->repo->criarContaPagar($parcelaData);
            }

            $contaId = $idCotacao;
        }

        return [
            'conta_id' => $contaId ?? 0,
            'valor' => $valorTotal,
            'fornecedor' => $fornecedor['nome_fantasia'],
        ];
    }

    /**
     * Atualizar valor e/ou data de vencimento de uma parcela de fornecedor (contas_pagar)
     * Usado pelo modal de edição na tab fechamento fornecedores
     */
    public function atualizarParcelaFornecedor(int $idConta, array $data): array
    {
        $conta = $this->repo->getContaPagarById($idConta);
        if (!$conta) {
            throw new \Exception('Conta a pagar nao encontrada.');
        }
        if ($conta['status'] === 'PAGO') {
            throw new \Exception('Nao e possivel editar uma parcela ja paga.');
        }

        $updateData = [];
        if (isset($data['valor']) && $data['valor'] > 0) {
            $updateData['valor'] = round((float) $data['valor'], 2);
        }
        if (!empty($data['data_vencimento'])) {
            $updateData['data_vencimento'] = $data['data_vencimento'];
        }

        if (empty($updateData)) {
            throw new \Exception('Nenhum dado para atualizar.');
        }

        $this->repo->atualizarContaPagar($idConta, $updateData);

        return [
            'conta_id' => $idConta,
            'updated' => $updateData,
        ];
    }

    /**
     * Marcar presenca manualmente
     */
    public function marcarPresencaManual(
        int $idAlocacao,
        string $data,
        string $horaEntrada,
        string $horaSaida,
        string $status,
        string $observacao,
        ?string $fotoEntradaPath,
        ?string $fotoSaidaPath,
        bool $manual = true
    ): array {
        // Verificar se a alocacao existe
        $alocacao = $this->repo->getColaboradores(
            \App\Database\Connection::query(
                "SELECT id_evento FROM evento_colaboradores WHERE id = ?",
                [$idAlocacao]
            )[0]['id_evento'] ?? 0
        );

        // Verificar se ja existe presenca para esta data
        $presencasExistentes = $this->repo->getPresencas($idAlocacao);
        foreach ($presencasExistentes as $p) {
            if ($p['data'] === $data) {
                throw new \Exception('Ja existe presenca registrada para esta data (' . $data . ').');
            }
        }

        // Formatar horarios com data
        $horaEntradaReal = null;
        $horaSaidaReal = null;

        if (!empty($horaEntrada)) {
            $horaEntradaReal = $data . ' ' . $horaEntrada . ':00';
        }
        if (!empty($horaSaida)) {
            $horaSaidaReal = $data . ' ' . $horaSaida . ':00';
        }

        // Inserir presenca
        $id = \App\Database\Connection::exec(
            "INSERT INTO evento_colaborador_presencas 
             (id_alocacao, data, foto_entrada, hora_entrada_real, foto_saida, hora_saida_real, status, observacao, registro_manual)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $idAlocacao,
                $data,
                $fotoEntradaPath,
                $horaEntradaReal,
                $fotoSaidaPath,
                $horaSaidaReal,
                $status,
                $observacao,
                $manual ? 'S' : 'N'
            ]
        );

        return [
            'id' => \App\Database\Connection::lastInsertId(),
            'data' => $data,
            'status' => $status,
            'manual' => $manual,
        ];
    }

    /**
     * Listar horas extras de uma alocacao
     */
    public function getHorasExtras(int $idAlocacao): array
    {
        return $this->repo->getHorasExtras($idAlocacao);
    }

    /**
     * Lancar nova hora extra
     */
    public function lancarHoraExtra(array $data): int
    {
        return $this->repo->lancarHoraExtra($data);
    }

    /**
     * Deletar hora extra por ID
     */
    public function deletarHoraExtra(int $id): int
    {
        return $this->repo->deletarHoraExtra($id);
    }
}

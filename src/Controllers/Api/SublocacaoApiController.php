<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\SublocacaoService;
use App\Service\SublocacaoItemService;
use App\Repository\FornecedorRepository;
use App\Auth\Rbac;

class SublocacaoApiController extends Controller
{
    private SublocacaoService $sublocacaoService;
    private SublocacaoItemService $itemService;
    private FornecedorRepository $fornecedorRepo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->sublocacaoService = new SublocacaoService();
        $this->itemService = new SublocacaoItemService();
        $this->fornecedorRepo = new FornecedorRepository();
    }

    public function itens(int $idFornecedor): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $itens = $this->itemService->findByFornecedor($idFornecedor);
        return $this->json(['success' => true, 'data' => $itens]);
    }

    public function listVinculos(int $idProdutoEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $vinculos = $this->sublocacaoService->findByProdutoEvento($idProdutoEvento);
        return $this->json(['success' => true, 'data' => $vinculos]);
    }

    public function vincular(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $idProdutoEvento = (int)$this->post('id_produto_evento');
        $idFornecedor = (int)$this->post('id_fornecedor');
        $idSublocacaoItem = $this->post('id_sublocacao_item') ? (int)$this->post('id_sublocacao_item') : null;
        $quantidade = (float)str_replace(',', '.', $this->post('quantidade', 1));
        $valorUnit = (float)str_replace(',', '.', $this->post('valor_unit', 0));
        $serialFornecedor = $this->post('serial_fornecedor') ?: null;
        $produtoFornecedor = $this->post('produto_fornecedor') ?: null;
        $custoUnit = $this->post('custo_unit') ? (float)str_replace(',', '.', $this->post('custo_unit')) : null;
        $produto = $this->post('produto') ?: null;
        $codigoBarras = $this->post('codigo_barras') ?: null;

        if ($idProdutoEvento <= 0 || $idFornecedor <= 0) {
            return $this->json(['error' => 'Dados inválidos'], 400);
        }

        try {
            $id = $this->sublocacaoService->vincularSublocador(
                $idProdutoEvento, $idFornecedor, $idSublocacaoItem, $quantidade, $valorUnit,
                $serialFornecedor, $produtoFornecedor, $custoUnit, $produto, $codigoBarras
            );
            $vinculos = $this->sublocacaoService->findByProdutoEvento($idProdutoEvento);
            return $this->json(['success' => true, 'id' => $id, 'data' => $vinculos]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function desvincular(int $id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->sublocacaoService->desvincularSublocador($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function gerarContas(int $idEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $result = $this->sublocacaoService->gerarContasPagar($idEvento);
            return $this->json([
                'success' => $result['success'],
                'message' => count($result['contas']) . ' conta(s) criada(s) com sucesso',
                'contas' => $result['contas'],
                'errors' => $result['errors'],
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function enviarPagamento(int $id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $tipoPagamento = $this->post('tipo_pagamento', 'avista');
        $data = [
            'numero_nf' => $this->post('numero_nf', ''),
            'data_vencimento' => $this->post('data_vencimento', ''),
            'tipo_pagamento' => $tipoPagamento,
        ];

        if ($tipoPagamento === 'parcelado') {
            $data['entrada_valor'] = (float) $this->post('entrada_valor', 0);
            $data['parcelas_qtd'] = (int) $this->post('parcelas_qtd', 1);
            $data['primeira_parcela_vencimento'] = $this->post('primeira_parcela_vencimento', '');
            $data['intervalo_parcelas'] = (int) $this->post('intervalo_parcelas', 30);
            $data['parcelas_valores'] = $this->post('parcelas_valores', '[]');
        }

        $file = isset($_FILES['documento_anexo']) && $_FILES['documento_anexo']['error'] === UPLOAD_ERR_OK
            ? $_FILES['documento_anexo']
            : null;

        try {
            $result = $this->sublocacaoService->enviarPagamentoIndividual($id, $data, $file);
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function vincularLote(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $idProdutoEvento   = (int)$this->post('id_produto_evento');
        $idFornecedor      = (int)$this->post('id_fornecedor');
        $produtoFornecedor = $this->post('produto_fornecedor') ?: '';
        $valorUnit         = (float)str_replace(',', '.', $this->post('valor_unit', '0'));
        $custoUnit         = (float)str_replace(',', '.', $this->post('custo_unit', '0'));
        $seriaisJson       = $this->post('seriais', '[]');
        $seriais           = json_decode($seriaisJson, true);

        if ($idProdutoEvento <= 0 || $idFornecedor <= 0) {
            return $this->json(['error' => 'Dados inválidos'], 400);
        }
        if (!is_array($seriais) || empty($seriais)) {
            return $this->json(['error' => 'Nenhum serial informado'], 400);
        }

        try {
            $criados  = $this->sublocacaoService->vincularLote($idProdutoEvento, $idFornecedor, $produtoFornecedor, $valorUnit, $custoUnit, $seriais);
            $vinculos = $this->sublocacaoService->findByProdutoEvento($idProdutoEvento);
            return $this->json(['success' => true, 'criados' => count($criados), 'data' => $vinculos]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listByEvento(int $idEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $vinculos = $this->sublocacaoService->findByEvento($idEvento);
        return $this->json(['success' => true, 'data' => $vinculos]);
    }

    public function pagarFornecedor(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $idFornecedor = (int)$this->post('id_fornecedor');
        $idEvento     = (int)$this->post('id_evento') ?: (int)$this->post('evento_id');

        if ($idFornecedor <= 0 || $idEvento <= 0) {
            return $this->json(['error' => 'Dados inválidos: id_fornecedor=' . $idFornecedor . ' id_evento=' . $idEvento], 400);
        }

        $data = [
            'tipo_pagamento'              => $this->post('tipo_pagamento', 'avista'),
            'numero_nf'                   => $this->post('numero_nf', ''),
            'data_vencimento'             => $this->post('data_vencimento', ''),
            'entrada_valor'               => (float)$this->post('entrada_valor', '0'),
            'parcelas_qtd'                => (int)$this->post('parcelas_qtd', '1'),
            'primeira_parcela_vencimento' => $this->post('primeira_parcela_vencimento', ''),
            'intervalo_parcelas'          => (int)$this->post('intervalo_parcelas', '30'),
            'parcelas_valores'            => $this->post('parcelas_valores', '[]'),
            'observacao'                  => $this->post('observacao', ''),
        ];

        $file = isset($_FILES['documento_anexo']) && $_FILES['documento_anexo']['error'] === UPLOAD_ERR_OK
            ? $_FILES['documento_anexo'] : null;

        try {
            $result = $this->sublocacaoService->pagarFornecedor($idEvento, $idFornecedor, $data, $file);
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function fornecedores(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $fornecedores = $this->fornecedorRepo->getAtivos();
        return $this->json(['success' => true, 'data' => $fornecedores]);
    }
}

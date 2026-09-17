<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\ProdutoEventoSerialService;
use App\Service\ProdutoEventoAlocacaoService;
use App\Repository\ProdutoEventoRepository;
use App\Auth\Rbac;

class AlocacaoEstoqueApiController extends Controller
{
    private ProdutoEventoSerialService $serialService;
    private ProdutoEventoAlocacaoService $alocacaoService;
    private ProdutoEventoRepository $produtoEventoRepo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->serialService = new ProdutoEventoSerialService();
        $this->alocacaoService = new ProdutoEventoAlocacaoService();
        $this->produtoEventoRepo = new ProdutoEventoRepository();
    }

    public function seriaisDisponiveis(int $idProdutoEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $seriais = $this->serialService->sugerirSeriais($idProdutoEvento);
            return $this->json(['success' => true, 'data' => $seriais]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function listAlocacoes(int $idProdutoEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $alocacoes = $this->serialService->findByProdutoEvento($idProdutoEvento);
            return $this->json(['success' => true, 'data' => $alocacoes]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function alocar(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $idProdutoEvento = (int)$this->post('id_produto_evento');
        $idsSerials = $this->post('ids_seriais');

        if ($idProdutoEvento <= 0 || empty($idsSerials)) {
            return $this->json(['error' => 'Dados inválidos'], 400);
        }

        if (is_string($idsSerials)) {
            $idsSerials = json_decode($idsSerials, true) ?? [$idsSerials];
        }
        $idsSerials = array_map('intval', (array)$idsSerials);
        $idsSerials = array_filter($idsSerials);

        try {
            $result = $this->serialService->alocarEmLote($idProdutoEvento, $idsSerials);
            $alocacoes = $this->serialService->findByProdutoEvento($idProdutoEvento);
            $faltante = $this->alocacaoService->calcularFaltante($idProdutoEvento);

            return $this->json([
                'success' => !empty($result['success']),
                'alocados' => count($result['success']),
                'errors' => $result['errors'],
                'data' => $alocacoes,
                'faltante' => $faltante,
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function desalocar(int $id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->serialService->desalocarSerial($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function resumo(int $idEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $resumo = $this->alocacaoService->resumoAlocacao($idEvento);
            return $this->json(['success' => true, 'data' => $resumo]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

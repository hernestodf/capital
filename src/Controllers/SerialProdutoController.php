<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Http\Controller;
use App\Core\Response;
use App\Service\SerialProdutoService;
use App\Auth\Rbac;

class SerialProdutoController extends Controller
{
    private SerialProdutoService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new SerialProdutoService();
    }

    public function listByProduto($idProduto): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $seriais = $this->service->findByProduto($idProduto);
        return $this->json(['success' => true, 'data' => $seriais]);
    }

    public function getSerial($id): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }
        try {
            $serial = $this->service->find($id);
            if (!$serial) {
                return $this->json(['success' => false, 'message' => 'Serial nao encontrado'], 404);
            }
            return $this->json(['success' => true, 'data' => $serial]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $data = [
                'id_produto' => $this->post('id_produto'),
                'serial' => $this->post('serial'),
                'status' => $this->post('status', 'ATIVO'),
                'motivo' => $this->post('motivo', ''),
            ];

            $this->service->create($data);
            return $this->json(['success' => true, 'message' => 'Serial adicionado com sucesso']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao adicionar serial: ' . $e->getMessage()], 500);
        }
    }

    public function update($id): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $data = [
                'serial' => $this->post('serial'),
                'status' => $this->post('status'),
                'motivo' => $this->post('motivo', ''),
            ];

            $this->service->update($id, $data);
            return $this->json(['success' => true, 'message' => 'Serial atualizado com sucesso']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao atualizar serial: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus($id): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $status = $this->post('status');
            $motivo = $this->post('motivo', '');

            $this->service->updateStatus($id, $status, $motivo);
            return $this->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('estoque.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['success' => true, 'message' => 'Serial excluido com sucesso']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao excluir serial: ' . $e->getMessage()], 500);
        }
    }

    public function storeBatch(): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $idProduto = $this->post('id_produto');
            $seriaisText = $this->post('seriais');

            if (empty($idProduto)) {
                return $this->json(['success' => false, 'message' => 'Produto e obrigatorio'], 400);
            }

            if (empty($seriaisText)) {
                return $this->json(['success' => false, 'message' => 'Nenhum código de barras fornecido'], 400);
            }

            // Split text into lines (one barcode per line)
            $seriais = explode("\n", str_replace("\r", "", $seriaisText));
            $seriais = array_filter(array_map('trim', $seriais));

            if (empty($seriais)) {
                return $this->json(['success' => false, 'message' => 'Nenhum código de barras válido fornecido'], 400);
            }

            $result = $this->service->storeBatch((int)$idProduto, $seriais, 'ATIVO');

            $message = sprintf(
                '%d código(s) de barras adicionado(s), %d duplicado(s)',
                $result['success'],
                $result['duplicates']
            );

            return $this->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'success' => $result['success'],
                    'duplicates' => $result['duplicates'],
                    'errors' => $result['errors'],
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao processar lote: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/seriais/store-batch
     * Cadastro em lote de seriais — protegido por API Key (sem sessão PHP, sem CSRF)
     *
     * Requer header: X-Api-Key
     * Body (form-encoded ou JSON):
     *   id_produto  : int  (obrigatório)
     *   seriais     : string (um serial por linha) (obrigatório)
     *   id_usuario_responsavel : int (opcional — registra quem cadastrou)
     *
     * Response:
     *   {
     *     "success": true,
     *     "message": "5 serial(is) adicionado(s), 0 duplicado(s)",
     *     "data": {
     *       "success": 5,
     *       "duplicates": 0,
     *       "errors": [],
     *       "seriais": ["SN001","SN002",...]
     *     }
     *   }
     */
    public function storeBatchApi(): Response
    {
        // Autenticação via API Key — já validada pelo ApiKeyMiddleware
        $apiKeyData = $GLOBALS['api_key'] ?? null;

        $idProduto     = (int) ($this->post('id_produto') ?? 0);
        $seriaisText   = $this->post('seriais', '');
        $idUsuarioResp = $this->post('id_usuario_responsavel');

        if ($idProduto < 1) {
            return $this->json(['success' => false, 'message' => 'id_produto é obrigatório'], 400);
        }

        if (empty($seriaisText)) {
            return $this->json(['success' => false, 'message' => 'Nenhum serial fornecido'], 400);
        }

        // Split text into lines (one serial per line), trim whitespace, remove empty
        $seriais = explode("\n", str_replace("\r", "", $seriaisText));
        $seriais = array_filter(array_map('trim', $seriais));

        if (empty($seriais)) {
            return $this->json(['success' => false, 'message' => 'Nenhum serial válido fornecido'], 400);
        }

        try {
            $result = $this->service->storeBatch($idProduto, $seriais, 'ATIVO');

            $totalProcessed = $result['success'] + $result['duplicates'] + count($result['errors']);
            $message = sprintf(
                '%d serial(is) adicionado(s), %d duplicado(s), %d com erro (total processado: %d)',
                $result['success'],
                $result['duplicates'],
                count($result['errors']),
                $totalProcessed
            );

            return $this->json([
                'success'       => true,
                'message'       => $message,
                'data' => [
                    'success'        => $result['success'],
                    'duplicates'     => $result['duplicates'],
                    'errors'         => $result['errors'],
                    'seriais'        => array_values($seriais),
                    'produto'        => $idProduto,
                    'id_usuario_responsavel' => $idUsuarioResp ? (int) $idUsuarioResp : null,
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao processar lote: ' . $e->getMessage()], 500);
        }
    }
}

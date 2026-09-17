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
                'numero_serie' => $this->post('numero_serie', ''),
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
                'numero_serie' => $this->post('numero_serie', ''),
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

    /**
     * Gera códigos por faixa (prefixo + inicial + final).
     *
     * Com preview=1: só monta a lista e informa quais já existem, sem inserir.
     * Sem preview: gera e cadastra de fato no estoque (via storeBatch).
     */
    public function generateRange(): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $idProduto = (int) $this->post('id_produto');
        $prefixo   = trim((string) $this->post('prefixo', ''));
        $inicial   = $this->post('inicial');
        $final     = $this->post('final');
        $preview   = (bool) $this->post('preview', false);

        if ($inicial === null || $inicial === '' || $final === null || $final === '' || !ctype_digit((string)$inicial) || !ctype_digit((string)$final)) {
            return $this->json(['success' => false, 'message' => 'Sequencial inicial e final devem ser números inteiros'], 400);
        }

        try {
            if ($preview) {
                $result = $this->service->previewRange($prefixo, (int)$inicial, (int)$final);
                return $this->json(['success' => true, 'data' => $result]);
            }

            if (empty($idProduto)) {
                return $this->json(['success' => false, 'message' => 'Produto é obrigatório'], 400);
            }

            $result = $this->service->generateRange($idProduto, $prefixo, (int)$inicial, (int)$final);

            $message = sprintf(
                '%d código(s) gerado(s) e cadastrado(s) no estoque, %d já existente(s)',
                $result['success'],
                $result['duplicates']
            );

            return $this->json([
                'success' => true,
                'message' => $message,
                'data'    => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao gerar faixa: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Página de impressão do QR Code de um único código de barras (reimpressão).
     * Não gera nenhum código novo — usa exatamente o serial já existente.
     */
    public function qrCode($id): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $serial = $this->service->find((int)$id);
        if (!$serial) {
            $_SESSION['error'] = 'Código não encontrado.';
            return $this->redirect($this->baseUrl . '/estoque');
        }

        return $this->view('estoque/qrcode_print', [
            'title'   => 'Imprimir QR Code',
            'codigos' => [$serial['serial']],
        ]);
    }

    /**
     * Página de impressão em lote — recebe uma lista de ids (query string
     * "ids=1,2,3") e imprime o QR Code de cada um, usando os seriais já
     * existentes (não gera nada novo).
     */
    public function qrCodeLote(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $idsParam = $this->get('ids', '');
        $codigosParam = $this->get('codigos', '');

        $codigos = [];

        if ($codigosParam !== '') {
            // Impressão logo após gerar uma faixa — o backend já devolveu as
            // strings geradas. Ainda assim, só imprime o que realmente está
            // cadastrado (nunca texto arbitrário vindo da URL).
            $solicitados = array_filter(array_map('trim', explode(',', (string)$codigosParam)));
            $codigos = $this->service->filterExisting($solicitados);
        } else {
            $ids = array_filter(array_map('intval', explode(',', (string)$idsParam)));
            foreach ($ids as $id) {
                $serial = $this->service->find($id);
                if ($serial) {
                    $codigos[] = $serial['serial'];
                }
            }
        }

        if (empty($codigos)) {
            $_SESSION['error'] = 'Nenhum código encontrado.';
            return $this->redirect($this->baseUrl . '/estoque');
        }

        return $this->view('estoque/qrcode_print', [
            'title'   => 'Imprimir QR Codes',
            'codigos' => $codigos,
        ]);
    }
}

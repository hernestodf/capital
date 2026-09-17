<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\ContasPagarService;
use App\Service\FornecedorService;
use App\Auth\Rbac;

class ContasPagarController extends Controller
{
    private ContasPagarService $contasPagarService;
    private FornecedorService $fornecedorService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->contasPagarService = new ContasPagarService();
        $this->fornecedorService = new FornecedorService();
    }

    public function index(): Response
    {
        if (!Rbac::check('contas_pagar.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $page = (int) $this->get('page', 1);
        $search = $this->get('search', '');
        $filters = [
            'search' => $search,
            'id_fornecedor' => $this->get('id_fornecedor'),
            'id_colaborador' => $this->get('id_colaborador'),
            'tipo' => $this->get('tipo'),
            'evento_id' => $this->get('evento_id'),
            'status' => $this->get('status'),
            'data_vencimento_inicio' => $this->get('data_vencimento_inicio'),
            'data_vencimento_fim' => $this->get('data_vencimento_fim'),
            'valor_min' => $this->get('valor_min'),
            'valor_max' => $this->get('valor_max'),
        ];
        // Remover filtros vazios
        $filters = array_filter($filters, function($v) { return $v !== '' && $v !== null; });

        $data = $this->contasPagarService->search($filters, $page, 15);
        $contas = $data['data'];
        $totais = $this->contasPagarService->getTotais();
        $vencidas = $this->contasPagarService->getVencidas();
        $vencemHoje = $this->contasPagarService->getVencemHoje();
        $fornecedores = $this->fornecedorService->getAtivos();

        // Buscar colaboradores e eventos para os filtros
        $colaboradores = $this->getColaboradores();
        $eventos = $this->getEventos();

        return $this->view('contas_pagar/index', [
            'title' => 'Contas a Pagar',
            'contas' => $contas,
            'totais' => $totais,
            'vencidas' => $vencidas,
            'vencemHoje' => $vencemHoje,
            'fornecedores' => $fornecedores,
            'colaboradores' => $colaboradores,
            'eventos' => $eventos,
            'search' => $search,
            'filters' => $filters,
            'pagination' => $data['pagination'],
        ]);
    }

    /**
     * Buscar colaboradores ativos para filtros
     */
    private function getColaboradores(): array
    {
        try {
            $db = \App\Database\Connection::get();
            $stmt = $db->query("SELECT id, nome FROM colaboradores WHERE status = 1 ORDER BY nome");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Buscar eventos ativos para filtros
     */
    private function getEventos(): array
    {
        try {
            $db = \App\Database\Connection::get();
            $stmt = $db->query("SELECT id, nome FROM eventos WHERE status = 1 ORDER BY nome");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function create(): Response
    {
        if (!Rbac::check('contas_pagar.criar')) {
            return $this->redirect($this->baseUrl . '/contas-pagar');
        }

        $fornecedores = $this->fornecedorService->getAtivos();

        return $this->view('contas_pagar/create', [
            'title' => 'Nova Conta a Pagar',
            'fornecedores' => $fornecedores,
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('contas_pagar.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('contas_pagar/create', [
                'title' => 'Nova Conta a Pagar',
                'error' => 'Token CSRF inválido',
                'fornecedores' => $this->fornecedorService->getAtivos(),
            ]);
        }

        $idFornecedor = $this->post('id_fornecedor') ?: null;

        $data = [
            'id_fornecedor' => $idFornecedor,
            'tipo' => $idFornecedor ? 'fornecedor' : 'outro',
            'numero_nf' => $this->post('numero_nf'),
            'descricao' => $this->post('descricao'),
            'valor' => $this->post('valor'),
            'data_vencimento' => $this->post('data_vencimento'),
            'observacao' => $this->post('observacao'),
        ];

        try {
            $this->contasPagarService->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Conta a pagar criada com sucesso']);
            return $this->redirect($this->baseUrl . '/contas-pagar?success=created');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('contas_pagar/create', [
                'title' => 'Nova Conta a Pagar',
                'error' => $e->getMessage(),
                'data' => $data,
                'fornecedores' => $this->fornecedorService->getAtivos(),
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('contas_pagar.editar')) {
            return $this->redirect($this->baseUrl . '/contas-pagar');
        }

        $conta = $this->contasPagarService->find($id);

        if (!$conta) {
            return $this->redirect($this->baseUrl . '/contas-pagar');
        }

        $fornecedores = [];
        if ($conta['tipo'] === 'fornecedor') {
            $fornecedores = $this->fornecedorService->getAtivos();
        }

        return $this->view('contas_pagar/edit', [
            'title' => 'Editar Conta a Pagar',
            'conta'  => $conta,
            'fornecedores' => $fornecedores,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('contas_pagar.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        $contaAtual = $this->contasPagarService->find($id);

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            $fornecedores = ($contaAtual && $contaAtual['tipo'] === 'fornecedor') ? $this->fornecedorService->getAtivos() : [];
            return $this->view('contas_pagar/edit', [
                'title' => 'Editar Conta a Pagar',
                'conta' => $contaAtual,
                'error' => 'Token CSRF inválido',
                'fornecedores' => $fornecedores,
            ]);
        }

        if (!$contaAtual) {
            return $this->redirect($this->baseUrl . '/contas-pagar');
        }

        $status = $this->post('status') ?: $contaAtual['status'];

        $data = [
            'status' => $status,
            'numero_nf' => $this->post('numero_nf') !== null ? (trim($this->post('numero_nf')) ?: null) : $contaAtual['numero_nf'],
            'observacao' => $this->post('observacao') !== null ? (trim($this->post('observacao')) ?: null) : $contaAtual['observacao'],
        ];

        if ($contaAtual['tipo'] === 'fornecedor') {
            $idFornecedor = $this->post('id_fornecedor') !== null ? (int)$this->post('id_fornecedor') : null;
            $data['id_fornecedor'] = $idFornecedor ?: null;
        }

        // Campos de pagamento — só persiste quando PAGO
        if ($status === 'PAGO') {
            $data['data_pagamento']  = $this->post('data_pagamento') ?: date('Y-m-d');
            $rawValorPago            = $this->post('valor_pago') ?: $contaAtual['valor'];
            $data['valor_pago']      = str_replace(['.', ','], ['', '.'], $rawValorPago);
            $data['tipo_pagamento']  = $this->post('tipo_pagamento') ?: null;
        } else {
            // Limpa dados de pagamento se reverter status
            $data['data_pagamento'] = null;
            $data['valor_pago']     = null;
            $data['tipo_pagamento'] = null;
        }

        // Upload comprovante de pagamento
        if (isset($_FILES['comprovante_anexo']) && $_FILES['comprovante_anexo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['comprovante_anexo']['name'], PATHINFO_EXTENSION));
            $result = \App\Service\UploadService::upload(
                $_FILES['comprovante_anexo'],
                __DIR__ . '/../../storage/uploads/comprovantes/',
                'comprovante_' . $id . '_' . time() . '.' . $ext,
                ['pdf', 'jpg', 'jpeg', 'png'],
                10 * 1024 * 1024
            );
            if ($result['success']) {
                $data['comprovante_anexo'] = 'storage/uploads/comprovantes/' . $result['filename'];
            }
        }

        // Upload nota fiscal
        if (isset($_FILES['nota_fiscal']) && $_FILES['nota_fiscal']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['nota_fiscal']['name'], PATHINFO_EXTENSION));
            $result = \App\Service\UploadService::upload(
                $_FILES['nota_fiscal'],
                __DIR__ . '/../../storage/uploads/notas-fiscais/',
                'nf_cp_' . $id . '_' . time() . '.' . $ext,
                ['pdf', 'jpg', 'jpeg', 'png'],
                10 * 1024 * 1024
            );
            if ($result['success']) {
                $data['nota_fiscal'] = 'storage/uploads/notas-fiscais/' . $result['filename'];
            }
        }

        try {
            $this->contasPagarService->update($id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Conta a pagar atualizada com sucesso']);
            return $this->redirect($this->baseUrl . '/contas-pagar?success=updated');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            $fornecedores = ($contaAtual && $contaAtual['tipo'] === 'fornecedor') ? $this->fornecedorService->getAtivos() : [];
            return $this->view('contas_pagar/edit', [
                'title' => 'Editar Conta a Pagar',
                'conta' => $this->contasPagarService->find($id),
                'error' => $e->getMessage(),
                'fornecedores' => $fornecedores,
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('contas_pagar.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->contasPagarService->delete($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Registrar pagamento de uma conta
     */
    public function pagar($id): Response
    {
        if (!Rbac::check('contas_pagar.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'data_pagamento' => $this->post('data_pagamento') ?: date('Y-m-d'),
            'valor_pago' => $this->post('valor_pago'),
            'tipo_pagamento' => $this->post('tipo_pagamento'),
        ];

        try {
            $this->contasPagarService->registrarPagamento($id, $data);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Gera PDF para impressão (abre inline no navegador)
     */
    public function print($id): Response
    {
        if (!Rbac::check('contas_pagar.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $conta = $this->contasPagarService->find($id);

        if (!$conta) {
            return $this->json(['error' => 'Conta nao encontrada'], 404);
        }

        try {
            $pdfService = new \App\Service\PdfGeneratorService();
            $empresa = $pdfService->getEmpresa();

            ob_start();
            require dirname(__DIR__, 2) . '/views/contas_pagar/print.php';
            $html = ob_get_clean();

            $pdfService->setupHeaderFooter();
            $pdfService->getMpdf()->AddPage();

            $filename = 'comprovante_pagamento_' . $id . '_' . date('Ymd');
            $nomePessoa = $conta['fornecedor_nome'] ?? $conta['colaborador_nome'] ?? 'conta';
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $nomePessoa);
            $filename .= '_' . substr($cleanName, 0, 30);

            $pdfContent = $pdfService->getPdfContent($html);

            $response = new Response($pdfContent, 200);
            $response->header('Content-Type', 'application/pdf');
            $response->header('Content-Disposition', 'inline; filename="' . $filename . '.pdf"');
            $response->header('Content-Length', (string)strlen($pdfContent));
            return $response;
        } catch (\Throwable $e) {
            if (ob_get_level()) ob_end_clean();
            \App\Core\Logger::error('PDF inline error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Erro ao gerar PDF'], 500);
        }
    }
    /**
     * Gera PDF para download
     */
    public function pdf($id): Response
    {
        if (!Rbac::check('contas_pagar.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $conta = $this->contasPagarService->find($id);

        if (!$conta) {
            return $this->json(['error' => 'Conta nao encontrada'], 404);
        }

        try {
            $pdfService = new \App\Service\PdfGeneratorService();
            $empresa = $pdfService->getEmpresa();

            ob_start();
            require dirname(__DIR__, 2) . '/views/contas_pagar/print.php';
            $html = ob_get_clean();

            $pdfService->setupHeaderFooter();
            $filename = 'comprovante_pagamento_' . $id . '_' . date('Ymd');

            $nomePessoa = $conta['fornecedor_nome'] ?? $conta['colaborador_nome'] ?? 'conta';
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $nomePessoa);
            $filename .= '_' . substr($cleanName, 0, 30);

            $pdfContent = $pdfService->getPdfContent($html);

            $response = new Response($pdfContent, 200);
            $response->header('Content-Type', 'application/pdf');
            $response->header('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"');
            $response->header('Content-Length', (string)strlen($pdfContent));
            return $response;
        } catch (\Throwable $e) {
            if (ob_get_level()) ob_end_clean();
            \App\Core\Logger::error('PDF download error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Erro ao gerar PDF'], 500);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('contas_pagar.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $idsRaw = $this->post('ids');
        $ids = json_decode($idsRaw, true);
        if (!is_array($ids) || empty($ids)) {
            return $this->json(['error' => 'Nenhum ID fornecido'], 400);
        }

        $deleted = 0;
        $errors = [];
        foreach ($ids as $id) {
            try {
                $this->contasPagarService->delete((int)$id);
                $deleted++;
            } catch (\Throwable $e) {
                $errors[] = "ID $id: " . $e->getMessage();
            }
        }

        return $this->json([
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }
}
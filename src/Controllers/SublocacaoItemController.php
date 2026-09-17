<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\SublocacaoItemService;
use App\Auth\Rbac;

class SublocacaoItemController extends Controller
{
    private SublocacaoItemService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new SublocacaoItemService();
    }

    public function list(int $idFornecedor): Response
    {
        if (!Rbac::check('fornecedores.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $itens = $this->service->findByFornecedor($idFornecedor);
        return $this->json(['success' => true, 'data' => $itens]);
    }

    public function store(int $idFornecedor): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'id_fornecedor' => $idFornecedor,
            'produto' => $this->post('produto'),
            'codigo' => $this->post('codigo') ?: null,
            'quantidade' => $this->post('quantidade', 1),
            'valor_unit' => $this->post('valor_unit', 0),
            'observacao' => $this->post('observacao') ?: null,
        ];

        if (empty($data['produto'])) {
            return $this->json(['error' => 'Nome do produto é obrigatório'], 400);
        }

        try {
            $id = $this->service->create($data);
            $item = $this->service->find($id);
            return $this->json(['success' => true, 'id' => $id, 'item' => $item]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function update(int $idFornecedor, int $itemId): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'produto' => $this->post('produto'),
            'codigo' => $this->post('codigo') ?: null,
            'quantidade' => $this->post('quantidade', 1),
            'valor_unit' => $this->post('valor_unit', 0),
            'observacao' => $this->post('observacao') ?: null,
        ];

        try {
            $this->service->update($itemId, $data);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function delete(int $idFornecedor, int $itemId): Response
    {
        if (!Rbac::check('fornecedores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($itemId);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\CategoriaSalaService;
use App\Auth\Rbac;

class CategoriaSalaController extends Controller
{
    private CategoriaSalaService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new CategoriaSalaService();
    }

    /**
     * Lista todas as categorias
     */
    public function index(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $categorias = $this->service->getAllOrdenadas();
        return $this->json(['success' => true, 'data' => $categorias]);
    }

    /**
     * Lista categorias ativas (para selects) usando apenas o banco local.
     */
    public function listAll(): Response
    {
        $categorias = array_map(function ($cat) {
            $cat['nome'] = $cat['nome_categoria'] ?? '';
            return $cat;
        }, $this->service->getAtivas());

        return $this->json(['ok' => true, 'data' => $categorias]);
    }

    /**
     * Cria uma categoria
     */
    public function store(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['ok' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('csrf_token') ?: $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'nome_categoria' => $this->post('nome'),
            'ordem' => (int) ($this->post('ordem') ?: 0),
        ];

        try {
            $id = $this->service->create($data);
            return $this->json(['ok' => true, 'id' => $id, 'nome' => $data['nome_categoria']]);
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Atualiza uma categoria (via PUT)
     */
    public function update($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        // Parse body para PUT requests
        $body = file_get_contents('php://input');
        parse_str($body, $data);
        
        $csrfToken = $data['csrf_token'] ?? $data['_csrf_token'] ?? ($this->post('csrf_token') ?? $this->post('_csrf_token') ?? '');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $nome = $data['nome'] ?? $this->post('nome') ?? '';

        try {
            $this->service->update($id, [
                'nome_categoria' => $nome,
                'ordem' => (int) ($data['ordem'] ?? 0),
            ]);
            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Deleta uma categoria (via DELETE)
     */
    public function delete($id): Response
    {
        if (!Rbac::check('eventos.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        // Parse body para DELETE requests
        $body = file_get_contents('php://input');
        parse_str($body, $data);
        
        $csrfToken = $data['_csrf_token'] ?? ($this->post('_csrf_token') ?? '');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Toggle status de uma categoria
     */
    public function toggle($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newStatus = $this->service->toggleStatus($id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

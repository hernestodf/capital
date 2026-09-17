<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\FuncaoService;
use App\Auth\Rbac;

class FuncaoController extends Controller
{
    private FuncaoService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new FuncaoService();
    }

    public function listAll(): Response
    {
        if (!Rbac::check('colaboradores.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $funcoes = $this->service->getAtivas();

        return $this->json([
            'success' => true,
            'data' => array_map(fn($f) => [
                'id'   => (int)$f['id'],
                'nome' => $f['nome'],
            ], $funcoes),
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('colaboradores.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $id = $this->service->create(['nome' => $this->post('nome') ?? '']);
            return $this->json(['success' => true, 'data' => ['id' => $id, 'nome' => trim($this->post('nome'))]]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->update((int)$id, ['nome' => $this->post('nome') ?? '']);
            return $this->json(['success' => true, 'data' => ['id' => (int)$id, 'nome' => trim($this->post('nome'))]]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('colaboradores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete((int)$id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

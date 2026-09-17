<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\UnidadeMedidaService;
use App\Auth\Rbac;

class UnidadeMedidaController extends Controller
{
    private UnidadeMedidaService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new UnidadeMedidaService();
    }

    public function listAll(): Response
    {
        $data = $this->service->all();
        return $this->json(['success' => true, 'data' => $data]);
    }

    public function store(): Response
    {
        if (!Rbac::check('planilhas.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'unidademedida' => $this->post('unidademedida'),
        ];

        try {
            $id = $this->service->create($data);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function update($id): Response
    {
        if (!Rbac::check('planilhas.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'unidademedida' => $this->post('unidademedida'),
        ];

        try {
            $this->service->update($id, $data);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('planilhas.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

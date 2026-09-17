<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Http\Controller;
use App\Core\Response;
use App\Service\SecaoService;
use App\Auth\Rbac;

class SecaoController extends Controller
{
    private SecaoService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new SecaoService();
    }

    public function listAll(): Response
    {
        $secoes = $this->service->getAtivas();
        return $this->json(['success' => true, 'data' => $secoes]);
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
                'secao' => $this->post('secao'),
            ];

            $this->service->create($data);
            return $this->json(['success' => true, 'message' => 'Secao criada com sucesso']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
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
                'secao' => $this->post('secao'),
            ];

            $this->service->update($id, $data);
            return $this->json(['success' => true, 'message' => 'Secao atualizada com sucesso']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
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
            return $this->json(['success' => true, 'message' => 'Secao excluida com sucesso']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao excluir secao: ' . $e->getMessage()], 500);
        }
    }
}

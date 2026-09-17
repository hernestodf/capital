<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\ProdutorService;
use App\Auth\Rbac;

class ProdutorController extends Controller
{
    private ProdutorService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ProdutorService();
    }

    public function index(): Response
    {
        if (!Rbac::check('produtores.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $produtores = $this->service->all();

        return $this->view('produtor/index', [
            'title' => 'Comercial',
            'produtores' => $produtores,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('produtores.criar')) {
            return $this->redirect($this->baseUrl . '/comercial');
        }

        return $this->view('produtor/create', [
            'title' => 'Novo Comercial',
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('produtores.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('produtor/create', [
                'title' => 'Novo Comercial',
                'error' => 'Token CSRF inválido',
            ]);
        }

        $data = [
            'id_users' => !empty($this->post('id_users')) ? (int) $this->post('id_users') : null,
            'nome'     => $this->post('nome'),
            'email'    => $this->post('email'),
            'telefone' => $this->post('telefone'),
        ];

        try {
            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Comercial criado com sucesso']);
            return $this->redirect($this->baseUrl . '/comercial?success=created');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('produtor/create', [
                'title' => 'Novo Comercial',
                'error' => $e->getMessage(),
                'old'   => $data,
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('produtores.editar')) {
            return $this->redirect($this->baseUrl . '/comercial');
        }

        try {
            $produtor = $this->service->findOrFail((int) $id);
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Comercial não encontrado.';
            return $this->redirect($this->baseUrl . '/comercial');
        }

        return $this->view('produtor/edit', [
            'title'   => 'Editar Comercial',
            'produtor' => $produtor,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('produtores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            try {
                $produtor = $this->service->findOrFail((int) $id);
            } catch (\Throwable $e) {
                $_SESSION['error'] = 'Comercial não encontrado.';
                return $this->redirect($this->baseUrl . '/comercial');
            }
            return $this->view('produtor/edit', [
                'title'    => 'Editar Comercial',
                'error'    => 'Token CSRF inválido',
                'produtor' => $produtor,
            ]);
        }

        $data = [
            'id_users' => !empty($this->post('id_users')) ? (int) $this->post('id_users') : null,
            'nome'     => $this->post('nome'),
            'email'    => $this->post('email'),
            'telefone' => $this->post('telefone'),
        ];

        try {
            $this->service->update((int) $id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Comercial atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/comercial?success=updated');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            try {
                $produtor = $this->service->findOrFail((int) $id);
            } catch (\Throwable $ex) {
                $_SESSION['error'] = 'Comercial não encontrado.';
                return $this->redirect($this->baseUrl . '/comercial');
            }
            return $this->view('produtor/edit', [
                'title'    => 'Editar Comercial',
                'error'    => $e->getMessage(),
                'produtor' => array_merge($produtor, $data),
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('produtores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete((int) $id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function toggle($id): Response
    {
        if (!Rbac::check('produtores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newStatus = $this->service->toggleStatus((int) $id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('produtores.excluir')) {
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
                $this->service->delete((int)$id);
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
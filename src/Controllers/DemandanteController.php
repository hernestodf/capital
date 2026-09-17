<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\DemandanteService;
use App\Service\ClienteService;
use App\Auth\Rbac;

class DemandanteController extends Controller
{
    private DemandanteService $service;
    private ClienteService $clienteService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new DemandanteService();
        $this->clienteService = new ClienteService();
    }

    public function index(): Response
    {
        if (!Rbac::check('demandantes.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $demandantes = $this->service->allComCliente();

        return $this->view('comprador/index', [
            'title' => 'Compradores',
            'demandantes' => $demandantes,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('demandantes.criar')) {
            return $this->redirect($this->baseUrl . '/compradores');
        }

        $clientes = $this->clienteService->all();

        return $this->view('comprador/create', [
            'title' => 'Novo Comprador',
            'clientes' => $clientes,
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('demandantes.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('comprador/create', [
                'title' => 'Novo Comprador',
                'error' => 'Token CSRF inválido',
                'clientes' => $this->clienteService->all(),
            ]);
        }

        $data = [
            'id_cliente' => $this->post('id_cliente') ?: null,
            'nome' => $this->post('nome'),
            'telefone' => $this->post('telefone'),
            'email' => $this->post('email'),
            'observacao' => $this->post('observacao'),
        ];

        try {
            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Comprador criado com sucesso']);
            return $this->redirect($this->baseUrl . '/compradores?success=created');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('comprador/create', [
                'title' => 'Novo Comprador',
                'error' => $e->getMessage(),
                'old' => $data,
                'clientes' => $this->clienteService->all(),
            ]);
        }
    }

    public function storeAjax(): Response
    {
        if (!Rbac::check('demandantes.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }

        $nome = trim($this->post('nome') ?? '');
        if ($nome === '') {
            return $this->json(['success' => false, 'message' => 'Nome é obrigatório'], 422);
        }

        $data = [
            'nome'       => $nome,
            'telefone'   => $this->post('telefone') ?: null,
            'email'      => $this->post('email')    ?: null,
            'id_cliente' => $this->post('id_cliente') ?: null,
        ];

        try {
            $this->service->create($data);
            $id = (int) \App\Database\Connection::lastInsertId();
            return $this->json(['success' => true, 'id' => $id, 'nome' => $nome]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('demandantes.editar')) {
            return $this->redirect($this->baseUrl . '/compradores');
        }

        $demandante = $this->service->find((int) $id);

        if (!$demandante) {
            return $this->redirect($this->baseUrl . '/compradores');
        }

        $clientes = $this->clienteService->all();

        return $this->view('comprador/edit', [
            'title' => 'Editar Comprador',
            'demandante' => $demandante,
            'clientes' => $clientes,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('demandantes.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('comprador/edit', [
                'title' => 'Editar Comprador',
                'demandante' => $this->service->find((int) $id),
                'error' => 'Token CSRF inválido',
                'clientes' => $this->clienteService->all(),
            ]);
        }

        $data = [
            'id_cliente' => $this->post('id_cliente') ?: null,
            'nome' => $this->post('nome'),
            'telefone' => $this->post('telefone'),
            'email' => $this->post('email'),
            'observacao' => $this->post('observacao'),
        ];

        try {
            $this->service->update((int) $id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Comprador atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/compradores');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('comprador/edit', [
                'title' => 'Editar Comprador',
                'demandante' => $this->service->find((int) $id),
                'error' => $e->getMessage(),
                'clientes' => $this->clienteService->all(),
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('demandantes.excluir')) {
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
        if (!Rbac::check('demandantes.editar')) {
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
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('demandantes.excluir')) {
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
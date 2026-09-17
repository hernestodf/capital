<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\ClienteService;
use App\Auth\Rbac;

class ClienteController extends Controller
{
    private ClienteService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ClienteService();
    }

    public function index(): Response
    {
        // Verifica permissão: estoquista NÃO pode ver clientes
        if (!Rbac::check('clientes.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $clientes = $this->service->all();

        return $this->view('cliente/index', [
            'title' => 'Clientes',
            'clientes' => $clientes,
        ]);
    }

    public function create(): Response
    {
        // Verifica permissão: apenas administrador e produtor
        if (!Rbac::check('clientes.criar')) {
            return $this->redirect($this->baseUrl . '/clientes');
        }

        return $this->view('cliente/create', [
            'title' => 'Novo Cliente',
        ]);
    }

    public function store(): Response
    {
        // Verifica permissão
        if (!Rbac::check('clientes.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('cliente/create', [
                'title' => 'Novo Cliente',
                'error' => 'Token CSRF inválido',
            ]);
        }

        $data = [
            'cpf_cnpj' => $this->post('cpf_cnpj'),
            'nome_fantasia' => $this->post('nome_fantasia'),
            'razao_social' => $this->post('razao_social'),
            'contato' => $this->post('contato'),
            'email' => $this->post('email'),
            'telefone' => $this->post('telefone'),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'numero' => $this->post('numero'),
            'complemento' => $this->post('complemento'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'observacao' => $this->post('observacao'),
        ];

        try {
            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Cliente criado com sucesso']);
            return $this->redirect($this->baseUrl . '/clientes');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('cliente/create', [
                'title' => 'Novo Cliente',
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public function edit($id): Response
    {
        // Verifica permissão: apenas administrador e produtor
        if (!Rbac::check('clientes.editar')) {
            return $this->redirect($this->baseUrl . '/clientes');
        }

        $cliente = $this->service->find($id);

        if (!$cliente) {
            return $this->redirect($this->baseUrl . '/clientes');
        }

        return $this->view('cliente/edit', [
            'title' => 'Editar Cliente',
            'cliente' => $cliente,
        ]);
    }

    public function update($id): Response
    {
        // Verifica permissão
        if (!Rbac::check('clientes.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('cliente/edit', [
                'title' => 'Editar Cliente',
                'cliente' => $this->service->find($id),
                'error' => 'Token CSRF inválido',
            ]);
        }

        $data = [
            'cpf_cnpj' => $this->post('cpf_cnpj'),
            'nome_fantasia' => $this->post('nome_fantasia'),
            'razao_social' => $this->post('razao_social'),
            'contato' => $this->post('contato'),
            'email' => $this->post('email'),
            'telefone' => $this->post('telefone'),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'numero' => $this->post('numero'),
            'complemento' => $this->post('complemento'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'observacao' => $this->post('observacao'),
        ];

        try {
            $this->service->update($id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Cliente atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/clientes');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('cliente/edit', [
                'title' => 'Editar Cliente',
                'cliente' => $this->service->find($id),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id): Response
    {
        // Verifica permissão: apenas administrador
        if (!Rbac::check('clientes.excluir')) {
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

    public function toggle($id): Response
    {
        // Verifica permissão: apenas administrador e produtor
        if (!Rbac::check('clientes.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
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

    public function bulkDelete(): Response
    {
        if (!Rbac::check('clientes.excluir')) {
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
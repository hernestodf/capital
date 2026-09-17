<?php

namespace App\Http;

use App\Core\Response;

/**
 * Trait BaseCrudController
 * 
 * Elimina duplicação de código CRUD em controllers
 * Usado por: UserController, ClienteController, DemandanteController, etc.
 */
trait BaseCrudController
{
    /**
     * Retorna a instância do service específico
     * Deve ser implementado pelo controller que usa este trait
     */
    abstract protected function getService();

    /**
     * Retorna a URL base para redirecionamentos
     */
    abstract protected function getBaseUrl(): string;

    /**
     * Retorna a view de listagem
     */
    abstract protected function getIndexView(): string;

    /**
     * Retorna a view de criação
     */
    abstract protected function getCreateView(): string;

    /**
     * Retorna a view de edição
     */
    abstract protected function getEditView(): string;

    /**
     * Lista todos os registros
     */
    public function index(): Response
    {
        $items = $this->getService()->getAll();
        
        return $this->view($this->getIndexView(), [
            'items' => $items,
            'title' => $this->getListTitle(),
        ]);
    }

    /**
     * Mostra formulário de criação
     */
    public function create(): Response
    {
        return $this->view($this->getCreateView(), [
            'title' => $this->getCreateTitle(),
        ]);
    }

    /**
     * Armazena novo registro
     */
    public function store(): Response
    {
        $data = $this->getStoreData();
        
        try {
            $this->getService()->create($data);
            return $this->redirect($this->getBaseUrl() . '?success=created');
        } catch (\Throwable $e) {
            return $this->view($this->getCreateView(), [
                'title' => $this->getCreateTitle(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostra formulário de edição
     */
    public function edit($id): Response
    {
        $item = $this->getService()->find($id);
        
        if (!$item) {
            return $this->redirect($this->getBaseUrl());
        }

        return $this->view($this->getEditView(), [
            'item' => $item,
            'title' => $this->getEditTitle(),
        ]);
    }

    /**
     * Atualiza registro existente
     */
    public function update($id): Response
    {
        $data = $this->getUpdateData();
        
        try {
            $this->getService()->update($id, $data);
            return $this->redirect($this->getBaseUrl() . '?success=updated');
        } catch (\Throwable $e) {
            return $this->view($this->getEditView(), [
                'item' => $this->getService()->find($id),
                'title' => $this->getEditTitle(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Deleta registro
     */
    public function delete($id): Response
    {
        $csrfToken = $this->post('_csrf_token');
        
        if (!\App\Core\Csrf::validate($csrfToken)) {
            return $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->getService()->delete($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Alterna status do registro (ativo/inativo)
     */
    public function toggle($id): Response
    {
        $csrfToken = $this->post('_csrf_token');
        
        if (!\App\Core\Csrf::validate($csrfToken)) {
            return $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->getService()->toggleStatus($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Métodos auxiliares para customização
     */
    protected function getListTitle(): string
    {
        return 'Listagem';
    }

    protected function getCreateTitle(): string
    {
        return 'Criar Novo';
    }

    protected function getEditTitle(): string
    {
        return 'Editar';
    }

    protected function getStoreData(): array
    {
        return $this->post();
    }

    protected function getUpdateData(): array
    {
        return $this->post();
    }
}

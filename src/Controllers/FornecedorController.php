<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\FornecedorService;
use App\Service\CategoriaService;
use App\Service\SubcategoriaService;
use App\Auth\Rbac;

class FornecedorController extends Controller
{
    private FornecedorService  $fornecedorService;
    private CategoriaService   $categoriaService;
    private SubcategoriaService $subcategoriaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->fornecedorService   = new FornecedorService();
        $this->categoriaService    = new CategoriaService();
        $this->subcategoriaService = new SubcategoriaService();
    }

    public function index(): Response
    {
        if (!Rbac::check('fornecedores.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $fornecedores = $this->fornecedorService->all();
        $categorias   = $this->categoriaService->getAtivas();

        return $this->view('fornecedor/index', [
            'title'       => 'Fornecedores',
            'fornecedores' => $fornecedores,
            'categorias'  => $categorias,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('fornecedores.criar')) {
            return $this->redirect($this->baseUrl . '/fornecedores');
        }

        return $this->view('fornecedor/create', [
            'title'      => 'Novo Fornecedor',
            'categorias' => $this->categoriaService->getAtivas(),
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('fornecedores.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('fornecedor/create', [
                'title'      => 'Novo Fornecedor',
                'error'      => 'Token CSRF inválido',
                'categorias' => $this->categoriaService->getAtivas(),
            ]);
        }

        $data = $this->buildFornecedorData();

        try {
            $this->fornecedorService->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Fornecedor criado com sucesso']);
            return $this->redirect($this->baseUrl . '/fornecedores');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('fornecedor/create', [
                'title'      => 'Novo Fornecedor',
                'error'      => $this->getFriendlyErrorMessage($e),
                'data'       => $data,
                'categorias' => $this->categoriaService->getAtivas(),
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->redirect($this->baseUrl . '/fornecedores');
        }

        $fornecedor = $this->fornecedorService->find((int) $id);

        if (!$fornecedor) {
            return $this->redirect($this->baseUrl . '/fornecedores');
        }

        $categorias   = $this->categoriaService->getAtivas();
        $subcategorias = !empty($fornecedor['id_categoria'])
            ? $this->subcategoriaService->findByCategoria((int)$fornecedor['id_categoria'])
            : [];

        return $this->view('fornecedor/edit', [
            'title'        => 'Editar Fornecedor',
            'fornecedor'   => $fornecedor,
            'categorias'   => $categorias,
            'subcategorias' => $subcategorias,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('fornecedor/edit', [
                'title'      => 'Editar Fornecedor',
                'fornecedor' => $this->fornecedorService->find((int) $id),
                'error'      => 'Token CSRF inválido',
                'categorias' => $this->categoriaService->getAtivas(),
            ]);
        }

        $data = $this->buildFornecedorData();

        try {
            $this->fornecedorService->update((int) $id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Fornecedor atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/fornecedores');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('fornecedor/edit', [
                'title'      => 'Editar Fornecedor',
                'fornecedor' => $this->fornecedorService->find((int) $id),
                'error'      => $this->getFriendlyErrorMessage($e),
                'categorias' => $this->categoriaService->getAtivas(),
            ]);
        }
    }

    /**
     * Monta o array de dados do fornecedor a partir do POST.
     * Centraliza o mapeamento de todos os campos novos e campos legados.
     */
    private function buildFornecedorData(): array
    {
        return [
            // Categoria
            'id_categoria'    => $this->post('id_categoria')    ?: null,
            'id_subcategoria' => $this->post('id_subcategoria') ?: null,
            'telefone'        => preg_replace('/\D/', '', $this->post('telefone') ?? '') ?: null,
            // Identificação
            'cpf_cnpj'           => $this->post('cpf_cnpj'),
            'inscricao_estadual' => $this->post('inscricao_estadual') ?: null,
            'nome_fantasia'      => $this->post('nome_fantasia'),
            'razao_social'       => $this->post('razao_social'),
            // Contato Financeiro
            'fin_nome'      => $this->post('fin_nome')      ?: null,
            'fin_telefone'  => $this->post('fin_telefone')  ?: null,
            'fin_email'     => $this->post('fin_email')     ?: null,
            'fin_nome2'     => $this->post('fin_nome2')     ?: null,
            'fin_telefone2' => $this->post('fin_telefone2') ?: null,
            'fin_email2'    => $this->post('fin_email2')    ?: null,
            // Contato Comercial
            'com_nome'      => $this->post('com_nome')      ?: null,
            'com_telefone'  => $this->post('com_telefone')  ?: null,
            'com_email'     => $this->post('com_email')     ?: null,
            'com_nome2'     => $this->post('com_nome2')     ?: null,
            'com_telefone2' => $this->post('com_telefone2') ?: null,
            'com_email2'    => $this->post('com_email2')    ?: null,
            // Presença Online
            'site'      => $this->post('site')      ?: null,
            'instagram' => $this->post('instagram') ?: null,
            // Endereço
            'cep'          => $this->post('cep'),
            'endereco'     => $this->post('endereco'),
            'numero'       => $this->post('numero'),
            'complemento'  => $this->post('complemento'),
            'bairro'       => $this->post('bairro'),
            'cidade'       => $this->post('cidade'),
            'estado'       => $this->post('estado'),
            'estado_para_trabalho' => $this->post('estado_para_trabalho'),
            // Outros
            'observacao' => $this->post('observacao'),
            'dados_pagamento' => $this->post('dados_pagamento') ?: null,
        ];
    }

    public function delete($id): Response
    {
        if (!Rbac::check('fornecedores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->fornecedorService->delete((int) $id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function toggle($id): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newStatus = $this->fornecedorService->toggleStatus((int) $id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Subcategorias de uma categoria (AJAX — usado no form de fornecedor).
     * Usa ProFox central API como fonte única de verdade.
     */
    public function getSubcategorias($idCategoria): Response
    {
        $subcategorias = $this->subcategoriaService->findByCategoria((int)$idCategoria);

        $data = array_map(fn($s) => [
            'id'           => (int)$s['id'],
            'id_categoria' => (int)$s['id_categoria'],
            'subcategoria' => $s['subcategoria'],
            'status'       => (int)$s['status'],
        ], $subcategorias);

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Retorna uma mensagem de erro amigável para exceções de banco de dados.
     */
    private function getFriendlyErrorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();

        // Verifica erros de chave estrangeira (foreign key)
        if (stripos($message, 'foreign key constraint fails') !== false) {
            $detalhes = '';
            if (stripos($message, 'id_categoria') !== false || stripos($message, 'fornecedores_ibfk_1') !== false) {
                $detalhes = 'A Categoria selecionada é inválida ou foi removida do sistema.';
            } elseif (stripos($message, 'id_subcategoria') !== false || stripos($message, 'fornecedores_ibfk_2') !== false) {
                $detalhes = 'A Subcategoria selecionada é inválida ou foi removida do sistema.';
            } else {
                $detalhes = 'Uma das referências selecionadas (Categoria ou Subcategoria) é inválida.';
            }

            return 'Erro de integridade: ' . $detalhes . ' ' .
                   'Para corrigir, por favor selecione uma Categoria e uma Subcategoria válidas no formulário. ' .
                   'Se necessário, você pode cadastrar novas opções válidas utilizando os botões "+ Categoria" ou "+ Subcategoria" no topo da página.';
        }

        // Duplicidade de dados (e.g. CPF/CNPJ)
        if (stripos($message, 'duplicate entry') !== false) {
            return 'Erro: Já existe um fornecedor cadastrado com este mesmo CPF/CNPJ ou Razão Social.';
        }

        return $message;
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('fornecedores.excluir')) {
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
                $this->fornecedorService->delete((int)$id);
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
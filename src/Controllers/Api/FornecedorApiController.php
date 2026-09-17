<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\FornecedorService;
use App\Http\Middleware\ApiKeyMiddleware;

class FornecedorApiController extends Controller
{
    private FornecedorService $fornecedorService;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->fornecedorService = new FornecedorService();
    }

    public function store(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('fornecedores:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        // Mapear campos (backward compatibility)
        $cpfCnpj = $this->post('cpf_cnpj') ?: $this->post('cnpj');
        
        $data = [
            'id_categoria' => $this->post('id_categoria') ?: null,
            'id_subcategoria' => $this->post('id_subcategoria') ?: null,
            'cpf_cnpj' => $cpfCnpj,
            'nome_fantasia' => $this->post('nome_fantasia'),
            'razao_social' => $this->post('razao_social'),
            'telefone' => $this->post('telefone'),
            'email' => $this->post('email'),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'numero' => $this->post('numero'),
            'complemento' => $this->post('complemento'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'observacao' => $this->post('observacao'),
        ];

        if (empty($data['nome_fantasia'])) {
            return $this->json(['success' => false, 'message' => 'nome_fantasia é obrigatório'], 400);
        }

        try {
            $id = $this->fornecedorService->create($data);
            return $this->json([
                'success' => true,
                'message' => 'Fornecedor criado com sucesso',
                'data' => ['id' => $id, 'nome' => $data['nome_fantasia']]
            ], 201);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('fornecedores:read') && 
            !ApiKeyMiddleware::hasPermission('fornecedores:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        try {
            return $this->json(['success' => true, 'data' => $this->fornecedorService->all()]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

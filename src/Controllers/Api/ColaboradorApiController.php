<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\ColaboradorService;
use App\Http\Middleware\ApiKeyMiddleware;

/**
 * API Controller para Colaboradores
 * Endpoints: /api/v1/colaboradores
 */
class ColaboradorApiController extends Controller
{
    private ColaboradorService $colaboradorService;

    public function __construct($request = null)
    {
        // Se request não for injetado, criar um (fallback)
        if ($request) {
            parent::__construct($request);
        }
        $this->colaboradorService = new ColaboradorService();
    }

    /**
     * POST /api/v1/colaboradores
     * Criar novo colaborador
     */
    public function store(): Response
    {
        // Verificar permissão
        if (!ApiKeyMiddleware::hasPermission('colaboradores:write')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: colaboradores:write'
            ], 403);
        }

        // Mapear campos (backward compatibility)
        $telefone = $this->post('telefone') ?: $this->post('whatsapp');
        
        $data = [
            'nome' => $this->post('nome'),
            'email' => $this->post('email'),
            'telefone' => $telefone,
            'origem' => $this->post('origem'),
            'tipo' => $this->post('tipo'),
            'estado_para_trabalho' => $this->post('estado_para_trabalho'),
            'atua_como' => $this->post('atua_como'),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'tipo_chave_pix' => $this->post('tipo_chave_pix'),
            'chavepix' => $this->post('chavepix'),
            'observacao' => $this->post('observacao'),
        ];

        // Validações
        if (empty($data['nome'])) {
            return $this->json([
                'success' => false,
                'message' => 'Campo nome é obrigatório'
            ], 400);
        }

        try {
            $id = $this->colaboradorService->create($data);
            
            return $this->json([
                'success' => true,
                'message' => 'Colaborador criado com sucesso',
                'data' => [
                    'id' => $id,
                    'nome' => $data['nome']
                ]
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao criar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/colaboradores
     * Listar colaboradores
     */
    public function index(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('colaboradores:read') && 
            !ApiKeyMiddleware::hasPermission('colaboradores:write')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada'
            ], 403);
        }

        try {
            $colaboradores = $this->colaboradorService->all();
            
            return $this->json([
                'success' => true,
                'data' => $colaboradores
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao listar colaboradores: ' . $e->getMessage()
            ], 500);
        }
    }
}

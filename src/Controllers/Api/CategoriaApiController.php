<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\CategoriaService;
use App\Http\Middleware\ApiKeyMiddleware;

class CategoriaApiController extends Controller
{
    private CategoriaService $categoriaService;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->categoriaService = new CategoriaService();
    }

    public function store(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('categorias:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        // Mapear campos (backward compatibility)
        $categoria = $this->post('categoria') ?: $this->post('nome');

        $data = [
            'categoria' => $categoria,
        ];

        if (empty($data['categoria'])) {
            return $this->json(['success' => false, 'message' => 'Categoria é obrigatória'], 400);
        }

        try {
            $id = $this->categoriaService->create($data);
            $categoria = $this->categoriaService->find($id);
            return $this->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso',
                'data' => $categoria
            ], 201);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('categorias:read') &&
            !ApiKeyMiddleware::hasPermission('categorias:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        try {
            return $this->json(['success' => true, 'data' => $this->categoriaService->all()]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

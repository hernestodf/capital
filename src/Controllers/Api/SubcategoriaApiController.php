<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\SubcategoriaService;
use App\Http\Middleware\ApiKeyMiddleware;

class SubcategoriaApiController extends Controller
{
    private SubcategoriaService $subcategoriaService;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->subcategoriaService = new SubcategoriaService();
    }

    public function store(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('subcategorias:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        // Mapear campos (backward compatibility)
        $subcategoria = $this->post('subcategoria') ?: $this->post('nome');

        $data = [
            'subcategoria' => $subcategoria,
            'id_categoria' => $this->post('id_categoria'),
        ];

        if (empty($data['subcategoria']) || empty($data['id_categoria'])) {
            return $this->json(['success' => false, 'message' => 'Subcategoria e id_categoria são obrigatórios'], 400);
        }

        try {
            $id = $this->subcategoriaService->create($data);
            $subcategoria = $this->subcategoriaService->find($id);
            return $this->json([
                'success' => true,
                'message' => 'Subcategoria criada com sucesso',
                'data' => $subcategoria
            ], 201);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('subcategorias:read') &&
            !ApiKeyMiddleware::hasPermission('subcategorias:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        try {
            return $this->json(['success' => true, 'data' => $this->subcategoriaService->all()]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

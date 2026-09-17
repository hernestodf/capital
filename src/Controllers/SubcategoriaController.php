<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\SubcategoriaService;
use App\Auth\Rbac;

class SubcategoriaController extends Controller
{
    private SubcategoriaService $subcategoriaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->subcategoriaService = new SubcategoriaService();
    }

    public function listByCategoria($idCategoria): Response
    {
        if (!Rbac::check('fornecedores.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $subcategorias = $this->subcategoriaService->findByCategoria((int)$idCategoria);

        $data = array_map(fn($s) => [
            'id'           => (int)$s['id'],
            'id_categoria' => (int)$s['id_categoria'],
            'subcategoria' => $s['subcategoria'],
            'status'       => (int)$s['status'],
        ], $subcategorias);

        return $this->json(['success' => true, 'data' => $data]);
    }

    public function store(): Response
    {
        if (!Rbac::check('fornecedores.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $idCategoria = (int)($this->post('id_categoria') ?? 0);
        $nome        = trim($this->post('subcategoria') ?? '');

        if (!$idCategoria) {
            return $this->json(['success' => false, 'message' => 'Categoria é obrigatória'], 422);
        }
        if ($nome === '') {
            return $this->json(['success' => false, 'message' => 'Nome é obrigatório'], 422);
        }

        try {
            $id = $this->subcategoriaService->create([
                'id_categoria' => $idCategoria,
                'subcategoria' => $nome,
            ]);

            return $this->json([
                'success' => true,
                'data'    => [
                    'id'           => $id,
                    'id_categoria' => $idCategoria,
                    'subcategoria' => $nome,
                    'status'       => 1,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update($id): Response
    {
        if (!Rbac::check('fornecedores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $nome = trim($this->post('subcategoria') ?? '');
        if ($nome === '') {
            return $this->json(['success' => false, 'message' => 'Nome é obrigatório'], 422);
        }

        try {
            $this->subcategoriaService->update((int)$id, ['subcategoria' => $nome]);

            return $this->json([
                'success' => true,
                'data'    => ['id' => (int)$id, 'subcategoria' => $nome],
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('fornecedores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }
        if (!Csrf::validate($this->post('_csrf_token'))) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->subcategoriaService->delete((int)$id);

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

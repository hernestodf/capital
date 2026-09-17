<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\CategoriaService;
use App\Database\Connection;
use App\Auth\Rbac;

class CategoriaController extends Controller
{
    private CategoriaService $categoriaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->categoriaService = new CategoriaService();
    }

    public function listAll(): Response
    {
        if (!Rbac::check('fornecedores.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $categorias = $this->categoriaService->getAtivas();

        $data = array_map(fn($c) => [
            'id'        => (int)$c['id'],
            'categoria' => $c['categoria'],
            'status'    => (int)$c['status'],
        ], $categorias);

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

        $nome = trim($this->post('categoria') ?? '');
        if ($nome === '') {
            return $this->json(['success' => false, 'message' => 'Nome é obrigatório'], 422);
        }

        try {
            $id = $this->categoriaService->create(['categoria' => $nome]);

            return $this->json([
                'success' => true,
                'data'    => ['id' => $id, 'categoria' => $nome, 'status' => 1],
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

        $nome = trim($this->post('categoria') ?? '');
        if ($nome === '') {
            return $this->json(['success' => false, 'message' => 'Nome é obrigatório'], 422);
        }

        try {
            $this->categoriaService->update((int)$id, ['categoria' => $nome]);

            return $this->json([
                'success' => true,
                'data'    => ['id' => (int)$id, 'categoria' => $nome],
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

        $localId = (int)$id;

        try {
            $subs = Connection::query(
                "SELECT COUNT(*) as total FROM subcategorias WHERE id_categoria = ? AND status = 1",
                [$localId]
            );
            $total = (int)($subs[0]['total'] ?? 0);

            if ($total > 0) {
                return $this->json([
                    'success' => false,
                    'message' => "Esta categoria possui {$total} subcategoria(s) ativa(s). Remova-as primeiro.",
                ], 422);
            }

            $this->categoriaService->delete($localId);

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

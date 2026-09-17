<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Service\CategoriaSincronizadoService;
use App\Service\FuncaoService;

class PublicoCategoriaController extends Controller
{
    private CategoriaSincronizadoService $categoriaService;
    private FuncaoService $funcaoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->categoriaService = new CategoriaSincronizadoService();
        $this->funcaoService    = new FuncaoService();
    }

    /**
     * GET /public/categorias
     * Retorna todas as categorias ativas.
     */
    public function categorias(): Response
    {
        try {
            $categorias = $this->categoriaService->getAtivas();
            $data = array_map(function ($c) {
                return [
                    'id'   => (int)$c['id'],
                    'nome' => $c['nome'] ?? $c['categoria'] ?? '',
                ];
            }, $categorias);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /funcoes
     * Retorna lista de funções para atua_como de colaboradores.
     */
    public function funcoes(): Response
    {
        try {
            $funcoes = $this->funcaoService->getAtivas();
            $data = array_map(fn($f) => [
                'id'   => (int)$f['id'],
                'nome' => $f['nome'],
            ], $funcoes);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /public/subcategorias/{id_categoria}
     * Retorna subcategorias ativas de uma categoria.
     */
    public function subcategorias($idCategoria): Response
    {
        try {
            $id = (int)$idCategoria;
            if ($id <= 0) {
                return $this->json(['success' => false, 'message' => 'ID da categoria inválido'], 400);
            }
            $subcategorias = $this->categoriaService->getSubcategorias($id);
            $data = array_map(function ($s) {
                return [
                    'id'   => (int)$s['id'],
                    'nome' => $s['nome'] ?? $s['subcategoria'] ?? '',
                ];
            }, $subcategorias);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}


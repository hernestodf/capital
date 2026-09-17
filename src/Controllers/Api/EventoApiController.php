<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\EventoService;
use App\Http\Middleware\ApiKeyMiddleware;

/**
 * API Controller para Eventos
 * Endpoints: /api/v1/eventos/*
 */
class EventoApiController extends Controller
{
    private EventoService $eventoService;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->eventoService = new EventoService();
    }

    /**
     * GET /api/v1/eventos/ativos
     * Lista todos os eventos ativos (status = 1)
     *
     * Query params:
     *   estado     — (opcional) filtrar por estado: 'O' (Orçamento) ou 'L' (Locação)
     *   status_locacao — (opcional) filtrar por status da locação: 'A' (Andamento) ou 'F' (Finalizada)
     *   page       — (opcional) número da página
     *   perPage    — (opcional) registros por página
     *
     * Response:
     *   {
     *     "success": true,
     *     "data": [
     *       {
     *         "id": 1,
     *         "nome_evento": "Evento X",
     *         "local_evento": "Salão Y",
     *         "estado": "O",
     *         "status_locacao": "A",
     *         "data_inicio": "2026-06-01",
     *         "data_fim": "2026-06-01",
     *         "status": 1,
     *         ...
     *       }
     *     ],
     *     "pagination": { "total": 10, "page": 1, "totalPages": 1 }
     *   }
     */
    public function ativos(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('eventos:read')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: eventos:read'
            ], 403);
        }

        $estado       = $this->get('estado', '');
        $statusLocacao = $this->get('status_locacao', '');
        $page         = (int) $this->get('page', 1);
        $perPage      = (int) $this->get('perPage', 50);
        $perPage      = min($perPage, 200);

        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 50;

        $filters = ['status' => 1]; // eventos ativos (não deletados)

        if (!empty($estado)) {
            $filters['estado'] = strtoupper($estado);
        }
        if (!empty($statusLocacao)) {
            $filters['status_locacao'] = strtoupper($statusLocacao);
        }

        try {
            $result = $this->eventoService->search($filters, $page, $perPage);

            return $this->json([
                'success'    => true,
                'data'       => $result['data'] ?? [],
                'pagination' => [
                    'total'      => $result['pagination']['total']      ?? 0,
                    'page'       => $result['pagination']['page']       ?? 1,
                    'perPage'    => $result['pagination']['perPage']    ?? $perPage,
                    'totalPages' => $result['pagination']['totalPages'] ?? 0,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao buscar eventos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/eventos
     * Alias para ativos() — todos os eventos não excluídos
     */
    public function index(): Response
    {
        return $this->ativos();
    }
}

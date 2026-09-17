<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Service\MontagemService;
use App\Http\Middleware\ApiKeyMiddleware;

/**
 * API Controller para Montagem de OS (Montar produtos em eventos)
 * Endpoints: /api/v1/montagem/*
 */
class MontagemApiController extends Controller
{
    private MontagemService $montagemService;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->montagemService = new MontagemService();
    }

    /**
     * POST /api/v1/montagem/inserir-lote
     * Inserir seriais em lote em um evento (campo de texto)
     * Body: { id_evento, seriais (texto, um por linha ou separados por quebra), observacao (opcional) }
     */
    public function inserirLote(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('montagem:write')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: montagem:write'
            ], 403);
        }

        $idEvento = (int) $this->post('id_evento');
        $seriaisRaw = trim($this->post('seriais', ''));
        $observacao = trim($this->post('observacao', ''));

        if ($idEvento <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'id_evento é obrigatório'
            ], 400);
        }

        if (empty($seriaisRaw)) {
            return $this->json([
                'success' => false,
                'message' => 'seriais é obrigatório (um por linha)'
            ], 400);
        }

        // Separar por linhas (aceita \n, \r\n ou JS array)
        if (str_starts_with(trim($seriaisRaw), '[')) {
            $seriais = json_decode(trim($seriaisRaw), true);
            if (!is_array($seriais)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Formato de seriais inválido'
                ], 400);
            }
        } else {
            $seriais = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $seriaisRaw))));
        }

        if (empty($seriais)) {
            return $this->json([
                'success' => false,
                'message' => 'Nenhum serial válido informado'
            ], 400);
        }

        try {
            $result = $this->montagemService->inserirLote($seriais, $idEvento, null, $observacao);
            return $this->json([
                'success' => true,
                'message' => $result['message'] ?? 'Lote processado',
                'detalhes' => $result['detalhes'] ?? [],
                'seriais_inseridos' => $result['seriais_inseridos'] ?? [],
                'erros' => $result['erros'] ?? []
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao processar lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/montagem/listar/{idEvento}
     * Listar seriais inseridos em um evento
     */
    public function listar(int $idEvento): Response
    {
        if (!ApiKeyMiddleware::hasPermission('montagem:write') &&
            !ApiKeyMiddleware::hasPermission('montagem:read')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada'
            ], 403);
        }

        try {
            $montagens = $this->montagemService->findByEvento($idEvento);

            $data = array_map(function ($m) {
                return [
                    'id' => $m['id'],
                    'serial' => $m['serial'],
                    'produto' => $m['produto'],
                    'status' => $m['status'],
                    'sala' => $m['sala'] ?? null,
                    'observacao' => $m['observacao_item'] ?? '',
                    'evento' => $m['evento'] ?? ''
                ];
            }, $montagens);

            return $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao listar montagens: ' . $e->getMessage()
            ], 500);
        }
    }
}

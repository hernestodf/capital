<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Database\Connection;
use App\Repository\EventoColaboradorRepository;
use App\Repository\EventoColaboradorPresencaRepository;
use App\Service\EventoService;
use App\Http\Middleware\ApiKeyMiddleware;

class PresencaAppApiController extends Controller
{
    private EventoService $eventoService;
    private EventoColaboradorRepository $alocacaoRepo;
    private EventoColaboradorPresencaRepository $presencaRepo;

    public function __construct($request = null)
    {
        if ($request) {
            parent::__construct($request);
        }
        $this->eventoService = new EventoService();
        $this->alocacaoRepo  = new EventoColaboradorRepository();
        $this->presencaRepo  = new EventoColaboradorPresencaRepository();
    }

    /**
     * GET /api/v1/presencaapp/eventos
     * Lista eventos ativos com colaboradores alocados
     */
    public function eventos(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('presencaapp:read') &&
            !ApiKeyMiddleware::hasPermission('presencaapp:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        try {
            $rows = Connection::query(
                "SELECT e.id, e.nome_evento, e.local_evento, e.data_inicio, e.data_fim, e.status_locacao
                 FROM eventos e
                 WHERE e.status = 1
                 ORDER BY e.data_inicio DESC
                 LIMIT 50"
            );
            return $this->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/presencaapp/evento/{idEvento}/colaboradores
     * Lista colaboradores alocados a um evento
     */
    public function colaboradores($idEvento): Response
    {
        if (!ApiKeyMiddleware::hasPermission('presencaapp:read') &&
            !ApiKeyMiddleware::hasPermission('presencaapp:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        try {
            $rows = Connection::query(
                "SELECT ec.id as id_alocacao, ec.id_colaborador, c.nome as colaborador_nome,
                        c.foto, ec.funcao, ec.horario_entrada, ec.horario_saida,
                        ec.status as status_alocacao
                 FROM evento_colaboradores ec
                 INNER JOIN colaboradores c ON ec.id_colaborador = c.id
                 WHERE ec.id_evento = ? AND ec.status = 1
                 ORDER BY c.nome ASC",
                [(int) $idEvento]
            );
            return $this->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/presencaapp/entrada
     * Registra entrada de colaborador
     * Body: { id_alocacao, foto, lat, lng }
     */
    public function entrada(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('presencaapp:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        $body       = $this->post();
        $idAlocacao = (int) ($body['id_alocacao'] ?? 0);
        $foto       = $body['foto'] ?? '';
        $lat        = (float) ($body['lat'] ?? 0);
        $lng        = (float) ($body['lng'] ?? 0);

        if (!$idAlocacao) {
            return $this->json(['success' => false, 'message' => 'id_alocacao obrigatório'], 422);
        }

        try {
            $data = date('Y-m-d');
            $this->presencaRepo->registrarEntrada($idAlocacao, $data, $foto, $lat, $lng);
            return $this->json(['success' => true, 'message' => 'Entrada registrada']);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/presencaapp/saida
     * Registra saída de colaborador
     * Body: { id_alocacao, foto, lat, lng }
     */
    public function saida(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('presencaapp:write')) {
            return $this->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        $body       = $this->post();
        $idAlocacao = (int) ($body['id_alocacao'] ?? 0);
        $foto       = $body['foto'] ?? '';
        $lat        = (float) ($body['lat'] ?? 0);
        $lng        = (float) ($body['lng'] ?? 0);

        if (!$idAlocacao) {
            return $this->json(['success' => false, 'message' => 'id_alocacao obrigatório'], 422);
        }

        try {
            $data = date('Y-m-d');
            $this->presencaRepo->registrarSaida($idAlocacao, $data, $foto, $lat, $lng);
            return $this->json(['success' => true, 'message' => 'Saída registrada']);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

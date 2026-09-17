<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Request;
use App\Service\DevolucaoService;
use App\Service\MontagemService;
use App\Database\Connection;
use App\Http\Middleware\ApiKeyMiddleware;

class DevolucaoApiController extends Controller
{
    private DevolucaoService $devolucaoService;
    private MontagemService $montagemService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->devolucaoService = new DevolucaoService();
        $this->montagemService = new MontagemService();
    }

    public function listarSeriaisMontados(int $idEvento): Response
    {
        if (!ApiKeyMiddleware::hasPermission('devolucao:read') &&
            !ApiKeyMiddleware::hasPermission('devolucao:write')) {
            return $this->json(['ok' => false, 'error' => 'Permissão negada'], 403);
        }

        $seriais = Connection::query(
            "SELECT m.id, m.id_sala, m.status, s.serial, p.produto, sl.nome_sala,
                    d.status as status_devolucao, d.id as devolucao_id
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN produtos p ON p.id = s.id_produto
             LEFT JOIN salas sl ON sl.id = m.id_sala
             LEFT JOIN devolucoes d ON d.id_serial = m.id_serial AND d.id_evento = m.id_evento
             WHERE m.id_evento = ? AND m.status = 'montado'
             ORDER BY sl.nome_sala, p.produto, s.serial",
            [$idEvento]
        );
        return $this->json(['ok' => true, 'data' => $seriais]);
    }

    public function devolverSerial(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('devolucao:write')) {
            return $this->json(['ok' => false, 'error' => 'Permissão negada'], 403);
        }

        $serial = trim((string) ($this->input('serial') ?? $this->post('serial', '')));
        $idEvento = (int) ($this->input('evento_id') ?? $this->post('evento_id', 0));

        if ($idEvento <= 0) {
            return $this->json(['ok' => false, 'error' => 'evento_id é obrigatório'], 400);
        }
        if (empty($serial)) {
            return $this->json(['ok' => false, 'error' => 'serial é obrigatório'], 400);
        }

        try {
            $result = $this->devolucaoService->processarUnico($idEvento, $serial, 'A');
            return $this->json([
                'ok' => $result['success'] ?? false,
                'id' => $result['id'] ?? null,
                'serial' => $serial,
            ]);
        } catch (\Exception $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function devolverLote(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('devolucao:write')) {
            return $this->json(['ok' => false, 'error' => 'Permissão negada'], 403);
        }

        $idEvento = (int) ($this->input('evento_id') ?? $this->post('evento_id', 0));
        $rawSeriais = $this->input('seriais') ?? $this->post('seriais', '');

        if ($idEvento <= 0) {
            return $this->json(['ok' => false, 'error' => 'evento_id é obrigatório'], 400);
        }

        $seriais = [];
        if (is_array($rawSeriais)) {
            foreach ($rawSeriais as $item) {
                $s = is_array($item) ? trim($item['serial'] ?? '') : trim((string) $item);
                if (!empty($s)) $seriais[] = ['serial' => $s];
            }
        } else {
            $seriais = array_map(function($s) {
                return ['serial' => trim($s)];
            }, array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", (string) $rawSeriais)))));
        }

        if (empty($seriais)) {
            return $this->json(['ok' => false, 'error' => 'Nenhum serial informado'], 400);
        }

        $inseridos = [];
        $falhas = [];

        foreach ($seriais as $item) {
            $s = $item['serial'];
            try {
                $r = $this->devolucaoService->processarUnico($idEvento, $s, 'A');
                if ($r['success'] ?? false) {
                    $inseridos[] = ['serial' => $s, 'id' => $r['id'] ?? null];
                } else {
                    $falhas[] = ['serial' => $s, 'mensagem' => $r['error'] ?? 'Erro desconhecido'];
                }
            } catch (\Exception $e) {
                $falhas[] = ['serial' => $s, 'mensagem' => $e->getMessage()];
            }
        }

        return $this->json([
            'ok' => true,
            'inseridos' => $inseridos,
            'falhas' => $falhas,
            'resumo' => [
                'total_enviados' => count($inseridos) + count($falhas),
                'sucesso' => count($inseridos),
                'erros' => count($falhas),
            ],
        ]);
    }

    public function listarDevolucoes(int $idEvento): Response
    {
        if (!ApiKeyMiddleware::hasPermission('devolucao:read') &&
            !ApiKeyMiddleware::hasPermission('devolucao:write')) {
            return $this->json(['ok' => false, 'error' => 'Permissão negada'], 403);
        }

        $devolucoes = $this->devolucaoService->findByEvento($idEvento);
        return $this->json(['ok' => true, 'data' => $devolucoes]);
    }

    public function stats(int $idEvento): Response
    {
        if (!ApiKeyMiddleware::hasPermission('devolucao:read') &&
            !ApiKeyMiddleware::hasPermission('devolucao:write')) {
            return $this->json(['ok' => false, 'error' => 'Permissão negada'], 403);
        }

        $montados = Connection::query(
            "SELECT COUNT(*) as total FROM montagens WHERE id_evento = ? AND status = 'montado'",
            [$idEvento]
        );
        $total = (int) ($montados[0]['total'] ?? 0);

        $devolvidos = $this->devolucaoService->getStatsByEvento($idEvento);
        $devolvidos['total_montados'] = $total;

        return $this->json(['ok' => true, 'data' => $devolvidos]);
    }
}

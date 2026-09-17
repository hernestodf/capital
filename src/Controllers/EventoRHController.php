<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Auth\Rbac;
use App\Service\EventoColaboradorService;
use App\Service\UploadService;

/**
 * Controller para Recursos Humanos de Eventos (rotas internas autenticadas)
 */
class EventoRHController extends Controller
{
    private EventoColaboradorService $service;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->service = new EventoColaboradorService();
    }

    /**
     * Listar colaboradores alocados ao evento (AJAX)
     */
    public function listar(int $idEvento): Response
    {
        if (!Rbac::check('rh.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $colaboradores = $this->service->findByEvento($idEvento);

        // Calcular dias e valor total por colaborador
        foreach ($colaboradores as &$c) {
            $dtInicio = new \DateTime($c['data_inicio']);
            $dtFim = new \DateTime($c['data_fim']);
            $dias = max(1, (int)$dtInicio->diff($dtFim)->days + 1);
            $c['dias'] = $dias;
            $c['total_bruto'] = (float)$c['valor_diaria'] * $dias;
        }
        unset($c);

        // Otimizado: buscar todas presencas do dia em uma so query (evitar N+1)
        $dataHoje = date('Y-m-d');
        $presencasHoje = $this->service->findPresencasHoje($idEvento, $dataHoje);
        $presencasMap = [];
        foreach ($presencasHoje as $p) {
            $presencasMap[(int)$p['id_alocacao']] = $p;
        }
        foreach ($colaboradores as &$c) {
            $c['presenca_hoje'] = $presencasMap[(int)$c['id']] ?? null;
        }

        $stats = $this->service->getStats($idEvento, $dataHoje, $colaboradores);

        return $this->json([
            'success' => true,
            'data' => $colaboradores,
            'stats' => $stats
        ]);
    }

    /**
     * Ver todas presencas de uma alocacao (AJAX)
     */
    public function verPresencas(int $idAlocacao): Response
    {
        if (!Rbac::check('rh.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $result = $this->service->verPresencas($idAlocacao);
        return $this->json($result);
    }

    /**
     * Alocar colaborador em evento (AJAX POST)
     */
    public function alocar(): Response
    {
        if (!Rbac::check('rh.criar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $result = $this->service->alocar([
                'id_evento' => $this->post('id_evento'),
                'id_colaborador' => $this->post('id_colaborador'),
                'funcao' => $this->post('funcao'),
                'data_inicio' => $this->post('data_inicio'),
                'data_fim' => $this->post('data_fim'),
                'hora_inicio' => $this->post('hora_inicio'),
                'hora_fim' => $this->post('hora_fim'),
                'valor_diaria' => $this->post('valor_diaria'),
                'data_vencimento_pagamento' => $this->post('data_vencimento_pagamento', null),
            ]);

            return $this->json($result);
        } catch (\Exception $e) {
            \App\Core\Logger::error('[EventoRHController::alocar] Erro: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->json(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Desalocar colaborador (AJAX POST)
     */
    public function desalocar(int $id): Response
    {
        if (!Rbac::check('rh.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $result = $this->service->desalocar($id);
        return $this->json($result);
    }

    /**
     * Registrar pagamento com comprovante (AJAX POST)
     */
    public function registrarPagamento(int $id): Response
    {
        if (!Rbac::check('rh.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $dataPagamento = $this->post('data_pagamento');
        if (empty($dataPagamento)) {
            return $this->json(['success' => false, 'error' => 'Data de pagamento e obrigatoria']);
        }

        // Upload de comprovante
        $comprovantePath = null;
        if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/comprovantes/';
            $ext = strtolower(pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION));
            $filename = 'comprovante_' . $id . '_' . time() . '.' . $ext;

            $result = UploadService::upload(
                $_FILES['comprovante'],
                $uploadDir,
                $filename,
                ['pdf', 'jpg', 'jpeg', 'png']
            );

            if (!$result['success']) {
                return $this->json(['success' => false, 'error' => $result['error']]);
            }

            $comprovantePath = 'uploads/comprovantes/' . $filename;
        }

        $result = $this->service->registrarPagamento($id, $dataPagamento, $comprovantePath);

        return $this->json($result);
    }

    /**
     * Marcar para enviar pagamento (AJAX POST)
     */
/**
     * Marcar para enviar pagamento (AJAX POST)
     */
    public function enviarPagamento(int $id): Response
    {
        if (!Rbac::check('rh.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $result = $this->service->enviarPagamento($id);
        return $this->json($result);
    }

}

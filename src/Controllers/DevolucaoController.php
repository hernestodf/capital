<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Auth\Rbac;
use App\Core\Response;
use App\Core\Request;
use App\Service\DevolucaoService;

/**
 * Controller para devolucao (desmontagem) de produtos em eventos
 * Usado pelo partial edit-devolver-os.php via AJAX
 */
class DevolucaoController extends Controller
{
    private DevolucaoService $devolucaoService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->devolucaoService = new DevolucaoService();
    }

    /**
     * Processar devolucao unica de serial (AJAX)
     * POST /devolucao/processar-unico
     */
    public function processarUnico(): Response
    {
        if (!Rbac::check('devolucao.criar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $idEvento = (int) $this->post('id_evento');
        $serial = trim($this->post('serial', ''));
        $status = $this->post('status', 'A');
        $motivo = trim($this->post('motivo', ''));
        $solucao = trim($this->post('solucao', ''));

        if ($idEvento <= 0) {
            return $this->json(['success' => false, 'error' => 'Evento nao informado']);
        }

        if (empty($serial)) {
            return $this->json(['success' => false, 'error' => 'Serial e obrigatorio']);
        }

        if (!in_array($status, ['A', 'P', 'S'])) {
            return $this->json(['success' => false, 'error' => 'Status invalido']);
        }

        if ($status === 'P' && empty($motivo)) {
            return $this->json(['success' => false, 'error' => 'Motivo da pendencia e obrigatorio']);
        }

        if ($status === 'S' && empty($solucao)) {
            return $this->json(['success' => false, 'error' => 'Solucao da pendencia e obrigatoria']);
        }

        try {
            $motivoParam = ($status === 'P') ? $motivo : null;
            $solucaoParam = ($status === 'S') ? $solucao : null;
            
            $result = $this->devolucaoService->processarUnico($idEvento, $serial, $status, $motivoParam, $solucaoParam);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Processar devolucao em lote (AJAX)
     * POST /devolucao/processar-lote
     */
    public function processarLote(): Response
    {
        if (!Rbac::check('devolucao.criar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $idEvento = (int) $this->post('id_evento');
        $seriaisRaw = trim($this->post('seriais', ''));

        if ($idEvento <= 0) {
            return $this->json(['success' => false, 'error' => 'Evento nao informado']);
        }

        if (empty($seriaisRaw)) {
            return $this->json(['success' => false, 'error' => 'Nenhum serial informado']);
        }

        // Separar seriais por linha
        $seriais = array_filter(array_map('trim', explode("\n", $seriaisRaw)));

        if (empty($seriais)) {
            return $this->json(['success' => false, 'error' => 'Nenhum serial valido informado']);
        }

        try {
            $result = $this->devolucaoService->processarLote($idEvento, $seriais);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Resolver pendencia de devolucao (AJAX)
     * POST /devolucao/resolver-pendencia/{id}
     */
    public function resolverPendencia($id): Response
    {
        if (!Rbac::check('devolucao.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $solucao = trim($this->post('solucao', ''));

        if (empty($solucao)) {
            return $this->json(['success' => false, 'error' => 'Solucao e obrigatoria']);
        }

        try {
            $result = $this->devolucaoService->resolverPendencia((int) $id, $solucao);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Alterar status de devolucao inline na listagem (AJAX)
     * POST /devolucao/alterar-status/{id}
     */
    public function alterarStatus($id): Response
    {
        if (!Rbac::check('devolucao.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $novoStatus = $this->post('status', '');
        $motivo = trim($this->post('motivo', ''));
        $solucao = trim($this->post('solucao', ''));

        if (!in_array($novoStatus, ['A', 'P', 'S'])) {
            return $this->json(['success' => false, 'error' => 'Status invalido']);
        }

        if ($novoStatus === 'P' && empty($motivo)) {
            return $this->json(['success' => false, 'error' => 'Motivo da pendencia e obrigatorio']);
        }

        try {
            // Buscar devolucao antes de validar (precisamos do status atual)
            $devolucao = $this->devolucaoService->findById((int) $id);
            if (!$devolucao) {
                return $this->json(['success' => false, 'error' => 'Devolucao nao encontrada']);
            }

            // Solucao so e obrigatoria quando mudando de P para S (resolvendo pendencia)
            if ($novoStatus === 'S' && $devolucao['status'] === 'P' && empty($solucao)) {
                return $this->json(['success' => false, 'error' => 'Solucao e obrigatoria']);
            }

            // Usar o metodo resolverPendencia se for P -> S
            if ($novoStatus === 'S' && $devolucao['status'] === 'P') {
                // Resolver pendencia existente
                $result = $this->devolucaoService->resolverPendencia((int) $id, $solucao);
            } elseif ($novoStatus === 'P') {
                // Mudar para pendencia - precisa atualizar a devolucao
                $this->devolucaoService->updateStatus((int) $id, 'P', $motivo);
                $result = ['success' => true];
            } else {
                // Mudar para ativo
                $this->devolucaoService->updateStatus((int) $id, 'A');
                $result = ['success' => true];
            }

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Historico de devolucoes de um serial (AJAX)
     * GET /devolucao/historico-serial?id_serial=X
     */
    public function historicoPorSerial(): Response
    {
        if (!Rbac::check('devolucao.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $idSerial = (int) $this->get('id_serial');

        if ($idSerial <= 0) {
            return $this->json(['success' => false, 'error' => 'Serial invalido']);
        }

        try {
            $devolucoes = $this->devolucaoService->findBySerial($idSerial);
            return $this->json(['success' => true, 'data' => $devolucoes]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Listar todas as devolucoes de um evento (AJAX)
     * GET /devolucao/listar/{idEvento}
     */
    public function listar($idEvento): Response
    {
        if (!Rbac::check('devolucao.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $devolucoes = $this->devolucaoService->findByEvento((int) $idEvento);
            return $this->json(['success' => true, 'data' => $devolucoes]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Obter estatisticas de devolucao de um evento (AJAX)
     * GET /devolucao/stats/{idEvento}
     */
    public function stats($idEvento): Response
    {
        if (!Rbac::check('devolucao.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $stats = $this->devolucaoService->getStatsByEvento((int) $idEvento);
            return $this->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

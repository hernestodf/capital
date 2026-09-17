<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Env;
use App\Service\AIService;
use App\Auth\Rbac;

/**
 * AI Controller - Interface para o sistema de agentes de IA
 *
 * Gerencia rotas web e API para o sistema multi-agente
 */
class AIController extends Controller
{
    private AIService $aiService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->aiService = new AIService();
    }

    /**
     * Dashboard dos agentes de IA (Web)
     */
    public function dashboard(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->redirect(Env::get('BASE_URL', '') . '/dashboard');
        }

        $stats = $this->aiService->getStats();
        $agents = $this->aiService->getAgents();
        $recentTasks = $this->aiService->getRecentTasks(10);

        return $this->view('ai/dashboard', [
            'stats' => $stats,
            'agents' => $agents,
            'recentTasks' => $recentTasks
        ]);
    }

    /**
     * Lista de agentes (Web)
     */
    public function agents(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $agents = $this->aiService->getAgents();

        return $this->view('ai/agents', [
            'agents' => $agents
        ]);
    }

    /**
     * Detalhes de um agente (Web)
     */
    public function agentDetail(string $id): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $agent = $this->aiService->getAgent($id);
        $metrics = $this->aiService->getAgentMetrics($id);

        return $this->view('ai/agent-detail', [
            'agent' => $agent,
            'metrics' => $metrics
        ]);
    }

    /**
     * Sistema de aprendizado (Web)
     */
    public function learning(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $patterns = $this->aiService->getPatterns();
        $corrections = $this->aiService->getCorrections();

        return $this->view('ai/learning', [
            'patterns' => $patterns,
            'corrections' => $corrections
        ]);
    }

    /**
     * Executar agente (Web)
     */
    public function execute(): Response
    {
        if (!Rbac::check('ai.execute')) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $data = $this->request->post();
        $agentId = $data['agent'] ?? '';
        $context = $data['context'] ?? [];

        $result = $this->aiService->executeAgent($agentId, $context);

        return $this->json([
            'success' => true,
            'result' => $result
        ]);
    }

    /**
     * Orquestrar tarefa (Web)
     */
    public function orchestrate(): Response
    {
        if (!Rbac::check('ai.orchestrate')) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $data = $this->request->post();
        $request = $data['request'] ?? '';

        $result = $this->aiService->orchestrate($request);

        return $this->json([
            'success' => true,
            'result' => $result
        ]);
    }

    // ==================== API ENDPOINTS ====================

    /**
     * API: Listar agentes
     */
    public function apiAgents(): Response
    {
        if (!Rbac::check('ai')) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $agents = $this->aiService->getAgents();

        return $this->json([
            'success' => true,
            'data' => $agents
        ]);
    }

    /**
     * API: Executar agente
     */
    public function apiExecute(): Response
    {
        $data = $this->request->json();
        $agentId = $data['agent'] ?? '';
        $context = $data['context'] ?? [];

        if (empty($agentId)) {
            return $this->json([
                'success' => false,
                'error' => 'Agente não especificado'
            ], 400);
        }

        $result = $this->aiService->executeAgent($agentId, $context);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Orquestrar tarefa
     */
    public function apiOrchestrate(): Response
    {
        $data = $this->request->json();
        $request = $data['request'] ?? '';

        if (empty($request)) {
            return $this->json([
                'success' => false,
                'error' => 'Requisição não especificada'
            ], 400);
        }

        $result = $this->aiService->orchestrate($request);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Registrar padrão de aprendizado
     */
    public function apiLearnPattern(): Response
    {
        $data = $this->request->json();

        $result = $this->aiService->learnPattern($data);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Consultar padrões
     */
    public function apiGetPatterns(): Response
    {
        $contexto = $this->request->get('contexto');
        $tipo = $this->request->get('tipo');

        $patterns = $this->aiService->getPatterns($contexto, $tipo);

        return $this->json([
            'success' => true,
            'data' => $patterns
        ]);
    }

    /**
     * API: Registrar métrica
     */
    public function apiLearnMetric(): Response
    {
        $data = $this->request->json();

        $result = $this->aiService->learnMetric($data);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Enviar feedback
     */
    public function apiLearnFeedback(): Response
    {
        $data = $this->request->json();

        if (empty($data['agente']) || empty($data['tipo'])) {
            return $this->json([
                'success' => false,
                'error' => 'Agente e tipo são obrigatórios'
            ], 400);
        }

        $result = $this->aiService->learnFeedback($data);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Registrar interação
     */
    public function apiLearnInteraction(): Response
    {
        $data = $this->request->json();

        if (empty($data['requisicao'])) {
            return $this->json([
                'success' => false,
                'error' => 'Requisição é obrigatória'
            ], 400);
        }

        $result = $this->aiService->learnInteraction($data);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Salvar preferência
     */
    public function apiLearnPreference(): Response
    {
        $data = $this->request->json();

        if (empty($data['usuario_id']) || empty($data['chave']) || empty($data['valor'])) {
            return $this->json([
                'success' => false,
                'error' => 'usuario_id, chave e valor são obrigatórios'
            ], 400);
        }

        $result = $this->aiService->learnPreference($data);

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * API: Consultar preferências
     */
    public function apiGetPreferences(): Response
    {
        $usuarioId = $this->request->get('usuario_id');

        if (empty($usuarioId)) {
            return $this->json([
                'success' => false,
                'error' => 'usuario_id é obrigatório'
            ], 400);
        }

        $preferences = $this->aiService->getPreferences($usuarioId);

        return $this->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * API: Consultar feedbacks
     */
    public function apiGetFeedbacks(): Response
    {
        $agente = $this->request->get('agente');
        $tipo = $this->request->get('tipo');

        $feedbacks = $this->aiService->getFeedbacks($agente, $tipo);

        return $this->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * API: Estatísticas
     */
    public function apiStats(): Response
    {
        $stats = $this->aiService->getFullStats();

        return $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * API: Health check
     */
    public function apiHealth(): Response
    {
        return $this->json([
            'success' => true,
            'status' => 'healthy',
            'version' => '4.0',
            'agents' => count($this->aiService->getAgents())
        ]);
    }
}

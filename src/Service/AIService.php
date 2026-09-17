<?php

namespace App\Service;

use App\Repository\AILearningRepository;

/**
 * AI Service - Orquestração do sistema multi-agente
 */
class AIService extends BaseService
{
    protected string $entityName = 'AI';

    private array $agents = [];
    private string $agentsPath;

    public function __construct()
    {
        // Call parent with AILearningRepository instance
        parent::__construct(new AILearningRepository());
        $this->agentsPath = dirname(__DIR__, 2) . '/.ai/agents/';
        $this->loadAgents();
    }

    /**
     * Carrega definições dos agentes
     */
    private function loadAgents(): void
    {
        $agentFiles = glob($this->agentsPath . 'AGENT_*.md');

        foreach ($agentFiles as $file) {
            $name = basename($file, '.md');
            $id = strtolower(str_replace(['AGENT_', '.md'], '', $name));

            $this->agents[$id] = [
                'id' => $id,
                'name' => $name,
                'file' => $file,
                'content' => file_get_contents($file)
            ];
        }
    }

    /**
     * Retorna lista de agentes
     */
    public function getAgents(): array
    {
        return array_map(function($agent) {
            return [
                'id' => $agent['id'],
                'name' => $this->getAgentName($agent['id']),
                'description' => $this->getAgentDescription($agent['id'])
            ];
        }, $this->agents);
    }

    /**
     * Retorna detalhes de um agente
     */
    public function getAgent(string $id): ?array
    {
        return $this->agents[$id] ?? null;
    }

    /**
     * Executa um agente específico
     */
    public function executeAgent(string $agentId, array $context): array
    {
        $agent = $this->getAgent($agentId);

        if (!$agent) {
            return [
                'success' => false,
                'error' => "Agente '{$agentId}' não encontrado"
            ];
        }

        // Registrar execução
        $executionId = $this->repository->logExecution([
            'agent_id' => $agentId,
            'context' => json_encode($context),
            'started_at' => date('Y-m-d H:i:s')
        ]);

        // Simular execução do agente
        $result = $this->simulateAgentExecution($agentId, $context);

        // Atualizar log
        $this->repository->updateExecution($executionId, [
            'completed_at' => date('Y-m-d H:i:s'),
            'result' => json_encode($result),
            'success' => $result['success'] ?? false
        ]);

        return $result;
    }

    /**
     * Orquestra execução de uma tarefa completa
     */
    public function orchestrate(string $request): array
    {
        $workflow = [
            ['agent' => 'vibe', 'action' => 'interpretar'],
            ['agent' => 'planner', 'action' => 'planejar'],
            ['agent' => 'analisador', 'action' => 'analisar'],
            ['agent' => 'security', 'action' => 'validar'],
            ['agent' => 'implementador', 'action' => 'implementar'],
            ['agent' => 'component', 'action' => 'componentizar'],
            ['agent' => 'validador', 'action' => 'validar'],
            ['agent' => 'learning', 'action' => 'aprender']
        ];

        $results = [];
        $context = ['request' => $request];

        foreach ($workflow as $step) {
            $result = $this->executeAgent($step['agent'], $context);
            $results[] = [
                'step' => $step,
                'result' => $result
            ];

            if (!($result['success'] ?? false)) {
                break;
            }

            // Atualizar contexto para próximo passo
            $context['previous_result'] = $result;
        }

        return [
            'success' => true,
            'request' => $request,
            'workflow' => $results,
            'completed' => count($results) === count($workflow)
        ];
    }

    /**
     * Registra padrão de aprendizado
     */
    public function learnPattern(array $data): array
    {
        $pattern = [
            'agente' => $data['agente'] ?? '',
            'tipo' => $data['tipo'] ?? '',
            'contexto' => $data['contexto'] ?? '',
            'padrao' => $data['padrao'] ?? '',
            'exemplo' => $data['exemplo'] ?? '',
            'sucesso_rate' => $data['sucesso_rate'] ?? 1.0
        ];

        $id = $this->repository->savePattern($pattern);

        return [
            'success' => true,
            'pattern_id' => $id
        ];
    }

    /**
     * Retorna padrões de aprendizado
     */
    public function getPatterns(?string $contexto = null, ?string $tipo = null): array
    {
        return $this->repository->getPatterns($contexto, $tipo);
    }

    /**
     * Registra métrica
     */
    public function learnMetric(array $data): array
    {
        $metric = [
            'tarefa_id' => $data['tarefa_id'] ?? uniqid(),
            'agente' => $data['agente'] ?? '',
            'acao' => $data['acao'] ?? '',
            'tempo_execucao' => $data['tempo_execucao'] ?? 0,
            'linhas_codigo' => $data['linhas_codigo'] ?? 0,
            'bugs_encontrados' => $data['bugs_encontrados'] ?? 0,
            'retrabalho' => $data['retrabalho'] ?? false,
            'score_validacao' => $data['score_validacao'] ?? 0
        ];

        $id = $this->repository->saveMetric($metric);

        return [
            'success' => true,
            'metric_id' => $id
        ];
    }

    /**
     * Registra feedback
     */
    public function learnFeedback(array $data): array
    {
        $feedback = [
            'tarefa_id' => $data['tarefa_id'] ?? uniqid(),
            'agente' => $data['agente'],
            'tipo' => $data['tipo'],
            'rating' => $data['rating'] ?? 3,
            'comentario' => $data['comentario'] ?? '',
            'contexto' => $data['contexto'] ?? [],
            'aprendido' => $data['aprendido'] ?? 0
        ];

        $id = $this->repository->saveFeedback($feedback);

        return [
            'success' => true,
            'feedback_id' => $id
        ];
    }

    /**
     * Registra interação
     */
    public function learnInteraction(array $data): array
    {
        $interaction = [
            'usuario_id' => $data['usuario_id'] ?? '',
            'requisicao' => $data['requisicao'],
            'agentes_executados' => $data['agentes_executados'] ?? [],
            'resultado' => $data['resultado'] ?? 'sucesso',
            'tempo_total' => $data['tempo_total'] ?? 0,
            'arquivos_gerados' => $data['arquivos_gerados'] ?? [],
            'feedback_recebido' => $data['feedback_recebido'] ?? 'neutro'
        ];

        $id = $this->repository->saveInteraction($interaction);

        return [
            'success' => true,
            'interaction_id' => $id
        ];
    }

    /**
     * Salva preferência
     */
    public function learnPreference(array $data): array
    {
        $preference = [
            'usuario_id' => $data['usuario_id'],
            'tipo' => $data['tipo'] ?? 'geral',
            'chave' => $data['chave'],
            'valor' => $data['valor']
        ];

        $id = $this->repository->savePreference($preference);

        return [
            'success' => true,
            'preference_id' => $id
        ];
    }

    /**
     * Retorna preferências do usuário
     */
    public function getPreferences(string $usuarioId): array
    {
        return $this->repository->getUserPreferences($usuarioId);
    }

    /**
     * Retorna feedbacks
     */
    public function getFeedbacks(?string $agente = null, ?string $tipo = null): array
    {
        return $this->repository->getFeedbacks($agente, $tipo);
    }

    /**
     * Retorna estatísticas completas
     */
    public function getFullStats(): array
    {
        return $this->repository->getFullStats();
    }

    /**
     * Retorna estatísticas
     */
    public function getStats(): array
    {
        return [
            'total_agents' => count($this->agents),
            'total_patterns' => $this->repository->countPatterns(),
            'total_metrics' => $this->repository->countMetrics(),
            'total_executions' => $this->repository->countExecutions(),
            'success_rate' => $this->repository->getSuccessRate(),
            'avg_execution_time' => $this->repository->getAvgExecutionTime()
        ];
    }

    /**
     * Retorna tarefas recentes
     */
    public function getRecentTasks(int $limit = 10): array
    {
        return $this->repository->getRecentExecutions($limit);
    }

    /**
     * Retorna métricas de um agente
     */
    public function getAgentMetrics(string $agentId): array
    {
        return $this->repository->getAgentMetrics($agentId);
    }

    /**
     * Retorna correções registradas
     */
    public function getCorrections(): array
    {
        return $this->repository->getCorrections();
    }

    // ==================== MÉTODOS PRIVADOS ====================

    private function getAgentName(string $id): string
    {
        $names = [
            'orquestrador' => 'Orquestrador',
            'vibe' => 'Vibe (Interpretação)',
            'planner' => 'Planner (Planejamento)',
            'analisador' => 'Analisador',
            'security' => 'Security',
            'implementador' => 'Implementador',
            'component' => 'Component',
            'validador' => 'Validador',
            'learning' => 'Learning'
        ];

        return $names[$id] ?? ucfirst($id);
    }

    private function getAgentDescription(string $id): string
    {
        $descriptions = [
            'orquestrador' => 'Coordena todos os agentes',
            'vibe' => 'Traduz pedidos em especificações',
            'planner' => 'Divide tarefas em etapas',
            'analisador' => 'Analisa impacto no sistema',
            'security' => 'Valida segurança',
            'implementador' => 'Gera código',
            'component' => 'Cria componentes',
            'validador' => 'Valida qualidade',
            'learning' => 'Registra aprendizados'
        ];

        return $descriptions[$id] ?? 'Agente especializado';
    }

    private function simulateAgentExecution(string $agentId, array $context): array
    {
        // Simulação de execução do agente
        // Em produção, isso seria integrado com LLM

        sleep(1); // Simular processamento

        return [
            'success' => true,
            'agent' => $agentId,
            'message' => "Agente {$agentId} executado com sucesso",
            'context_received' => $context,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

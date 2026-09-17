<?php

namespace App\Repository;

/**
 * AI Learning Repository - Gerencia dados de aprendizado dos agentes
 */
class AILearningRepository extends BaseRepository
{
    protected string $table = 'ai_learning';
    protected string $primaryKey = 'id';

    /**
     * Salva um padrão de aprendizado
     */
    public function savePattern(array $data): int
    {
        // Verificar se padrão já existe
        $existing = $this->query(
            "SELECT id, frequencia FROM {$this->table} 
             WHERE agente = ? AND tipo = ? AND contexto = ? AND padrao = ?",
            [$data['agente'], $data['tipo'], $data['contexto'], $data['padrao']]
        );

        if (!empty($existing)) {
            // Atualizar frequência
            $this->query(
                "UPDATE {$this->table} 
                 SET frequencia = frequencia + 1, 
                     sucesso_rate = ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [$data['sucesso_rate'], $existing[0]['id']]
            );
            return (int) $existing[0]['id'];
        }

        // Inserir novo
        return $this->insert([
            'agente' => $data['agente'],
            'tipo' => $data['tipo'],
            'contexto' => $data['contexto'],
            'padrao' => $data['padrao'],
            'exemplo' => $data['exemplo'] ?? '',
            'sucesso_rate' => $data['sucesso_rate'] ?? 1.0,
            'frequencia' => 1
        ]);
    }

    /**
     * Retorna padrões de aprendizado
     */
    public function getPatterns(?string $contexto = null, ?string $tipo = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($contexto) {
            $sql .= " AND contexto = ?";
            $params[] = $contexto;
        }

        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }

        $sql .= " ORDER BY frequencia DESC, sucesso_rate DESC";

        return $this->query($sql, $params);
    }

    /**
     * Conta total de padrões
     */
    public function countPatterns(): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table}");
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Salva uma métrica
     */
    public function saveMetric(array $data): int
    {
        return $this->insert([
            'tarefa_id' => $data['tarefa_id'],
            'agente' => $data['agente'],
            'acao' => $data['acao'],
            'tempo_execucao' => $data['tempo_execucao'],
            'linhas_codigo' => $data['linhas_codigo'],
            'bugs_encontrados' => $data['bugs_encontrados'],
            'retrabalho' => $data['retrabalho'] ? 1 : 0,
            'score_validacao' => $data['score_validacao']
        ]);
    }

    /**
     * Conta total de métricas
     */
    public function countMetrics(): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM ai_metrics");
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Registra execução de agente
     */
    public function logExecution(array $data): int
    {
        return $this->insert([
            'agent_id' => $data['agent_id'],
            'context' => $data['context'],
            'started_at' => $data['started_at']
        ]);
    }

    /**
     * Atualiza execução
     */
    public function updateExecution(int $id, array $data): bool
    {
        return $this->update($id, [
            'completed_at' => $data['completed_at'],
            'result' => $data['result'],
            'success' => $data['success'] ? 1 : 0
        ]);
    }

    /**
     * Conta total de execuções
     */
    public function countExecutions(): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM ai_executions");
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Retorna taxa de sucesso
     */
    public function getSuccessRate(): float
    {
        $result = $this->query(
            "SELECT 
                COUNT(CASE WHEN success = 1 THEN 1 END) as success_count,
                COUNT(*) as total
             FROM ai_executions"
        );

        if (empty($result) || $result[0]['total'] == 0) {
            return 0.0;
        }

        return round($result[0]['success_count'] / $result[0]['total'] * 100, 2);
    }

    /**
     * Retorna tempo médio de execução
     */
    public function getAvgExecutionTime(): float
    {
        $result = $this->query(
            "SELECT AVG(tempo_execucao) as avg_time FROM ai_metrics"
        );

        return round((float) ($result[0]['avg_time'] ?? 0), 2);
    }

    /**
     * Retorna execuções recentes
     */
    public function getRecentExecutions(int $limit = 10): array
    {
        return $this->query(
            "SELECT * FROM ai_executions 
             ORDER BY started_at DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Retorna métricas de um agente específico
     */
    public function getAgentMetrics(string $agentId): array
    {
        return $this->query(
            "SELECT 
                COUNT(*) as total_execucoes,
                AVG(tempo_execucao) as tempo_medio,
                AVG(score_validacao) as score_medio,
                SUM(bugs_encontrados) as total_bugs,
                SUM(CASE WHEN retrabalho = 1 THEN 1 ELSE 0 END) as total_retrabalho
             FROM ai_metrics 
             WHERE agente = ?",
            [$agentId]
        );
    }

    /**
     * Retorna correções registradas
     */
    public function getCorrections(): array
    {
        return $this->query(
            "SELECT * FROM ai_corrections ORDER BY created_at DESC"
        );
    }

    /**
     * Salva feedback do usuário
     */
    public function saveFeedback(array $data): int
    {
        return $this->insert([
            'tarefa_id' => $data['tarefa_id'] ?? '',
            'agente' => $data['agente'],
            'tipo' => $data['tipo'],
            'rating' => $data['rating'] ?? 3,
            'comentario' => $data['comentario'] ?? '',
            'contexto' => json_encode($data['contexto'] ?? []),
            'aprendido' => $data['aprendido'] ?? 0
        ]);
    }

    /**
     * Retorna feedbacks
     */
    public function getFeedbacks(?string $agente = null, ?string $tipo = null): array
    {
        $sql = "SELECT * FROM ai_feedbacks WHERE 1=1";
        $params = [];

        if ($agente) {
            $sql .= " AND agente = ?";
            $params[] = $agente;
        }

        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 50";

        return $this->query($sql, $params);
    }

    /**
     * Retorna rating médio
     */
    public function getAvgRating(?string $agente = null): float
    {
        $sql = "SELECT AVG(rating) as avg_rating FROM ai_feedbacks";
        $params = [];

        if ($agente) {
            $sql .= " WHERE agente = ?";
            $params[] = $agente;
        }

        $result = $this->query($sql, $params);
        return round((float) ($result[0]['avg_rating'] ?? 0), 2);
    }

    /**
     * Registra interação
     */
    public function saveInteraction(array $data): int
    {
        return $this->insert([
            'usuario_id' => $data['usuario_id'] ?? '',
            'requisicao' => $data['requisicao'],
            'agentes_executados' => json_encode($data['agentes_executados'] ?? []),
            'resultado' => $data['resultado'] ?? 'sucesso',
            'tempo_total' => $data['tempo_total'] ?? 0,
            'arquivos_gerados' => json_encode($data['arquivos_gerados'] ?? []),
            'feedback_recebido' => $data['feedback_recebido'] ?? 'neutro'
        ]);
    }

    /**
     * Salva preferência do usuário
     */
    public function savePreference(array $data): int
    {
        // Verificar se preferência já existe
        $existing = $this->query(
            "SELECT id, frequencia_uso FROM ai_preferences 
             WHERE usuario_id = ? AND chave = ?",
            [$data['usuario_id'], $data['chave']]
        );

        if (!empty($existing)) {
            $this->query(
                "UPDATE ai_preferences 
                 SET valor = ?, frequencia_uso = frequencia_uso + 1,
                     ultima_atualizacao = NOW()
                 WHERE id = ?",
                [$data['valor'], $existing[0]['id']]
            );
            return (int) $existing[0]['id'];
        }

        return $this->insert([
            'usuario_id' => $data['usuario_id'],
            'tipo' => $data['tipo'],
            'chave' => $data['chave'],
            'valor' => $data['valor'],
            'frequencia_uso' => 1
        ]);
    }

    /**
     * Retorna preferências do usuário
     */
    public function getUserPreferences(string $usuarioId): array
    {
        return $this->query(
            "SELECT * FROM ai_preferences 
             WHERE usuario_id = ? 
             ORDER BY frequencia_uso DESC",
            [$usuarioId]
        );
    }

    /**
     * Conta feedbacks por tipo
     */
    public function countFeedbacksByType(): array
    {
        return $this->query(
            "SELECT tipo, COUNT(*) as total 
             FROM ai_feedbacks 
             GROUP BY tipo"
        );
    }

    /**
     * Retorna estatísticas completas
     */
    public function getFullStats(): array
    {
        $totalPatterns = $this->countPatterns();
        $totalMetrics = $this->countMetrics();
        $totalExecutions = $this->countExecutions();
        $successRate = $this->getSuccessRate();
        $avgExecutionTime = $this->getAvgExecutionTime();
        $avgRating = $this->getAvgRating();

        $feedbacksByType = $this->countFeedbacksByType();
        $totalFeedbacks = array_sum(array_column($feedbacksByType, 'total'));

        // Top agentes
        $topAgents = $this->query(
            "SELECT agente, COUNT(*) as total 
             FROM ai_metrics 
             GROUP BY agente 
             ORDER BY total DESC 
             LIMIT 5"
        );

        // Velocidade de aprendizado (padrões por semana)
        $learningVelocity = $this->query(
            "SELECT COUNT(*) / GREATEST(DATEDIFF(NOW(), MIN(created_at)) / 7, 1) as velocity 
             FROM ai_learning"
        );

        return [
            'total_patterns' => $totalPatterns,
            'total_metrics' => $totalMetrics,
            'total_executions' => $totalExecutions,
            'total_feedbacks' => $totalFeedbacks,
            'success_rate' => $successRate,
            'avg_rating' => $avgRating,
            'avg_execution_time' => $avgExecutionTime,
            'top_agents' => $topAgents,
            'learning_velocity' => round((float) ($learningVelocity[0]['velocity'] ?? 0), 2),
            'feedbacks_by_type' => $feedbacksByType
        ];
    }

    /**
     * Sobrescreve insert para tabela dinâmica
     */
    public function insert(array $data): int
    {
        // Detectar tabela baseado nos campos
        $table = $this->detectTable($data);

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        \App\Database\Connection::exec(
            "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );

        return (int) \App\Database\Connection::lastInsertId();
    }

    /**
     * Detecta tabela baseado nos campos
     */
    private function detectTable(array $data): string
    {
        if (isset($data['padrao'])) {
            return 'ai_learning';
        }
        if (isset($data['tempo_execucao'])) {
            return 'ai_metrics';
        }
        if (isset($data['agent_id'])) {
            return 'ai_executions';
        }
        if (isset($data['rating'])) {
            return 'ai_feedbacks';
        }
        if (isset($data['requisicao'])) {
            return 'ai_interactions';
        }
        if (isset($data['chave']) && isset($data['usuario_id'])) {
            return 'ai_preferences';
        }
        return $this->table;
    }
}

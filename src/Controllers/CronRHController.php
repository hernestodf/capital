<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Database\Connection;
use App\Service\EmailService;

/**
 * Controller para tarefas agendadas (CRON) de Recursos Humanos
 */
class CronRHController extends Controller
{
    private EmailService $emailService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->emailService = new EmailService();
    }

    /**
     * Processar envio diário de links de presença
     * GET /cron/presencas-diarias
     */
    public function presencasDiarias(): Response
    {
        // Opcional: Proteger a rota via Token de segurança no .env ou Query String
        $hoje = date('Y-m-d');
        $db = Connection::get();

        // Buscar todas as alocações ativas e confirmadas para hoje, que ainda não receberam e-mail hoje
        $stmt = $db->prepare("
            SELECT ec.*, c.nome as colaborador_nome, c.telefone, c.email,
                   e.nome_evento, e.local_evento
            FROM evento_colaboradores ec
            INNER JOIN colaboradores c ON c.id = ec.id_colaborador
            INNER JOIN eventos e ON e.id = ec.id_evento
            WHERE ec.status = 'A'
              AND ec.confirmado = 'C'
              AND ? BETWEEN ec.data_inicio AND ec.data_fim
              AND (ec.ultima_presenca_enviada IS NULL OR ec.ultima_presenca_enviada < ?)
              AND c.email IS NOT NULL AND c.email != ''
        ");
        $stmt->execute([$hoje, $hoje]);
        $alocacoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sentCount = 0;
        $failedCount = 0;
        $report = [];

        foreach ($alocacoes as $alocacao) {
            $result = $this->emailService->enviarLinksPresencaDiaria($alocacao);
            if ($result['success']) {
                $sentCount++;
                // Atualizar ultima_presenca_enviada para hoje
                $updateStmt = $db->prepare("
                    UPDATE evento_colaboradores 
                    SET ultima_presenca_enviada = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$hoje, $alocacao['id']]);
                
                $report[] = [
                    'colaborador' => $alocacao['colaborador_nome'],
                    'evento' => $alocacao['nome_evento'],
                    'status' => 'sucesso',
                    'provider' => $result['provider'] ?? 'smtp'
                ];
            } else {
                $failedCount++;
                $report[] = [
                    'colaborador' => $alocacao['colaborador_nome'],
                    'evento' => $alocacao['nome_evento'],
                    'status' => 'falha',
                    'error' => $result['error'] ?? 'Erro desconhecido'
                ];
            }
        }

        return $this->json([
            'success' => true,
            'data' => [
                'hoje' => $hoje,
                'total_encontrado' => count($alocacoes),
                'enviados' => $sentCount,
                'falhas' => $failedCount,
                'detalhes' => $report
            ]
        ]);
    }
}

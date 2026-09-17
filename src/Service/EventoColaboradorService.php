<?php

namespace App\Service;

use App\Repository\EventoColaboradorRepository;
use App\Repository\EventoColaboradorPresencaRepository;

/**
 * Service para gestao de colaboradores em eventos
 */
class EventoColaboradorService
{
    private EventoColaboradorRepository $colaboradorRepo;
    private EventoColaboradorPresencaRepository $presencaRepo;

    public function __construct()
    {
        $this->colaboradorRepo = new EventoColaboradorRepository();
        $this->presencaRepo = new EventoColaboradorPresencaRepository();
    }

    /**
     * Listar colaboradores alocados a um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->colaboradorRepo->findByEvento($idEvento);
    }

    /**
     * Alocar colaborador em evento
     */
    public function alocar(array $data): array
    {
        $idEvento = (int)($data['id_evento'] ?? 0);
        $idColaborador = (int)($data['id_colaborador'] ?? 0);
        $funcao = trim($data['funcao'] ?? '');
        $dataInicio = trim($data['data_inicio'] ?? '');
        $dataFim = trim($data['data_fim'] ?? '');
        $horaInicio = trim($data['hora_inicio'] ?? '');
        $horaFim = trim($data['hora_fim'] ?? '');
        $valorDiaria = (float)($data['valor_diaria'] ?? 0);
        $dataVencimento = !empty($data['data_vencimento_pagamento']) ? trim($data['data_vencimento_pagamento']) : null;

        // Validacoes
        if ($idEvento <= 0) {
            return ['success' => false, 'error' => 'Evento invalido'];
        }
        if ($idColaborador <= 0) {
            return ['success' => false, 'error' => 'Colaborador invalido'];
        }
        if (empty($funcao)) {
            return ['success' => false, 'error' => 'Funcao e obrigatoria'];
        }
        if (empty($dataInicio) || empty($dataFim)) {
            return ['success' => false, 'error' => 'Periodo e obrigatorio'];
        }
        if (empty($horaInicio) || empty($horaFim)) {
            return ['success' => false, 'error' => 'Horarios sao obrigatorios'];
        }

        // Verificar se ja existe alocacao (ativa ou inativa)
        // Buscar qualquer alocacao (ignorar status na busca inicial)
        $existenteAtiva = $this->colaboradorRepo->findByColaboradorAndEvento($idColaborador, $idEvento);
        if ($existenteAtiva && $existenteAtiva['status'] === 'A') {
            return ['success' => false, 'error' => 'Colaborador ja esta alocado neste evento'];
        }

        // Buscar alocacao inativa para possivel reativacao (query direta)
        $existenteInativa = \App\Database\Connection::query(
            "SELECT ec.*, c.nome as colaborador_nome, c.telefone
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             WHERE ec.id_colaborador = ? AND ec.id_evento = ? AND ec.status = 'I'
             LIMIT 1",
            [$idColaborador, $idEvento]
        );

        // Se existe alocacao inativa, reativar
        if (!empty($existenteInativa[0])) {
            $existente = $existenteInativa[0];
            $updateData = [
                'status' => 'A',
                'confirmado' => 'P',
                'funcao' => $funcao,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'hora_inicio' => $horaInicio,
                'hora_fim' => $horaFim,
                'valor_diaria' => $valorDiaria,
            ];
            if ($dataVencimento) {
                $updateData['data_vencimento_pagamento'] = $dataVencimento;
            }
            $this->colaboradorRepo->update((int)$existente['id'], $updateData);
            
            $alocacao = $this->colaboradorRepo->findById((int)$existente['id']);
            if ($alocacao) {
                try {
                    $emailService = new \App\Service\EmailService();
                    $emailService->enviarEmailConfirmacaoAlocacao($alocacao);
                } catch (\Exception $e) {
                    \App\Core\Logger::warning('[EventoColaboradorService] Erro ao enviar e-mail (reativação): ' . $e->getMessage());
                }
            }
            
            return ['success' => true, 'data' => $alocacao, 'token' => $existente['token_presenca']];
        }

        // Gerar token unico para nova alocacao
        $token = bin2hex(random_bytes(32));

        $insertData = [
            'id_evento' => $idEvento,
            'id_colaborador' => $idColaborador,
            'funcao' => $funcao,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'valor_diaria' => $valorDiaria,
            'token_presenca' => $token,
            'confirmado' => 'P',
            'status' => 'A'
        ];

        if ($dataVencimento) {
            $insertData['data_vencimento_pagamento'] = $dataVencimento;
        }

        $id = $this->colaboradorRepo->create($insertData);

        // Buscar dados completos para response
        $alocacao = $this->colaboradorRepo->findById($id);

        if ($alocacao) {
            try {
                $emailService = new \App\Service\EmailService();
                $emailService->enviarEmailConfirmacaoAlocacao($alocacao);
            } catch (\Exception $e) {
                \App\Core\Logger::warning('[EventoColaboradorService] Erro ao enviar e-mail: ' . $e->getMessage());
            }
        }

        return ['success' => true, 'data' => $alocacao, 'token' => $token];
    }

    /**
     * Desalocar colaborador (soft delete)
     */
    public function desalocar(int $idAlocacao): array
    {
        $alocacao = $this->colaboradorRepo->findById($idAlocacao);
        if (!$alocacao) {
            return ['success' => false, 'error' => 'Alocacao nao encontrada'];
        }
        if ($alocacao['status'] === 'I') {
            return ['success' => false, 'error' => 'Alocacao ja esta desativada'];
        }

        $this->colaboradorRepo->update($idAlocacao, ['status' => 'I']);
        return ['success' => true];
    }

    /**
     * Buscar presencas de todos os colaboradores do evento para o dia (evitar N+1)
     */
    public function findPresencasHoje(int $idEvento, string $data): array
    {
        return $this->colaboradorRepo->findPresencasHojeByEvento($idEvento, $data);
    }

    /**
     * Ver todas presencas de uma alocacao com totais
     */
    public function verPresencas(int $idAlocacao): array
    {
        $alocacao = $this->colaboradorRepo->findById($idAlocacao);
        if (!$alocacao) {
            return ['success' => false, 'error' => 'Alocacao nao encontrada'];
        }

        $presencas = $this->presencaRepo->findByAlocacao($idAlocacao);
        $totais = $this->colaboradorRepo->calcularTotalDiarias($idAlocacao);

        return [
            'success' => true,
            'data' => [
                'alocacao' => $alocacao,
                'presencas' => $presencas,
                'totais' => $totais
            ]
        ];
    }

    /**
     * Registrar pagamento com comprovante
     */
    public function registrarPagamento(int $idAlocacao, string $dataPagamento, ?string $comprovantePath): array
    {
        $alocacao = $this->colaboradorRepo->findById($idAlocacao);
        if (!$alocacao) {
            return ['success' => false, 'error' => 'Alocacao nao encontrada'];
        }

        $updateData = [
            'data_pagamento' => $dataPagamento,
        ];

        if ($comprovantePath) {
            $updateData['comprovante_anexo'] = $comprovantePath;
        }

        $this->colaboradorRepo->update($idAlocacao, $updateData);
        return ['success' => true];
    }

    /**
     * Marcar para enviar pagamento (fechamento)
     */
    public function enviarPagamento(int $idAlocacao): array
    {
        $alocacao = $this->colaboradorRepo->findById($idAlocacao);
        if (!$alocacao) {
            return ['success' => false, 'error' => 'Alocacao nao encontrada'];
        }

        $this->colaboradorRepo->update($idAlocacao, ['enviar_pagamento' => 'S']);
        return ['success' => true];
    }

    /**
     * Registrar presenca (entrada ou saida)
     */
    public function registrarPresenca(string $token, string $data, string $tipo, string $foto, float $lat, float $lng): array
    {
        $alocacao = $this->colaboradorRepo->findByToken($token);
        if (!$alocacao) {
            return ['success' => false, 'error' => 'Link invalido'];
        }

        // Verificar se data esta dentro do periodo
        if ($data < $alocacao['data_inicio'] || $data > $alocacao['data_fim']) {
            return ['success' => false, 'error' => 'Data fora do periodo de alocacao'];
        }

        if ($tipo === 'entrada') {
            $this->presencaRepo->registrarEntrada((int)$alocacao['id'], $data, $foto, $lat, $lng);
        } elseif ($tipo === 'saida') {
            $this->presencaRepo->registrarSaida((int)$alocacao['id'], $data, $foto, $lat, $lng);
        } else {
            return ['success' => false, 'error' => 'Tipo de registro invalido'];
        }

        return ['success' => true, 'data' => ['nome' => $alocacao['colaborador_nome']]];
    }

    /**
     * Validar token de presenca e retornar dados do colaborador
     */
    public function validarTokenPresenca(string $token): ?array
    {
        return $this->colaboradorRepo->findByToken($token);
    }

    /**
     * Obter estatisticas de RH do evento
     */
    public function getStats(int $idEvento, string $dataHoje, array $alocacoes = []): array
    {
        $totalAlocados = $this->colaboradorRepo->countByEvento($idEvento);
        $presencasHoje = $this->presencaRepo->countPresencasHojeByEvento($idEvento, $dataHoje);
        $pagamentosPendentes = $this->colaboradorRepo->countPagamentosPendentes($idEvento);

        $totalDiarias = 0;
        foreach ($alocacoes as $a) {
            $dtInicio = new \DateTime($a['data_inicio']);
            $dtFim = new \DateTime($a['data_fim']);
            $dias = max(1, (int)$dtInicio->diff($dtFim)->days + 1);
            $totalDiarias += (float)$a['valor_diaria'] * $dias;
        }

        return [
            'total_alocados' => $totalAlocados,
            'presencas_hoje' => $presencasHoje,
            'pagamentos_pendentes' => $pagamentosPendentes,
            'total_diarias' => $totalDiarias
        ];
    }
}

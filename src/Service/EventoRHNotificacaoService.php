<?php

namespace App\Service;

use App\Repository\EventoColaboradorRepository;
use App\Core\Env;
use App\Core\Logger;

/**
 * Service CRON para envio de notificacoes WhatsApp de presenca
 * Roda a cada 5 minutos via agendador externo
 */
class EventoRHNotificacaoService
{
    private EventoColaboradorRepository $repo;

    public function __construct()
    {
        $this->repo = new EventoColaboradorRepository();
    }

    /**
     * Enviar notificacoes de entrada/saida
     * Protegido por SECRET_KEY
     */
    public function enviarNotificacoes(?string $secretKey = null): array
    {
        // Validar secret key
        $expectedKey = Env::get('CRON_SECRET_KEY', '');
        if (!empty($expectedKey) && $secretKey !== $expectedKey) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        $dataHoje = date('Y-m-d');
        $horaAtual = date('H:i');

        $enviados = 0;
        $erros = 0;

        // Notificacoes de ENTRADA (30 min antes do hora_inicio)
        $alocacoesEntrada = $this->repo->findAlocacoesDoDia($dataHoje, $horaAtual, 'entrada');
        foreach ($alocacoesEntrada as $alocacao) {
            $result = $this->enviarWhatsApp(
                $alocacao['telefone'],
                $alocacao['colaborador_nome'],
                $alocacao['token_presenca'],
                $dataHoje,
                'entrada',
                $alocacao['nome_evento']
            );

            if ($result['success']) {
                $enviados++;
            } else {
                $erros++;
                Logger::error('[RH-CRON] Erro envio entrada: ' . $result['error']);
            }
        }

        // Notificacoes de SAIDA (30 min antes do hora_fim)
        $alocacoesSaida = $this->repo->findAlocacoesDoDia($dataHoje, $horaAtual, 'saida');
        foreach ($alocacoesSaida as $alocacao) {
            $result = $this->enviarWhatsApp(
                $alocacao['telefone'],
                $alocacao['colaborador_nome'],
                $alocacao['token_presenca'],
                $dataHoje,
                'saida',
                $alocacao['nome_evento']
            );

            if ($result['success']) {
                $enviados++;
            } else {
                $erros++;
                Logger::error('[RH-CRON] Erro envio saida: ' . $result['error']);
            }
        }

        return [
            'success' => true,
            'data' => [
                'enviados' => $enviados,
                'erros' => $erros,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Enviar mensagem WhatsApp para colaborador
     */
    private function enviarWhatsApp(string $telefone, string $nome, string $token, string $data, string $tipo, string $nomeEvento): array
    {
        $botUrl = Env::get('WHATSAPP_BOT_URL');
        $apiKey = Env::get('WHATSAPP_API_KEY');
        $sessionId = Env::get('WHATSAPP_SESSION_ID', 'novoframework');

        if (empty($botUrl) || empty($apiKey)) {
            Logger::info('RH: Bot nao configurado - modo debug', ['channel' => 'whatsapp']);
            return ['success' => false, 'error' => 'Bot nao configurado'];
        }

        $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
        $linkPresenca = $baseUrl . '/presenca/' . $token . '?data=' . $data . '&tipo=' . $tipo;

        if ($tipo === 'entrada') {
            $mensagem = "Ola {$nome}! Seu turno no evento {$nomeEvento} comeca em 30 minutos.\n\nRegistre sua entrada com foto e localizacao:\n{$linkPresenca}";
        } else {
            $mensagem = "Ola {$nome}! Seu turno no evento {$nomeEvento} termina em 30 minutos.\n\nRegistre sua saida com foto e localizacao:\n{$linkPresenca}";
        }

        try {
            $endpoint = $botUrl . '/sessions/' . $sessionId . '/send-message';
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $apiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'number' => $telefone,
                    'message' => $mensagem,
                    'linkPreview' => true
                ])
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                Logger::info('[RH] Mensagem enviada para ' . $nome . ' (' . $tipo . ')', ['channel' => 'whatsapp', 'phone' => $telefone]);
                return ['success' => true];
            } else {
                Logger::error('[RH] Erro HTTP ' . $httpCode . ' para ' . $nome, ['channel' => 'whatsapp', 'phone' => $telefone, 'http_code' => $httpCode]);
                return ['success' => false, 'error' => 'HTTP ' . $httpCode];
            }
        } catch (\Exception $e) {
            Logger::error('[RH] Excecao: ' . $e->getMessage(), ['channel' => 'whatsapp', 'exception' => get_class($e)]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

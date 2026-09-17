<?php

namespace App\Service;

use App\Core\Env;

class WhatsAppService
{
    private string $baseUrl;
    private string $apiKey;
    private string $sessionId;

    public function __construct()
    {
        $this->baseUrl = rtrim(Env::get('WHATSAPP_BOT_URL', ''), '/');
        $this->apiKey = Env::get('WHATSAPP_API_KEY', '');
        $this->sessionId = Env::get('WHATSAPP_SESSION_ID', 'default');

        if (empty($this->baseUrl) || empty($this->apiKey)) {
            throw new \RuntimeException('WhatsApp Bot nao configurado. Defina WHATSAPP_BOT_URL e WHATSAPP_API_KEY no .env');
        }
    }

    public function sendMessage(string $number, string $message): array
    {
        $url = $this->baseUrl . '/send';

        $to = $this->formatWhatsAppId($number);
        $payload = json_encode([
            'sessionId' => $this->sessionId,
            'to' => $to,
            'message' => $message
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($response === false || $httpCode === 0) {
            return [
                'ok' => false,
                'error' => 'Não foi possível conectar ao bot WhatsApp. Verifique se o bot está rodando em ' . $this->baseUrl,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'http_code' => $httpCode
            ];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error'] ?? 'Falha ao enviar mensagem';

            if (strpos($errorMsg, 'Session not connected') !== false ||
                strpos($errorMsg, 'Sessão não conectada') !== false) {
                return [
                    'ok' => false,
                    'error' => 'Sessão do WhatsApp desconectada. Escaneie o QR Code na página de Configurações.',
                    'needs_restart' => true,
                    'http_code' => $httpCode
                ];
            }

            if (strpos($errorMsg, 'detached Frame') !== false ||
                strpos($errorMsg, 'Target closed') !== false) {
                return [
                    'ok' => false,
                    'error' => 'Sessão do WhatsApp desconectada. É necessário reiniciar o bot.',
                    'needs_restart' => true,
                    'http_code' => $httpCode
                ];
            }

            return [
                'ok' => false,
                'error' => $errorMsg,
                'http_code' => $httpCode,
                'response' => $response
            ];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return ['ok' => false, 'error' => 'Resposta inválida do servidor'];
        }
        if (isset($result['success']) && !isset($result['ok'])) {
            $result['ok'] = $result['success'];
        }
        return $result;
    }

    public function sendMedia(string $number, string $mediaUrl, string $caption = ''): array
    {
        $url = $this->baseUrl . '/send';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'sessionId' => $this->sessionId,
                'to' => $this->formatWhatsAppId($number),
                'mediaUrl' => $mediaUrl,
                'caption' => $caption,
                'filename' => basename($mediaUrl)
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            return ['ok' => false, 'error' => $errorData['error'] ?? 'Falha ao enviar mídia'];
        }

        $result = json_decode($response, true) ?? ['ok' => false, 'error' => 'Resposta inválida'];
        if (isset($result['success']) && !isset($result['ok'])) {
            $result['ok'] = $result['success'];
        }
        return $result;
    }

    public function getStatus(): array
    {
        $url = $this->baseUrl . '/session/' . $this->sessionId . '/status';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($response === false || $httpCode === 0) {
            return [
                'ok' => false,
                'error' => 'Bot WhatsApp offline',
                'detail' => 'Não foi possível conectar ao servidor do bot (' . $this->baseUrl . ').' .
                    ($curlError ? ' Erro: ' . $curlError : ' Servidor não respondeu.'),
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
            ];
        }

        if ($httpCode === 404) {
            return [
                'ok' => false,
                'error' => 'Sessão "' . $this->sessionId . '" não encontrada',
                'detail' => 'A sessão configurada no .env não existe no bot. Crie uma nova sessão em Configurações > WhatsApp.',
            ];
        }

        if ($httpCode !== 200) {
            return [
                'ok' => false,
                'error' => 'Falha ao verificar status (HTTP ' . $httpCode . ')',
                'detail' => $response,
            ];
        }

        $data = json_decode($response, true) ?? [];
        if (isset($data['success']) && !isset($data['ok'])) {
            $data['ok'] = $data['success'];
        }

        // Normalizar status do bot para maiúsculo
        if (isset($data['status'])) {
            $data['status'] = strtoupper($data['status']);
        }

        return $data;
    }

    public function restartSession(): array
    {
        $url = $this->baseUrl . '/session/' . $this->sessionId . '/restart';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => 'Falha ao reiniciar sessão'];
        }

        $data = json_decode($response, true) ?? ['ok' => false, 'error' => 'Resposta inválida'];
        if (isset($data['success']) && !isset($data['ok'])) {
            $data['ok'] = $data['success'];
        }
        return $data;
    }

    public function logout(): array
    {
        $url = $this->baseUrl . '/session/' . $this->sessionId . '/logout';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => 'Falha ao fazer logout'];
        }

        $data = json_decode($response, true) ?? ['ok' => false, 'error' => 'Resposta inválida'];
        if (isset($data['success']) && !isset($data['ok'])) {
            $data['ok'] = $data['success'];
        }
        return $data;
    }

    public function getQrImage(): ?string
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $url = $this->baseUrl . '/session/' . $this->sessionId . '/qr.png';

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode === 200 && strpos($contentType, 'image') !== false) {
                return 'data:image/png;base64,' . base64_encode($response);
            }

            if ($httpCode === 404 && $attempt === 0) {
                $this->createSession();
                sleep(2);
                continue;
            }

            return null;
        }

        return null;
    }

    private function createSession(): void
    {
        $url = $this->baseUrl . '/session/' . $this->sessionId;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    public function listSessions(): array
    {
        $url = $this->baseUrl . '/sessions';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => 'Falha ao listar sessões'];
        }

        $data = json_decode($response, true) ?? ['ok' => false, 'error' => 'Resposta inválida'];
        if (isset($data['success']) && !isset($data['ok'])) {
            $data['ok'] = $data['success'];
        }
        return $data;
    }

    public function generateComprovanteMessage(array $conta, string $baseUrl): string
    {
        $nome = $conta['fornecedor_nome'] ?? $conta['descricao'] ?? 'Cliente';
        $valor = $conta['valor'] ?? $conta['valor_total'] ?? '0';
        $valorPago = $conta['valor_pago'] ?? $valor;
        $dataVencimento = $conta['data_vencimento'] ? date('d/m/Y', strtotime($conta['data_vencimento'])) : 'N/A';
        $dataPagamento = $conta['data_pagamento'] ? date('d/m/Y', strtotime($conta['data_pagamento'])) : 'N/A';
        $tipoPagamento = $conta['tipo_pagamento'] ?? 'N/A';
        $descricao = $conta['descricao'] ?? 'Pagamento';
        $numeroNF = $conta['numero_nf'] ?? 'N/A';

        $message = "🧾 *COMPROVANTE DE PAGAMENTO*\n\n";
        $message .= "Olá! Segue o comprovante de pagamento referente a:\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "*Evento/Descrição:* {$descricao}\n";
        $message .= "*Número NF:* {$numeroNF}\n";
        $message .= "*Valor Original:* R$ " . number_format((float)$valor, 2, ',', '.') . "\n";
        $message .= "*Valor Pago:* R$ " . number_format((float)$valorPago, 2, ',', '.') . "\n";
        $message .= "*Data Vencimento:* {$dataVencimento}\n";
        $message .= "*Data Pagamento:* {$dataPagamento}\n";
        $message .= "*Tipo Pagamento:* {$tipoPagamento}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Agradecemos a preferência! 🙏\n\n";
        $message .= "_Este é um comprovante automático enviado pelo sistema SisLoc._";

        return $message;
    }

    private function formatWhatsAppId(string $number): string
    {
        $clean = preg_replace('/\D/', '', $number);
        if (strlen($clean) < 10) {
            return $number;
        }
        return $clean . '@s.whatsapp.net';
    }
}

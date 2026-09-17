<?php
namespace App\Service;

use App\Core\Env;

class MailjetService
{
    private string $apiKey;
    private string $secretKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey = Env::get('MAILJET_API_KEY', '');
        $this->secretKey = Env::get('MAILJET_SECRET_KEY', '');
        $this->fromEmail = Env::get('MAILJET_FROM_EMAIL', 'profox@sisloc.online');
        $this->fromName = Env::get('MAILJET_FROM_NAME', 'PROFOXNETWORKS');
    }

    /**
     * Enviar email via Mailjet API v3.1
     */
    public function send(array $params): array
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            return ['success' => false, 'error' => 'Mailjet nao configurado'];
        }

        $url = 'https://api.mailjet.com/v3.1/send';

        $payload = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->fromEmail,
                        'Name' => $this->fromName,
                    ],
                    'To' => [
                        [
                            'Email' => $params['to'],
                            'Name' => $params['to_name'] ?? '',
                        ],
                    ],
                    'Subject' => $params['subject'],
                    'TextPart' => $params['text_part'] ?? '',
                    'HTMLPart' => $params['html_part'] ?? '',
                ],
            ],
        ];

        // CCO
        if (!empty($params['bcc'])) {
            $payload['Messages'][0]['Bcc'] = [
                ['Email' => $params['bcc'], 'Name' => '']
            ];
        }

        // Headers dedicados do Mailjet (nao usar colecao Headers para estes)
        if (!empty($params['message_id'])) {
            $payload['Messages'][0]['MessageID'] = $params['message_id'];
        }
        if (!empty($params['in_reply_to'])) {
            $payload['Messages'][0]['InReplyTo'] = $params['in_reply_to'];
        }

        // Headers customizados (apenas outros headers nao-padrao)
        if (!empty($params['headers'])) {
            $payload['Messages'][0]['Headers'] = $params['headers'];
        }

        // Anexos
        if (!empty($params['attachments']) && is_array($params['attachments'])) {
            $attachments = [];
            foreach ($params['attachments'] as $att) {
                $attachments[] = [
                    'ContentType' => $att['content_type'] ?? 'application/octet-stream',
                    'Filename' => $att['filename'] ?? 'attachment',
                    'Base64Content' => $att['base64_content'],
                ];
            }
            $payload['Messages'][0]['Attachments'] = $attachments;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->secretKey,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log para debug
        \App\Core\Logger::info('Mailjet: envio realizado', [
            'to' => $params['to'],
            'subject' => $params['subject'],
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500),
        ]);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }

        $data = json_decode($response, true);

        if ($httpCode === 200) {
            return ['success' => true, 'data' => $data];
        }

        $errorMsg = $data['Messages'][0]['Errors'][0]['ErrorMessage'] ?? 'Erro desconhecido';
        return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
    }

    /**
     * Enviar múltiplos emails em lote via Mailjet API v3.1
     */
    public function sendBatch(array $messagesParams): array
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            return ['success' => false, 'error' => 'Mailjet nao configurado'];
        }

        $url = 'https://api.mailjet.com/v3.1/send';
        $messages = [];

        foreach ($messagesParams as $params) {
            $msg = [
                'From' => [
                    'Email' => $this->fromEmail,
                    'Name' => $this->fromName,
                ],
                'To' => [
                    [
                        'Email' => $params['to'],
                        'Name' => $params['to_name'] ?? '',
                    ],
                ],
                'Subject' => $params['subject'],
                'TextPart' => $params['text_part'] ?? '',
                'HTMLPart' => $params['html_part'] ?? '',
            ];

            if (!empty($params['bcc'])) {
                $msg['Bcc'] = [
                    ['Email' => $params['bcc'], 'Name' => '']
                ];
            }

            if (!empty($params['message_id'])) {
                $msg['MessageID'] = $params['message_id'];
            }

            if (!empty($params['in_reply_to'])) {
                $msg['InReplyTo'] = $params['in_reply_to'];
            }

            if (!empty($params['headers'])) {
                $msg['Headers'] = $params['headers'];
            }

            if (!empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = [];
                foreach ($params['attachments'] as $att) {
                    $attachments[] = [
                        'ContentType' => $att['content_type'] ?? 'application/octet-stream',
                        'Filename' => $att['filename'] ?? 'attachment',
                        'Base64Content' => $att['base64_content'],
                    ];
                }
                $msg['Attachments'] = $attachments;
            }

            $messages[] = $msg;
        }

        $payload = ['Messages' => $messages];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->secretKey,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        \App\Core\Logger::info('Mailjet: envio em lote realizado', [
            'count' => count($messagesParams),
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500),
        ]);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }

        $data = json_decode($response, true);

        if ($httpCode === 200) {
            return ['success' => true, 'data' => $data];
        }

        return ['success' => false, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode];
    }
}

<?php
namespace App\Service;

use Webklex\PHPIMAP\ClientManager;
use App\Core\Env;

class ImapService
{
    private ?ClientManager $cm = null;

    private function getClient(): ?\Webklex\PHPIMAP\Client
    {
        if ($this->cm === null) {
            $host = Env::get('IMAP_HOST', '');
            if (empty($host)) return null;

            try {
                $this->cm = new ClientManager([
                    'default' => 'default',
                    'accounts' => [
                        'default' => [
                            'host' => $host,
                            'port' => (int) Env::get('IMAP_PORT', 993),
                            'encryption' => 'ssl',
                            'username' => Env::get('IMAP_USER', ''),
                            'password' => Env::get('IMAP_PASS', ''),
                            'protocol' => 'imap',
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                \App\Core\Logger::error('[IMAP] Falha ao criar ClientManager: ' . $e->getMessage());
                return null;
            }
        }

        try {
            return $this->cm->account('default');
        } catch (\Exception $e) {
            \App\Core\Logger::error('[IMAP] Falha ao conectar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar emails nao lidos e processar cotacoes
     * Retorna array de emails processados
     */
    public function processIncomingEmails(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return ['error' => 'IMAP not configured or connection failed'];
        }

        try {
            $client->connect();
            $folder = $client->getFolder('INBOX');

            // Buscar emails nao lidos
            $messages = $folder->messages()->unseen()->get();

            $processed = [];

            foreach ($messages as $message) {
                $result = $this->processMessage($message);
                if ($result) {
                    $processed[] = $result;
                    // Marcar como lido
                    $message->setFlag('Seen');
                }
            }

            $client->disconnect();

            return ['processed' => $processed, 'count' => count($processed)];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Processar um email individual
     */
    private function processMessage($message): ?array
    {
        $subject = (string) $message->getSubject();
        $inReplyTo = (string) ($message->getInReplyTo() ?? '');
        $messageId = (string) ($message->getMessageId() ?? '');

        // Decodificar MIME encoded words (RFC 2047)
        // Ex: =?UTF-8?B?UmU6IFtDT1QtU09MSUMtRVZULTFd?= => Re: [COT-SOLIC-EVT-1]
        if (preg_match('/=\?[^?]+\?[BbQq]\?[^?]+\?=/i', $subject)) {
            $decoded = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if ($decoded !== false && $decoded !== $subject) {
                $subject = $decoded;
            }
        }

        // Capturar token de cotacao em qualquer variacao:
        // [COT-123-EVT-5], [COT-SOLIC-EVT-5], [COT-REPLY-EVT-5], [COT-RENOV-EVT-5], etc.
        // Aceita espacos opcionais: [COT-SOLIC-EVT- 1]
        $idCotacao = 0;
        $idEvento = 0;
        $hasToken = preg_match('/\[COT[^\]]*-EVT-\s*(\d+)\]/i', $subject, $matches);

        if ($hasToken) {
            $idEvento = (int) $matches[1];

            // Se o assunto tem formato [COT-123-EVT-5], o primeiro numero e o id_cotacao
            if (preg_match('/\[COT-(\d+)-EVT-(\d+)\]/i', $subject, $matches2)) {
                $idCotacao = (int) $matches2[1];
                $idEvento = (int) $matches2[2];
            }
        } else {
            // Sem token no assunto — tentar resolver via In-Reply-To ou References
            // Busca no banco a mensagem original pelo message_id ou pelo email do fornecedor
            $fromEmail = (string) (($message->getFrom()[0] ?? null)->mail ?? '');
            if (!empty($inReplyTo) || !empty($fromEmail)) {
                $resolved = $this->resolveByInReplyTo($inReplyTo, $subject, $fromEmail);
                if ($resolved) {
                    $idCotacao = $resolved['id_cotacao'];
                    $idEvento = $resolved['id_evento'];
                }
            }

            // Se ainda nao resolveu, nao e email de cotacao
            if ($idCotacao === 0 && $idEvento === 0) {
                return null;
            }
        }

        if ($idEvento === 0) return null;

        $from = $message->getFrom()[0] ?? null;
        if (!$from) return null;

        $body = '';
        // Tentar HTML, senao texto puro
        $htmlBody = $message->getHTMLBody();
        if (!empty($htmlBody)) {
            $body = is_array($htmlBody) ? implode("\n", $htmlBody) : $htmlBody;
            $body = strip_tags($body);
        } else {
            $textBody = $message->getTextBody();
            $body = is_array($textBody) ? implode("\n", $textBody) : $textBody;
        }

        // Extrair apenas o texto util (antes da citacao)
        $body = $this->extractUsefulBody($body);

        // Adicionar codigo da cotacao se disponivel
        if ($idCotacao > 0 && $idEvento > 0) {
            $body = sprintf("[COT-%d-EVT-%d] %s", $idCotacao, $idEvento, $body);
        }

        // Salvar anexos
        $attachments = [];
        $configuredPath = Env::get('UPLOAD_PATH');
        
        // Se o caminho configurado for absoluto e começar com /public_html mas não existir no disco,
        // tentamos resolver usando prefixos conhecidos do cPanel ou fallback para o caminho relativo
        if ($configuredPath && str_starts_with($configuredPath, '/')) {
            if (!is_dir($configuredPath)) {
                $resolved = false;
                $possiblePrefixes = ['/home/sisloc', '/backup/sisloc'];
                foreach ($possiblePrefixes as $prefix) {
                    if (is_dir($prefix . $configuredPath)) {
                        $configuredPath = $prefix . $configuredPath;
                        $resolved = true;
                        break;
                    }
                }
                if (!$resolved) {
                    $configuredPath = dirname(__DIR__, 2) . '/storage/uploads';
                }
            }
        }
        
        // Se o caminho for relativo (ex: 'storage/uploads' ou 'public/uploads'), resolvemos a partir do root
        if ($configuredPath && !str_starts_with($configuredPath, '/')) {
            $configuredPath = dirname(__DIR__, 2) . '/' . $configuredPath;
        }

        $uploadDir = rtrim($configuredPath ?: dirname(__DIR__, 2) . '/public/uploads', '/') . '/cotacoes/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $a = $message->getAttachments();
        foreach ($a as $attachment) {
            try {
                $filename = $attachment->name ?? 'attachment_' . uniqid();
                // Limpar nome de arquivo (remover chars especiais)
                $filename = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $filename);
                $safeName = time() . '_' . basename($filename);
                $fullPath = $uploadDir . $safeName;

                // Salvar anexo no disco
                $attachment->save($uploadDir, $safeName);

                $attachments[] = [
                    'nome_arquivo' => $filename,
                    'path' => 'uploads/cotacoes/' . $safeName,
                    'mime_type' => $attachment->content_type ?? '',
                    'tamanho' => $attachment->size ?? 0
                ];
            } catch (\Exception $e) {
                \App\Core\Logger::warning('IMAP: falha ao salvar anexo', [
                    'error' => $e->getMessage(),
                    'subject' => $subject
                ]);
            }
        }

        return [
            'cotacao_id' => $idCotacao,
            'evento_id' => $idEvento,
            'fornecedor_id' => null,
            'subject' => $subject,
            'from_email' => (string) ($from->mail ?? ''),
            'from_name' => (string) ($from->name ?? $from->mail ?? ''),
            'body' => trim($body),
            'message_id' => (string) ($message->getMessageId() ?? ''),
            'in_reply_to' => (string) ($message->getInReplyTo() ?? ''),
            'date' => (string) ($message->getDate() ?? date('Y-m-d H:i:s')),
            'anexos' => $attachments,
        ];
    }

    /**
     * Extrair apenas o texto util do corpo do email, removendo citacoes
     * e limpando quebras de linha excessivas
     */
    private function extractUsefulBody(string $body): string
    {
        // Cortar citacoes Gmail PT: "Em dom., 26 de abr. de 2026 às 22:59, ... escreveu:"
        $body = preg_replace('/\s*Em\s+.+?,\s+\d+.+escreveu:.*/is', '', $body);
        
        // Cortar citacoes Gmail EN: "On Mon, Jan 1, 2024 at 1:00 PM ... wrote:"
        $body = preg_replace('/\s*On\s+.+?at\s+\d+:\d+\s*(AM|PM)?.*wrote:.*/is', '', $body);

        // Cortar citacoes Gmail EN: "On Mon, Jan 1, 2024 at 1:00 PM ... wrote:"
        $body = preg_replace('/\s*On\s+\w+,\s+\w+\s+\d+,\s+\d+\s+at\s+\d+:\d+\s+(AM|PM),.+wrote:.*$/is', '', $body);

        // Cortar Outlook: "De: ..."
        $body = preg_replace('/\r?\nDe:\s.+/is', '', $body);

        // Separadores
        $body = preg_replace('/_{20,}.*$/is', '', $body);
        $body = preg_replace('/\s*-{20,}\s*$/is', '', $body);

        // Limpar: remover quebras de linha excessivas (max 2 consecutivas)
        $body = preg_replace('/[\r\n]{3,}/', "\n\n", $body);

        // Remover linhas em branco no inicio/fim
        $body = trim($body);

        // Remover caracteres invisiveis (zero-width space, em-space)
        $body = preg_replace('/[\x{200B}\x{2003}\x{2002}\x{FEFF}]/u', '', $body);

        // Normalizar espacos em linhas
        $body = preg_replace('/[ \t]+/', ' ', $body);
        $body = preg_replace('/\n[ \t]+\n/', "\n\n", $body);

        // Separar linhas coladas (palavraPalavra com letra maiuscula)
        $body = preg_replace('/([a-záàâãéêõíóôúüç])([A-ZÁÀÂÃÉÊÕÍÓÔÚÜÇ])/', "$1\n$2", $body);

        return trim($body);
    }

    /**
     * Resolver cotacao via In-Reply-To, References ou subject + from_email
     */
    private function resolveByInReplyTo(string $inReplyTo, string $subject, string $fromEmail): ?array
    {
        try {
            $db = \App\Database\Connection::get();

            // 1. Buscar por match parcial do message_id nas mensagens
            $clean = trim($inReplyTo, "<> \t\n\r\0\x0B");
            if (!empty($clean)) {
                $stmt = $db->prepare(
                    "SELECT id_cotacao FROM item_cotacoes_mensagens 
                     WHERE id_cotacao > 0 AND (
                         message_id_email LIKE ? OR in_reply_to LIKE ?
                     ) LIMIT 1"
                );
                $search = '%' . $clean . '%';
                $stmt->execute([$search, $search]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $stmt2 = $db->prepare("SELECT id_evento FROM item_cotacoes WHERE id = ?");
                    $stmt2->execute([$row['id_cotacao']]);
                    $evt = $stmt2->fetch(\PDO::FETCH_ASSOC);
                    if ($evt) {
                        return ['id_cotacao' => $row['id_cotacao'], 'id_evento' => $evt['id_evento']];
                    }
                }
            }

            // 2. Buscar pela ultima mensagem enviada para este fornecedor
            // O email de resposta vem de um fornecedor — buscar a ultima cotacao ativa com ele
            $stmt = $db->prepare(
                "SELECT ic.id as id_cotacao, ic.id_evento 
                 FROM item_cotacoes ic
                 INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
                 WHERE f.email = ? AND ic.status IN ('aguardando', 'com_proposta')
                 ORDER BY ic.id DESC LIMIT 1"
            );
            $stmt->execute([$fromEmail]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) return $row;

            // 3. Fallback: buscar qualquer cotacao deste fornecedor
            $stmt = $db->prepare(
                "SELECT ic.id as id_cotacao, ic.id_evento 
                 FROM item_cotacoes ic
                 INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
                 WHERE f.email = ?
                 ORDER BY ic.id DESC LIMIT 1"
            );
            $stmt->execute([$fromEmail]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;

        } catch (\Exception $e) {
            return null;
        }
    }
}

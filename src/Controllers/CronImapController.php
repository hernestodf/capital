<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Env;
use App\Core\Logger;
use App\Service\ImapService;
use App\Service\MailjetService;
use App\Repository\CotacaoMensagemRepository;
use App\Repository\FornecedorRepository;

class CronImapController extends Controller
{
    private ImapService $imapService;
    private CotacaoMensagemRepository $msgRepo;
    private FornecedorRepository $fornecedorRepo;
    private MailjetService $mailjet;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->imapService = new ImapService();
        $this->msgRepo = new CotacaoMensagemRepository();
        $this->fornecedorRepo = new FornecedorRepository();
        $this->mailjet = new MailjetService();
    }

    /**
     * Processar emails recebidos via IMAP
     * GET /cron/imap-cotacao?key=SECRET
     * ou header X-Cron-Secret: SECRET
     *
     * Este endpoint deve ser chamado por cron job a cada 5 minutos
     * Ex: cron job configurado no servidor para chamar a cada 5 min
     */
    public function processarEmails(): Response
    {
        // Support both GET param and header for flexibility
        $key = $this->get('key', '') ?: ($_SERVER['HTTP_X_CRON_SECRET'] ?? '');
        $expectedKey = Env::get('CRON_SECRET', 'CHANGE_ME_SECRET');

        if ($key !== $expectedKey) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $resultados = $this->imapService->processIncomingEmails();

            // Handle error response from ImapService
            if (isset($resultados['error'])) {
                \App\Core\Logger::error('[IMAP-CRON] ' . $resultados['error']);
                return $this->json([
                    'success' => false,
                    'error' => $resultados['error'],
                    'processados' => 0,
                    'erros' => 1,
                    'total' => 0
                ]);
            }

            $processados = $resultados['count'] ?? 0;
            $erros = 0;

            foreach ($resultados['processed'] ?? [] as $resultado) {
                try {
                    // Resolver ID da cotação PRIMEIRO
                    $idCotacao = $resultado['cotacao_id'] ?? 0;

                    // Se tem cotacao_id, buscar fornecedor + evento da cotacao
                    $idEventoCotacao = 0;
                    $nomeFornecedor = '';
                    if (!empty($idCotacao)) {
                        $stmt = \App\Database\Connection::get()->prepare(
                            "SELECT ic.id_fornecedor, ic.id_evento, f.nome_fantasia
                             FROM item_cotacoes ic
                             LEFT JOIN fornecedores f ON f.id = ic.id_fornecedor
                             WHERE ic.id = ?"
                        );
                        $stmt->execute([$idCotacao]);
                        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                        if ($row) {
                            $idFornecedor = (int) $row['id_fornecedor'];
                            $idEventoCotacao = (int) $row['id_evento'];
                            $nomeFornecedor = $row['nome_fantasia'] ?? '';
                        }
                    }

                    // Se nao tem cotacao_id ou nao conseguiu resolver fornecedor da cotacao,
                    // tentar resolver pelo email
                    if (empty($idFornecedor) && !empty($resultado['from_email'])) {
                        $fornecedor = $this->fornecedorRepo->findByEmail($resultado['from_email']);
                        if ($fornecedor) {
                            $idFornecedor = (int) $fornecedor['id'];
                        }
                    }

                    // Se nao conseguiu resolver fornecedor, registra erro
                    if (empty($idFornecedor)) {
                        \App\Core\Logger::warning('IMAP: fornecedor nao identificado', [
                            'email' => $resultado['from_email'] ?? 'unknown',
                            'subject' => $resultado['subject'] ?? 'unknown'
                        ]);
                        $erros++;
                        continue;
                    }

                    // Se nao tem cotacao_id (formato [COT-EVT-N]), buscar pelo evento + fornecedor + produto
                    if (empty($idCotacao) && !empty($resultado['evento_id'])) {
                        $idCotacao = $this->resolveCotacaoId(
                            $resultado['evento_id'],
                            $idFornecedor,
                            $resultado['subject'] ?? ''
                        );
                    }

                    // Se ainda nao tem cotacao_id, registra aviso
                    if (empty($idCotacao)) {
                        \App\Core\Logger::warning('IMAP: cotacao nao encontrada', [
                            'email' => $resultado['from_email'] ?? 'unknown',
                            'subject' => $resultado['subject'] ?? 'unknown',
                            'evento_id' => $resultado['evento_id'] ?? 0,
                            'fornecedor_id' => $idFornecedor
                        ]);
                        $erros++;
                        continue;
                    }

                    // Salvar mensagem no banco
                    $msgId = $this->msgRepo->create([
                        'id_cotacao' => $idCotacao,
                        'id_fornecedor' => $idFornecedor,
                        'tipo' => 'recebido',
                        'remetente' => $resultado['from_email'] ?? '',
                        'destinatario' => '',
                        'assunto' => $resultado['subject'] ?? '',
                        'corpo' => $resultado['body'] ?? '',
                        'message_id_email' => $resultado['message_id'] ?? '',
                        'in_reply_to' => $resultado['in_reply_to'] ?? '',
                    ]);

                    // Salvar anexos se existirem
                    if (!empty($resultado['anexos'])) {
                        foreach ($resultado['anexos'] as $anexo) {
                            $this->msgRepo->saveAnexo($msgId, $anexo);
                        }
                    }

                    // Reencaminhar copia completa para o produtor do evento
                    if (!empty($idEventoCotacao)) {
                        $this->encaminharParaProdutor(
                            $idEventoCotacao,
                            $idCotacao,
                            $nomeFornecedor,
                            $resultado['from_email'] ?? '',
                            $resultado['subject'] ?? '',
                            $resultado['body'] ?? '',
                            $resultado['anexos'] ?? []
                        );
                    }
                } catch (\Exception $e) {
                    $erros++;
                    \App\Core\Logger::error('IMAP: erro ao processar email', [
                        'subject' => $resultado['subject'] ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    error_log("IMAP ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                }
            }

            return $this->json([
                'success' => true,
                'processados' => $processados,
                'erros' => $erros,
                'total' => $processados
            ]);
        } catch (\Exception $e) {
            \App\Core\Logger::error('[IMAP-CRON] Excecao: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'processados' => 0,
                'erros' => 1,
                'total' => 0
            ]);
        }
    }

    /**
     * Reencaminhar copia completa da resposta do fornecedor para o produtor
     * Inclui corpo e anexos originais + link para conversa no sistema
     */
    private function encaminharParaProdutor(int $idEvento, int $idCotacao, string $nomeFornecedor, string $fromEmail, string $assunto, string $corpo, array $anexos): void
    {
        try {
            $stmt = \App\Database\Connection::get()->prepare(
                "SELECT e.nome_evento, p.nome as produtor_nome, p.email as produtor_email
                 FROM eventos e
                 LEFT JOIN produtores p ON p.id = e.id_produtor
                 WHERE e.id = ?"
            );
            $stmt->execute([$idEvento]);
            $evento = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$evento || empty($evento['produtor_email'])) {
                return;
            }

            $stmt2 = \App\Database\Connection::get()->prepare(
                "SELECT id_produto_evento FROM item_cotacoes WHERE id = ?"
            );
            $stmt2->execute([$idCotacao]);
            $cotRow = $stmt2->fetch(\PDO::FETCH_ASSOC);
            $idProdutoEvento = $cotRow ? (int) $cotRow['id_produto_evento'] : 0;

            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            $linkCotacao = $baseUrl . '/eventos/cotacao/' . $idEvento . '/' . $idProdutoEvento;

            // Montar HTML com o corpo original + anexos + link
            $htmlBody = sprintf(
                "<p><strong>%s</strong> (%s) respondeu a cotação do evento <strong>%s</strong>:</p>
                 <hr>
                 <p><strong>Assunto:</strong> %s</p>
                 <blockquote style=\"padding:12px 16px;margin:12px 0;border-left:4px solid #0E9AA7;background:#f5f5f5\">
                     %s
                 </blockquote>
                 <hr>
                 <p style=\"text-align:center;margin:20px 0\">
                     <a href=\"%s\" style=\"display:inline-block;padding:12px 24px;background:#0E9AA7;color:#fff;text-decoration:none;border-radius:6px\">
                         Ver Conversa Completa no Sistema
                     </a>
                 </p>
                 <p style=\"font-size:12px;color:#999\">Esta é uma cópia automática da resposta enviada pelo fornecedor.</p>",
                htmlspecialchars($nomeFornecedor ?: 'Fornecedor'),
                htmlspecialchars($fromEmail),
                htmlspecialchars($evento['nome_evento'] ?? 'Evento #' . $idEvento),
                htmlspecialchars($assunto),
                nl2br(htmlspecialchars($corpo)),
                $linkCotacao
            );

            $textPart = strip_tags($corpo) . "\n\n---\nPara responder, acesse: " . $linkCotacao;

            // Montar payload do Mailjet com anexos originais
            $mailjetParams = [
                'to' => $evento['produtor_email'],
                'to_name' => $evento['produtor_nome'] ?? 'Produtor',
                'subject' => 'Re: ' . $assunto,
                'html_part' => $htmlBody,
                'text_part' => $textPart,
            ];

            // Reencaminhar anexos originais
            $attachments = [];
            foreach ($anexos as $anexo) {
                $path = $anexo['path'] ?? '';
                if (empty($path)) continue;

                $fullPath = $path;
                if (!str_starts_with($path, '/') && !str_starts_with($path, 'public/')) {
                    $fullPath = __DIR__ . '/../../public/' . $path;
                }

                if (file_exists($fullPath)) {
                    $attachments[] = [
                        'content_type' => mime_content_type($fullPath) ?: 'application/octet-stream',
                        'filename' => basename($path),
                        'base64_content' => base64_encode(file_get_contents($fullPath)),
                    ];
                }
            }
            if (!empty($attachments)) {
                $mailjetParams['attachments'] = $attachments;
            }

            $result = $this->mailjet->send($mailjetParams);

            if ($result['success']) {
                Logger::info('[IMAP] Email reencaminhado para produtor', [
                    'email' => $evento['produtor_email'],
                    'evento' => $idEvento,
                    'cotacao' => $idCotacao,
                    'anexos' => count($attachments),
                ]);
            } else {
                Logger::warning('[IMAP] Falha ao reencaminhar para produtor', [
                    'email' => $evento['produtor_email'],
                    'error' => $result['error'] ?? 'unknown',
                ]);
            }
        } catch (\Exception $e) {
            Logger::error('[IMAP] Erro ao reencaminhar para produtor', [
                'error' => $e->getMessage(),
                'evento' => $idEvento,
            ]);
        }
    }

    /**
     * Resolver ID da cotação extraindo nome do produto do assunto do email
     * 
     * @param int $idEvento ID do evento
     * @param int $idFornecedor ID do fornecedor
     * @param string $assunto Assunto do email (ex: "Re: [COT-EVT-1] SONORIZACAO — Fornecedor: AURATEC")
     * @return int ID da cotação ou 0 se não encontrar
     */
    private function resolveCotacaoId(int $idEvento, int $idFornecedor, string $assunto): int
    {
        // Extrair nome do produto do assunto
        // Formato novo: "Re: [COT-EVT-1] SONORIZACAO PARA 100 PESSOAS — Fornecedor: AURATEC INDUSTRIAL LTDA"
        // Formato antigo: "Re: [COT-SOLIC-EVT-1] Solicitação de Proposta - SONORIZACAO PARA 100 PESSOAS"
        
        // Tentar novo formato primeiro: [COT-EVT-N] PRODUTO — Fornecedor:
        if (preg_match('/\[COT-EVT-\d+\]\s+(.+?)\s*[—–-]\s*Fornecedor:/iu', $assunto, $matches)) {
            $nomeProduto = trim($matches[1]);
        }
        // Tentar formato antigo: Solicitação de Proposta - PRODUTO
        elseif (preg_match('/Solicitação de Proposta\s*[-–—]\s*(.+)/iu', $assunto, $matches)) {
            $nomeProduto = trim($matches[1]);
        }
        // Se nao conseguiu extrair produto, fallback para primeira cotação
        else {
            $stmt = \App\Database\Connection::get()->prepare(
                "SELECT id FROM item_cotacoes WHERE id_evento = ? AND id_fornecedor = ? LIMIT 1"
            );
            $stmt->execute([$idEvento, $idFornecedor]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int) $row['id'] : 0;
        }

        $nomeProduto = trim($nomeProduto);

        // Buscar cotação específica pelo evento, fornecedor e nome do produto
        $stmt = \App\Database\Connection::get()->prepare(
            "SELECT ic.id 
             FROM item_cotacoes ic
             INNER JOIN produtos_evento pe ON pe.id = ic.id_produto_evento
             WHERE ic.id_evento = ? 
               AND ic.id_fornecedor = ?
               AND pe.produto = ?
             LIMIT 1"
        );
        $stmt->execute([$idEvento, $idFornecedor, $nomeProduto]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? (int) $row['id'] : 0;
    }
}

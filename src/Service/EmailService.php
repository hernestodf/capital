<?php
namespace App\Service;

use App\Core\Env;

class EmailService
{
    private EmailTemplateService $templateService;
    private ?array $empresa = null;

    public function __construct()
    {
        $this->templateService = new EmailTemplateService();
        try {
            $empresaService = new EmpresaService();
            $this->empresa = $empresaService->getConfig();
        } catch (\Exception $e) {
            $this->empresa = null;
        }
    }

    public function enviarMensagem(
        string $toEmail,
        string $toName,
        string $ccEmail,
        string $assunto,
        string $corpo,
        ?string $anexoPath = null,
        ?string $messageId = null,
        ?string $inReplyTo = null
    ): array {
        $htmlBody = $this->templateService->buildMensagemTemplate($corpo, $toName, $assunto);
        $textBody = strip_tags(str_replace('<br>', "\n", $corpo));

        return $this->sendViaPhpMailer($toEmail, $toName, $ccEmail, $assunto, $htmlBody, $textBody, $anexoPath);
    }

    private function sendViaPhpMailer(
        string $toEmail,
        string $toName,
        string $ccEmail,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $anexoPath = null
    ): array {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = Env::get('SMTP_HOST', 'mail.sisloc.online');
            $mail->SMTPAuth = true;
            $mail->Username = Env::get('SMTP_USER', 'capital@sisloc.online');
            $mail->Password = Env::get('SMTP_PASS', '');
            $mail->SMTPSecure = Env::get('SMTP_SECURE', 'ssl');
            $mail->Port = (int) Env::get('SMTP_PORT', 465);
            $mail->CharSet = 'UTF-8';

            $empresaNome = $this->empresa['nome'] ?? 'SisLoc';
            $fromEmail = Env::get('SMTP_USER', 'capital@sisloc.online');

            $mail->DKIM_domain = 'sisloc.online';
            $mail->DKIM_selector = 'default';
            $mail->DKIM_identity = $fromEmail;
            $dkimPrivateKey = Env::get('DKIM_PRIVATE_KEY', '');
            if (!empty($dkimPrivateKey)) {
                $mail->DKIM_private = $dkimPrivateKey;
                $mail->DKIM_passphrase = '';
            }

            $mail->setFrom($fromEmail, $empresaNome);
            $mail->addReplyTo($fromEmail, $empresaNome);
            $mail->addAddress($toEmail, $toName);
            if (!empty($ccEmail)) {
                $mail->addBCC($ccEmail);
            }

            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;

            if (!empty($anexoPath)) {
                $fullPath = $anexoPath;
                if (!str_starts_with($anexoPath, '/') && !str_starts_with($anexoPath, 'public/')) {
                    $fullPath = __DIR__ . '/../../public/' . $anexoPath;
                } elseif (str_starts_with($anexoPath, 'public/')) {
                    $fullPath = __DIR__ . '/../../' . $anexoPath;
                }
                if (file_exists($fullPath)) {
                    $mail->addAttachment($fullPath);
                }
            }

            $mail->send();
            return ['success' => true, 'error' => null, 'provider' => 'smtp'];
        } catch (\Exception $e) {
            \App\Core\Logger::error('PHPMailer: erro ao enviar', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function enviarEmailConfirmacaoAlocacao(array $alocacao): array
    {
        $toEmail = $alocacao['email'] ?? '';
        $toName = $alocacao['colaborador_nome'] ?? 'Colaborador';

        if (empty($toEmail)) {
            return ['success' => false, 'error' => 'Email do colaborador nao cadastrado'];
        }

        $template = $this->templateService->buildConfirmacaoAlocacaoTemplate($alocacao);

        return $this->sendViaPhpMailer($template['toEmail'], $template['toName'], '', $template['subject'], $template['htmlBody'], $template['textBody']);
    }

    public function enviarLinksPresencaDiaria(array $alocacao): array
    {
        $toEmail = $alocacao['email'] ?? '';
        $toName = $alocacao['colaborador_nome'] ?? 'Colaborador';

        if (empty($toEmail)) {
            return ['success' => false, 'error' => 'Email do colaborador nao cadastrado'];
        }

        $template = $this->templateService->buildLinksPresencaDiariaTemplate($alocacao);

        return $this->sendViaPhpMailer($template['toEmail'], $template['toName'], '', $template['subject'], $template['htmlBody'], $template['textBody']);
    }
}

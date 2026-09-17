<?php
namespace App\Service;

use App\Core\Env;
use App\Service\EmpresaService;

class EmailTemplateService
{
    private ?array $empresa = null;

    public function __construct()
    {
        try {
            $empresaService = new EmpresaService();
            $this->empresa = $empresaService->getConfig();
        } catch (\Exception $e) {
            $this->empresa = null;
        }
    }

    public function buildMensagemTemplate(string $corpo, string $toName, string $assunto): string
    {
        $empresaNome = $this->empresa['nome'] ?? 'SisLoc';

        $toNameHtml = htmlspecialchars($toName);
        $corpoHtml = nl2br(htmlspecialchars($corpo));
        $assuntoHtml = htmlspecialchars($assunto);

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6f8">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:20px">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);padding:20px;text-align:center">
                            <h2 style="margin:0;color:#ffffff;font-size:20px">{$empresaNome}</h2>
                            <p style="margin:4px 0 0;color:rgba(255,255,255,0.8);font-size:13px">{$assuntoHtml}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px">
                            <p style="margin:0 0 16px;font-size:15px;color:#334155">Ola, <strong>{$toNameHtml}</strong>!</p>
                            <div style="font-size:14px;color:#475569;line-height:1.8">{$corpoHtml}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px;border-top:1px solid #e2e8f0">
                            <p style="margin:16px 0 0;font-size:13px;color:#475569">Atenciosamente,<br><strong style="color:#0891b2">Equipe {$empresaNome}</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:12px 24px;text-align:center;border-top:1px solid #e2e8f0">
                            <p style="margin:0;font-size:10px;color:#94a3b8">© {$empresaNome}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    public function buildConfirmacaoAlocacaoTemplate(array $alocacao): array
    {
        $toEmail = $alocacao['email'] ?? '';
        $toName = $alocacao['colaborador_nome'] ?? 'Colaborador';

        $nomeEvento = $alocacao['nome_evento'] ?? 'Evento';
        $subject = sprintf('[SisLoc] Confirme sua participação no evento: %s', $nomeEvento);

        $baseUrl = Env::get('BASE_URL', 'https://profoxba.sisloc.online/public');
        $linkConfirmacao = rtrim($baseUrl, '/') . '/presenca/confirmar-alocacao/' . $alocacao['token_presenca'];

        $empresaNome = $this->empresa['nome'] ?? 'SisLoc';

        $dataInicio = date('d/m/Y', strtotime($alocacao['data_inicio']));
        $dataFim = date('d/m/Y', strtotime($alocacao['data_fim']));
        $horaInicio = date('H:i', strtotime($alocacao['hora_inicio']));
        $horaFim = date('H:i', strtotime($alocacao['hora_fim']));
        $local = $alocacao['local_evento'] ?? 'A definir';
        $funcao = $alocacao['funcao'] ?? 'A definir';
        $valorDiaria = number_format((float)($alocacao['valor_diaria'] ?? 0), 2, ',', '.');

        $cpf = !empty($alocacao['cpf']) ? htmlspecialchars($alocacao['cpf']) : 'Não informado';
        $telefone = !empty($alocacao['telefone']) ? htmlspecialchars($alocacao['telefone']) : 'Não informado';
        $cidade = !empty($alocacao['cidade']) ? htmlspecialchars($alocacao['cidade']) : '';
        $estado = !empty($alocacao['estado']) ? htmlspecialchars($alocacao['estado']) : '';
        $cidadeEstado = trim($cidade . '/' . $estado, '/ ');
        if (empty($cidadeEstado)) {
            $cidadeEstado = 'Não informado';
        }
        $atuaComo = !empty($alocacao['atua_como']) ? htmlspecialchars($alocacao['atua_como']) : 'Geral';

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Alocação - {$empresaNome}</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6f8">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:20px">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);padding:24px;text-align:center">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700">{$empresaNome}</h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.9);font-size:14px">Convite de Alocação de Trabalho</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 24px">
                            <p style="margin:0 0 16px;font-size:16px;color:#334155">Olá, <strong>{$toName}</strong>!</p>
                            <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6">
                                Você foi escalado para trabalhar no seguinte evento. Por favor, confira os detalhes abaixo, leia o **Termo de Disponibilidade** e informe sua decisão:
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;margin-bottom:24px">
                                <tr style="background-color:#f8fafc">
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600;width:30%">Evento</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;font-weight:500">{$nomeEvento}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600">Local</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;font-weight:500">{$local}</td>
                                </tr>
                                <tr style="background-color:#f8fafc">
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600">Período</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;font-weight:500">{$dataInicio} a {$dataFim}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600">Horário</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;font-weight:500">{$horaInicio} às {$horaFim}</td>
                                </tr>
                                <tr style="background-color:#f8fafc">
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600">Função</td>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;font-weight:500">{$funcao}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;font-size:13px;color:#64748b;font-weight:600">Valor Diária</td>
                                    <td style="padding:12px 16px;font-size:14px;color:#1e293b;font-weight:700">R$ {$valorDiaria}</td>
                                </tr>
                            </table>

                            <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:20px;margin-bottom:24px;font-size:13px;color:#475569;line-height:1.6">
                                <h3 style="margin-top:0;color:#0e7490;font-size:14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;text-align:center">
                                    TERMO DE DISPONIBILIDADE E MANIFESTAÇÃO DE INTERESSE PARA PRESTAÇÃO DE SERVIÇOS FREELANCER
                                </h3>
                                <p style="text-align:center;font-weight:bold;margin:8px 0;color:#1e293b">PROFOX NETWORKS</p>
                                <p>O presente documento tem por finalidade registrar a manifestação de interesse do profissional abaixo identificado em integrar o banco de talentos e profissionais freelancers da PROFOX NETWORKS para futuras oportunidades de prestação de serviços em eventos, feiras, congressos, montagens, desmontagens e demais atividades operacionais.</p>

                                <strong style="color:#0e7490;display:block;margin-top:14px">1. IDENTIFICAÇÃO DO PROFISSIONAL</strong>
                                <ul style="margin:6px 0;padding-left:20px">
                                    <li><strong>Nome:</strong> {$toName}</li>
                                    <li><strong>CPF:</strong> {$cpf}</li>
                                    <li><strong>Telefone:</strong> {$telefone}</li>
                                    <li><strong>E-mail:</strong> {$toEmail}</li>
                                    <li><strong>Cidade/Estado:</strong> {$cidadeEstado}</li>
                                </ul>

                                <strong style="color:#0e7490;display:block;margin-top:14px">2. MANIFESTAÇÃO DE INTERESSE</strong>
                                <p style="margin:6px 0">O profissional declara possuir interesse em prestar serviços como freelancer para a PROFOX NETWORKS, podendo ser consultado para participação em projetos, eventos e atividades operacionais de acordo com sua experiência, disponibilidade e perfil profissional.</p>
                                <p style="margin:6px 0">O presente documento não caracteriza contratação, vínculo empregatício, exclusividade ou garantia de convocação para serviços futuros.</p>

                                <strong style="color:#0e7490;display:block;margin-top:14px">3. DISPONIBILIDADE</strong>
                                <p style="margin:6px 0">O profissional compromete-se a informar sua disponibilidade sempre que consultado pela PROFOX NETWORKS para participação em determinado evento ou projeto.</p>
                                <p style="margin:6px 0">A confirmação de disponibilidade não implica contratação automática, ficando a efetiva convocação sujeita às necessidades operacionais, critérios técnicos e aprovação da empresa.</p>

                                <strong style="color:#0e7490;display:block;margin-top:14px">4. DADOS PROFISSIONAIS</strong>
                                <p style="margin:6px 0">O profissional declara possuir experiência ou interesse nas seguintes áreas de atuação: <strong>{$atuaComo}</strong></p>

                                <strong style="color:#0e7490;display:block;margin-top:14px">5. CONDUTA PROFISSIONAL</strong>
                                <p style="margin:4px 0">Quando convocado e confirmado para prestação de serviços, o profissional compromete-se a:</p>
                                <ul style="margin:6px 0;padding-left:20px">
                                    <li>cumprir os horários informados pela coordenação;</li>
                                    <li>comparecer devidamente uniformizado ou conforme orientação recebida;</li>
                                    <li>manter postura profissional perante clientes e parceiros;</li>
                                    <li>respeitar normas de segurança e operação;</li>
                                    <li>zelar pelos equipamentos e materiais disponibilizados;</li>
                                    <li>comunicar previamente qualquer impedimento que possa comprometer sua participação.</li>
                                </ul>

                                <strong style="color:#0e7490;display:block;margin-top:14px">6. PROTEÇÃO DE DADOS E CADASTRO</strong>
                                <p style="margin:6px 0">O profissional autoriza a PROFOX NETWORKS a manter seus dados cadastrais em banco de dados interno para fins de recrutamento, seleção, convocação e gestão operacional de eventos.</p>

                                <strong style="color:#0e7490;display:block;margin-top:14px">7. DECLARAÇÃO</strong>
                                <p style="margin:6px 0">Declaro que as informações prestadas são verdadeiras e que possuo interesse em integrar o cadastro de profissionais freelancers da PROFOX NETWORKS, podendo ser consultado para futuras oportunidades de prestação de serviços.</p>
                                <p style="margin:6px 0;margin-bottom:0">Estou ciente de que este documento não gera vínculo empregatício, promessa de contratação ou obrigação de convocação por parte da empresa.</p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
                                <tr>
                                    <td align="center" width="50%" style="padding:10px">
                                        <a href="{$linkConfirmacao}" style="display:block;padding:14px 20px;background-color:#10b981;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:14px;text-align:center;box-shadow:0 2px 4px rgba(16,185,129,0.1)">CONFIRMAR ESCALA</a>
                                    </td>
                                    <td align="center" width="50%" style="padding:10px">
                                        <a href="{$linkConfirmacao}" style="display:block;padding:14px 20px;background-color:#ef4444;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:14px;text-align:center;box-shadow:0 2px 4px rgba(239,68,68,0.1)">NÃO CONFIRMAR</a>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:16px;background-color:#fffbeb;border-left:4px solid #f59e0b;border-radius:4px">
                                        <p style="margin:0;font-size:13px;color:#78350f;line-height:1.6">
                                            <strong>Aviso importante:</strong><br>
                                            Ao clicar em um dos botões, você será direcionado à página de manifestação de interesse. Nos dias do evento, o registro de presença (entrada/saída) é obrigatório e exige **foto e geolocalização GPS** pelo celular.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:16px 24px;text-align:center;border-top:1px solid #e2e8f0">
                            <p style="margin:0;font-size:10px;color:#94a3b8">© {$empresaNome} - Sistema de Locacao</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $textBody = <<<TEXT
Olá, {$toName}!

Você foi escalado para trabalhar no evento: {$nomeEvento}.
Local: {$local}
Período: {$dataInicio} a {$dataFim}
Horário: {$horaInicio} às {$horaFim}
Função: {$funcao}
Diária: R$ {$valorDiaria}

Acesse o link abaixo para confirmar ou recusar sua escala:
{$linkConfirmacao}

Importante: Nos dias do evento, você receberá links para registrar sua Entrada e Saída (obrigatório foto e GPS).

Atenciosamente,
Equipe {$empresaNome}
TEXT;

        return compact('toEmail', 'toName', 'subject', 'htmlBody', 'textBody');
    }

    public function buildLinksPresencaDiariaTemplate(array $alocacao): array
    {
        $toEmail = $alocacao['email'] ?? '';
        $toName = $alocacao['colaborador_nome'] ?? 'Colaborador';

        $nomeEvento = $alocacao['nome_evento'] ?? 'Evento';
        $subject = sprintf('[Presença] Links para Registro de Ponto - Evento: %s', $nomeEvento);

        $baseUrl = Env::get('BASE_URL', 'https://profoxba.sisloc.online/public');
        $linkEntrada = rtrim($baseUrl, '/') . '/presenca/' . $alocacao['token_presenca'] . '?tipo=entrada';
        $linkSaida = rtrim($baseUrl, '/') . '/presenca/' . $alocacao['token_presenca'] . '?tipo=saida';

        $empresaNome = $this->empresa['nome'] ?? 'SisLoc';

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Presença - {$empresaNome}</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6f8">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:20px">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);padding:24px;text-align:center">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700">{$empresaNome}</h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.9);font-size:14px">Registro de Ponto Diário</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 24px">
                            <p style="margin:0 0 16px;font-size:16px;color:#334155">Olá, <strong>{$toName}</strong>!</p>
                            <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6">
                                Hoje é dia de trabalho no evento <strong>{$nomeEvento}</strong>. Utilize os botões abaixo diretamente no seu celular para registrar seus horários de entrada e saída.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
                                <tr>
                                    <td align="center" width="50%" style="padding:10px">
                                        <a href="{$linkEntrada}" style="display:block;padding:16px;background-color:#10b981;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:15px;box-shadow:0 2px 4px rgba(16,185,129,0.2)">REGISTRAR ENTRADA</a>
                                    </td>
                                    <td align="center" width="50%" style="padding:10px">
                                        <a href="{$linkSaida}" style="display:block;padding:16px;background-color:#f59e0b;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:15px;box-shadow:0 2px 4px rgba(245,158,11,0.2)">REGISTRAR SAÍDA</a>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:16px;background-color:#f0fdfa;border-left:4px solid #14b8a6;border-radius:4px">
                                        <p style="margin:0;font-size:13px;color:#115e59;line-height:1.6">
                                            <strong>Lembrete:</strong> Ao acessar o link, você deverá autorizar o acesso à sua <strong>Câmera</strong> para tirar uma foto em tempo real e ao <strong>GPS (Localização)</strong> para registrar a coordenada física. Ambos são obrigatórios para validar seu dia de trabalho.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:16px 24px;text-align:center;border-top:1px solid #e2e8f0">
                            <p style="margin:0;font-size:10px;color:#94a3b8">© {$empresaNome} - Sistema de Locacao</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $textBody = <<<TEXT
Olá, {$toName}!

Hoje você trabalha no evento: {$nomeEvento}.
Utilize os links abaixo no seu celular para registrar sua presença:

Registrar ENTRADA:
{$linkEntrada}

Registrar SAÍDA:
{$linkSaida}

Atenção: É necessário permitir acesso à Câmera e Localização GPS para validar o registro.

Atenciosamente,
Equipe {$empresaNome}
TEXT;

        return compact('toEmail', 'toName', 'subject', 'htmlBody', 'textBody');
    }
}

<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Auth\Rbac;
use App\Service\MontagemService;
use App\Service\PdfGeneratorService;
use App\Database\Connection;

/**
 * API Controller para sessao e montagem (appmontagem)
 * Sessao baseada em cookie PHP (mesmo dominio)
 * Sem CSRF — endpoints para appmobile / PWA externa
 */
class SessaoApiController extends Controller
{
    /**
     * POST /api/v1/sessao/login
     * Login de sessao — SEM CSRF (uso externo)
     * Body: { email, senha }
     */
    public function login(): Response
    {
        $email    = $this->input('email')    ?? $this->post('email', '');
        $password = $this->input('senha')    ?? $this->post('senha', '')
                   ?? $this->input('password') ?? $this->post('password', '');

        if (empty($email) || empty($password)) {
            return $this->json([
                'ok'   => false,
                'error' => 'E-mail e senha são obrigatórios'
            ], 400);
        }

        $result = Rbac::login($email, $password);

        if (!$result['success']) {
            return $this->json([
                'ok'   => false,
                'error' => $result['message'] ?? 'Falha na autenticação'
            ], 401);
        }

        $user = Rbac::getUser();

        return $this->json([
            'ok'   => true,
            'user' => [
                'id'     => $user['id']     ?? null,
                'nome'   => $user['nome']
                        ?? $user['name']
                        ?? 'Desconhecido',
                'email'  => $user['email']  ?? '',
                'role'   => $user['role']   ?? 'guest',
            ],
        ]);
    }

    /**
     * GET /api/v1/eventos/ativos
     * Lista eventos ativos no formato appmontagem
     * Query: estado(O|L), status_locacao(A|F), page, perPage
     */
    public function ativos(): Response
    {
        $estado       = $this->get('estado', '');
        $statusLocacao = $this->get('status_locacao', '');
        $page         = (int) $this->get('page', 1);
        $perPage      = (int) $this->get('perPage', 50);

        $page  = max(1, $page);
        $perPage = max(1, $perPage);

        $pdo   = Connection::get();
        $where = ['status = 1'];
        $params = [];

        if (!empty($estado)) {
            $where[]     = 'estado = ?';
            $params[]    = strtoupper($estado);
        }
        if (!empty($statusLocacao)) {
            $where[]     = 'status_locacao = ?';
            $params[]    = strtoupper($statusLocacao);
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $stmt       = $pdo->prepare("SELECT COUNT(*) FROM eventos{$whereSql}");
        $stmt->execute($params);
        $total      = (int) $stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt   = $pdo->prepare(
            "SELECT id, nome_evento, id_cliente, estado, status_locacao,
                    data_inicio, data_fim, local_evento, observacao
             FROM eventos{$whereSql}
             ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt->execute($allParams);

        $eventos = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Buscar nome do cliente
            $clienteNome = '';
            if (!empty($row['id_cliente'])) {
                $cStmt = $pdo->prepare('SELECT nome_fantasia FROM clientes WHERE id = ? LIMIT 1');
                $cStmt->execute([$row['id_cliente']]);
                $clienteNome = $cStmt->fetchColumn() ?: '';
            }

            $eventos[] = [
                'id'              => (int) $row['id'],
                'nome_evento'     => $row['nome_evento'] ?? '',
                'nome'            => $row['nome_evento'] ?? '',
                'cliente_nome'    => $clienteNome,
                'estado'          => $row['estado']          ?? '',
                'status_locacao'  => $row['status_locacao']  ?? '',
                'data_inicio'     => $row['data_inicio']     ?? '',
                'data_fim'        => $row['data_fim']        ?? '',
                'local_evento'    => $row['local_evento']    ?? '',
                'observacao'      => $row['observacao']      ?? '',
            ];
        }

        return $this->json([
            'ok'      => true,
            'eventos' => $eventos,
        ]);
    }

    /**
     * POST /api/v1/montagem/inserir-lote
     * Inserir seriais em lote (appmontagem array format) + gerar PDF
     * Body: { evento_id, seriais: [{serial, sala_id?}], observacao? }
     * Responde: { ok, inseridos, falhas, resumo, pdf_base64 }
     */
    public function inserirLote(): Response
    {
        $idEvento  = (int) ($this->input('evento_id') ?? $this->post('evento_id', 0));
        $obs       = trim((string) ($this->input('observacao') ?? $this->post('observacao', '')));

        if ($idEvento <= 0) {
            return $this->json(['ok' => false, 'error' => 'evento_id é obrigatório'], 400);
        }

        // Aceita {seriais: [...]} ou texto puro
        $rawSeriais = $this->input('seriais') ?? $this->post('seriais', '');

        $service = new MontagemService();

        if (is_array($rawSeriais)) {
            $inseridos = [];
            $falhas    = [];
            foreach ($rawSeriais as $item) {
                $serial = is_array($item) ? trim($item['serial'] ?? '') : trim((string) $item);
                if (empty($serial)) continue;
                $salaId = is_array($item) ? (isset($item['sala_id']) ? (int) $item['sala_id'] : null) : null;
                $result = $service->inserirSerial($serial, $idEvento, $salaId, $obs);
                if ($result['success']) {
                    $inseridos[] = [
                        'serial'       => $serial,
                        'produto_nome' => $result['data']['produto'] ?? '',
                        'sala_id'      => $salaId,
                    ];
                } else {
                    $falhas[] = [
                        'serial'   => $serial,
                        'mensagem' => $result['error'] ?? 'Erro desconhecido',
                    ];
                }
            }
        } else {
            $seriais = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", (string) $rawSeriais))));
            if (empty($seriais)) {
                return $this->json(['ok' => false, 'error' => 'Nenhum serial informado'], 400);
            }
            $result = $service->inserirLote($seriais, $idEvento, null, $obs);
            $inseridos = [];
            foreach ($result['seriais_inseridos'] ?? [] as $s) {
                $inseridos[] = [
                    'serial'       => (string) ($s['serial']    ?? ''),
                    'produto_nome' => (string) ($s['produto']   ?? ''),
                    'sala_id'      => $s['id_sala'] ?? null,
                ];
            }
            $falhas = [];
            foreach ($result['erros'] ?? [] as $e) {
                $falhas[] = [
                    'serial'     => (string) ($e['serial'] ?? ''),
                    'mensagem'   => (string) ($e['erro']   ?? 'Erro desconhecido'),
                ];
            }
        }

        $totalEnviados = count($inseridos) + count($falhas);

        $response = [
            'ok'           => true,
            'inseridos'    => $inseridos,
            'falhas'       => $falhas,
            'resumo'       => [
                'total_enviados' => $totalEnviados,
                'sucesso'        => count($inseridos),
                'erros'          => count($falhas),
            ],
        ];

        // Gerar PDF se houver inseridos
        if (!empty($inseridos)) {
            try {
                $pdfBase64 = $this->gerarPdfMontagem($idEvento, $inseridos, $falhas, $obs);
                $response['pdf_base64'] = $pdfBase64;
            } catch (\Throwable $e) {
                // PDF falhou — não interrompe a resposta
            }
        }

        return $this->json($response);
    }

    /**
     * Gera PDF da Ordem de Servico da montagem e retorna base64
     */
    private function gerarPdfMontagem(int $idEvento, array $inseridos, array $falhas, string $obs): string
    {
        $pdo = Connection::get();

        // Dados do evento
        $evStmt = $pdo->prepare('SELECT nome_evento, local_evento, data_inicio, data_fim, estado, status_locacao FROM eventos WHERE id = ? LIMIT 1');
        $evStmt->execute([$idEvento]);
        $evento = $evStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        $title = 'Ordem de Servico — Montagem';
        $nomeEvento  = $evento['nome_evento']    ?? "Evento #{$idEvento}";
        $localEvento = $evento['local_evento']   ?? '';
        $dataInicio  = $evento['data_inicio']    ?? '';
        $dataFim     = $evento['data_fim']       ?? '';

        $pdf  = new PdfGeneratorService();
        $mpdf = $pdf->getMpdf();
        $mpdf->SetTitle($title);
        $pdf->setupHeaderFooter();

        $body = '<div style="text-align:center;margin-bottom:12px"><h1>' . htmlspecialchars($title) . '</h1></div>';

        $body .= '<table cellpadding="6" cellspacing="0" border="1" style="width:100%;border-collapse:collapse;margin-bottom:12px">';
        $body .= '<tr><td style="font-weight:bold;width:120px">Evento</td><td>' . htmlspecialchars($nomeEvento) . '</td></tr>';
        if ($localEvento) $body .= '<tr><td style="font-weight:bold">Local</td><td>' . htmlspecialchars($localEvento) . '</td></tr>';
        if ($dataInicio)  $body .= '<tr><td style="font-weight:bold">Inicio</td><td>' . htmlspecialchars($dataInicio) . ($dataFim ? ' ate ' . htmlspecialchars($dataFim) : '') . '</td></tr>';
        if ($obs)         $body .= '<tr><td style="font-weight:bold">Obs</td><td>' . htmlspecialchars($obs) . '</td></tr>';
        $body .= '</table>';

        $body .= '<p><b>Data/Hora:</b> ' . date('d/m/Y H:i') . '</p>';

        // Tabela de seriais inseridos
        $body .= '<h2 style="margin-top:16px">Seriais Inseridos</h2>';
        $body .= '<table cellpadding="5" cellspacing="0" border="1" style="width:100%;border-collapse:collapse"><thead><tr><th>#</th><th>Serial</th><th>Produto</th><th>Status</th></tr></thead><tbody>';
        foreach ($inseridos as $i => $s) {
            $body .= '<tr>'
                   . '<td>' . ($i + 1) . '</td>'
                   . '<td>' . htmlspecialchars((string) ($s['serial'] ?? '')) . '</td>'
                   . '<td>' . htmlspecialchars((string) ($s['produto_nome'] ?? '')) . '</td>'
                   . '<td><span style="color:green">Pendente</span></td>'
                   . '</tr>';
        }
        $body .= '</tbody></table>';

        // Tabela de falhas
        if (!empty($falhas)) {
            $body .= '<h2 style="margin-top:16px">Falhas</h2>';
            $body .= '<table cellpadding="5" cellspacing="0" border="1" style="width:100%;border-collapse:collapse"><thead><tr><th>#</th><th>Serial</th><th>Erro</th></tr></thead><tbody>';
            foreach ($falhas as $i => $f) {
                $body .= '<tr>'
                       . '<td>' . ($i + 1) . '</td>'
                       . '<td>' . htmlspecialchars((string) ($f['serial'] ?? '')) . '</td>'
                       . '<td>' . htmlspecialchars((string) ($f['mensagem'] ?? '')) . '</td>'
                       . '</tr>';
            }
            $body .= '</tbody></table>';
        }

        $empresa = $pdf->getEmpresa();
        $footer  = $pdf->generateFooter();

        $mpdf->WriteHTML($body);
        $mpdf->SetHTMLFooter($footer);

        return base64_encode($mpdf->Output('', 'S'));
    }
}

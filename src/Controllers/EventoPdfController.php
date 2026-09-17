<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Env;
use App\Core\Logger;
use App\Service\EventoService;
use App\Service\ClienteService;
use App\Service\ProdutorService;
use App\Service\DemandanteService;
use App\Service\SalaService;
use App\Service\ProdutoEventoService;
use App\Service\MontagemService;
use App\Service\SublocacaoService;
use App\Service\PdfGeneratorService;
use App\Service\EventoFotoService;
use App\Auth\Rbac;

class EventoPdfController extends Controller
{
    private EventoService $service;
    private ClienteService $clienteService;
    private ProdutorService $produtorService;
    private DemandanteService $demandanteService;
    private SalaService $salaService;
    private ProdutoEventoService $produtoEventoService;
    private MontagemService $montagemService;
    private SublocacaoService $sublocacaoService;
    private PdfGeneratorService $pdfService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new EventoService();
        $this->clienteService = new ClienteService();
        $this->produtorService = new ProdutorService();
        $this->demandanteService = new DemandanteService();
        $this->salaService = new SalaService();
        $this->produtoEventoService = new ProdutoEventoService();
        $this->montagemService = new MontagemService();
        $this->sublocacaoService = new SublocacaoService();
        $this->pdfService = new PdfGeneratorService();
    }

    public function gerarPdf($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return Response::json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $evento = $this->service->findOrFail($id);
            $itens = $this->produtoEventoService->findByEvento($id);
            $salas = $this->salaService->findByEvento($id);

            $clienteNome = $this->getClienteNome($evento['id_cliente'] ?? null);
            $produtorNome = $this->getProdutorNome($evento['id_produtor'] ?? null);
            $demandanteNome = $this->getDemandanteNome($evento['id_demandante'] ?? null);
            $separacaoNome = $this->getSeparacaoNome($evento['id_usuario_separacao'] ?? null);

            $semValores = filter_var($this->get('sem_valores', '0'), FILTER_VALIDATE_BOOLEAN);
            $mostrarCustos = filter_var($this->get('com_custos', '0'), FILTER_VALIDATE_BOOLEAN);
            $isLocacao = ($evento['estado'] ?? 'O') === 'L';

            $totaisPorSala = [];
            $totais = ['total_venda' => 0, 'total_custo' => 0, 'total_itens' => 0];
            foreach ($itens as $item) {
                $q = (int)($item['qtd'] ?? 1);
                $v = (float)($item['valor_unit'] ?? 0);
                $c = (float)($item['custo_unit'] ?? 0);
                $d = (int)($item['dias'] ?? 1);
                $totalItem = $q * $v * $d;
                $salaKey = $item['id_sala'] ?? 'sem_sala';
                if (!isset($totaisPorSala[$salaKey])) $totaisPorSala[$salaKey] = 0;
                $totaisPorSala[$salaKey] += $totalItem;
                $totais['total_venda'] += $totalItem;
                $totais['total_custo'] += $c;
                $totais['total_itens']++;
            }

            $this->pdfService->setupHeaderFooter();

            $baseCss = $this->pdfService->getBaseCss();
            $assinaturaHtml = $this->getAssinaturaBlock($evento['id_cliente'] ?? null);
            $html = $this->renderPdfTemplate('evento', compact(
                'evento', 'salas', 'itens', 'totais', 'totaisPorSala',
                'semValores', 'isLocacao', 'mostrarCustos',
                'clienteNome', 'produtorNome', 'demandanteNome', 'separacaoNome',
                'baseCss', 'assinaturaHtml'
            ));

            $nomeArquivo = ($isLocacao ? 'locacao' : 'orcamento') . ($mostrarCustos ? '_com_custos' : '') . '_' . ($evento['nome_evento'] ?? 'evento') . '_' . date('Y-m-d_His');

            Logger::info('PDF generated', [
                'type' => $isLocacao ? 'locacao' : 'orcamento',
                'evento_id' => $id,
                'evento_nome' => $evento['nome_evento'],
                'total_itens' => $totais['total_itens'],
                'total_venda' => $totais['total_venda'],
            ]);

            $this->pdfService->download($html, $nomeArquivo);
            return new \App\Core\Response('', 200);
        } catch (\Throwable $e) {
            Logger::pdfError('Evento', $e->getMessage(), ['evento_id' => $id]);
            throw $e;
        }
    }

    public function gerarPdfFechamento(int $eventoId, bool $semValores = false): Response
    {
        if (!Rbac::check('fechamento.gerar_pdf')) {
            return Response::json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $fechamentoService = new \App\Service\FechamentoService();
            $evento = $this->service->findOrFail($eventoId);

            $colaboradores = $fechamentoService->getColaboradores($eventoId);
            $fornecedores = $fechamentoService->getFornecedores($eventoId);
            $outros = $fechamentoService->getOutrosCustos($eventoId);
            $totais = $fechamentoService->getTotais($eventoId);

            $fotoService = new EventoFotoService();
            $fotos = $fotoService->listarFotos($eventoId);

            $clienteNome = $this->getClienteNome($evento['id_cliente'] ?? null);
            $produtorNome = $this->getProdutorNome($evento['id_produtor'] ?? null);
            $demandanteNome = $this->getDemandanteNome($evento['id_demandante'] ?? null);

            $this->pdfService->setupHeaderFooter();

            $baseCss = $this->pdfService->getBaseCss();
            $assinaturaHtml = $this->getAssinaturaBlock($evento['id_cliente'] ?? null);
            $publicDir = realpath(__DIR__ . '/../../public');
            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            $html = $this->renderPdfTemplate('fechamento', compact(
                'evento', 'colaboradores', 'fornecedores', 'outros', 'totais', 'fotos',
                'semValores', 'clienteNome', 'produtorNome', 'demandanteNome',
                'baseCss', 'assinaturaHtml', 'publicDir', 'baseUrl'
            ));

            $nomeArquivo = 'fechamento_' . ($evento['nome_evento'] ?? 'evento') . '_' . date('Y-m-d_His');

            Logger::info('PDF fechamento generated', [
                'evento_id' => $eventoId,
                'evento_nome' => $evento['nome_evento'],
                'total_colaboradores' => count($colaboradores),
                'total_fornecedores' => count($fornecedores),
            ]);

            $this->pdfService->download($html, $nomeArquivo);
            return new \App\Core\Response('', 200);
        } catch (\Throwable $e) {
            Logger::pdfError('Fechamento', $e->getMessage(), ['evento_id' => $eventoId]);
            throw $e;
        }
    }

    public function gerarPdfMontagem($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return Response::json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $evento = $this->service->findOrFail($id);
            $salas = $this->salaService->findByEvento($id);

            $produtosPorSala = [];
            foreach ($salas as $sala) {
                $produtosPorSala[$sala['id']] = $this->produtoEventoService->findBySala($sala['id']);
            }

            $montagemRaw = $this->montagemService->findBySala($id);
            $montagemPorSala = [];
            foreach ($montagemRaw as $salaData) {
                $montagemPorSala[$salaData['sala_id']] = $salaData['itens'] ?? [];
            }

            $sublocacaoRaw = $this->sublocacaoService->findByEvento($id);
            $sublocacoesPorProduto = [];
            foreach ($sublocacaoRaw as $sub) {
                if (($sub['status'] ?? '') === 'cancelado') continue;
                $peId = (int)$sub['id_produto_evento'];
                $sublocacoesPorProduto[$peId][] = $sub;
            }

            $clienteNome = $this->getClienteNome($evento['id_cliente'] ?? null);
            $produtorNome = $this->getProdutorNome($evento['id_produtor'] ?? null);
            $demandanteNome = $this->getDemandanteNome($evento['id_demandante'] ?? null);
            $separacaoNome = $this->getSeparacaoNome($evento['id_usuario_separacao'] ?? null);

            $this->pdfService->setupHeaderFooter();

            $baseCss = $this->pdfService->getBaseCss();
            $assinaturaHtml = $this->getAssinaturaBlock($evento['id_cliente'] ?? null);
            $html = $this->renderPdfTemplate('montagem', compact(
                'evento', 'salas', 'produtosPorSala', 'montagemPorSala',
                'sublocacoesPorProduto',
                'clienteNome', 'produtorNome', 'demandanteNome', 'separacaoNome',
                'baseCss', 'assinaturaHtml'
            ));

            $nomeArquivo = 'plano_montagem_' . ($evento['nome_evento'] ?? 'evento') . '_' . date('Y-m-d_His');

            Logger::info('PDF montagem generated', [
                'evento_id' => $id,
                'evento_nome' => $evento['nome_evento'],
            ]);

            $this->pdfService->download($html, $nomeArquivo);
            return new \App\Core\Response('', 200);
        } catch (\Throwable $e) {
            Logger::pdfError('Montagem', $e->getMessage(), ['evento_id' => $id]);
            throw $e;
        }
    }

    private function renderPdfTemplate(string $view, array $vars): string
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        include __DIR__ . '/../../views/pdf/' . $view . '.php';
        return (string)ob_get_clean();
    }

    private function getClienteDados(?int $idCliente): array
    {
        if (empty($idCliente)) return [];
        return $this->clienteService->find($idCliente) ?? [];
    }

    private function getEmpresaDados(): array
    {
        $rows = \App\Database\Connection::query("SELECT nome, cnpj FROM empresa LIMIT 1");
        return $rows[0] ?? [];
    }

    private function fmtCnpj(string $doc): string
    {
        $d = preg_replace('/\D/', '', $doc);
        if (strlen($d) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d);
        }
        if (strlen($d) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d);
        }
        return $doc;
    }

    private function getAssinaturaBlock(?int $idCliente): string
    {
        $empresa = $this->getEmpresaDados();
        $cliente = $this->getClienteDados($idCliente);

        $empNome = $empresa['nome'] ?? '';
        $empCnpj = !empty($empresa['cnpj']) ? $this->fmtCnpj($empresa['cnpj']) : '';
        $cliNome = $cliente['razao_social'] ?? $cliente['nome_fantasia'] ?? '';
        $cliDoc  = !empty($cliente['cpf_cnpj']) ? $this->fmtCnpj($cliente['cpf_cnpj']) : '';

        $col = function(string $nome, string $doc, string $label) {
            $html  = '<td style="width:50%;padding:0 24px;text-align:center;vertical-align:top">';
            $html .= '<div style="border-top:1px dashed #64748b;padding-top:10px;margin-top:40px">';
            if ($nome) $html .= '<div style="font-size:11px;font-weight:700;color:#1e293b;margin-bottom:2px">' . htmlspecialchars($nome) . '</div>';
            if ($doc)  $html .= '<div style="font-size:10px;color:#475569;margin-bottom:4px">CNPJ/CPF: ' . htmlspecialchars($doc) . '</div>';
            $html .= '<div style="font-size:10px;color:#94a3b8">' . htmlspecialchars($label) . '</div>';
            $html .= '</div></td>';
            return $html;
        };

        $html  = '<table style="width:100%;border-collapse:collapse;margin-top:32px;page-break-inside:avoid">';
        $html .= '<tr>';
        $html .= $col($empNome, $empCnpj, 'Locadora');
        $html .= $col($cliNome, $cliDoc,  'Contratante');
        $html .= '</tr></table>';

        return $html;
    }

    private function getClienteNome(?int $idCliente): string
    {
        if (empty($idCliente)) return '';
        $cliente = $this->clienteService->find($idCliente);
        return $cliente ? ($cliente['nome_fantasia'] ?? $cliente['razao_social'] ?? '') : '';
    }

    private function getProdutorNome(?int $idProdutor): string
    {
        $dados = $this->getProdutorDados($idProdutor);
        return $dados['nome'] ?? '';
    }

    private function getProdutorDados(?int $idProdutor): array
    {
        if (empty($idProdutor)) return [];
        $produtor = $this->produtorService->find($idProdutor);
        return $produtor ?? [];
    }

    private function getDemandanteNome(?int $idDemandante): string
    {
        if (empty($idDemandante)) return '';
        $demandante = $this->demandanteService->find($idDemandante);
        return $demandante ? ($demandante['nome'] ?? '') : '';
    }

    private function getSeparacaoNome(?int $idUsuario): string
    {
        if (empty($idUsuario)) return '';
        $user = (new \App\Repository\UserRepository())->find($idUsuario);
        return $user ? ($user['name'] ?? '') : '';
    }
}

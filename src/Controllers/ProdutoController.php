<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Http\Controller;
use App\Core\Response;
use App\Service\ProdutoService;
use App\Service\SecaoService;
use App\Service\RelatorioEstoqueService;
use App\Service\ConsultaAlocacaoService;
use App\Auth\Rbac;

class ProdutoController extends Controller
{
    private ProdutoService $service;
    private SecaoService $secaoService;
    private RelatorioEstoqueService $relatorioService;
    private ConsultaAlocacaoService $consultaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ProdutoService();
        $this->secaoService = new SecaoService();
        $this->relatorioService = new RelatorioEstoqueService();
        $this->consultaService = new ConsultaAlocacaoService();
    }

    public function index(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $produtos = $this->service->all();
        $secoes = $this->secaoService->getAtivas();

        return $this->view('estoque/index', [
            'title' => 'Produtos',
            'produtos' => $produtos,
            'secoes' => $secoes,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('estoque.criar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $secoes = $this->secaoService->getAtivas();

        return $this->view('estoque/create', [
            'title' => 'Novo Produto',
            'secoes' => $secoes,
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('estoque.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $data = [
                'produto' => $this->post('produto'),
                'codigo_barras' => $this->post('codigo_barras') ?: null,
                'custo' => $this->post('custo'),
                'id_secao' => $this->post('id_secao'),
                'pode_ser_locado' => $this->post('pode_ser_locado'),
                'observacao' => $this->post('observacao'),
            ];

            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Produto criado com sucesso']);
            return $this->redirect($this->baseUrl . '/estoque?success=created');
        } catch (\InvalidArgumentException $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->redirect($this->baseUrl . '/estoque/create?error=' . urlencode($e->getMessage()));
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        try {
            $produto = $this->service->findOrFail($id);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Produto nao encontrado.';
            return $this->redirect($this->baseUrl . '/estoque');
        }
        $secoes = $this->secaoService->getAtivas();
        $seriais = $this->service->findByProduto($id);
        $seriaisCount = $this->service->countSeriaisByProduto($id);

        return $this->view('estoque/edit', [
            'title' => 'Editar Produto',
            'produto' => $produto,
            'secoes' => $secoes,
            'seriais' => $seriais,
            'seriais_count' => $seriaisCount,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $data = [
                'produto' => $this->post('produto'),
                'codigo_barras' => $this->post('codigo_barras') ?: null,
                'custo' => $this->post('custo'),
                'id_secao' => $this->post('id_secao'),
                'pode_ser_locado' => $this->post('pode_ser_locado'),
                'observacao' => $this->post('observacao'),
            ];

            $this->service->update($id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Produto atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/estoque?success=updated');
        } catch (\InvalidArgumentException $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->redirect($this->baseUrl . '/estoque/edit/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('estoque.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['success' => true, 'message' => 'Produto excluido com sucesso']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao excluir produto: ' . $e->getMessage()], 500);
        }
    }

    public function toggleLocado($id): Response
    {
        if (!Rbac::check('estoque.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newValue = $this->service->toggleLocado($id);
            return $this->json(['success' => true, 'pode_ser_locado' => $newValue]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function seriais($id): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        try {
            $produto = $this->service->findOrFail($id);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Produto nao encontrado.';
            return $this->redirect($this->baseUrl . '/estoque');
        }
        $seriais = $this->service->findByProduto($id);
        $seriais_count = $this->service->countSeriaisByProduto($id);

        return $this->view('estoque/seriais', [
            'title' => 'Códigos de Barras - ' . $produto['produto'],
            'produto' => $produto,
            'seriais' => $seriais,
            'seriais_count' => $seriais_count,
        ]);
    }

    public function relatorios(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $tipos = $this->relatorioService->getTiposRelatorio();
        $secoes = $this->relatorioService->getSecoes();
        $produtos = $this->relatorioService->getProdutos();

        return $this->view('estoque/relatorios', [
            'title' => 'Relatorios de Estoque',
            'tiposRelatorio' => $tipos,
            'secoesList' => $secoes,
            'produtosList' => $produtos,
        ]);
    }

    public function gerarRelatorio(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            http_response_code(403);
            return $this->json(['error' => 'Acesso nao autorizado']);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido']);
        }

        $tipo = $this->post('tipo');
        $status = $this->post('status', '');
        $filtros = [];

        try {
            $dados = [];

            switch ($tipo) {
                case 'geral':
                    $dados = $this->relatorioService->buscarGeral($status);
                    if ($status) {
                        $filtros['Status'] = $status;
                    }
                    break;

                case 'por_secao':
                    $idSecao = (int) $this->post('id_secao');
                    if ($idSecao > 0) {
                        $secoes = $this->relatorioService->getSecoes();
                        $secaoNome = '';
                        foreach ($secoes as $sec) {
                            if ($sec['id'] == $idSecao) {
                                $secaoNome = $sec['secao'];
                                break;
                            }
                        }
                        $dados = $this->relatorioService->buscarPorSecao($idSecao, $status);
                        $filtros['Secao'] = $secaoNome ?: 'N/A';
                    } else {
                        // Sem secao especifica = relatorio geral
                        $dados = $this->relatorioService->buscarGeral($status);
                        $filtros['Secao'] = 'Todas';
                    }
                    if ($status) {
                        $filtros['Status'] = $status;
                    }
                    break;

                case 'manutencao':
                case 'vender':
                    $dados = $this->relatorioService->buscarPorStatus(strtoupper($tipo));
                    $filtros['Status'] = strtoupper($tipo);
                    break;

                case 'por_produto':
                    $idProduto = (int) $this->post('id_produto');
                    $produtos = $this->relatorioService->getProdutos();
                    $produtoNome = '';
                    foreach ($produtos as $prod) {
                        if ($prod['id'] == $idProduto) {
                            $produtoNome = $prod['produto'];
                            break;
                        }
                    }
                    $dados = $this->relatorioService->buscarPorProduto($idProduto, $status);
                    $filtros['Produto'] = $produtoNome ?: 'N/A';
                    if ($status) {
                        $filtros['Status'] = $status;
                    }
                    break;

                default:
                    return $this->json(['error' => 'Tipo de relatorio invalido']);
            }

            $this->relatorioService->gerarPDF($dados, $tipo, $filtros);
            // gerarPDF faz download e exit, entao retornamos algo apenas em caso de erro
            return $this->json(['error' => 'Erro ao gerar relatorio']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erro ao gerar relatorio: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /estoque/em-campo?id_produto=X
     * Retorna seriais ativos em montagens (pendente/montado) com detalhes do evento e sala
     */
    public function emCampo(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $idProduto = (int)($this->get('id_produto') ?? 0);
        if (!$idProduto) {
            return $this->json(['success' => false, 'message' => 'id_produto obrigatório'], 400);
        }

        try {
            $dados = \App\Database\Connection::query(
                "SELECT m.id as montagem_id, m.status as status_montagem,
                        sp.serial, sp.id as serial_id,
                        e.id as evento_id, e.nome_evento, e.data_inicio, e.data_fim, e.local_evento,
                        sl.nome_sala
                 FROM montagens m
                 JOIN seriaisproduto sp ON sp.id = m.id_serial
                 JOIN eventos e ON e.id = m.id_evento
                 LEFT JOIN salas sl ON sl.id = m.id_sala
                 WHERE sp.id_produto = ? AND m.status != 'devolvido'
                 ORDER BY e.data_inicio DESC, sl.nome_sala, sp.serial",
                [$idProduto]
            );
            return $this->json(['success' => true, 'data' => $dados]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /estoque/alocacoes?produto=Nome+do+Produto
     */
    public function alocacoesProduto(): Response
    {
        if (!Rbac::check('estoque.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $nomeProduto = trim($this->get('produto') ?? '');
        if (empty($nomeProduto)) {
            return $this->json(['success' => false, 'message' => 'Produto não informado'], 400);
        }

        try {
            $repo = new \App\Repository\ProdutoRepository();
            $eventos = $repo->alocacoesPorNome($nomeProduto);
            return $this->json(['success' => true, 'data' => $eventos, 'produto' => $nomeProduto]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Consultar alocação de um produto
     * GET /api/produtos/consultar-alocacao?nome=Cadeira&evento_id=1
     */
    public function consultarAlocacao(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $nomeProduto = $this->get('nome') ?? $this->post('nome') ?? '';
        $idEventoAtual = (int)($this->get('evento_id') ?? $this->post('evento_id') ?? 0);

        if (empty($nomeProduto)) {
            return $this->json(['success' => false, 'error' => 'Nome do produto é obrigatório']);
        }

        try {
            $resultado = $this->consultaService->consultarPorNome($nomeProduto, $idEventoAtual);
            return $this->json(['success' => true, 'data' => $resultado]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Resumo rápido de disponibilidade
     * GET /api/produtos/disponibilidade?nome=Cadeira
     */
    public function disponibilidade(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $nomeProduto = $this->get('nome') ?? $this->post('nome') ?? '';

        if (empty($nomeProduto)) {
            return $this->json(['success' => false, 'error' => 'Nome do produto é obrigatório']);
        }

        try {
            $resultado = $this->consultaService->resumoDisponibilidade($nomeProduto);
            return $this->json(['success' => true, 'data' => $resultado]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('estoque.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $idsRaw = $this->post('ids');
        $ids = json_decode($idsRaw, true);
        if (!is_array($ids) || empty($ids)) {
            return $this->json(['error' => 'Nenhum ID fornecido'], 400);
        }

        $deleted = 0;
        $errors = [];
        foreach ($ids as $id) {
            try {
                $this->service->delete((int)$id);
                $deleted++;
            } catch (\Throwable $e) {
                $errors[] = "ID $id: " . $e->getMessage();
            }
        }

        return $this->json([
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }
}
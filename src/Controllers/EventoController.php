<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Logger;
use App\Service\EventoService;
use App\Service\ClienteService;
use App\Service\ProdutorService;
use App\Service\DemandanteService;
use App\Service\SalaService;
use App\Service\ProdutoEventoService;
use App\Service\CategoriaSalaService;
use App\Service\MontagemService;
use App\Service\DevolucaoService;
use App\Repository\DevolucaoRepository;
use App\Repository\ColaboradorRepository;
use App\Repository\UserRepository;
use App\Auth\Rbac;
class EventoController extends Controller
{
    private EventoService $service;
    private ClienteService $clienteService;
    private ProdutorService $produtorService;
    private DemandanteService $demandanteService;
    private SalaService $salaService;
    private ProdutoEventoService $produtoEventoService;
    private CategoriaSalaService $categoriaSalaService;
    private MontagemService $montagemService;
    private DevolucaoService $devolucaoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new EventoService();
        $this->clienteService = new ClienteService();
        $this->produtorService = new ProdutorService();
        $this->demandanteService = new DemandanteService();
        $this->salaService = new SalaService();
        $this->produtoEventoService = new ProdutoEventoService();
        $this->categoriaSalaService = new CategoriaSalaService();
        $this->montagemService = new MontagemService();
        $this->devolucaoService = new DevolucaoService();
    }

    public function index(): Response
    {
        if (!Rbac::check('eventos.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $estado = $this->get('estado', '');
        $statusLocacao = $this->get('status_locacao', '');
        $pendencia = $this->get('pendencia', '');

        $filters = [];
        if (!empty($estado)) $filters['estado'] = $estado;
        if (!empty($statusLocacao)) $filters['status_locacao'] = $statusLocacao;
        if ($pendencia !== '') $filters['pendencia'] = $pendencia;

        // Comercial só vê os próprios eventos; admin e estoquista veem todos
        if (Rbac::isComercial()) {
            $user = Rbac::getUser();
            $produtorAtual = (new \App\Repository\ProdutorRepository())->findByUserId((int) $user['id']);
            $idProdutor = $produtorAtual ? (int) $produtorAtual['id'] : null;
            $eventos = $idProdutor ? $this->service->allByProdutor($idProdutor) : [];
            $stats = $idProdutor
                ? $this->service->countByFilters(array_merge($filters, ['id_produtor' => $idProdutor]))
                : ['total' => 0, 'confirmados' => 0, 'pendentes' => 0, 'cancelados' => 0];
        } else {
            $eventos = $this->service->all();
            $stats = $this->service->countByFilters($filters);
        }

        return $this->view('evento/index', [
            'title' => 'Eventos',
            'eventos' => $eventos,
            'estado' => $estado,
            'status_locacao' => $statusLocacao,
            'pendencia' => $pendencia,
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('eventos.criar')) {
            return $this->redirect($this->baseUrl . '/eventos');
        }

        $clientes = $this->clienteService->allAtivos();
        $produtores = $this->produtorService->allAtivos();
        $demandantes = $this->demandanteService->allAtivos();
        $usuarios = (new UserRepository())->allAtivos();

        return $this->view('evento/create', [
            'title' => 'Novo Evento',
            'clientes' => $clientes,
            'produtores' => $produtores,
            'demandantes' => $demandantes,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('eventos.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $data = [
            'id_cliente' => $this->post('id_cliente') ?: null,
            'id_produtor' => $this->post('id_produtor') ?: null,
            'id_demandante' => $this->post('id_demandante') ?: null,
            'id_usuario_separacao' => $this->post('id_usuario_separacao') ?: null,
            'nome_evento' => $this->post('nome_evento'),
            'local_evento' => $this->post('local_evento'),
            'os_cliente' => $this->post('os_cliente'),
            'demandante_local' => $this->post('demandante_local'),
            'telefone_demandantelocal' => $this->post('telefone_demandantelocal'),
            'observacao' => $this->post('observacao'),
            'data_montagem' => $this->post('data_montagem') ?: null,
            'hora_montagem' => $this->post('hora_montagem') ?: null,
            'data_inicio' => $this->post('data_inicio') ?: null,
            'hora_inicio' => $this->post('hora_inicio') ?: null,
            'data_fim' => $this->post('data_fim') ?: null,
            'hora_fim' => $this->post('hora_fim') ?: null,
            'data_desmontagem' => $this->post('data_desmontagem') ?: null,
            'hora_desmontagem' => $this->post('hora_desmontagem') ?: null,
            'estado' => $this->post('estado', 'O'),
            'status_locacao' => $this->post('status_locacao', 'A'),
            'evento_montado' => $this->post('evento_montado') ? 1 : 0,
            'evento_desmontado' => $this->post('evento_desmontado') ? 1 : 0,
        ];

        try {
            $id = $this->service->create($data);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->redirect($this->baseUrl . '/eventos');
        }

        try {
            $evento = $this->service->findOrFail($id);
        } catch (\Throwable $e) {
            return $this->redirect($this->baseUrl . '/eventos?erro=Evento+n%C3%A3o+encontrado+ou+foi+removido');
        }
        $clientes = $this->comIndicadorInativo(
            $this->clienteService->allAtivos(),
            $evento['id_cliente'] ?? null,
            fn($cid) => $this->clienteService->find($cid),
            'nome_fantasia'
        );
        $produtores = $this->comIndicadorInativo(
            $this->produtorService->allAtivos(),
            $evento['id_produtor'] ?? null,
            fn($pid) => $this->produtorService->find($pid),
            'nome'
        );
        $demandantes = $this->comIndicadorInativo(
            $this->demandanteService->allAtivos(),
            $evento['id_demandante'] ?? null,
            fn($did) => $this->demandanteService->find($did),
            'nome'
        );
        $usuarios = (new UserRepository())->allAtivos();

        $salas = $this->salaService->findByEvento($id);
        $categorias = $this->categoriaSalaService->getAtivas();
        $produtosPorSala = [];
        foreach ($salas as $sala) {
            $produtosPorSala[$sala['id']] = $this->produtoEventoService->findBySala($sala['id']);
        }
        $grouped = $this->produtoEventoService->groupBySala($id);
        $totais = $this->produtoEventoService->getTotaisEvento($id);

        $salasMontagem = $this->montagemService->findByEvento($id);
        $eventos = $this->service->search([], 1, 100);

        $devolucaoStats = $this->devolucaoService->getStatsByEvento($id);
        $devRepo = new DevolucaoRepository();
        $devolucoesPendentes = $devRepo->findPendentesByEvento($id);
        $devolucoesTodas = $devRepo->findByEvento($id);

        $colabRepo = new ColaboradorRepository();
        $colaboradoresList = $colabRepo->search('', 1, 1000);
        $colaboradoresList['data'] = array_values(array_filter($colaboradoresList['data'] ?? [], function($c) {
            return !empty($c['ativo']);
        }));

        return $this->view('evento/edit', [
            'title' => 'Editar Evento',
            'evento' => $evento,
            'clientes' => $clientes,
            'produtores' => $produtores,
            'demandantes' => $demandantes,
            'usuarios' => $usuarios,
            'salas' => $salas,
            'categorias' => $categorias,
            'produtosPorSala' => $produtosPorSala,
            'grouped' => $grouped,
            'totais' => $totais,
            'produtoEventoService' => $this->produtoEventoService,
            'salasMontagem' => $salasMontagem,
            'eventosMontagem' => $eventos['data'] ?? [],
            'idEventoMontagem' => $id,
            'produtosEventoPorSala' => $produtosPorSala,
            'devolucaoStats' => $devolucaoStats,
            'devolucoesPendentes' => $devolucoesPendentes,
            'devolucoesTodas' => $devolucoesTodas,
            'colaboradoresList' => $colaboradoresList['data'] ?? [],
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $data = [
            'id_cliente' => $this->post('id_cliente') ?: null,
            'id_produtor' => $this->post('id_produtor') ?: null,
            'id_demandante' => $this->post('id_demandante') ?: null,
            'id_usuario_separacao' => $this->post('id_usuario_separacao') ?: null,
            'nome_evento' => $this->post('nome_evento'),
            'local_evento' => $this->post('local_evento'),
            'os_cliente' => $this->post('os_cliente'),
            'demandante_local' => $this->post('demandante_local'),
            'telefone_demandantelocal' => $this->post('telefone_demandantelocal'),
            'observacao' => $this->post('observacao'),
            'data_montagem' => $this->post('data_montagem') ?: null,
            'hora_montagem' => $this->post('hora_montagem') ?: null,
            'data_inicio' => $this->post('data_inicio') ?: null,
            'hora_inicio' => $this->post('hora_inicio') ?: null,
            'data_fim' => $this->post('data_fim') ?: null,
            'hora_fim' => $this->post('hora_fim') ?: null,
            'data_desmontagem' => $this->post('data_desmontagem') ?: null,
            'hora_desmontagem' => $this->post('hora_desmontagem') ?: null,
            'estado' => $this->post('estado', 'O'),
            'status_locacao' => $this->post('status_locacao', 'A'),
            'evento_montado' => $this->post('evento_montado') ? 1 : 0,
            'evento_desmontado' => $this->post('evento_desmontado') ? 1 : 0,
            'observacoes_fechamento' => $this->post('observacoes_fechamento'),
        ];

        try {
            $this->service->update($id, $data);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateComprador($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->update($id, [
                'id_demandante' => $this->post('id_demandante') ?: null,
            ]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateSeparacao($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->update($id, [
                'id_usuario_separacao' => $this->post('id_usuario_separacao') ?: null,
            ]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateEstado($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->update($id, [
                'estado' => $this->post('estado', 'O'),
            ]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updatePedido($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->update($id, [
                'os_cliente' => $this->post('os_cliente') ?: null,
            ]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('eventos.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->delete($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function toggle($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newStatus = $this->service->toggleStatus($id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function converter($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->converterEmLocacao($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function finalizar($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $observacoes = $this->post('observacoes_fechamento');
            $this->service->finalizarLocacao($id, ['observacoes_fechamento' => $observacoes]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('eventos.excluir')) {
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

    /**
     * Garante que o registro ja vinculado ao evento (cliente/produtor/demandante)
     * continue aparecendo no select mesmo se foi desativado depois -- senao a
     * edicao do evento perderia esse vinculo silenciosamente ao salvar. So
     * busca e anexa se o id atual nao estiver na lista de ativos.
     */
    private function comIndicadorInativo(array $ativos, ?int $idAtual, callable $find, string $labelField): array
    {
        if (!$idAtual) {
            return $ativos;
        }
        foreach ($ativos as $item) {
            if ((int)($item['id'] ?? 0) === $idAtual) {
                return $ativos;
            }
        }
        $item = $find($idAtual);
        if ($item) {
            $item[$labelField] = trim(($item[$labelField] ?? '') . ' (Inativo)');
            $ativos[] = $item;
        }
        return $ativos;
    }
}

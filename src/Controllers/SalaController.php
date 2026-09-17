<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\SalaService;
use App\Service\CategoriaSalaService;
use App\Service\ProdutoEventoService;
use App\Auth\Rbac;

class SalaController extends Controller
{
    private SalaService $service;
    private CategoriaSalaService $categoriaService;
    private ProdutoEventoService $produtoEventoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new SalaService();
        $this->categoriaService = new CategoriaSalaService();
        $this->produtoEventoService = new ProdutoEventoService();
    }

    /**
     * Lista salas de um evento (API)
     * Aceita autenticacao por sessao RBAC ou por API Key (appmontagem)
     */
    public function listByEvento(int $id = 0): Response
    {
        $temSessao = Rbac::check('eventos.editar');
        $temApiKey = \App\Http\Middleware\ApiKeyMiddleware::hasPermission('eventos:read');

        if (!$temSessao && !$temApiKey) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        // Aceita evento_id como query param OU path param {id}
        $idEvento = (int) ($this->get('evento_id') ?? $this->get('id') ?? $id ?: 0);
        $salas = $this->service->findByEvento($idEvento);

        // Inclui os produtos de cada sala — window.SALAS_MAP (usado pelo fluxo
        // de encaminhar serial em Montar OS) espera esse formato; sem isso o
        // refresh via carregarSalasDoServidor() sobrescreve o mapa inicial
        // (renderizado pelo PHP com produtos) por um sem produtos, quebrando
        // o dropdown de "item destino" apos a 1a atualizacao.
        foreach ($salas as &$sala) {
            $sala['produtos'] = $this->produtoEventoService->findBySala((int) $sala['id']);
        }
        unset($sala);

        return $this->json(['success' => true, 'data' => $salas]);
    }

    /**
     * Lista salas de um evento
     */
    public function index(int $idEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $salas = $this->service->findByEvento($idEvento);
        $categorias = $this->categoriaService->getAtivas();

        return $this->json(['success' => true, 'data' => $salas, 'categorias' => $categorias]);
    }

    /**
     * Cria uma sala
     */
    public function store(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['ok' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('csrf_token') ?: $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $data = [
            'id_evento' => $this->post('evento_id') ?: $this->post('id_evento'),
            'nome_sala' => $this->post('nome_sala') ?: $this->post('nome'),
            'orientacoes_montagem' => $this->post('observacao_montagem') ?: $this->post('orientacoes_montagem'),
            'ordem' => (int) ($this->post('ordem') ?: 0),
        ];

        try {
            $id = $this->service->create($data);
            return $this->json(['ok' => true, 'id' => $id, 'nome' => $data['nome_sala']]);
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Atualiza uma sala (via PUT)
     */
    public function update($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['ok' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        // Parse body para PUT requests
        $body = file_get_contents('php://input');
        parse_str($body, $data);

        $csrfToken = $data['csrf_token'] ?? $data['_csrf_token'] ?? ($this->post('csrf_token') ?? $this->post('_csrf_token') ?? '');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $nomeSala = $data['nome_sala'] ?? $data['nome'] ?? '';
        $obsMontagem = $data['observacao_montagem'] ?? $data['orientacoes_montagem'] ?? '';

        try {
            $this->service->update($id, [
                'nome_sala' => $nomeSala,
                'orientacoes_montagem' => $obsMontagem,
                'ordem' => (int) ($data['ordem'] ?? 0),
            ]);
            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Deleta uma sala (via DELETE)
     */
    public function delete($id): Response
    {
        if (!Rbac::check('eventos.excluir')) {
            return $this->json(['ok' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        // Parse body para DELETE requests
        $body = file_get_contents('php://input');
        parse_str($body, $data);

        $csrfToken = $data['_csrf_token'] ?? ($this->post('_csrf_token') ?? '');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Toggle status de uma sala
     */
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
}

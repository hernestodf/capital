<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\PlanilhaService;
use App\Service\UnidadeMedidaService;
use App\Auth\Rbac;

class PlanilhaController extends Controller
{
    private PlanilhaService $service;
    private UnidadeMedidaService $unidadeMedidaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new PlanilhaService();
        $this->unidadeMedidaService = new UnidadeMedidaService();
    }

    public function index(): Response
    {
        if (!Rbac::check('planilhas.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $planilhas = $this->service->all();
        $unidadesMedida = $this->unidadeMedidaService->all();

        return $this->view('planilha/index', [
            'title' => 'Planilhas',
            'planilhas' => $planilhas,
            'unidadesMedida' => $unidadesMedida,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('planilhas.criar')) {
            return $this->redirect($this->baseUrl . '/planilhas');
        }

        $unidadesMedida = $this->unidadeMedidaService->all();

        return $this->view('planilha/create', [
            'title' => 'Nova Planilha',
            'unidadesMedida' => $unidadesMedida,
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('planilhas.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('planilha/create', [
                'title' => 'Nova Planilha',
                'error' => 'Token CSRF inválido',
                'unidadesMedida' => $this->unidadeMedidaService->all(),
            ]);
        }

        $potW = $this->post('potencia_w');
        $data = [
            'item' => $this->post('item'),
            'descricao' => $this->post('descricao'),
            'id_unidademedida' => $this->post('id_unidademedida') ?: null,
            'valor' => $this->post('valor'),
            'potencia_w' => ($potW !== null && $potW !== '') ? (float) str_replace(',', '.', $potW) : null,
            'horas_uso'  => ($this->post('horas_uso') !== null && $this->post('horas_uso') !== '') ? (float) str_replace(',', '.', $this->post('horas_uso')) : null,
        ];

        try {
            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Planilha criada com sucesso']);
            return $this->redirect($this->baseUrl . '/planilhas');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('planilha/create', [
                'title' => 'Nova Planilha',
                'error' => $e->getMessage(),
                'data' => $data,
                'unidadesMedida' => $this->unidadeMedidaService->all(),
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('planilhas.editar')) {
            return $this->redirect($this->baseUrl . '/planilhas');
        }

        $planilha = $this->service->find((int) $id);

        if (!$planilha) {
            return $this->redirect($this->baseUrl . '/planilhas');
        }

        $unidadesMedida = $this->unidadeMedidaService->all();

        return $this->view('planilha/edit', [
            'title' => 'Editar Planilha',
            'planilha' => $planilha,
            'unidadesMedida' => $unidadesMedida,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('planilhas.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('planilha/edit', [
                'title' => 'Editar Planilha',
                'planilha' => $this->service->find((int) $id),
                'error' => 'Token CSRF inválido',
                'unidadesMedida' => $this->unidadeMedidaService->all(),
            ]);
        }

        $potW = $this->post('potencia_w');
        $data = [
            'item' => $this->post('item'),
            'descricao' => $this->post('descricao'),
            'id_unidademedida' => $this->post('id_unidademedida') ?: null,
            'valor' => $this->post('valor'),
            'potencia_w' => ($potW !== null && $potW !== '') ? (float) str_replace(',', '.', $potW) : null,
            'horas_uso'  => ($this->post('horas_uso') !== null && $this->post('horas_uso') !== '') ? (float) str_replace(',', '.', $this->post('horas_uso')) : null,
        ];

        try {
            $this->service->update((int) $id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Planilha atualizada com sucesso']);
            return $this->redirect($this->baseUrl . '/planilhas');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('planilha/edit', [
                'title' => 'Editar Planilha',
                'planilha' => $this->service->find((int) $id),
                'error' => $e->getMessage(),
                'unidadesMedida' => $this->unidadeMedidaService->all(),
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('planilhas.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete((int) $id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    public function bulkDelete(): Response
    {
        if (!Rbac::check('planilhas.excluir')) {
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
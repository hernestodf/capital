<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Service\ProdutoEventoService;
use App\Auth\Rbac;

class ProdutoEventoController extends Controller
{
    private ProdutoEventoService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ProdutoEventoService();
    }

    /**
     * Lista produtos de uma sala
     */
    public function indexBySala(int $idSala): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $produtos = $this->service->findBySala($idSala);
        return $this->json(['success' => true, 'data' => $produtos]);
    }

    /**
     * Lista produtos agrupados por sala de um evento
     */
    public function indexByEvento(int $idEvento): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $grouped = $this->service->groupBySala($idEvento);
        $totais = $this->service->getTotaisEvento($idEvento);

        return $this->json(['success' => true, 'grouped' => $grouped, 'totais' => $totais]);
    }

    /**
     * Cria um produto
     */
    public function store(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $data = [
            'id_evento' => $this->post('id_evento'),
            'id_sala' => $this->post('id_sala'),
            'id_planilha' => $this->post('id_planilha') ?: null,
            'produto' => $this->post('produto'),
            'observacao_montagem' => $this->post('observacao_montagem'),
            'qtd' => $this->post('qtd', 1),
            'valor_unit' => $this->post('valor_unit', 0),
            'dias' => $this->post('dias', 1),
            'custo_unit' => $this->post('custo_unit', 0),
        ];

        try {
            $id = $this->service->create($data);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Atualiza um produto
     */
    public function update($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $data = [
            'produto' => $this->post('produto'),
            'observacao_montagem' => $this->post('observacao_montagem'),
            'qtd' => $this->post('qtd', 1),
            'valor_unit' => $this->post('valor_unit', 0),
            'dias' => $this->post('dias', 1),
            'custo_unit' => $this->post('custo_unit', 0),
        ];

        try {
            $this->service->update($id, $data);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Deleta um produto
     */
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

    public function autocomplete(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso nao autorizado'], 403);
        }

        $term = $this->get('q', '');
        if (empty($term)) {
            return $this->json(['success' => true, 'data' => []]);
        }

        try {
            $results = $this->service->searchPlanilhas($term);
            return $this->json(['success' => true, 'data' => $results]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function adicionarItem(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $idPlanilha = $this->post('id_planilha') ?: null;
        $potenciaW  = null;
        $horasUso   = 20;
        if ($idPlanilha) {
            $pl = $this->service->getPlanilhaById((int) $idPlanilha);
            $potenciaW = isset($pl['potencia_w']) && $pl['potencia_w'] > 0 ? (float)$pl['potencia_w'] : null;
            $horasUso  = isset($pl['horas_uso'])  && $pl['horas_uso']  > 0 ? (float)$pl['horas_uso']  : 20;
        }

        $data = [
            'id_evento'   => $this->post('id_evento'),
            'id_sala'     => $this->post('id_sala') ?: null,
            'id_categoria'=> $this->post('id_categoria') ?: null,
            'id_planilha' => $idPlanilha,
            'produto'     => $this->post('item'),
            'qtd'         => (float) ($this->post('quantidade') ?: 1),
            'valor_unit'  => (float) ($this->post('valor_unit') ?: $this->post('valor') ?: 0),
            'dias'        => (int) ($this->post('dias') ?: $this->post('dias_locacao') ?: 1),
            'custo_unit'  => (float) ($this->post('custo_unit') ?: $this->post('valor_pago_fornecedor') ?: 0),
            'potencia_w'  => $potenciaW,
            'horas_uso'   => $horasUso,
        ];
        $data['total_item'] = $data['qtd'] * $data['valor_unit'] * $data['dias'];

        try {
            $id = $this->service->create($data);
            $item = $this->service->findWithPlanilha($id);
            return $this->json(['success' => true, 'item' => $item]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function excluirItem(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $itemId = $this->post('item_id');
        if (!$itemId) {
            return $this->json(['success' => false, 'error' => 'ID do item e obrigatorio'], 400);
        }

        try {
            $this->service->delete((int)$itemId);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function listarItensPlanilha(): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $itens = $this->service->searchPlanilhas('');
            return $this->json(['success' => true, 'itens' => $itens]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function salvarObsItem($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $this->service->update((int)$id, ['observacao_montagem' => $this->post('observacao_montagem')]);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function atualizarCampo($id): Response
    {
        if (!Rbac::check('eventos.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $camposPermitidos = ['qtd', 'quantidade', 'valor_unit', 'dias', 'custo_unit', 'observacao_montagem', 'potencia_w', 'horas_uso'];
        $data = [];

        foreach ($camposPermitidos as $campo) {
            $valor = $this->post($campo);
            if ($valor !== null && $valor !== '') {
                if ($campo === 'qtd' || $campo === 'quantidade') {
                    $data['qtd'] = (float) str_replace(',', '.', $valor);
                } elseif (in_array($campo, ['valor_unit', 'custo_unit', 'potencia_w', 'horas_uso'])) {
                    $data[$campo] = (float) str_replace(',', '.', $valor);
                } elseif ($campo === 'dias') {
                    $data['dias'] = (int) $valor;
                } else {
                    $data[$campo] = $valor;
                }
            }
        }

        if (empty($data)) {
            return $this->json(['success' => false, 'error' => 'Nenhum dado para atualizar'], 400);
        }

        try {
            $this->service->update((int)$id, $data);
            $item = $this->service->findWithPlanilha((int)$id);
            return $this->json(['success' => true, 'data' => $data, 'item' => $item]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}

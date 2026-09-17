<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Auth\Rbac;
use App\Core\Response;
use App\Core\Request;
use App\Service\MontagemService;

class MontagemController extends Controller
{
    private MontagemService $montagemService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->montagemService = new MontagemService();
    }

    public function inserirSerial(): Response
    {
        if (!Rbac::check('montagem.criar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $serial = trim($this->post('serial', ''));
        $idEvento = (int) $this->post('id_evento');
        $idSala = $this->post('id_sala', null);
        $observacao = trim($this->post('observacao', ''));

        if (empty($serial)) {
            return $this->json(['success' => false, 'error' => 'Serial e obrigatorio']);
        }

        if ($idEvento <= 0) {
            return $this->json(['success' => false, 'error' => 'Evento nao informado']);
        }

        $idSalaInt = $idSala ? (int) $idSala : null;
        $result = $this->montagemService->inserirSerial($serial, $idEvento, $idSalaInt, $observacao);
        return $this->json($result);
    }

    public function inserirLote(): Response
    {
        if (!Rbac::check('montagem.criar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $seriaisRaw = $this->post('seriais', '');

        if (empty($seriaisRaw)) {
            return $this->json(['success' => false, 'error' => 'Nenhum serial informado']);
        }

        if (str_starts_with(trim($seriaisRaw), '[')) {
            $seriais = json_decode(trim($seriaisRaw), true);
            if (!is_array($seriais)) {
                return $this->json(['success' => false, 'error' => 'Formato de seriais invalido']);
            }
        } else {
            $seriais = array_filter(array_map('trim', explode("\n", $seriaisRaw)));
        }

        if (empty($seriais)) {
            return $this->json(['success' => false, 'error' => 'Nenhum serial valido informado']);
        }

        $idEvento = (int) $this->post('id_evento');
        if ($idEvento <= 0) {
            return $this->json(['success' => false, 'error' => 'Evento nao informado']);
        }

        $idSala = $this->post('id_sala', null);
        $idSalaInt = $idSala ? (int) $idSala : null;
        $observacao = trim($this->post('observacao', ''));

        $result = $this->montagemService->inserirLote($seriais, $idEvento, $idSalaInt, $observacao);
        return $this->json($result);
    }

    public function encaminharSala(int $id): Response
    {
        if (!Rbac::check('montagem.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $idSala = (int) $this->post('id_sala');
        if ($idSala <= 0) {
            return $this->json(['success' => false, 'error' => 'Selecione uma sala']);
        }

        $idProdutoEvento = $this->post('id_produto_evento') ? (int) $this->post('id_produto_evento') : null;

        $result = $this->montagemService->encaminharParaSala($id, $idSala, $idProdutoEvento);
        return $this->json($result);
    }

    public function removerDaSala(int $id): Response
    {
        if (!Rbac::check('montagem.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $result = $this->montagemService->removerDaSala($id);
        return $this->json($result);
    }

    public function devolver(int $id): Response
    {
        if (!Rbac::check('montagem.editar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $result = $this->montagemService->devolverSerial($id);
        return $this->json($result);
    }

    public function listar(int $idEvento): Response
    {
        if (!Rbac::check('montagem.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $montagens = $this->montagemService->findByEvento($idEvento);
        return $this->json(['success' => true, 'data' => $montagens]);
    }
}
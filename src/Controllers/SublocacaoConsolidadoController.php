<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Env;
use App\Auth\Rbac;
use App\Repository\ProdutoEventoSublocacaoRepository;

class SublocacaoConsolidadoController extends Controller
{
    private ProdutoEventoSublocacaoRepository $repo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->repo = new ProdutoEventoSublocacaoRepository();
    }

    public function index(): Response
    {
        if (!Rbac::check('sublocacoes.listar')) {
            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            return $this->redirect($baseUrl . '/eventos');
        }

        $fornecedores = $this->repo->consolidado();

        return $this->view('sublocacoes/index', [
            'title'        => 'Sublocações',
            'fornecedores' => $fornecedores,
        ]);
    }

    public function devolver(int $id): Response
    {
        if (!Rbac::check('sublocacoes.listar')) {
            return $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        $item = $this->repo->find($id);
        if (!$item) {
            return $this->json(['success' => false, 'message' => 'Item não encontrado'], 404);
        }

        if ($item['devolvido_em'] !== null) {
            return $this->json(['success' => false, 'message' => 'Já marcado como devolvido'], 409);
        }

        $this->repo->marcarDevolvido($id);

        $itemAtualizado = $this->repo->find($id);

        return $this->json(['success' => true, 'data' => ['id' => $id, 'devolvido_em' => $itemAtualizado['devolvido_em'] ?? null]]);
    }
}

<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Env;
use App\Service\CotacaoService;
use App\Auth\Rbac;

class TestDiagController extends Controller
{
    private string $baseUrl;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    }

    public function index(): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $service = new CotacaoService();
        $itens = [];
        for ($i = 1; $i <= 5; $i++) {
            $item = $service->getItemData($i);
            if ($item) {
                $itens[] = $item;
            }
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        return $this->view('test/diag', [
            'title' => 'Diagnostico de Rotas - Cotacao',
            'itens' => $itens,
        ]);
    }
}

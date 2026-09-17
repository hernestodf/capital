<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Env;
use App\Service\CotacaoService;
use App\Service\FornecedorService;
use App\Auth\Rbac;
use App\Database\Connection;

class CotacaoViewController extends Controller
{
    private CotacaoService $cotacaoService;
    private FornecedorService $fornecedorService;
    private string $baseUrl;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->cotacaoService = new CotacaoService();
        $this->fornecedorService = new FornecedorService();
        $this->baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    }

    /**
     * Tela dedicada de cotacao de um item do evento
     * GET /eventos/cotacao/{id_evento}/{id_produto_evento}
     */
    public function showCotacao($idEvento, $idProdutoEvento): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        // Ensure parameters are integers
        $idEvento = (int) $idEvento;
        $idProdutoEvento = (int) $idProdutoEvento;

        // Dados do item do evento
        $itemData = $this->cotacaoService->getItemData($idProdutoEvento);
        if (!$itemData) {
            $_SESSION['error'] = 'Item nao encontrado';
            return $this->redirect($this->baseUrl . '/eventos/edit/' . $idEvento);
        }

        error_log("Item data: produto=" . ($itemData['produto'] ?? 'NULL'));

        // Verify that the item belongs to the event
        if ((int)$itemData['id_evento'] !== $idEvento) {
            $_SESSION['error'] = 'Item nao pertence a este evento';
            return $this->redirect($this->baseUrl . '/eventos/edit/' . $idEvento);
        }

        // Dados do evento
        $stmt = Connection::get()->prepare(
            "SELECT e.*, p.nome as produtor_nome, p.email as produtor_email, p.telefone as produtor_telefone
             FROM eventos e
             LEFT JOIN produtores p ON p.id = e.id_produtor
             WHERE e.id = ?"
        );
        $stmt->execute([$idEvento]);
        $evento = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Propostas existentes
        $propostas = $this->cotacaoService->getPropostas($idProdutoEvento);

        // Cotações ativas (para vincular mensagens mesmo sem propostas)
        $cotacoes = $this->cotacaoService->getCotacoesAtivas($idProdutoEvento);

        // Fornecedores ativos
        $fornecedores = array_values(array_filter($this->fornecedorService->all(), function($f) {
            return $f['status'] == 1;
        }));

        // Dados do vencedor atual
        $vencedor = null;
        foreach ($propostas as $p) {
            if ($p['vencedor'] === 'S') {
                $vencedor = $p;
                break;
            }
        }

        // Prevent browser caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Vary: No-Cache');

        return $this->view('evento/cotacao', [
            'title' => 'Cotacao - ' . ($itemData['produto'] ?? 'Item'),
            'evento' => $evento,
            'item' => $itemData,
            'propostas' => $propostas,
            'cotacoes' => $cotacoes,
            'fornecedores' => $fornecedores,
            'vencedor' => $vencedor,
        ]);
    }
}

<?php

namespace App\Service;

use App\Repository\ProdutoEventoRepository;
use App\Repository\ProdutoEventoSublocacaoRepository;

class ProdutoEventoAlocacaoService
{
    private ProdutoEventoRepository $produtoEventoRepo;
    private ProdutoEventoSerialService $serialService;
    private ProdutoEventoSublocacaoRepository $sublocacaoRepo;

    public function __construct()
    {
        $this->produtoEventoRepo = new ProdutoEventoRepository();
        $this->serialService = new ProdutoEventoSerialService();
        $this->sublocacaoRepo = new ProdutoEventoSublocacaoRepository();
    }

    public function calcularFaltante(int $idProdutoEvento): float
    {
        $item = $this->produtoEventoRepo->find($idProdutoEvento);
        if (!$item) {
            throw new \InvalidArgumentException('Item do evento não encontrado');
        }

        $qtd = (float)($item['qtd'] ?? 0);
        $qtdAlocada = (float)($item['qtd_alocada'] ?? 0);
        $qtdSublocada = (float)($item['qtd_sublocada'] ?? 0);

        return max(0, $qtd - $qtdAlocada - $qtdSublocada);
    }

    public function alocarEstoqueAutomatico(int $idProdutoEvento): array
    {
        $faltante = $this->calcularFaltante($idProdutoEvento);
        if ($faltante <= 0) {
            return ['success' => [], 'errors' => ['Item já está completamente coberto']];
        }

        $seriais = $this->serialService->sugerirSeriais($idProdutoEvento);
        $idsParaAlocar = [];
        $alocacoesNecessarias = (int)ceil($faltante);

        foreach ($seriais as $i => $serial) {
            if ($i >= $alocacoesNecessarias) break;
            $idsParaAlocar[] = (int)$serial['id'];
        }

        if (empty($idsParaAlocar)) {
            return ['success' => [], 'errors' => ['Nenhum serial disponível no estoque']];
        }

        return $this->serialService->alocarEmLote($idProdutoEvento, $idsParaAlocar);
    }

    public function resumoAlocacao(int $idEvento): array
    {
        $itens = $this->produtoEventoRepo->findByEvento($idEvento);
        $resumo = [];

        foreach ($itens as $item) {
            $qtd = (float)($item['qtd'] ?? 0);
            $qtdAlocada = (float)($item['qtd_alocada'] ?? 0);
            $qtdSublocada = (float)($item['qtd_sublocada'] ?? 0);
            $faltante = max(0, $qtd - $qtdAlocada - $qtdSublocada);

            $resumo[] = [
                'id' => $item['id'],
                'produto' => $item['produto'],
                'qtd' => $qtd,
                'qtd_alocada' => $qtdAlocada,
                'qtd_sublocada' => $qtdSublocada,
                'faltante' => $faltante,
                'coberto' => $faltante <= 0,
            ];
        }

        return $resumo;
    }
}

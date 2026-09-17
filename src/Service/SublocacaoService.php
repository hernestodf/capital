<?php

namespace App\Service;

class SublocacaoService
{
    private SublocacaoConsolidacaoService $consolidacaoService;

    public function __construct()
    {
        $this->consolidacaoService = new SublocacaoConsolidacaoService();
    }

    public function vincularSublocador(
        int $idProdutoEvento,
        int $idFornecedor,
        ?int $idSublocacaoItem = null,
        float $quantidade = 1,
        float $valorUnit = 0,
        ?string $serialFornecedor = null,
        ?string $produtoFornecedor = null,
        ?float $custoUnit = null,
        ?string $produto = null,
        ?string $codigoBarras = null
    ): int {
        return $this->consolidacaoService->vincularSublocador(
            $idProdutoEvento, $idFornecedor, $idSublocacaoItem, $quantidade, $valorUnit,
            $serialFornecedor, $produtoFornecedor, $custoUnit, $produto, $codigoBarras
        );
    }

    public function vincularLote(
        int $idProdutoEvento,
        int $idFornecedor,
        string $produtoFornecedor,
        float $valorUnit,
        float $custoUnit,
        array $seriais
    ): array {
        return $this->consolidacaoService->vincularLote(
            $idProdutoEvento, $idFornecedor, $produtoFornecedor, $valorUnit, $custoUnit, $seriais
        );
    }

    public function desvincularSublocador(int $id): int
    {
        return $this->consolidacaoService->desvincularSublocador($id);
    }

    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        return $this->consolidacaoService->findByProdutoEvento($idProdutoEvento);
    }

    public function findByEvento(int $idEvento): array
    {
        return $this->consolidacaoService->findByEvento($idEvento);
    }

    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        return $this->consolidacaoService->countByProdutoEvento($idProdutoEvento);
    }

    public function gerarContasPagar(int $idEvento): array
    {
        return $this->consolidacaoService->gerarContasPagar($idEvento);
    }

    public function pagarFornecedor(int $idEvento, int $idFornecedor, array $data = [], ?array $file = null): array
    {
        return $this->consolidacaoService->pagarFornecedor($idEvento, $idFornecedor, $data, $file);
    }

    public function enviarPagamentoIndividual(int $idSublocacao, array $data = [], ?array $file = null): array
    {
        return $this->consolidacaoService->enviarPagamentoIndividual($idSublocacao, $data, $file);
    }
}

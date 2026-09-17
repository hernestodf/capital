<?php

namespace App\Service;

use App\Database\Connection;
use App\Repository\ProdutoEventoSerialRepository;
use App\Repository\ProdutoEventoRepository;
use App\Repository\SerialProdutoRepository;

class ProdutoEventoSerialService
{
    private ProdutoEventoSerialRepository $repo;
    private ProdutoEventoRepository $produtoEventoRepo;
    private SerialProdutoRepository $serialRepo;

    public function __construct()
    {
        $this->repo = new ProdutoEventoSerialRepository();
        $this->produtoEventoRepo = new ProdutoEventoRepository();
        $this->serialRepo = new SerialProdutoRepository();
    }

    public function alocarSerial(int $idProdutoEvento, int $idSerial): int
    {
        $produtoEvento = $this->produtoEventoRepo->find($idProdutoEvento);
        if (!$produtoEvento) {
            throw new \InvalidArgumentException('Item do evento não encontrado');
        }

        $serial = $this->serialRepo->find($idSerial);
        if (!$serial) {
            throw new \InvalidArgumentException('Serial não encontrado');
        }

        if ($serial['status'] !== 'ATIVO') {
            throw new \InvalidArgumentException('Serial não está disponível (status: ' . $serial['status'] . ')');
        }

        $jaAlocado = $this->repo->findSerialAlocadoEmOutroEvento($idSerial, $idProdutoEvento);
        if ($jaAlocado) {
            throw new \InvalidArgumentException('Este serial já está alocado em outro evento');
        }

        return Connection::transaction(function () use ($idProdutoEvento, $idSerial) {
            $id = $this->repo->create([
                'id_produto_evento' => $idProdutoEvento,
                'id_serial' => $idSerial,
                'status' => 'alocado',
            ]);

            $qtdAlocada = $this->repo->sumQtdAlocadaByProdutoEvento($idProdutoEvento);
            $this->produtoEventoRepo->update($idProdutoEvento, ['qtd_alocada' => $qtdAlocada]);

            return $id;
        });
    }

    public function desalocarSerial(int $id): bool
    {
        $alocacao = $this->repo->find($id);
        if (!$alocacao) {
            throw new \InvalidArgumentException('Alocação não encontrada');
        }

        Connection::transaction(function () use ($alocacao) {
            $this->repo->delete($alocacao['id']);

            $qtdAlocada = $this->repo->sumQtdAlocadaByProdutoEvento($alocacao['id_produto_evento']);
            $this->produtoEventoRepo->update($alocacao['id_produto_evento'], ['qtd_alocada' => $qtdAlocada]);
        });

        return true;
    }

    public function alocarEmLote(int $idProdutoEvento, array $idsSerials): array
    {
        $results = ['success' => [], 'errors' => []];
        foreach ($idsSerials as $idSerial) {
            try {
                $id = $this->alocarSerial($idProdutoEvento, (int)$idSerial);
                $results['success'][] = $id;
            } catch (\Throwable $e) {
                $results['errors'][] = $e->getMessage();
            }
        }
        return $results;
    }

    public function sugerirSeriais(int $idProdutoEvento): array
    {
        $produtoEvento = $this->produtoEventoRepo->find($idProdutoEvento);
        if (!$produtoEvento) {
            throw new \InvalidArgumentException('Item do evento não encontrado');
        }

        $serialData = $this->serialRepo->findByNumero('');
        $idProduto = null;

        if (!empty($produtoEvento['id_planilha'])) {
            $planilha = $this->produtoEventoRepo->findPlanilhaById((int)$produtoEvento['id_planilha']);
        }

        $produtoNome = $produtoEvento['produto'] ?? '';
        $sql = "SELECT p.id FROM produtos p WHERE p.produto = ? AND p.pode_ser_locado = 'S' LIMIT 1";
        $produtoResult = \App\Database\Connection::query($sql, [$produtoNome]);
        $idProduto = $produtoResult[0]['id'] ?? null;

        if (!$idProduto) {
            return [];
        }

        return $this->repo->findSeriaisDisponiveis($idProdutoEvento, $idProduto);
    }

    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        return $this->repo->findByProdutoEvento($idProdutoEvento);
    }

    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        return $this->repo->countByProdutoEvento($idProdutoEvento);
    }
}

<?php

namespace App\Service;

use App\Repository\ProdutoRepository;

class ProdutoService extends BaseService
{
    protected string $entityName = 'Produto';

    public function __construct()
    {
        parent::__construct(new ProdutoRepository());
    }

    public function create(array $data): int
    {
        $data = $this->sanitizeData($data);
        $this->validate($data);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->sanitizeData($data);
        $this->validate($data, $id);
        return $this->repository->update($id, $data);
    }

    private function validate(array $data, ?int $id = null): void
    {
        if (empty($data['produto'])) {
            throw new \InvalidArgumentException('Nome do produto e obrigatorio');
        }

        if (!empty($data['custo']) && !is_numeric($data['custo'])) {
            throw new \InvalidArgumentException('Custo deve ser um valor numerico');
        }

        if (!empty($data['id_secao']) && !is_numeric($data['id_secao'])) {
            throw new \InvalidArgumentException('Secao deve ser valida');
        }

        if (!empty($data['pode_ser_locado']) && !in_array($data['pode_ser_locado'], ['S', 'N'])) {
            throw new \InvalidArgumentException('Pode ser locado deve ser S ou N');
        }
    }

    public function sanitizeData(array $data): array
    {
        $data['produto'] = trim(strip_tags($data['produto'] ?? ''));
        $data['observacao'] = trim($data['observacao'] ?? '');
        $data['pode_ser_locado'] = $data['pode_ser_locado'] ?? 'N';
        
        if (!empty($data['custo'])) {
            $custo = $data['custo'];
            $custo = str_replace('.', '', $custo);  // Remove pontos de milhar
            $custo = str_replace(',', '.', $custo); // Converte vírgula decimal para ponto
            $data['custo'] = floatval($custo);
        } else {
            $data['custo'] = 0;
        }

        if (!empty($data['id_secao'])) {
            $data['id_secao'] = intval($data['id_secao']);
        } else {
            $data['id_secao'] = null;
        }

        return $data;
    }

    public function toggleStatus(int $id): int
    {
        $entity = $this->findOrFail($id);
        $newStatus = $entity['status'] == 1 ? 0 : 1;
        $this->repository->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    public function toggleLocado(int $id): string
    {
        $entity = $this->findOrFail($id);
        $newValue = ($entity['pode_ser_locado'] ?? 'N') === 'S' ? 'N' : 'S';
        $this->repository->update($id, ['pode_ser_locado' => $newValue]);
        return $newValue;
    }

    public function countSeriaisByProduto(int $idProduto): array
    {
        return $this->repository->countSeriaisByProduto($idProduto);
    }

    public function findByProduto(int $idProduto): array
    {
        return $this->repository->findByProduto($idProduto);
    }

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repository->search($search, $page, $perPage);
    }

    public function delete(int $id): int
    {
        return $this->repository->delete($id);
    }
}

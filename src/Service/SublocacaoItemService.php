<?php

namespace App\Service;

use App\Repository\SublocacaoItemRepository;

class SublocacaoItemService extends BaseService
{
    private SublocacaoItemRepository $repo;

    public function __construct()
    {
        $this->repo = new SublocacaoItemRepository();
        parent::__construct($this->repo);
    }

    public function findByFornecedor(int $idFornecedor): array
    {
        return $this->repo->findByFornecedor($idFornecedor);
    }

    public function countByFornecedor(int $idFornecedor): int
    {
        return $this->repo->countByFornecedor($idFornecedor);
    }

    public function create(array $data): int
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }
}

<?php

namespace App\Service;

use App\Repository\UnidadeMedidaRepository;

class UnidadeMedidaService extends BaseService
{
    protected string $entityName = 'UnidadeMedida';

    public function __construct()
    {
        parent::__construct(new UnidadeMedidaRepository());
    }

    public function create(array $data): int
    {
        $this->validateRequired($data, ['unidademedida']);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $this->validateRequired($data, ['unidademedida']);
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id);
        $this->repository->delete($id);
    }
}

<?php

namespace App\Service;

use App\Repository\PlanilhaRepository;

class PlanilhaService extends BaseService
{
    protected string $entityName = 'Planilha';

    public function __construct()
    {
        parent::__construct(new PlanilhaRepository());
    }

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repository->search($search, $page, $perPage);
    }

    public function create(array $data): int
    {
        $this->validateRequired($data, ['item']);

        // Sanitizar valor
        if (isset($data['valor'])) {
            $data['valor'] = str_replace(',', '.', $data['valor']);
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);

        // Sanitizar valor
        if (isset($data['valor'])) {
            $data['valor'] = str_replace(',', '.', $data['valor']);
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id);
        $this->repository->delete($id);
    }
}

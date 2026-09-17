<?php

namespace App\Service;

use App\Repository\CategoriaRepository;

class CategoriaService extends BaseService
{
    private CategoriaRepository $repo;

    public function __construct()
    {
        $this->repo = new CategoriaRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        return $this->repo->create(parent::sanitize($data));
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        return $this->repo->update($id, parent::sanitize($data));
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }

    /**
     * Alterna status da categoria
     */
    public function toggleStatus(int $id): int
    {
        $categoria = $this->findOrFail($id);
        $newStatus = $categoria['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca categorias com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Busca todas as categorias ativas
     */
    public function getAtivas(): array
    {
        return $this->repo->getAtivas();
    }

    private function validate(array $data): void
    {
        if (empty($data['categoria'])) {
            throw new \InvalidArgumentException('Nome da categoria é obrigatório');
        }
    }
}

<?php

namespace App\Service;

use App\Repository\CategoriaSalaRepository;

class CategoriaSalaService extends BaseService
{
    private CategoriaSalaRepository $repo;

    public function __construct()
    {
        $this->repo = new CategoriaSalaRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
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

    public function toggleStatus(int $id): int
    {
        $entity = $this->findOrFail($id);
        $newStatus = $entity['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca categorias ativas ordenadas
     */
    public function getAtivas(): array
    {
        return $this->repo->getAtivas();
    }

    /**
     * Busca todas ordenadas
     */
    public function getAllOrdenadas(): array
    {
        return $this->repo->getAllOrdenadas();
    }

    /**
     * Conta categorias ativas
     */
    public function countAtivas(): int
    {
        return $this->repo->countAtivas();
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['nome_categoria'])) {
            $errors[] = 'Nome da categoria e obrigatorio';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }
}

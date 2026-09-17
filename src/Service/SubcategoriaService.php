<?php

namespace App\Service;

use App\Repository\SubcategoriaRepository;

class SubcategoriaService extends BaseService
{
    private SubcategoriaRepository $repo;

    public function __construct()
    {
        $this->repo = new SubcategoriaRepository();
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
     * Alterna status da subcategoria
     */
    public function toggleStatus(int $id): int
    {
        $subcategoria = $this->findOrFail($id);
        $newStatus = $subcategoria['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca subcategorias com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Busca subcategorias por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->repo->findByCategoria($idCategoria);
    }

    private function validate(array $data): void
    {
        if (empty($data['subcategoria'])) {
            throw new \InvalidArgumentException('Nome da subcategoria é obrigatório');
        }
        if (empty($data['id_categoria'])) {
            throw new \InvalidArgumentException('Categoria é obrigatória');
        }
    }
}

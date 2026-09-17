<?php

namespace App\Service;

use App\Repository\DemandanteRepository;

class DemandanteService extends BaseService
{
    private DemandanteRepository $repo;

    public function __construct()
    {
        $this->repo = new DemandanteRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        return $this->repo->create(parent::sanitize($data));
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $data = $this->sanitizeData($data);
        return $this->repo->update($id, parent::sanitize($data));
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }

    /**
     * Alterna status do demandante (ativo/inativo)
     */
    public function toggleStatus(int $id): int
    {
        $demandante = $this->findOrFail($id);
        $newStatus = $demandante['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca demandantes com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Demandantes ativos, pra uso em selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->repo->allAtivos();
    }

    /**
     * Todos os demandantes com o nome do cliente vinculado (para a listagem)
     */
    public function allComCliente(): array
    {
        return $this->repo->allComCliente();
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        if (!empty($data['telefone'])) {
            $data['telefone'] = preg_replace('/\D/', '', $data['telefone']);
        }
        return $data;
    }
}

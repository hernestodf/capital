<?php

namespace App\Service;

use App\Repository\SalaRepository;

class SalaService extends BaseService
{
    private SalaRepository $repo;

    public function __construct()
    {
        $this->repo = new SalaRepository();
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
     * Busca salas de um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->repo->findByEvento($idEvento);
    }

    /**
     * Busca uma sala especifica de um evento
     */
    public function findByEventoAndId(int $idEvento, int $idSala): ?array
    {
        return $this->repo->findByEventoAndId($idEvento, $idSala);
    }

    /**
     * Conta salas por evento
     */
    public function countByEvento(int $idEvento): int
    {
        return $this->repo->countByEvento($idEvento);
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['id_evento'])) {
            $errors[] = 'Evento e obrigatorio';
        }
        if (empty($data['nome_sala'])) {
            $errors[] = 'Nome da sala e obrigatorio';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }
}

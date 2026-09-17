<?php

namespace App\Service;

use App\Repository\FuncaoRepository;

class FuncaoService extends BaseService
{
    private FuncaoRepository $repo;

    public function __construct()
    {
        $this->repo = new FuncaoRepository();
        parent::__construct($this->repo);
    }

    public function getAtivas(): array
    {
        return $this->repo->getAtivas();
    }

    public function create(array $data): int
    {
        $nome = trim($data['nome'] ?? '');
        if ($nome === '') {
            throw new \InvalidArgumentException('Nome da função é obrigatório');
        }
        return $this->repo->create(['nome' => $nome, 'status' => 1]);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $nome = trim($data['nome'] ?? '');
        if ($nome === '') {
            throw new \InvalidArgumentException('Nome da função é obrigatório');
        }
        return $this->repo->update($id, ['nome' => $nome]);
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }
}

<?php

namespace App\Service;

use App\Repository\SecaoRepository;

class SecaoService extends BaseService
{
    protected string $entityName = 'Secao';

    public function __construct()
    {
        parent::__construct(new SecaoRepository());
    }

    public function create(array $data): int
    {
        $this->validate($data);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $this->validate($data, $id);
        return $this->repository->update($id, $data);
    }

    private function validate(array $data, ?int $id = null): void
    {
        if (empty($data['secao'])) {
            throw new \InvalidArgumentException('Nome da secao e obrigatorio');
        }

        if (strlen($data['secao']) > 100) {
            throw new \InvalidArgumentException('Nome da secao deve ter no maximo 100 caracteres');
        }
    }

    public function sanitizeData(array $data): array
    {
        $data['secao'] = trim(strip_tags($data['secao'] ?? ''));
        return $data;
    }

    public function getAtivas(): array
    {
        return $this->repository->getAtivas();
    }

    public function delete(int $id): int
    {
        return $this->repository->delete($id);
    }
}

<?php

namespace App\Service;

use App\Repository\ClienteRepository;

class ClienteService extends BaseService
{
    private ClienteRepository $repo;

    public function __construct()
    {
        $this->repo = new ClienteRepository();
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
     * Alterna status do cliente (ativo/inativo)
     */
    public function toggleStatus(int $id): int
    {
        $cliente = $this->findOrFail($id);
        $newStatus = $cliente['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca clientes com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Clientes ativos, pra uso em selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->repo->allAtivos();
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['nome_fantasia'])) {
            $errors[] = 'Nome fantasia é obrigatório';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!empty($data['cpf_cnpj']) && strlen(preg_replace('/\D/', '', $data['cpf_cnpj'])) < 11) {
            $errors[] = 'CPF/CNPJ inválido';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        if (!empty($data['cpf_cnpj'])) {
            $data['cpf_cnpj'] = preg_replace('/\D/', '', $data['cpf_cnpj']);
        }
        if (!empty($data['cep'])) {
            $data['cep'] = preg_replace('/\D/', '', $data['cep']);
        }
        if (!empty($data['telefone'])) {
            $data['telefone'] = preg_replace('/\D/', '', $data['telefone']);
        }
        return $data;
    }
}
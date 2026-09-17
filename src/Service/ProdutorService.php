<?php

namespace App\Service;

use App\Repository\ProdutorRepository;

class ProdutorService extends BaseService
{
    private ProdutorRepository $repo;

    public function __construct()
    {
        $this->repo = new ProdutorRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        if (empty($data['assinatura'])) {
            $data['assinatura'] = $this->generateSignature(
                $data['nome'],
                $data['telefone'] ?? '',
                $data['email'] ?? ''
            );
        }
        return $this->repo->create(parent::sanitize($data));
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $data = $this->sanitizeData($data);
        if (empty($data['assinatura'])) {
            $data['assinatura'] = $this->generateSignature(
                $data['nome'],
                $data['telefone'] ?? '',
                $data['email'] ?? ''
            );
        }
        return $this->repo->update($id, parent::sanitize($data));
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }

    /**
     * Alterna status do produtor (ativo/inativo)
     */
    public function toggleStatus(int $id): int
    {
        $produtor = $this->findOrFail($id);
        $newStatus = $produtor['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca produtores com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Produtores ativos, pra uso em selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->repo->allAtivos();
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

    public function generateSignature(string $nome, string $telefone, string $email): string
    {
        $lines = [];
        $lines[] = trim($nome);
        $lines[] = 'Produtor(a)';
        
        $cleanPhone = preg_replace('/\D/', '', $telefone);
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 10) {
            $lines[] = '(' . substr($cleanPhone, 0, 2) . ')' . substr($cleanPhone, 2);
        } else {
            $lines[] = trim($telefone);
        }
        
        $lines[] = trim($email);
        
        return implode("\n", $lines);
    }
}

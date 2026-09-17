<?php

namespace App\Service;

use App\Repository\UserRepository;

class UsuarioService extends BaseService
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        
        // Verificar se email já existe
        $existing = $this->repo->findByEmail($data['email']);
        if ($existing) {
            throw new \Exception('E-mail já está em uso');
        }

        // Hash da senha
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $data = $this->sanitizeData($data);
        return $this->repo->create(parent::sanitize($data));
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        
        // Verificar se email já existe (exceto o próprio usuário)
        $existing = $this->repo->findByEmail($data['email']);
        if ($existing && (int)$existing['id'] !== $id) {
            throw new \Exception('E-mail já está em uso');
        }

        // Hash da senha se fornecida
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        $data = $this->sanitizeData($data);
        return $this->repo->update($id, parent::sanitize($data));
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }

    /**
     * Alterna status do usuário (ativo/inativo)
     */
    public function toggleStatus(int $id): int
    {
        $usuario = $this->findOrFail($id);
        $newStatus = $usuario['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca usuários com termo de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }

        if (empty($data['email'])) {
            $errors[] = 'E-mail é obrigatório';
        }

        if (empty($data['password']) && $id === null) {
            $errors[] = 'Senha é obrigatória';
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
        if (!empty($data['celular'])) {
            $data['celular'] = preg_replace('/\D/', '', $data['celular']);
        }
        return $data;
    }
}

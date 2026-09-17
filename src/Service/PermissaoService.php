<?php

namespace App\Service;

use App\Repository\PermissaoRepository;
use App\Database\Connection;

class PermissaoService extends BaseService
{
    private PermissaoRepository $repo;

    public function __construct()
    {
        $this->repo = new PermissaoRepository();
        parent::__construct($this->repo);
    }

    public function getAllWithModulo(): array
    {
        return $this->repo->getAllWithModulo();
    }

    public function findByRole(string $role): array
    {
        return $this->repo->findByRole($role);
    }

    public function getMatrix(): array
    {
        $roles = ['administrativo', 'comercial', 'estoquista'];
        $modulos = $this->getAllWithModulo();
        
        $matrix = [];
        foreach ($modulos as $permissao) {
            $moduloNome = $permissao['modulo_nome'];
            if (!isset($matrix[$moduloNome])) {
                $matrix[$moduloNome] = [
                    'modulo_titulo' => $permissao['modulo_titulo'],
                    'permissoes' => []
                ];
            }
            
            $matrix[$moduloNome]['permissoes'][] = [
                'id' => $permissao['id'],
                'nome' => $permissao['nome'],
                'titulo' => $permissao['titulo'],
                'roles' => $this->getRolesForPermissao($permissao['id'])
            ];
        }
        
        return $matrix;
    }

    public function getRolesForPermissao(int $permissaoId): array
    {
        $results = Connection::query(
            "SELECT role FROM role_permissoes WHERE permissao_id = ?",
            [$permissaoId]
        );
        return array_column($results, 'role');
    }

    public function updateRoles(int $permissaoId, array $roles): int
    {
        // Remove todas as relações existentes
        Connection::exec(
            "DELETE FROM role_permissoes WHERE permissao_id = ?",
            [$permissaoId]
        );
        
        // Insere as novas relações
        if (empty($roles)) {
            return 0;
        }
        
        $validRoles = ['administrativo', 'comercial', 'estoquista'];
        $roles = array_values(array_intersect($roles, $validRoles));

        if (empty($roles)) {
            return 0;
        }

        $values = [];
        $params = [];
        foreach ($roles as $role) {
            $values[] = '(?, ?)';
            $params[] = $role;
            $params[] = $permissaoId;
        }
        
        $sql = "INSERT INTO role_permissoes (role, permissao_id) VALUES " . implode(', ', $values);
        return Connection::exec($sql, $params);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->validate($data, $id);
        return $this->repo->update($id, $data);
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors[] = 'Nome da permissão é obrigatório';
        }
        
        if (empty($data['titulo'])) {
            $errors[] = 'Título da permissão é obrigatório';
        }
        
        if (empty($data['modulo_id'])) {
            $errors[] = 'Módulo é obrigatório';
        }
        
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }
}

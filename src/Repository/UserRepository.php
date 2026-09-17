<?php

namespace App\Repository;

use App\Database\Connection;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'email', 'password', 'telefone', 'celular', 'cep', 'role', 'status'
    ];

    /**
     * Busca usuários com filtro de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if (!empty($term)) {
            $where = "WHERE name LIKE ? OR email LIKE ? OR telefone LIKE ? OR celular LIKE ?";
            $searchTerm = "%{$term}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql = "SELECT id, name, email, telefone, celular, cep, role, status, created_at, updated_at FROM {$this->table} $where ORDER BY name LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $where";
        $totalResult = $this->query($countSql, $params);
        
        return [
            'data' => $results,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalResult[0]['total'] ?? 0,
                'last_page' => ceil(($totalResult[0]['total'] ?? 1) / $perPage),
            ]
        ];
    }

    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $result = $this->query($sql, [$email]);
        return $result[0] ?? null;
    }

    public function allAtivos(): array
    {
        return $this->query(
            "SELECT id, name FROM {$this->table} WHERE status = 1 ORDER BY name"
        );
    }

    /**
     * Retorna usuários recentes
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->query(
            "SELECT id, name, email, role, status, created_at FROM {$this->table} ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
}

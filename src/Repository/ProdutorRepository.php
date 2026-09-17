<?php

namespace App\Repository;

use App\Database\Connection;

class ProdutorRepository extends BaseRepository
{
    protected string $table = 'produtores';
    protected array $fillable = [
        'id_users', 'nome', 'email', 'telefone', 'status'
    ];

    /**
     * Produtores ativos, pra uso em listagens/selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nome");
    }

    public function findByUserId(int $userId): ?array
    {
        $result = $this->query("SELECT * FROM {$this->table} WHERE id_users = ? LIMIT 1", [$userId]);
        return $result[0] ?? null;
    }

    public function linkUser(int $produtorId, int $userId): void
    {
        Connection::exec("UPDATE {$this->table} SET id_users = NULL WHERE id_users = ?", [$userId]);
        Connection::exec("UPDATE {$this->table} SET id_users = ? WHERE id = ?", [$userId, $produtorId]);
    }

    public function unlinkUser(int $userId): void
    {
        Connection::exec("UPDATE {$this->table} SET id_users = NULL WHERE id_users = ?", [$userId]);
    }

    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if (!empty($term)) {
            $where = "WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ?";
            $searchTerm = "%{$term}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        $sql = "SELECT p.*, u.name as user_name FROM {$this->table} p LEFT JOIN users u ON p.id_users = u.id $where ORDER BY p.nome LIMIT :limit OFFSET :offset";
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
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $totalResult[0]['total'] ?? 0,
                'last_page' => ceil(($totalResult[0]['total'] ?? 1) / $perPage),
            ]
        ];
    }
}

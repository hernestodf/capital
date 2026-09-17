<?php

namespace App\Repository;

use App\Database\Connection;

class CategoriaRepository extends BaseRepository
{
    protected string $table = 'categorias';
    protected array $fillable = ['categoria'];

    /**
     * Busca todas as categorias ativas
     */
    public function getAtivas(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY categoria");
    }

    /**
     * Busca categorias com filtro de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if (!empty($term)) {
            $where = "WHERE categoria LIKE ?";
            $params = ["%{$term}%"];
        }

        $sql = "SELECT * FROM {$this->table} $where ORDER BY categoria LIMIT :limit OFFSET :offset";
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
}

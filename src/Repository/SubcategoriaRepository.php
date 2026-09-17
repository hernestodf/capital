<?php

namespace App\Repository;

use App\Database\Connection;

class SubcategoriaRepository extends BaseRepository
{
    protected string $table = 'subcategorias';
    protected array $fillable = ['id_categoria', 'subcategoria'];

    /**
     * Busca subcategorias por categoria
     */
    public function findByCategoria(int $idCategoria): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE id_categoria = ? AND status = 1 ORDER BY subcategoria",
            [$idCategoria]
        );
    }

    /**
     * Busca subcategorias com filtro de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if (!empty($term)) {
            $where = "WHERE s.subcategoria LIKE ?";
            $params = ["%{$term}%"];
        }

        $sql = "SELECT s.*, c.categoria as categoria_nome 
                FROM {$this->table} s 
                LEFT JOIN categorias c ON s.id_categoria = c.id 
                $where 
                ORDER BY s.subcategoria 
                LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} s $where";
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

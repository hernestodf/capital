<?php

namespace App\Repository;

use App\Database\Connection;

class PlanilhaRepository extends BaseRepository
{
    protected string $table = 'planilhas';
    protected array $fillable = ['item', 'descricao', 'id_unidademedida', 'valor', 'potencia_w', 'horas_uso'];

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where = "WHERE item LIKE ? OR descricao LIKE ?";
            $params = ["%{$search}%", "%{$search}%"];
        }

        $offset = ($page - 1) * $perPage;

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $stmt = Connection::get()->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // Get page data with JOIN
        $sql = "SELECT p.*, u.unidademedida 
                FROM {$this->table} p 
                LEFT JOIN unidademedida u ON p.id_unidademedida = u.id 
                {$where} 
                ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'pagination' => [
                'total' => (int) $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage),
            ],
        ];
    }

    public function all(): array
    {
        return Connection::query(
            "SELECT p.*, u.unidademedida 
             FROM {$this->table} p 
             LEFT JOIN unidademedida u ON p.id_unidademedida = u.id 
             ORDER BY p.id ASC"
        );
    }
}

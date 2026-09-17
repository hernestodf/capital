<?php

namespace App\Repository;

use App\Database\Connection;

class DemandanteRepository extends BaseRepository
{
    protected string $table = 'demandantes';
    protected array $fillable = [
        'id_cliente', 'nome', 'telefone', 'email', 'observacao', 'status'
    ];

    /**
     * Demandantes ativos, pra uso em listagens/selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nome");
    }

    /**
     * Todos os demandantes com o nome do cliente vinculado (para a listagem)
     */
    public function allComCliente(): array
    {
        return $this->query(
            "SELECT d.*, c.nome_fantasia as cliente_nome
             FROM {$this->table} d
             LEFT JOIN clientes c ON d.id_cliente = c.id
             ORDER BY d.nome"
        );
    }

    /**
     * Busca demandantes com filtro de busca e relaciona com cliente
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if (!empty($term)) {
            $where = "WHERE d.nome LIKE ? OR d.email LIKE ? OR c.nome_fantasia LIKE ?";
            $searchTerm = "%{$term}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql = "SELECT d.*, c.nome_fantasia as cliente_nome 
                FROM {$this->table} d 
                LEFT JOIN clientes c ON d.id_cliente = c.id 
                $where 
                ORDER BY d.nome 
                LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} d 
                     LEFT JOIN clientes c ON d.id_cliente = c.id 
                     $where";
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

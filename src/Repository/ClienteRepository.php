<?php

namespace App\Repository;

use App\Database\Connection;

class ClienteRepository extends BaseRepository
{
    protected string $table = 'clientes';
    protected array $fillable = [
        'cpf_cnpj', 'nome_fantasia', 'razao_social', 'contato',
        'email', 'telefone', 'cep', 'endereco', 'numero',
        'complemento', 'bairro', 'cidade', 'estado', 'observacao', 'status'
    ];

    /**
     * Clientes ativos, pra uso em listagens/selects (ex: form de evento)
     */
    public function allAtivos(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nome_fantasia");
    }

    /**
     * Busca clientes com filtro de busca
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if (!empty($term)) {
            $where = "WHERE nome_fantasia LIKE ? OR razao_social LIKE ? OR cpf_cnpj LIKE ? OR email LIKE ?";
            $searchTerm = "%{$term}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql = "SELECT * FROM {$this->table} $where ORDER BY nome_fantasia LIMIT :limit OFFSET :offset";
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
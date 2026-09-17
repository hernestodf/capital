<?php

namespace App\Repository;

use App\Database\Connection;

class EventoRepository extends BaseRepository
{
    protected string $table = 'eventos';
    protected array $fillable = [
        'id_cliente', 'id_produtor', 'id_demandante', 'id_usuario_separacao', 'nome_evento', 'local_evento',
        'os_cliente', 'demandante_local', 'telefone_demandantelocal', 'observacao',
        'data_montagem', 'hora_montagem', 'data_inicio', 'hora_inicio',
        'data_fim', 'hora_fim', 'data_desmontagem', 'hora_desmontagem',
        'estado', 'status_locacao', 'evento_montado', 'evento_desmontado',
        'observacoes_fechamento', 'status'
    ];

    /**
     * Busca eventos com filtros e relacionamentos
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por estado
        if (!empty($filters['estado'])) {
            $where[] = "e.estado = ?";
            $params[] = $filters['estado'];
        }
        
        // Filtro por status_locacao
        if (!empty($filters['status_locacao'])) {
            $where[] = "e.status_locacao = ?";
            $params[] = $filters['status_locacao'];
        }
        
        // Filtro por pendencia
        if (isset($filters['pendencia']) && $filters['pendencia'] !== '') {
            if ($filters['pendencia'] === '1') {
                $where[] = "e.tem_pendencia = 1";
            } elseif ($filters['pendencia'] === '0') {
                $where[] = "(e.tem_pendencia = 0 OR e.tem_pendencia IS NULL)";
            }
        }
        
        // Busca por texto
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $where[] = "(e.nome_evento LIKE ? OR c.nome_fantasia LIKE ? OR e.local_evento LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT e.*,
                       c.nome_fantasia as cliente_nome,
                       p.nome as produtor_nome,
                       d.nome as demandante_nome,
                       u.name as usuario_separacao_nome
                FROM {$this->table} e
                LEFT JOIN clientes c ON e.id_cliente = c.id
                LEFT JOIN produtores p ON e.id_produtor = p.id
                LEFT JOIN demandantes d ON e.id_demandante = d.id
                LEFT JOIN users u ON e.id_usuario_separacao = u.id
                WHERE {$whereSql}
                ORDER BY e.data_inicio DESC, e.id DESC
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
                     FROM {$this->table} e
                     LEFT JOIN clientes c ON e.id_cliente = c.id
                     LEFT JOIN produtores p ON e.id_produtor = p.id
                     LEFT JOIN demandantes d ON e.id_demandante = d.id
                     LEFT JOIN users u ON e.id_usuario_separacao = u.id
                     WHERE {$whereSql}";
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
     * Busca todos os eventos de um produtor específico (sem paginação)
     */
    public function allByProdutor(int $idProdutor): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT * FROM {$this->table} WHERE id_produtor = ? AND status = 1 ORDER BY data_inicio DESC LIMIT :lim"
        );
        $stmt->bindValue(1, $idProdutor, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $this->defaultLimit ?? 2000, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Conta eventos por estado e status
     */
    public function countByFilters(array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['id_produtor'])) {
            $where .= " AND id_produtor = ?";
            $params[] = (int) $filters['id_produtor'];
        }
        if (!empty($filters['estado'])) {
            $where .= " AND estado = ?";
            $params[] = $filters['estado'];
        }
        if (!empty($filters['status_locacao'])) {
            $where .= " AND status_locacao = ?";
            $params[] = $filters['status_locacao'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'O' THEN 1 ELSE 0 END) as total_orcamentos,
                    SUM(CASE WHEN estado = 'L' AND status_locacao = 'A' THEN 1 ELSE 0 END) as total_andamento,
                    SUM(CASE WHEN estado = 'L' AND status_locacao = 'F' THEN 1 ELSE 0 END) as total_finalizadas,
                    SUM(CASE WHEN estado = 'L' AND status_locacao = 'T' THEN 1 ELSE 0 END) as total_faturadas
                FROM {$this->table}
                WHERE {$where} AND status = 1";
        
        $result = $this->query($sql, $params);
        return $result[0] ?? ['total' => 0, 'total_orcamentos' => 0, 'total_andamento' => 0, 'total_finalizadas' => 0, 'total_faturadas' => 0];
    }

    /**
     * Conta eventos por intervalo de datas
     */
    public function countByDateRange(string $startDate, string $endDate): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE DATE(data_inicio) BETWEEN ? AND ?
                AND status = 1";
        $result = $this->query($sql, [$startDate, $endDate]);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Conta colaboradores alocados em eventos de uma data específica
     */
    public function countColaboradoresAlocadosHoje(string $date): int
    {
        $sql = "SELECT COUNT(DISTINCT ec.id_colaborador) as total
                FROM evento_colaboradores ec
                INNER JOIN eventos e ON ec.id_evento = e.id
                WHERE DATE(e.data_inicio) = ?
                AND ec.status = 'A'";
        $result = Connection::get()->prepare($sql);
        $result->execute([$date]);
        return (int) ($result->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    /**
     * Busca próximos eventos
     */
    public function getProximos(int $limit = 5): array
    {
        $sql = "SELECT id, nome_evento as nome, data_inicio, data_fim, status
                FROM {$this->table}
                WHERE data_inicio >= CURDATE()
                AND status = 1
                ORDER BY data_inicio ASC
                LIMIT ?";
        return $this->query($sql, [$limit]);
    }
}

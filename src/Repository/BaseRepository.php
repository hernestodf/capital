<?php

namespace App\Repository;

use App\Database\Connection;
use PDO;

abstract class BaseRepository
{
    protected string $table;
    protected array $fillable = [];
    protected ?int $defaultLimit = 2000;

    private const ALLOWED_FIELD_CHARS = '/^[a-zA-Z0-9_]+$/';

    public function all(): array
    {
        if ($this->defaultLimit !== null && $this->defaultLimit > 0) {
            $stmt = Connection::get()->prepare("SELECT * FROM {$this->table} LIMIT :limit");
            $stmt->bindValue(':limit', $this->defaultLimit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return Connection::query("SELECT * FROM {$this->table}");
    }

    public function find(int $id): ?array
    {
        $results = Connection::query(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        return $results[0] ?? null;
    }

    public function findBy(string $field, $value): ?array
    {
        if (!preg_match(self::ALLOWED_FIELD_CHARS, $field)) {
            throw new \InvalidArgumentException("Campo invalido: {$field}");
        }
        $results = Connection::query(
            "SELECT * FROM {$this->table} WHERE {$field} = ?",
            [$value]
        );
        return $results[0] ?? null;
    }

    public function create(array $data): int
    {
        $data = $this->fill($data);
        
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        
        Connection::exec(
            "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)",
            array_values($data)
        );
        
        return (int) Connection::lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $data = $this->fill($data);

        if (empty($data)) {
            return 0;
        }

        $sets = implode(' = ?, ', array_keys($data)) . ' = ?';

        return Connection::exec(
            "UPDATE {$this->table} SET $sets WHERE id = ?",
            array_merge(array_values($data), [$id])
        );
    }

    public function delete(int $id): int
    {
        return Connection::exec(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = Connection::get()->prepare(
            "SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = Connection::get()->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $results,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total ?? 0,
                'last_page' => ceil(($total ?? 1) / $perPage),
            ]
        ];
    }

    /**
     * Conta registros na tabela
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        
        $result = Connection::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Executa query SQL
     */
    protected function query(string $sql, array $params = []): array
    {
        return Connection::query($sql, $params);
    }

    protected function fill(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function __call(string $method, array $args)
    {
        if (str_starts_with($method, 'findBy')) {
            $field = lcfirst(substr($method, 6));
            return $this->findBy($field, $args[0]);
        }
        
        throw new \BadMethodCallException("Método $method não existe");
    }
}

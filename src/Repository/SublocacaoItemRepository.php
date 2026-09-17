<?php

namespace App\Repository;

class SublocacaoItemRepository extends BaseRepository
{
    protected string $table = 'sublocacao_itens';
    protected array $fillable = [
        'id_fornecedor', 'produto', 'codigo', 'quantidade', 'valor_unit', 'observacao', 'status'
    ];

    public function findByFornecedor(int $idFornecedor): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE id_fornecedor = ? ORDER BY produto ASC",
            [$idFornecedor]
        );
    }

    public function countByFornecedor(int $idFornecedor): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE id_fornecedor = ? AND status = 1",
            [$idFornecedor]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    public function getAtivos(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY produto ASC"
        );
    }
}

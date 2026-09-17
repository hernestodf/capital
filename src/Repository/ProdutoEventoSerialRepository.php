<?php

namespace App\Repository;

use App\Database\Connection;

class ProdutoEventoSerialRepository extends BaseRepository
{
    protected string $table = 'produto_evento_seriais';
    protected array $fillable = [
        'id_produto_evento', 'id_serial', 'status'
    ];

    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        $sql = "SELECT pes.*, s.serial as serial_nome, s.status as serial_status,
                       p.produto as produto_nome
                FROM {$this->table} pes
                INNER JOIN seriaisproduto s ON pes.id_serial = s.id
                LEFT JOIN produtos p ON s.id_produto = p.id
                WHERE pes.id_produto_evento = ?
                ORDER BY pes.created_at DESC";
        return $this->query($sql, [$idProdutoEvento]);
    }

    public function findByEvento(int $idEvento): array
    {
        $sql = "SELECT pes.*, pe.produto as produto_evento_nome,
                       s.serial as serial_nome, s.status as serial_status,
                       p.produto as produto_nome
                FROM {$this->table} pes
                INNER JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                INNER JOIN seriaisproduto s ON pes.id_serial = s.id
                LEFT JOIN produtos p ON s.id_produto = p.id
                WHERE pe.id_evento = ?
                ORDER BY pe.produto ASC, pes.created_at DESC";
        return $this->query($sql, [$idEvento]);
    }

    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE id_produto_evento = ?",
            [$idProdutoEvento]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    public function countByEvento(int $idEvento): array
    {
        $sql = "SELECT 
                    pes.status,
                    COUNT(*) as total
                FROM {$this->table} pes
                INNER JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                WHERE pe.id_evento = ?
                GROUP BY pes.status";
        $results = $this->query($sql, [$idEvento]);
        $grouped = ['alocado' => 0, 'entregue' => 0, 'devolvido' => 0];
        foreach ($results as $row) {
            $grouped[$row['status']] = (int) $row['total'];
        }
        return $grouped;
    }

    public function findSerialAlocadoEmOutroEvento(int $idSerial, int $idProdutoEvento): ?array
    {
        $sql = "SELECT pes.*, pe.id_evento
                FROM {$this->table} pes
                INNER JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                WHERE pes.id_serial = ? AND pes.id_produto_evento != ? AND pes.status = 'alocado'
                LIMIT 1";
        $result = $this->query($sql, [$idSerial, $idProdutoEvento]);
        return $result[0] ?? null;
    }

    public function findSeriaisDisponiveis(int $idProdutoEvento, int $idProduto): array
    {
        $sql = "SELECT s.*, p.produto as nome_produto, p.pode_ser_locado
                FROM seriaisproduto s
                INNER JOIN produtos p ON s.id_produto = p.id
                WHERE s.id_produto = ?
                  AND s.status = 'ATIVO'
                  AND p.pode_ser_locado = 'S'
                  AND s.id NOT IN (
                      SELECT id_serial FROM {$this->table} WHERE status = 'alocado'
                  )
                ORDER BY s.serial ASC";
        return $this->query($sql, [$idProduto]);
    }

    public function sumQtdAlocadaByProdutoEvento(int $idProdutoEvento): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE id_produto_evento = ? AND status = 'alocado'",
            [$idProdutoEvento]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Buscar alocacao por serial e produto_evento
     */
    public function findBySerialAndProdutoEvento(int $idSerial, int $idProdutoEvento): ?array
    {
        $result = $this->query(
            "SELECT * FROM {$this->table} WHERE id_serial = ? AND id_produto_evento = ? LIMIT 1",
            [$idSerial, $idProdutoEvento]
        );
        return $result[0] ?? null;
    }

    /**
     * Buscar a (unica) alocacao de um serial, independente do produto_evento.
     * uk_serial_evento e UNIQUE(id_serial) — so pode existir uma linha por serial.
     */
    public function findBySerial(int $idSerial): ?array
    {
        $result = $this->query(
            "SELECT * FROM {$this->table} WHERE id_serial = ? LIMIT 1",
            [$idSerial]
        );
        return $result[0] ?? null;
    }

    /**
     * Atualizar status de uma alocacao
     */
    public function atualizarStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
}

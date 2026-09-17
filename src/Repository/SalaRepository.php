<?php

namespace App\Repository;

use App\Database\Connection;

class SalaRepository extends BaseRepository
{
    protected string $table = 'salas';
    protected array $fillable = [
        'id_evento', 'nome_sala', 'categoria_sala', 'orientacoes_montagem', 'ordem', 'status'
    ];

    /**
     * Busca salas de um evento com categoria
     */
    public function findByEvento(int $idEvento): array
    {
        $sql = "SELECT s.*, cs.nome_categoria 
                FROM {$this->table} s
                LEFT JOIN categorias_sala cs ON s.categoria_sala = cs.id
                WHERE s.id_evento = ?
                ORDER BY COALESCE(cs.ordem, 999) ASC, s.ordem ASC";
        return $this->query($sql, [$idEvento]);
    }

    /**
     * Busca uma sala especifica de um evento
     */
    public function findByEventoAndId(int $idEvento, int $idSala): ?array
    {
        $sql = "SELECT s.*, cs.nome_categoria 
                FROM {$this->table} s
                LEFT JOIN categorias_sala cs ON s.categoria_sala = cs.id
                WHERE s.id_evento = ? AND s.id = ?";
        $results = $this->query($sql, [$idEvento, $idSala]);
        return $results[0] ?? null;
    }

    /**
     * Conta salas por evento
     */
    public function countByEvento(int $idEvento): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE id_evento = ?", [$idEvento]);
        return (int) ($result[0]['total'] ?? 0);
    }
}

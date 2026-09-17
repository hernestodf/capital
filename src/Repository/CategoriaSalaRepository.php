<?php

namespace App\Repository;

class CategoriaSalaRepository extends BaseRepository
{
    protected string $table = 'categorias_sala';
    protected array $fillable = ['nome_categoria', 'ordem', 'status'];

    /**
     * Busca categorias ativas ordenadas
     */
    public function getAtivas(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY ordem ASC");
    }

    /**
     * Busca todas ordenadas
     */
    public function getAllOrdenadas(): array
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY ordem ASC");
    }

    /**
     * Conta categorias
     */
    public function countAtivas(): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1");
        return (int) ($result[0]['total'] ?? 0);
    }
}

<?php

namespace App\Repository;

use App\Database\Connection;

class UnidadeMedidaRepository extends BaseRepository
{
    protected string $table = 'unidademedida';
    protected array $fillable = ['unidademedida'];

    public function all(): array
    {
        return Connection::query("SELECT * FROM {$this->table} ORDER BY unidademedida ASC");
    }
}

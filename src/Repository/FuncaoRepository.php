<?php

namespace App\Repository;

class FuncaoRepository extends BaseRepository
{
    protected string $table = 'funcoes';
    protected array $fillable = ['nome', 'status'];

    public function getAtivas(): array
    {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nome");
    }
}

<?php

namespace App\Repository;

use App\Database\Connection;

class SecaoRepository extends BaseRepository
{
    protected string $table = 'secao';
    protected array $fillable = ['secao'];

    public function getAtivas(): array
    {
        $stmt = Connection::get()->prepare("SELECT id, secao FROM {$this->table} ORDER BY secao");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

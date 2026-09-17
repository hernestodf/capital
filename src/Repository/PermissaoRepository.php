<?php

namespace App\Repository;

class PermissaoRepository extends BaseRepository
{
    protected string $table = 'permissoes';
    protected array $fillable = ['modulo_id', 'nome', 'titulo', 'descricao', 'role_required'];

    public function findByModuloId(int $moduloId): array
    {
        return $this->query(
            "SELECT p.*, m.nome as modulo_nome, m.titulo as modulo_titulo 
             FROM {$this->table} p 
             INNER JOIN modulos m ON p.modulo_id = m.id 
             WHERE p.modulo_id = ? 
             ORDER BY p.nome",
            [$moduloId]
        );
    }

    public function getAllWithModulo(): array
    {
        return $this->query(
            "SELECT p.*, m.nome as modulo_nome, m.titulo as modulo_titulo 
             FROM {$this->table} p 
             INNER JOIN modulos m ON p.modulo_id = m.id 
             ORDER BY m.ordem, p.nome"
        );
    }

    public function findByRole(string $role): array
    {
        return $this->query(
            "SELECT p.*, m.nome as modulo_nome, m.titulo as modulo_titulo 
             FROM {$this->table} p 
             INNER JOIN modulos m ON p.modulo_id = m.id 
             INNER JOIN role_permissoes rp ON p.id = rp.permissao_id 
             WHERE rp.role = ? 
             ORDER BY m.ordem, p.nome",
            [$role]
        );
    }
}

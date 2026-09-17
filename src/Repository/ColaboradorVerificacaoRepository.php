<?php

namespace App\Repository;

use App\Database\Connection;

class ColaboradorVerificacaoRepository extends BaseRepository
{
    protected string $table = 'colaboradores_verificacao';
    protected array $fillable = [
        'colaborador_id', 'token', 'enviado_via_whatsapp', 'data_envio',
        'data_acesso', 'foto_enviada', 'geolocalizacao_lat', 'geolocalizacao_lng', 'status'
    ];

    public function findByToken(string $token): ?array
    {
        $results = Connection::query(
            "SELECT * FROM {$this->table} WHERE token = ?",
            [$token]
        );
        return $results[0] ?? null;
    }

    public function findByColaborador(int $colaboradorId): ?array
    {
        $results = Connection::query(
            "SELECT * FROM {$this->table} WHERE colaborador_id = ? ORDER BY id DESC LIMIT 1",
            [$colaboradorId]
        );
        return $results[0] ?? null;
    }

    /**
     * Busca status de verificacao de multiplos colaboradores em uma unica query
     */
    public function findByColaboradores(array $colaboradorIds): array
    {
        if (empty($colaboradorIds)) return [];

        $placeholders = implode(',', array_fill(0, count($colaboradorIds), '?'));
        $sql = "SELECT cv.*, 
                       ROW_NUMBER() OVER(PARTITION BY cv.colaborador_id ORDER BY cv.id DESC) as rn
                FROM {$this->table} cv
                WHERE cv.colaborador_id IN ($placeholders)";

        $results = Connection::query($sql, $colaboradorIds);

        // Retorna apenas o mais recente de cada colaborador
        $map = [];
        foreach ($results as $row) {
            if ($row['rn'] == 1) {
                $map[$row['colaborador_id']] = $row;
            }
        }
        return $map;
    }

    public function createToken(int $colaboradorId, string $token): int
    {
        $existing = $this->findByColaborador($colaboradorId);

        if ($existing) {
            // Reutiliza token existente se já existe
            Connection::exec(
                "UPDATE {$this->table} 
                 SET token = ?, enviado_via_whatsapp = 0, data_envio = NULL, 
                     data_acesso = NULL, foto_enviada = NULL, status = 'pendente'
                 WHERE id = ?",
                [$token, $existing['id']]
            );
            return (int) $existing['id'];
        }

        // Cria novo registro
        return $this->create([
            'colaborador_id' => $colaboradorId,
            'token' => $token,
            'status' => 'pendente',
        ]);
    }

    public function markAsSent(int $id): int
    {
        return Connection::exec(
            "UPDATE {$this->table} 
             SET enviado_via_whatsapp = 1, data_envio = NOW() 
             WHERE id = ?",
            [$id]
        );
    }

    public function markAsAccessed(int $id): int
    {
        return Connection::exec(
            "UPDATE {$this->table} 
             SET status = 'acessado', data_acesso = NOW() 
             WHERE id = ?",
            [$id]
        );
    }

    public function markAsVerified(int $id, string $foto, ?float $lat = null, ?float $lng = null): int
    {
        return Connection::exec(
            "UPDATE {$this->table} 
             SET status = 'verificado', foto_enviada = ?, 
                 geolocalizacao_lat = ?, geolocalizacao_lng = ?
             WHERE id = ?",
            [$foto, $lat, $lng, $id]
        );
    }
}

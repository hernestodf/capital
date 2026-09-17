<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repository para fotos do evento
 */
class EventoFotoRepository extends BaseRepository
{
    protected string $table = 'evento_fotos';
    protected array $fillable = [
        'evento_id', 'sala_id', 'caminho_arquivo', 'nome_arquivo'
    ];

    /**
     * Listar todas as fotos de um evento
     */
    public function findByEvento(int $eventoId): array
    {
        return Connection::query(
            "SELECT ef.*, s.nome_sala
             FROM evento_fotos ef
             LEFT JOIN salas s ON s.id = ef.sala_id
             WHERE ef.evento_id = ?
             ORDER BY ef.sala_id, ef.created_at DESC",
            [$eventoId]
        );
    }

    /**
     * Contar fotos por sala de um evento
     */
    public function countBySala(int $eventoId): array
    {
        return Connection::query(
            "SELECT sala_id, COUNT(*) as total
             FROM evento_fotos
             WHERE evento_id = ?
             GROUP BY sala_id",
            [$eventoId]
        );
    }

    /**
     * Fotos gerais (sem sala)
     */
    public function findGerais(int $eventoId): array
    {
        return Connection::query(
            "SELECT * FROM evento_fotos
             WHERE evento_id = ? AND sala_id IS NULL
             ORDER BY created_at DESC",
            [$eventoId]
        );
    }

    /**
     * Fotos de uma sala especifica
     */
    public function findBySala(int $eventoId, int $salaId): array
    {
        return Connection::query(
            "SELECT * FROM evento_fotos
             WHERE evento_id = ? AND sala_id = ?
             ORDER BY created_at DESC",
            [$eventoId, $salaId]
        );
    }

    /**
     * Verificar se foto pertence ao evento
     */
    public function belongsToEvento(int $fotoId, int $eventoId): bool
    {
        $result = Connection::query(
            "SELECT id FROM evento_fotos WHERE id = ? AND evento_id = ? LIMIT 1",
            [$fotoId, $eventoId]
        );
        return !empty($result);
    }
}

<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repository para presencas diarias de colaboradores em eventos
 */
class EventoColaboradorPresencaRepository extends BaseRepository
{
    protected string $table = 'evento_colaborador_presencas';
    protected array $fillable = [
        'id_alocacao', 'data', 'foto_entrada', 'geo_entrada_lat',
        'geo_entrada_lng', 'hora_entrada_real', 'foto_saida',
        'geo_saida_lat', 'geo_saida_lng', 'hora_saida_real', 'status'
    ];

    /**
     * Criar ou atualizar registro de entrada
     */
    public function registrarEntrada(int $idAlocacao, string $data, string $foto, float $lat, float $lng): int
    {
        $existente = $this->findByAlocacaoAndData($idAlocacao, $data);

        if ($existente) {
            $status = $existente['hora_saida_real'] ? 'completo' : 'parcial';
            return Connection::exec(
                "UPDATE evento_colaborador_presencas SET
                    foto_entrada = ?, geo_entrada_lat = ?, geo_entrada_lng = ?,
                    hora_entrada_real = NOW(), status = ?
                 WHERE id_alocacao = ? AND data = ?",
                [$foto, $lat, $lng, $status, $idAlocacao, $data]
            );
        }

        return Connection::exec(
            "INSERT INTO evento_colaborador_presencas
                (id_alocacao, data, foto_entrada, geo_entrada_lat, geo_entrada_lng, hora_entrada_real, status)
             VALUES (?, ?, ?, ?, ?, NOW(), 'parcial')",
            [$idAlocacao, $data, $foto, $lat, $lng]
        );
    }

    /**
     * Criar ou atualizar registro de saida
     */
    public function registrarSaida(int $idAlocacao, string $data, string $foto, float $lat, float $lng): int
    {
        $existente = $this->findByAlocacaoAndData($idAlocacao, $data);

        if ($existente) {
            $status = $existente['hora_entrada_real'] ? 'completo' : 'parcial';
            return Connection::exec(
                "UPDATE evento_colaborador_presencas SET
                    foto_saida = ?, geo_saida_lat = ?, geo_saida_lng = ?,
                    hora_saida_real = NOW(), status = ?
                 WHERE id_alocacao = ? AND data = ?",
                [$foto, $lat, $lng, $status, $idAlocacao, $data]
            );
        }

        return Connection::exec(
            "INSERT INTO evento_colaborador_presencas
                (id_alocacao, data, foto_saida, geo_saida_lat, geo_saida_lng, hora_saida_real, status)
             VALUES (?, ?, ?, ?, ?, NOW(), 'parcial')",
            [$idAlocacao, $data, $foto, $lat, $lng]
        );
    }

    /**
     * Buscar presenca por alocacao e data
     */
    public function findByAlocacaoAndData(int $idAlocacao, string $data): ?array
    {
        $result = Connection::query(
            "SELECT * FROM evento_colaborador_presencas
             WHERE id_alocacao = ? AND data = ?
             LIMIT 1",
            [$idAlocacao, $data]
        );
        return $result[0] ?? null;
    }

    /**
     * Buscar todas presencas de uma alocacao
     */
    public function findByAlocacao(int $idAlocacao): array
    {
        return Connection::query(
            "SELECT * FROM evento_colaborador_presencas
             WHERE id_alocacao = ?
             ORDER BY data DESC",
            [$idAlocacao]
        );
    }

    /**
     * Contar presencas completas por alocacao
     */
    public function countCompletaByAlocacao(int $idAlocacao): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total FROM evento_colaborador_presencas
             WHERE id_alocacao = ? AND status = 'completo'",
            [$idAlocacao]
        );
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Contar presencas do dia por evento
     */
    public function countPresencasHojeByEvento(int $idEvento, string $data): int
    {
        $result = Connection::query(
            "SELECT COUNT(DISTINCT ep.id) as total
             FROM evento_colaborador_presencas ep
             INNER JOIN evento_colaboradores ec ON ec.id = ep.id_alocacao
             WHERE ec.id_evento = ? AND ep.data = ? AND ep.status != 'aguardando'",
            [$idEvento, $data]
        );
        return (int)($result[0]['total'] ?? 0);
    }
}

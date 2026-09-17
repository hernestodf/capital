<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repository para alocacao de colaboradores em eventos
 */
class EventoColaboradorRepository extends BaseRepository
{
    protected string $table = 'evento_colaboradores';
    protected array $fillable = [
        'id_evento', 'id_colaborador', 'funcao', 'data_inicio', 'data_fim',
        'hora_inicio', 'hora_fim', 'valor_diaria', 'data_vencimento_pagamento',
        'data_pagamento', 'comprovante_anexo', 'enviar_pagamento',
        'token_presenca', 'status'
    ];

    /**
     * Listar todos os colaboradores alocados a um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return Connection::query(
            "SELECT ec.*, c.nome as colaborador_nome, c.telefone, c.email, c.atua_como,
                    CASE
                        WHEN c.foto LIKE 'uploads/%' THEN c.foto
                        WHEN c.foto IS NOT NULL AND c.foto != '' THEN 'base64'
                        ELSE ''
                    END as foto,
                    c.tipo_chave_pix, c.chavepix
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             WHERE ec.id_evento = ? AND ec.status = 'A'
             ORDER BY c.nome",
            [$idEvento]
        );
    }

    /**
     * Buscar alocacao por ID do colaborador e evento
     */
    public function findByColaboradorAndEvento(int $idColaborador, int $idEvento): ?array
    {
        $result = Connection::query(
            "SELECT ec.*, c.nome as colaborador_nome, c.telefone, c.email
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             WHERE ec.id_colaborador = ? AND ec.id_evento = ? AND ec.status = 'A'
             LIMIT 1",
            [$idColaborador, $idEvento]
        );
        return $result[0] ?? null;
    }

    /**
     * Buscar alocacao por token de presenca
     */
    public function findByToken(string $token): ?array
    {
        $result = Connection::query(
            "SELECT ec.*, c.nome as colaborador_nome, c.telefone, c.email, c.foto, c.cpf, c.cidade, c.estado, c.atua_como,
                    e.nome_evento, e.local_evento, e.data_inicio as evento_data_inicio, e.data_fim as evento_data_fim
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             INNER JOIN eventos e ON e.id = ec.id_evento
             WHERE ec.token_presenca = ? AND ec.status = 'A'
             LIMIT 1",
            [$token]
        );
        return $result[0] ?? null;
    }

    /**
     * Buscar alocacao por ID com dados do colaborador
     */
    public function findById(int $id): ?array
    {
        $result = Connection::query(
            "SELECT ec.*, c.nome as colaborador_nome, c.telefone, c.email, c.atua_como, c.foto, c.cpf, c.cidade, c.estado,
                    c.tipo_chave_pix, c.chavepix, e.nome_evento, e.local_evento, e.data_inicio as evento_data_inicio, e.data_fim as evento_data_fim
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             INNER JOIN eventos e ON e.id = ec.id_evento
             WHERE ec.id = ?
             LIMIT 1",
            [$id]
        );
        return $result[0] ?? null;
    }

    /**
     * Buscar presencas do dia por evento (para evitar N+1)
     */
    public function findPresencasHojeByEvento(int $idEvento, string $data): array
    {
        return Connection::query(
            "SELECT ep.id_alocacao, ep.data, ep.status, ep.hora_entrada_real, ep.hora_saida_real,
                    ep.foto_entrada, ep.foto_saida, ep.geo_entrada_lat, ep.geo_entrada_lng,
                    ep.geo_saida_lat, ep.geo_saida_lng
             FROM evento_colaborador_presencas ep
             INNER JOIN evento_colaboradores ec ON ec.id = ep.id_alocacao
             WHERE ec.id_evento = ? AND ep.data = ?",
            [$idEvento, $data]
        );
    }

    /**
     * Buscar presencas de uma alocacao
     */
    public function findPresencas(int $idAlocacao): array
    {
        return Connection::query(
            "SELECT * FROM evento_colaborador_presencas
             WHERE id_alocacao = ?
             ORDER BY data DESC",
            [$idAlocacao]
        );
    }

    /**
     * Buscar presenca por alocacao e data
     */
    public function findPresencaByData(int $idAlocacao, string $data): ?array
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
     * Contar colaboradores alocados por evento
     */
    public function countByEvento(int $idEvento): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total FROM evento_colaboradores
             WHERE id_evento = ? AND status = 'A'",
            [$idEvento]
        );
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Contar alocacoes com enviar_pagamento = S
     */
    public function countPagamentosPendentes(int $idEvento): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total FROM evento_colaboradores
             WHERE id_evento = ? AND status = 'A' AND enviar_pagamento = 'S' AND data_pagamento IS NULL",
            [$idEvento]
        );
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Buscar alocacoes do dia para CRON de notificacoes
     */
    public function findAlocacoesDoDia(string $data, ?string $horaReference = null, string $tipo = 'entrada'): array
    {
        if ($tipo === 'entrada') {
            // Buscar alocacoes onde hora_inicio esta entre horaReference e horaReference + 30min
            return Connection::query(
                "SELECT ec.*, c.nome as colaborador_nome, c.telefone,
                        e.nome_evento
                 FROM evento_colaboradores ec
                 INNER JOIN colaboradores c ON c.id = ec.id_colaborador
                 INNER JOIN eventos e ON e.id = ec.id_evento
                 WHERE ec.status = 'A'
                   AND ec.data_inicio <= ? AND ec.data_fim >= ?
                   AND TIME(ec.hora_inicio) BETWEEN SUBTIME(?, '00:30:00') AND ?
                   AND c.telefone IS NOT NULL AND c.telefone != ''",
                [$data, $data, $horaReference, $horaReference]
            );
        } else {
            // Saida: hora_fim entre referencia e referencia + 30min
            return Connection::query(
                "SELECT ec.*, c.nome as colaborador_nome, c.telefone,
                        e.nome_evento
                 FROM evento_colaboradores ec
                 INNER JOIN colaboradores c ON c.id = ec.id_colaborador
                 INNER JOIN eventos e ON e.id = ec.id_evento
                 WHERE ec.status = 'A'
                   AND ec.data_inicio <= ? AND ec.data_fim >= ?
                   AND TIME(ec.hora_fim) BETWEEN SUBTIME(?, '00:30:00') AND ?
                   AND c.telefone IS NOT NULL AND c.telefone != ''",
                [$data, $data, $horaReference, $horaReference]
            );
        }
    }

    /**
     * Calcular total de diarias de uma alocacao
     */
    public function calcularTotalDiarias(int $idAlocacao): array
    {
        return Connection::query(
            "SELECT
                ec.valor_diaria,
                COUNT(ep.id) as total_presencas_completas,
                ec.valor_diaria * COUNT(ep.id) as total_a_pagar
             FROM evento_colaboradores ec
             LEFT JOIN evento_colaborador_presencas ep ON ep.id_alocacao = ec.id
                AND ep.status = 'completo'
             WHERE ec.id = ?
             GROUP BY ec.id, ec.valor_diaria",
            [$idAlocacao]
        )[0] ?? ['valor_diaria' => 0, 'total_presencas_completas' => 0, 'total_a_pagar' => 0];
    }
}

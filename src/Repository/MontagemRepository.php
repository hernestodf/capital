<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repositorio para montagem de seriais em salas de eventos
 */
class MontagemRepository extends BaseRepository
{
    protected string $table = 'montagens';
    protected array $fillable = [
        'id_serial', 'id_evento', 'id_sala', 'id_produto_evento', 'observacao_item', 'status'
    ];

    /**
     * Buscar dados completos de uma montagem com serial e produto
     */
    public function findMontagemWithDetails(int $id): ?array
    {
        $result = $this->query(
            "SELECT m.id, m.id_serial, m.id_evento, m.id_sala, m.id_produto_evento, m.status,
                    s.serial, p.produto
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN produtos p ON p.id = s.id_produto
             WHERE m.id = ?
             LIMIT 1",
            [$id]
        );
        
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Verificar se serial ja esta em montagem (ativo em algum evento)
     */
    public function findSerialEmMontagem(string $serial): ?array
    {
        $result = $this->query(
            "SELECT m.*, s.serial, e.nome_evento, e.id as evento_id, sl.nome_sala
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN eventos e ON e.id = m.id_evento
             LEFT JOIN salas sl ON sl.id = m.id_sala
             WHERE s.serial = ? AND m.status != 'devolvido'
             LIMIT 1",
            [$serial]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Verificar se serial pertence a um produto especifico
     */
    public function findSerialInfo(string $serial): ?array
    {
        $result = $this->query(
            "SELECT s.id, s.serial, s.status as serial_status, s.id_produto, p.produto as nome_produto
             FROM seriaisproduto s
             LEFT JOIN produtos p ON p.id = s.id_produto
             WHERE s.serial = ?
             LIMIT 1",
            [$serial]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Verificar se serial ja foi inserido em um evento especifico (qualquer status)
     */
    public function findSerialNoEvento(string $serial, int $idEvento): ?array
    {
        $result = $this->query(
            "SELECT m.id, m.status, m.id_sala, sl.nome_sala
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             LEFT JOIN salas sl ON sl.id = m.id_sala
             WHERE s.serial = ? AND m.id_evento = ?
             LIMIT 1",
            [$serial, $idEvento]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Inserir serial na montagem
     */
    public function inserirMontagem(array $data): int
    {
        $this->create($data);
        return (int) Connection::lastInsertId();
    }

    /**
     * Listar seriais pendentes de montagem (nao enviados para nenhuma sala)
     * Retorna seriais do estoque que podem ser inseridos no evento
     */
    public function findPendentes(int $idEvento): array
    {
        return $this->query(
            "SELECT s.id as id_serial, s.serial, s.id_produto, p.produto as nome_produto,
                    COALESCE(m.id, 0) as montagem_id, m.observacao_item, m.status as montagem_status
             FROM seriaisproduto s
             INNER JOIN produtos p ON p.id = s.id_produto
             LEFT JOIN montagens m ON m.id_serial = s.id AND m.id_evento = ?
             WHERE p.pode_ser_locado = 'S'
               AND s.status = 'ATIVO'
               AND (m.id IS NULL OR m.status = 'devolvido')
             ORDER BY p.produto, s.serial",
            [$idEvento]
        );
    }

    /**
     * Listar seriais em montagem agrupados por sala
     */
    public function findBySala(int $idEvento): array
    {
        return $this->query(
            "SELECT m.id as montagem_id, m.observacao_item, m.status,
                    s.id as serial_id, s.serial, s.id_produto,
                    p.produto as nome_produto,
                    sl.id as sala_id, sl.nome_sala, sl.orientacoes_montagem
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN produtos p ON p.id = s.id_produto
             INNER JOIN salas sl ON sl.id = m.id_sala
             WHERE m.id_evento = ? AND m.status != 'devolvido'
             ORDER BY sl.ordem, sl.nome_sala, p.produto, s.serial",
            [$idEvento]
        );
    }

    /**
     * Devolver serial da montagem (marcar como devolvido)
     */
    public function devolverSerial(int $montagemId): bool
    {
        return $this->update($montagemId, ['status' => 'devolvido']);
    }

    /**
     * Marcar como montado
     */
    public function marcarComoMontado(int $montagemId): bool
    {
        return $this->update($montagemId, ['status' => 'montado']);
    }

    /**
     * Atualizar observacao do item
     */
    public function updateObservacao(int $montagemId, string $observacao): bool
    {
        return $this->update($montagemId, ['observacao_item' => $observacao]);
    }

    /**
     * Listar montagens por evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->query(
            "SELECT m.id, m.id_sala, m.id_produto_evento, m.status, m.observacao_item,
                    s.serial, p.produto, e.nome_evento as evento, sl.nome_sala as sala
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN produtos p ON p.id = s.id_produto
             INNER JOIN eventos e ON e.id = m.id_evento
             LEFT JOIN salas sl ON sl.id = m.id_sala
             WHERE m.id_evento = ? AND m.status != 'devolvido'
             ORDER BY sl.nome_sala, p.produto, s.serial",
            [$idEvento]
        );
    }

    /**
     * Contar seriais por sala
     */
    public function countBySala(int $idEvento): array
    {
        return $this->query(
            "SELECT sl.id as sala_id, sl.nome_sala, COUNT(m.id) as total_seriais,
                    SUM(CASE WHEN m.status = 'montado' THEN 1 ELSE 0 END) as montados,
                    SUM(CASE WHEN m.status = 'pendente' THEN 1 ELSE 0 END) as pendentes
             FROM salas sl
             LEFT JOIN montagens m ON m.id_sala = sl.id AND m.id_evento = ? AND m.status != 'devolvido'
             WHERE sl.id_evento = ?
             GROUP BY sl.id, sl.nome_sala
             ORDER BY sl.ordem, sl.nome_sala",
            [$idEvento, $idEvento]
        );
    }

    /**
     * Buscar montagem pelo numero do serial e evento
     */
    public function getBySerialAndEvento(string $serial, int $idEvento): ?array
    {
        $result = $this->query(
            "SELECT m.id, m.id_sala, m.id_evento, m.id_serial, m.status, m.observacao_item,
                    s.serial, p.produto as nome_produto, p.id as id_produto
             FROM montagens m
             INNER JOIN seriaisproduto s ON s.id = m.id_serial
             INNER JOIN produtos p ON p.id = s.id_produto
             WHERE s.serial = ? AND m.id_evento = ? AND m.status != 'devolvido'
             LIMIT 1",
            [$serial, $idEvento]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Marcar montagem como devolvida
     */
    public function marcarDevolvida(int $montagemId): bool
    {
        return $this->update($montagemId, ['status' => 'devolvido']);
    }

    /**
     * Contar montagens por evento e status
     */
    public function countByEventoStatus(int $idEvento, string $status): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM montagens WHERE id_evento = ? AND status = ?",
            [$idEvento, $status]
        );

        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Contar montagens por status (todas)
     */
    public function countByStatus(string $status): int
    {
        // Status reais: 'pendente', 'montado', 'devolvido'
        $result = $this->query(
            "SELECT COUNT(*) as total FROM montagens WHERE status = ?",
            [$status]
        );

        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Contar devoluções pendentes (status = 'montado' significa ainda não devolvido)
     */
    public function countDevolucoesPendentes(): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM montagens WHERE status = 'montado'"
        );

        return (int) ($result[0]['total'] ?? 0);
    }
}

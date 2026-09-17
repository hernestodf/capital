<?php

namespace App\Repository;

use App\Database\Connection;

class DevolucaoRepository extends BaseRepository
{
    protected string $table = 'devolucoes';
    protected array $fillable = [
        'id_evento',
        'id_serial',
        'tipo_devolucao',
        'status',
        'motivo_pendencia',
        'solucao_pendencia',
        'observacao',
        'usuario_devolucao'
    ];

    /**
     * Verifica se um serial já foi devolvido para este evento
     */
    public function jaDevolvido(string $serial, int $idEvento): bool
    {
        // Buscar o ID do serial
        $serialResult = Connection::query(
            "SELECT id FROM seriaisproduto WHERE serial = ? LIMIT 1",
            [$serial]
        );
        
        if (empty($serialResult)) {
            return false; // Serial nem existe
        }
        
        $idSerial = $serialResult[0]['id'];
        
        // Verificar se já existe devolucao para este serial neste evento
        $result = Connection::query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE id_serial = ? AND id_evento = ?",
            [$idSerial, $idEvento]
        );
        
        return ($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Busca todas as devolucoes de um serial (qualquer evento)
     */
    public function findBySerial(int $idSerial): array
    {
        return Connection::query(
            "SELECT d.*, s.serial, p.produto as nome_produto,
                    e.nome_evento, e.data_inicio, e.data_fim
             FROM {$this->table} d
             LEFT JOIN seriaisproduto s ON s.id = d.id_serial
             LEFT JOIN produtos p ON p.id = s.id_produto
             LEFT JOIN eventos e ON e.id = d.id_evento
             WHERE d.id_serial = ?
             ORDER BY d.data_devolucao DESC",
            [$idSerial]
        );
    }

    /**
     * Busca devolucao por serial e evento
     */
    public function findBySerialAndEvento(int $idSerial, int $idEvento): ?array
    {
        $result = Connection::query(
            "SELECT * FROM {$this->table} WHERE id_serial = ? AND id_evento = ? LIMIT 1",
            [$idSerial, $idEvento]
        );
        
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Busca devoluções por evento e status
     */
    public function findByEventoStatus(int $idEvento, string $status): array
    {
        return Connection::query(
            "SELECT * FROM {$this->table} WHERE id_evento = ? AND status = ?",
            [$idEvento, $status]
        );
    }

    /**
     * Conta devoluções por evento e status
     */
    public function countByEventoStatus(int $idEvento, string $status): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE id_evento = ? AND status = ?",
            [$idEvento, $status]
        );
        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Busca todas as devoluções de um evento
     */
    /**
     * Busca todas as devolucoes de um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return Connection::query(
            "SELECT d.*, s.serial, p.produto as nome_produto
             FROM {$this->table} d
             LEFT JOIN seriaisproduto s ON s.id = d.id_serial
             LEFT JOIN produtos p ON p.id = s.id_produto
             WHERE d.id_evento = ? 
             ORDER BY d.data_devolucao DESC",
            [$idEvento]
        );
    }

    /**
     * Atualiza o status de um serial na tabela de devoluções
     */
    public function updateStatusSerial(int $idEvento, int $idSerial, string $status): int
    {
        return Connection::exec(
            "UPDATE {$this->table} SET status = ? WHERE id_evento = ? AND id_serial = ?",
            [$status, $idEvento, $idSerial]
        );
    }

    /**
     * Busca as devoluções pendentes de um evento
     */
    public function findPendentesByEvento(int $idEvento): array
    {
        return Connection::query(
            "SELECT * FROM {$this->table} WHERE id_evento = ? AND status = 'P'",
            [$idEvento]
        );
    }

    /**
     * Conta devoluções pendentes de um evento
     */
    public function countPendentesByEvento(int $idEvento): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE id_evento = ? AND status = 'P'",
            [$idEvento]
        );
        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Atualiza o campo de solução de pendência
     */
    public function updateSolucao(int $id, string $solucao): int
    {
        return Connection::exec(
            "UPDATE {$this->table} SET solucao_pendencia = ?, status = 'S' WHERE id = ?",
            [$solucao, $id]
        );
    }
}
?>
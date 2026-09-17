<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repository para queries do Dashboard
 * Extrai queries raw do DashboardController
 */
class DashboardRepository
{
    /**
     * Conta contas pendentes do mês
     */
    public function countContasPendentesMes(string $mes, string $ano): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total 
             FROM contas_pagar 
             WHERE MONTH(data_vencimento) = ? 
             AND YEAR(data_vencimento) = ? 
             AND status = 'PENDENTE'",
            [$mes, $ano]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Total pago no mês
     */
    public function getTotalPagoMes(string $mes, string $ano): float
    {
        $result = Connection::query(
            "SELECT COALESCE(SUM(valor_pago), 0) as total 
             FROM contas_pagar 
             WHERE MONTH(data_vencimento) = ? 
             AND YEAR(data_vencimento) = ? 
             AND status = 'PAGO'",
            [$mes, $ano]
        );
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Últimas contas pagas
     */
    public function getAtividadesRecentes(int $limit = 5): array
    {
        return Connection::query(
            "SELECT 'Conta paga' as tipo, descricao, data_pagamento as created_at
             FROM contas_pagar
              WHERE status = 'PAGO'
             ORDER BY data_pagamento DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Conta montagens concluídas hoje
     */
    public function countMontagensConcluidasHoje(): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total 
             FROM montagens 
             WHERE status = 'devolvido' 
             AND DATE(updated_at) = CURDATE()"
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Montagens pendentes
     */
    public function getMontagensPendentes(int $limit = 10): array
    {
        return Connection::query(
            "SELECT m.id, m.id_evento, e.nome_evento as evento_nome, m.status
             FROM montagens m
             INNER JOIN eventos e ON m.id_evento = e.id
             WHERE m.status = 'pendente'
             ORDER BY e.data_inicio ASC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Devoluções pendentes
     */
    public function getDevolucoesPendentes(int $limit = 10): array
    {
        return Connection::query(
            "SELECT m.id, m.id_evento, e.nome_evento as evento_nome, m.status
             FROM montagens m
             INNER JOIN eventos e ON m.id_evento = e.id
             WHERE m.status = 'montado'
             ORDER BY e.data_fim DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Total de seriais
     */
    public function getTotalSeriais(): int
    {
        $result = Connection::query("SELECT COUNT(*) as total FROM seriaisproduto");
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Seriais em evento (montados)
     */
    public function getSeriaisEmEvento(): int
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total FROM montagens WHERE status = 'montado'"
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Total de salas
     */
    public function getTotalSalas(): int
    {
        $result = Connection::query("SELECT COUNT(*) as total FROM salas");
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Produtos por seção
     */
    public function getProdutosPorSecao(): array
    {
        return Connection::query(
            "SELECT s.secao as nome, 
                   COUNT(p.id) as total_produtos
            FROM secao s
            LEFT JOIN produtos p ON p.id_secao = s.id
            GROUP BY s.secao
            ORDER BY total_produtos DESC"
        );
    }

    /**
     * Últimos produtos cadastrados
     */
    public function getUltimosProdutos(int $limit = 10): array
    {
        return Connection::query(
            "SELECT p.id, p.produto as nome, p.custo, p.observacao,
                   s.secao as secao_nome
            FROM produtos p
            LEFT JOIN secao s ON p.id_secao = s.id
            ORDER BY p.id DESC
            LIMIT ?",
            [$limit]
        );
    }

    /**
     * Salas com produtos
     */
    public function getSalasComProdutos(int $limit = 10): array
    {
        return Connection::query(
            "SELECT sl.nome_sala as nome, 
                   COUNT(pe.id) as total_produtos
            FROM salas sl
            LEFT JOIN produtos_evento pe ON pe.id_sala = sl.id
            GROUP BY sl.nome_sala
            ORDER BY total_produtos DESC
            LIMIT ?",
            [$limit]
        );
    }
}

<?php

namespace App\Repository;

class ProdutoEventoSublocacaoRepository extends BaseRepository
{
    protected string $table = 'produto_evento_sublocacao';
    protected array $fillable = [
        'id_produto_evento', 'id_fornecedor', 'id_sublocacao_item',
        'quantidade', 'valor_unit', 'total', 'status',
        'serial_fornecedor', 'produto_fornecedor', 'custo_unit'
    ];

    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        $sql = "SELECT pes.*, f.nome_fantasia as fornecedor_nome, si.produto as item_produto,
                       si.codigo as item_codigo
                FROM {$this->table} pes
                LEFT JOIN fornecedores f ON pes.id_fornecedor = f.id
                LEFT JOIN sublocacao_itens si ON pes.id_sublocacao_item = si.id
                WHERE pes.id_produto_evento = ?
                ORDER BY f.nome_fantasia ASC";
        return $this->query($sql, [$idProdutoEvento]);
    }

    public function findByEvento(int $idEvento): array
    {
        $sql = "SELECT pes.*, pe.produto as produto_nome, pe.qtd as produto_qtd,
                       f.nome_fantasia as fornecedor_nome, ev.nome_evento
                FROM {$this->table} pes
                INNER JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                INNER JOIN fornecedores f ON pes.id_fornecedor = f.id
                INNER JOIN eventos ev ON pe.id_evento = ev.id
                WHERE pe.id_evento = ?
                ORDER BY pe.produto ASC, f.nome_fantasia ASC";
        return $this->query($sql, [$idEvento]);
    }

    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE id_produto_evento = ? AND status != 'cancelado'",
            [$idProdutoEvento]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Retorna todos os itens sublocados (ativos), agrupados por fornecedor.
     * Não devolvidos aparecem primeiro dentro de cada fornecedor.
     */
    public function consolidado(): array
    {
        $sql = "SELECT
                    pes.id,
                    pe.produto                        AS produto_evento,
                    pes.produto                       AS produto_sublocacao,
                    pes.produto_fornecedor,
                    pes.codigo_barras,
                    pes.serial_fornecedor,
                    pes.quantidade,
                    pes.custo_unit,
                    pes.status          AS status_pagamento,
                    pes.devolvido_em,
                    f.id                AS id_fornecedor,
                    f.nome_fantasia     AS fornecedor_nome,
                    ev.id               AS id_evento,
                    ev.nome_evento,
                    ev.local_evento,
                    ev.data_inicio,
                    ev.data_fim
                FROM {$this->table} pes
                INNER JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                INNER JOIN fornecedores f     ON pes.id_fornecedor = f.id
                INNER JOIN eventos ev         ON pe.id_evento = ev.id
                WHERE pes.status != 'cancelado'
                ORDER BY (pes.devolvido_em IS NULL) DESC, f.nome_fantasia ASC, ev.data_inicio DESC";

        $rows = $this->query($sql);

        $grouped = [];
        foreach ($rows as $row) {
            $fid = $row['id_fornecedor'];
            if (!isset($grouped[$fid])) {
                $grouped[$fid] = [
                    'fornecedor_nome' => $row['fornecedor_nome'],
                    'itens'           => [],
                ];
            }
            $grouped[$fid]['itens'][] = $row;
        }

        return $grouped;
    }

    public function marcarDevolvido(int $id): void
    {
        $this->query(
            "UPDATE {$this->table} SET devolvido_em = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function sumQtdSublocadaByProdutoEvento(int $idProdutoEvento): float
    {
        $result = $this->query(
            "SELECT COALESCE(SUM(quantidade), 0) as total FROM {$this->table} WHERE id_produto_evento = ? AND status != 'cancelado'",
            [$idProdutoEvento]
        );
        return (float) ($result[0]['total'] ?? 0);
    }
}

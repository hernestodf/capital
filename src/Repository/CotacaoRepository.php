<?php
namespace App\Repository;

use App\Database\Connection;

class CotacaoRepository
{
    protected string $table = 'item_cotacoes';

    /**
     * Listar todas as cotacoes de um item do evento
     */
    public function findByProdutoEvento(int $idProdutoEvento): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT ic.*, f.nome_fantasia as fornecedor_nome, f.email as fornecedor_email, f.telefone as fornecedor_telefone
             FROM item_cotacoes ic
             INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
             WHERE ic.id_produto_evento = ?
             ORDER BY ic.valor_proposto ASC"
        );
        $stmt->execute([$idProdutoEvento]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Buscar cotacao por ID
     */
    public function find(int $id): ?array
    {
        $stmt = Connection::get()->prepare(
            "SELECT ic.*, f.nome_fantasia as fornecedor_nome, f.email as fornecedor_email
             FROM item_cotacoes ic
             INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
             WHERE ic.id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Criar nova cotacao
     */
    public function create(array $data): int
    {
        $stmt = Connection::get()->prepare(
            "INSERT INTO item_cotacoes (id_produto_evento, id_fornecedor, id_evento, valor_proposto, status)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['id_produto_evento'],
            $data['id_fornecedor'],
            $data['id_evento'],
            $data['valor_proposto'],
            $data['status'] ?? 'aguardando'
        ]);
        return (int) Connection::get()->lastInsertId();
    }

    /**
     * Atualizar cotacao
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        $values = [];
        foreach ($data as $key => $val) {
            $sets[] = "$key = ?";
            $values[] = $val;
        }
        $values[] = $id;
        $stmt = Connection::get()->prepare(
            "UPDATE item_cotacoes SET " . implode(', ', $sets) . " WHERE id = ?"
        );
        return $stmt->execute($values);
    }

    /**
     * Desmarcar todos os vencedores de um item
     */
    public function unmarkWinner(int $idProdutoEvento): bool
    {
        $stmt = Connection::get()->prepare(
            "UPDATE item_cotacoes SET vencedor = 'N', status = 'aguardando'
             WHERE id_produto_evento = ? AND vencedor = 'S'"
        );
        return $stmt->execute([$idProdutoEvento]);
    }

    /**
     * Marcar vencedor
     */
    public function markWinner(int $idCotacao, int $idProdutoEvento, float $valor): bool
    {
        $stmt = Connection::get()->prepare(
            "UPDATE item_cotacoes SET vencedor = 'S', status = 'vencedor_definido'
             WHERE id = ?"
        );
        $stmt->execute([$idCotacao]);

        // Atualizar custo do item
        $stmt = Connection::get()->prepare(
            "UPDATE produtos_evento SET custo_unit = ?, fornecedor_vencedor = (
                SELECT f.nome_fantasia FROM item_cotacoes ic
                INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
                WHERE ic.id = ?
            ) WHERE id = ?"
        );
        return $stmt->execute([$valor, $idCotacao, $idProdutoEvento]);
    }

    /**
     * Contar propostas por item
     */
    public function countByProdutoEvento(int $idProdutoEvento): int
    {
        $stmt = Connection::get()->prepare(
            "SELECT COUNT(*) FROM item_cotacoes WHERE id_produto_evento = ?"
        );
        $stmt->execute([$idProdutoEvento]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verificar se ja existe proposta deste fornecedor para este item
     */
    public function exists(int $idProdutoEvento, int $idFornecedor): bool
    {
        $stmt = Connection::get()->prepare(
            "SELECT COUNT(*) FROM item_cotacoes WHERE id_produto_evento = ? AND id_fornecedor = ?"
        );
        $stmt->execute([$idProdutoEvento, $idFornecedor]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Criar cotacao ou ignorar se ja existe (atomico via UNIQUE KEY)
     */
    public function createOrIgnore(array $data): int
    {
        $stmt = Connection::get()->prepare(
            "INSERT INTO item_cotacoes (id_produto_evento, id_fornecedor, id_evento, valor_proposto, status)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        );
        $stmt->execute([
            $data['id_produto_evento'],
            $data['id_fornecedor'],
            $data['id_evento'],
            $data['valor_proposto'],
            $data['status'] ?? 'aguardando'
        ]);
        return (int) Connection::get()->lastInsertId();
    }

    /**
     * Buscar ID da cotacao por produto_evento e fornecedor
     */
    public function findIdByProdutoFornecedor(int $idProdutoEvento, int $idFornecedor): ?int
    {
        $stmt = Connection::get()->prepare(
            "SELECT id FROM item_cotacoes WHERE id_produto_evento = ? AND id_fornecedor = ? LIMIT 1"
        );
        $stmt->execute([$idProdutoEvento, $idFornecedor]);
        $result = $stmt->fetchColumn();
        return $result ? (int) $result : null;
    }

    /**
     * Buscar vencedor de um item
     */
    public function findWinner(int $idProdutoEvento): ?array
    {
        $stmt = Connection::get()->prepare(
            "SELECT ic.*, f.nome_fantasia as fornecedor_nome, f.email as fornecedor_email
             FROM item_cotacoes ic
             INNER JOIN fornecedores f ON f.id = ic.id_fornecedor
             WHERE ic.id_produto_evento = ? AND ic.vencedor = 'S'
             LIMIT 1"
        );
        $stmt->execute([$idProdutoEvento]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Resumo de cotacoes por evento (para KPIs)
     */
    public function summaryByEvento(int $idEvento): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT
                COUNT(DISTINCT ic.id_produto_evento) as total_itens_cotados,
                COUNT(*) as total_propostas,
                SUM(CASE WHEN ic.vencedor = 'S' THEN 1 ELSE 0 END) as vencedores_definidos
             FROM item_cotacoes ic
             WHERE ic.id_evento = ?"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

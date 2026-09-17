<?php

namespace App\Repository;

use App\Database\Connection;

class ProdutoRepository extends BaseRepository
{
    protected string $table = 'produtos';
    protected array $fillable = ['produto', 'codigo_barras', 'custo', 'id_secao', 'pode_ser_locado', 'observacao'];

    public function all(): array
    {
        return Connection::query(
            "SELECT p.*, s.secao as secao_nome,
                    (SELECT COUNT(*)
                     FROM seriaisproduto sp
                     WHERE sp.id_produto = p.id AND sp.status = 'ATIVO') as total_codigos,
                    (SELECT COUNT(DISTINCT sp2.id)
                     FROM seriaisproduto sp2
                     JOIN montagens m ON m.id_serial = sp2.id
                     WHERE sp2.id_produto = p.id AND sp2.status = 'ATIVO' AND m.status != 'devolvido') as em_campo
             FROM {$this->table} p
             LEFT JOIN secao s ON p.id_secao = s.id
             ORDER BY p.id ASC"
        );
    }

    public function alocacoesPorNome(string $nome): array
    {
        return Connection::query(
            "SELECT pe.id_evento, pe.produto, pe.qtd, pe.qtd_alocada, pe.qtd_sublocada,
                    e.nome_evento, e.data_inicio, e.data_fim, e.local_evento,
                    e.status_locacao
             FROM produtos_evento pe
             JOIN eventos e ON e.id = pe.id_evento
             WHERE pe.produto COLLATE utf8mb4_unicode_ci = ? AND e.status = 1 AND pe.qtd_alocada > 0
             ORDER BY e.data_inicio DESC",
            [$nome]
        );
    }

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where = "WHERE p.produto LIKE ? OR p.observacao LIKE ?";
            $params = ["%{$search}%", "%{$search}%"];
        }

        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p {$where}";
        $stmt = Connection::get()->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        $sql = "SELECT p.*, s.secao as secao_nome 
                FROM {$this->table} p 
                LEFT JOIN secao s ON p.id_secao = s.id 
                {$where} 
                ORDER BY p.id DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage),
            ],
        ];
    }

    public function countSeriaisByProduto(int $idProduto): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sp.status = 'ATIVO' THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN sp.status = 'MANUTENCAO' THEN 1 ELSE 0 END) as manutencao,
                SUM(CASE WHEN sp.status = 'VENDER' THEN 1 ELSE 0 END) as vender
             FROM seriaisproduto sp 
             WHERE sp.id_produto = ?"
        );
        $stmt->execute([$idProduto]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByProduto(int $idProduto): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT sp.*, p.produto as produto_nome
             FROM seriaisproduto sp
             LEFT JOIN produtos p ON sp.id_produto = p.id
             WHERE sp.id_produto = ?
             ORDER BY sp.serial"
        );
        $stmt->execute([$idProduto]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

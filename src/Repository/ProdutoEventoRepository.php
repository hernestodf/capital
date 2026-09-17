<?php

namespace App\Repository;

use App\Database\Connection;

class ProdutoEventoRepository extends BaseRepository
{
    protected string $table = 'produtos_evento';
    protected array $fillable = [
        'id_evento', 'id_sala', 'id_categoria', 'id_planilha', 'produto', 'observacao_montagem',
        'qtd', 'qtd_alocada', 'qtd_sublocada', 'valor_unit', 'dias', 'total_item', 'custo_unit',
        'potencia_w', 'horas_uso', 'status'
    ];

    /**
     * Busca produtos de uma sala com dados da planilha
     */
    public function findBySala(int $idSala): array
    {
        $sql = "SELECT pe.*, p.item as planilha_item, p.descricao as planilha_descricao,
                       cs.nome_categoria as categoria_nome
                FROM {$this->table} pe
                LEFT JOIN planilhas p ON pe.id_planilha = p.id
                LEFT JOIN categorias_sala cs ON pe.id_categoria = cs.id
                WHERE pe.id_sala = ?
                ORDER BY pe.id ASC";
        return $this->query($sql, [$idSala]);
    }

    /**
     * Busca todos os produtos de um evento
     */
    public function findByEvento(int $idEvento): array
    {
        $sql = "SELECT pe.*, p.item as planilha_item, p.descricao as planilha_descricao,
                       cs.nome_categoria as categoria_nome
                FROM {$this->table} pe
                LEFT JOIN planilhas p ON pe.id_planilha = p.id
                LEFT JOIN categorias_sala cs ON pe.id_categoria = cs.id
                WHERE pe.id_evento = ?
                ORDER BY pe.id_sala ASC, pe.id ASC";
        return $this->query($sql, [$idEvento]);
    }

    /**
     * Busca produtos agrupados por sala
     */
    public function groupBySala(int $idEvento): array
    {
        $sql = "SELECT pe.id_sala, s.nome_sala, s.orientacoes_montagem,
                       COUNT(pe.id) as total_produtos,
                       SUM(pe.total_item) as total_venda,
                       SUM(pe.custo_unit) as total_custo,
                       (SUM(pe.total_item) - SUM(pe.custo_unit)) as lucro
                FROM {$this->table} pe
                INNER JOIN salas s ON pe.id_sala = s.id
                WHERE pe.id_evento = ?
                GROUP BY pe.id_sala, s.nome_sala, s.orientacoes_montagem
                ORDER BY s.ordem ASC";
        return $this->query($sql, [$idEvento]);
    }

    /**
     * Soma totals financeiros de um evento
     */
    public function getTotaisEvento(int $idEvento): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_itens,
                    COALESCE(SUM(total_item), 0) as total_venda,
                    COALESCE(SUM(custo_unit), 0) as total_custo,
                    COALESCE(SUM(total_item) - SUM(custo_unit), 0) as lucro,
                    COALESCE(SUM(qtd_alocada), 0) as total_alocado,
                    COALESCE(SUM(qtd_sublocada), 0) as total_sublocado
                FROM {$this->table}
                WHERE id_evento = ? AND status = 1";
        $result = $this->query($sql, [$idEvento]);
        return $result[0] ?? ['total_itens' => 0, 'total_venda' => 0, 'total_custo' => 0, 'lucro' => 0, 'total_alocado' => 0, 'total_sublocado' => 0];
    }

    /**
     * Conta produtos por sala
     */
    public function countBySala(int $idSala): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE id_sala = ?", [$idSala]);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Conta produtos por status
     */
    public function countByStatus(int $status): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = ?", [$status]);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Busca um produto com dados da planilha
     */
    public function findWithPlanilha(int $id): array
    {
        $sql = "SELECT pe.*, p.item as planilha_item, p.descricao as planilha_descricao,
                       cs.nome_categoria as categoria_nome
                FROM {$this->table} pe
                LEFT JOIN planilhas p ON pe.id_planilha = p.id
                LEFT JOIN categorias_sala cs ON pe.id_categoria = cs.id
                WHERE pe.id = ?";
        $result = $this->query($sql, [$id]);
        return $result[0] ?? [];
    }

    /**
     * Busca dados da planilha por ID
     */
    public function findPlanilhaById(int $id): ?array
    {
        $sql = "SELECT id, item, descricao, valor FROM planilhas WHERE id = ?";
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * Autocomplete de produtos da planilha
     */
    public function searchPlanilhas(string $term): array
    {
        $sql = "SELECT id, item, valor, descricao, potencia_w, horas_uso
                FROM planilhas
                WHERE item LIKE ?
                ORDER BY item ASC";
        return $this->query($sql, ["%{$term}%"]);
    }

    /**
     * Busca alocação de um produto em todos os eventos
     * Retorna: Todos os eventos onde o produto está alocado com qtds
     */
    public function findByProdutoNome(string $nomeProduto): array
    {
        $sql = "SELECT pe.id, pe.id_evento, pe.produto, pe.qtd, pe.qtd_alocada, pe.qtd_sublocada
                FROM {$this->table} pe
                WHERE pe.produto LIKE ?
                ORDER BY pe.id_evento DESC, pe.produto ASC";
        return $this->query($sql, ["%{$nomeProduto}%"]);
    }
}

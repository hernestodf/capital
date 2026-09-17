<?php

namespace App\Repository;

use App\Database\Connection;

class ContasPagarRepository extends BaseRepository
{
    protected string $table = 'contas_pagar';
    protected array $fillable = [
        'id_fornecedor', 'tipo', 'numero_nf', 'descricao', 'valor', 'valor_pago',
        'data_vencimento', 'data_pagamento', 'status', 'tipo_pagamento',
        'observacao', 'comprovante_anexo', 'nota_fiscal'
    ];

    /**
     * Busca contas a pagar com filtros avancados
     * Prioridade: contas nao pagas ordenadas por data de vencimento
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        // Filtro por termo de busca (descricao, NF, fornecedor)
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $where[] = "(cp.descricao LIKE ? OR cp.numero_nf LIKE ? OR f.nome_fantasia LIKE ? OR c.nome LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filtro por fornecedor
        if (!empty($filters['id_fornecedor'])) {
            $where[] = "cp.id_fornecedor = ?";
            $params[] = $filters['id_fornecedor'];
        }

        // Filtro por colaborador (via referencia_id quando tipo = 'colaborador')
        if (!empty($filters['id_colaborador'])) {
            $where[] = "cp.tipo = 'colaborador' AND cp.referencia_id = ?";
            $params[] = $filters['id_colaborador'];
        }

        // Filtro por tipo (colaborador/fornecedor/outro)
        if (!empty($filters['tipo'])) {
            $where[] = "cp.tipo = ?";
            $params[] = $filters['tipo'];
        }

        // Filtro por evento
        if (!empty($filters['evento_id'])) {
            $where[] = "cp.evento_id = ?";
            $params[] = $filters['evento_id'];
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $where[] = "cp.status = ?";
            $params[] = $filters['status'];
        }

        // Filtro por periodo de vencimento (data_inicio e data_fim)
        if (!empty($filters['data_vencimento_inicio'])) {
            $where[] = "cp.data_vencimento >= ?";
            $params[] = $filters['data_vencimento_inicio'];
        }
        if (!empty($filters['data_vencimento_fim'])) {
            $where[] = "cp.data_vencimento <= ?";
            $params[] = $filters['data_vencimento_fim'];
        }

        // Filtro por valor (valor_min e valor_max)
        if (!empty($filters['valor_min'])) {
            $where[] = "cp.valor >= ?";
            $params[] = $filters['valor_min'];
        }
        if (!empty($filters['valor_max'])) {
            $where[] = "cp.valor <= ?";
            $params[] = $filters['valor_max'];
        }

        // Montar clausula WHERE
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // ORDENACAO PRIORITARIA:
        // 1. Nao pagos primeiro (PENDENTE, VENCIDO, PARCIAL antes de PAGO)
        // 2. Dentro dos nao pagos, ordenar por data de vencimento ASC
        // 3. Pagos ficam no final
        $sql = "SELECT cp.*, f.nome_fantasia as fornecedor_nome, c.nome as colaborador_nome, ev.nome_evento as evento_nome,
                       ev.os_cliente as evento_os_cliente,
                       ev.local_evento as evento_local, ev.data_inicio as evento_data_inicio, ev.data_fim as evento_data_fim,
                       CASE 
                           WHEN cp.tipo = 'fornecedor' THEN pe.produto
                           WHEN cp.tipo = 'colaborador' THEN ec.funcao
                           WHEN cp.tipo = 'outro' THEN eoc.descricao
                           ELSE NULL
                       END as item_nome,
                       CASE 
                           WHEN cp.tipo = 'fornecedor' THEN pe.dias
                           WHEN cp.tipo = 'colaborador' THEN (DATEDIFF(ec.data_fim, ec.data_inicio) + 1)
                           ELSE NULL
                       END as item_dias,
                       CASE 
                           WHEN cp.tipo = 'fornecedor' THEN pe.observacao_montagem
                           WHEN cp.tipo = 'outro' THEN eoc.observacao
                           ELSE NULL
                       END as item_observacao
                FROM {$this->table} cp 
                LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
                LEFT JOIN produto_evento_sublocacao pes ON cp.tipo = 'fornecedor' AND cp.referencia_id = pes.id
                LEFT JOIN produtos_evento pe ON pes.id_produto_evento = pe.id
                LEFT JOIN evento_colaboradores ec ON cp.tipo = 'colaborador' AND cp.referencia_id = ec.id
                LEFT JOIN colaboradores c ON ec.id_colaborador = c.id
                LEFT JOIN evento_outros_custos eoc ON cp.tipo = 'outro' AND cp.referencia_id = eoc.id
                LEFT JOIN eventos ev ON (cp.evento_id = ev.id OR (cp.tipo = 'colaborador' AND ec.id_evento = ev.id) OR (cp.tipo = 'outro' AND eoc.evento_id = ev.id))
                $whereClause
                ORDER BY 
                    CASE cp.status 
                        WHEN 'VENCIDO' THEN 1 
                        WHEN 'PENDENTE' THEN 2 
                        WHEN 'PARCIAL' THEN 3 
                        WHEN 'PAGO' THEN 4 
                        ELSE 5 
                    END ASC,
                    cp.data_vencimento ASC,
                    cp.valor DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Count total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} cp 
                     LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
                     LEFT JOIN evento_colaboradores ec ON cp.tipo = 'colaborador' AND cp.referencia_id = ec.id
                     LEFT JOIN colaboradores c ON ec.id_colaborador = c.id
                     LEFT JOIN evento_outros_custos eoc ON cp.tipo = 'outro' AND cp.referencia_id = eoc.id
                     LEFT JOIN eventos ev ON (cp.evento_id = ev.id OR (cp.tipo = 'colaborador' AND ec.id_evento = ev.id) OR (cp.tipo = 'outro' AND eoc.evento_id = ev.id))
                     $whereClause";
        $totalResult = $this->query($countSql, $params);

        return [
            'data' => $results,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalResult[0]['total'] ?? 0,
                'last_page' => ceil(($totalResult[0]['total'] ?? 1) / $perPage),
            ]
        ];
    }

    /**
     * Buscar contas a pagar por fornecedor
     */
    public function findByFornecedor(int $idFornecedor): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE id_fornecedor = ? ORDER BY data_vencimento ASC",
            [$idFornecedor]
        );
    }

    /**
     * Buscar contas a pagar por status
     */
    public function findByStatus(string $status): array
    {
        return $this->query(
            "SELECT cp.*, f.nome_fantasia as fornecedor_nome 
             FROM {$this->table} cp 
             LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
             WHERE cp.status = ? 
             ORDER BY cp.data_vencimento ASC",
            [$status]
        );
    }

    /**
     * Retorna totais por status para dashboard
     */
    public function getTotais(): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as quantidade,
                    COALESCE(SUM(valor), 0) as total_valor
                FROM {$this->table}
                GROUP BY status";
        return $this->query($sql);
    }

    /**
     * Buscar contas vencidas (ainda nao pagas)
     */
    public function getVencidas(): array
    {
        return $this->query(
            "SELECT cp.*, f.nome_fantasia as fornecedor_nome 
             FROM {$this->table} cp 
             LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
             WHERE cp.status = 'PENDENTE' AND cp.data_vencimento < CURDATE() 
             ORDER BY cp.data_vencimento ASC"
        );
    }

    /**
     * Buscar contas que vencem hoje
     */
    public function getVencemHoje(): array
    {
        return $this->query(
            "SELECT cp.*, f.nome_fantasia as fornecedor_nome 
             FROM {$this->table} cp 
             LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
             WHERE cp.status = 'PENDENTE' AND cp.data_vencimento = CURDATE() 
             ORDER BY cp.data_vencimento ASC"
        );
    }

    /**
     * Buscar contas próximo vencimento (não pagas)
     */
    public function getProximasVencimento(int $limit = 5): array
    {
        return $this->query(
            "SELECT cp.*, f.nome_fantasia as fornecedor_nome 
             FROM {$this->table} cp 
             LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id 
             WHERE cp.status != 'PAGO' 
             AND cp.data_vencimento >= CURDATE() 
             ORDER BY cp.data_vencimento ASC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Buscar uma conta a pagar detalhada por ID
     */
    public function find(int $id): ?array
    {
        $results = $this->query(
            "SELECT cp.*, 
                    f.nome_fantasia as fornecedor_nome,
                    c.nome as colaborador_nome,
                    ev.nome_evento as evento_nome,
                    ev.os_cliente as evento_os_cliente,
                    ev.local_evento as evento_local,
                    ev.data_inicio as evento_data_inicio,
                    ev.data_fim as evento_data_fim,
                    -- Item info (cotação removida em jun/2026; tipo=fornecedor retorna NULL)
                    CASE
                        WHEN cp.tipo = 'colaborador' THEN ec.funcao
                        WHEN cp.tipo = 'outro' THEN eoc.descricao
                        ELSE NULL
                    END as item_nome,
                    CASE
                        WHEN cp.tipo = 'colaborador' THEN (DATEDIFF(ec.data_fim, ec.data_inicio) + 1)
                        ELSE NULL
                    END as item_dias,
                    CASE
                        WHEN cp.tipo = 'outro' THEN eoc.observacao
                        ELSE NULL
                    END as item_observacao,
                    -- Dados para pagamento
                    CASE
                        WHEN cp.tipo = 'fornecedor' THEN f.dados_pagamento
                        WHEN cp.tipo = 'colaborador' THEN c.dados_pagamento
                        ELSE NULL
                    END as dados_pagamento,
                    -- Pix / CPF / etc
                    CASE
                        WHEN cp.tipo = 'colaborador' THEN c.tipo_chave_pix
                        ELSE NULL
                    END as tipo_chave_pix,
                    CASE
                        WHEN cp.tipo = 'colaborador' THEN c.chavepix
                        ELSE NULL
                    END as chavepix,
                    CASE
                        WHEN cp.tipo = 'fornecedor' THEN f.cpf_cnpj
                        WHEN cp.tipo = 'colaborador' THEN c.cpf
                        ELSE NULL
                    END as cpf_cnpj
             FROM {$this->table} cp
             LEFT JOIN fornecedores f ON cp.id_fornecedor = f.id
             LEFT JOIN evento_colaboradores ec ON cp.tipo = 'colaborador' AND cp.referencia_id = ec.id
             LEFT JOIN colaboradores c ON ec.id_colaborador = c.id
             LEFT JOIN evento_outros_custos eoc ON cp.tipo = 'outro' AND cp.referencia_id = eoc.id
             LEFT JOIN eventos ev ON (cp.evento_id = ev.id OR (cp.tipo = 'colaborador' AND ec.id_evento = ev.id) OR (cp.tipo = 'outro' AND eoc.evento_id = ev.id))
             WHERE cp.id = ? LIMIT 1",
            [$id]
        );
        return $results[0] ?? null;
    }

    /**
     * Busca contas a pagar por tipo e referencia
     */
    public function findByReferencia(string $tipo, int $referenciaId): array
    {
        return Connection::query(
            "SELECT * FROM contas_pagar WHERE tipo = ? AND referencia_id = ?",
            [$tipo, $referenciaId]
        );
    }

    /**
     * Verifica se já existe conta a pagar para a referência
     */
    public function existsByReferencia(string $tipo, int $referenciaId): bool
    {
        $result = Connection::query(
            "SELECT COUNT(*) as total FROM contas_pagar WHERE tipo = ? AND referencia_id = ?",
            [$tipo, $referenciaId]
        );
        return (int) ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * Atualiza conta a pagar com whitelist de colunas
     */
    public function atualizarSeguro(int $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        $allowed = ['status', 'valor_pago', 'data_pagamento', 'tipo_pagamento', 'parcelas_qtd', 'entrada_valor', 'descricao', 'comprovante_anexo'];
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, $allowed, true)) {
                continue;
            }
            $sets[] = "$k = ?";
            $params[] = $v;
        }
        if (empty($sets)) {
            return 0;
        }
            $params[] = $id;
        return Connection::exec(
            "UPDATE contas_pagar SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?",
            $params
        );
    }

    /**
     * Deleta conta a pagar e reverte flags nos registros de origem
     */
    public function deleteWithCascade(int $id): int
    {
        $conta = Connection::query(
            "SELECT id, tipo, referencia_id FROM contas_pagar WHERE id = ? LIMIT 1",
            [$id]
        );

        if (empty($conta)) {
            throw new \Exception('Conta nao encontrada.');
        }

        $tipo = $conta[0]['tipo'];
        $refId = (int)$conta[0]['referencia_id'];

        // Reverter flag conforme tipo de origem
        if ($tipo === 'outro' && $refId > 0) {
            Connection::exec(
                "UPDATE evento_outros_custos SET enviado_contas_pagar='N', conta_pagar_id=NULL, updated_at=NOW() WHERE id=?",
                [$refId]
            );
        } elseif ($tipo === 'fornecedor' && $refId > 0) {
            Connection::exec(
                "UPDATE produto_evento_sublocacao SET status='pendente' WHERE id=?",
                [$refId]
            );
        } elseif ($tipo === 'colaborador' && $refId > 0) {
            Connection::exec(
                "UPDATE evento_colaboradores SET enviar_pagamento='N' WHERE id=?",
                [$refId]
            );
        }

        return Connection::exec(
            "DELETE FROM contas_pagar WHERE id = ?",
            [$id]
        );
    }
}

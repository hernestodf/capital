<?php

namespace App\Repository;

use App\Database\Connection;

class FornecedorRepository extends BaseRepository
{
    protected string $table = 'fornecedores';
    protected array $fillable = [
        'id_categoria', 'id_subcategoria', 'cpf_cnpj', 'inscricao_estadual',
        'nome_fantasia', 'razao_social',
        // compat (sincronizados com fin_email/fin_telefone — usados por cotacao/whatsapp)
        'telefone', 'email',
        // Contato Financeiro
        'fin_nome', 'fin_telefone', 'fin_email',
        'fin_nome2', 'fin_telefone2', 'fin_email2',
        // Contato Comercial
        'com_nome', 'com_telefone', 'com_email',
        'com_nome2', 'com_telefone2', 'com_email2',
        // Presença Online
        'site', 'instagram',
        // Endereço
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'estado_para_trabalho',
        // Outros
        'observacao', 'status', 'dados_pagamento',
    ];

    /**
     * Buscar fornecedor por email (busca em email e fin_email para compatibilidade).
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE email = ? OR fin_email = ? LIMIT 1",
            [$email, $email]
        );
        return $stmt[0] ?? null;
    }

    /**
     * Retorna fornecedores ativos.
     */
    public function getAtivos(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nome_fantasia"
        );
    }

    /**
     * Lista todos com JOIN para nomes de categoria e subcategoria.
     */
    public function all(): array
    {
        $sql = "SELECT f.*, c.categoria as categoria_nome, s.subcategoria as subcategoria_nome
                FROM {$this->table} f
                LEFT JOIN categorias c ON f.id_categoria = c.id
                LEFT JOIN subcategorias s ON f.id_subcategoria = s.id
                ORDER BY f.nome_fantasia";
        return $this->query($sql);
    }

    /**
     * Busca fornecedores com filtro e paginação.
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $where  = '';
        $params = [];

        if (!empty($term)) {
            $where = "WHERE f.nome_fantasia LIKE ? OR f.razao_social LIKE ?
                         OR f.cpf_cnpj LIKE ?
                         OR f.email LIKE ? OR f.fin_email LIKE ?
                         OR f.fin_nome LIKE ? OR f.com_nome LIKE ?";
            $t      = "%{$term}%";
            $params = [$t, $t, $t, $t, $t, $t, $t];
        }

        $sql = "SELECT f.*, c.categoria as categoria_nome, s.subcategoria as subcategoria_nome
                FROM {$this->table} f
                LEFT JOIN categorias c ON f.id_categoria = c.id
                LEFT JOIN subcategorias s ON f.id_subcategoria = s.id
                $where
                ORDER BY f.nome_fantasia
                LIMIT :limit OFFSET :offset";
        $stmt = Connection::get()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key : ":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countSql    = "SELECT COUNT(*) as total FROM {$this->table} f $where";
        $totalResult = $this->query($countSql, $params);

        return [
            'data'       => $results,
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $totalResult[0]['total'] ?? 0,
                'last_page' => ceil(($totalResult[0]['total'] ?? 1) / $perPage),
            ],
        ];
    }
}

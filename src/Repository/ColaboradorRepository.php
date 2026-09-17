<?php

namespace App\Repository;

use App\Database\Connection;

class ColaboradorRepository extends BaseRepository
{
    protected string $table = 'colaboradores';
    protected array $fillable = [
        'origem', 'tipo', 'estado_para_trabalho', 'nome', 'cpf', 'telefone',
        'email', 'atua_como', 'id_funcao', 'foto', 'cep', 'endereco', 'bairro',
        'cidade', 'estado', 'tipo_chave_pix', 'chavepix', 'observacao', 'ativo', 'dados_pagamento'
    ];

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where = "WHERE c.nome LIKE ? OR c.email LIKE ? OR c.telefone LIKE ?";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $offset = ($page - 1) * $perPage;

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} c {$where}";
        $stmt = Connection::get()->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // Get page data
        $sql = "SELECT c.id, c.origem, c.tipo, c.estado_para_trabalho, c.nome, c.cpf, c.telefone, c.email,
                       c.id_funcao, COALESCE(f.nome, c.atua_como) as atua_como,
                       CASE
                           WHEN c.foto LIKE 'uploads/%' THEN c.foto
                           WHEN c.foto IS NOT NULL AND c.foto != '' THEN 'base64'
                           ELSE ''
                       END as foto,
                       c.cep, c.endereco, c.bairro, c.cidade, c.estado, c.tipo_chave_pix, c.chavepix, c.observacao, c.ativo, c.dados_pagamento
                FROM {$this->table} c
                LEFT JOIN funcoes f ON f.id = c.id_funcao
                {$where} ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
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
                'total' => (int) $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage),
            ],
        ];
    }

    public function findByToken(string $token): ?array
    {
        $results = Connection::query(
            "SELECT c.* FROM {$this->table} c
             INNER JOIN colaboradores_verificacao cv ON c.id = cv.colaborador_id
             WHERE cv.token = ?",
            [$token]
        );
        return $results[0] ?? null;
    }

    public function toggleStatus(int $id): int
    {
        $colaborador = $this->find($id);
        if (!$colaborador) {
            throw new \Exception('Colaborador não encontrado');
        }

        $newStatus = $colaborador['ativo'] == 1 ? 0 : 1;
        Connection::exec(
            "UPDATE {$this->table} SET ativo = ? WHERE id = ?",
            [$newStatus, $id]
        );
        return $newStatus;
    }

    public function countByStatus(): array
    {
        $results = Connection::query(
            "SELECT 
                COUNT(*) as total,
                SUM(ativo = 1) as ativos,
                SUM(ativo = 0) as inativos
             FROM {$this->table}"
        );
        return $results[0] ?? ['total' => 0, 'ativos' => 0, 'inativos' => 0];
    }
}

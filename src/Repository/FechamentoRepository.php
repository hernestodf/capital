<?php

namespace App\Repository;

use App\Database\Connection;

/**
 * Repository para queries de fechamento de eventos
 * Nota: Usa $table = 'eventos' apenas para herdar de BaseRepository.
 * Todos os metodos usam SQL direto via Connection::query/exec.
 */
class FechamentoRepository extends BaseRepository
{
    protected string $table = 'eventos';

    /**
     * Listar colaboradores do evento com presencas e valores
     */
    public function getColaboradores(int $eventoId): array
    {
        return Connection::query(
            "SELECT ec.id as id_alocacao, ec.id_colaborador, ec.funcao,
                    ec.data_inicio, ec.data_fim, ec.hora_inicio, ec.hora_fim,
                    ec.valor_diaria, ec.enviar_pagamento, ec.status,
                    ec.data_vencimento_pagamento,
                    c.nome, c.telefone, c.email, c.chavepix, c.tipo_chave_pix,
                    DATEDIFF(ec.data_fim, ec.data_inicio) + 1 as dias,
                    COALESCE(he.total_horas_extras, 0) as total_horas_extras,
                    ((DATEDIFF(ec.data_fim, ec.data_inicio) + 1) * ec.valor_diaria)
                    + COALESCE(he.total_horas_extras, 0) as valor_total,
                    COALESCE(ep.dias_presentes, 0) as dias_presentes,
                    COALESCE(ep.dias_total, 0) as dias_total,
                    CASE WHEN cp.id IS NOT NULL THEN 1 ELSE 0 END as ja_enviado_pagamento
             FROM evento_colaboradores ec
             INNER JOIN colaboradores c ON c.id = ec.id_colaborador
             LEFT JOIN (
                 SELECT id_alocacao, SUM(valor) as total_horas_extras
                 FROM evento_colaborador_horas_extras
                 GROUP BY id_alocacao
             ) he ON he.id_alocacao = ec.id
             LEFT JOIN (
                 SELECT id_alocacao,
                        COUNT(*) as dias_total,
                        SUM(CASE WHEN status IN ('parcial', 'completo') THEN 1 ELSE 0 END) as dias_presentes
                 FROM evento_colaborador_presencas
                 GROUP BY id_alocacao
             ) ep ON ep.id_alocacao = ec.id
             LEFT JOIN contas_pagar cp ON cp.tipo = 'colaborador' AND cp.referencia_id = ec.id
             WHERE ec.id_evento = ? AND ec.status = 'A'
             ORDER BY c.nome",
            [$eventoId]
        );
    }

    /**
     * Listar presencas de uma alocacao
     */
    public function getPresencas(int $idAlocacao): array
    {
        return Connection::query(
            "SELECT data, foto_entrada, geo_entrada_lat, geo_entrada_lng,
                    hora_entrada_real, foto_saida, geo_saida_lat, geo_saida_lng,
                    hora_saida_real, status
             FROM evento_colaborador_presencas
             WHERE id_alocacao = ?
             ORDER BY data",
            [$idAlocacao]
        );
    }

    /**
     * Listar fornecedores vencedores de cotacoes do evento
     */
    public function getFornecedores(int $eventoId): array
    {
        $fornecedores = Connection::query(
            "SELECT pes.id as id_cotacao, COALESCE(pes.total, pes.quantidade * pes.valor_unit) as valor_proposto,
                    f.id as id_fornecedor, f.nome_fantasia, f.cpf_cnpj, f.telefone, f.email,
                    f.dados_pagamento,
                    COALESCE(pes.produto, pes.produto_fornecedor, pe.produto) as servico,
                    pe.qtd, pe.valor_unit, pe.total_item, pe.dias,
                    pe.observacao_montagem,
                    s.nome_sala as sala, s.id as sala_id,
                    EXISTS(
                        SELECT 1 FROM contas_pagar cp
                        WHERE cp.tipo = 'fornecedor' AND cp.referencia_id = pes.id
                    ) as ja_enviado_pagamento
             FROM produto_evento_sublocacao pes
             INNER JOIN fornecedores f ON f.id = pes.id_fornecedor
             INNER JOIN produtos_evento pe ON pe.id = pes.id_produto_evento
             LEFT JOIN salas s ON s.id = pe.id_sala
             WHERE pe.id_evento = ? AND pes.status != 'cancelado'
             ORDER BY f.nome_fantasia",
            [$eventoId]
        );

        // Buscar informacoes de contas a pagar para cada fornecedor
        foreach ($fornecedores as &$fornecedor) {
            if ($fornecedor['ja_enviado_pagamento']) {
                $contas = Connection::query(
                    "SELECT id, descricao, valor, valor_pago, data_vencimento, data_pagamento, status, 
                            tipo_pagamento, parcelas_qtd, parcelas_valor, entrada_valor
                     FROM contas_pagar 
                     WHERE tipo = 'fornecedor' AND referencia_id = ?
                     ORDER BY data_vencimento ASC",
                    [$fornecedor['id_cotacao']]
                );
                $fornecedor['contas_pagar_info'] = $contas;
            }
        }

        return $fornecedores;
    }

    /**
     * Buscar contas a pagar por referencia (fornecedor)
     */
    public function getContasPagarByReferencia(int $idCotacao): array
    {
        return Connection::query(
            "SELECT id, descricao, valor, valor_pago, data_vencimento, data_pagamento, status, 
                    tipo_pagamento, parcelas_qtd, parcelas_valor, entrada_valor
             FROM contas_pagar 
             WHERE tipo = 'fornecedor' AND referencia_id = ?
             ORDER BY data_vencimento ASC",
            [$idCotacao]
        );
    }

    /**
     * Totais financeiros do evento
     * Inclui custos projetados (alocacoes e cotacoes) + custos reais (contas a pagar)
     * + outros custos manuais (tipo='outro' em contas_pagar)
     */
    public function getTotais(int $eventoId): array
    {
        // Custo projetado colaboradores (soma dos valores das alocacoes + horas extras)
        $custoColaboradores = Connection::query(
            "SELECT COALESCE(SUM(
                ((DATEDIFF(ec.data_fim, ec.data_inicio) + 1) * ec.valor_diaria) + COALESCE((
                    SELECT SUM(valor)
                    FROM evento_colaborador_horas_extras
                    WHERE id_alocacao = ec.id
                ), 0)
             ), 0) as total
             FROM evento_colaboradores ec
             WHERE ec.id_evento = ? AND ec.status = 'A'",
            [$eventoId]
        )[0]['total'] ?? 0;

        // Custo fornecedores (soma dos custo_unit dos produtos com fornecedor vencedor)
        $custoFornecedores = Connection::query(
            "SELECT COALESCE(SUM(COALESCE(pes.total, pes.quantidade * pes.valor_unit)), 0) as total
             FROM produto_evento_sublocacao pes
             INNER JOIN produtos_evento pe ON pe.id = pes.id_produto_evento
             WHERE pe.id_evento = ? AND pes.status != 'cancelado'",
            [$eventoId]
        )[0]['total'] ?? 0;

        // Custo outros (custos manuais via evento_outros_custos)
        $custoOutros = Connection::query(
            "SELECT COALESCE(SUM(valor), 0) as total
             FROM evento_outros_custos
             WHERE evento_id = ?",
            [$eventoId]
        )[0]['total'] ?? 0;

        // Receita (soma dos produtos do evento)
        $receita = Connection::query(
            "SELECT COALESCE(SUM(total_item), 0) as total
             FROM produtos_evento
             WHERE id_evento = ?",
            [$eventoId]
        )[0]['total'] ?? 0;

        $custoTotal = (float) $custoColaboradores + (float) $custoFornecedores + (float) $custoOutros;
        $lucro = (float) $receita - $custoTotal;
        $margem = $receita > 0 ? ($lucro / $receita) * 100 : 0;

        return [
            'total_colaboradores' => (float) $custoColaboradores,
            'total_fornecedores' => (float) $custoFornecedores,
            'total_outros' => (float) $custoOutros,
            'custo_total' => $custoTotal,
            'receita' => (float) $receita,
            'lucro' => $lucro,
            'margem' => round($margem, 2),
        ];
    }

    public function getProdutosEvento(int $eventoId): array
    {
        return Connection::query(
            "SELECT pe.id, pe.produto, pe.qtd, pe.valor_unit, pe.dias, pe.total_item,
                    pe.observacao_montagem, pe.custo_unit,
                    s.nome_sala AS sala_nome, s.id AS sala_id
             FROM produtos_evento pe
             LEFT JOIN salas s ON pe.id_sala = s.id
             WHERE pe.id_evento = ? AND pe.status = 1
             ORDER BY s.ordem, s.nome_sala, pe.produto",
            [$eventoId]
        );
    }

    /**
     * Verificar se ja existe conta a pagar para referencia
     */
    public function contaPagarExists(string $tipo, int $referenciaId): bool
    {
        $result = Connection::query(
            "SELECT id FROM contas_pagar WHERE tipo = ? AND referencia_id = ? LIMIT 1",
            [$tipo, $referenciaId]
        );
        return !empty($result);
    }

    /**
     * Criar conta a pagar
     */
    public function criarContaPagar(array $data): int
    {
        $allowedCols = ['evento_id','tipo','referencia_id','id_fornecedor','descricao','valor','data_vencimento','status','tipo_pagamento','parcelas_qtd','entrada_valor','observacao','comprovante_anexo','nota_fiscal','numero_nf'];
        $keys = array_intersect(array_keys($data), $allowedCols);
        if (empty($keys)) {
            throw new \Exception('Nenhum campo valido para inserir.');
        }
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        Connection::exec(
            "INSERT INTO contas_pagar ($fields) VALUES ($placeholders)",
            array_values(array_intersect_key($data, array_flip($keys)))
        );

        return (int) Connection::lastInsertId();
    }

    /**
     * Marcar alocacao como enviar_pagamento com data de vencimento
     */
    public function marcarEnvioPagamentoColaborador(int $idAlocacao, string $dataVencimento): int
    {
        return Connection::exec(
            "UPDATE evento_colaboradores SET enviar_pagamento = 'S', data_vencimento_pagamento = ? WHERE id = ?",
            [$dataVencimento, $idAlocacao]
        );
    }

    /**
     * Listar outros custos do evento (de evento_outros_custos, com status do contas_pagar)
     */
    public function getOutrosCustos(int $eventoId): array
    {
        return Connection::query(
            "SELECT eoc.id, eoc.evento_id, eoc.descricao, eoc.valor, eoc.data_vencimento,
                    eoc.observacao, eoc.nota_fiscal, eoc.enviado_contas_pagar, eoc.data_envio_contas_pagar,
                    eoc.conta_pagar_id, eoc.created_at,
                    cp.status, cp.data_pagamento, cp.valor_pago, cp.tipo_pagamento
             FROM evento_outros_custos eoc
             LEFT JOIN contas_pagar cp ON cp.id = eoc.conta_pagar_id
             WHERE eoc.evento_id = ?
             ORDER BY eoc.data_vencimento ASC, eoc.created_at ASC",
            [$eventoId]
        );
    }

    /**
     * Criar outro custo em evento_outros_custos (não entra em contas_pagar ainda)
     */
    public function criarOutroCusto(array $data): int
    {
        Connection::exec(
            "INSERT INTO evento_outros_custos (evento_id, descricao, valor, data_vencimento, observacao, nota_fiscal)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['evento_id'],
                $data['descricao'],
                $data['valor'],
                $data['data_vencimento'],
                $data['observacao'] ?? null,
                $data['nota_fiscal'] ?? null,
            ]
        );
        return (int) Connection::lastInsertId();
    }

    /**
     * Atualizar outro custo (somente se ainda não enviado para pagamento)
     */
    public function atualizarOutroCusto(int $id, array $data): int
    {
        $sets   = ['descricao=?', 'valor=?', 'data_vencimento=?', 'observacao=?'];
        $params = [$data['descricao'], $data['valor'], $data['data_vencimento'], $data['observacao'] ?? null];

        // Atualiza nota_fiscal somente se um novo arquivo foi enviado
        if (!empty($data['nota_fiscal'])) {
            $sets[]   = 'nota_fiscal=?';
            $params[] = $data['nota_fiscal'];
        }

        $sets[]   = 'updated_at=NOW()';
        $params[] = $id;

        return Connection::exec(
            "UPDATE evento_outros_custos SET " . implode(', ', $sets) . " WHERE id=? AND enviado_contas_pagar='N'",
            $params
        );
    }

    /**
     * Excluir outro custo (somente se ainda não enviado para pagamento)
     */
    public function deletarOutroCusto(int $id): int
    {
        return Connection::exec(
            "DELETE FROM evento_outros_custos WHERE id=? AND enviado_contas_pagar='N'",
            [$id]
        );
    }

    /**
     * Enviar outro custo para pagamento:
     * 1. Cria registro em contas_pagar (tipo='outro')
     * 2. Marca evento_outros_custos.enviado_contas_pagar='S'
     */
    public function enviarOutroParaPagamento(int $id): int
    {
        $rows = Connection::query(
            "SELECT * FROM evento_outros_custos WHERE id=? AND enviado_contas_pagar='N' LIMIT 1",
            [$id]
        );

        if (empty($rows)) {
            throw new \Exception('Custo nao encontrado ou ja enviado para pagamento.');
        }

        $outro = $rows[0];

        // Criar registro em contas_pagar
        $contaData = [
            'evento_id'       => $outro['evento_id'],
            'tipo'            => 'outro',
            'referencia_id'   => $id,
            'descricao'       => $outro['descricao'],
            'valor'           => $outro['valor'],
            'data_vencimento' => $outro['data_vencimento'],
            'status'          => 'PENDENTE',
        ];
        if (!empty($outro['nota_fiscal'])) {
            $contaData['nota_fiscal'] = $outro['nota_fiscal'];
        }
        $contaId = $this->criarContaPagar($contaData);

        // Marcar como enviado e guardar referência
        Connection::exec(
            "UPDATE evento_outros_custos
             SET enviado_contas_pagar='S', data_envio_contas_pagar=NOW(), conta_pagar_id=?, updated_at=NOW()
             WHERE id=?",
            [$contaId, $id]
        );

        return $contaId;
    }

    /**
     * Atualizar conta a pagar (usado para fornecedores, colaboradores etc.)
     */
    public function atualizarContaPagar(int $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        $allowed = ['status', 'valor', 'valor_pago', 'data_vencimento', 'data_pagamento', 'tipo_pagamento', 'parcelas_qtd', 'entrada_valor', 'descricao', 'comprovante_anexo', 'nota_fiscal', 'observacao', 'numero_nf'];
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
     * Buscar conta a pagar por ID
     */
    public function getContaPagarById(int $id): ?array
    {
        $result = Connection::query(
            "SELECT id, evento_id, tipo, referencia_id, id_fornecedor, descricao,
                    valor, valor_pago, data_vencimento, data_pagamento, status,
                    tipo_pagamento, parcelas_qtd, entrada_valor
             FROM contas_pagar WHERE id = ? LIMIT 1",
            [$id]
        );
        return $result[0] ?? null;
    }

    /**
     * Deletar conta a pagar por ID
     */
    public function deletarContaPagar(int $id): int
    {
        return Connection::exec(
            "DELETE FROM contas_pagar WHERE id = ?",
            [$id]
        );
    }

    /**
     * Listar horas extras de uma alocacao
     */
    public function getHorasExtras(int $idAlocacao): array
    {
        return Connection::query(
            "SELECT id, data, horas, valor, motivo
             FROM evento_colaborador_horas_extras
             WHERE id_alocacao = ?
             ORDER BY data ASC, id ASC",
            [$idAlocacao]
        );
    }

    /**
     * Lancar nova hora extra
     */
    public function lancarHoraExtra(array $data): int
    {
        Connection::exec(
            "INSERT INTO evento_colaborador_horas_extras (id_alocacao, data, horas, valor, motivo)
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['id_alocacao'],
                $data['data'],
                $data['horas'],
                $data['valor'],
                $data['motivo']
            ]
        );
        return (int) Connection::lastInsertId();
    }

    /**
     * Deletar hora extra por ID
     */
    public function deletarHoraExtra(int $id): int
    {
        return Connection::exec(
            "DELETE FROM evento_colaborador_horas_extras WHERE id = ?",
            [$id]
        );
    }

    // Nota: marcarContaPagarComoPago() removido — pagamento gerenciado em ContasPagarController
}

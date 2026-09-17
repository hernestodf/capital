<?php
namespace App\Repository;

use App\Database\Connection;

class CotacaoMensagemRepository
{
    protected string $table = 'item_cotacoes_mensagens';

    /**
     * Thread de mensagens entre cotação e fornecedor
     */
    public function findByCotacaoFornecedor(int $idCotacao, int $idFornecedor): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT m.id, m.id_cotacao, m.id_fornecedor, m.tipo, m.remetente,
                    m.destinatario, m.assunto, m.corpo, m.message_id_email,
                    m.in_reply_to, m.lido, m.created_at,
                    a.id as anexo_id, a.nome_arquivo, a.path, a.mime_type, a.tamanho
             FROM item_cotacoes_mensagens m
             LEFT JOIN item_cotacoes_anexos a ON a.id_mensagem = m.id
             WHERE m.id_cotacao = ? AND m.id_fornecedor = ?
             ORDER BY m.created_at ASC"
        );
        $stmt->execute([$idCotacao, $idFornecedor]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupar anexos por mensagem (single query com JOIN)
        $messages = [];
        $msgIndex = [];
        foreach ($rows as $row) {
            $msgId = $row['id'];
            if (!isset($msgIndex[$msgId])) {
                $msgIndex[$msgId] = count($messages);
                $messages[] = [
                    'id' => $row['id'],
                    'id_cotacao' => $row['id_cotacao'],
                    'id_fornecedor' => $row['id_fornecedor'],
                    'tipo' => $row['tipo'],
                    'remetente' => $row['remetente'],
                    'destinatario' => $row['destinatario'],
                    'assunto' => $row['assunto'],
                    'corpo' => $row['corpo'],
                    'message_id_email' => $row['message_id_email'],
                    'in_reply_to' => $row['in_reply_to'],
                    'lido' => $row['lido'],
                    'created_at' => $row['created_at'],
                    'anexos' => []
                ];
            }
            if (!empty($row['anexo_id'])) {
                $messages[$msgIndex[$msgId]]['anexos'][] = [
                    'nome_arquivo' => $row['nome_arquivo'],
                    'path' => $row['path'],
                    'mime_type' => $row['mime_type'],
                    'tamanho' => $row['tamanho']
                ];
            }
        }

        return $messages;
    }

    /**
     * Salvar mensagem enviada
     */
    public function create(array $data): int
    {
        $stmt = Connection::get()->prepare(
            "INSERT INTO item_cotacoes_mensagens
             (id_cotacao, id_fornecedor, tipo, remetente, destinatario, assunto, corpo, message_id_email, in_reply_to)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['id_cotacao'],
            $data['id_fornecedor'],
            $data['tipo'],
            $data['remetente'],
            $data['destinatario'],
            $data['assunto'],
            $data['corpo'],
            $data['message_id_email'] ?? null,
            $data['in_reply_to'] ?? null
        ]);
        return (int) Connection::get()->lastInsertId();
    }

    /**
     * Salvar anexo
     */
    public function saveAnexo(int $idMensagem, array $data): bool
    {
        $stmt = Connection::get()->prepare(
            "INSERT INTO item_cotacoes_anexos (id_mensagem, nome_arquivo, path, mime_type, tamanho)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $idMensagem,
            $data['nome_arquivo'],
            $data['path'],
            $data['mime_type'] ?? '',
            $data['tamanho'] ?? 0
        ]);
    }

    /**
     * Buscar fornecedores que tem mensagens para uma cotacao
     * 
     * Retorna colunas com aliases que o JavaScript espera:
     * - fornecedor_nome (não nome_fantasia)
     * - fornecedor_email (não email)
     * - nao_lidas (contador de mensagens não lidas)
     */
    public function findFornecedoresByCotacao(int $idCotacao): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT DISTINCT m.id_fornecedor, 
                    f.nome_fantasia as fornecedor_nome, 
                    f.email as fornecedor_email,
                    COUNT(m.id) as num_mensagens,
                    SUM(CASE WHEN m.tipo = 'recebido' AND m.lido = 'N' THEN 1 ELSE 0 END) as nao_lidas
             FROM item_cotacoes_mensagens m
             INNER JOIN fornecedores f ON f.id = m.id_fornecedor
             WHERE m.id_cotacao = ?
             GROUP BY m.id_fornecedor, f.nome_fantasia, f.email
             ORDER BY f.nome_fantasia"
        );
        $stmt->execute([$idCotacao]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Contar mensagens nao lidas de um fornecedor
     */
    public function countUnread(int $idCotacao, int $idFornecedor): int
    {
        $stmt = Connection::get()->prepare(
            "SELECT COUNT(*) FROM item_cotacoes_mensagens
             WHERE id_cotacao = ? AND id_fornecedor = ? AND tipo = 'recebido' AND lido = 'N'"
        );
        $stmt->execute([$idCotacao, $idFornecedor]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead(int $idCotacao, int $idFornecedor): bool
    {
        $stmt = Connection::get()->prepare(
            "UPDATE item_cotacoes_mensagens SET lido = 'S'
             WHERE id_cotacao = ? AND id_fornecedor = ? AND tipo = 'recebido'"
        );
        return $stmt->execute([$idCotacao, $idFornecedor]);
    }
}

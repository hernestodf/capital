<?php
namespace App\Service;

use App\Repository\CotacaoRepository;
use App\Repository\CotacaoMensagemRepository;
use App\Database\Connection;
use App\Core\Env;

class CotacaoService
{
    private CotacaoRepository $repo;
    private CotacaoMensagemRepository $msgRepo;
    private EmailService $emailService;

    public function __construct()
    {
        $this->repo = new CotacaoRepository();
        $this->msgRepo = new CotacaoMensagemRepository();
        $this->emailService = new EmailService();
    }

    /**
     * Listar propostas de um item
     */
    public function getPropostas(int $idProdutoEvento): array
    {
        return $this->repo->findByProdutoEvento($idProdutoEvento);
    }

    /**
     * Adicionar proposta de fornecedor
     */
    public function adicionarProposta(array $data): array
    {
        $idProdutoEvento = $data['id_produto_evento'];
        $idFornecedor = $data['id_fornecedor'];
        $valor = $data['valor_proposto'];

        // Buscar id_evento do produto_evento
        $itemData = $this->getItemData($idProdutoEvento);
        if (!$itemData) {
            return ['success' => false, 'error' => 'Item nao encontrado'];
        }
        $data['id_evento'] = $itemData['id_evento'];

        // Verificar se ja existe
        if ($this->repo->exists($idProdutoEvento, $idFornecedor)) {
            // Atualizar valor
            $stmt = Connection::get()->prepare(
                "UPDATE item_cotacoes SET valor_proposto = ?, status = 'com_proposta'
                 WHERE id_produto_evento = ? AND id_fornecedor = ?"
            );
            $stmt->execute([$valor, $idProdutoEvento, $idFornecedor]);
            return ['success' => true, 'message' => 'Proposta atualizada'];
        }

        $data['status'] = 'aguardando';
        $id = $this->repo->create($data);

        return $id > 0
            ? ['success' => true, 'id' => $id]
            : ['success' => false, 'error' => 'Erro ao criar proposta'];
    }

    /**
     * Marcar vencedor
     * 1. Desmarca anterior
     * 2. Marca novo
     * 3. Atualiza custo_unit em produtos_evento
     */
    public function marcarVencedor(int $idCotacao, float $valor): array
    {
        $cotacao = $this->repo->find($idCotacao);
        if (!$cotacao) {
            return ['success' => false, 'error' => 'Cotação não encontrada'];
        }

        $idProdutoEvento = $cotacao['id_produto_evento'];
        $idEvento = $cotacao['id_evento'];

        try {
            // Desmarca anterior
            $this->repo->unmarkWinner($idProdutoEvento);

            // Marca novo vencedor + atualiza custo
            $this->repo->markWinner($idCotacao, $idProdutoEvento, $valor);

            return [
                'success' => true,
                'custo_unit' => $valor,
                'fornecedor' => $cotacao['fornecedor_nome']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Criar/atualizar proposta e marcar como vencedor em um unico passo (atalho rapido)
     */
    public function adicionarEMarcarVencedor(int $idProdutoEvento, int $idFornecedor, float $valor): array
    {
        $itemData = $this->getItemData($idProdutoEvento);
        if (!$itemData) {
            return ['success' => false, 'error' => 'Item nao encontrado'];
        }

        $propResult = $this->adicionarProposta([
            'id_produto_evento' => $idProdutoEvento,
            'id_fornecedor'     => $idFornecedor,
            'id_evento'         => $itemData['id_evento'],
            'valor_proposto'    => $valor,
            'status'            => 'com_proposta',
        ]);
        if (!($propResult['success'] ?? false)) {
            return $propResult;
        }

        // Obter o id da cotacao (criada ou atualizada)
        $cotacaoId = $propResult['id'] ?? null;
        if (!$cotacaoId) {
            foreach ($this->repo->findByProdutoEvento($idProdutoEvento) as $c) {
                if ((int)$c['id_fornecedor'] === $idFornecedor) {
                    $cotacaoId = (int)$c['id'];
                    break;
                }
            }
        }
        if (!$cotacaoId) {
            return ['success' => false, 'error' => 'Proposta nao localizada apos criacao'];
        }

        return $this->marcarVencedor($cotacaoId, $valor);
    }

    /**
     * Listar fornecedores ativos (id + nome) para autocomplete
     */
    public function listarFornecedoresAtivos(): array
    {
        return Connection::query(
            "SELECT id, nome_fantasia FROM fornecedores WHERE status = 1 ORDER BY nome_fantasia ASC"
        );
    }

    /**
     * Enviar solicitacao de cotação em massa para N fornecedores
     */
    public function enviarSolicitacaoMassa(array $dados): array
    {
        $fornecedores = $dados['fornecedores']; // [{id, email, nome_fantasia}, ...]
        $idEvento = $dados['id_evento'];
        $idProdutoEvento = $dados['id_produto_evento'];
        $mensagemCustom = $dados['mensagem'] ?? null;
        $anexoPath = $dados['anexo_path'] ?? null;

        // Buscar dados do evento e produtor (CCO)
        $eventoData = $this->getEventoData($idEvento);
        $ccEmail = $eventoData['produtor_email'] ?? '';

        // Buscar TODAS as cotacoes existentes UMA vez (evita N+1 queries)
        $existingCotacoes = $this->repo->findByProdutoEvento($idProdutoEvento);
        $cotacaoMap = [];
        foreach ($existingCotacoes as $c) {
            $cotacaoMap[$c['id_fornecedor']] = $c['id'];
        }

        $enviados = 0;
        $erros = [];
        $fornecedoresList = [];

        foreach ($fornecedores as $fornecedor) {
            // Criar cotacao atomicamente (evita race condition)
            $idCotacao = $cotacaoMap[$fornecedor['id']] ?? null;
            if (!$idCotacao) {
                $idCotacao = $this->repo->createOrIgnore([
                    'id_produto_evento' => $idProdutoEvento,
                    'id_fornecedor' => $fornecedor['id'],
                    'id_evento' => $idEvento,
                    'valor_proposto' => 0,
                    'status' => 'aguardando'
                ]);
                $cotacaoMap[$fornecedor['id']] = $idCotacao;
            }

            if ($idCotacao === 0) {
                // Fallback: buscar ID se createOrIgnore retornou 0
                $idCotacao = $this->repo->findIdByProdutoFornecedor($idProdutoEvento, $fornecedor['id']) ?? 0;
            }

            if ($idCotacao === 0) {
                $erros[] = $fornecedor['nome_fantasia'] . ': nao foi possivel criar a cotacao';
                continue;
            }

            $fornecedoresList[] = [
                'id' => $fornecedor['id'],
                'email' => $fornecedor['email'],
                'nome_fantasia' => $fornecedor['nome_fantasia'],
                'id_cotacao' => $idCotacao,
                'cc_email' => $ccEmail,
            ];
        }

        if (empty($fornecedoresList)) {
            return [
                'success' => false,
                'enviados' => 0,
                'erros' => $erros
            ];
        }

        $dadosComuns = array_merge($dados, [
            'data_inicio' => $eventoData['data_inicio'] ?? '-',
            'data_fim' => $eventoData['data_fim'] ?? '-',
            'local' => $eventoData['local_evento'] ?? '-',
            'qtd' => $dados['quantidade'] ?? '-',
            'nome_empresa' => Env::get('APP_NAME', 'SisLoc'),
        ]);

        $batchResult = $this->emailService->enviarSolicitacaoCotacaoBatch(
            $fornecedoresList,
            $dadosComuns,
            $mensagemCustom,
            $anexoPath
        );

        $batch = $batchResult['batch'] ?? [];

        foreach ($fornecedoresList as $f) {
            $idCotacao = $f['id_cotacao'];
            $res = $batch[$idCotacao] ?? ['success' => false, 'error' => 'Nao processado'];

            if ($res['success']) {
                $enviados++;
                // Salvar mensagem enviada
                $messageId = $res['message_id'] ?? $this->emailService->generateMessageId($idCotacao, $idEvento);
                $msgId = $this->msgRepo->create([
                    'id_cotacao' => $idCotacao,
                    'id_fornecedor' => $f['id'],
                    'tipo' => 'enviado',
                    'remetente' => Env::get('SMTP_USER', 'profox@sisloc.online'),
                    'destinatario' => $f['email'],
                    'assunto' => sprintf('[COT-%d-EVT-%d] %s — Fornecedor: %s', $idCotacao, $idEvento, $dados['nome_item'], $f['nome_fantasia']),
                    'corpo' => $mensagemCustom ?? 'Solicitação de proposta enviada.',
                    'message_id_email' => $messageId,
                ]);

                // Salvar anexo se existir
                if (!empty($anexoPath)) {
                    $fullPath = $anexoPath;
                    if (!str_starts_with($anexoPath, '/') && !str_starts_with($anexoPath, 'public/')) {
                        $fullPath = __DIR__ . '/../../public/' . $anexoPath;
                    } elseif (str_starts_with($anexoPath, 'public/')) {
                        $fullPath = __DIR__ . '/../../' . $anexoPath;
                    }

                    $this->msgRepo->saveAnexo($msgId, [
                        'nome_arquivo' => basename($anexoPath),
                        'path' => $anexoPath,
                        'mime_type' => @mime_content_type($fullPath) ?: 'application/octet-stream',
                        'tamanho' => @filesize($fullPath) ?: 0,
                    ]);
                }
            } else {
                $erros[] = $f['nome_fantasia'] . ': ' . ($res['error'] ?? 'Erro desconhecido');
            }
        }

        return [
            'success' => $enviados > 0,
            'enviados' => $enviados,
            'erros' => $erros
        ];
    }

    /**
     * Enviar mensagem/resposta para fornecedor
     */
    public function enviarMensagem(array $dados): array
    {
        $idCotacao = $dados['id_cotacao'];
        $idFornecedor = $dados['id_fornecedor'];

        $cotacao = $this->repo->find($idCotacao);
        if (!$cotacao) {
            return ['success' => false, 'error' => 'Cotação não encontrada'];
        }

        $eventoData = $this->getEventoData($cotacao['id_evento']);
        $ccEmail = $eventoData['produtor_email'] ?? '';

        $messageId = $this->emailService->generateMessageId($idCotacao, $cotacao['id_evento']);

        $result = $this->emailService->enviarMensagem(
            $cotacao['fornecedor_email'],
            $cotacao['fornecedor_nome'],
            $ccEmail,
            $dados['assunto'],
            $dados['corpo'],
            $dados['anexo_path'] ?? null,
            $messageId,
            $dados['in_reply_to'] ?? null
        );

        if ($result['success']) {
            $msgId = $this->msgRepo->create([
                'id_cotacao' => $idCotacao,
                'id_fornecedor' => $idFornecedor,
                'tipo' => 'enviado',
                'remetente' => Env::get('SMTP_USER', 'profox@sisloc.online'),
                'destinatario' => $cotacao['fornecedor_email'],
                'assunto' => $dados['assunto'],
                'corpo' => $dados['corpo'],
                'message_id_email' => $messageId,
                'in_reply_to' => $dados['in_reply_to'] ?? null,
            ]);

            if (!empty($dados['anexo_path'])) {
                $this->msgRepo->saveAnexo($msgId, [
                    'nome_arquivo' => $dados['anexo_nome'] ?: basename($dados['anexo_path']),
                    'path' => $dados['anexo_path'],
                    'mime_type' => $dados['anexo_mime'] ?? '',
                    'tamanho' => $dados['anexo_size'] ?? 0,
                ]);
            }
        }

        return $result;
    }

    /**
     * Thread de mensagens com fornecedor
     */
    public function getThread(int $idCotacao, int $idFornecedor): array
    {
        $this->msgRepo->markAsRead($idCotacao, $idFornecedor);
        return $this->msgRepo->findByCotacaoFornecedor($idCotacao, $idFornecedor);
    }

    /**
     * Fornecedores com mensagens para uma cotação
     */
    public function getFornecedoresComMensagens(int $idCotacao): array
    {
        return $this->msgRepo->findFornecedoresByCotacao($idCotacao);
    }

    /**
     * Fornecedores com mensagens por produto_evento (resolve quando nao temos id_cotacao)
     * 
     * Busca fornecedores que tem mensagens em QUALQUER cotação do produto_evento,
     * não apenas na primeira.
     */
    public function getFornecedoresByProdutoEvento(int $idProdutoEvento): array
    {
        // Buscar todas as cotacoes do produto_evento
        $cotacoes = $this->repo->findByProdutoEvento($idProdutoEvento);
        if (empty($cotacoes)) return [];

        // Build map: fornecedor_id -> cotacao_id
        $cotacaoMap = [];
        foreach ($cotacoes as $cot) {
            $cotacaoMap[$cot['id_fornecedor']] = $cot['id'];
        }

        // Buscar fornecedores com mensagens de TODAS as cotações
        // Fazendo UNION de todas as consultas por cotacao
        $allFornecedores = [];
        foreach ($cotacoes as $cotacao) {
            $fornecedores = $this->msgRepo->findFornecedoresByCotacao($cotacao['id']);
            foreach ($fornecedores as $f) {
                $idFornecedor = $f['id_fornecedor'];
                // Se fornecedor já existe, soma mensagens e nao_lidas
                if (isset($allFornecedores[$idFornecedor])) {
                    $allFornecedores[$idFornecedor]['num_mensagens'] += $f['num_mensagens'];
                    $allFornecedores[$idFornecedor]['nao_lidas'] += $f['nao_lidas'];
                } else {
                    $allFornecedores[$idFornecedor] = $f;
                    $allFornecedores[$idFornecedor]['id_cotacao'] = $cotacaoMap[$idFornecedor] ?? null;
                }
            }
        }

        // Re-index array
        return array_values($allFornecedores);
    }

    /**
     * Cotações ativas por produto_evento (para vincular mensagens)
     */
    public function getCotacoesAtivas(int $idProdutoEvento): array
    {
        return $this->repo->findByProdutoEvento($idProdutoEvento);
    }

    /**
     * Dados do evento + produtor email
     */
    private function getEventoData(int $idEvento): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT e.*, p.email as produtor_email, p.nome as produtor_nome
             FROM eventos e
             LEFT JOIN produtores p ON p.id = e.id_produtor
             WHERE e.id = ?"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Dados do item do evento
     */
    public function getItemData(int $idProdutoEvento): ?array
    {
        $stmt = Connection::get()->prepare(
            "SELECT pe.*, s.nome_sala
             FROM produtos_evento pe
             LEFT JOIN salas s ON s.id = pe.id_sala
             WHERE pe.id = ?"
        );
        $stmt->execute([$idProdutoEvento]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

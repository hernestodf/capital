<?php

namespace App\Service;

use App\Database\Connection;
use App\Repository\MontagemRepository;
use App\Repository\ProdutoEventoSerialRepository;
use App\Service\DevolucaoService;

/**
 * Service para montagem de seriais em salas de eventos
 */
class MontagemService extends BaseService
{
    protected string $entityName = 'Montagem';
    private MontagemRepository $repo;

    public function __construct()
    {
        $this->repo = new MontagemRepository();
        parent::__construct($this->repo);
    }

    /**
     * Inserir serial na montagem de um evento
     * Retorna array com sucesso/erro e mensagem detalhada
     */
    public function inserirSerial(string $serial, int $idEvento, ?int $idSala = null, string $observacao = ''): array
    {
        // 1. Verificar se o serial existe
        $serialInfo = $this->repo->findSerialInfo($serial);
        if (!$serialInfo) {
            return ['success' => false, 'error' => "Serial '{$serial}' nao encontrado no estoque."];
        }

        // 1b. Verificar se o serial tem status valido para montagem
        if ($serialInfo['serial_status'] !== 'ATIVO') {
            return ['success' => false, 'error' => "Serial '{$serial}' esta com status '{$serialInfo['serial_status']}' e nao pode ser montado. Apenas seriais ATIVO podem ser utilizados."];
        }

        // 2. Verificar se o serial ja esta em montagem em outro evento
        $emMontagem = $this->repo->findSerialEmMontagem($serial);
        if ($emMontagem && $emMontagem['evento_id'] != $idEvento) {
            return [
                'success' => false,
                'error' => "Serial '{$serial}' ja esta alocado no evento '{$emMontagem['nome_evento']}', sala '{$emMontagem['nome_sala']}'. Devolva o serial nesse evento antes de inserir aqui."
            ];
        }

        // 3. Verificar se o serial ja esta neste evento (ativo ou em sala)
        $jaNoEvento = $this->repo->findSerialNoEvento($serial, $idEvento);
        if ($jaNoEvento && $jaNoEvento['status'] !== 'devolvido') {
            // Serial ativo no evento - nao permitir duplicata
            return [
                'success' => false,
                'error' => "Serial '{$serial}' ja foi inserido neste evento (sala: '{$jaNoEvento['nome_sala']}', status: '{$jaNoEvento['status']}')."
            ];
        }
        
        // 4. Inserir ou reativar na montagem
        $novoStatus = $idSala ? 'montado' : 'pendente';
        $montagemId = null;
        if ($jaNoEvento && $jaNoEvento['status'] === 'devolvido') {
            // Reativar registro devolvido
            $this->repo->update($jaNoEvento['id'], [
                'id_sala' => $idSala,
                'observacao_item' => $observacao,
                'status' => $novoStatus
            ]);
            $montagemId = $jaNoEvento['id'];
        } else {
            // Novo registro
            $montagemId = $this->repo->inserirMontagem([
                'id_serial' => $serialInfo['id'],
                'id_evento' => $idEvento,
                'id_sala' => $idSala,
                'observacao_item' => $observacao,
                'status' => $novoStatus
            ]);
        }

        return [
            'success' => true,
            'data' => [
                'id' => $montagemId,
                'serial' => $serial,
                'produto' => $serialInfo['nome_produto'],
                'id_produto' => $serialInfo['id_produto']
            ]
        ];
    }

    /**
     * Inserir multiplos seriais em lote
     */
    public function inserirLote(array $seriais, int $idEvento, ?int $idSala = null, string $observacao = ''): array
    {
        $resultados = ['sucesso' => [], 'erros' => []];

        foreach ($seriais as $serial) {
            $serial = trim($serial);
            if (empty($serial)) continue;

            $result = $this->inserirSerial($serial, $idEvento, $idSala, $observacao);

            if ($result['success']) {
                $resultados['sucesso'][] = $result['data'];
            } else {
                $resultados['erros'][] = ['serial' => $serial, 'erro' => $result['error']];
            }
        }

        return [
            'success' => true,
            'message' => 'Lote processado',
            'detalhes' => [
                'sucesso' => count($resultados['sucesso']),
                'erros' => count($resultados['erros'])
            ],
            'seriais_inseridos' => $resultados['sucesso'],
            'erros' => $resultados['erros']
        ];
    }

    /**
     * Listar seriais pendentes de montagem
     */
    public function findPendentes(int $idEvento): array
    {
        return $this->repo->findPendentes($idEvento);
    }

    /**
     * Listar seriais em montagem agrupados por sala
     */
    public function findBySala(int $idEvento): array
    {
        $itens = $this->repo->findBySala($idEvento);

        // Agrupar por sala
        $agrupado = [];
        foreach ($itens as $item) {
            $salaId = $item['sala_id'];
            if (!isset($agrupado[$salaId])) {
                $agrupado[$salaId] = [
                    'sala_id' => $salaId,
                    'nome_sala' => $item['nome_sala'],
                    'orientacoes_montagem' => $item['orientacoes_montagem'],
                    'itens' => []
                ];
            }
            $agrupado[$salaId]['itens'][] = [
                'montagem_id' => $item['montagem_id'],
                'serial_id' => $item['serial_id'],
                'serial' => $item['serial'],
                'id_produto' => $item['id_produto'],
                'nome_produto' => $item['nome_produto'],
                'observacao_item' => $item['observacao_item'],
                'status' => $item['status']
            ];
        }

        return array_values($agrupado);
    }

    /**
     * Encaminhar serial pendente para uma sala + produto específico
     */
    public function encaminharParaSala(int $montagemId, int $idSala, ?int $idProdutoEvento = null): array
    {
        try {
            // Buscar dados completos do serial antes de atualizar
            $dados = $this->repo->findMontagemWithDetails($montagemId);

            if (!$dados) {
                return [
                    'success' => false,
                    'error' => 'Montagem não encontrada: ' . $montagemId
                ];
            }

            // Validar que a sala existe e pertence ao mesmo evento
            $salaExiste = \App\Database\Connection::query(
                "SELECT id FROM salas WHERE id = ? LIMIT 1",
                [$idSala]
            );
            if (empty($salaExiste)) {
                return [
                    'success' => false,
                    'error' => 'Sala nao encontrada'
                ];
            }

            // Se idProdutoEvento foi fornecido, validar que existe e pertence à sala
            if ($idProdutoEvento) {
                $produtoEvento = \App\Database\Connection::query(
                    "SELECT id FROM produtos_evento WHERE id = ? AND id_sala = ? LIMIT 1",
                    [$idProdutoEvento, $idSala]
                );
                if (empty($produtoEvento)) {
                    return [
                        'success' => false,
                        'error' => 'Produto não encontrado nesta sala'
                    ];
                }
            }

            // Atualizar sala, produto_evento e marcar como montado
            $idProdutoEventoAnterior = $dados['id_produto_evento'] ?? null;
            $updateData = ['id_sala' => $idSala, 'status' => 'montado'];
            if ($idProdutoEvento) {
                $updateData['id_produto_evento'] = $idProdutoEvento;
            }
            $this->repo->update($montagemId, $updateData);

            $pesRepo = new ProdutoEventoSerialRepository();

            // Se mudou de produto_evento, desfaz alocação anterior
            if ($idProdutoEventoAnterior && $idProdutoEventoAnterior !== $idProdutoEvento) {
                $this->desalocarSerial((int)$dados['id_serial'], (int)$idProdutoEventoAnterior, $pesRepo);
            }

            // Aloca serial no produto_evento atual
            if ($idProdutoEvento) {
                $this->alocarSerial((int)$dados['id_serial'], $idProdutoEvento, $pesRepo);
            }

            // Busca nome real do produto
            $produtoNome = $dados['produto'];
            if ($idProdutoEvento) {
                $produtoReal = Connection::query(
                    "SELECT produto FROM produtos_evento WHERE id = ? LIMIT 1",
                    [$idProdutoEvento]
                );
                if (!empty($produtoReal)) {
                    $produtoNome = $produtoReal[0]['produto'];
                }
            }

            return [
                'success' => true,
                'message' => 'Serial encaminhado para a sala',
                'data' => [
                    'id' => $dados['id'],
                    'serial' => $dados['serial'],
                    'produto' => $produtoNome,
                    'id_sala' => $idSala,
                    'id_produto_evento' => $idProdutoEvento
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao encaminhar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remover serial da sala (volta para pendentes)
     */
    public function removerDaSala(int $montagemId): array
    {
        try {
            // Buscar dados completos do serial antes de atualizar
            $dados = $this->repo->findMontagemWithDetails($montagemId);
            
            if (!$dados) {
                return [
                    'success' => false,
                    'error' => 'Montagem não encontrada: ' . $montagemId
                ];
            }
            
            $idProdutoEvento = $dados['id_produto_evento'] ?? null;

            // Desaloca do produto_evento antes de limpar o vínculo
            if ($idProdutoEvento) {
                $pesRepo = new ProdutoEventoSerialRepository();
                $this->desalocarSerial((int)$dados['id_serial'], (int)$idProdutoEvento, $pesRepo);
            }

            // Volta para pendente sem sala nem produto_evento
            $this->repo->update($montagemId, ['id_sala' => null, 'id_produto_evento' => null, 'status' => 'pendente']);

            return [
                'success' => true,
                'message' => 'Serial voltou para pendentes',
                'data' => [
                    'id' => $dados['id'],
                    'serial' => $dados['serial'],
                    'produto' => $dados['produto'],
                    'id_sala' => null
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao remover da sala: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Devolver serial da montagem
     */
    public function devolverSerial(int $montagemId): array
    {
        $entity = $this->findOrFail($montagemId);

        // Buscar dados completos antes de devolver
        $dados = $this->repo->findMontagemWithDetails($montagemId);

        // Desaloca do produto_evento antes de marcar como devolvido — sem isso, um
        // serial ja montado (id_produto_evento setado) que fosse devolvido direto
        // (sem passar por "Pendente" primeiro) deixava produto_evento_seriais preso
        // em status='alocado' e qtd_alocada nunca era decrementado.
        $idProdutoEvento = $dados['id_produto_evento'] ?? null;
        if ($idProdutoEvento) {
            $pesRepo = new ProdutoEventoSerialRepository();
            $this->desalocarSerial((int)$dados['id_serial'], (int)$idProdutoEvento, $pesRepo);
        }

        $this->repo->devolverSerial($montagemId);

        // Criar registro de devolucao para historico
        $devolucaoService = new DevolucaoService();
        $devolucaoService->processarUnico(
            (int)$dados['id_evento'],
            $dados['serial'],
            'A', // Aprovado
            null,
            null,
            'Sistema'
        );

        return [
            'success' => true,
            'data' => [
                'id' => $montagemId,
                'serial' => $dados ? ($dados['serial'] ?? '') : '',
                'produto' => $dados ? ($dados['produto'] ?? '') : ''
            ]
        ];
    }

    /**
     * Marcar serial como montado
     */
    public function marcarComoMontado(int $montagemId): array
    {
        $entity = $this->findOrFail($montagemId);
        $this->repo->marcarComoMontado($montagemId);
        return ['success' => true];
    }

    /**
     * Insere ou reativa registro em produto_evento_seriais e recalcula qtd_alocada.
     *
     * produto_evento_seriais.id_serial tem UNIQUE KEY (uk_serial_evento) — so pode
     * existir UMA linha por serial, independente do produto_evento. Por isso a busca
     * aqui e por id_serial sozinho (findBySerial), nao pelo par (id_serial, id_produto_evento):
     * se o serial ja tinha uma linha (mesmo devolvida) presa a OUTRO produto_evento e o
     * codigo tentasse fazer um INSERT novo, violaria a constraint. Reassociar a linha
     * existente via UPDATE evita isso e mantem a linha unica por serial sempre em dia.
     */
    private function alocarSerial(int $idSerial, int $idProdutoEvento, ProdutoEventoSerialRepository $pesRepo): void
    {
        $existing = $pesRepo->findBySerial($idSerial);
        $idProdutoEventoAntigo = null;

        if ($existing) {
            if ((int)$existing['id_produto_evento'] !== $idProdutoEvento) {
                $idProdutoEventoAntigo = (int)$existing['id_produto_evento'];
            }
            $pesRepo->update($existing['id'], [
                'id_produto_evento' => $idProdutoEvento,
                'status' => 'alocado',
            ]);
        } else {
            $pesRepo->create([
                'id_produto_evento' => $idProdutoEvento,
                'id_serial'         => $idSerial,
                'status'            => 'alocado',
            ]);
        }

        $qtd = $pesRepo->sumQtdAlocadaByProdutoEvento($idProdutoEvento);
        Connection::exec(
            "UPDATE produtos_evento SET qtd_alocada = ? WHERE id = ?",
            [$qtd, $idProdutoEvento]
        );

        // A linha reassociada pertencia a outro produto_evento — recalcula tambem o antigo
        if ($idProdutoEventoAntigo !== null) {
            $qtdAntigo = $pesRepo->sumQtdAlocadaByProdutoEvento($idProdutoEventoAntigo);
            Connection::exec(
                "UPDATE produtos_evento SET qtd_alocada = ? WHERE id = ?",
                [$qtdAntigo, $idProdutoEventoAntigo]
            );
        }
    }

    /**
     * Remove alocação de produto_evento_seriais e recalcula qtd_alocada
     */
    private function desalocarSerial(int $idSerial, int $idProdutoEvento, ProdutoEventoSerialRepository $pesRepo): void
    {
        $existing = $pesRepo->findBySerialAndProdutoEvento($idSerial, $idProdutoEvento);
        if ($existing && $existing['status'] === 'alocado') {
            $pesRepo->atualizarStatus($existing['id'], 'devolvido');
            $qtd = $pesRepo->sumQtdAlocadaByProdutoEvento($idProdutoEvento);
            Connection::exec(
                "UPDATE produtos_evento SET qtd_alocada = ? WHERE id = ?",
                [$qtd, $idProdutoEvento]
            );
        }
    }

    /**
     * Atualizar observacao do item
     */
    public function updateObservacao(int $montagemId, string $observacao): array
    {
        $entity = $this->findOrFail($montagemId);
        $this->repo->updateObservacao($montagemId, $observacao);
        return ['success' => true];
    }

    /**
     * Listar montagens por evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->repo->findByEvento($idEvento);
    }

    /**
     * Contar seriais por sala
     */
    public function countBySala(int $idEvento): array
    {
        return $this->repo->countBySala($idEvento);
    }
}

<?php

namespace App\Service;

use App\Repository\DevolucaoRepository;
use App\Repository\MontagemRepository;
use App\Repository\EventoRepository;
use App\Repository\SerialProdutoRepository;
use App\Repository\ProdutoEventoSerialRepository;
use App\Repository\ProdutoEventoRepository;
use App\Database\Connection;

class DevolucaoService extends BaseService
{
    private DevolucaoRepository $devolucaoRepo;
    private MontagemRepository $montagemRepo;
    private EventoRepository $eventoRepo;
    private SerialProdutoRepository $serialRepo;
    private ProdutoEventoSerialRepository $produtoEventoSerialRepo;
    private ProdutoEventoRepository $produtoEventoRepo;

    public function __construct()
    {
        $this->devolucaoRepo = new DevolucaoRepository();
        $this->montagemRepo = new MontagemRepository();
        $this->eventoRepo = new EventoRepository();
        $this->serialRepo = new SerialProdutoRepository();
        $this->produtoEventoSerialRepo = new ProdutoEventoSerialRepository();
        $this->produtoEventoRepo = new ProdutoEventoRepository();
        parent::__construct($this->devolucaoRepo);
    }

    /**
     * Processa devolução única
     */
    public function processarUnico(int $idEvento, string $serial, string $status, ?string $motivo = null, ?string $solucao = null, string $usuario = 'Sistema'): array
    {
        // Validações
        $this->validarDadosUnico($serial, $status, $motivo, $solucao);
        
        // Verificar se serial existe no estoque
        $serialInfo = $this->serialRepo->findByNumero($serial);
        if (!$serialInfo) {
            throw new \Exception("Serial '{$serial}' não encontrado no estoque.");
        }
        
        $idSerial = $serialInfo['id'];
        
        // Verificar se o serial está montado no evento
        $montagem = $this->montagemRepo->getBySerialAndEvento($serial, $idEvento);
        if (!$montagem) {
            throw new \Exception("Serial '{$serial}' não está montado neste evento.");
        }
        
        $idMontagem = $montagem['id'];
        
        // Verificar se já existe devolucao para este serial
        $devolucaoExistente = $this->devolucaoRepo->findBySerialAndEvento($idSerial, $idEvento);
        
        // Iniciar transação
        Connection::beginTransaction();
        
        try {
            if ($devolucaoExistente) {
                // Atualizar devolucao existente
                $devolucaoId = $devolucaoExistente['id'];
                
                $updateData = [
                    'status' => $status,
                    'motivo_pendencia' => ($status === 'P') ? $motivo : $devolucaoExistente['motivo_pendencia'],
                    'solucao_pendencia' => ($status === 'S') ? $solucao : $devolucaoExistente['solucao_pendencia']
                ];
                
                $this->devolucaoRepo->update($devolucaoId, $updateData);
            } else {
                // Inserir nova devolucao
                $devolucaoId = $this->devolucaoRepo->create([
                    'id_evento' => $idEvento,
                    'id_serial' => $idSerial,
                    'tipo_devolucao' => 'unico',
                    'status' => $status,
                    'motivo_pendencia' => ($status === 'P') ? $motivo : null,
                    'solucao_pendencia' => ($status === 'S') ? $solucao : null,
                    'usuario_devolucao' => $usuario,
                ]);
            }
            
            // Atualizar status do serial
            $this->atualizarStatusSerial($idSerial, $status);
            
            // Marcar montagem como devolvida
            $this->montagemRepo->marcarDevolvida($idMontagem);
            
            // Desalocar serial do produto_evento (decrementar qtd_alocada)
            $this->desalocarSerialDoProdutoEvento($idSerial, $montagem);
            
            // Atualizar flag de pendência do evento
            $this->atualizarFlagPendenciaEvento($idEvento);
            
            // Verificar se evento está completo
            $this->verificarEventoCompleto($idEvento);
            
            Connection::commit();
            
            return [
                'success' => true,
                'id' => $devolucaoId,
                'status' => $status
            ];
            
        } catch (\Exception $e) {
            Connection::rollback();
            throw $e;
        }
    }

    /**
     * Processa devolução em lote
     */
    public function processarLote(int $idEvento, array $seriais): array
    {
        $resultados = ['success' => true, 'sucesso' => [], 'erros' => []];
        
        foreach ($seriais as $serial) {
            $serial = trim($serial);
            if (empty($serial)) continue;
            
            try {
                $resultado = $this->processarUnico($idEvento, $serial, 'A');
                $resultados['sucesso'][] = $serial;
            } catch (\Exception $e) {
                $resultados['erros'][$serial] = $e->getMessage();
            }
        }
        
        // Se todos falharam, marca como erro
        if (empty($resultados['sucesso']) && !empty($resultados['erros'])) {
            $resultados['success'] = false;
        }
        
        return $resultados;
    }

    /**
     * Atualiza o status de um serial
     */
    private function atualizarStatusSerial(int $idSerial, string $status): int
    {
        $statusDevolucao = match($status) {
            'A' => 'ATIVO',
            'P' => 'PENDENTE',
            'S' => 'SOLUCIONADO'
        };
        
        return $this->serialRepo->update($idSerial, [
            'status_devolucao' => $statusDevolucao
        ]);
    }

    /**
     * Atualiza a flag de pendência do evento
     */
    private function atualizarFlagPendenciaEvento(int $idEvento): void
    {
        $pendenciasAtivas = $this->devolucaoRepo->countPendentesByEvento($idEvento);
        $temPendencia = $pendenciasAtivas > 0 ? 1 : 0;
        
        Connection::exec(
            "UPDATE eventos SET tem_pendencia = ? WHERE id = ?",
            [$temPendencia, $idEvento]
        );
    }

    /**
     * Verifica se todas as OS foram devolvidas
     */
    private function verificarEventoCompleto(int $idEvento): void
    {
        $totalMontados = $this->montagemRepo->countByEventoStatus($idEvento, 'montado');
        $totalDevolvidosA = $this->devolucaoRepo->countByEventoStatus($idEvento, 'A');
        $totalDevolvidosS = $this->devolucaoRepo->countByEventoStatus($idEvento, 'S');
        $totalDevolvidos = $totalDevolvidosA + $totalDevolvidosS;
        $pendenciasAtivas = $this->devolucaoRepo->countPendentesByEvento($idEvento);
        
        // Se todas OS foram devolvidas e nao ha pendencias (A + S contam como devolvidos)
        if ($totalMontados > 0 && $totalMontados === $totalDevolvidos && $pendenciasAtivas === 0) {
            $this->eventoRepo->update($idEvento, [
                'data_devolucao_completa' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Valida dados para devolução única
     */
    private function validarDadosUnico(string $serial, string $status, ?string $motivo, ?string $solucao): void
    {
        if (empty($serial)) {
            throw new \Exception("Serial é obrigatório.");
        }
        
        if (!in_array($status, ['A', 'P', 'S'])) {
            throw new \Exception("Status inválido.");
        }
        
        if ($status === 'P' && empty($motivo)) {
            throw new \Exception("Motivo da pendência é obrigatório.");
        }
        
        if ($status === 'S' && empty($solucao)) {
            throw new \Exception("Solução da pendência é obrigatória.");
        }
        
        // Se for status A, motivo e solucao devem ser nulos
        if ($status === 'A' && (!is_null($motivo) || !is_null($solucao))) {
            throw new \Exception("Motivo e solucao devem ser nulos para status A.");
        }
    }


    /**
     * Desalocar serial do produto_evento e decrementar qtd_alocada
     */
    private function desalocarSerialDoProdutoEvento(int $idSerial, array $montagem): void
    {
        $idProdutoEvento = $montagem['id_produto_evento'] ?? null;
        if (!$idProdutoEvento) {
            return; // Serial nao estava vinculado a um produto especifico
        }
        
        $alocacao = $this->produtoEventoSerialRepo->findBySerialAndProdutoEvento($idSerial, $idProdutoEvento);
        if (!$alocacao) {
            return; // Serial nao estava alocado via produto_evento_serial
        }
        
        // Atualizar status da alocacao para 'devolvido'
        $this->produtoEventoSerialRepo->atualizarStatus($alocacao['id'], 'devolvido');
        
        // Recalcular qtd_alocada
        $qtdAlocada = $this->produtoEventoSerialRepo->sumQtdAlocadaByProdutoEvento($idProdutoEvento);
        $this->produtoEventoRepo->update($idProdutoEvento, ['qtd_alocada' => $qtdAlocada]);
    }

    /**
     * Busca estatísticas de devolução por evento
     */
    public function getStatsByEvento(int $idEvento): array
    {
        $totalMontados = $this->montagemRepo->countByEventoStatus($idEvento, 'montado');
        $totalDevolvidosA = $this->devolucaoRepo->countByEventoStatus($idEvento, 'A');
        $totalDevolvidosP = $this->devolucaoRepo->countByEventoStatus($idEvento, 'P');
        $totalDevolvidosS = $this->devolucaoRepo->countByEventoStatus($idEvento, 'S');
        $pendenciasAtivas = $this->devolucaoRepo->countPendentesByEvento($idEvento);
        
        return [
            'total_montados' => $totalMontados,
            'total_devolvidos' => $totalDevolvidosA,
            'pendencias_ativas' => $pendenciasAtivas,
            'total_devolvidos_pendente' => $totalDevolvidosP,
            'total_devolvidos_solucionado' => $totalDevolvidosS
        ];
    }

    /**
     * Busca todas as devolucoes de um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->devolucaoRepo->findByEvento($idEvento);
    }

    /**
     * Busca todas as devolucoes de um serial (qualquer evento)
     */
    public function findBySerial(int $idSerial): array
    {
        return $this->devolucaoRepo->findBySerial($idSerial);
    }

    /**
     * Busca uma devolucao pelo ID
     */
    public function findById(int $id): ?array
    {
        return $this->devolucaoRepo->find($id);
    }

    /**
     * Atualiza o status de uma devolucao
     */
    public function updateStatus(int $idDevolucao, string $status, ?string $motivo = null): void
    {
        Connection::beginTransaction();

        try {
            $devolucao = $this->devolucaoRepo->find($idDevolucao);
            if (!$devolucao) {
                throw new \Exception("Devolucao nao encontrada.");
            }

            $data = ['status' => $status];
            if ($status === 'P' && $motivo !== null) {
                $data['motivo_pendencia'] = $motivo;
            }

            $this->devolucaoRepo->update($idDevolucao, $data);

            $this->atualizarStatusSerial($devolucao['id_serial'], $status);

            $this->atualizarFlagPendenciaEvento($devolucao['id_evento']);

            Connection::commit();
        } catch (\Exception $e) {
            Connection::rollback();
            throw $e;
        }
    }

    /**
     * Marca uma devolução como resolvida
     */
    public function resolverPendencia(int $idDevolucao, string $solucao): array
    {
        // Buscar a devolução
        $devolucao = $this->devolucaoRepo->find($idDevolucao);
        if (!$devolucao) {
            throw new \Exception("Devolução não encontrada.");
        }
        
        if ($devolucao['status'] !== 'P') {
            throw new \Exception("Apenas devoluções em status Pendência podem ser resolvidas.");
        }
        
        Connection::beginTransaction();
        
        try {
            // Atualizar devolução para solucionada
            $this->devolucaoRepo->updateSolucao($idDevolucao, $solucao);
            
            // Atualizar status do serial
            $this->atualizarStatusSerial($devolucao['id_serial'], 'S');
            
            // Atualizar flag do evento
            $this->atualizarFlagPendenciaEvento($devolucao['id_evento']);
            
            // Verificar se o evento está completo
            $this->verificarEventoCompleto($devolucao['id_evento']);
            
            Connection::commit();
            
            return [
                'success' => true,
                'id_devolucao' => $idDevolucao
            ];
            
        } catch (\Exception $e) {
            Connection::rollback();
            throw $e;
        }
    }
}
?>
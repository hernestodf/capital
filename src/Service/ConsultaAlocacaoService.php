<?php

namespace App\Service;

use App\Repository\ProdutoEventoRepository;
use App\Database\Connection;

/**
 * Service para consultar disponibilidade de estoque físico (código de barras)
 * Responde: "Quantos códigos deste produto existem, quantos estão livres, e quais
 * estão fisicamente montados em qual evento/O.S. agora?"
 */
class ConsultaAlocacaoService
{
    private ProdutoEventoRepository $produtoEventoRepo;

    public function __construct()
    {
        $this->produtoEventoRepo = new ProdutoEventoRepository();
    }

    /**
     * Consulta disponibilidade de produto(s) de ESTOQUE FÍSICO por termo de busca
     * (contém, não precisa ser nome exato — ex: "moving" acha "Moving Head 200W").
     * Simples e objetivo: quantos códigos de barras ativos existem por produto, quantos
     * estao livres, e quais estao fisicamente montados em outro evento agora (com evento + O.S.).
     *
     * Nao trata itens de `planilhas` (kits/servicos como "SONORIZACAO 1") — esses nao
     * correspondem a uma unidade fisica rastreavel, entao nao fazem parte deste contexto.
     */
    public function consultarPorNome(string $termo, ?int $idEventoAtual = null): array
    {
        if (empty($termo)) {
            return [];
        }

        $produtos = $this->buscarProdutosEstoque($termo);

        if (empty($produtos)) {
            return [
                'termo' => $termo,
                'encontrado' => false,
                'mensagem' => 'Produto não encontrado no estoque físico (catálogo de produtos/código de barras)',
            ];
        }

        $resultados = [];
        foreach ($produtos as $produto) {
            $seriais = Connection::query(
                "SELECT sp.id, sp.serial, m.id_evento, e.nome_evento, e.os_cliente
                 FROM seriaisproduto sp
                 LEFT JOIN montagens m ON m.id_serial = sp.id AND m.status = 'montado'
                 LEFT JOIN eventos e ON e.id = m.id_evento
                 WHERE sp.id_produto = ? AND sp.status = 'ATIVO'
                 ORDER BY sp.serial ASC",
                [$produto['id']]
            );

            $ocupados = [];
            $disponiveis = 0;

            foreach ($seriais as $s) {
                if (empty($s['id_evento'])) {
                    $disponiveis++;
                    continue;
                }

                $idEvento = (int)$s['id_evento'];
                $ocupados[] = [
                    'serial' => $s['serial'],
                    'id_evento' => $idEvento,
                    'nome_evento' => $s['nome_evento'] ?? "Evento #{$idEvento}",
                    'os_cliente' => $s['os_cliente'] ?? '',
                    'evento_atual' => $idEventoAtual !== null && $idEvento === $idEventoAtual,
                ];
            }

            $resultados[] = [
                'produto' => $produto['produto'],
                'total_ativo' => count($seriais),
                'disponiveis' => $disponiveis,
                'ocupados' => $ocupados,
            ];
        }

        return [
            'termo' => $termo,
            'encontrado' => true,
            'produtos' => $resultados,
        ];
    }

    /**
     * Busca produtos de estoque cujo nome contem o termo (case-insensitive)
     * Retorna: [['id' => int, 'produto' => string], ...]
     */
    private function buscarProdutosEstoque(string $termo): array
    {
        return Connection::query(
            "SELECT id, produto FROM produtos WHERE produto LIKE ? ORDER BY produto ASC LIMIT 10",
            ['%' . $termo . '%']
        );
    }

    /**
     * Consulta alocação de uma sala (por nome)
     * Retorna: Todos eventos onde está em uso + status
     */
    public function consultarSalaPorNome(string $nomeSala, ?int $idEventoAtual = null): array
    {
        if (empty($nomeSala)) {
            return [];
        }

        // Aqui você poderia consultar produtos/salas
        // Por enquanto, retorna estrutura simples
        return [
            'sala' => $nomeSala,
            'encontrado' => false,
            'mensagem' => 'Consulta de salas ainda não implementada',
            'eventos' => []
        ];
    }

    /**
     * Resumo rápido de disponibilidade do estoque
     * Mostra: Qtd do estoque total vs alocado
     */
    public function resumoDisponibilidade(string $nomeProduto): array
    {
        $alocacoes = $this->produtoEventoRepo->findByProdutoNome($nomeProduto);

        if (empty($alocacoes)) {
            return [
                'produto' => $nomeProduto,
                'total_alocado' => 0,
                'total_subblocado' => 0,
                'status' => 'NAO_ENCONTRADO'
            ];
        }

        $totalAlocado = 0;
        $totalSublocado = 0;

        foreach ($alocacoes as $aloc) {
            $totalAlocado += (float)($aloc['qtd_alocada'] ?? 0);
            $totalSublocado += (float)($aloc['qtd_sublocada'] ?? 0);
        }

        return [
            'produto' => $nomeProduto,
            'total_alocado' => $totalAlocado,
            'total_sublocado' => $totalSublocado,
            'total_em_uso' => $totalAlocado + $totalSublocado,
            'status' => $totalAlocado > 0 ? 'ALOCADO' : 'DISPONIVEL'
        ];
    }
}

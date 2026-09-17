<?php

namespace App\Service;

use App\Repository\SerialProdutoRepository;

class SerialProdutoService extends BaseService
{
    protected string $entityName = 'SerialProduto';

    public function __construct()
    {
        parent::__construct(new SerialProdutoRepository());
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        $this->checkDuplicata($data['serial']);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $this->validate($data, $id);
        $data = $this->sanitizeData($data);
        $this->checkDuplicata($data['serial'] ?? '', $id);
        return $this->repository->update($id, $data);
    }

    private function checkDuplicata(string $serial, ?int $ignoreId = null): void
    {
        if (empty($serial)) return;
        $existing = $this->repository->findByNumero($serial);
        if ($existing && (int)$existing['id'] !== $ignoreId) {
            $nomeProd = $existing['nome_produto'] ?? 'outro produto';
            throw new \InvalidArgumentException("Código '{$serial}' já está cadastrado em '{$nomeProd}'");
        }
    }

    public function updateStatus(int $id, string $status, string $motivo = ''): bool
    {
        if (!in_array($status, ['ATIVO', 'MANUTENCAO', 'VENDER'])) {
            throw new \InvalidArgumentException('Status invalido');
        }

        if ($status === 'MANUTENCAO' && empty($motivo)) {
            throw new \InvalidArgumentException('Motivo e obrigatorio para status de manutencao');
        }

        return $this->repository->updateStatus($id, $status, $motivo);
    }

    private function validate(array $data, ?int $id = null): void
    {
        // id_produto e obrigatorio apenas na criacao
        if ($id === null && empty($data['id_produto'])) {
            throw new \InvalidArgumentException('Produto e obrigatorio');
        }

        // serial e obrigatorio sempre
        if (empty($data['serial'])) {
            throw new \InvalidArgumentException('Código de barras é obrigatório');
        }

        if (!empty($data['status']) && !in_array($data['status'], ['ATIVO', 'MANUTENCAO', 'VENDER'])) {
            throw new \InvalidArgumentException('Status invalido');
        }
    }

    public function sanitizeData(array $data): array
    {
        $data['serial'] = trim(strip_tags($data['serial'] ?? ''));
        $data['motivo'] = trim(strip_tags($data['motivo'] ?? ''));
        $numeroSerie = trim(strip_tags($data['numero_serie'] ?? ''));
        $data['numero_serie'] = $numeroSerie !== '' ? $numeroSerie : null;

        if (!isset($data['status'])) {
            $data['status'] = 'ATIVO';
        }
        
        if (isset($data['id_produto'])) {
            $data['id_produto'] = intval($data['id_produto']);
        }

        return $data;
    }

    public function findByProduto(int $idProduto): array
    {
        return $this->repository->findByProduto($idProduto);
    }

    /**
     * Filtra uma lista de códigos, retornando só os que realmente existem
     * cadastrados — nunca imprime QR de texto arbitrário não registrado.
     * @return string[]
     */
    public function filterExisting(array $codigos): array
    {
        return $this->repository->findExisting($codigos);
    }

    public function delete(int $id): int
    {
        return $this->repository->delete($id);
    }

    /**
     * Add multiple serials in batch
     * @param int $idProduto Product ID
     * @param array $serials Array of serial numbers (one per line)
     * @param string $status Status (default: ATIVO)
     * @return array ['success' => int, 'duplicates' => int, 'errors' => array]
     */
    public function storeBatch(int $idProduto, array $serials, string $status = 'ATIVO'): array
    {
        // Validate
        if (empty($serials)) {
            throw new \InvalidArgumentException('Nenhum serial fornecido');
        }

        // Sanitize - trim each serial
        $serials = array_map(function($s) {
            return trim(strip_tags($s));
        }, $serials);

        // Remove empty entries
        $serials = array_filter($serials, function($s) {
            return !empty($s);
        });

        if (empty($serials)) {
            throw new \InvalidArgumentException('Nenhum serial valido fornecido');
        }

        return $this->repository->storeBatch($idProduto, $serials, $status);
    }

    /**
     * Limite de seguranca por geracao — evita picos absurdos por erro de digitacao.
     */
    public const MAX_RANGE_SIZE = 500;

    /**
     * Gera a lista de codigos "PREFIXO-N" para uma faixa (inicial..final).
     * @return string[]
     */
    public function buildRangeCodes(string $prefixo, int $inicial, int $final): array
    {
        $prefixo = trim($prefixo);
        if ($prefixo === '') {
            throw new \InvalidArgumentException('Prefixo é obrigatório');
        }
        if ($inicial > $final) {
            throw new \InvalidArgumentException('O sequencial inicial não pode ser maior que o final');
        }

        $total = $final - $inicial + 1;
        if ($total > self::MAX_RANGE_SIZE) {
            throw new \InvalidArgumentException(
                "Faixa grande demais ({$total} códigos) — máximo permitido: " . self::MAX_RANGE_SIZE
            );
        }

        $codigos = [];
        for ($i = $inicial; $i <= $final; $i++) {
            $codigos[] = $prefixo . '-' . $i;
        }
        return $codigos;
    }

    /**
     * Pré-visualiza uma geração por faixa: monta a lista e indica quais já existem,
     * sem inserir nada no banco.
     * @return array ['codigos' => string[], 'existentes' => string[], 'total' => int]
     */
    public function previewRange(string $prefixo, int $inicial, int $final): array
    {
        $codigos = $this->buildRangeCodes($prefixo, $inicial, $final);
        $existentes = $this->repository->findExisting($codigos);
        return [
            'codigos'    => $codigos,
            'existentes' => array_values($existentes),
            'total'      => count($codigos),
        ];
    }

    /**
     * Gera a faixa e cadastra de fato no estoque (via storeBatch, reaproveitando
     * a checagem de duplicata e a transação já existentes).
     * @return array ['success' => int, 'duplicates' => int, 'errors' => array, 'codigos' => string[]]
     */
    public function generateRange(int $idProduto, string $prefixo, int $inicial, int $final): array
    {
        if (empty($idProduto)) {
            throw new \InvalidArgumentException('Produto é obrigatório');
        }

        $codigos = $this->buildRangeCodes($prefixo, $inicial, $final);
        $result = $this->storeBatch($idProduto, $codigos, 'ATIVO');
        $result['codigos'] = $codigos;
        return $result;
    }
}

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
}

<?php

namespace App\Service;

use App\Repository\ColaboradorRepository;
use App\Repository\ColaboradorVerificacaoRepository;
use App\Database\Connection;

class ColaboradorService extends BaseService
{
    protected string $entityName = 'Colaborador';
    private ColaboradorRepository $repo;
    private ColaboradorVerificacaoRepository $verificacaoRepo;

    public function __construct()
    {
        $this->repo = new ColaboradorRepository();
        $this->verificacaoRepo = new ColaboradorVerificacaoRepository();
        parent::__construct($this->repo);
    }

    public function search(string $search = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($search, $page, $perPage);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        $data['ativo'] = $data['ativo'] ?? 0; // Sempre inativo por padrão

        return $this->repo->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $data = $this->sanitizeData($data);

        return $this->repo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $colaborador = $this->findOrFail($id);

        // Verificar se colaborador está vinculado a algum evento (ativo ou histórico)
        $vinculos = Connection::query(
            "SELECT COUNT(*) as total FROM evento_colaboradores WHERE id_colaborador = ?",
            [$id]
        );
        if ((int)($vinculos[0]['total'] ?? 0) > 0) {
            throw new \RuntimeException(
                'Este colaborador não pode ser excluído pois está vinculado a um ou mais eventos. ' .
                'Para removê-lo do sistema, primeiro desvincule-o de todos os eventos.'
            );
        }

        $this->repo->delete($id);
    }

    public function toggleStatus(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->toggleStatus($id);
    }

    public function getVerificacaoStatus(int $colaboradorId): ?array
    {
        return $this->verificacaoRepo->findByColaborador($colaboradorId);
    }

    /**
     * Busca status de verificacao de multiplos colaboradores (1 query)
     */
    public function getVerificacaoStatusBatch(array $colaboradores): array
    {
        $ids = array_map(function($c) { return $c['id']; }, $colaboradores);
        return $this->verificacaoRepo->findByColaboradores($ids);
    }

    private function validate(array $data): void
    {
        $this->validateRequired($data, ['nome']);

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email inválido');
        }

        if (!empty($data['tipo']) && !in_array($data['tipo'], ['FUNCIONARIO', 'FREELANCE'])) {
            throw new \Exception('Tipo deve ser FUNCIONARIO ou FREELANCE');
        }
    }

    private function sanitizeData(array $data): array
    {
        $sanitized = $this->sanitize($data);

        // Sanitiza telefone
        if (!empty($sanitized['telefone'])) {
            $sanitized['telefone'] = preg_replace('/[^0-9+]/', '', $sanitized['telefone']);
        }

        // Sanitiza CEP
        if (!empty($sanitized['cep'])) {
            $sanitized['cep'] = preg_replace('/[^0-9]/', '', $sanitized['cep']);
        }

        return $sanitized;
    }
}

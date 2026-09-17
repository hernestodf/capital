<?php

namespace App\Service;

use App\Repository\EventoRepository;

class EventoService extends BaseService
{
    private EventoRepository $repo;

    public function __construct()
    {
        $this->repo = new EventoRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        return $this->repo->create(parent::sanitize($data));
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $data = $this->sanitizeData($data);
        return $this->repo->update($id, parent::sanitize($data));
    }

    public function delete(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->delete($id);
    }

    public function toggleStatus(int $id): int
    {
        $entity = $this->findOrFail($id);
        $newStatus = $entity['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Todos os eventos de um produtor (para comercial com acesso restrito)
     */
    public function allByProdutor(int $idProdutor): array
    {
        return $this->repo->allByProdutor($idProdutor);
    }

    /**
     * Busca eventos com filtros
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($filters, $page, $perPage);
    }

    /**
     * Conta eventos por estado/status
     */
    public function countByFilters(array $filters = []): array
    {
        return $this->repo->countByFilters($filters);
    }

    /**
     * Converte orcamento em locacao
     */
    public function converterEmLocacao(int $id): int
    {
        $this->findOrFail($id);
        return $this->repo->update($id, [
            'estado' => 'L',
            'status_locacao' => 'A'
        ]);
    }

    /**
     * Finaliza locacao
     */
    public function finalizarLocacao(int $id, array $fechamento = []): int
    {
        $this->findOrFail($id);
        $data = [
            'status_locacao' => 'F',
        ];
        if (!empty($fechamento['observacoes_fechamento'])) {
            $data['observacoes_fechamento'] = $fechamento['observacoes_fechamento'];
        }
        return $this->repo->update($id, $data);
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['nome_evento'])) {
            $errors[] = 'Nome do evento e obrigatorio';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        if (!empty($data['telefone_demandantelocal'])) {
            $data['telefone_demandantelocal'] = preg_replace('/\D/', '', $data['telefone_demandantelocal']);
        }
        if (!empty($data['nome_evento'])) {
            $data['nome_evento'] = trim($data['nome_evento']);
        }
        if (!empty($data['local_evento'])) {
            $data['local_evento'] = trim($data['local_evento']);
        }
        return $data;
    }
}

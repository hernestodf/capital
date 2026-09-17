<?php

namespace App\Service;

use App\Repository\ContasPagarRepository;

class ContasPagarService extends BaseService
{
    private ContasPagarRepository $repo;

    public function __construct()
    {
        $this->repo = new ContasPagarRepository();
        parent::__construct($this->repo);
    }

    public function delete(int $id): int
    {
        return $this->repo->deleteWithCascade($id);
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

    /**
     * Busca contas a pagar com filtros avancados
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($filters, $page, $perPage);
    }

    /**
     * Registrar pagamento de uma conta
     */
    public function registrarPagamento(int $id, array $data): int
    {
        $conta = $this->findOrFail($id);

        if ($conta['status'] === 'PAGO') {
            throw new \InvalidArgumentException('Conta já está registrada como paga');
        }

        $pagamento = [
            'status' => 'PAGO',
            'data_pagamento' => $data['data_pagamento'] ?? date('Y-m-d'),
            'valor_pago' => $data['valor_pago'] ?? $conta['valor'],
            'tipo_pagamento' => $data['tipo_pagamento'] ?? null,
        ];

        return $this->repo->update($id, $pagamento);
    }

    /**
     * Retorna totais por status
     */
    public function getTotais(): array
    {
        return $this->repo->getTotais();
    }

    /**
     * Buscar contas vencidas
     */
    public function getVencidas(): array
    {
        return $this->repo->getVencidas();
    }

    /**
     * Buscar contas que vencem hoje
     */
    public function getVencemHoje(): array
    {
        return $this->repo->getVencemHoje();
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['descricao'])) {
            $errors[] = 'Descrição é obrigatória';
        }

        if (empty($data['valor']) || $data['valor'] <= 0) {
            $errors[] = 'Valor deve ser maior que zero';
        }

        if (empty($data['data_vencimento'])) {
            $errors[] = 'Data de vencimento é obrigatória';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        if (!empty($data['numero_nf'])) {
            $data['numero_nf'] = preg_replace('/\D/', '', $data['numero_nf']);
        }
        if (!empty($data['valor'])) {
            $data['valor'] = str_replace(',', '.', $data['valor']);
        }
        return $data;
    }
}

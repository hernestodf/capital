<?php

namespace App\Service;

use App\Repository\ProdutoEventoRepository;

class ProdutoEventoService extends BaseService
{
    private ProdutoEventoRepository $repo;

    public function __construct()
    {
        $this->repo = new ProdutoEventoRepository();
        parent::__construct($this->repo);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        
        // Se tem id_planilha, buscar dados da planilha para preencher campos faltantes
        if (!empty($data['id_planilha'])) {
            $planilha = $this->repo->findPlanilhaById((int)$data['id_planilha']);
            if ($planilha) {
                // Preencher produto se vazio
                if (empty($data['produto']) && !empty($planilha['item'])) {
                    $data['produto'] = $planilha['item'];
                }
                // Preencher valor_unit se vazio ou zero
                if (empty($data['valor_unit']) && !empty($planilha['valor'])) {
                    $data['valor_unit'] = (float)$planilha['valor'];
                }
                // Armazenar descricao para uso posterior (nao salva no banco)
                $data['_planilha_descricao'] = $planilha['descricao'] ?? '';
            }
        }
        
        $data = $this->sanitizeData($data);
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): int
    {
        $this->findOrFail($id);
        $data = $this->sanitizeData($data);
        return $this->repo->update($id, $data);
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
     * Busca produtos de uma sala
     */
    public function findBySala(int $idSala): array
    {
        return $this->repo->findBySala($idSala);
    }

    /**
     * Busca todos os produtos de um evento
     */
    public function findByEvento(int $idEvento): array
    {
        return $this->repo->findByEvento($idEvento);
    }

    /**
     * Busca produtos agrupados por sala com totais
     */
    public function groupBySala(int $idEvento): array
    {
        return $this->repo->groupBySala($idEvento);
    }

    /**
     * Soma totais financeiros de um evento
     */
    public function getTotaisEvento(int $idEvento): array
    {
        return $this->repo->getTotaisEvento($idEvento);
    }

    /**
     * Conta produtos por sala
     */
    public function countBySala(int $idSala): int
    {
        return $this->repo->countBySala($idSala);
    }

    /**
     * Busca produto com dados da planilha (para retorno apos criacao/edicao)
     */
    public function findWithPlanilha(int $id): array
    {
        return $this->repo->findWithPlanilha($id);
    }

    /**
     * Autocomplete de produtos da planilha
     */
    public function searchPlanilhas(string $term): array
    {
        return $this->repo->searchPlanilhas($term);
    }

    public function getPlanilhaById(int $id): array
    {
        $rows = \App\Database\Connection::query(
            "SELECT id, item, valor, potencia_w, horas_uso FROM planilhas WHERE id = ? LIMIT 1",
            [$id]
        );
        return $rows[0] ?? [];
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['id_evento']) && $id === null) {
            $errors[] = 'Evento e obrigatorio';
        }
        if (empty($data['id_sala']) && $id === null) {
            $errors[] = 'Sala e obrigatoria';
        }
        if (empty($data['produto'])) {
            $errors[] = 'Nome do produto e obrigatorio';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        // Calcula total_item automaticamente se todos os campos estiverem presentes
        if (isset($data['qtd']) && isset($data['valor_unit']) && isset($data['dias'])) {
            $data['total_item'] = (float)$data['qtd'] * (float)$data['valor_unit'] * (int)$data['dias'];
        }

        // Garante valores numericos APENAS para campos enviados
        if (isset($data['qtd'])) $data['qtd'] = (float) $data['qtd'];
        if (isset($data['valor_unit'])) $data['valor_unit'] = (float) $data['valor_unit'];
        if (isset($data['dias'])) $data['dias'] = (int) $data['dias'];
        if (isset($data['custo_unit'])) $data['custo_unit'] = (float) $data['custo_unit'];

        if (!empty($data['produto'])) {
            $data['produto'] = trim($data['produto']);
        }

        return $data;
    }
}

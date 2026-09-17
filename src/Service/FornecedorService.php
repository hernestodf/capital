<?php

namespace App\Service;

use App\Repository\FornecedorRepository;

class FornecedorService extends BaseService
{
    private FornecedorRepository $repo;

    public function __construct()
    {
        $this->repo = new FornecedorRepository();
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

    /**
     * Alterna status do fornecedor.
     */
    public function toggleStatus(int $id): int
    {
        $fornecedor = $this->findOrFail($id);
        $newStatus  = $fornecedor['status'] == 1 ? 0 : 1;
        $this->repo->update($id, ['status' => $newStatus]);
        return $newStatus;
    }

    /**
     * Busca fornecedores com termo de busca.
     */
    public function search(string $term = '', int $page = 1, int $perPage = 15): array
    {
        return $this->repo->search($term, $page, $perPage);
    }

    /**
     * Retorna fornecedores ativos.
     */
    public function getAtivos(): array
    {
        return $this->repo->getAtivos();
    }

    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['nome_fantasia'])) {
            $errors[] = 'Nome fantasia é obrigatório';
        }

        // Valida emails dos contatos (quando preenchidos)
        $emailFields = ['email', 'fin_email', 'fin_email2', 'com_email', 'com_email2'];
        foreach ($emailFields as $field) {
            if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email inválido no campo {$field}";
            }
        }

        if (!empty($data['cpf_cnpj']) && strlen(preg_replace('/\D/', '', $data['cpf_cnpj'])) < 11) {
            $errors[] = 'CPF/CNPJ inválido';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    private function sanitizeData(array $data): array
    {
        // Documentos
        if (!empty($data['cpf_cnpj'])) {
            $data['cpf_cnpj'] = preg_replace('/\D/', '', $data['cpf_cnpj']);
        }
        if (!empty($data['cep'])) {
            $data['cep'] = preg_replace('/\D/', '', $data['cep']);
        }

        // Formatar todos os campos de telefone (somente dígitos)
        $phoneFields = ['telefone', 'fin_telefone', 'fin_telefone2', 'com_telefone', 'com_telefone2'];
        foreach ($phoneFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]);
            }
        }

        // Manter email/telefone sincronizados com fin_email/fin_telefone
        // (usados por cotacao, IMAP e contas_pagar — não alterar esses módulos)
        if (!empty($data['fin_email'])) {
            $data['email'] = $data['fin_email'];
        }
        if (!empty($data['fin_telefone'])) {
            $data['telefone'] = $data['fin_telefone'];
        }

        return $data;
    }
}

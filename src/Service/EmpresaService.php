<?php

namespace App\Service;

use App\Repository\EmpresaRepository;

class EmpresaService extends BaseService
{
    private EmpresaRepository $repo;

    public function __construct()
    {
        $this->repo = new EmpresaRepository();
        parent::__construct($this->repo);
    }

    /**
     * Busca configurações da empresa
     */
    public function getConfig(): ?array
    {
        return $this->repo->getConfig();
    }

    /**
     * Atualiza configurações da empresa
     */
    public function updateConfig(array $data): int
    {
        $this->validate($data);
        $data = $this->sanitizeData($data);
        return $this->repo->updateConfig($data);
    }

    /**
     * Valida dados da empresa
     */
    private function validate(array $data): void
    {
        $errors = [];

        if (!empty($data['cnpj'])) {
            $cnpj = preg_replace('/\D/', '', $data['cnpj']);
            if (strlen($cnpj) !== 14) {
                $errors[] = 'CNPJ inválido';
            }
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!empty($data['cep'])) {
            $cep = preg_replace('/\D/', '', $data['cep']);
            if (strlen($cep) !== 8) {
                $errors[] = 'CEP inválido';
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode('. ', $errors));
        }
    }

    /**
     * Sanitiza dados
     */
    private function sanitizeData(array $data): array
    {
        if (!empty($data['cnpj'])) {
            $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj']);
        }
        if (!empty($data['cep'])) {
            $data['cep'] = preg_replace('/\D/', '', $data['cep']);
        }
        if (!empty($data['telefone'])) {
            $data['telefone'] = preg_replace('/\D/', '', $data['telefone']);
        }
        if (!empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/\D/', '', $data['whatsapp']);
        }
        return $data;
    }
}

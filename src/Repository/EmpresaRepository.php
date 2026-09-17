<?php

namespace App\Repository;

class EmpresaRepository extends BaseRepository
{
    protected string $table = 'empresa';
    protected array $fillable = [
        'nome', 'identificador', 'subtitulo', 'tagline',
        'cnpj', 'inscricao_estadual', 'endereco', 'cidade',
        'estado', 'cep', 'telefone', 'whatsapp', 'email',
        'site', 'pix_chave', 'pix_tipo', 'logo_path', 'rodape_pdf'
    ];

    /**
     * Busca a configuração da empresa (sempre ID 1)
     */
    public function getConfig(): ?array
    {
        $result = $this->find(1);
        return $result;
    }

    /**
     * Atualiza configurações da empresa
     */
    public function updateConfig(array $data): int
    {
        // Se não existir, cria
        $existing = $this->find(1);
        if (!$existing) {
            return $this->create(array_merge(['id' => 1], $data));
        }
        
        return $this->update(1, $data);
    }
}

<?php

namespace App\Service;

use App\Repository\ColaboradorVerificacaoRepository;
use App\Repository\ColaboradorRepository;
use App\Core\Env;

class ColaboradorVerificacaoService extends BaseService
{
    protected string $entityName = 'ColaboradorVerificacao';
    private ColaboradorVerificacaoRepository $repo;
    private ColaboradorRepository $colaboradorRepo;

    public function __construct()
    {
        $this->repo = new ColaboradorVerificacaoRepository();
        $this->colaboradorRepo = new ColaboradorRepository();
        parent::__construct($this->repo);
    }

    public function gerarToken(int $colaboradorId): string
    {
        $colaborador = $this->colaboradorRepo->find($colaboradorId);
        if (!$colaborador) {
            throw new \Exception('Colaborador não encontrado');
        }

        $token = bin2hex(random_bytes(32));
        $this->repo->createToken($colaboradorId, $token);

        return $token;
    }

    /**
     * Busca verificacao existente de um colaborador
     */
    public function findByColaborador(int $colaboradorId): ?array
    {
        return $this->repo->findByColaborador($colaboradorId);
    }

    public function enviarLinkEmail(int $colaboradorId, string $token): bool
    {
        $colaborador = $this->colaboradorRepo->find($colaboradorId);
        if (!$colaborador || empty($colaborador['email'])) {
            throw new \Exception('Colaborador não possui email cadastrado');
        }

        $verificacao = $this->repo->findByToken($token);
        if (!$verificacao) {
            throw new \Exception('Verificação não encontrada');
        }

        $publicUrl = rtrim(Env::get('BASE_URL', ''), '/');
        $link = $publicUrl . '/colaboradores/verificar/' . $token;
        $nome = $colaborador['nome'] ?? 'Colaborador';

        $corpo = '<p>Olá ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Clique no link abaixo para confirmar seu cadastro:</p>'
            . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</a></p>';

        $emailService = new EmailService();
        $result = $emailService->enviarMensagem(
            $colaborador['email'],
            $nome,
            '',
            'Confirmação de cadastro',
            $corpo
        );

        if (!($result['success'] ?? false)) {
            throw new \Exception($result['error'] ?? 'Falha ao enviar email');
        }

        $this->repo->markAsSent($verificacao['id']);
        return true;
    }

    public function processarVerificacao(string $token, string $foto, ?float $lat = null, ?float $lng = null): bool
    {
        $verificacao = $this->repo->findByToken($token);
        if (!$verificacao) {
            throw new \Exception('Token de verificação inválido');
        }

        if ($verificacao['status'] === 'verificado') {
            throw new \Exception('Verificação já foi concluída');
        }

        // Marca como acessado
        $this->repo->markAsAccessed($verificacao['id']);

        // Salva foto e geolocalização
        $this->repo->markAsVerified($verificacao['id'], $foto, $lat, $lng);

        // Ativa o colaborador
        $this->colaboradorRepo->toggleStatus($verificacao['colaborador_id']);
        // Força ativar mesmo que toggleStatus desative
        \App\Database\Connection::exec(
            "UPDATE colaboradores SET ativo = 1 WHERE id = ?",
            [$verificacao['colaborador_id']]
        );

        return true;
    }
    /**
     * Cria registro de verificação para colaborador (usado pelo cadastro público)
     */
    public function criarVerificacao(int $colaboradorId, bool $enviarEmail = true): string
    {
        $colaborador = $this->colaboradorRepo->find($colaboradorId);
        if (!$colaborador) {
            throw new \Exception('Colaborador não encontrado');
        }

        $token = bin2hex(random_bytes(32));
        $this->repo->createToken($colaboradorId, $token);

        if ($enviarEmail && !empty($colaborador['email'])) {
            try {
                $this->enviarLinkEmail($colaboradorId, $token);
            } catch (\Throwable $e) {
                // Silenciar erro de email - não crítico para criação do registro.
                \App\Core\Logger::warning('Erro ao enviar email de verificação: ' . $e->getMessage());
            }
        }

        return $token;
    }


    public function verificarToken(string $token): ?array
    {
        $verificacao = $this->repo->findByToken($token);
        if (!$verificacao) {
            return null;
        }

        $colaborador = $this->colaboradorRepo->find($verificacao['colaborador_id']);
        if (!$colaborador) {
            return null;
        }

        return [
            'verificacao' => $verificacao,
            'colaborador' => $colaborador,
        ];
    }
}

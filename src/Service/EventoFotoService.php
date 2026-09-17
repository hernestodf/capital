<?php

namespace App\Service;

use App\Repository\EventoFotoRepository;

/**
 * Service para upload e gerenciamento de fotos do evento
 */
class EventoFotoService extends BaseService
{
    private EventoFotoRepository $repo;
    protected string $entityName = 'EventoFoto';

    public function __construct()
    {
        $this->repo = new EventoFotoRepository();
        parent::__construct($this->repo);
    }

    /**
     * Upload de foto do evento
     */
    public function uploadFoto(array $file, int $eventoId, ?int $salaId = null): array
    {
        // Validar tipo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Tipo de arquivo nao permitido. Use JPEG, PNG ou WebP.');
        }

        // Validar tamanho (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new \Exception('Arquivo muito grande. Maximo 10MB.');
        }

        // Diretorio de upload
        $uploadDir = __DIR__ . '/../../public/uploads/eventos/' . $eventoId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nome unico
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'foto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \Exception('Erro ao salvar arquivo.');
        }

        // Caminho relativo
        $relativePath = 'uploads/eventos/' . $eventoId . '/' . $filename;

        // Salvar no banco
        $id = $this->repo->create([
            'evento_id' => $eventoId,
            'sala_id' => $salaId,
            'caminho_arquivo' => $relativePath,
            'nome_arquivo' => $file['name'],
        ]);

        return [
            'id' => $id,
            'caminho_arquivo' => $relativePath,
            'nome_arquivo' => $file['name'],
        ];
    }

    /**
     * Remover foto do banco e disco
     */
    public function removerFoto(int $fotoId, int $eventoId): bool
    {
        // Verificar pertencimento
        if (!$this->repo->belongsToEvento($fotoId, $eventoId)) {
            throw new \Exception('Foto nao encontrada ou nao pertence a este evento.');
        }

        $foto = $this->repo->find($fotoId);
        if (!$foto) {
            throw new \Exception('Foto nao encontrada.');
        }

        // Remover arquivo
        $fullPath = __DIR__ . '/../../public/' . $foto['caminho_arquivo'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Remover do banco
        return $this->repo->delete($fotoId) > 0;
    }

    /**
     * Listar fotos do evento agrupadas por sala
     */
    public function listarFotos(int $eventoId): array
    {
        $fotos = $this->repo->findByEvento($eventoId);
        $countBySala = $this->repo->countBySala($eventoId);
        $gerais = $this->repo->findGerais($eventoId);

        $countMap = [];
        foreach ($countBySala as $c) {
            $countMap[$c['sala_id']] = (int) $c['total'];
        }

        // Buscar TODAS as salas do evento (mesmo sem fotos)
        $todasSalas = \App\Database\Connection::query(
            "SELECT s.id, s.nome_sala
             FROM salas s
             INNER JOIN produtos_evento pe ON pe.id_sala = s.id
             WHERE pe.id_evento = ?
             GROUP BY s.id
             ORDER BY s.nome_sala",
            [$eventoId]
        );

        // Agrupar fotos por sala
        $fotosMap = [];
        foreach ($fotos as $f) {
            if ($f['sala_id'] !== null) {
                $salaId = (int) $f['sala_id'];
                if (!isset($fotosMap[$salaId])) {
                    $fotosMap[$salaId] = [];
                }
                $fotosMap[$salaId][] = $f;
            }
        }

        // Montar lista com todas as salas
        $porSala = [];
        foreach ($todasSalas as $sala) {
            $salaId = (int) $sala['id'];
            $porSala[] = [
                'sala_id' => $salaId,
                'nome_sala' => $sala['nome_sala'] ?? 'Sem nome',
                'fotos' => $fotosMap[$salaId] ?? [],
                'total' => $countMap[$salaId] ?? 0,
            ];
        }

        return [
            'por_sala' => $porSala,
            'gerais' => $gerais,
            'total_geral' => count($gerais),
        ];
    }
}

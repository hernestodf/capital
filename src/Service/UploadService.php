<?php

namespace App\Service;

class UploadService
{
    /**
     * Mapeamento de extensões para MIME types válidos
     */
    private const ALLOWED_MIME_TYPES = [
        // Imagens
        'jpg' => ['image/jpeg', 'image/jpg'],
        'jpeg' => ['image/jpeg', 'image/jpg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        
        // Documentos
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        
        // Comprovantes
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];

    /**
     * Tamanho máximo padrão (5MB)
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Realiza upload seguro com validação completa
     * 
     * @param array $file Arquivo do $_FILES
     * @param string $uploadDir Diretório de destino
     * @param string $filename Nome do arquivo (gerado se null)
     * @param array $allowedExtensions Extensões permitidas
     * @param int $maxSize Tamanho máximo em bytes
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function upload(
        array $file,
        string $uploadDir,
        ?string $filename = null,
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'],
        int $maxSize = self::MAX_FILE_SIZE
    ): array {
        // Verificar se há erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return self::error(self::getUploadErrorMessage($file['error']));
        }

        // Verificar tamanho do arquivo
        if ($file['size'] > $maxSize) {
            $maxMB = number_format($maxSize / 1024 / 1024, 1);
            return self::error("Arquivo muito grande. Máximo: {$maxMB}MB");
        }

        // Obter extensão do arquivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validar extensão
        if (!in_array($extension, $allowedExtensions)) {
            return self::error("Extensão não permitida: .{$extension}");
        }

        // FIX SEGURANÇA: Validar MIME type real (não apenas extensão)
        $mimeType = self::getMimeType($file['tmp_name']);
        if (!self::isMimeTypeAllowed($mimeType, $extension)) {
            return self::error("Tipo de arquivo inválido. MIME detectado: {$mimeType}");
        }

        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return self::error("Não foi possível criar o diretório de upload");
            }
        }

        // Gerar nome do arquivo se não fornecido
        if (!$filename) {
            $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        }

        $destination = rtrim($uploadDir, '/') . '/' . $filename;

        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return self::error("Falha ao mover arquivo enviado");
        }

        // Definir permissões seguras
        chmod($destination, 0644);

        return [
            'success' => true,
            'path' => $destination,
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size' => $file['size'],
        ];
    }

    /**
     * Obtém MIME type real do arquivo usando finfo
     */
    private static function getMimeType(string $filePath): string
    {
        if (!function_exists('finfo_open')) {
            // Fallback se finfo não estiver disponível
            return mime_content_type($filePath);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Verifica se o MIME type é permitido para a extensão
     */
    private static function isMimeTypeAllowed(string $mimeType, string $extension): bool
    {
        if (!isset(self::ALLOWED_MIME_TYPES[$extension])) {
            return false;
        }

        return in_array($mimeType, self::ALLOWED_MIME_TYPES[$extension]);
    }

    /**
     * Retorna mensagem de erro legível para código de upload PHP
     */
    private static function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite máximo do servidor',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o limite máximo do formulário',
            UPLOAD_ERR_PARTIAL => 'Upload parcial - tente novamente',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não configurado',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão PHP',
        ];

        return $messages[$errorCode] ?? 'Erro desconhecido no upload';
    }

    /**
     * Retorna mensagem de erro padronizada
     */
    private static function error(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
        ];
    }

    /**
     * Remove arquivo de forma segura
     */
    public static function delete(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Valida arquivo sem fazer upload (para checks preliminares)
     */
    public static function validate(array $file, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'], int $maxSize = self::MAX_FILE_SIZE): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return self::error(self::getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > $maxSize) {
            $maxMB = number_format($maxSize / 1024 / 1024, 1);
            return self::error("Arquivo muito grande. Máximo: {$maxMB}MB");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return self::error("Extensão não permitida: .{$extension}");
        }

        $mimeType = self::getMimeType($file['tmp_name']);
        if (!self::isMimeTypeAllowed($mimeType, $extension)) {
            return self::error("Tipo de arquivo inválido: {$mimeType}");
        }

        return ['success' => true];
    }
}

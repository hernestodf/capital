<?php

namespace App\Core;

class Logger
{
    private static string $logPath;
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) return;

        self::$logPath = dirname(__DIR__, 2) . '/storage/logs';
        
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0750, true);
        }

        self::$initialized = true;
    }

    public static function getLogPath(): string
    {
        self::init();
        return self::$logPath;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();

        $timestamp = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $logMessage = sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr
        );

        $fileName = self::$logPath . '/' . $date . '.log';
        
        // Limitar tamanho do log a 10MB
        if (file_exists($fileName) && filesize($fileName) > 10 * 1024 * 1024) {
            rename($fileName, $fileName . '.' . date('His'));
        }
        
        file_put_contents($fileName, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (Env::get('APP_ENV') === 'local') {
            self::log('debug', $message, $context);
        }
    }

    /**
     * Log especifico para acao de usuario
     */
    public static function userAction(string $userId, string $action, string $entity, ?int $entityId = null, array $extra = []): void
    {
        self::info('User Action', array_merge([
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
        ], $extra));
    }

    /**
     * Log especifico para erros de PDF
     */
    public static function pdfError(string $type, string $error, array $context = []): void
    {
        self::error('PDF: ' . $type, array_merge([
            'error' => $error,
            'channel' => 'pdf',
        ], $context));
    }

    public static function getLogs(?string $date = null): array
    {
        self::init();

        $date = $date ?? date('Y-m-d');
        $fileName = self::$logPath . '/' . $date . '.log';

        if (!file_exists($fileName)) {
            return [];
        }

        $content = file_get_contents($fileName);
        $lines = array_filter(explode("\n", $content));

        $logs = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $logs[] = $line;
        }

        return $logs;
    }

    public static function getErrorId(): string
    {
        return uniqid('ERR-', true);
    }
}

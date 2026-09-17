<?php

namespace App\Core;

class Env
{
    private static array $cache = [];
    private static bool $loaded = false;
    private static ?string $detectedState = null;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) return;
        
        $path = $path ?? dirname(__DIR__, 2) . '/.env';
        
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line) || str_starts_with($line, '#')) continue;
                if (!str_contains($line, '=')) continue;
                
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$cache[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        // Detectar estado baseado no hostname
        self::$detectedState = self::detectState();
        
        // Aplicar configurações do estado detectado
        self::applyStateConfig();
        
        // Detecção automática de ambiente quando APP_ENV não está definido no .env
        if (empty(self::$cache['APP_ENV'])) {
            self::$cache['APP_ENV'] = self::detectEnvironment();
            $_ENV['APP_ENV'] = self::$cache['APP_ENV'];
            putenv('APP_ENV=' . self::$cache['APP_ENV']);
        }

        // Normalizar BASE_URL
        if (isset(self::$cache['BASE_URL'])) {
            self::$cache['BASE_URL'] = rtrim(self::$cache['BASE_URL'], '/');
            $_ENV['BASE_URL'] = self::$cache['BASE_URL'];
            putenv('BASE_URL=' . self::$cache['BASE_URL']);
        }
        
        self::$loaded = true;
    }

    private static function detectState(): ?string
    {
        $config = self::loadStateConfig();
        $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
        
        // Primeiro tenta pelo .env manual
        $manualState = self::$cache['APP_STATE'] ?? null;
        if ($manualState && isset($config['states'][$manualState])) {
            return $manualState;
        }
        
        // Depois tenta pelo hostname
        foreach ($config['hostname_map'] ?? [] as $hostname => $state) {
            if ($state && str_contains($host, $hostname)) {
                return $state;
            }
        }
        
        return $config['default'] ?? null;
    }

    private static function loadStateConfig(): array
    {
        $configPath = dirname(__DIR__, 2) . '/config/states.php';
        return file_exists($configPath) ? require $configPath : ['states' => [], 'default' => null];
    }

    private static function applyStateConfig(): void
    {
        if (!self::$detectedState) return;
        
        $config = self::loadStateConfig();
        $stateConfig = $config['states'][self::$detectedState] ?? [];
        
        $overrides = [
            'BASE_URL' => $stateConfig['base_url'] ?? null,
            'DB_ONLINE_NAME' => $stateConfig['db_name'] ?? null,
            'DB_ONLINE_USER' => $stateConfig['db_user'] ?? null,
            'DB_ONLINE_PASS' => $stateConfig['db_pass'] ?? null,
            'FTP_PATH' => $stateConfig['ftp_path'] ?? null,
            'UPLOAD_PATH' => $stateConfig['upload_path'] ?? null,
        ];
        
        foreach ($overrides as $key => $value) {
            if ($value) {
                if ($key === 'BASE_URL' || empty(self::$cache[$key])) {
                    self::$cache[$key] = $value;
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
    
    public static function getState(): ?string
    {
        if (!self::$loaded) self::load();
        return self::$detectedState;
    }

    private static function detectEnvironment(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $host = strtolower($host);

        $productionIndicators = [
            'sisloc.online',
            'profox.sisloc.online',
            'sisloc.com',
            'profox.com',
        ];

        foreach ($productionIndicators as $indicator) {
            if (str_contains($host, $indicator)) {
                return 'production';
            }
        }

        return 'local';
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) self::load();
        return self::$cache[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        if (!self::$loaded) self::load();
        return isset(self::$cache[$key]);
    }

    public static function all(): array
    {
        if (!self::$loaded) self::load();
        return self::$cache;
    }
}
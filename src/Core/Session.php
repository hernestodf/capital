<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = (int) Env::get('SESSION_LIFETIME', 120);

            // Seguranca de cookies — DEVE ser antes de session_start()
            ini_set('session.cookie_lifetime', $lifetime * 60);
            ini_set('session.cookie_httponly', '1');       // Impede acesso via JavaScript (XSS)
            ini_set('session.use_strict_mode', '1');       // Previne session fixation
            // PHP 8.4+ gerencia sid_length e bits_per_character automaticamente

            // Secure cookie — apenas em HTTPS (producao)
            if (!Env::get('APP_ENV') || Env::get('APP_ENV') === 'production') {
                ini_set('session.cookie_secure', '1');
                ini_set('session.cookie_samesite', 'Lax');
            } else {
                // Local (HTTP): SameSite=Lax permite desenvolvimento normal
                ini_set('session.cookie_samesite', 'Lax');
            }

            session_start();
        }
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}

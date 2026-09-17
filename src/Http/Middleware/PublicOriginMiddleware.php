<?php

namespace App\Http\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Http\MiddlewareInterface;

class PublicOriginMiddleware implements MiddlewareInterface
{
    private const ALLOWED_ORIGINS = [
        'https://capital.sisloc.online',
        'http://localhost',
        'https://localhost',
    ];

    private const MAX_REQUESTS  = 10;
    private const WINDOW_SECONDS = 300; // 5 minutos

    public function handle(Request $request, callable $next): Response
    {
        if ($request->method() !== 'POST') {
            return $next($request);
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        if ($origin !== null && !in_array($origin, self::ALLOWED_ORIGINS, true)) {
            return (new Response())->json(['success' => false, 'message' => 'Origin não autorizado'], 403);
        }

        if (!$this->checkRateLimit($request->ip())) {
            return (new Response())->json(['success' => false, 'message' => 'Muitas requisições. Tente novamente em alguns minutos.'], 429);
        }

        return $next($request);
    }

    private function checkRateLimit(string $ip): bool
    {
        $file = sys_get_temp_dir() . '/cap_rl_' . md5($ip) . '.json';
        $now  = time();

        $data = ['count' => 0, 'window_start' => $now];
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $data = json_decode($raw, true) ?? $data;
            }
        }

        if ($now - ($data['window_start'] ?? 0) > self::WINDOW_SECONDS) {
            $data = ['count' => 1, 'window_start' => $now];
        } else {
            $data['count']++;
        }

        @file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['count'] <= self::MAX_REQUESTS;
    }
}

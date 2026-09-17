<?php

namespace App\Http\Middleware;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware para proteger endpoints CRON.
 * Valida secret key via header X-Cron-Secret (nao URL query).
 */
class CronAuthMiddleware implements \App\Http\MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $secretKey = $request->header('X-Cron-Secret');
        $expectedKey = Env::get('CRON_SECRET');

        if (empty($expectedKey)) {
            return Response::json(['error' => 'CRON_SECRET nao configurado'], 500);
        }

        if ($secretKey === null || !hash_equals($expectedKey, $secretKey)) {
            return Response::json(['error' => 'Nao autorizado'], 403);
        }

        return $next($request);
    }
}

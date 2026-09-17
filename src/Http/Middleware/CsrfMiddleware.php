<?php

namespace App\Http\Middleware;

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Http\MiddlewareInterface;

/**
 * Middleware que valida token CSRF em requisições que modificam estado.
 *
 * Aplica-se a grupos de rotas POST/PUT/DELETE/PATCH.
 * Retorna 400 se o token estiver ausente ou inválido.
 */
class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $method = $request->method();

        // Apenas métodos que modificam estado precisam de CSRF
        $protectedMethods = ['POST', 'PUT', 'DELETE', 'PATCH'];
        if (!in_array($method, $protectedMethods, true)) {
            return $next($request);
        }

        // Tentar obter token de múltiplas fontes
        $token = $request->post('_csrf_token')
            ?? $request->header('X-CSRF-Token')
            ?? $request->header('X-XSRF-Token');

        // Para PUT/DELETE com Content-Type urlencoded, $_POST fica vazio
        // Tentar parsear manualmente do php://input
        if ($token === null && in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            $body = file_get_contents('php://input');
            if (!empty($body)) {
                parse_str($body, $parsed);
                $token = $parsed['_csrf_token'] ?? $parsed['csrf_token'] ?? null;
            }
        }

        if ($token === null || !Csrf::validate($token)) {
            if ($request->isAjax()) {
                return Response::json(['error' => 'Token CSRF inválido ou ausente'], 400);
            }
            $_SESSION['error'] = 'Token CSRF inválido ou ausente';
            $referer = $_SERVER['HTTP_REFERER'] ?? Env::get('BASE_URL', '') . '/';
            return Response::redirect($referer);
        }

        return $next($request);
    }
}

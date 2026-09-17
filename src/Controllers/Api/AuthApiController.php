<?php

namespace App\Controllers\Api;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;
use App\Auth\Rbac;
use App\Http\Middleware\ApiKeyMiddleware;

class AuthApiController extends Controller
{
    public function login(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('auth:login')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: auth:login'
            ], 403);
        }

        $email    = $this->post('email');
        $password = $this->post('password');

        if (empty($email) || empty($password)) {
            return $this->json([
                'success' => false,
                'message' => 'E-mail e senha são obrigatórios'
            ], 400);
        }

        $result = Rbac::login($email, $password);

        if ($result['success']) {
            $user = Rbac::getUser();

            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            $redirect = $baseUrl . Rbac::getDashboardRoute();

            return $this->json([
                'success'   => true,
                'message'   => 'Login realizado com sucesso',
                'user'      => [
                    'id'    => $user['id']    ?? null,
                    'name'  => $user['name']  ?? $user['nome'] ?? 'Desconhecido',
                    'email' => $user['email'] ?? '',
                    'role'  => $user['role'] ?? 'guest',
                ],
                'role'      => $user['role'] ?? 'guest',
                'redirect'  => $redirect,
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => $result['message'] ?? 'Falha na autenticação'
        ], 401);
    }

    public function session(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('auth:read')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: auth:read'
            ], 403);
        }

        $user = Rbac::getUser();

        if ($user) {
            return $this->json([
                'success'  => true,
                'logged_in' => true,
                'user' => [
                    'id'    => $user['id']    ?? null,
                    'name'  => $user['name']  ?? $user['nome'] ?? 'Desconhecido',
                    'email' => $user['email'] ?? '',
                    'role'  => $user['role'] ?? 'guest',
                ],
            ]);
        }

        return $this->json([
            'success'    => true,
            'logged_in'  => false,
            'user'       => null,
        ]);
    }

    public function logout(): Response
    {
        if (!ApiKeyMiddleware::hasPermission('auth:login')) {
            return $this->json([
                'success' => false,
                'message' => 'Permissão negada: auth:login'
            ], 403);
        }

        Rbac::logout();
        Csrf::forget();
        Session::destroy();

        return $this->json([
            'success' => true,
            'message' => 'Logout realizado'
        ]);
    }
}

<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Application;
use App\Auth\Rbac;

class AuthController extends Controller
{
    public function login(): Response
    {
        if (!Csrf::hasToken()) {
            Csrf::generate();
        }
        
        $app = Application::getInstance();
        $theme = $app->getTheme();
        
        return $this->view('auth/login', [
            'title' => 'Login - NovoFramework',
            'theme' => $theme,
            'baseUrl' => $app->baseUrl(),
        ]);
    }

    public function doLogin(): Response
    {
        // Rate limiting por IP — nao por sessao (bypassavel)
        $maxAttempts = 5;
        $lockoutTime = 900;

        $ip = $this->request->ip();
        $storageDir = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $lockFile = $storageDir . '/.login_lock_' . md5($ip);

        if (file_exists($lockFile)) {
            $lockData = json_decode(file_get_contents($lockFile), true);
            $timeElapsed = time() - $lockData['time'];
            if ($timeElapsed < $lockoutTime) {
                $remaining = ceil(($lockoutTime - $timeElapsed) / 60);
                return $this->json([
                    'success' => false,
                    'message' => "Muitas tentativas. Aguarde {$remaining} minutos."
                ], 429);
            } else {
                @unlink($lockFile);
            }
        }

        $attemptsFile = $storageDir . '/.login_attempts_' . md5($ip);
        $attempts = ['count' => 0, 'first_attempt' => time()];
        if (file_exists($attemptsFile)) {
            $attempts = json_decode(file_get_contents($attemptsFile), true);
            $timeElapsed = time() - $attempts['first_attempt'];
            if ($timeElapsed >= $lockoutTime) {
                $attempts = ['count' => 0, 'first_attempt' => time()];
            }
        }
        
        $email = $this->input('email') ?? $this->post('email');
        $password = $this->input('password') ?? $this->post('password');
        
        // CSRF obrigatoria no login
        $csrfInput = $this->input('_csrf_token') ?? $this->post('_csrf_token');
        
        if (!$csrfInput || !Csrf::validate($csrfInput)) {
            return $this->json(['success' => false, 'message' => 'Token CSRF inválido ou ausente'], 403);
        }

        $loginResult = Rbac::login($email, $password);

        if ($loginResult['success']) {
            // Rbac::login ja chama session_regenerate_id
            @unlink($attemptsFile);
            Csrf::regenerate();

            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            $redirect = $baseUrl . Rbac::getDashboardRoute();

            return $this->json(['success' => true, 'redirect' => $redirect]);
        }

        $attempts['count']++;
        if ($attempts['count'] == 1) {
            $attempts['first_attempt'] = time();
        }
        file_put_contents($attemptsFile, json_encode($attempts));

        if ($attempts['count'] >= $maxAttempts) {
            file_put_contents($lockFile, json_encode(['time' => time()]));
        }
        
        $remaining = $maxAttempts - $attempts['count'];
        $message = $loginResult['message'];
        if ($remaining > 0) {
            $message .= " ({$remaining} tentativas restantes)";
        }
        
        return $this->json(['success' => false, 'message' => $message], 401);
    }

    public function logout(): Response
    {
        if ($this->request->method() !== 'POST') {
            $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
            return $this->redirect($baseUrl . '/auth/login');
        }

        Csrf::forget();
        Rbac::logout();

        $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
        return $this->redirect($baseUrl . '/auth/login');
    }

    public function sessionInfo(): Response
    {
        $user = Rbac::getUser();
        
        if ($user) {
            return $this->json([
                'logged_in' => true,
                'user' => [
                    'id' => $user['id'] ?? null,
                    'nome' => $user['name'] ?? $user['nome'] ?? 'Desconhecido',
                    'email' => $user['email'] ?? '',
                    'role' => $user['role'] ?? 'guest'
                ]
            ]);
        }
        
        return $this->json([
            'logged_in' => false,
            'user' => null
        ]);
    }
}

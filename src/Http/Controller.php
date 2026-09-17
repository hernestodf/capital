<?php

namespace App\Http;

use App\Core\Request;
use App\Core\Response;
use App\Core\Env;

abstract class Controller
{
    protected Request $request;
    protected array $data = [];
    protected string $baseUrl;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    }

    protected function view(string $view, array $data = [], int $statusCode = 200): Response
    {
        $this->data = array_merge($this->data, $data);
        
        // Adicionar baseUrl automaticamente para todas as views
        $baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
        $this->data['baseUrl'] = $baseUrl;
        
        return \App\Core\Application::getInstance()->view($view, $this->data, $statusCode);
    }

    protected function json(mixed $data, int $statusCode = 200): Response
    {
        return \App\Core\Response::json($data, $statusCode);
    }

    protected function redirect(string $url): Response
    {
        return \App\Core\Response::redirect($url);
    }

    protected function back(): Response
    {
        $referer = $this->request->server('HTTP_REFERER', '/');
        return $this->redirect($referer);
    }

    protected function input(?string $key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }

    protected function get(?string $key = null, $default = null)
    {
        return $this->request->get($key, $default);
    }

    protected function post(?string $key = null, $default = null)
    {
        // Se for JSON request (detecta via Content-Type ou body JSON)
        $contentType = $this->request->header('Content-Type');
        if (!$contentType) {
            // Fallback: verificar CONTENT_TYPE do $_SERVER
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        }

        if (str_contains($contentType, 'application/json')) {
            return $this->request->input($key, $default);
        }

        // Caso contrário, usar post() do request
        return $this->request->post($key, $default);
    }

    protected function isAjax(): bool
    {
        return strtolower($this->request->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}
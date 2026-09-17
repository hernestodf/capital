<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private $body;

    public function __construct($body = '', int $statusCode = 200)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
    }

    public static function make($body = '', int $statusCode = 200): self
    {
        return new self($body, $statusCode);
    }

    public static function json(mixed $data, int $statusCode = 200): self
    {
        $response = new self(json_encode($data, JSON_UNESCAPED_UNICODE), $statusCode);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

    public static function html(string $html, int $statusCode = 200): self
    {
        $response = new self($html, $statusCode);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        $response = new self('', $statusCode);
        $response->header('Location', $url);
        return $response;
    }

    public static function download(string $file, ?string $name = null): self
    {
        $name = $name ?? basename($file);
        // Sanitize filename: remove CRLF and path traversal chars
        $name = preg_replace('/[\r\n]/', '', $name);
        $name = basename($name);
        $response = new self(file_get_contents($file));
        $response->header('Content-Type', 'application/octet-stream');
        $response->header('Content-Disposition', 'attachment; filename="' . $name . '"');
        $response->header('Content-Length', filesize($file));
        return $response;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function statusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        if (!headers_sent()) {
            // X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy e
            // Content-Security-Policy sao definidos por "Header always set" em public/.htaccess,
            // que roda depois do PHP e sempre substitui (nao soma) o valor aqui — declara-los
            // duas vezes so escondia qual politica de fato chega ao navegador (ver bug-035).
            // .htaccess e a fonte de verdade para esses headers; so Permissions-Policy fica aqui.
            header("Permissions-Policy: geolocation=(self), camera=(self), microphone=()");
        }
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        $isJson = false;
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === 'content-type' && str_contains($value, 'application/json')) {
                $isJson = true;
                break;
            }
        }
        if ($isJson && ob_get_length() > 0) {
            ob_clean();
        }
        
        echo $this->body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

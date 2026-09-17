<?php

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;
    private string $prefix;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60, string $prefix = 'rate_limit')
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->prefix = $prefix;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->getKey($request);
        $now = time();

        // Get or initialize rate limit data
        $data = $_SESSION[$this->prefix][$key] ?? ['count' => 0, 'reset_at' => $now + $this->windowSeconds];

        // Reset if window expired
        if ($now >= $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => $now + $this->windowSeconds];
        }

        // Check limit
        if ($data['count'] >= $this->maxRequests) {
            $retryAfter = $data['reset_at'] - $now;

            if ($this->isAjax($request)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Muitas requisições. Tente novamente em ' . $retryAfter . ' segundos.',
                    'retry_after' => $retryAfter,
                ], 429);
            }

            return Response::json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $retryAfter,
            ], 429);
        }

        // Increment counter
        $data['count']++;
        $_SESSION[$this->prefix][$key] = $data;

        // Add rate limit headers
        $response = $next($request);
        $response->header('X-RateLimit-Limit', (string)$this->maxRequests);
        $response->header('X-RateLimit-Remaining', (string)max(0, $this->maxRequests - $data['count']));
        $response->header('X-RateLimit-Reset', (string)$data['reset_at']);

        return $response;
    }

    private function getKey(Request $request): string
    {
        // Use authenticated user ID if available, otherwise IP
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            return 'user_' . $userId;
        }

        $ip = $request->server('REMOTE_ADDR', '0.0.0.0');
        return 'ip_' . $ip;
    }

    private function isAjax(Request $request): bool
    {
        return strtolower($request->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Create rate limiter with custom settings.
     */
    public static function create(int $maxRequests = 60, int $windowSeconds = 60, string $prefix = 'rate_limit'): self
    {
        return new self($maxRequests, $windowSeconds, $prefix);
    }

    /**
     * Strict rate limiter for auth endpoints.
     */
    public static function strict(): self
    {
        return new self(5, 300, 'rate_limit_auth');
    }

    /**
     * API rate limiter.
     */
    public static function api(): self
    {
        return new self(120, 60, 'rate_limit_api');
    }
}

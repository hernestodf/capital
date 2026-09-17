<?php

namespace App\Http\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Http\MiddlewareInterface;
use App\Database\Connection;

/**
 * Middleware de autenticação via API Key
 * 
 * Valida API Keys no header X-API-Key
 * Verifica permissões, rate limit e IP whitelist
 */
class ApiKeyMiddleware implements MiddlewareInterface
{
    private static ?array $currentApiKey = null;

    /**
     * Handle da requisição
     */
    public function handle(Request $request, callable $next): Response
    {
        // Obter API key do header (case-insensitive)
        $apiKey = $request->header('X-Api-Key');
        
        if (!$apiKey) {
            return new Response()->json([
                'success' => false,
                'message' => 'API Key não fornecida. Envie no header: X-API-Key'
            ], 401);
        }

        // Hash da API key para comparação
        $apiKeyHash = hash('sha256', $apiKey);

        // Buscar API key no banco
        $db = Connection::get();
        $stmt = $db->prepare("
            SELECT id, name, api_key, api_key_public, permissions, rate_limit, ip_whitelist, status
            FROM api_keys
            WHERE api_key = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$apiKeyHash]);
        $apiKeyData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$apiKeyData) {
            return new Response()->json([
                'success' => false,
                'message' => 'API Key inválida ou inativa'
            ], 401);
        }

        // Verificar IP whitelist (se configurado)
        if (!empty($apiKeyData['ip_whitelist'])) {
            $allowedIps = json_decode($apiKeyData['ip_whitelist'], true);
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
            
            if (!in_array($clientIp, $allowedIps)) {
                return new Response()->json([
                    'success' => false,
                    'message' => 'IP não autorizado'
                ], 403);
            }
        }

        // Verificar rate limit
        if (!$this->checkRateLimit($apiKeyData['id'], $apiKeyData['rate_limit'])) {
            return new Response()->json([
                'success' => false,
                'message' => 'Rate limit excedido. Máximo ' . $apiKeyData['rate_limit'] . ' requests por hora.'
            ], 429);
        }

        // Atualizar último uso
        $stmt = $db->prepare("UPDATE api_keys SET last_used_at = NOW() WHERE id = ?");
        $stmt->execute([$apiKeyData['id']]);

        // Armazenar dados da API key (static property, not $GLOBALS)
        self::$currentApiKey = $apiKeyData;

        // Log da requisição
        $this->logRequest($apiKeyData['id'], $request);

        return $next($request);
    }

    /**
     * Retorna a API key atual (se autenticada)
     */
    public static function getCurrentApiKey(): ?array
    {
        return self::$currentApiKey;
    }

    /**
     * Verifica rate limit por hora
     */
    private function checkRateLimit(int $apiKeyId, int $maxRequests): bool
    {
        $db = Connection::get();
        
        // Contar requests na última hora
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM api_request_logs
            WHERE api_key_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$apiKeyId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] < $maxRequests;
    }

    /**
     * Verifica se API key tem permissão específica
     */
    public static function hasPermission(string $permission): bool
    {
        if (self::$currentApiKey === null) {
            return false;
        }

        $permissions = json_decode(self::$currentApiKey['permissions'] ?? '[]', true);
        
        // Verificar permissão exata ou wildcard
        return in_array($permission, $permissions) || 
               in_array('*:write', $permissions) ||
               in_array('*:read', $permissions);
    }

    /**
     * Log de requisição para rate limiting
     */
    private function logRequest(int $apiKeyId, Request $request): void
    {
        try {
            $db = Connection::get();
            $stmt = $db->prepare("
                INSERT INTO api_request_logs (api_key_id, endpoint, method, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $apiKeyId,
                $request->uri(),
                $request->method(),
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (\Throwable $e) {
            // Silenciar erros de log
        }
    }
}

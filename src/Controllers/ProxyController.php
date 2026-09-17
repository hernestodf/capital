<?php
/**
 * ProxyController - Endpoints proxy para APIs externas
 * Contorna CSP do servidor que bloqueia connect-src para domínios externos.
 */

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;

class ProxyController extends Controller
{
    /**
     * Proxy para BrasilAPI CNPJ
     * Uso: GET /proxy/cnpj/12345678000100
     */
    public function cnpj(string $cnpj): Response
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        $url = 'https://brasilapi.com.br/api/cnpj/v1/' . $cnpj;
        $result = $this->fetchUrl($url);

        if ($result === null) {
            return $this->json(['success' => false, 'error' => 'Falha ao buscar CNPJ'], 502);
        }

        return $this->json($result);
    }

    /**
     * Proxy para ViaCEP
     * Uso: GET /proxy/cep/72010010
     */
    public function cep(string $cep): Response
    {
        $cep = preg_replace('/\D/', '', $cep);

        $url = 'https://viacep.com.br/ws/' . $cep . '/json/';
        $result = $this->fetchUrl($url);

        if ($result === null) {
            return $this->json(['success' => false, 'error' => 'Falha ao buscar CEP'], 502);
        }

        return $this->json($result);
    }

    private function fetchUrl(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'SisLoc/2.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            return null;
        }

        return json_decode($response, true) ?? null;
    }
}

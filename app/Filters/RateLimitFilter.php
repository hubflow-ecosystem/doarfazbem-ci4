<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate Limiting Filter
 * Protege contra brute force e DDoS
 */
class RateLimitFilter implements FilterInterface
{
    /**
     * Limites por endpoint (requests por minuto)
     */
    protected array $limits = [
        'login' => 5,           // 5 tentativas por minuto
        'register' => 3,        // 3 registros por minuto
        'forgot-password' => 3, // 3 solicitações por minuto
        'donations' => 10,      // 10 doações por minuto
        'rifas' => 20,          // 20 compras por minuto
        'webhook' => 100,       // 100 webhooks por minuto
        'default' => 60,        // 60 requests por minuto (padrão)
    ];

    /**
     * Tempo de bloqueio em segundos após exceder limite
     */
    protected int $blockTime = 300; // 5 minutos

    public function before(RequestInterface $request, $arguments = null)
    {
        $ip = $request->getIPAddress();
        $path = $request->getPath();

        // Determinar limite baseado no endpoint
        $limit = $this->getLimit($path);
        $key = "ratelimit_" . md5($ip) . "_" . $this->getEndpointKey($path);
        $blockKey = "ratelimit_blocked_" . md5($ip);

        $cache = \Config\Services::cache();

        // Verificar se IP está bloqueado
        if ($cache->get($blockKey)) {
            log_message('warning', "RATE LIMIT: IP {$ip} bloqueado tentando acessar {$path}");
            return $this->tooManyRequests();
        }

        // Obter contador atual
        $data = $cache->get($key);

        if ($data === null) {
            // Primeira requisição
            $cache->save($key, ['count' => 1, 'first' => time()], 60);
            return;
        }

        $count = $data['count'];
        $firstRequest = $data['first'];
        $elapsed = time() - $firstRequest;

        // Se passou 1 minuto, resetar contador
        if ($elapsed >= 60) {
            $cache->save($key, ['count' => 1, 'first' => time()], 60);
            return;
        }

        // Incrementar contador
        $count++;

        if ($count > $limit) {
            // Bloquear IP
            $cache->save($blockKey, true, $this->blockTime);

            log_message('warning', "RATE LIMIT EXCEEDED: IP {$ip} bloqueado por {$this->blockTime}s - {$count} requests em {$elapsed}s para {$path}");

            return $this->tooManyRequests();
        }

        // Atualizar contador
        $cache->save($key, ['count' => $count, 'first' => $firstRequest], 60 - $elapsed);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Adicionar headers de rate limit
        $ip = $request->getIPAddress();
        $path = $request->getPath();
        $key = "ratelimit_" . md5($ip) . "_" . $this->getEndpointKey($path);
        $limit = $this->getLimit($path);

        $cache = \Config\Services::cache();
        $data = $cache->get($key);

        $remaining = $limit - ($data['count'] ?? 0);
        $reset = ($data['first'] ?? time()) + 60;

        $response->setHeader('X-RateLimit-Limit', (string) $limit);
        $response->setHeader('X-RateLimit-Remaining', (string) max(0, $remaining));
        $response->setHeader('X-RateLimit-Reset', (string) $reset);

        return $response;
    }

    /**
     * Obter limite para o endpoint
     */
    protected function getLimit(string $path): int
    {
        foreach ($this->limits as $endpoint => $limit) {
            if (strpos($path, $endpoint) !== false) {
                return $limit;
            }
        }
        return $this->limits['default'];
    }

    /**
     * Obter chave do endpoint para cache
     */
    protected function getEndpointKey(string $path): string
    {
        foreach (array_keys($this->limits) as $endpoint) {
            if (strpos($path, $endpoint) !== false) {
                return $endpoint;
            }
        }
        return 'default';
    }

    /**
     * Resposta 429 Too Many Requests
     */
    protected function tooManyRequests(): ResponseInterface
    {
        $response = \Config\Services::response();

        return $response
            ->setStatusCode(429)
            ->setHeader('Retry-After', (string) $this->blockTime)
            ->setJSON([
                'error' => 'Muitas requisições. Tente novamente em ' . ($this->blockTime / 60) . ' minutos.',
                'retry_after' => $this->blockTime
            ]);
    }
}

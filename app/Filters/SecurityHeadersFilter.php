<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Security Headers Filter
 * Adiciona headers de segurança em todas as respostas
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nada a fazer antes
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // X-Content-Type-Options: Previne MIME type sniffing
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options: Previne clickjacking
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');

        // X-XSS-Protection: Ativa proteção XSS do browser
        $response->setHeader('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Controla informações de referrer
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Restringe APIs do browser
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Em produção, adicionar HSTS
        if (ENVIRONMENT === 'production') {
            // Strict-Transport-Security: Força HTTPS
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

            // Content-Security-Policy: Restringe origens de conteúdo
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.google.com https://www.gstatic.com https://js.stripe.com",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
                "img-src 'self' data: https: blob:",
                "connect-src 'self' https://api.asaas.com https://api.mercadopago.com https://www.google-analytics.com",
                "frame-src 'self' https://accounts.google.com https://www.google.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]);
            $response->setHeader('Content-Security-Policy', $csp);
        }

        return $response;
    }
}

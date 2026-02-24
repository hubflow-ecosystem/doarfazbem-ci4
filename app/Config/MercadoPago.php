<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MercadoPago extends BaseConfig
{
    /**
     * Ambiente: 'sandbox' ou 'production'
     */
    public string $environment = 'sandbox';

    /**
     * Credenciais de Sandbox (Teste)
     */
    public string $sandboxPublicKey = '';
    public string $sandboxAccessToken = '';

    /**
     * Credenciais de Producao
     */
    public string $productionPublicKey = '';
    public string $productionAccessToken = '';

    /**
     * URL base da API
     */
    public string $apiUrl = 'https://api.mercadopago.com';

    /**
     * Tempo de expiracao do PIX em minutos
     */
    public int $pixExpirationMinutes = 30;

    /**
     * Taxa do Mercado Pago (1% para PIX)
     */
    public float $pixFeePercent = 1.0;

    /**
     * Construtor - carrega do banco de dados e .env
     */
    public function __construct()
    {
        parent::__construct();

        // Carregar configurações do banco de dados
        helper('settings');

        // Ambiente do banco de dados tem prioridade
        $dbEnvironment = setting('mercadopago_environment');
        if ($dbEnvironment !== null) {
            $this->environment = $dbEnvironment;
        } else {
            // Fallback para .env
            $this->environment = env('mercadopago.environment', 'sandbox');
        }

        // Credenciais sempre vêm do .env por segurança
        $this->sandboxPublicKey = env('mercadopago.sandbox.public_key', '');
        $this->sandboxAccessToken = env('mercadopago.sandbox.access_token', '');

        $this->productionPublicKey = env('mercadopago.production.public_key', '');
        $this->productionAccessToken = env('mercadopago.production.access_token', '');
    }

    /**
     * Retorna Public Key do ambiente atual
     */
    public function getPublicKey(): string
    {
        return $this->environment === 'production'
            ? $this->productionPublicKey
            : $this->sandboxPublicKey;
    }

    /**
     * Retorna Access Token do ambiente atual
     */
    public function getAccessToken(): string
    {
        return $this->environment === 'production'
            ? $this->productionAccessToken
            : $this->sandboxAccessToken;
    }

    /**
     * Verifica se esta em producao
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Verifica se as credenciais estao configuradas
     */
    public function isConfigured(): bool
    {
        return !empty($this->getAccessToken());
    }
}

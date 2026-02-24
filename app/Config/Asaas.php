<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configurações da API Asaas
 *
 * Gateway de pagamento brasileiro que oferece:
 * - Pagamentos via PIX, Cartão de Crédito e Boleto
 * - Sistema de Split Payment (divisão automática)
 * - Criação de subcontas para criadores de campanhas
 * - Webhooks para notificações em tempo real
 */
class Asaas extends BaseConfig
{
    /**
     * Ambiente de execução
     * Valores: 'sandbox' | 'production'
     * NOTA: Agora é carregado do banco de dados (settings)
     */
    public string $environment = 'sandbox';

    /**
     * URLs da API
     */
    public string $apiUrlSandbox = 'https://api-sandbox.asaas.com/v3/';
    public string $apiUrlProduction = 'https://api.asaas.com/v3/';

    /**
     * Credenciais Sandbox (Testes)
     * IMPORTANTE: Carregar do .env, NAO colocar valores aqui
     */
    public string $apiKeySandbox = '';
    public string $walletIdSandbox = '';

    /**
     * Credenciais Produção
     * IMPORTANTE: Carregar do .env, NAO colocar valores aqui
     */
    public string $apiKeyProduction = '';
    public string $walletIdProduction = '';

    public function __construct()
    {
        parent::__construct();

        // Carregar credenciais do .env (NUNCA hardcoded!)
        $this->apiKeySandbox = env('ASAAS_SANDBOX_API_KEY', '');
        $this->walletIdSandbox = env('ASAAS_SANDBOX_WALLET_ID', '');
        $this->apiKeyProduction = env('ASAAS_PRODUCTION_API_KEY', '');
        $this->walletIdProduction = env('ASAAS_PRODUCTION_WALLET_ID', '');

        // Validar credenciais em producao
        if (ENVIRONMENT === 'production') {
            if (empty($this->apiKeyProduction)) {
                log_message('critical', 'ASAAS_PRODUCTION_API_KEY nao configurada!');
            }
        }

        // Carregar configurações do banco de dados
        helper('settings');

        // Ambiente (sandbox/production)
        $dbEnvironment = setting('asaas_environment');
        if ($dbEnvironment !== null) {
            $this->environment = $dbEnvironment;
        }

        // Taxas da plataforma do banco de dados
        $medicalFee = setting('platform_fee_medical');
        $socialFee = setting('platform_fee_social');
        $otherFee = setting('platform_fee_other');

        if ($medicalFee !== null) {
            $this->platformFees['medical'] = (float) $medicalFee;
        }
        if ($socialFee !== null) {
            $this->platformFees['social'] = (float) $socialFee;
        }
        if ($otherFee !== null) {
            $this->platformFees['other'] = (float) $otherFee;
        }
    }

    /**
     * Configurações de Webhook
     */
    public string $webhookUrl = 'http://doarfazbem.ai/webhook/asaas';
    public string $webhookEmail = 'contato@doarfazbem.com.br';

    /**
     * Webhook Secret para validação HMAC
     * Deve ser configurado no painel do Asaas e no .env
     * IMPORTANTE: Usar uma string aleatória forte de 32+ caracteres
     */
    public string $webhookSecretSandbox = '';
    public string $webhookSecretProduction = '';

    /**
     * Retorna o Webhook Secret de acordo com o ambiente
     */
    public function getWebhookSecret(): string
    {
        // Primeiro tenta pegar do .env (suporta ambos formatos)
        $envSecret = env('ASAAS_WEBHOOK_TOKEN', '') ?: env('asaas.webhook_secret', '');
        if (!empty($envSecret)) {
            return $envSecret;
        }

        return $this->environment === 'production'
            ? $this->webhookSecretProduction
            : $this->webhookSecretSandbox;
    }

    /**
     * Taxas do Gateway Asaas (valores oficiais 2025)
     * Estrutura: valor fixo (R$) + percentual (%)
     * Fonte: https://www.asaas.com/precos-e-taxas
     */
    public array $fees = [
        'pix' => [
            'fixed' => 0.95,  // R$ 0,95 por transação (taxa com desconto)
            'percent' => 0.0,
        ],
        'boleto' => [
            'fixed' => 0.99,  // R$ 0,99 por boleto (taxa com desconto)
            'percent' => 0.0,
        ],
        'credit_card' => [
            'fixed' => 0.49,   // R$ 0,49 por transação
            'percent' => 1.99, // 1,99% do valor (à vista)
        ],
        'credit_card_2_6' => [
            'fixed' => 0.49,   // R$ 0,49 por transação
            'percent' => 2.49, // 2,49% do valor (2-6 parcelas)
        ],
        'credit_card_7_12' => [
            'fixed' => 0.49,   // R$ 0,49 por transação
            'percent' => 2.99, // 2,99% do valor (7-12 parcelas)
        ],
    ];

    /**
     * Taxas da Plataforma DoarFazBem
     */
    public array $platformFees = [
        'medical' => 0.0,  // 0% para campanhas médicas
        'social' => 2.0,   // 2% para campanhas sociais
        'other' => 2.0,    // 2% para outras campanhas
    ];

    /**
     * Configurações de Split Payment
     */
    public array $split = [
        'enabled' => true,
        'walletId' => '8e3acaa3-5040-436c-83fc-cff9b8c1b326', // Wallet da plataforma
    ];

    /**
     * Timeout para requisições HTTP (em segundos)
     */
    public int $timeout = 30;

    /**
     * Habilitar logs detalhados
     */
    public bool $debug = true;

    /**
     * Métodos auxiliares
     */

    /**
     * Retorna a URL base da API de acordo com o ambiente
     */
    public function getApiUrl(): string
    {
        return $this->environment === 'production'
            ? $this->apiUrlProduction
            : $this->apiUrlSandbox;
    }

    /**
     * Retorna a API Key de acordo com o ambiente
     */
    public function getApiKey(): string
    {
        return $this->environment === 'production'
            ? $this->apiKeyProduction
            : $this->apiKeySandbox;
    }

    /**
     * Retorna o Wallet ID de acordo com o ambiente
     */
    public function getWalletId(): string
    {
        return $this->environment === 'production'
            ? $this->walletIdProduction
            : $this->walletIdSandbox;
    }

    /**
     * Verifica se está em modo de produção
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Verifica se está em modo sandbox
     */
    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox';
    }

    /**
     * Calcula a taxa total para um tipo de campanha e método de pagamento
     *
     * @param string $campaignType Tipo da campanha (medical, social, other)
     * @param string $paymentMethod Método de pagamento (pix, boleto, credit_card)
     * @param float $amount Valor da doação
     * @return array ['gateway_fee' => float, 'platform_fee' => float, 'total_fee' => float]
     */
    public function calculateFees(string $campaignType, string $paymentMethod, float $amount): array
    {
        // Taxa do gateway
        $gatewayFixed = $this->fees[$paymentMethod]['fixed'] ?? 0;
        $gatewayPercent = $this->fees[$paymentMethod]['percent'] ?? 0;
        $gatewayFee = $gatewayFixed + ($amount * ($gatewayPercent / 100));

        // Taxa da plataforma
        $platformPercent = $this->platformFees[$campaignType] ?? $this->platformFees['other'];
        $platformFee = $amount * ($platformPercent / 100);

        return [
            'gateway_fee' => round($gatewayFee, 2),
            'platform_fee' => round($platformFee, 2),
            'total_fee' => round($gatewayFee + $platformFee, 2),
            'net_amount' => round($amount - $gatewayFee - $platformFee, 2),
        ];
    }

    /**
     * Retorna a taxa da plataforma para um tipo de campanha
     */
    public function getPlatformFee(string $campaignType): float
    {
        return $this->platformFees[$campaignType] ?? $this->platformFees['other'];
    }

    /**
     * Calcula valores quando DOADOR paga as taxas
     * Retorna valor arredondado para cima (sempre inteiro)
     *
     * @param string $campaignType Tipo da campanha
     * @param string $paymentMethod Método de pagamento
     * @param float $donationAmount Valor que o criador vai receber
     * @return array Detalhes completos do cálculo
     */
    public function calculateDonorPaysAmour($campaignType, string $paymentMethod, float $donationAmount): array
    {
        // Taxa do gateway
        $gatewayFixed = $this->fees[$paymentMethod]['fixed'] ?? 0;
        $gatewayPercent = $this->fees[$paymentMethod]['percent'] ?? 0;

        // Calcular taxa do gateway (R$ fixo + % do valor)
        if ($gatewayPercent > 0) {
            // Quando há taxa percentual + fixa, precisa calcular o valor bruto que gera o líquido desejado
            // Fórmula: valor_bruto = (valor_liquido + taxa_fixa) / (1 - taxa_percent/100)
            // A taxa total é: valor_bruto - valor_liquido
            $valorBruto = ($donationAmount + $gatewayFixed) / (1 - $gatewayPercent / 100);
            $gatewayFee = $valorBruto - $donationAmount;
        } else {
            // Apenas taxa fixa
            $gatewayFee = $gatewayFixed;
        }

        // Taxa da plataforma: sempre 1% adicional quando doador paga
        $platformFeePercent = 1.0; // Sempre 1% adicional
        $platformFee = $donationAmount * ($platformFeePercent / 100);

        // Valor bruto (antes do arredondamento)
        $totalBeforeRounding = $donationAmount + $gatewayFee + $platformFee;

        // Arredonda para cima (próximo inteiro)
        $totalRounded = ceil($totalBeforeRounding);

        // Excedente (centavos que sobram) vai para plataforma
        $roundingExtra = $totalRounded - $totalBeforeRounding;

        return [
            'donation_amount' => round($donationAmount, 2), // Valor que criador recebe
            'gateway_fee' => round($gatewayFee, 2),
            'platform_fee' => round($platformFee, 2),
            'rounding_extra' => round($roundingExtra, 2), // Centavos extra para plataforma
            'platform_total' => round($platformFee + $roundingExtra, 2), // Total da plataforma
            'total_before_rounding' => round($totalBeforeRounding, 2),
            'total_to_pay' => $totalRounded, // Valor que doador paga (inteiro)
        ];
    }

    /**
     * Calcula valores quando CRIADOR paga as taxas (padrão)
     *
     * @param string $campaignType Tipo da campanha
     * @param string $paymentMethod Método de pagamento
     * @param float $donationAmount Valor doado
     * @return array Detalhes do cálculo
     */
    public function calculateCreatorPaysAmount(string $campaignType, string $paymentMethod, float $donationAmount): array
    {
        // Taxa do gateway
        $gatewayFixed = $this->fees[$paymentMethod]['fixed'] ?? 0;
        $gatewayPercent = $this->fees[$paymentMethod]['percent'] ?? 0;
        $gatewayFee = $gatewayFixed + ($donationAmount * ($gatewayPercent / 100));

        // Taxa da plataforma (1% exceto médicas que é 0%)
        $platformPercent = $this->platformFees[$campaignType] ?? $this->platformFees['other'];

        // Taxa da plataforma é sobre o líquido (após gateway)
        $netAfterGateway = $donationAmount - $gatewayFee;
        $platformFee = $netAfterGateway * ($platformPercent / 100);

        // Valor que o criador recebe
        $creatorReceives = $netAfterGateway - $platformFee;

        return [
            'donation_amount' => round($donationAmount, 2),
            'gateway_fee' => round($gatewayFee, 2),
            'platform_fee' => round($platformFee, 2),
            'creator_receives' => round($creatorReceives, 2),
            'total_fees' => round($gatewayFee + $platformFee, 2),
        ];
    }
}

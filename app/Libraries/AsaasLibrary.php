<?php

namespace App\Libraries;

use Config\Asaas as AsaasConfig;

/**
 * Biblioteca de Integração com API Asaas
 *
 * Esta biblioteca fornece métodos para interagir com a API do Asaas,
 * incluindo criação de subcontas, cobranças, split payment e webhooks.
 *
 * Documentação oficial: https://docs.asaas.com
 */
class AsaasLibrary
{
    protected AsaasConfig $config;
    protected string $apiUrl;
    protected string $apiKey;
    protected int $timeout;
    protected bool $debug;

    public function __construct()
    {
        $this->config = config('Asaas');
        $this->apiUrl = $this->config->getApiUrl();
        $this->apiKey = $this->config->getApiKey();
        $this->timeout = $this->config->timeout;
        $this->debug = $this->config->debug;
    }

    /**
     * ==========================================
     * SUBCONTAS (ACCOUNTS)
     * ==========================================
     */

    /**
     * Cria uma subconta no Asaas para um criador de campanha
     *
     * @param array $data Dados da subconta
     * @return array Resposta da API
     */
    public function createAccount(array $data): array
    {
        $endpoint = 'accounts';

        // Telefone - usa o mesmo valor para phone e mobilePhone
        $phone = $data['mobile_phone'] ?? $data['phone'] ?? null;

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => $data['cpf_cnpj'],
            'birthDate' => $data['birth_date'] ?? null,
            'phone' => $phone,
            'mobilePhone' => $phone,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postalCode' => $data['postal_code'] ?? null,
            'incomeValue' => $data['income_value'] ?? null,
            'companyType' => $data['company_type'] ?? null,
        ];

        // Log dos dados sendo enviados para debug (usando error para garantir que apareça)
        log_message('error', 'Asaas createAccount payload: ' . json_encode($payload));

        $result = $this->request('POST', $endpoint, $payload);

        // Log do resultado
        if (!$result['success']) {
            log_message('error', 'Asaas createAccount error: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Busca informações de uma subconta
     *
     * @param string $accountId ID da subconta
     * @return array Resposta da API
     */
    public function getAccount(string $accountId): array
    {
        return $this->request('GET', "accounts/{$accountId}");
    }

    /**
     * Atualiza informações de uma subconta
     *
     * @param string $accountId ID da subconta
     * @param array $data Dados para atualizar
     * @return array Resposta da API
     */
    public function updateAccount(string $accountId, array $data): array
    {
        return $this->request('PUT', "accounts/{$accountId}", $data);
    }

    /**
     * ==========================================
     * COBRANÇAS (PAYMENTS)
     * ==========================================
     */

    /**
     * Cria uma cobrança (PIX, Boleto ou Cartão)
     *
     * @param array $data Dados da cobrança
     * @return array Resposta da API
     */
    public function createPayment(array $data): array
    {
        $endpoint = 'payments';

        $payload = [
            'customer' => $data['customer_id'], // ID do cliente no Asaas
            'billingType' => $data['billing_type'], // PIX, BOLETO, CREDIT_CARD
            'value' => $data['value'],
            'dueDate' => $data['due_date'],
            'description' => $data['description'] ?? null,
            'externalReference' => $data['external_reference'] ?? null,
            'postalService' => false,
        ];

        // Split payment (se habilitado)
        if ($this->config->split['enabled'] && isset($data['split'])) {
            $payload['split'] = $data['split'];
        }

        // Dados específicos para cartão de crédito
        if ($data['billing_type'] === 'CREDIT_CARD' && isset($data['credit_card'])) {
            $payload['creditCard'] = $data['credit_card'];
            $payload['creditCardHolderInfo'] = $data['credit_card_holder'] ?? null;
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Busca informações de uma cobrança
     *
     * @param string $paymentId ID da cobrança
     * @return array Resposta da API
     */
    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', "payments/{$paymentId}");
    }

    /**
     * Cancela uma cobrança
     *
     * @param string $paymentId ID da cobrança
     * @return array Resposta da API
     */
    public function deletePayment(string $paymentId): array
    {
        return $this->request('DELETE', "payments/{$paymentId}");
    }

    /**
     * Estorna uma cobrança
     *
     * @param string $paymentId ID da cobrança
     * @return array Resposta da API
     */
    public function refundPayment(string $paymentId): array
    {
        return $this->request('POST', "payments/{$paymentId}/refund");
    }

    /**
     * Gera QR Code para pagamento PIX
     *
     * @param string $paymentId ID da cobrança
     * @return array Resposta da API com QR Code
     */
    public function getPixQrCode(string $paymentId): array
    {
        return $this->request('GET', "payments/{$paymentId}/pixQrCode");
    }

    /**
     * Processa pagamento com cartão de crédito
     *
     * @param array $data Dados do cartão e pagamento
     * @return array Resposta da API
     */
    public function payWithCreditCard(array $data): array
    {
        $paymentId = $data['payment_id'] ?? null;

        if (!$paymentId) {
            return [
                'success' => false,
                'message' => 'ID do pagamento não fornecido',
                'data' => []
            ];
        }

        $payload = [
            'creditCard' => [
                'holderName' => $data['card_holder'],
                'number' => $data['card_number'],
                'expiryMonth' => $data['expiry_month'],
                'expiryYear' => $data['expiry_year'],
                'ccv' => $data['cvv'],
            ],
            'creditCardHolderInfo' => [
                'name' => $data['holder_name'] ?? $data['card_holder'],
                'email' => $data['holder_email'] ?? null,
                'cpfCnpj' => preg_replace('/\D/', '', $data['holder_cpf'] ?? ''),
                'postalCode' => preg_replace('/\D/', '', $data['holder_postal_code'] ?? ''),
                'addressNumber' => $data['holder_address_number'] ?? 'S/N',
                'addressComplement' => $data['holder_address_complement'] ?? null,
                'phone' => preg_replace('/\D/', '', $data['holder_phone'] ?? ''),
                'mobilePhone' => preg_replace('/\D/', '', $data['holder_mobile_phone'] ?? $data['holder_phone'] ?? ''),
            ],
            'remoteIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ];

        // Se tiver parcelamento
        if (isset($data['installment_count']) && $data['installment_count'] > 1) {
            $payload['installmentCount'] = $data['installment_count'];
        }

        return $this->request('POST', "payments/{$paymentId}/payWithCreditCard", $payload);
    }

    /**
     * ==========================================
     * CLIENTES (CUSTOMERS)
     * ==========================================
     */

    /**
     * Cria ou atualiza um cliente no Asaas
     *
     * @param array $data Dados do cliente
     * @return array Resposta da API
     */
    public function createOrUpdateCustomer(array $data): array
    {
        $endpoint = 'customers';

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => $data['cpf_cnpj'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['mobile_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['province'] ?? null,
            'postalCode' => $data['postal_code'] ?? null,
            'externalReference' => $data['external_reference'] ?? null,
            'notificationDisabled' => $data['notification_disabled'] ?? false,
        ];

        // Se já existe um customer_id, atualiza ao invés de criar
        if (isset($data['customer_id'])) {
            return $this->request('PUT', "customers/{$data['customer_id']}", $payload);
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Busca um cliente por ID
     *
     * @param string $customerId ID do cliente
     * @return array Resposta da API
     */
    public function getCustomer(string $customerId): array
    {
        return $this->request('GET', "customers/{$customerId}");
    }

    /**
     * Busca cliente por email
     *
     * @param string $email Email do cliente
     * @return array Resposta da API
     */
    public function getCustomerByEmail(string $email): array
    {
        return $this->request('GET', 'customers', ['email' => $email]);
    }

    /**
     * ==========================================
     * ASSINATURAS (SUBSCRIPTIONS)
     * Para campanhas com doações recorrentes
     * ==========================================
     */

    /**
     * Cria uma assinatura recorrente
     *
     * @param array $data Dados da assinatura
     * @return array Resposta da API
     */
    public function createSubscription(array $data): array
    {
        $endpoint = 'subscriptions';

        $payload = [
            'customer' => $data['customer_id'],
            'billingType' => $data['billing_type'],
            'value' => $data['value'],
            'nextDueDate' => $data['next_due_date'],
            'cycle' => $data['cycle'], // WEEKLY, BIWEEKLY, MONTHLY, QUARTERLY, SEMIANNUALLY, YEARLY
            'description' => $data['description'] ?? null,
            'externalReference' => $data['external_reference'] ?? null,
        ];

        // Split payment para assinaturas
        if ($this->config->split['enabled'] && isset($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Busca informações de uma assinatura
     *
     * @param string $subscriptionId ID da assinatura
     * @return array Resposta da API
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->request('GET', "subscriptions/{$subscriptionId}");
    }

    /**
     * Cancela uma assinatura
     *
     * @param string $subscriptionId ID da assinatura
     * @return array Resposta da API
     */
    public function deleteSubscription(string $subscriptionId): array
    {
        return $this->request('DELETE', "subscriptions/{$subscriptionId}");
    }

    /**
     * ==========================================
     * TRANSFERÊNCIAS (TRANSFERS)
     * ==========================================
     */

    /**
     * Consulta saldo disponível
     *
     * @return array Resposta da API
     */
    public function getBalance(): array
    {
        return $this->request('GET', 'finance/balance');
    }

    /**
     * ==========================================
     * MÉTODOS AUXILIARES
     * ==========================================
     */

    /**
     * Realiza requisição HTTP para a API do Asaas
     *
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $endpoint Endpoint da API
     * @param array $data Dados a enviar (opcional)
     * @return array Resposta da API
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->apiUrl . $endpoint;

        // Adiciona query string para requisições GET
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            $data = [];
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $this->apiKey,
                'User-Agent: DoarFazBem/1.0',
            ],
        ]);

        // Adiciona body para POST/PUT
        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log da resposta raw para debug
        log_message('error', "Asaas API Response (raw): HTTP {$httpCode} - {$response}");

        // Log da requisição (se debug habilitado)
        if ($this->debug) {
            log_message('info', "Asaas API Request: {$method} {$url}");
            log_message('info', "Asaas API Response Code: {$httpCode}");
            if (!empty($data)) {
                log_message('info', 'Asaas API Request Data: ' . json_encode($data));
            }
            log_message('info', 'Asaas API Response: ' . $response);
        }

        // Tratamento de erros
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
            ];
        }

        $result = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => $result ?? [],
        ];
    }

    /**
     * Valida webhook do Asaas
     *
     * @param string $token Token recebido no webhook
     * @return bool
     */
    public function validateWebhookToken(string $token): bool
    {
        // Implementar validação do token do webhook
        // Por segurança, o Asaas envia um token que deve ser validado
        return !empty($token);
    }

    /**
     * Calcula split de pagamento
     *
     * @param float $amount Valor total
     * @param string $campaignType Tipo da campanha (medical, social, other)
     * @param string $campaignCreatorWalletId Wallet ID do criador da campanha
     * @return array Array com configuração de split
     */
    public function calculateSplit(float $amount, string $campaignType, string $campaignCreatorWalletId): array
    {
        $fees = $this->config->calculateFees($campaignType, 'pix', $amount);
        $platformFee = $fees['platform_fee'];

        $splits = [];

        // Split para o criador da campanha
        if ($campaignCreatorWalletId) {
            $splits[] = [
                'walletId' => $campaignCreatorWalletId,
                'fixedValue' => round($amount - $platformFee, 2),
            ];
        }

        // Split para a plataforma (se houver taxa)
        if ($platformFee > 0) {
            $splits[] = [
                'walletId' => $this->config->getWalletId(),
                'fixedValue' => $platformFee,
            ];
        }

        return $splits;
    }
}

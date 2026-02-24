<?php

namespace App\Libraries;

/**
 * Asaas API Service
 * Documentação: https://docs.asaas.com/
 * COPIADO DO MEDLIFE - TESTADO E FUNCIONANDO
 */
class AsaasService
{
    private $apiKey;
    private $environment;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = getenv('ASAAS_API_KEY');
        $this->environment = getenv('ASAAS_ENVIRONMENT') ?: 'sandbox';

        // Define base URL based on environment
        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    /**
     * Create customer in Asaas
     */
    public function createCustomer(array $data)
    {
        $endpoint = '/customers';

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => $data['cpfCnpj'] ?? $data['cpf'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['mobile_phone'] ?? null,
            'postalCode' => $data['postal_code'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['province'] ?? null,
            'externalReference' => $data['user_id'] ?? null,
            'notificationDisabled' => false,
            'additionalEmails' => $data['additional_emails'] ?? null,
        ];

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Create or update customer
     */
    public function createOrUpdateCustomer(array $data)
    {
        // Try to find existing customer by CPF
        if (!empty($data['cpfCnpj'])) {
            try {
                $existing = $this->getCustomerByCpfCnpj($data['cpfCnpj']);
                if ($existing && isset($existing['id'])) {
                    return $existing;
                }
            } catch (\Exception $e) {
                // Customer not found, will create new
            }
        }

        return $this->createCustomer($data);
    }

    /**
     * Get customer by CPF/CNPJ
     */
    public function getCustomerByCpfCnpj($cpfCnpj)
    {
        $endpoint = "/customers?cpfCnpj=" . preg_replace('/\D/', '', $cpfCnpj);
        $response = $this->request('GET', $endpoint);
        return $response['data'][0] ?? null;
    }

    /**
     * Get customer by ID
     */
    public function getCustomer($customerId)
    {
        $endpoint = "/customers/{$customerId}";
        return $this->request('GET', $endpoint);
    }

    /**
     * Update customer
     */
    public function updateCustomer($customerId, array $data)
    {
        $endpoint = "/customers/{$customerId}";
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Create PIX payment
     */
    public function createPixPayment(array $data)
    {
        $endpoint = '/payments';

        $payload = [
            'customer' => $data['customer'] ?? $data['customer_id'],
            'billingType' => 'PIX',
            'value' => $data['value'] ?? $data['amount'],
            'dueDate' => $data['dueDate'] ?? $data['due_date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? 'Doação - DoarFazBem',
            'externalReference' => $data['externalReference'] ?? $data['external_reference'] ?? $data['transaction_id'] ?? null,
            'discount' => $data['discount'] ?? null,
        ];

        // Split payment
        if (!empty($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Get PIX QR Code
     */
    public function getPixQrCode($paymentId)
    {
        $endpoint = "/payments/{$paymentId}/pixQrCode";
        return $this->request('GET', $endpoint);
    }

    /**
     * Create Boleto payment
     */
    public function createBoletoPayment(array $data)
    {
        $endpoint = '/payments';

        $payload = [
            'customer' => $data['customer'] ?? $data['customer_id'],
            'billingType' => 'BOLETO',
            'value' => $data['value'] ?? $data['amount'],
            'dueDate' => $data['dueDate'] ?? $data['due_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'description' => $data['description'] ?? 'Doação - DoarFazBem',
            'externalReference' => $data['externalReference'] ?? $data['external_reference'] ?? $data['transaction_id'] ?? null,
            'discount' => $data['discount'] ?? null,
            'fine' => $data['fine'] ?? null,
            'interest' => $data['interest'] ?? null,
        ];

        // Split payment
        if (!empty($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Create payment (generic method for any billing type)
     */
    public function createPayment(array $data)
    {
        $endpoint = '/payments';

        $payload = [
            'customer' => $data['customer'] ?? $data['customer_id'],
            'billingType' => $data['billingType'] ?? $data['billing_type'],
            'value' => $data['value'] ?? $data['amount'],
            'dueDate' => $data['dueDate'] ?? $data['due_date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? 'Doação - DoarFazBem',
            'externalReference' => $data['externalReference'] ?? $data['external_reference'] ?? $data['transaction_id'] ?? null,
        ];

        // Split payment
        if (!empty($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Create Credit Card payment
     */
    public function createCreditCardPayment(array $data)
    {
        $endpoint = '/payments';

        $payload = [
            'customer' => $data['customer'] ?? $data['customer_id'],
            'billingType' => 'CREDIT_CARD',
            'value' => $data['value'] ?? $data['amount'],
            'dueDate' => $data['dueDate'] ?? $data['due_date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? 'Doação - DoarFazBem',
            'externalReference' => $data['externalReference'] ?? $data['external_reference'] ?? $data['transaction_id'] ?? null,
            'installmentCount' => $data['installmentCount'] ?? $data['installments'] ?? $data['installment_count'] ?? 1,
            'installmentValue' => $data['installmentValue'] ?? $data['installment_value'] ?? ($data['value'] ?? $data['amount']),
        ];

        // Use saved card token if provided
        if (!empty($data['credit_card_token'])) {
            $payload['creditCardToken'] = $data['credit_card_token'];
        } else {
            // Use new card data
            $payload['creditCard'] = [
                'holderName' => $data['card_holder'] ?? $data['card_holder_name'],
                'number' => preg_replace('/\D/', '', $data['card_number']),
                'expiryMonth' => $data['expiry_month'] ?? $data['card_expiry_month'],
                'expiryYear' => $data['expiry_year'] ?? $data['card_expiry_year'],
                'ccv' => $data['cvv'] ?? $data['card_cvv']
            ];
            $payload['creditCardHolderInfo'] = [
                'name' => $data['holder_name'],
                'email' => $data['holder_email'],
                'cpfCnpj' => preg_replace('/\D/', '', $data['holder_cpf']),
                'postalCode' => preg_replace('/\D/', '', $data['holder_postal_code']),
                'addressNumber' => $data['holder_address_number'],
                'addressComplement' => $data['holder_address_complement'] ?? null,
                'phone' => preg_replace('/\D/', '', $data['holder_phone']),
                'mobilePhone' => $data['holder_mobile_phone'] ?? null,
            ];
        }

        // Split payment
        if (!empty($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Pay with credit card (existing payment)
     */
    public function payWithCreditCard(array $data)
    {
        $paymentId = $data['payment_id'];
        $endpoint = "/payments/{$paymentId}/payWithCreditCard";

        $payload = [
            'creditCard' => [
                'holderName' => $data['card_holder'],
                'number' => preg_replace('/\D/', '', $data['card_number']),
                'expiryMonth' => $data['expiry_month'],
                'expiryYear' => $data['expiry_year'],
                'ccv' => $data['cvv']
            ],
            'creditCardHolderInfo' => [
                'name' => $data['holder_name'],
                'email' => $data['holder_email'],
                'cpfCnpj' => preg_replace('/\D/', '', $data['holder_cpf']),
                'postalCode' => preg_replace('/\D/', '', $data['holder_postal_code']),
                'addressNumber' => $data['holder_address_number'],
                'addressComplement' => $data['holder_address_complement'] ?? null,
                'phone' => preg_replace('/\D/', '', $data['holder_phone']),
            ]
        ];

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Create subscription (recurring payment)
     */
    public function createSubscription(array $data)
    {
        $endpoint = '/subscriptions';

        $payload = [
            'customer' => $data['customer'] ?? $data['customer_id'],
            'billingType' => $data['billingType'] ?? $data['billing_type'],
            'value' => $data['value'] ?? $data['amount'],
            'nextDueDate' => $data['nextDueDate'] ?? $data['next_due_date'] ?? date('Y-m-d', strtotime('+1 month')),
            'cycle' => $data['cycle'] ?? 'MONTHLY',
            'description' => $data['description'] ?? 'Doação Recorrente - DoarFazBem',
            'externalReference' => $data['externalReference'] ?? $data['external_reference'] ?? $data['subscription_id'] ?? null,
            'discount' => $data['discount'] ?? null,
        ];

        // Add credit card if provided
        if (isset($data['creditCard'])) {
            $payload['creditCard'] = $data['creditCard'];
            $payload['creditCardHolderInfo'] = $data['creditCardHolderInfo'];
        }

        // Split payment
        if (!empty($data['split'])) {
            $payload['split'] = $data['split'];
        }

        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        $endpoint = "/subscriptions/{$subscriptionId}";
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Get payment by ID
     */
    public function getPayment($paymentId)
    {
        $endpoint = "/payments/{$paymentId}";
        return $this->request('GET', $endpoint);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        $payment = $this->getPayment($paymentId);
        return $payment['status'] ?? null;
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhook($payload, $receivedToken)
    {
        $webhookToken = getenv('ASAAS_WEBHOOK_TOKEN');

        if (empty($webhookToken)) {
            if (function_exists('log_message')) {
                log_message('warning', 'Asaas webhook token not configured - accepting all webhooks');
            }
            return true;
        }

        if (empty($receivedToken)) {
            if (function_exists('log_message')) {
                log_message('error', 'No token received in webhook request');
            }
            return false;
        }

        return hash_equals($webhookToken, $receivedToken);
    }

    /**
     * Make HTTP request to Asaas API
     */
    private function request($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
            'User-Agent: DoarFazBem/1.0 (https://doarfazbem.ai)',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log request for debugging
        if (function_exists('log_message')) {
            log_message('info', "Asaas API Request: {$method} {$endpoint}");
            log_message('info', "Asaas API Response: HTTP {$httpCode} - {$response}");
        }

        if ($curlError) {
            if (function_exists('log_message')) {
                log_message('error', "Asaas API cURL Error: {$curlError}");
            }
            throw new \Exception("Erro ao conectar com Asaas: {$curlError}");
        }

        $responseData = json_decode($response, true);

        // Handle errors
        if ($httpCode >= 400) {
            $errorMessage = $responseData['errors'][0]['description'] ?? 'Erro desconhecido';
            if (function_exists('log_message')) {
                log_message('error', "Asaas API Error: {$errorMessage}");
            }
            throw new \Exception("Erro Asaas: {$errorMessage}");
        }

        return $responseData;
    }

    /**
     * Test connection with Asaas API
     */
    public function testConnection()
    {
        try {
            $endpoint = '/customers?limit=1';
            $response = $this->request('GET', $endpoint);
            return [
                'success' => true,
                'message' => 'Conexão com Asaas estabelecida com sucesso!',
                'environment' => $this->environment,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'environment' => $this->environment
            ];
        }
    }
}

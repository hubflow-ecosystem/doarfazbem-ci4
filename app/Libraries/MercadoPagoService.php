<?php

namespace App\Libraries;

use Config\MercadoPago;

/**
 * Servico de integracao com Mercado Pago
 * Foco em pagamentos PIX para o sistema de rifas
 */
class MercadoPagoService
{
    protected MercadoPago $config;
    protected string $accessToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->config = config('MercadoPago');
        $this->accessToken = $this->config->getAccessToken();
        $this->apiUrl = $this->config->apiUrl;
    }

    /**
     * Cria pagamento PIX
     */
    public function createPixPayment(array $data): array
    {
        // Validar configuracao
        if (!$this->config->isConfigured()) {
            log_message('warning', 'MercadoPago: Credenciais nao configuradas, usando modo desenvolvimento');
            return $this->createDevelopmentPayment($data);
        }

        $payload = [
            'transaction_amount' => (float) $data['amount'],
            'description' => $data['description'] ?? 'Numeros da Sorte - DoarFazBem',
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $data['email'],
                'first_name' => $this->getFirstName($data['name']),
                'last_name' => $this->getLastName($data['name']),
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/[^0-9]/', '', $data['cpf']),
                ],
            ],
            'date_of_expiration' => date('c', strtotime('+' . $this->config->pixExpirationMinutes . ' minutes')),
            'notification_url' => base_url('webhook/mercadopago/rifas'),
            'external_reference' => $data['external_reference'] ?? null,
        ];

        $response = $this->request('POST', '/v1/payments', $payload);

        if (isset($response['error'])) {
            log_message('error', 'MercadoPago PIX Error: ' . json_encode($response));
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Erro ao criar pagamento PIX',
            ];
        }

        return [
            'success' => true,
            'payment_id' => (string) $response['id'],
            'status' => $response['status'],
            'pix_code' => $response['point_of_interaction']['transaction_data']['qr_code'] ?? '',
            'pix_qrcode_base64' => $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? '',
            'expiration_date' => $response['date_of_expiration'] ?? null,
        ];
    }

    /**
     * Consulta status de um pagamento
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->config->isConfigured()) {
            return ['status' => 'pending', 'development_mode' => true];
        }

        $response = $this->request('GET', "/v1/payments/{$paymentId}");

        if (isset($response['error'])) {
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Erro ao consultar pagamento',
            ];
        }

        return [
            'success' => true,
            'status' => $response['status'],
            'status_detail' => $response['status_detail'] ?? null,
            'date_approved' => $response['date_approved'] ?? null,
            'transaction_amount' => $response['transaction_amount'] ?? 0,
            'fee_details' => $response['fee_details'] ?? [],
        ];
    }

    /**
     * Processa notificacao de webhook
     */
    public function processWebhook(array $data): array
    {
        $type = $data['type'] ?? $data['action'] ?? null;
        $paymentId = $data['data']['id'] ?? null;

        if (!$paymentId) {
            return ['success' => false, 'error' => 'Payment ID not found'];
        }

        // Consultar pagamento para obter status atual
        $payment = $this->getPaymentStatus($paymentId);

        if (!$payment['success']) {
            return $payment;
        }

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'status' => $payment['status'],
            'is_approved' => $payment['status'] === 'approved',
        ];
    }

    /**
     * Faz requisicao para API do Mercado Pago
     */
    protected function request(string $method, string $endpoint, ?array $data = null): array
    {
        $url = $this->apiUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid('mp_', true),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "MercadoPago cURL Error: {$error}");
            return ['error' => true, 'message' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            log_message('error', "MercadoPago API Error [{$httpCode}]: {$response}");
            return [
                'error' => true,
                'message' => $result['message'] ?? 'Erro na API do Mercado Pago',
                'cause' => $result['cause'] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Cria pagamento de desenvolvimento (quando nao ha credenciais)
     */
    protected function createDevelopmentPayment(array $data): array
    {
        $paymentId = 'DEV_' . time() . '_' . rand(1000, 9999);

        // Gerar codigo PIX fake
        $pixCode = '00020126580014br.gov.bcb.pix0136' . bin2hex(random_bytes(16));
        $pixCode .= '52040000530398654' . str_pad(number_format($data['amount'], 2, '', ''), 10, '0', STR_PAD_LEFT);
        $pixCode .= '5802BR5925DOARFAZBEM6009SAO PAULO62070503***6304';

        // QR Code SVG
        $qrcodeSvg = $this->generateDevQRCode();

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'status' => 'pending',
            'pix_code' => $pixCode,
            'pix_qrcode_base64' => base64_encode($qrcodeSvg),
            'development_mode' => true,
        ];
    }

    /**
     * Gera QR Code SVG para desenvolvimento
     */
    protected function generateDevQRCode(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
            <rect width="200" height="200" fill="#fff"/>
            <rect x="20" y="20" width="60" height="60" fill="#000"/>
            <rect x="30" y="30" width="40" height="40" fill="#fff"/>
            <rect x="40" y="40" width="20" height="20" fill="#000"/>
            <rect x="120" y="20" width="60" height="60" fill="#000"/>
            <rect x="130" y="30" width="40" height="40" fill="#fff"/>
            <rect x="140" y="40" width="20" height="20" fill="#000"/>
            <rect x="20" y="120" width="60" height="60" fill="#000"/>
            <rect x="30" y="130" width="40" height="40" fill="#fff"/>
            <rect x="40" y="140" width="20" height="20" fill="#000"/>
            <rect x="90" y="90" width="20" height="20" fill="#000"/>
            <rect x="120" y="120" width="20" height="20" fill="#000"/>
            <rect x="150" y="150" width="20" height="20" fill="#000"/>
            <text x="100" y="105" font-family="Arial" font-size="8" text-anchor="middle" fill="#666">MODO DEV</text>
        </svg>';
    }

    /**
     * Extrai primeiro nome
     */
    protected function getFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? 'Usuario';
    }

    /**
     * Extrai sobrenome
     */
    protected function getLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        array_shift($parts);
        return implode(' ', $parts) ?: 'DoarFazBem';
    }
}

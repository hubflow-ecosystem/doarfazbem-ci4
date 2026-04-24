<?php

namespace App\Libraries;

use Config\MercadoPago;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;

/**
 * Servico de integracao com Mercado Pago
 * Usa SDK oficial mercadopago/dx-php v3
 * Foco em pagamentos PIX para o sistema de rifas
 */
class MercadoPagoService
{
    protected MercadoPago $config;
    protected string $accessToken;

    public function __construct()
    {
        $this->config = config('MercadoPago');
        $this->accessToken = $this->config->getAccessToken();

        // Configurar SDK oficial
        MercadoPagoConfig::setAccessToken($this->accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    /**
     * Cria pagamento PIX usando SDK oficial
     */
    public function createPixPayment(array $data): array
    {
        // Validar configuracao
        if (!$this->config->isConfigured()) {
            log_message('warning', 'MercadoPago: Credenciais nao configuradas, usando modo desenvolvimento');
            return $this->createDevelopmentPayment($data);
        }

        $firstName = $this->getFirstName($data['name']);
        $lastName = $this->getLastName($data['name']);
        $cpfClean = preg_replace('/[^0-9]/', '', $data['cpf']);

        $request = [
            'transaction_amount' => (float) $data['amount'],
            'description' => $data['description'] ?? 'Numeros da Sorte - DoarFazBem',
            'payment_method_id' => 'pix',
            'statement_descriptor' => 'DOARFAZBEM',
            'external_reference' => $data['external_reference'] ?? null,
            'notification_url' => base_url('webhook/mercadopago/rifas'),
            'date_of_expiration' => (new \DateTime('+' . $this->config->pixExpirationMinutes . ' minutes', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d\TH:i:s.vP'),
            'payer' => [
                'email' => $data['email'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $cpfClean,
                ],
            ],
            'metadata' => [
                'platform' => 'doarfazbem',
                'purchase_id' => $data['purchase_id'] ?? null,
                'raffle_id' => $data['raffle_id'] ?? null,
            ],
            'additional_info' => [
                'items' => [
                    [
                        'id' => $data['external_reference'] ?? 'raffle_item',
                        'title' => $data['description'] ?? 'Numero da Sorte',
                        'description' => 'Numero da Sorte - Rifa Solidaria DoarFazBem',
                        'category_id' => 'donations',
                        'quantity' => (int) ($data['quantity'] ?? 1),
                        'unit_price' => (float) $data['amount'],
                    ],
                ],
                'payer' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ],
            ],
        ];

        try {
            $client = new PaymentClient();
            $requestOptions = new RequestOptions($this->accessToken);

            // Headers customizados para certificacao de qualidade
            $customHeaders = [
                'X-Idempotency-Key: ' . uniqid('mp_', true),
            ];

            // Device Session ID do MercadoPago.JS V2 (certificacao: Identificador do dispositivo)
            $deviceSessionId = $data['device_session_id'] ?? '';
            if (!empty($deviceSessionId)) {
                $customHeaders[] = 'X-meli-session-id: ' . $deviceSessionId;
            }

            $requestOptions->setCustomHeaders($customHeaders);

            $payment = $client->create($request, $requestOptions);

            return [
                'success' => true,
                'payment_id' => (string) $payment->id,
                'status' => $payment->status,
                'pix_code' => $payment->point_of_interaction->transaction_data->qr_code ?? '',
                'pix_qrcode_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? '',
                'expiration_date' => $payment->date_of_expiration ?? null,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $rawContent = $apiResponse ? $apiResponse->getContent() : 'Erro desconhecido';
            $content = is_array($rawContent) ? json_encode($rawContent) : $rawContent;
            log_message('error', 'MercadoPago SDK PIX Error: ' . $content);

            return [
                'success' => false,
                'error' => 'Erro ao criar pagamento PIX: ' . $content,
            ];
        } catch (\Exception $e) {
            log_message('error', 'MercadoPago SDK Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erro ao criar pagamento PIX',
            ];
        }
    }

    /**
     * Consulta status de um pagamento usando SDK oficial
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->config->isConfigured()) {
            return ['status' => 'pending', 'development_mode' => true];
        }

        try {
            $client = new PaymentClient();
            $payment = $client->get((int) $paymentId);

            return [
                'success' => true,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail ?? null,
                'date_approved' => $payment->date_approved ?? null,
                'transaction_amount' => $payment->transaction_amount ?? 0,
                'fee_details' => $payment->fee_details ?? [],
            ];
        } catch (MPApiException $e) {
            $content = $e->getApiResponse() ? $e->getApiResponse()->getContent() : $e->getMessage();
            log_message('error', "MercadoPago SDK Get Error: {$content}");

            return [
                'success' => false,
                'error' => 'Erro ao consultar pagamento',
            ];
        } catch (\Exception $e) {
            log_message('error', 'MercadoPago SDK Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erro ao consultar pagamento',
            ];
        }
    }

    /**
     * Processa notificacao de webhook
     */
    public function processWebhook(array $data): array
    {
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

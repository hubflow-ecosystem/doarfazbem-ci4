<?php

namespace App\Libraries;

/**
 * AsaasAPI
 *
 * Biblioteca para integração com API do Asaas (Gateway de Pagamento)
 * Documentação: https://docs.asaas.com
 */
class AsaasAPI
{
    private $apiKey;
    private $baseUrl;
    private $walletId;

    public function __construct()
    {
        $this->apiKey = getenv('ASAAS_API_KEY');
        $this->walletId = getenv('ASAAS_WALLET_ID');

        // Ambiente: sandbox ou production
        $environment = getenv('ASAAS_ENVIRONMENT') ?: 'sandbox';

        if ($environment === 'sandbox') {
            $this->baseUrl = 'https://sandbox.asaas.com/api/v3';
        } else {
            $this->baseUrl = 'https://www.asaas.com/api/v3';
        }
    }

    /**
     * Faz requisição HTTP para API do Asaas
     */
    private function request($method, $endpoint, $data = [])
    {
        $curl = curl_init();

        $url = $this->baseUrl . $endpoint;

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $this->apiKey
            ],
        ]);

        if ($method !== 'GET' && !empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            log_message('error', 'Asaas API Error: ' . $error);
            return ['error' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            log_message('error', 'Asaas API HTTP ' . $httpCode . ': ' . $response);
        }

        return $result;
    }

    /**
     * Cria um cliente no Asaas
     *
     * @param array $data Dados do cliente
     * @return array Resposta da API
     */
    public function createCustomer($data)
    {
        return $this->request('POST', '/customers', $data);
    }

    /**
     * Cria uma conta/wallet para recebimento (subconta)
     *
     * @param array $data Dados da conta
     * @return array Resposta da API
     */
    public function createWallet($data)
    {
        return $this->request('POST', '/accounts', $data);
    }

    /**
     * Cria uma cobrança/pagamento
     *
     * @param array $data Dados do pagamento
     * @return array Resposta da API
     */
    public function createPayment($data)
    {
        return $this->request('POST', '/payments', $data);
    }

    /**
     * Busca informações de um pagamento
     *
     * @param string $paymentId ID do pagamento
     * @return array Dados do pagamento
     */
    public function getPayment($paymentId)
    {
        return $this->request('GET', '/payments/' . $paymentId);
    }

    /**
     * Cria pagamento com PIX
     *
     * @param string $customerId ID do cliente Asaas
     * @param float $amount Valor
     * @param string $description Descrição
     * @return array Contém QR Code e chave Pix Copia e Cola
     */
    public function createPixPayment($customerId, $amount, $description)
    {
        $data = [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => $amount,
            'dueDate' => date('Y-m-d'),
            'description' => $description,
        ];

        return $this->createPayment($data);
    }

    /**
     * Busca QR Code do PIX
     *
     * @param string $paymentId ID do pagamento
     * @return array QR Code e Pix Copia e Cola
     */
    public function getPixQrCode($paymentId)
    {
        return $this->request('GET', '/payments/' . $paymentId . '/pixQrCode');
    }

    /**
     * Cria pagamento com Cartão de Crédito
     *
     * @param array $paymentData Dados do pagamento e cartão
     * @return array Resposta da API
     */
    public function createCreditCardPayment($paymentData)
    {
        return $this->createPayment($paymentData);
    }

    /**
     * Cria pagamento com Boleto
     *
     * @param string $customerId ID do cliente
     * @param float $amount Valor
     * @param string $dueDate Data de vencimento (Y-m-d)
     * @param string $description Descrição
     * @return array Resposta com URL do boleto
     */
    public function createBoletoPayment($customerId, $amount, $dueDate, $description)
    {
        $data = [
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'value' => $amount,
            'dueDate' => $dueDate,
            'description' => $description,
        ];

        return $this->createPayment($data);
    }

    /**
     * Cria split de pagamento (divisão entre plataforma e beneficiário)
     *
     * @param string $paymentId ID do pagamento
     * @param array $splits Array com divisões
     * @return array Resposta da API
     */
    public function createSplit($paymentId, $splits)
    {
        $data = [
            'payment' => $paymentId,
            'splits' => $splits
        ];

        return $this->request('POST', '/payments/' . $paymentId . '/split', $data);
    }

    /**
     * Prepara split padrão da plataforma
     *
     * @param string $beneficiaryWalletId Wallet do criador da campanha
     * @param float $totalAmount Valor total
     * @param string $category Categoria da campanha
     * @return array Splits configurados
     */
    public function prepareSplit($beneficiaryWalletId, $totalAmount, $category)
    {
        // Taxas por categoria
        $platformFees = [
            'medica' => 0.00,  // 0% para campanhas médicas
            'social' => 0.02,  // 2% para campanhas sociais
            'criativa' => 0.02, // 2% para projetos criativos
            'negocio' => 0.02,  // 2% para negócios
            'educacao' => 0.02, // 2% para educação
        ];

        $feePercentage = $platformFees[$category] ?? 0.02;
        $platformAmount = $totalAmount * $feePercentage;
        $beneficiaryAmount = $totalAmount - $platformAmount;

        $splits = [];

        // Split para o beneficiário (criador da campanha)
        if ($beneficiaryAmount > 0) {
            $splits[] = [
                'walletId' => $beneficiaryWalletId,
                'fixedValue' => number_format($beneficiaryAmount, 2, '.', '')
            ];
        }

        // Split para a plataforma (se houver taxa)
        if ($platformAmount > 0) {
            $splits[] = [
                'walletId' => $this->walletId,
                'fixedValue' => number_format($platformAmount, 2, '.', '')
            ];
        }

        return $splits;
    }

    /**
     * Cria transferência (saque)
     *
     * @param array $data Dados da transferência
     * @return array Resposta da API
     */
    public function createTransfer($data)
    {
        return $this->request('POST', '/transfers', $data);
    }

    /**
     * Estorna um pagamento (reembolso)
     *
     * @param string $paymentId ID do pagamento
     * @return array Resposta da API
     */
    public function refundPayment($paymentId)
    {
        return $this->request('POST', '/payments/' . $paymentId . '/refund');
    }

    /**
     * Busca saldo da conta
     *
     * @return array Saldo disponível
     */
    public function getBalance()
    {
        return $this->request('GET', '/finance/balance');
    }

    /**
     * Webhook - Valida assinatura do webhook
     *
     * @param string $payload Payload JSON recebido
     * @param string $signature Assinatura recebida no header
     * @return bool Válido ou não
     */
    public function validateWebhookSignature($payload, $signature)
    {
        // Implementar validação conforme documentação Asaas
        // Por enquanto retorna true para desenvolvimento
        return true;
    }
}

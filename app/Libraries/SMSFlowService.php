<?php

namespace App\Libraries;

/**
 * SMSFlowService - Integração com SMSFlow do Ecossistema HubFlow
 *
 * Serviço para envio de SMS via plataforma centralizada SMSFlow.
 * Usado para confirmações de doação, alertas ao criador de campanha, OTP e boas-vindas.
 *
 * Padrão idêntico ao MediLife — mesma API, mesmo mecanismo de autenticação.
 *
 * @package App\Libraries
 * @author  HubFlow AI
 * @version 1.0.0
 */
class SMSFlowService
{
    /**
     * URL base da API SMSFlow
     */
    protected string $baseUrl;

    /**
     * API Key para autenticação via Bearer Token (fallback)
     */
    protected string $apiKey;

    /**
     * Token de serviço M2M (preferencial sobre apiKey)
     */
    protected string $serviceToken;

    /**
     * Secret para validação de webhooks recebidos do SMSFlow
     */
    protected string $webhookSecret;

    /**
     * Nome do serviço no ecossistema (enviado no header X-Service-Name)
     */
    protected string $serviceName = 'doarfazbem';

    /**
     * Timeout das requisições em segundos
     */
    protected int $timeout = 30;

    public function __construct()
    {
        $this->baseUrl       = rtrim(env('SMSFLOW_API_URL', 'https://sms.hubflowai.com/api/v1'), '/');
        $this->apiKey        = env('SMSFLOW_API_KEY', '');
        $this->serviceToken  = env('SMSFLOW_SERVICE_TOKEN', '');
        $this->webhookSecret = env('SMSFLOW_WEBHOOK_SECRET', '');
    }

    // ========================================
    // VERIFICAÇÕES
    // ========================================

    /**
     * Verifica se o serviço está configurado
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    // ========================================
    // MÉTODOS GENÉRICOS DE ENVIO
    // ========================================

    /**
     * Envia SMS simples
     *
     * @param string $to       Número com DDD (ex: 47991234567 ou +5547991234567)
     * @param string $message  Texto da mensagem
     * @param int    $priority 1=alta, 2=normal, 3=baixa
     * @param array  $metadata Dados extras para rastreamento
     */
    public function sendSms(string $to, string $message, int $priority = 2, array $metadata = []): array
    {
        return $this->request('POST', '/ecosystem/sms/send', [
            'to'       => $this->formatPhone($to),
            'message'  => $message,
            'priority' => $priority,
            'metadata' => array_merge($metadata, ['source' => $this->serviceName]),
        ]);
    }

    /**
     * Envia SMS em massa (individualmente — SMSFlow não tem endpoint bulk)
     *
     * @param array $recipients [{to, message, metadata?, priority?}]
     */
    public function sendSmsBulk(array $recipients, int $priority = 2): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[] = $this->request('POST', '/ecosystem/sms/send', [
                'to'       => $this->formatPhone($recipient['to']),
                'message'  => $recipient['message'],
                'priority' => $recipient['priority'] ?? $priority,
                'metadata' => array_merge($recipient['metadata'] ?? [], ['source' => $this->serviceName]),
            ]);
        }

        $allOk = array_reduce($results, fn($carry, $r) => $carry && ($r['success'] ?? false), true);

        return [
            'success' => $allOk,
            'total'   => count($results),
            'results' => $results,
        ];
    }

    // ========================================
    // OTP — VERIFICAÇÃO DE TELEFONE
    // ========================================

    /**
     * Envia código OTP para verificação de telefone
     *
     * @param string $to        Número do usuário
     * @param int    $length    Tamanho do código (4-8 dígitos)
     * @param int    $expiresIn Validade em segundos (padrão: 300 = 5 min)
     * @return array ['success', 'otp_id', 'expires_at']
     */
    public function sendOtp(string $to, int $length = 6, int $expiresIn = 300): array
    {
        return $this->request('POST', '/ecosystem/otp/send', [
            'to'         => $this->formatPhone($to),
            'channel'    => 'sms',
            'length'     => $length,
            'expires_in' => $expiresIn,
        ]);
    }

    /**
     * Verifica código OTP digitado pelo usuário
     *
     * @param string $otpId ID retornado pelo sendOtp()
     * @param string $code  Código digitado
     * @return array ['valid' => true/false, 'attempts_remaining']
     */
    public function verifyOtp(string $otpId, string $code): array
    {
        return $this->request('POST', '/ecosystem/otp/verify', [
            'otp_id' => $otpId,
            'code'   => $code,
        ]);
    }

    /**
     * Reenvia código OTP já criado
     *
     * @param string $otpId ID do OTP original
     */
    public function resendOtp(string $otpId): array
    {
        return $this->request('POST', '/ecosystem/otp/resend', [
            'otp_id' => $otpId,
        ]);
    }

    // ========================================
    // TEMPLATES ESPECÍFICOS DO DOARFAZBEM
    // ========================================

    /**
     * SMS ao doador confirmando que a doação foi recebida
     *
     * @param string $phone         Telefone do doador
     * @param string $donorName     Nome do doador
     * @param string $campaignTitle Título da campanha
     * @param float  $amount        Valor doado
     * @param string $campaignSlug  Slug da campanha (para link)
     * @param int    $donationId    ID da doação
     */
    public function sendDonationConfirmed(
        string $phone,
        string $donorName,
        string $campaignTitle,
        float  $amount,
        string $campaignSlug,
        int    $donationId
    ): array {
        $amountFormatted = 'R$ ' . number_format($amount, 2, ',', '.');
        $link            = base_url("c/{$campaignSlug}");

        $message = "✅ {$donorName}, sua doação de {$amountFormatted} para \"{$campaignTitle}\" foi confirmada! Obrigado por fazer o bem 💚 Ver campanha: {$link} - DoarFazBem";

        return $this->sendSms($phone, $message, 1, [
            'type'        => 'donation_confirmed',
            'donation_id' => $donationId,
            'campaign'    => $campaignTitle,
        ]);
    }

    /**
     * SMS ao criador da campanha avisando de nova doação
     *
     * @param string $phone          Telefone do criador
     * @param string $creatorName    Nome do criador
     * @param string $campaignTitle  Título da campanha
     * @param float  $donationAmount Valor da nova doação
     * @param float  $totalRaised    Total arrecadado até agora
     * @param float  $goalAmount     Meta da campanha
     * @param int    $campaignId     ID da campanha
     */
    public function sendCreatorNewDonation(
        string $phone,
        string $creatorName,
        string $campaignTitle,
        float  $donationAmount,
        float  $totalRaised,
        float  $goalAmount,
        int    $campaignId
    ): array {
        $donationFormatted = 'R$ ' . number_format($donationAmount, 2, ',', '.');
        $totalFormatted    = 'R$ ' . number_format($totalRaised, 2, ',', '.');
        $progress          = $goalAmount > 0 ? round(($totalRaised / $goalAmount) * 100) : 0;

        $message = "💰 {$creatorName}, nova doação de {$donationFormatted} em \"{$campaignTitle}\"! Total: {$totalFormatted} ({$progress}% da meta). - DoarFazBem";

        return $this->sendSms($phone, $message, 1, [
            'type'        => 'creator_new_donation',
            'campaign_id' => $campaignId,
        ]);
    }

    /**
     * SMS ao criador quando a campanha atinge a meta (100%)
     *
     * @param string $phone         Telefone do criador
     * @param string $creatorName   Nome do criador
     * @param string $campaignTitle Título da campanha
     * @param float  $totalRaised   Total arrecadado
     * @param int    $donorsCount   Número de doadores
     * @param int    $campaignId    ID da campanha
     */
    public function sendCampaignGoalReached(
        string $phone,
        string $creatorName,
        string $campaignTitle,
        float  $totalRaised,
        int    $donorsCount,
        int    $campaignId
    ): array {
        $totalFormatted = 'R$ ' . number_format($totalRaised, 2, ',', '.');

        $message = "🎉🎉 PARABÉNS {$creatorName}! Sua campanha \"{$campaignTitle}\" atingiu a META! Total: {$totalFormatted} com {$donorsCount} doadores. Acesse o painel para retirar os fundos. - DoarFazBem";

        return $this->sendSms($phone, $message, 1, [
            'type'        => 'goal_reached',
            'campaign_id' => $campaignId,
        ]);
    }

    /**
     * SMS ao criador quando a campanha atinge um marco (25%, 50%, 75%)
     *
     * @param string $phone         Telefone do criador
     * @param string $creatorName   Nome do criador
     * @param string $campaignTitle Título da campanha
     * @param int    $percentage    25, 50 ou 75
     * @param float  $totalRaised   Total arrecadado
     * @param int    $campaignId    ID da campanha
     */
    public function sendCampaignMilestone(
        string $phone,
        string $creatorName,
        string $campaignTitle,
        int    $percentage,
        float  $totalRaised,
        int    $campaignId
    ): array {
        $totalFormatted = 'R$ ' . number_format($totalRaised, 2, ',', '.');

        $emojis = [25 => '🌱', 50 => '🚀', 75 => '🔥'];
        $emoji  = $emojis[$percentage] ?? '📊';

        $message = "{$emoji} {$creatorName}, sua campanha \"{$campaignTitle}\" atingiu {$percentage}% da meta! Já arrecadou {$totalFormatted}. Continue divulgando! - DoarFazBem";

        return $this->sendSms($phone, $message, 2, [
            'type'        => 'campaign_milestone',
            'percentage'  => $percentage,
            'campaign_id' => $campaignId,
        ]);
    }

    /**
     * SMS ao criador quando a campanha é aprovada pelo admin
     *
     * @param string $phone         Telefone do criador
     * @param string $creatorName   Nome do criador
     * @param string $campaignTitle Título da campanha
     * @param string $campaignSlug  Slug para o link público
     */
    public function sendCampaignApproved(
        string $phone,
        string $creatorName,
        string $campaignTitle,
        string $campaignSlug
    ): array {
        $link    = base_url("c/{$campaignSlug}");
        $message = "✅ {$creatorName}, sua campanha \"{$campaignTitle}\" foi APROVADA e já está no ar! Compartilhe o link: {$link} - DoarFazBem";

        return $this->sendSms($phone, $message, 1, [
            'type'     => 'campaign_approved',
            'campaign' => $campaignTitle,
        ]);
    }

    /**
     * SMS ao criador quando a campanha é rejeitada pelo admin
     *
     * @param string      $phone         Telefone do criador
     * @param string      $creatorName   Nome do criador
     * @param string      $campaignTitle Título da campanha
     * @param string|null $reason        Motivo da rejeição
     */
    public function sendCampaignRejected(
        string  $phone,
        string  $creatorName,
        string  $campaignTitle,
        ?string $reason = null
    ): array {
        $reasonText = $reason ? " Motivo: {$reason}." : '';
        $message    = "❌ {$creatorName}, sua campanha \"{$campaignTitle}\" foi reprovada.{$reasonText} Acesse o painel para mais detalhes e edite sua campanha. - DoarFazBem";

        return $this->sendSms($phone, $message, 1, [
            'type'     => 'campaign_rejected',
            'campaign' => $campaignTitle,
        ]);
    }

    /**
     * SMS de boas-vindas para novo usuário registrado
     *
     * @param string $phone    Telefone do usuário
     * @param string $userName Nome do usuário
     */
    public function sendWelcome(string $phone, string $userName): array
    {
        $message = "👋 Olá {$userName}, bem-vindo(a) ao DoarFazBem! Aqui você pode criar campanhas e receber doações 100% grátis. Comece agora: " . base_url('dashboard') . " - DoarFazBem";

        return $this->sendSms($phone, $message, 2, [
            'type' => 'welcome',
        ]);
    }

    /**
     * SMS de estorno de doação ao doador
     *
     * @param string $phone         Telefone do doador
     * @param string $donorName     Nome do doador
     * @param float  $amount        Valor estornado
     * @param string $campaignTitle Título da campanha
     */
    public function sendDonationRefunded(
        string $phone,
        string $donorName,
        float  $amount,
        string $campaignTitle
    ): array {
        $amountFormatted = 'R$ ' . number_format($amount, 2, ',', '.');
        $message         = "↩️ {$donorName}, sua doação de {$amountFormatted} para \"{$campaignTitle}\" foi estornada. Em caso de dúvidas, acesse nosso suporte. - DoarFazBem";

        return $this->sendSms($phone, $message, 2, [
            'type'   => 'donation_refunded',
            'amount' => $amount,
        ]);
    }

    // ========================================
    // CRÉDITOS E WEBHOOKS
    // ========================================

    /**
     * Consulta créditos disponíveis na conta SMSFlow
     */
    public function getCredits(): array
    {
        return $this->request('GET', '/ecosystem/credits');
    }

    /**
     * Registra webhook para receber eventos do SMSFlow
     *
     * @param string $url    URL do endpoint no DoarFazBem
     * @param array  $events Eventos para assinar
     */
    public function registerWebhook(string $url, array $events): array
    {
        return $this->request('POST', '/ecosystem/webhooks', [
            'url'    => $url,
            'events' => $events,
        ]);
    }

    /**
     * Valida assinatura HMAC do webhook recebido do SMSFlow
     *
     * @param string $payload   Corpo bruto da requisição
     * @param string $signature Header X-Webhook-Signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            log_message('warning', '[SMSFlow] Webhook secret não configurado');
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    // ========================================
    // AUXILIARES
    // ========================================

    /**
     * Formata número para padrão E.164 (+55DDDNUMERO)
     *
     * Aceita:
     *   47991234567     → +5547991234567
     *   (47) 9 9123-4567 → +5547991234567
     *   +5547991234567  → +5547991234567
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 10 ou 11 dígitos: adiciona código do Brasil
        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Executa requisição HTTP para a API SMSFlow
     *
     * @param string $method   GET, POST, PUT, DELETE
     * @param string $endpoint Ex: /ecosystem/sms/send
     * @param array  $data     Payload
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        if (!$this->isConfigured()) {
            log_message('error', '[SMSFlow] Serviço não configurado — SMSFLOW_API_KEY ausente no .env');
            return [
                'success' => false,
                'error'   => 'SMSFlow não configurado. Adicione SMSFLOW_API_KEY no .env',
            ];
        }

        $url = $this->baseUrl . $endpoint;

        // Detecta domínio de origem para autenticação no SMSFlow
        $origin = env('SMSFLOW_ORIGIN', '');
        if (empty($origin)) {
            $baseUrl = env('app.baseURL', '');
            $origin  = $baseUrl ? (parse_url($baseUrl, PHP_URL_HOST) ?: 'doarfazbem.com.br') : 'doarfazbem.com.br';
        }

        // Usa Service Token M2M se disponível, senão usa API Key
        $token = !empty($this->serviceToken) ? $this->serviceToken : $this->apiKey;

        $headers = [
            'Authorization: Bearer ' . $token,
            'X-Service-Name: ' . $this->serviceName,
            'X-Origin: ' . $origin,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $curl    = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        switch (strtoupper($method)) {
            case 'POST':
                $options[CURLOPT_POST]       = true;
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $options[CURLOPT_POSTFIELDS]    = json_encode($data);
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'GET':
            default:
                if (!empty($data)) {
                    $options[CURLOPT_URL] = $url . '?' . http_build_query($data);
                }
                break;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);

        curl_close($curl);

        if ($error) {
            log_message('error', "[SMSFlow] Erro cURL: {$error}");
            return ['success' => false, 'error' => 'Erro de conexão: ' . $error];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            log_message('error', "[SMSFlow] Erro HTTP {$httpCode}: " . json_encode($decoded));
            return [
                'success'   => false,
                'error'     => $decoded['error'] ?? $decoded['message'] ?? 'Erro na requisição',
                'http_code' => $httpCode,
            ];
        }

        log_message('debug', "[SMSFlow] {$method} {$endpoint} — HTTP {$httpCode}");

        return $decoded ?? ['success' => true];
    }
}

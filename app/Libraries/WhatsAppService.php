<?php

namespace App\Libraries;

/**
 * Servico de integracao com WhatsApp Business API
 * Envia notificacoes via WhatsApp para usuarios e criadores de campanhas
 */
class WhatsAppService
{
    protected string $phoneId;
    protected string $accessToken;
    protected string $apiUrl = 'https://graph.facebook.com/v18.0';
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->phoneId = getenv('WHATSAPP_PHONE_ID') ?: '';
        $this->accessToken = getenv('WHATSAPP_ACCESS_TOKEN') ?: '';
        $this->isConfigured = !empty($this->phoneId) && !empty($this->accessToken);
    }

    /**
     * Verifica se o servico esta configurado
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Envia mensagem de texto simples
     */
    public function sendMessage(string $phone, string $message): array
    {
        if (!$this->isConfigured) {
            log_message('warning', 'WhatsApp: Servico nao configurado');
            return ['success' => false, 'error' => 'WhatsApp nao configurado'];
        }

        $phone = $this->formatPhone($phone);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message
            ]
        ];

        return $this->request('messages', $payload);
    }

    /**
     * Envia template de notificacao de nova doacao
     */
    public function sendDonationNotification(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Notificacao de doacao');
            return ['success' => true, 'simulated' => true];
        }

        $message = "🎉 *Nova Doação Recebida!*\n\n";
        $message .= "Sua campanha *{$data['campaign_title']}* recebeu uma doação!\n\n";
        $message .= "💰 *Valor:* R$ " . number_format($data['amount'], 2, ',', '.') . "\n";
        $message .= "👤 *Doador:* {$data['donor_name']}\n";

        if (!empty($data['message'])) {
            $message .= "💬 *Mensagem:* \"{$data['message']}\"\n";
        }

        $message .= "\n📊 *Progresso:* {$data['percentage']}%\n";
        $message .= "✅ Acesse sua campanha para mais detalhes!";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Envia notificacao de compra de rifa
     */
    public function sendRafflePurchaseNotification(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Notificacao de compra de rifa');
            return ['success' => true, 'simulated' => true];
        }

        $message = "🎟️ *Números da Sorte - Compra Confirmada!*\n\n";
        $message .= "Você adquiriu *{$data['quantity']} cotas*!\n\n";
        $message .= "💰 *Valor pago:* R$ " . number_format($data['total'], 2, ',', '.') . "\n";
        $message .= "🔢 *Seus números:*\n";

        // Mostrar até 10 números
        $numbers = array_slice($data['numbers'], 0, 10);
        foreach ($numbers as $num) {
            $message .= "  • {$num}\n";
        }

        if (count($data['numbers']) > 10) {
            $message .= "  ... e mais " . (count($data['numbers']) - 10) . " números\n";
        }

        $message .= "\n🍀 *Boa sorte!*\n";
        $message .= "📅 Sorteio: " . date('d/m/Y', strtotime($data['draw_date']));

        return $this->sendMessage($phone, $message);
    }

    /**
     * Envia notificacao de premio instantaneo
     */
    public function sendInstantPrizeNotification(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Notificacao de premio instantaneo');
            return ['success' => true, 'simulated' => true];
        }

        $message = "🎊 *PARABÉNS! VOCÊ GANHOU!* 🎊\n\n";
        $message .= "Seu número *{$data['number']}* é um número premiado!\n\n";
        $message .= "🏆 *Prêmio:* {$data['prize_name']}\n";
        $message .= "💰 *Valor:* R$ " . number_format($data['prize_amount'], 2, ',', '.') . "\n\n";
        $message .= "📞 Entraremos em contato para realizar o pagamento.\n";
        $message .= "✨ Continue participando e concorra a mais prêmios!";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Envia lembrete de pagamento pendente de rifa
     */
    public function sendRafflePaymentReminder(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Lembrete de pagamento');
            return ['success' => true, 'simulated' => true];
        }

        $message = "⏰ *Lembrete - Pagamento Pendente*\n\n";
        $message .= "Você tem uma compra de *{$data['quantity']} cotas* aguardando pagamento!\n\n";
        $message .= "💰 *Valor:* R$ " . number_format($data['total'], 2, ',', '.') . "\n";
        $message .= "⏳ *Expira em:* {$data['minutes_remaining']} minutos\n\n";
        $message .= "Pague via PIX para garantir seus números!\n";
        $message .= "🔗 {$data['payment_url']}";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Envia notificacao de meta atingida
     */
    public function sendGoalReachedNotification(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Meta atingida');
            return ['success' => true, 'simulated' => true];
        }

        $message = "🎉🎉🎉 *META ATINGIDA!* 🎉🎉🎉\n\n";
        $message .= "Sua campanha *{$data['campaign_title']}* alcançou 100% da meta!\n\n";
        $message .= "💰 *Total arrecadado:* R$ " . number_format($data['total_raised'], 2, ',', '.') . "\n";
        $message .= "👥 *Total de doadores:* {$data['donors_count']}\n\n";
        $message .= "Parabéns! Obrigado a todos que contribuíram! 💚";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Envia notificacao de marco atingido (25%, 50%, 75%)
     */
    public function sendMilestoneNotification(string $phone, array $data): array
    {
        if (!$this->isConfigured) {
            log_message('info', 'WhatsApp: Modo simulacao - Marco atingido');
            return ['success' => true, 'simulated' => true];
        }

        $emojis = [
            25 => '🌱',
            50 => '🚀',
            75 => '🔥',
            100 => '🎉'
        ];

        $emoji = $emojis[$data['milestone']] ?? '📈';

        $message = "{$emoji} *Marco Atingido: {$data['milestone']}%*\n\n";
        $message .= "Sua campanha *{$data['campaign_title']}* está progredindo!\n\n";
        $message .= "💰 *Arrecadado:* R$ " . number_format($data['total_raised'], 2, ',', '.') . "\n";
        $message .= "🎯 *Meta:* R$ " . number_format($data['goal'], 2, ',', '.') . "\n\n";
        $message .= "Continue divulgando para alcançar a meta! 💪";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Formata numero de telefone para padrao internacional
     */
    protected function formatPhone(string $phone): string
    {
        // Remover tudo que nao for numero
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Se comeca com 0, remover
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }

        // Se nao tem codigo do pais, adicionar 55 (Brasil)
        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    /**
     * Faz requisicao para API do WhatsApp
     */
    protected function request(string $endpoint, array $data): array
    {
        $url = "{$this->apiUrl}/{$this->phoneId}/{$endpoint}";

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "WhatsApp cURL Error: {$error}");
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            log_message('error', "WhatsApp API Error [{$httpCode}]: {$response}");
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Erro na API do WhatsApp',
                'details' => $result['error'] ?? null
            ];
        }

        return [
            'success' => true,
            'message_id' => $result['messages'][0]['id'] ?? null,
            'response' => $result
        ];
    }
}

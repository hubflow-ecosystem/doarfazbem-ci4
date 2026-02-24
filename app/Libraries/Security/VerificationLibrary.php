<?php
/**
 * HUBFLOW - Biblioteca de Verificação de Email e Telefone
 * =======================================================
 * Sistema de confirmação com opt por WhatsApp/SMS
 *
 * Funcionalidades:
 * - Verificação de email com código/link
 * - Verificação de telefone via SMS ou WhatsApp
 * - Tokens de verificação com expiração
 * - Rate limiting de envios
 */

namespace App\Libraries;

class VerificationLibrary
{
    protected $db;
    protected $session;

    // Configurações
    protected $codeLength = 6;
    protected $tokenExpiration = 3600; // 1 hora
    protected $maxAttemptsPerHour = 5;
    protected $cooldownSeconds = 60; // Tempo entre reenvios

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    /**
     * ==========================================================================
     * EMAIL VERIFICATION - Verificação de Email
     * ==========================================================================
     */

    /**
     * Criar token de verificação de email
     */
    public function createEmailVerification(int $userId, string $email): array
    {
        // Verificar rate limiting
        $canSend = $this->canSendVerification($userId, 'email');
        if (!$canSend['allowed']) {
            return [
                'success' => false,
                'message' => $canSend['message']
            ];
        }

        // Gerar código e token
        $code = $this->generateNumericCode();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenExpiration);

        // Invalidar tokens anteriores
        $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'email')
            ->where('verified_at IS NULL')
            ->update(['invalidated_at' => date('Y-m-d H:i:s')]);

        // Criar novo token
        $this->db->table('verification_tokens')->insert([
            'user_id' => $userId,
            'type' => 'email',
            'target' => $email,
            'code' => $code,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'code' => $code,
            'token' => $token,
            'expires_at' => $expiresAt,
            'verify_link' => base_url("verificar-email/{$token}")
        ];
    }

    /**
     * Verificar código de email
     */
    public function verifyEmailCode(int $userId, string $code): array
    {
        $verification = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'email')
            ->where('code', $code)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->where('verified_at IS NULL')
            ->where('invalidated_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$verification) {
            // Registrar tentativa falha
            $this->recordVerificationAttempt($userId, 'email', false);
            return [
                'success' => false,
                'message' => 'Código inválido ou expirado'
            ];
        }

        // Marcar como verificado
        $this->db->table('verification_tokens')
            ->where('id', $verification['id'])
            ->update(['verified_at' => date('Y-m-d H:i:s')]);

        // Registrar sucesso
        $this->recordVerificationAttempt($userId, 'email', true);

        return [
            'success' => true,
            'message' => 'Email verificado com sucesso!',
            'email' => $verification['target']
        ];
    }

    /**
     * Verificar token de email (link)
     */
    public function verifyEmailToken(string $token): array
    {
        $verification = $this->db->table('verification_tokens')
            ->where('token', $token)
            ->where('type', 'email')
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->where('verified_at IS NULL')
            ->where('invalidated_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'Link inválido ou expirado'
            ];
        }

        // Marcar como verificado
        $this->db->table('verification_tokens')
            ->where('id', $verification['id'])
            ->update(['verified_at' => date('Y-m-d H:i:s')]);

        return [
            'success' => true,
            'message' => 'Email verificado com sucesso!',
            'user_id' => $verification['user_id'],
            'email' => $verification['target']
        ];
    }

    /**
     * ==========================================================================
     * PHONE VERIFICATION - Verificação de Telefone
     * ==========================================================================
     */

    /**
     * Criar verificação de telefone
     * @param string $method 'sms' ou 'whatsapp'
     */
    public function createPhoneVerification(int $userId, string $phone, string $method = 'whatsapp'): array
    {
        // Normalizar telefone
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Verificar rate limiting
        $canSend = $this->canSendVerification($userId, 'phone');
        if (!$canSend['allowed']) {
            return [
                'success' => false,
                'message' => $canSend['message']
            ];
        }

        // Gerar código
        $code = $this->generateNumericCode();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenExpiration);

        // Invalidar códigos anteriores
        $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'phone')
            ->where('verified_at IS NULL')
            ->update(['invalidated_at' => date('Y-m-d H:i:s')]);

        // Criar novo token
        $this->db->table('verification_tokens')->insert([
            'user_id' => $userId,
            'type' => 'phone',
            'target' => $phone,
            'code' => $code,
            'method' => $method,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'code' => $code,
            'phone' => $phone,
            'method' => $method,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Verificar código de telefone
     */
    public function verifyPhoneCode(int $userId, string $code): array
    {
        $verification = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'phone')
            ->where('code', $code)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->where('verified_at IS NULL')
            ->where('invalidated_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$verification) {
            $this->recordVerificationAttempt($userId, 'phone', false);
            return [
                'success' => false,
                'message' => 'Código inválido ou expirado'
            ];
        }

        // Marcar como verificado
        $this->db->table('verification_tokens')
            ->where('id', $verification['id'])
            ->update(['verified_at' => date('Y-m-d H:i:s')]);

        $this->recordVerificationAttempt($userId, 'phone', true);

        return [
            'success' => true,
            'message' => 'Telefone verificado com sucesso!',
            'phone' => $verification['target']
        ];
    }

    /**
     * ==========================================================================
     * ENVIO DE VERIFICAÇÃO - Integração com SMS/WhatsApp
     * ==========================================================================
     */

    /**
     * Enviar código por WhatsApp (Evolution API)
     */
    public function sendWhatsAppCode(string $phone, string $code, string $appName = 'HubFlow'): array
    {
        $evolutionApiUrl = getenv('EVOLUTION_API_URL');
        $evolutionApiKey = getenv('EVOLUTION_API_KEY');
        $instanceName = getenv('EVOLUTION_INSTANCE_NAME');

        if (!$evolutionApiUrl || !$evolutionApiKey || !$instanceName) {
            log_message('error', 'Evolution API não configurada');
            return ['success' => false, 'message' => 'Serviço de WhatsApp não configurado'];
        }

        // Formatar número (Brasil)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 11) {
            $phone = '55' . $phone;
        } elseif (strlen($phone) == 10) {
            $phone = '55' . $phone;
        }

        $message = "🔐 *{$appName} - Código de Verificação*\n\n";
        $message .= "Seu código de verificação é: *{$code}*\n\n";
        $message .= "Este código expira em 1 hora.\n";
        $message .= "Se você não solicitou este código, ignore esta mensagem.";

        $payload = [
            'number' => $phone,
            'text' => $message
        ];

        $ch = curl_init("{$evolutionApiUrl}/message/sendText/{$instanceName}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $evolutionApiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            log_message('info', "Código de verificação enviado via WhatsApp para {$phone}");
            return ['success' => true, 'message' => 'Código enviado via WhatsApp'];
        }

        log_message('error', "Erro ao enviar WhatsApp para {$phone}: {$response}");
        return ['success' => false, 'message' => 'Erro ao enviar WhatsApp'];
    }

    /**
     * Enviar código por SMS (Twilio ou outro provider)
     */
    public function sendSMSCode(string $phone, string $code, string $appName = 'HubFlow'): array
    {
        $twilioSid = getenv('TWILIO_ACCOUNT_SID');
        $twilioToken = getenv('TWILIO_AUTH_TOKEN');
        $twilioPhone = getenv('TWILIO_PHONE_NUMBER');

        if (!$twilioSid || !$twilioToken || !$twilioPhone) {
            log_message('error', 'Twilio não configurado');
            return ['success' => false, 'message' => 'Serviço de SMS não configurado'];
        }

        // Formatar número (Brasil)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }
        $phone = '+' . $phone;

        $message = "{$appName}: Seu código de verificação é {$code}. Válido por 1 hora.";

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json";

        $data = [
            'From' => $twilioPhone,
            'To' => $phone,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$twilioSid}:{$twilioToken}");

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201) {
            log_message('info', "Código de verificação enviado via SMS para {$phone}");
            return ['success' => true, 'message' => 'Código enviado via SMS'];
        }

        log_message('error', "Erro ao enviar SMS para {$phone}: {$response}");
        return ['success' => false, 'message' => 'Erro ao enviar SMS'];
    }

    /**
     * Enviar código de verificação de email
     */
    public function sendEmailVerification(string $email, string $code, string $verifyLink, string $appName = 'HubFlow'): bool
    {
        $emailService = \Config\Services::email();

        $fromEmail = getenv('EMAIL_FROM') ?: 'noreply@hubflowai.com';
        $fromName = getenv('EMAIL_FROM_NAME') ?: $appName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($email);
        $emailService->setSubject("Código de Verificação - {$appName}");

        $message = $this->buildEmailVerificationTemplate($code, $verifyLink, $appName);
        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', "Email de verificação enviado para {$email}");
            return true;
        }

        log_message('error', "Erro ao enviar email de verificação para {$email}");
        return false;
    }

    /**
     * Template de email de verificação
     */
    protected function buildEmailVerificationTemplate(string $code, string $verifyLink, string $appName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🔐 Verificação de Email</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0;">{$appName}</p>
    </div>

    <div style="padding: 30px 20px;">
        <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #374151; margin-bottom: 20px;">
                Use o código abaixo para verificar seu email:
            </p>

            <div style="background: #f3f4f6; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                <span style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #1f2937;">{$code}</span>
            </div>

            <p style="font-size: 14px; color: #6b7280; text-align: center;">
                Ou clique no botão abaixo para verificar automaticamente:
            </p>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{$verifyLink}" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600;">
                    Verificar Email
                </a>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

            <p style="font-size: 12px; color: #9ca3af; text-align: center;">
                Este código expira em <strong>1 hora</strong>.<br>
                Se você não solicitou esta verificação, ignore este email.
            </p>

        </div>
    </div>

</body>
</html>
HTML;
    }

    /**
     * ==========================================================================
     * HELPERS - Funções auxiliares
     * ==========================================================================
     */

    /**
     * Verificar se pode enviar verificação (rate limiting)
     */
    protected function canSendVerification(int $userId, string $type): array
    {
        // Verificar último envio (cooldown)
        $lastSent = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', $type)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRowArray();

        if ($lastSent) {
            $timeSince = time() - strtotime($lastSent['created_at']);
            if ($timeSince < $this->cooldownSeconds) {
                $waitTime = $this->cooldownSeconds - $timeSince;
                return [
                    'allowed' => false,
                    'message' => "Aguarde {$waitTime} segundos antes de solicitar novo código"
                ];
            }
        }

        // Verificar máximo de tentativas por hora
        $attemptsLastHour = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('created_at >', date('Y-m-d H:i:s', time() - 3600))
            ->countAllResults();

        if ($attemptsLastHour >= $this->maxAttemptsPerHour) {
            return [
                'allowed' => false,
                'message' => 'Muitas tentativas. Tente novamente em 1 hora'
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Registrar tentativa de verificação
     */
    protected function recordVerificationAttempt(int $userId, string $type, bool $success): void
    {
        $this->db->table('verification_attempts')->insert([
            'user_id' => $userId,
            'type' => $type,
            'success' => $success ? 1 : 0,
            'ip_address' => service('request')->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Gerar código numérico
     */
    protected function generateNumericCode(): string
    {
        $code = '';
        for ($i = 0; $i < $this->codeLength; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    /**
     * Verificar se email está verificado
     */
    public function isEmailVerified(int $userId): bool
    {
        $verified = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'email')
            ->where('verified_at IS NOT NULL')
            ->countAllResults();

        return $verified > 0;
    }

    /**
     * Verificar se telefone está verificado
     */
    public function isPhoneVerified(int $userId): bool
    {
        $verified = $this->db->table('verification_tokens')
            ->where('user_id', $userId)
            ->where('type', 'phone')
            ->where('verified_at IS NOT NULL')
            ->countAllResults();

        return $verified > 0;
    }
}

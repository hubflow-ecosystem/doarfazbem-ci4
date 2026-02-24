<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Audit Log Model
 * Registra todas as ações críticas do sistema
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'extra_data',
        'created_at'
    ];
    protected $useTimestamps = false;

    /**
     * Ações disponíveis
     */
    const ACTION_LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    const ACTION_LOGIN_FAILED = 'LOGIN_FAILED';
    const ACTION_LOGOUT = 'LOGOUT';
    const ACTION_REGISTER = 'REGISTER';
    const ACTION_PASSWORD_RESET = 'PASSWORD_RESET';
    const ACTION_PASSWORD_CHANGE = 'PASSWORD_CHANGE';
    const ACTION_PROFILE_UPDATE = 'PROFILE_UPDATE';
    const ACTION_CAMPAIGN_CREATE = 'CAMPAIGN_CREATE';
    const ACTION_CAMPAIGN_UPDATE = 'CAMPAIGN_UPDATE';
    const ACTION_CAMPAIGN_DELETE = 'CAMPAIGN_DELETE';
    const ACTION_CAMPAIGN_APPROVE = 'CAMPAIGN_APPROVE';
    const ACTION_CAMPAIGN_REJECT = 'CAMPAIGN_REJECT';
    const ACTION_DONATION_CREATE = 'DONATION_CREATE';
    const ACTION_DONATION_CONFIRM = 'DONATION_CONFIRM';
    const ACTION_RAFFLE_PURCHASE = 'RAFFLE_PURCHASE';
    const ACTION_RAFFLE_CONFIRM = 'RAFFLE_CONFIRM';
    const ACTION_ADMIN_ACCESS = 'ADMIN_ACCESS';
    const ACTION_UNAUTHORIZED_ACCESS = 'UNAUTHORIZED_ACCESS';
    const ACTION_WEBHOOK_RECEIVED = 'WEBHOOK_RECEIVED';
    const ACTION_SUSPICIOUS_ACTIVITY = 'SUSPICIOUS_ACTIVITY';

    /**
     * Registra uma ação no log de auditoria
     */
    public function log(
        string $action,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        $oldValue = null,
        $newValue = null,
        ?array $extraData = null
    ): int {
        $request = \Config\Services::request();

        // Detectar IP e User Agent (tratar caso CLI)
        $ipAddress = '0.0.0.0';
        $userAgent = 'CLI';

        try {
            $ipAddress = $request->getIPAddress();
            if (method_exists($request, 'getUserAgent')) {
                $ua = $request->getUserAgent();
                if ($ua && method_exists($ua, 'getAgentString')) {
                    $userAgent = substr($ua->getAgentString(), 0, 500);
                }
            }
        } catch (\Exception $e) {
            // CLI mode, usar valores padrão
        }

        return $this->insert([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue ? $this->sanitizeForLog($oldValue) : null,
            'new_value' => $newValue ? $this->sanitizeForLog($newValue) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'extra_data' => $extraData ? json_encode($extraData) : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Atalho para log de login bem-sucedido
     */
    public function logLogin(int $userId, string $method = 'email'): int
    {
        return $this->log(
            self::ACTION_LOGIN_SUCCESS,
            $userId,
            'user',
            $userId,
            null,
            null,
            ['method' => $method]
        );
    }

    /**
     * Atalho para log de login falho
     */
    public function logLoginFailed(string $email, string $reason = 'invalid_credentials'): int
    {
        return $this->log(
            self::ACTION_LOGIN_FAILED,
            null,
            'user',
            null,
            null,
            null,
            ['email' => $this->maskEmail($email), 'reason' => $reason]
        );
    }

    /**
     * Atalho para log de atividade suspeita
     */
    public function logSuspicious(string $description, ?int $userId = null, ?array $details = null): int
    {
        return $this->log(
            self::ACTION_SUSPICIOUS_ACTIVITY,
            $userId,
            null,
            null,
            null,
            null,
            array_merge(['description' => $description], $details ?? [])
        );
    }

    /**
     * Busca logs por usuário
     */
    public function getByUser(int $userId, int $limit = 100): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Busca logs por ação
     */
    public function getByAction(string $action, int $limit = 100): array
    {
        return $this->where('action', $action)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Busca logs de atividades suspeitas
     */
    public function getSuspiciousActivities(int $hours = 24): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->whereIn('action', [
            self::ACTION_LOGIN_FAILED,
            self::ACTION_UNAUTHORIZED_ACCESS,
            self::ACTION_SUSPICIOUS_ACTIVITY
        ])
            ->where('created_at >=', $since)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Sanitiza dados sensíveis antes de salvar no log
     */
    protected function sanitizeForLog($data): string
    {
        if (is_string($data)) {
            return $data;
        }

        if (is_array($data) || is_object($data)) {
            $data = (array) $data;

            // Campos sensíveis a mascarar
            $sensitiveFields = [
                'password', 'senha', 'token', 'secret', 'key', 'api_key',
                'access_token', 'refresh_token', 'cpf', 'cnpj', 'card_number',
                'cvv', 'credit_card', 'bank_account'
            ];

            foreach ($sensitiveFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = '***REDACTED***';
                }
            }

            // Mascarar email parcialmente
            if (isset($data['email'])) {
                $data['email'] = $this->maskEmail($data['email']);
            }

            return json_encode($data);
        }

        return (string) $data;
    }

    /**
     * Mascara email para log
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***';
        }

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));

        return $maskedName . '@' . $domain;
    }
}

<?php
/**
 * HUBFLOW - Sistema de Logs Avançados (Audit Trail)
 * =================================================
 * Registra todas as ações importantes do sistema
 *
 * Funcionalidades:
 * - Log de ações de usuários
 * - Histórico de alterações (old/new values)
 * - Rastreamento de IP e User Agent
 * - Filtros e busca avançada
 * - Exportação de logs
 */

namespace App\Libraries;

class AuditLogLibrary
{
    protected $db;
    protected $request;
    protected $session;

    // Tipos de ações
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_LOGIN_FAILED = 'login_failed';
    const ACTION_PASSWORD_RESET = 'password_reset';
    const ACTION_PASSWORD_CHANGE = 'password_change';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_SETTINGS_CHANGE = 'settings_change';
    const ACTION_PERMISSION_CHANGE = 'permission_change';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->request = \Config\Services::request();
        $this->session = \Config\Services::session();
    }

    /**
     * ==========================================================================
     * LOGGING - Registro de logs
     * ==========================================================================
     */

    /**
     * Registrar log de ação
     */
    public function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $userId = null
    ): int {
        $userId = $userId ?? $this->session->get('user_id');

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'description' => $description,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'url' => current_url(),
            'method' => $this->request->getMethod(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('audit_logs')->insert($data);

        return $this->db->insertID();
    }

    /**
     * Log de criação de registro
     */
    public function logCreate(string $entityType, int $entityId, array $newValues, ?string $description = null): int
    {
        return $this->log(
            self::ACTION_CREATE,
            $entityType,
            $entityId,
            null,
            $this->filterSensitiveData($newValues),
            $description ?? "Criação de {$entityType} #{$entityId}"
        );
    }

    /**
     * Log de atualização de registro
     */
    public function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues, ?string $description = null): int
    {
        // Filtrar apenas campos que mudaram
        $changes = [];
        $originalValues = [];

        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changes[$key] = $value;
                $originalValues[$key] = $oldValues[$key];
            }
        }

        if (empty($changes)) {
            return 0; // Nada mudou
        }

        return $this->log(
            self::ACTION_UPDATE,
            $entityType,
            $entityId,
            $this->filterSensitiveData($originalValues),
            $this->filterSensitiveData($changes),
            $description ?? "Atualização de {$entityType} #{$entityId}"
        );
    }

    /**
     * Log de exclusão de registro
     */
    public function logDelete(string $entityType, int $entityId, array $oldValues, ?string $description = null): int
    {
        return $this->log(
            self::ACTION_DELETE,
            $entityType,
            $entityId,
            $this->filterSensitiveData($oldValues),
            null,
            $description ?? "Exclusão de {$entityType} #{$entityId}"
        );
    }

    /**
     * Log de visualização de registro
     */
    public function logView(string $entityType, int $entityId, ?string $description = null): int
    {
        return $this->log(
            self::ACTION_VIEW,
            $entityType,
            $entityId,
            null,
            null,
            $description
        );
    }

    /**
     * Log de login bem-sucedido
     */
    public function logLogin(int $userId, string $method = 'email'): int
    {
        return $this->log(
            self::ACTION_LOGIN,
            'user',
            $userId,
            null,
            ['method' => $method],
            "Login via {$method}",
            $userId
        );
    }

    /**
     * Log de tentativa de login falha
     */
    public function logLoginFailed(string $email, string $reason = 'invalid_credentials'): int
    {
        return $this->log(
            self::ACTION_LOGIN_FAILED,
            'user',
            null,
            null,
            [
                'email' => $email,
                'reason' => $reason
            ],
            "Tentativa de login falha: {$reason}"
        );
    }

    /**
     * Log de logout
     */
    public function logLogout(?int $userId = null): int
    {
        $userId = $userId ?? $this->session->get('user_id');

        return $this->log(
            self::ACTION_LOGOUT,
            'user',
            $userId,
            null,
            null,
            'Logout realizado',
            $userId
        );
    }

    /**
     * Log de alteração de senha
     */
    public function logPasswordChange(int $userId, string $method = 'manual'): int
    {
        return $this->log(
            self::ACTION_PASSWORD_CHANGE,
            'user',
            $userId,
            null,
            ['method' => $method],
            "Senha alterada via {$method}",
            $userId
        );
    }

    /**
     * Log de reset de senha
     */
    public function logPasswordReset(int $userId, string $email): int
    {
        return $this->log(
            self::ACTION_PASSWORD_RESET,
            'user',
            $userId,
            null,
            ['email' => $email],
            'Solicitação de reset de senha',
            $userId
        );
    }

    /**
     * Log de alteração de configurações
     */
    public function logSettingsChange(string $settingKey, $oldValue, $newValue, ?string $description = null): int
    {
        return $this->log(
            self::ACTION_SETTINGS_CHANGE,
            'settings',
            null,
            ['key' => $settingKey, 'value' => $oldValue],
            ['key' => $settingKey, 'value' => $newValue],
            $description ?? "Configuração alterada: {$settingKey}"
        );
    }

    /**
     * Log de alteração de permissões
     */
    public function logPermissionChange(int $userId, string $role, array $permissions, ?string $description = null): int
    {
        return $this->log(
            self::ACTION_PERMISSION_CHANGE,
            'user',
            $userId,
            null,
            ['role' => $role, 'permissions' => $permissions],
            $description ?? "Permissões alteradas para usuário #{$userId}"
        );
    }

    /**
     * ==========================================================================
     * QUERIES - Consultas de logs
     * ==========================================================================
     */

    /**
     * Buscar logs com filtros
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $builder = $this->db->table('audit_logs al')
            ->select('al.*, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC');

        // Aplicar filtros
        if (!empty($filters['user_id'])) {
            $builder->where('al.user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $builder->where('al.action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $builder->where('al.entity_type', $filters['entity_type']);
        }

        if (!empty($filters['entity_id'])) {
            $builder->where('al.entity_id', $filters['entity_id']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('al.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('al.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['ip_address'])) {
            $builder->where('al.ip_address', $filters['ip_address']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('al.description', $filters['search'])
                ->orLike('u.name', $filters['search'])
                ->orLike('u.email', $filters['search'])
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $logs = $builder->limit($limit, $offset)->get()->getResultArray();

        // Decodificar JSON
        foreach ($logs as &$log) {
            $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
            $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
        }

        return [
            'logs' => $logs,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Buscar histórico de uma entidade
     */
    public function getEntityHistory(string $entityType, int $entityId, int $limit = 20): array
    {
        return $this->getLogs([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ], $limit);
    }

    /**
     * Buscar histórico de um usuário
     */
    public function getUserHistory(int $userId, int $limit = 50): array
    {
        return $this->getLogs(['user_id' => $userId], $limit);
    }

    /**
     * Buscar logs de login
     */
    public function getLoginLogs(int $limit = 100): array
    {
        return $this->getLogs([
            'action' => self::ACTION_LOGIN
        ], $limit);
    }

    /**
     * Buscar logs de tentativas de login falhas
     */
    public function getFailedLoginLogs(int $limit = 100): array
    {
        return $this->getLogs([
            'action' => self::ACTION_LOGIN_FAILED
        ], $limit);
    }

    /**
     * Buscar ações suspeitas
     */
    public function getSuspiciousActivity(int $limit = 50): array
    {
        // Múltiplas tentativas de login falhas do mesmo IP
        $suspiciousIps = $this->db->table('audit_logs')
            ->select('ip_address, COUNT(*) as attempts')
            ->where('action', self::ACTION_LOGIN_FAILED)
            ->where('created_at >', date('Y-m-d H:i:s', time() - 3600))
            ->groupBy('ip_address')
            ->having('COUNT(*) >=', 5)
            ->get()
            ->getResultArray();

        return $suspiciousIps;
    }

    /**
     * ==========================================================================
     * STATISTICS - Estatísticas de logs
     * ==========================================================================
     */

    /**
     * Obter estatísticas gerais
     */
    public function getStatistics(string $period = 'day'): array
    {
        $dateFormat = match($period) {
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        // Total de logs por tipo de ação
        $byAction = $this->db->table('audit_logs')
            ->select('action, COUNT(*) as total')
            ->groupBy('action')
            ->get()
            ->getResultArray();

        // Logs por período
        $byPeriod = $this->db->table('audit_logs')
            ->select("DATE_FORMAT(created_at, '{$dateFormat}') as period, COUNT(*) as total")
            ->where('created_at >', date('Y-m-d H:i:s', time() - 604800)) // Última semana
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->get()
            ->getResultArray();

        // Usuários mais ativos
        $topUsers = $this->db->table('audit_logs al')
            ->select('al.user_id, u.name, u.email, COUNT(*) as actions')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.created_at >', date('Y-m-d H:i:s', time() - 604800))
            ->groupBy('al.user_id')
            ->orderBy('actions', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return [
            'by_action' => $byAction,
            'by_period' => $byPeriod,
            'top_users' => $topUsers,
            'total_logs' => $this->db->table('audit_logs')->countAllResults()
        ];
    }

    /**
     * ==========================================================================
     * HELPERS - Funções auxiliares
     * ==========================================================================
     */

    /**
     * Filtrar dados sensíveis
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_hash', 'senha', 'token', 'api_key',
            'secret', 'credit_card', 'cvv', 'cpf', 'cnpj',
            'access_token', 'refresh_token'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Obter IP do cliente
     */
    protected function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $this->request->getServer($header);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Limpar logs antigos
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', time() - ($daysToKeep * 86400));

        return $this->db->table('audit_logs')
            ->where('created_at <', $cutoffDate)
            ->delete();
    }

    /**
     * Exportar logs para CSV
     */
    public function exportToCsv(array $filters = []): string
    {
        $logs = $this->getLogs($filters, 10000)['logs'];

        $csv = "ID,Data,Usuário,Email,Ação,Entidade,ID Entidade,Descrição,IP,URL\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log['id'],
                $log['created_at'],
                '"' . str_replace('"', '""', $log['user_name'] ?? 'Sistema') . '"',
                '"' . str_replace('"', '""', $log['user_email'] ?? '') . '"',
                $log['action'],
                $log['entity_type'] ?? '',
                $log['entity_id'] ?? '',
                '"' . str_replace('"', '""', $log['description'] ?? '') . '"',
                $log['ip_address'],
                '"' . str_replace('"', '""', $log['url'] ?? '') . '"'
            ]) . "\n";
        }

        return $csv;
    }
}

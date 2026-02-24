<?php
/**
 * HUBFLOW - Biblioteca de Segurança Reforçada
 * ==========================================
 * Módulo reutilizável para todos os projetos SaaS
 *
 * Funcionalidades:
 * - Rate Limiting (anti-brute force)
 * - CSRF Protection
 * - Input Sanitization
 * - Security Headers
 * - Session Security
 * - IP Blocking
 * - Login Attempt Tracking
 */

namespace App\Libraries;

class SecurityLibrary
{
    protected $db;
    protected $session;
    protected $request;

    // Configurações de rate limiting
    protected $maxLoginAttempts = 5;
    protected $lockoutTime = 900; // 15 minutos em segundos
    protected $maxRequestsPerMinute = 60;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->request = \Config\Services::request();
    }

    /**
     * ==========================================================================
     * RATE LIMITING - Proteção contra ataques de força bruta
     * ==========================================================================
     */

    /**
     * Verificar se IP está bloqueado por tentativas excessivas
     */
    public function isIpBlocked(): bool
    {
        $ip = $this->getClientIp();

        $builder = $this->db->table('security_login_attempts');
        $attempts = $builder
            ->where('ip_address', $ip)
            ->where('success', 0)
            ->where('created_at >', date('Y-m-d H:i:s', time() - $this->lockoutTime))
            ->countAllResults();

        return $attempts >= $this->maxLoginAttempts;
    }

    /**
     * Registrar tentativa de login
     */
    public function recordLoginAttempt(string $email, bool $success, ?int $userId = null): void
    {
        $this->db->table('security_login_attempts')->insert([
            'ip_address' => $this->getClientIp(),
            'email' => $email,
            'user_id' => $userId,
            'success' => $success ? 1 : 0,
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Limpar tentativas antigas (mais de 24 horas)
        $this->db->table('security_login_attempts')
            ->where('created_at <', date('Y-m-d H:i:s', time() - 86400))
            ->delete();
    }

    /**
     * Obter tempo restante de bloqueio
     */
    public function getRemainingLockoutTime(): int
    {
        $ip = $this->getClientIp();

        $builder = $this->db->table('security_login_attempts');
        $lastAttempt = $builder
            ->where('ip_address', $ip)
            ->where('success', 0)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRowArray();

        if (!$lastAttempt) {
            return 0;
        }

        $lockoutEnd = strtotime($lastAttempt['created_at']) + $this->lockoutTime;
        $remaining = $lockoutEnd - time();

        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Limpar tentativas de login após sucesso
     */
    public function clearLoginAttempts(string $email): void
    {
        $this->db->table('security_login_attempts')
            ->where('email', $email)
            ->where('ip_address', $this->getClientIp())
            ->delete();
    }

    /**
     * ==========================================================================
     * SECURITY HEADERS - Headers de segurança HTTP
     * ==========================================================================
     */

    /**
     * Aplicar headers de segurança na resposta
     */
    public function applySecurityHeaders(\CodeIgniter\HTTP\ResponseInterface $response): void
    {
        // Prevenir clickjacking
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME type sniffing
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // Ativar XSS filter do navegador
        $response->setHeader('X-XSS-Protection', '1; mode=block');

        // Content Security Policy
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com https://accounts.google.com https://apis.google.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; ";
        $csp .= "img-src 'self' data: https: blob:; ";
        $csp .= "frame-src 'self' https://www.google.com https://accounts.google.com; ";
        $csp .= "connect-src 'self' https://apis.google.com https://www.googleapis.com;";
        $response->setHeader('Content-Security-Policy', $csp);

        // Referrer Policy
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS (apenas em produção com HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }

    /**
     * ==========================================================================
     * INPUT SANITIZATION - Sanitização de entrada
     * ==========================================================================
     */

    /**
     * Sanitizar string para prevenir XSS
     */
    public function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        // Remover tags HTML
        $input = strip_tags($input);

        // Converter caracteres especiais
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remover caracteres de controle
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        return trim($input);
    }

    /**
     * Sanitizar email
     */
    public function sanitizeEmail(?string $email): string
    {
        if ($email === null) {
            return '';
        }

        return filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitizar telefone (apenas números)
     */
    public function sanitizePhone(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }

        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Sanitizar array de dados
     */
    public function sanitizeArray(array $data, array $rules = []): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                switch ($rules[$key]) {
                    case 'email':
                        $sanitized[$key] = $this->sanitizeEmail($value);
                        break;
                    case 'phone':
                        $sanitized[$key] = $this->sanitizePhone($value);
                        break;
                    case 'int':
                        $sanitized[$key] = (int) $value;
                        break;
                    case 'float':
                        $sanitized[$key] = (float) $value;
                        break;
                    case 'bool':
                        $sanitized[$key] = (bool) $value;
                        break;
                    case 'html':
                        $sanitized[$key] = $this->sanitizeHtml($value);
                        break;
                    default:
                        $sanitized[$key] = $this->sanitizeString($value);
                }
            } else {
                $sanitized[$key] = is_string($value) ? $this->sanitizeString($value) : $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizar HTML (permite tags seguras)
     */
    public function sanitizeHtml(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code>';

        return strip_tags($html, $allowedTags);
    }

    /**
     * ==========================================================================
     * SESSION SECURITY - Segurança de sessão
     * ==========================================================================
     */

    /**
     * Regenerar ID da sessão (previne session fixation)
     */
    public function regenerateSession(): void
    {
        $this->session->regenerate(true);
    }

    /**
     * Validar sessão do usuário
     */
    public function validateSession(): bool
    {
        // Verificar se sessão existe
        if (!$this->session->has('user_id')) {
            return false;
        }

        // Verificar fingerprint do navegador
        $currentFingerprint = $this->generateBrowserFingerprint();
        $storedFingerprint = $this->session->get('browser_fingerprint');

        if ($storedFingerprint && $currentFingerprint !== $storedFingerprint) {
            log_message('warning', 'Session fingerprint mismatch for user ' . $this->session->get('user_id'));
            return false;
        }

        // Verificar timeout de inatividade (30 minutos)
        $lastActivity = $this->session->get('last_activity');
        if ($lastActivity && (time() - $lastActivity) > 1800) {
            return false;
        }

        // Atualizar timestamp de atividade
        $this->session->set('last_activity', time());

        return true;
    }

    /**
     * Criar sessão segura após login
     */
    public function createSecureSession(array $userData): void
    {
        // Regenerar sessão
        $this->regenerateSession();

        // Dados básicos da sessão
        $sessionData = [
            'user_id' => $userData['id'],
            'user_email' => $userData['email'],
            'user_name' => $userData['name'],
            'user_role' => $userData['role'] ?? 'user',
            'logged_in' => true,
            'login_time' => time(),
            'last_activity' => time(),
            'browser_fingerprint' => $this->generateBrowserFingerprint(),
            'ip_address' => $this->getClientIp()
        ];

        $this->session->set($sessionData);

        // Log de login
        log_message('info', "User {$userData['id']} logged in from IP " . $this->getClientIp());
    }

    /**
     * Destruir sessão de forma segura
     */
    public function destroySession(): void
    {
        $userId = $this->session->get('user_id');

        // Limpar dados da sessão
        $this->session->destroy();

        // Log de logout
        if ($userId) {
            log_message('info', "User {$userId} logged out");
        }
    }

    /**
     * Gerar fingerprint do navegador
     */
    protected function generateBrowserFingerprint(): string
    {
        $data = [
            $this->request->getUserAgent()->getAgentString(),
            $this->request->getServer('HTTP_ACCEPT_LANGUAGE'),
            $this->request->getServer('HTTP_ACCEPT_ENCODING')
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * ==========================================================================
     * IP MANAGEMENT - Gestão de IPs
     * ==========================================================================
     */

    /**
     * Obter IP real do cliente
     */
    public function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $this->request->getServer($header);
            if ($ip) {
                // Se for uma lista, pegar o primeiro
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validar IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Verificar se IP está em lista negra
     */
    public function isIpBlacklisted(): bool
    {
        $ip = $this->getClientIp();

        $blacklisted = $this->db->table('security_ip_blacklist')
            ->where('ip_address', $ip)
            ->where('(expires_at IS NULL OR expires_at > NOW())')
            ->countAllResults();

        return $blacklisted > 0;
    }

    /**
     * Adicionar IP à lista negra
     */
    public function blacklistIp(string $ip, string $reason, ?int $expiresInSeconds = null): void
    {
        $expiresAt = $expiresInSeconds ? date('Y-m-d H:i:s', time() + $expiresInSeconds) : null;

        $this->db->table('security_ip_blacklist')->insert([
            'ip_address' => $ip,
            'reason' => $reason,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        log_message('warning', "IP {$ip} blacklisted: {$reason}");
    }

    /**
     * ==========================================================================
     * PASSWORD SECURITY - Segurança de senhas
     * ==========================================================================
     */

    /**
     * Gerar hash seguro de senha
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verificar senha
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Verificar força da senha
     */
    public function checkPasswordStrength(string $password): array
    {
        $errors = [];
        $score = 0;

        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres';
        } else {
            $score += 1;
        }

        // Tem letra maiúscula
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula';
        }

        // Tem letra minúscula
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $errors[] = 'A senha deve conter pelo menos uma letra minúscula';
        }

        // Tem número
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $errors[] = 'A senha deve conter pelo menos um número';
        }

        // Tem caractere especial
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $errors[] = 'A senha deve conter pelo menos um caractere especial';
        }

        // Não é uma senha comum
        $commonPasswords = ['123456', 'password', 'qwerty', 'abc123', '111111', 'senha123'];
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Esta senha é muito comum. Escolha uma senha mais segura';
            $score = 0;
        }

        return [
            'valid' => empty($errors),
            'score' => $score,
            'max_score' => 5,
            'strength' => $this->getPasswordStrengthLabel($score),
            'errors' => $errors
        ];
    }

    protected function getPasswordStrengthLabel(int $score): string
    {
        return match(true) {
            $score <= 1 => 'muito_fraca',
            $score == 2 => 'fraca',
            $score == 3 => 'media',
            $score == 4 => 'forte',
            $score >= 5 => 'muito_forte',
            default => 'desconhecida'
        };
    }

    /**
     * ==========================================================================
     * TOKEN GENERATION - Geração de tokens seguros
     * ==========================================================================
     */

    /**
     * Gerar token seguro
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Gerar código numérico (para SMS/WhatsApp)
     */
    public function generateNumericCode(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }
}

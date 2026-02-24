<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditLogModel;
use App\Libraries\AsaasLibrary;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected $userModel;
    protected $validation;
    protected $session;
    protected $asaas;
    protected $auditLog;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditLog = new AuditLogModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->asaas = new AsaasLibrary();
        helper(['form', 'url', 'recaptcha']);
    }

    /**
     * Exibe formulário de login
     * GET /login
     */
    public function login()
    {
        // Se já estiver logado, redireciona para dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        $data = [
            'title' => 'Login - DoarFazBem',
            'description' => 'Faça login na sua conta DoarFazBem'
        ];

        return view('auth/login', $data);
    }

    /**
     * Processa login
     * POST /login
     */
    public function doLogin()
    {
        // Verificar reCAPTCHA
        $token = $this->request->getPost('recaptcha_token');
        if (!verify_recaptcha($token, 'login')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        }

        // Validação básica
        if (!$this->validate([
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'O email é obrigatório',
                    'valid_email' => 'Digite um email válido'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'A senha é obrigatória',
                    'min_length' => 'A senha deve ter no mínimo 8 caracteres'
                ]
            ]
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Buscar usuário por email
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            // Log de tentativa de login com email inexistente
            $this->auditLog->logLoginFailed($email, 'user_not_found');
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email ou senha incorretos');
        }

        // Verificar senha (campo password_hash na tabela)
        if (!password_verify($password, $user['password_hash'])) {
            // Log de tentativa de login com senha errada
            $this->auditLog->logLoginFailed($email, 'invalid_password');
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email ou senha incorretos');
        }

        // Verificar se o usuário está bloqueado/suspenso
        $loginCheck = $this->userModel->canLogin($user['id']);
        if (!$loginCheck['allowed']) {
            $this->auditLog->logLoginFailed($email, 'account_blocked');
            return redirect()->back()
                ->withInput()
                ->with('error', $loginCheck['reason']);
        }

        // Atualizar last_login
        $this->userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Criar sessão
        $sessionData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
            'isLoggedIn' => true
        ];

        $this->session->set($sessionData);

        // Regenerar ID da sessão para prevenir session fixation
        $this->session->regenerate();

        // Log de login bem-sucedido
        $this->auditLog->logLogin($user['id'], 'email');

        // Se marcou "lembrar-me", estender cookie de sessão
        if ($remember) {
            // Cookie de 30 dias
            $this->session->setTempdata('remember', true, 2592000);
        }

        // Redirecionar baseado no papel do usuário
        if (in_array($user['role'], ['admin', 'superadmin'])) {
            return redirect()->to(base_url('admin/dashboard'))
                ->with('success', 'Bem-vindo de volta, ' . $user['name'] . '!');
        }

        return redirect()->to(base_url('dashboard'))
            ->with('success', 'Login realizado com sucesso!');
    }

    /**
     * Exibe formulário de registro
     * GET /register
     */
    public function register()
    {
        // Se já estiver logado, redireciona para dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        $data = [
            'title' => 'Criar Conta - DoarFazBem',
            'description' => 'Crie sua conta gratuita na DoarFazBem'
        ];

        return view('auth/register', $data);
    }

    /**
     * Processa registro
     * POST /register
     */
    public function doRegister()
    {
        // Verificar reCAPTCHA
        $token = $this->request->getPost('recaptcha_token');
        if (!verify_recaptcha($token, 'register')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        }

        // Validação
        if (!$this->validate([
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'O nome é obrigatório',
                    'min_length' => 'O nome deve ter no mínimo 3 caracteres',
                    'max_length' => 'O nome deve ter no máximo 100 caracteres'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'O email é obrigatório',
                    'valid_email' => 'Digite um email válido',
                    'is_unique' => 'Este email já está cadastrado'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'A senha é obrigatória',
                    'min_length' => 'A senha deve ter no mínimo 8 caracteres'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'A confirmação de senha é obrigatória',
                    'matches' => 'As senhas não coincidem'
                ]
            ],
            'terms' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Você deve aceitar os Termos de Uso e Política de Privacidade'
                ]
            ]
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Criar usuário
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone' => $this->request->getPost('phone') ?: null,
            'cpf' => $this->request->getPost('cpf') ? preg_replace('/[^0-9]/', '', $this->request->getPost('cpf')) : null,
            'role' => 'user',
            'email_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $userId = $this->userModel->insert($userData);

        if (!$userId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar conta. Tente novamente.');
        }

        // Criar conta no Asaas (subconta para receber pagamentos)
        if (!empty($userData['cpf'])) {
            try {
                $asaasData = [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'cpf_cnpj' => $userData['cpf'],
                    'phone' => $userData['phone'] ?? null,
                    'mobile_phone' => $userData['phone'] ?? null,
                ];

                $asaasResponse = $this->asaas->createAccount($asaasData);

                if (isset($asaasResponse['id'])) {
                    // Salvar ID da conta Asaas no usuário
                    $this->userModel->update($userId, [
                        'asaas_account_id' => $asaasResponse['id']
                    ]);

                    log_message('info', 'Conta Asaas criada para usuário ' . $userId . ': ' . $asaasResponse['id']);
                } else {
                    log_message('error', 'Erro ao criar conta Asaas para usuário ' . $userId . ': ' . json_encode($asaasResponse));
                }
            } catch (\Exception $e) {
                log_message('error', 'Exceção ao criar conta Asaas: ' . $e->getMessage());
                // Não bloqueia o registro se falhar criar conta Asaas
            }
        }

        // Enviar email de boas-vindas
        $this->sendWelcomeEmail($userData['email'], $userData['name']);

        // Fazer login automático
        $user = $this->userModel->find($userId);

        $sessionData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
            'isLoggedIn' => true
        ];

        $this->session->set($sessionData);

        return redirect()->to(base_url('dashboard'))
            ->with('success', 'Conta criada com sucesso! Bem-vindo(a) ao DoarFazBem!');
    }

    /**
     * Logout
     * GET /logout
     */
    public function logout()
    {
        $this->session->destroy();

        return redirect()->to(base_url('/'))
            ->with('success', 'Logout realizado com sucesso!');
    }

    /**
     * Exibe formulário de recuperação de senha
     * GET /forgot-password
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Recuperar Senha - DoarFazBem',
            'description' => 'Recupere sua senha'
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Processa solicitação de recuperação de senha
     * POST /forgot-password
     */
    public function sendResetLink()
    {
        // Verificar reCAPTCHA
        $token = $this->request->getPost('recaptcha_token');
        if (!verify_recaptcha($token, 'forgot_password')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        }

        // Validação
        if (!$this->validate([
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'O email é obrigatório',
                    'valid_email' => 'Digite um email válido'
                ]
            ]
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->where('email', $email)->first();

        // Por segurança, sempre retorna sucesso mesmo se email não existir
        if (!$user) {
            return redirect()->back()
                ->with('success', 'Se o email existir em nossa base, você receberá instruções para redefinir sua senha.');
        }

        // Gerar token de reset
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco
        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);

        // Enviar email com link de reset
        $this->sendPasswordResetEmail($email, $user['name'], $token);

        return redirect()->back()
            ->with('success', 'Se o email existir em nossa base, você receberá instruções para redefinir sua senha.');
    }

    /**
     * Exibe formulário de redefinição de senha
     * GET /reset-password/{token}
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Token inválido');
        }

        // Verificar se token é válido e não expirou
        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_token_expiry >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Token inválido ou expirado. Solicite um novo link de recuperação.');
        }

        $data = [
            'title' => 'Redefinir Senha - DoarFazBem',
            'description' => 'Redefina sua senha',
            'token' => $token
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Processa redefinição de senha
     * POST /reset-password
     */
    public function doResetPassword()
    {
        // Validação
        if (!$this->validate([
            'token' => 'required',
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'A senha é obrigatória',
                    'min_length' => 'A senha deve ter no mínimo 8 caracteres'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'A confirmação de senha é obrigatória',
                    'matches' => 'As senhas não coincidem'
                ]
            ]
        ])) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');

        // Verificar token
        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_token_expiry >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Token inválido ou expirado');
        }

        // Atualizar senha e limpar token
        $this->userModel->update($user['id'], [
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expiry' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(base_url('login'))
            ->with('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }

    /**
     * ========================================================================
     * GOOGLE OAUTH - Login com Google (via League/oauth2-google)
     * ========================================================================
     */

    /**
     * Redireciona para Google OAuth com state CSRF
     * GET /auth/google
     */
    public function googleLogin()
    {
        $googleOAuth = new \App\Libraries\GoogleOAuth();
        $authUrl = $googleOAuth->getAuthorizationUrl();
        return redirect()->to($authUrl);
    }

    /**
     * Callback do Google OAuth com validação de state CSRF
     * GET /auth/google/callback
     */
    public function googleCallback()
    {
        $code  = $this->request->getGet('code');
        $state = $this->request->getGet('state');
        $error = $this->request->getGet('error');

        // Se usuário cancelou
        if ($error) {
            log_message('info', 'Google OAuth cancelado pelo usuário: ' . $error);
            return redirect()->to(base_url('login'))
                ->with('error', 'Login com Google cancelado.');
        }

        if (!$code) {
            log_message('error', 'Google OAuth: código não recebido');
            return redirect()->to(base_url('login'))
                ->with('error', 'Erro ao fazer login com Google. Tente novamente.');
        }

        $googleOAuth = new \App\Libraries\GoogleOAuth();

        // Validar state CSRF - previne CSRF attacks
        if (!$googleOAuth->validateState((string) $state)) {
            log_message('warning', 'Google OAuth: State inválido ou ausente - possível tentativa de CSRF');
            $this->auditLog->logSuspicious('Google OAuth state inválido', null, [
                'ip' => $this->request->getIPAddress(),
                'state_received' => $state,
            ]);
            return redirect()->to(base_url('login'))
                ->with('error', 'Erro de segurança. Por favor, tente novamente.');
        }

        try {
            $token = $googleOAuth->getAccessToken($code);

            if (!$token) {
                log_message('error', 'Google OAuth: falha ao obter access token');
                return redirect()->to(base_url('login'))
                    ->with('error', 'Erro ao autenticar com Google. Tente novamente.');
            }

            $userDetails = $googleOAuth->getUserDetails($token);

            if (!$userDetails || empty($userDetails['email'])) {
                log_message('error', 'Google OAuth: falha ao obter dados do usuário');
                return redirect()->to(base_url('login'))
                    ->with('error', 'Erro ao obter dados do Google. Tente novamente.');
            }

            // Adaptar formato para processGoogleUser
            $googleUserInfo = [
                'email'          => $userDetails['email'],
                'name'           => $userDetails['name'],
                'sub'            => $userDetails['google_id'],
                'picture'        => $userDetails['avatar'],
                'verified_email' => true, // OAuth Google só retorna emails verificados
            ];

            return $this->processGoogleUser($googleUserInfo);

        } catch (\Exception $e) {
            log_message('error', 'Google OAuth Exception: ' . $e->getMessage());
            return redirect()->to(base_url('login'))
                ->with('error', 'Erro ao fazer login com Google. Tente novamente.');
        }
    }

    /**
     * Processa login/registro de usuário do Google
     */
    private function processGoogleUser($googleUserInfo)
    {
        // Validar dados obrigatórios do Google
        if (empty($googleUserInfo['email'])) {
            log_message('error', 'Google OAuth: Email não fornecido');
            return redirect()->to(base_url('login'))
                ->with('error', 'Erro ao obter informações da conta Google. Tente novamente.');
        }

        $email = $googleUserInfo['email'];
        $name = $googleUserInfo['name'] ?? $googleUserInfo['given_name'] ?? 'Usuário';
        $googleId = $googleUserInfo['sub'] ?? $googleUserInfo['id'] ?? null;
        $picture = $googleUserInfo['picture'] ?? null;
        $emailVerified = $googleUserInfo['verified_email'] ?? $googleUserInfo['email_verified'] ?? false;

        // Log para debug
        log_message('debug', 'Google User Info: ' . json_encode($googleUserInfo));

        // Validar se temos o google_id
        if (empty($googleId)) {
            log_message('error', 'Google OAuth: ID do usuário não fornecido. Resposta: ' . json_encode($googleUserInfo));
            return redirect()->to(base_url('login'))
                ->with('error', 'Erro ao obter ID do Google. Tente novamente.');
        }

        // Verificar se usuário já existe (por email ou google_id)
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            $user = $this->userModel->where('google_id', $googleId)->first();
        }

        // Se usuário não existe, criar novo
        if (!$user) {
            // Gerar senha aleatória forte (não será usada, mas o banco requer)
            $randomPassword = bin2hex(random_bytes(16));

            $userData = [
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $picture,
                'email_verified' => $emailVerified ? 1 : 0,
                'role' => 'user',
                'password' => $randomPassword, // Será convertido para password_hash pelo Model
            ];

            // Desabilitar validação de senha para login OAuth
            $this->userModel->skipValidation(true);
            $userId = $this->userModel->insert($userData);
            $this->userModel->skipValidation(false);

            if (!$userId) {
                $errors = $this->userModel->errors();
                log_message('error', 'Erro ao criar usuário Google: ' . $email . ' - Erros: ' . json_encode($errors));
                return redirect()->to(base_url('login'))
                    ->with('error', 'Erro ao criar sua conta. Tente novamente.');
            }

            $user = $this->userModel->find($userId);

            log_message('info', 'Novo usuário criado via Google: ' . $email . ' (ID: ' . $userId . ')');

            // Enviar email de boas-vindas
            $this->sendWelcomeEmail($email, $name);

        } else {
            // Usuário existe, atualizar dados do Google se necessário
            $updateData = [];

            if (empty($user['google_id']) && $googleId) {
                $updateData['google_id'] = $googleId;
            }

            if (empty($user['avatar']) && $picture) {
                $updateData['avatar'] = $picture;
            }

            if (!$user['email_verified'] && $emailVerified) {
                $updateData['email_verified'] = 1;
            }

            $updateData['last_login'] = date('Y-m-d H:i:s');

            if (!empty($updateData)) {
                $this->userModel->update($user['id'], $updateData);
                // Recarregar usuário com dados atualizados
                $user = $this->userModel->find($user['id']);
            } else {
                // Apenas atualizar last_login
                $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            }

            log_message('info', 'Login Google: ' . $email . ' (ID: ' . $user['id'] . ')');
        }

        // Criar sessão
        $sessionData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
            'isLoggedIn' => true
        ];

        $this->session->set($sessionData);

        // Redirecionar baseado no papel do usuário
        if (in_array($user['role'], ['admin', 'superadmin'])) {
            return redirect()->to(base_url('admin/dashboard'))
                ->with('success', 'Bem-vindo de volta, ' . $user['name'] . '!');
        }

        return redirect()->to(base_url('dashboard'))
            ->with('success', 'Login realizado com sucesso!');
    }

    /**
     * Enviar email de boas-vindas
     */
    private function sendWelcomeEmail(string $email, string $name): bool
    {
        $emailService = \Config\Services::email();

        $emailService->setFrom(getenv('email.fromEmail') ?: 'contato@doarfazbem.com.br', getenv('email.fromName') ?: 'DoarFazBem');
        $emailService->setTo($email);
        $emailService->setSubject('Bem-vindo(a) ao DoarFazBem!');

        $message = $this->buildWelcomeEmailTemplate($name);
        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Email de boas-vindas enviado para: ' . $email);
            return true;
        } else {
            log_message('error', 'Erro ao enviar email de boas-vindas: ' . $emailService->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Template do email de boas-vindas
     */
    private function buildWelcomeEmailTemplate(string $name): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Bem-vindo(a) ao DoarFazBem!</h1>
    </div>

    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #4b5563;">Olá <strong>{$name}</strong>,</p>

            <p style="font-size: 16px; color: #4b5563;">
                É com grande alegria que damos as boas-vindas à nossa comunidade de solidariedade!
            </p>

            <p style="font-size: 16px; color: #4b5563;">
                No DoarFazBem, você pode:
            </p>

            <ul style="color: #4b5563; font-size: 15px;">
                <li style="margin-bottom: 10px;"><strong>Criar campanhas</strong> para arrecadar fundos para causas que você acredita</li>
                <li style="margin-bottom: 10px;"><strong>Doar</strong> para campanhas que tocam seu coração</li>
                <li style="margin-bottom: 10px;"><strong>Acompanhar</strong> o impacto das suas doações</li>
                <li style="margin-bottom: 10px;"><strong>Compartilhar</strong> campanhas com amigos e família</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{base_url('dashboard')}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Acessar Minha Conta
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">
                Se tiver qualquer dúvida, nossa equipe está sempre disponível para ajudar.
            </p>

            <p style="font-size: 14px; color: #6b7280;">
                Juntos, transformamos vidas! 💜
            </p>

        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">DoarFazBem - Transformando vidas através da solidariedade</p>
        <p style="margin: 5px 0 0 0;">Este é um email automático, por favor não responda.</p>
    </div>

</body>
</html>
HTML;
    }

    /**
     * Enviar email de reset de senha
     */
    private function sendPasswordResetEmail(string $email, string $name, string $resetToken): bool
    {
        $emailService = \Config\Services::email();

        $emailService->setFrom(getenv('email.fromEmail') ?: 'contato@doarfazbem.com.br', getenv('email.fromName') ?: 'DoarFazBem');
        $emailService->setTo($email);
        $emailService->setSubject('Redefinição de Senha - DoarFazBem');

        $resetLink = base_url("reset-password/{$resetToken}");
        $message = $this->buildPasswordResetEmailTemplate($name, $resetLink);
        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Email de reset de senha enviado para: ' . $email);
            return true;
        } else {
            log_message('error', 'Erro ao enviar email de reset: ' . $emailService->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Template do email de reset de senha
     */
    private function buildPasswordResetEmailTemplate(string $name, string $resetLink): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🔐 Redefinição de Senha</h1>
    </div>

    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #4b5563;">Olá <strong>{$name}</strong>,</p>

            <p style="font-size: 16px; color: #4b5563;">
                Recebemos uma solicitação para redefinir a senha da sua conta no DoarFazBem.
            </p>

            <p style="font-size: 16px; color: #4b5563;">
                Clique no botão abaixo para criar uma nova senha:
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{$resetLink}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Redefinir Minha Senha
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">
                Este link expira em <strong>1 hora</strong>.
            </p>

            <p style="font-size: 14px; color: #6b7280;">
                Se você não solicitou esta redefinição, ignore este email. Sua senha permanecerá a mesma.
            </p>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <p style="font-size: 12px; color: #9ca3af;">
                Se o botão não funcionar, copie e cole este link no seu navegador:<br>
                <a href="{$resetLink}" style="color: #667eea; word-break: break-all;">{$resetLink}</a>
            </p>

        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">DoarFazBem - Transformando vidas através da solidariedade</p>
    </div>

</body>
</html>
HTML;
    }

    /**
     * Enviar email de confirmação de mudança de senha
     */
    private function sendPasswordChangedEmail(string $email, string $name): bool
    {
        $emailService = \Config\Services::email();

        $emailService->setFrom(getenv('email.fromEmail') ?: 'contato@doarfazbem.com.br', getenv('email.fromName') ?: 'DoarFazBem');
        $emailService->setTo($email);
        $emailService->setSubject('Senha Alterada - DoarFazBem');

        $message = $this->buildPasswordChangedEmailTemplate($name);
        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Email de confirmação de mudança de senha enviado para: ' . $email);
            return true;
        } else {
            log_message('error', 'Erro ao enviar email de confirmação: ' . $emailService->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Template do email de confirmação de mudança de senha
     */
    private function buildPasswordChangedEmailTemplate(string $name): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">✅ Senha Alterada com Sucesso</h1>
    </div>

    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

            <p style="font-size: 16px; color: #4b5563;">Olá <strong>{$name}</strong>,</p>

            <p style="font-size: 16px; color: #4b5563;">
                Sua senha foi alterada com sucesso!
            </p>

            <p style="font-size: 16px; color: #4b5563;">
                Se você não realizou esta alteração, entre em contato conosco imediatamente através do email
                <a href="mailto:contato@doarfazbem.com.br" style="color: #667eea;">contato@doarfazbem.com.br</a>.
            </p>

            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    <strong>Dica de segurança:</strong> Nunca compartilhe sua senha com ninguém e use senhas diferentes para cada serviço.
                </p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{base_url('login')}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Fazer Login
                </a>
            </div>

        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">DoarFazBem - Transformando vidas através da solidariedade</p>
    </div>

</body>
</html>
HTML;
    }

}

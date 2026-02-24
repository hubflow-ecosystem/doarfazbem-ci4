<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * User Controller
 *
 * Gerencia autenticação e perfil de usuários
 * Funções: registro, login, logout, verificação de email, recuperação de senha
 */
class User extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    /**
     * Página de Registro
     * GET /register
     */
    public function register()
    {
        // Se já estiver logado, redireciona para dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Cadastre-se | DoarFazBem',
            'description' => 'Crie sua conta gratuita e comece a fazer a diferença'
        ];

        return view('auth/register', $data);
    }

    /**
     * Processar Registro
     * POST /register
     */
    public function processRegister()
    {
        // Verificar reCAPTCHA (DESABILITADO TEMPORARIAMENTE)
        // helper('recaptcha');
        // $token = $this->request->getPost('recaptcha_token');

        // if (!verify_recaptcha($token, 'register')) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        // }

        // Validação dos dados
        $validation = \Config\Services::validation();

        $validation->setRules([
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'O nome é obrigatório.',
                    'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
                    'max_length' => 'O nome não pode ter mais de 255 caracteres.'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'O email é obrigatório.',
                    'valid_email' => 'Por favor, insira um email válido.',
                    'is_unique' => 'Este email já está cadastrado.'
                ]
            ],
            'phone' => [
                'rules' => 'permit_empty|regex_match[/^\d{10,11}$/]',
                'errors' => [
                    'regex_match' => 'Telefone inválido. Use apenas números (ex: 11987654321)'
                ]
            ],
            'cpf' => [
                'rules' => 'permit_empty|regex_match[/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/]',
                'errors' => [
                    'regex_match' => 'CPF inválido. Use formato: 123.456.789-00'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'A senha é obrigatória.',
                    'min_length' => 'A senha deve ter pelo menos 8 caracteres.'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Confirme sua senha.',
                    'matches' => 'As senhas não coincidem.'
                ]
            ],
            'terms' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Você deve aceitar os termos de uso.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Validar CPF se fornecido
        $cpf = $this->request->getPost('cpf');
        if (!empty($cpf) && !UserModel::validateCPF($cpf)) {
            return redirect()->back()->withInput()->with('error', 'CPF inválido.');
        }

        // Verificar se CPF já existe (caso fornecido)
        if (!empty($cpf) && $this->userModel->cpfExists($cpf)) {
            return redirect()->back()->withInput()->with('error', 'Este CPF já está cadastrado.');
        }

        // Preparar dados para inserção
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone') ?: null,
            'cpf' => !empty($cpf) ? $cpf : null, // CPF pode ser NULL se não fornecido
            'password' => $this->request->getPost('password'),
            'email_verified' => false, // Será verificado via email
            'role' => 'user'
        ];

        // Inserir usuário no banco
        try {
            $userId = $this->userModel->insert($userData);

            if ($userId) {
                // Gerar token de verificação de email
                $token = bin2hex(random_bytes(32));
                $db = \Config\Database::connect();
                $db->table('email_verifications')->insert([
                    'user_id' => $userId,
                    'token' => $token,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
                ]);

                // Enviar email de verificação
                $verificationUrl = base_url("verify-email/{$token}");

                log_message('info', 'Tentando enviar email de verificação para: ' . $this->request->getPost('email'));

                $emailSent = $this->sendVerificationEmail(
                    $this->request->getPost('email'),
                    $this->request->getPost('name'),
                    $verificationUrl
                );

                if ($emailSent) {
                    log_message('info', 'Email de verificação enviado com sucesso para: ' . $this->request->getPost('email'));
                    $message = 'Cadastro realizado com sucesso! Verifique seu email para ativar sua conta.';
                } else {
                    log_message('error', 'Falha ao enviar email de verificação para: ' . $this->request->getPost('email'));
                    $message = 'Cadastro realizado! Porém houve erro ao enviar email de verificação. Entre em contato.';
                }

                $this->session->setFlashdata('success', $message);

                // Redirecionar para login
                return redirect()->to('/login');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao cadastrar usuário: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao cadastrar usuário. Tente novamente.');
        }
    }

    /**
     * Página de Login
     * GET /login
     */
    public function login()
    {
        // Se já estiver logado, redireciona para dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Entrar | DoarFazBem',
            'description' => 'Acesse sua conta DoarFazBem'
        ];

        return view('auth/login', $data);
    }

    /**
     * Processar Login
     * POST /login
     */
    public function processLogin()
    {
        // Verificar reCAPTCHA (DESABILITADO TEMPORARIAMENTE)
        // helper('recaptcha');
        // $token = $this->request->getPost('recaptcha_token');

        // if (!verify_recaptcha($token, 'login')) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        // }

        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'O email é obrigatório.',
                    'valid_email' => 'Por favor, insira um email válido.'
                ]
            ],
            'password' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'A senha é obrigatória.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Buscar usuário por email
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $user = $this->userModel->getUserByEmail($email);

        // Verificar se usuário existe e senha está correta
        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Email ou senha incorretos.');
        }

        // Verificar se email foi verificado
        if (!$user['email_verified']) {
            return redirect()->back()->withInput()->with('error', '⚠️ Sua conta ainda não foi verificada. Por favor, verifique seu email (confira também a pasta de spam/lixo eletrônico). <a href="/resend-verification?email=' . urlencode($email) . '" class="text-primary-600 underline">Reenviar email de verificação</a>');
        }

        // Criar sessão
        $sessionData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
            'email_verified' => $user['email_verified'],
            'isLoggedIn' => true
        ];

        $this->session->set($sessionData);

        // Se "Lembrar-me" foi marcado
        if ($remember) {
            // Cookie por 30 dias
            set_cookie('remember_token', bin2hex(random_bytes(32)), 2592000);
        }

        // Mensagem de sucesso
        $this->session->setFlashdata('success', 'Login realizado com sucesso! Bem-vindo, ' . $user['name'] . '!');

        // Redirecionar para dashboard
        return redirect()->to('/dashboard');
    }

    /**
     * Logout
     * GET /logout
     */
    public function logout()
    {
        // Destruir sessão
        $this->session->destroy();

        // Remover cookie "Lembrar-me"
        helper('cookie');
        delete_cookie('remember_token');

        // Mensagem de sucesso
        $this->session->setFlashdata('info', 'Você saiu da sua conta.');

        // Redirecionar para home
        return redirect()->to('/');
    }

    /**
     * Verificar Email
     * GET /verify-email/{token}
     */
    public function verifyEmail($token = null)
    {
        if (!$token) {
            return redirect()->to('/')->with('error', 'Token inválido.');
        }

        $db = \Config\Database::connect();
        $verification = $db->table('email_verifications')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$verification) {
            return redirect()->to('/')->with('error', 'Token inválido ou expirado. Solicite um novo email de verificação.');
        }

        // Marcar email como verificado
        $this->userModel->verifyEmail($verification['user_id']);

        // Deletar token usado
        $db->table('email_verifications')->where('id', $verification['id'])->delete();

        // Mensagem de sucesso
        $this->session->setFlashdata('success', '✅ Email verificado com sucesso! Você já pode fazer login.');

        return redirect()->to('/login');
    }

    /**
     * Página de Recuperação de Senha
     * GET /forgot-password
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Recuperar Senha | DoarFazBem',
            'description' => 'Redefina sua senha'
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Processar Recuperação de Senha
     * POST /forgot-password
     */
    public function processForgotPassword()
    {
        $email = $this->request->getPost('email');

        // Verificar se email existe
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            return redirect()->back()->with('error', 'Email não encontrado.');
        }

        // Gerar token
        $token = bin2hex(random_bytes(32));
        $db = \Config\Database::connect();

        // Deletar tokens anteriores deste email
        $db->table('password_resets')->where('email', $email)->delete();

        // Inserir novo token
        $db->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]);

        // Enviar email (será implementado na Fase 1.12)
        // TODO: Implementar envio de email

        $this->session->setFlashdata('success', 'Um link de recuperação foi enviado para seu email.');

        return redirect()->to('/login');
    }

    /**
     * Página de Redefinir Senha
     * GET /reset-password/{token}
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to('/')->with('error', 'Token inválido.');
        }

        $db = \Config\Database::connect();
        $reset = $db->table('password_resets')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$reset) {
            return redirect()->to('/')->with('error', 'Token inválido ou expirado.');
        }

        $data = [
            'title' => 'Redefinir Senha | DoarFazBem',
            'description' => 'Crie uma nova senha',
            'token' => $token
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Processar Redefinição de Senha
     * POST /reset-password
     */
    public function processResetPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        // Validação
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'A senha deve ter pelo menos 8 caracteres.');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', 'As senhas não coincidem.');
        }

        // Verificar token
        $db = \Config\Database::connect();
        $reset = $db->table('password_resets')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$reset) {
            return redirect()->to('/')->with('error', 'Token inválido ou expirado.');
        }

        // Buscar usuário
        $user = $this->userModel->getUserByEmail($reset['email']);

        if (!$user) {
            return redirect()->to('/')->with('error', 'Usuário não encontrado.');
        }

        // Atualizar senha
        $this->userModel->updatePassword($user['id'], $password);

        // Deletar token usado
        $db->table('password_resets')->where('id', $reset['id'])->delete();

        // Enviar email de confirmação de mudança de senha
        $this->sendPasswordChangedEmail($user['email'], $user['name']);

        // Mensagem de sucesso
        $this->session->setFlashdata('success', '✅ Senha redefinida com sucesso! Faça login com sua nova senha.');

        return redirect()->to('/login');
    }

    /**
     * Enviar email de verificação
     */
    private function sendVerificationEmail($toEmail, $toName, $verificationUrl)
    {
        try {
            $email = \Config\Services::email();

            $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
            $email->setTo($toEmail);
            $email->setSubject('Verifique seu email - DoarFazBem');

            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; }
                    .button { display: inline-block; background: #10B981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 10px 10px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>💚 Bem-vindo ao DoarFazBem!</h1>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>" . esc($toName) . "</strong>,</p>

                        <p>Obrigado por se cadastrar na maior plataforma de crowdfunding social do Brasil!</p>

                        <p>Para ativar sua conta e começar a fazer a diferença, clique no botão abaixo:</p>

                        <center>
                            <a href='" . $verificationUrl . "' class='button' style='color: white;'>
                                ✅ Verificar Meu Email
                            </a>
                        </center>

                        <p>Ou copie e cole este link no navegador:</p>
                        <p style='background: #f3f4f6; padding: 15px; border-radius: 5px; word-break: break-all;'>
                            <a href='" . $verificationUrl . "'>" . $verificationUrl . "</a>
                        </p>

                        <p><strong>⚠️ Este link expira em 24 horas.</strong></p>

                        <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;'>

                        <p style='color: #6b7280; font-size: 14px;'>
                            Se você não criou esta conta, ignore este email.
                        </p>
                    </div>
                    <div class='footer'>
                        <p><strong>DoarFazBem</strong> - A plataforma mais justa do Brasil</p>
                        <p>0% de taxa para campanhas médicas e sociais</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $email->setMessage($message);

            if ($email->send()) {
                return true;
            } else {
                log_message('error', 'Erro ao enviar email: ' . $email->printDebugger(['headers']));
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', 'Exceção ao enviar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Página de Perfil do Usuário
     * GET /profile
     */
    public function profile()
    {
        // Verificar se está logado
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Buscar dados do usuário
        $user = $this->userModel->find($this->session->get('id'));

        $data = [
            'title' => 'Meu Perfil | DoarFazBem',
            'description' => 'Gerencie seus dados pessoais',
            'user' => $user
        ];

        return view('user/profile', $data);
    }

    /**
     * Atualizar Perfil do Usuário
     * POST /profile/update
     */
    public function updateProfile()
    {
        // Verificar se está logado
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('id');

        $validationRules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'cpf' => 'permit_empty|max_length[14]',
            'birth_date' => 'permit_empty|valid_date',
            'postal_code' => 'permit_empty|max_length[9]',
            'address' => 'permit_empty|max_length[255]',
            'address_number' => 'permit_empty|max_length[20]',
            'address_complement' => 'permit_empty|max_length[100]',
            'province' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]',
            'state' => 'permit_empty|max_length[2]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Buscar usuário atual
        $currentUser = $this->userModel->find($userId);

        // Limpar formatação do telefone (remove parênteses, espaços, hífens)
        $phone = $this->request->getPost('phone');
        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'phone' => $phone,
            'birth_date' => $this->request->getPost('birth_date') ?: null,
            'postal_code' => $this->request->getPost('postal_code'),
            'address' => $this->request->getPost('address'),
            'address_number' => $this->request->getPost('address_number'),
            'address_complement' => $this->request->getPost('address_complement'),
            'province' => $this->request->getPost('province'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
        ];

        // Permitir salvar CPF apenas se ainda não estiver cadastrado
        if (empty($currentUser['cpf']) && !empty($this->request->getPost('cpf'))) {
            $data['cpf'] = $this->request->getPost('cpf');
        }

        // Atualizar dados
        try {
            if ($this->userModel->update($userId, $data)) {
                // Atualizar sessão
                $this->session->set('name', $data['name']);
                return redirect()->to('/profile')->with('success', 'Perfil atualizado com sucesso!');
            } else {
                // Log dos erros de validação do model
                $errors = $this->userModel->errors();
                log_message('error', 'Erro ao atualizar perfil: ' . json_encode($errors));
                log_message('error', 'Dados enviados: ' . json_encode($data));

                if (!empty($errors)) {
                    return redirect()->back()->withInput()->with('errors', $errors);
                }
                return redirect()->back()->withInput()->with('error', 'Erro ao atualizar perfil.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Exceção ao atualizar perfil: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar perfil: ' . $e->getMessage());
        }
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
        <h1 style="color: white; margin: 0; font-size: 28px;">Senha Alterada com Sucesso</h1>
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

    /**
     * Alterar senha do usuário
     * POST /profile/change-password
     */
    public function changePassword()
    {
        // Verificar se está logado
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('id');

        // Validação
        $rules = [
            'current_password' => [
                'rules' => 'required',
                'errors' => ['required' => 'A senha atual é obrigatória']
            ],
            'new_password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'A nova senha é obrigatória',
                    'min_length' => 'A nova senha deve ter no mínimo 8 caracteres'
                ]
            ],
            'confirm_password' => [
                'rules' => 'required|matches[new_password]',
                'errors' => [
                    'required' => 'Confirme a nova senha',
                    'matches' => 'As senhas não conferem'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Buscar usuário atual
        $user = $this->userModel->find($userId);

        // Verificar senha atual
        if (!password_verify($this->request->getPost('current_password'), $user['password_hash'])) {
            return redirect()->back()->with('error', 'Senha atual incorreta.');
        }

        // Verificar se a nova senha é diferente da atual
        if (password_verify($this->request->getPost('new_password'), $user['password_hash'])) {
            return redirect()->back()->with('error', 'A nova senha deve ser diferente da senha atual.');
        }

        // Atualizar senha
        $newPasswordHash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);

        if ($this->userModel->update($userId, ['password_hash' => $newPasswordHash])) {
            // Enviar email de confirmação
            $this->sendPasswordChangedEmail($user['email'], $user['name']);

            return redirect()->to('/profile')->with('success', 'Senha alterada com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao alterar senha. Tente novamente.');
    }
}

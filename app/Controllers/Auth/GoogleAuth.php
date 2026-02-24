<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Libraries\GoogleOAuth;
use App\Models\UserModel;

class GoogleAuth extends BaseController
{
    protected $googleOAuth;
    protected $userModel;

    public function __construct()
    {
        $this->googleOAuth = new GoogleOAuth();
        $this->userModel = new UserModel();
    }

    /**
     * Redireciona para a página de login do Google
     */
    public function redirect()
    {
        $authUrl = $this->googleOAuth->getAuthorizationUrl();
        return redirect()->to($authUrl);
    }

    /**
     * Callback após autenticação do Google
     */
    public function callback()
    {
        $code = $this->request->getGet('code');
        $state = $this->request->getGet('state');

        // Validar state para prevenir CSRF
        if (!$this->googleOAuth->validateState($state)) {
            return redirect()->to('/login')
                ->with('error', 'Estado de autenticação inválido. Tente novamente.');
        }

        // Trocar o code por access token
        $token = $this->googleOAuth->getAccessToken($code);

        if (!$token) {
            return redirect()->to('/login')
                ->with('error', 'Erro ao autenticar com Google. Tente novamente.');
        }

        // Obter dados do usuário
        $googleUser = $this->googleOAuth->getUserDetails($token);

        if (!$googleUser) {
            return redirect()->to('/login')
                ->with('error', 'Erro ao obter dados do usuário. Tente novamente.');
        }

        // Verificar se usuário já existe (por email ou google_id)
        $user = $this->userModel->where('email', $googleUser['email'])
            ->orWhere('google_id', $googleUser['google_id'])
            ->first();

        if ($user) {
            // Atualizar google_id e avatar se necessário
            if (empty($user['google_id'])) {
                $this->userModel->update($user['id'], [
                    'google_id' => $googleUser['google_id'],
                    'avatar' => $googleUser['avatar'],
                    'email_verified' => true, // Email já verificado pelo Google
                ]);
            }
        } else {
            // Criar novo usuário
            $userData = [
                'name'           => $googleUser['name'],
                'email'          => $googleUser['email'],
                'google_id'      => $googleUser['google_id'],
                'avatar'         => $googleUser['avatar'],
                'email_verified' => true, // Email já verificado pelo Google
                'password_hash'  => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), // Senha aleatória (não será usada)
                'created_at'     => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($userData);

            if (!$userId) {
                return redirect()->to('/login')
                    ->with('error', 'Erro ao criar conta. Tente novamente.');
            }

            $user = $this->userModel->find($userId);
        }

        // Criar sessão do usuário
        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'logged_in' => true,
        ]);

        // Redirecionar para dashboard
        return redirect()->to('/dashboard')
            ->with('success', 'Login realizado com sucesso!');
    }
}

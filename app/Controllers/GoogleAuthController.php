<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class GoogleAuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function redirect()
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $redirectUri = base_url('auth/google/callback');

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        return redirect()->to($authUrl);
    }

    public function callback()
    {
        log_message('info', '>>> GoogleAuthController callback iniciado <<<');

        $code = $this->request->getGet('code');

        if (!$code) {
            log_message('error', 'Código não recebido');
            return redirect()->to(base_url('login'))->with('error', 'Autenticação cancelada.');
        }

        try {
            $token = $this->getAccessToken($code);
            if (!$token) {
                return redirect()->to(base_url('login'))->with('error', 'Erro ao obter token.');
            }

            $userInfo = $this->getUserInfo($token);
            if (!$userInfo) {
                return redirect()->to(base_url('login'))->with('error', 'Erro ao obter dados do usuário.');
            }

            log_message('info', 'UserInfo: ' . json_encode($userInfo));
            return $this->processGoogleUser($userInfo);

        } catch (\Exception $e) {
            log_message('error', 'EXCEPTION: ' . $e->getMessage());
            return redirect()->to(base_url('login'))->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    private function getAccessToken($code)
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirectUri = base_url('auth/google/callback');
        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $params = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Habilitar verificacao SSL em producao
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ENVIRONMENT === 'production');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ENVIRONMENT === 'production' ? 2 : 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Token Error: ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    private function getUserInfo($accessToken)
    {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

        $ch = curl_init($userInfoUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Habilitar verificacao SSL em producao
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ENVIRONMENT === 'production');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ENVIRONMENT === 'production' ? 2 : 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'UserInfo Error: ' . $response);
            return null;
        }

        return json_decode($response, true);
    }

    private function processGoogleUser($userInfo)
    {
        // Usar null coalescing para evitar warning em modo development
        $email = $userInfo['email'] ?? null;
        $name = $userInfo['name'] ?? null;
        $googleId = $userInfo['id'] ?? null;
        $picture = $userInfo['picture'] ?? null;

        if (!$email || !$name || !$googleId) {
            log_message('error', 'Dados incompletos do Google: ' . json_encode($userInfo));
            throw new \Exception('Dados incompletos do Google');
        }

        log_message('info', "Processando: email=$email, name=$name, googleId=$googleId");

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            $userData = [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT),
                'role' => 'user',
                'google_id' => $googleId,
                'avatar' => $picture,
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->insert($userData);
            $user = $this->userModel->find($userId);
            log_message('info', 'Novo usuário criado: ' . $userId);
        } else {
            if (empty($user['google_id'])) {
                $this->userModel->update($user['id'], [
                    'google_id' => $googleId,
                    'avatar' => $picture,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->userModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        }

        $sessionData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'isLoggedIn' => true
        ];

        session()->set($sessionData);
        log_message('info', 'Login com Google bem-sucedido!');

        return redirect()->to(base_url('dashboard'))->with('success', 'Login com Google realizado com sucesso!');
    }
}

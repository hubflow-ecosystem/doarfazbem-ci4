<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class GoogleOAuth extends BaseController
{
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
        log_message('info', '>>> NOVO GoogleOAuth callback <<<');

        $code = $this->request->getGet('code');

        if (!$code) {
            return redirect()->to(base_url('login'))->with('error', 'Autenticação cancelada.');
        }

        try {
            $token = $this->getToken($code);
            if (!$token) {
                return redirect()->to(base_url('login'))->with('error', 'Erro ao obter token.');
            }

            $userInfo = $this->getUser($token);
            if (!$userInfo) {
                return redirect()->to(base_url('login'))->with('error', 'Erro ao obter dados do usuário.');
            }

            log_message('info', 'DADOS DO GOOGLE: ' . json_encode($userInfo));
            return $this->process($userInfo);

        } catch (\Throwable $e) {
            log_message('error', 'ERRO GOOGLE: ' . $e->getMessage());
            return redirect()->to(base_url('login'))->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    private function getToken($code)
    {
        $params = [
            'code' => $code,
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => base_url('auth/google/callback'),
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
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
        return $data['access_token'] ?? null;
    }

    private function getUser($token)
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
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

    private function process($info)
    {
        $userModel = new UserModel();

        $email = $info['email'] ?? null;
        $name = $info['name'] ?? null;
        $googleId = $info['id'] ?? null;
        $picture = $info['picture'] ?? null;

        if (!$email || !$name || !$googleId) {
            log_message('error', 'Dados incompletos');
            throw new \Exception('Dados incompletos do Google');
        }

        log_message('info', "email=$email, name=$name, googleId=$googleId");

        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            $userId = $userModel->insert([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT),
                'role' => 'user',
                'google_id' => $googleId,
                'avatar' => $picture,
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $user = $userModel->find($userId);
            log_message('info', 'Usuário criado: ' . $userId);
        } else {
            if (empty($user['google_id'])) {
                $userModel->update($user['id'], [
                    'google_id' => $googleId,
                    'avatar' => $picture,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        }

        session()->set([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'isLoggedIn' => true
        ]);

        log_message('info', 'Login Google OK!');
        return redirect()->to(base_url('dashboard'))->with('success', 'Login com Google realizado com sucesso!');
    }
}

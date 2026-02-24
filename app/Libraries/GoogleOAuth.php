<?php

namespace App\Libraries;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GoogleOAuth
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID'),
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => base_url('auth/google/callback'),
        ]);
    }

    /**
     * Gera a URL de autorização do Google
     */
    public function getAuthorizationUrl(): string
    {
        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => ['email', 'profile']
        ]);

        // Salvar o state na sessão para validação posterior
        session()->set('oauth2state', $this->provider->getState());

        return $authUrl;
    }

    /**
     * Obtém o token de acesso usando o código de autorização
     */
    public function getAccessToken(string $code)
    {
        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            return $token;
        } catch (IdentityProviderException $e) {
            log_message('error', 'Google OAuth Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém os dados do usuário autenticado
     */
    public function getUserDetails($token)
    {
        try {
            $user = $this->provider->getResourceOwner($token);

            return [
                'google_id' => $user->getId(),
                'email'     => $user->getEmail(),
                'name'      => $user->getName(),
                'avatar'    => $user->getAvatar(),
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting Google user details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida o state OAuth para prevenir CSRF
     */
    public function validateState(string $state): bool
    {
        $sessionState = session()->get('oauth2state');

        if (empty($state) || empty($sessionState) || $state !== $sessionState) {
            return false;
        }

        // Limpar o state da sessão após validação
        session()->remove('oauth2state');

        return true;
    }
}

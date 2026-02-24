<?php

namespace App\Services;

use App\Models\CampaignUpdateModel;
use App\Models\CampaignModel;

/**
 * Push Notification Service (Firebase Cloud Messaging HTTP v1 API)
 * Gerencia envio de notificações push via Firebase usando Service Account
 */
class PushNotificationService
{
    protected $projectId;
    protected $serviceAccount;
    protected $updateModel;
    protected $campaignModel;

    public function __construct()
    {
        $this->projectId = getenv('FIREBASE_PROJECT_ID');
        $this->updateModel = new CampaignUpdateModel();
        $this->campaignModel = new CampaignModel();

        // Carregar credenciais do service account
        $credentialsPath = ROOTPATH . 'config/firebase-credentials.json';

        if (!file_exists($credentialsPath)) {
            log_message('warning', 'firebase-credentials.json não encontrado');
            $this->serviceAccount = null;
            return;
        }

        $credentials = file_get_contents($credentialsPath);
        $this->serviceAccount = json_decode($credentials, true);

        if (!$this->serviceAccount) {
            log_message('error', 'Erro ao carregar firebase-credentials.json');
        }
    }

    /**
     * Enviar notificação push
     */
    public function sendCampaignUpdate($updateId, $pushToken)
    {
        if (!$this->serviceAccount || !$this->projectId) {
            throw new \Exception('Firebase não configurado. Verifique firebase-credentials.json e FIREBASE_PROJECT_ID no .env');
        }

        try {
            // Buscar dados da atualização
            $update = $this->updateModel->find($updateId);
            if (!$update) {
                throw new \Exception('Atualização não encontrada');
            }

            // Buscar dados da campanha
            $campaign = $this->campaignModel->find($update['campaign_id']);
            if (!$campaign) {
                throw new \Exception('Campanha não encontrada');
            }

            // Preparar payload FCM v1
            $message = [
                'message' => [
                    'token' => $pushToken,
                    'notification' => [
                        'title' => $campaign['title'],
                        'body' => $update['title'],
                    ],
                    'data' => [
                        'campaign_id' => (string) $campaign['id'],
                        'campaign_slug' => $campaign['slug'],
                        'update_id' => (string) $update['id'],
                        'click_action' => base_url("campaigns/{$campaign['slug']}"),
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high',
                        ],
                        'notification' => [
                            'icon' => base_url('assets/img/logo.png'),
                            'badge' => base_url('assets/img/logo.png'),
                        ],
                        'fcm_options' => [
                            'link' => base_url("campaigns/{$campaign['slug']}"),
                        ],
                    ],
                ],
            ];

            // Enviar via Firebase
            return $this->sendToFirebase($message);

        } catch (\Exception $e) {
            log_message('error', 'PushNotificationService: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar para Firebase Cloud Messaging HTTP v1 API
     */
    protected function sendToFirebase($message)
    {
        // Gerar Access Token OAuth 2.0
        $accessToken = $this->getAccessToken();

        // URL da API v1
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("Erro CURL ao enviar push: {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("Firebase retornou código {$httpCode}: {$response}");
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            throw new \Exception("Firebase erro: " . json_encode($result['error']));
        }

        log_message('info', "Push notification enviada com sucesso via FCM v1");
        return true;
    }

    /**
     * Gera Access Token OAuth 2.0 usando Service Account
     */
    protected function getAccessToken()
    {
        // Criar JWT (JSON Web Token)
        $now = time();
        $expiration = $now + 3600; // 1 hora

        $jwtHeader = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $jwtClaim = [
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiration,
            'iat' => $now,
        ];

        // Encode em Base64URL
        $headerEncoded = $this->base64UrlEncode(json_encode($jwtHeader));
        $claimEncoded = $this->base64UrlEncode(json_encode($jwtClaim));

        // Criar assinatura com private key
        $signatureInput = "{$headerEncoded}.{$claimEncoded}";
        $signature = '';

        openssl_sign(
            $signatureInput,
            $signature,
            $this->serviceAccount['private_key'],
            OPENSSL_ALGO_SHA256
        );

        $signatureEncoded = $this->base64UrlEncode($signature);

        // JWT completo
        $jwt = "{$headerEncoded}.{$claimEncoded}.{$signatureEncoded}";

        // Trocar JWT por Access Token
        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $postData = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Erro ao obter access token: {$response}");
        }

        $tokenData = json_decode($response, true);

        if (!isset($tokenData['access_token'])) {
            throw new \Exception("Access token não retornado: " . json_encode($tokenData));
        }

        return $tokenData['access_token'];
    }

    /**
     * Base64 URL-safe encoding
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Validar token Firebase
     */
    public function validateToken($token)
    {
        // Token FCM tem formato específico
        return !empty($token) && strlen($token) > 100;
    }
}

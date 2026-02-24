<?php

namespace App\Libraries;

/**
 * Firebase Cloud Messaging Service - DoarFazBem
 * Envia notificações push via FCM API v1 usando autenticação OAuth 2.0
 */
class FirebaseService
{
    private $projectId;
    private $credentials;
    private $accessToken = null;
    private $tokenExpiry = 0;

    public function __construct()
    {
        $credentialsPath = ROOTPATH . 'config/firebase-credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found: ' . $credentialsPath);
        }

        $this->credentials = json_decode(file_get_contents($credentialsPath), true);
        $this->projectId = $this->credentials['project_id'];
    }

    private static function writeLog($message)
    {
        log_message('info', $message);
    }

    private function getAccessToken()
    {
        if ($this->accessToken && time() < ($this->tokenExpiry - 300)) {
            return $this->accessToken;
        }

        $now = time();
        $exp = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'sub' => $this->credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];

        $jwt = $this->base64UrlEncode(json_encode($header)) . '.' . $this->base64UrlEncode(json_encode($claims));

        $signature = '';
        openssl_sign($jwt, $signature, $this->credentials['private_key'], 'SHA256');
        $jwt .= '.' . $this->base64UrlEncode($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            self::writeLog('[FCM] ERROR: Failed to get OAuth token: ' . $response);
            throw new \Exception('Failed to get OAuth access token');
        }

        $tokenData = json_decode($response, true);
        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpiry = $now + $tokenData['expires_in'];

        return $this->accessToken;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Ícones e cores por tipo - DOARFAZBEM
     */
    private function getIconByType($type)
    {
        $icons = [
            'donation_confirmed' => '/assets/icons/heart-success.png',
            'new_donation' => '/assets/icons/donation-received.png',
            'donation_refunded' => '/assets/icons/refund.png',
            'payment_failed' => '/assets/icons/payment-error.png',
            'campaign_approved' => '/assets/icons/check-circle.png',
            'campaign_rejected' => '/assets/icons/error-circle.png',
            'campaign_goal_reached' => '/assets/icons/trophy.png',
            'campaign_milestone' => '/assets/icons/milestone.png',
            'new_comment' => '/assets/icons/comment.png',
            'new_update' => '/assets/icons/megaphone.png',
            'default' => '/assets/icons/icon-192x192.png'
        ];

        return $icons[$type] ?? $icons['default'];
    }

    private function getColorByType($type)
    {
        $colors = [
            'donation_confirmed' => '#10B981',      // Verde
            'new_donation' => '#10B981',            // Verde
            'donation_refunded' => '#EF4444',       // Vermelho
            'payment_failed' => '#EF4444',          // Vermelho
            'campaign_approved' => '#10B981',       // Verde
            'campaign_rejected' => '#EF4444',       // Vermelho
            'campaign_goal_reached' => '#F59E0B',   // Amarelo/Ouro
            'campaign_milestone' => '#3B82F6',      // Azul
            'new_comment' => '#6366F1',             // Índigo
            'new_update' => '#8B5CF6',              // Roxo
            'default' => '#10B981'                  // Verde (cor da marca)
        ];

        return $colors[$type] ?? $colors['default'];
    }

    public function sendToToken($token, $title, $body, $data = [])
    {
        $icon = $data['icon'] ?? $this->getIconByType($data['type'] ?? 'default');
        $badge = $data['badge'] ?? '/assets/icons/icon-96x96.png';

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => array_map('strval', $data),
                'webpush' => [
                    'fcm_options' => [
                        'link' => $data['url'] ?? '/dashboard'
                    ],
                    'notification' => [
                        'icon' => $icon,
                        'badge' => $badge,
                        'requireInteraction' => false,
                        'vibrate' => [200, 100, 200],
                        'tag' => $data['tag'] ?? 'doarfazbem-notification',
                        'renotify' => true
                    ]
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'icon' => 'notification_icon',
                        'color' => $this->getColorByType($data['type'] ?? 'default'),
                        'sound' => 'default',
                        'defaultSound' => true,
                        'defaultVibrateTimings' => true
                    ]
                ]
            ]
        ];

        return $this->send($message);
    }

    private function send($payload)
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                self::writeLog('[FCM] ERROR: cURL Error: ' . $error);
                return ['success' => false, 'error' => $error];
            }

            curl_close($ch);
            $response = json_decode($result, true);

            if ($httpCode === 200) {
                self::writeLog('[FCM] INFO: Notification sent successfully');
                return ['success' => true, 'response' => $response];
            } else {
                self::writeLog('[FCM] ERROR: FCM Error [' . $httpCode . ']: ' . $result);
                return [
                    'success' => false,
                    'error' => $response['error']['message'] ?? 'Unknown error',
                    'response' => $response,
                    'http_code' => $httpCode
                ];
            }
        } catch (\Exception $e) {
            self::writeLog('[FCM] ERROR: Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function sendToUser($userId, $title, $body, $data = [])
    {
        self::writeLog("[FCM] Attempting to send notification to user {$userId}: {$title}");

        $db = \Config\Database::connect();

        $tokens = $db->table('fcm_tokens')
            ->select('token')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        if (empty($tokens)) {
            self::writeLog("[FCM] WARNING: No FCM tokens found for user {$userId}");
            self::saveNotificationHistory($userId, $title, $body, $data, 'failed', null, 'No FCM tokens found');
            return false;
        }

        self::writeLog("[FCM] Found " . count($tokens) . " token(s) for user {$userId}");

        $tokenList = array_column($tokens, 'token');
        $service = new self();
        $successCount = 0;
        $lastError = null;
        $fcmResponse = null;

        foreach ($tokenList as $token) {
            self::writeLog("[FCM] Sending to token: " . substr($token, 0, 20) . "...");

            $result = $service->sendToToken($token, $title, $body, $data);

            if ($result['success']) {
                $successCount++;
                $fcmResponse = $result['response'] ?? null;
                self::writeLog("[FCM] ✅ Notification sent successfully");
            } else {
                $lastError = $result['error'] ?? 'Unknown error';
                self::writeLog("[FCM] ❌ Failed: " . $lastError);

                // Desativar token inválido
                $db->table('fcm_tokens')
                    ->where('token', $token)
                    ->update(['is_active' => 0]);
            }
        }

        self::writeLog("[FCM] Sent {$successCount}/" . count($tokens) . " notifications for user {$userId}");

        $status = $successCount > 0 ? 'sent' : 'failed';
        self::saveNotificationHistory($userId, $title, $body, $data, $status, $fcmResponse, $lastError);

        return $successCount > 0;
    }

    private static function saveNotificationHistory($userId, $title, $body, $data, $status, $fcmResponse = null, $errorMessage = null)
    {
        try {
            $db = \Config\Database::connect();

            $notificationData = [
                'user_id' => $userId,
                'campaign_id' => $data['campaign_id'] ?? null,
                'donation_id' => $data['donation_id'] ?? null,
                'type' => $data['type'] ?? 'default',
                'title' => $title,
                'body' => $body,
                'icon' => $data['icon'] ?? null,
                'url' => $data['url'] ?? null,
                'data' => json_encode($data),
                'channel' => 'push',
                'status' => $status,
                'fcm_response' => $fcmResponse ? json_encode($fcmResponse) : null,
                'error_message' => $errorMessage,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->table('notifications')->insert($notificationData);
            self::writeLog("[FCM] Notification saved to history (ID: " . $db->insertID() . ")");
        } catch (\Exception $e) {
            self::writeLog("[FCM] ERROR: Failed to save notification history: " . $e->getMessage());
        }
    }

    public static function sendToMultipleUsers(array $userIds, $title, $body, $data = [])
    {
        $success = true;
        foreach ($userIds as $userId) {
            if (!self::sendToUser($userId, $title, $body, $data)) {
                $success = false;
            }
        }
        return $success;
    }

    public static function sendToAdmins($title, $body, $data = [])
    {
        $db = \Config\Database::connect();

        $admins = $db->table('users')
            ->select('id')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        if (empty($admins)) {
            self::writeLog("[FCM] WARNING: No admins found");
            return false;
        }

        $adminIds = array_column($admins, 'id');
        return self::sendToMultipleUsers($adminIds, $title, $body, $data);
    }

    public static function sendToCampaignOwner($campaignId, $title, $body, $data = [])
    {
        $db = \Config\Database::connect();

        $campaign = $db->table('campaigns')
            ->select('user_id')
            ->where('id', $campaignId)
            ->get()
            ->getRowArray();

        if (!$campaign) {
            self::writeLog("[FCM] WARNING: Campaign {$campaignId} not found");
            return false;
        }

        $data['campaign_id'] = $campaignId;
        return self::sendToUser($campaign['user_id'], $title, $body, $data);
    }

    public static function sendToDonor($donationId, $title, $body, $data = [])
    {
        $db = \Config\Database::connect();

        $donation = $db->table('donations')
            ->select('user_id')
            ->where('id', $donationId)
            ->get()
            ->getRowArray();

        if (!$donation || empty($donation['user_id'])) {
            self::writeLog("[FCM] WARNING: Donation {$donationId} has no registered user");
            return false;
        }

        $data['donation_id'] = $donationId;
        return self::sendToUser($donation['user_id'], $title, $body, $data);
    }
}

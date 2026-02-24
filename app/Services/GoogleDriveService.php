<?php

namespace App\Services;

use Config\Backup;

/**
 * Google Drive Service - DoarFazBem
 * Integração com Google Drive API v3 para upload de backups
 */
class GoogleDriveService
{
    protected Backup $config;
    protected ?array $credentials = null;
    protected ?array $token = null;
    protected string $accessToken = '';

    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';
    private const API_URL = 'https://www.googleapis.com/drive/v3/files';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    public function __construct()
    {
        $this->config = config('Backup');
        $this->loadCredentials();
        $this->loadToken();
    }

    /**
     * Carrega credenciais do arquivo JSON
     */
    protected function loadCredentials(): void
    {
        $path = $this->config->googleCredentialsPath;

        if (!file_exists($path)) {
            throw new \Exception('Arquivo de credenciais Google Drive não encontrado: ' . $path);
        }

        $this->credentials = json_decode(file_get_contents($path), true);

        if (!$this->credentials) {
            throw new \Exception('Credenciais Google Drive inválidas');
        }
    }

    /**
     * Carrega token de acesso salvo
     */
    protected function loadToken(): void
    {
        $path = $this->config->googleTokenPath;

        if (file_exists($path)) {
            $this->token = json_decode(file_get_contents($path), true);

            if ($this->token && isset($this->token['access_token'])) {
                // Verificar se token expirou
                if ($this->isTokenExpired()) {
                    $this->refreshToken();
                } else {
                    $this->accessToken = $this->token['access_token'];
                }
            }
        }
    }

    /**
     * Verifica se o token expirou
     */
    protected function isTokenExpired(): bool
    {
        if (!isset($this->token['created'], $this->token['expires_in'])) {
            return true;
        }

        $expiresAt = $this->token['created'] + $this->token['expires_in'] - 60; // 60s de margem
        return time() >= $expiresAt;
    }

    /**
     * Renova o token de acesso usando refresh token
     */
    protected function refreshToken(): void
    {
        if (!isset($this->token['refresh_token'])) {
            throw new \Exception('Refresh token não disponível. Reautorize a aplicação.');
        }

        $clientId = $this->credentials['installed']['client_id'] ?? $this->credentials['web']['client_id'] ?? null;
        $clientSecret = $this->credentials['installed']['client_secret'] ?? $this->credentials['web']['client_secret'] ?? null;

        $response = $this->httpRequest(self::TOKEN_URL, 'POST', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $this->token['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        if (isset($response['access_token'])) {
            $this->token['access_token'] = $response['access_token'];
            $this->token['created'] = time();

            if (isset($response['expires_in'])) {
                $this->token['expires_in'] = $response['expires_in'];
            }

            $this->accessToken = $response['access_token'];
            $this->saveToken();
        } else {
            throw new \Exception('Falha ao renovar token: ' . json_encode($response));
        }
    }

    /**
     * Salva token no arquivo
     */
    protected function saveToken(): void
    {
        file_put_contents(
            $this->config->googleTokenPath,
            json_encode($this->token, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Retorna o redirect URI para OAuth
     * Usa a URL base do sistema
     */
    public function getRedirectUri(): string
    {
        // Usar a URL base configurada no sistema
        $baseUrl = rtrim(env('app.baseURL', 'https://doarfazbem.ai'), '/');
        return $baseUrl . '/oauth-callback.php';
    }

    /**
     * Gera URL de autorização para obter o código inicial
     */
    public function getAuthUrl(): string
    {
        $clientId = $this->credentials['installed']['client_id'] ?? $this->credentials['web']['client_id'] ?? null;

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Troca código de autorização por tokens
     */
    public function exchangeCodeForToken(string $code): array
    {
        $clientId = $this->credentials['installed']['client_id'] ?? $this->credentials['web']['client_id'] ?? null;
        $clientSecret = $this->credentials['installed']['client_secret'] ?? $this->credentials['web']['client_secret'] ?? null;

        $response = $this->httpRequest(self::TOKEN_URL, 'POST', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
        ]);

        if (isset($response['access_token'])) {
            $this->token = $response;
            $this->token['created'] = time();
            $this->accessToken = $response['access_token'];
            $this->saveToken();

            return ['success' => true, 'message' => 'Token salvo com sucesso!'];
        }

        return ['success' => false, 'error' => $response['error_description'] ?? 'Erro desconhecido'];
    }

    /**
     * Verifica se está autenticado
     */
    public function isAuthenticated(): bool
    {
        return !empty($this->accessToken);
    }

    /**
     * Upload de arquivo para o Google Drive
     */
    public function uploadFile(string $filePath, string $fileName, string $folderId = ''): string
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Google Drive não autenticado');
        }

        if (!file_exists($filePath)) {
            throw new \Exception('Arquivo não encontrado: ' . $filePath);
        }

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Para arquivos pequenos (< 5MB), upload simples
        if ($fileSize < 5 * 1024 * 1024) {
            return $this->simpleUpload($filePath, $fileName, $mimeType, $folderId);
        }

        // Para arquivos grandes, upload resumable
        return $this->resumableUpload($filePath, $fileName, $mimeType, $folderId);
    }

    /**
     * Upload simples para arquivos pequenos
     */
    protected function simpleUpload(string $filePath, string $fileName, string $mimeType, string $folderId): string
    {
        $metadata = ['name' => $fileName];

        if (!empty($folderId)) {
            $metadata['parents'] = [$folderId];
        }

        $boundary = '===boundary===';
        $delimiter = "--{$boundary}";
        $closeDelimiter = "--{$boundary}--";

        $body = $delimiter . "\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= $delimiter . "\r\n";
        $body .= "Content-Type: {$mimeType}\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= base64_encode(file_get_contents($filePath)) . "\r\n";
        $body .= $closeDelimiter;

        $response = $this->httpRequest(
            self::UPLOAD_URL . '?uploadType=multipart',
            'POST',
            $body,
            [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: multipart/related; boundary=' . $boundary,
            ],
            false
        );

        if (!isset($response['id'])) {
            throw new \Exception('Falha no upload: ' . json_encode($response));
        }

        return $response['id'];
    }

    /**
     * Upload resumable para arquivos grandes
     */
    protected function resumableUpload(string $filePath, string $fileName, string $mimeType, string $folderId): string
    {
        // 1. Iniciar sessão de upload
        $metadata = ['name' => $fileName];

        if (!empty($folderId)) {
            $metadata['parents'] = [$folderId];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::UPLOAD_URL . '?uploadType=resumable',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($metadata),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json; charset=UTF-8',
                'X-Upload-Content-Type: ' . $mimeType,
                'X-Upload-Content-Length: ' . filesize($filePath),
            ],
        ]);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        curl_close($ch);

        // Extrair URL de upload
        preg_match('/location: (.*)/i', $headers, $matches);
        $uploadUrl = trim($matches[1] ?? '');

        if (empty($uploadUrl)) {
            throw new \Exception('Falha ao iniciar upload resumable');
        }

        // 2. Upload do conteúdo em chunks
        $fileSize = filesize($filePath);
        $chunkSize = $this->config->chunkSize;
        $handle = fopen($filePath, 'rb');
        $offset = 0;

        while ($offset < $fileSize) {
            $chunk = fread($handle, $chunkSize);
            $chunkLength = strlen($chunk);
            $endByte = $offset + $chunkLength - 1;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $uploadUrl,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $chunk,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: ' . $mimeType,
                    'Content-Length: ' . $chunkLength,
                    "Content-Range: bytes {$offset}-{$endByte}/{$fileSize}",
                ],
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // 308 = continue, 200/201 = complete
            if ($httpCode === 200 || $httpCode === 201) {
                $response = json_decode($result, true);
                fclose($handle);

                if (!isset($response['id'])) {
                    throw new \Exception('Upload concluído mas sem ID');
                }

                return $response['id'];
            }

            if ($httpCode !== 308) {
                fclose($handle);
                throw new \Exception("Erro no upload: HTTP {$httpCode}");
            }

            $offset += $chunkLength;
        }

        fclose($handle);
        throw new \Exception('Upload incompleto');
    }

    /**
     * Lista arquivos de backup no Google Drive
     */
    public function listBackupFiles(string $prefix, string $folderId = ''): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        $query = "name contains '{$prefix}' and trashed = false";

        if (!empty($folderId)) {
            $query .= " and '{$folderId}' in parents";
        }

        $params = [
            'q' => $query,
            'fields' => 'files(id, name, size, createdTime, modifiedTime)',
            'orderBy' => 'createdTime desc',
            'pageSize' => 100,
        ];

        $response = $this->httpRequest(
            self::API_URL . '?' . http_build_query($params),
            'GET',
            null,
            ['Authorization: Bearer ' . $this->accessToken]
        );

        return $response['files'] ?? [];
    }

    /**
     * Deleta arquivo do Google Drive
     */
    public function deleteFile(string $fileId): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL . '/' . $fileId,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 204 || $httpCode === 200;
    }

    /**
     * Download de arquivo do Google Drive
     */
    public function downloadFile(string $fileId, string $destinationPath): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $url = self::API_URL . '/' . $fileId . '?alt=media';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return file_put_contents($destinationPath, $content) !== false;
        }

        return false;
    }

    /**
     * Requisição HTTP genérica
     */
    protected function httpRequest(string $url, string $method, $data = null, array $headers = [], bool $isForm = true): array
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;

            if ($data !== null) {
                if ($isForm && is_array($data)) {
                    $options[CURLOPT_POSTFIELDS] = http_build_query($data);
                } else {
                    $options[CURLOPT_POSTFIELDS] = $data;
                }
            }
        }

        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error];
        }

        return json_decode($response, true) ?? ['raw' => $response];
    }
}

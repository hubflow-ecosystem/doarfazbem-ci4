<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuração centralizada das APIs do Google
 *
 * IMPORTANTE: Em produção, mova todas as chaves para .env
 */
class Google extends BaseConfig
{
    /**
     * Google Analytics
     */
    public string $analyticsPropertyId = '373648574';
    public string $analyticsMeasurementId = 'G-9SWBDMBQL6';
    public string $analyticsTagId = 'GT-P8452X3';

    /**
     * Google Maps
     */
    public string $mapsApiKey = 'AIzaSyBH6HdIM_l7ZhDWT0WGgm7ZbMCEbc1UUSs';

    /**
     * Google APIs (Geral - TODAS exceto Maps)
     */
    public string $generalApiKey = 'AIzaSyAB7TN7eR0ur7p2XvprkwA9RVGHuUf0B-k';

    /**
     * Google Search Console
     */
    public string $searchConsoleDomain = 'sc-domain:doarfazbem.com.br';
    public string $searchConsoleUrl = 'https://doarfazbem.com.br';

    /**
     * Service Account (Para APIs server-side)
     */
    public string $serviceAccountEmail = 'doar-faz-bem@doarfazbem.iam.gserviceaccount.com';
    public string $serviceAccountKeyFile = WRITEPATH . '../doarfazbem-f0015146da01.json';
    public string $serviceAccountKeyId = 'f0015146da01ff3fe0cb3f377d41c21fa2f85602';

    /**
     * reCAPTCHA v3
     * Configure as chaves no .env:
     * RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY, RECAPTCHA_SCORE_THRESHOLD
     */
    public string $recaptchaSiteKey = '';
    public string $recaptchaSecretKey = '';
    public string $recaptchaProjectId = 'doarfazbem';
    public float $recaptchaScoreThreshold = 0.5;

    public function __construct()
    {
        parent::__construct();

        // Carregar reCAPTCHA do .env
        $this->recaptchaSiteKey = env('RECAPTCHA_SITE_KEY', '');
        $this->recaptchaSecretKey = env('RECAPTCHA_SECRET_KEY', '');
        $this->recaptchaScoreThreshold = (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5);

        // Carregar API Key geral do .env
        $generalKey = env('GOOGLE_API_KEY', '');
        if (!empty($generalKey)) {
            $this->generalApiKey = $generalKey;
        }
    }

    /**
     * Google Drive
     */
    public string $driveFolderId = ''; // ID da pasta de backups (criar manualmente)
    public array $driveScopes = [
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/drive.appdata'
    ];

    /**
     * Google Sheets
     */
    public string $sheetsSpreadsheetId = ''; // ID da planilha de relatórios (criar manualmente)
    public array $sheetsScopes = [
        'https://www.googleapis.com/auth/spreadsheets'
    ];

    /**
     * Gmail API
     */
    public array $gmailScopes = [
        'https://www.googleapis.com/auth/gmail.send'
    ];

    /**
     * YouTube Data API
     */
    public string $youtubeChannelId = ''; // Caso tenha canal do DoarFazBem

    /**
     * APIs Habilitadas
     */
    public array $enabledApis = [
        'apps_script' => true,
        'gmail' => true,
        'calendar' => false, // Desabilitado por enquanto
        'docs' => false,
        'drive' => true,
        'sheets' => true,
        'recaptcha_enterprise' => true,
        'forms' => false,
        'local_services' => false,
        'google_ads' => false, // Ativar quando houver budget
        'analytics_admin' => true,
        'analytics_data' => true,
        'youtube' => false,
        'search_console' => true,
        'maps' => true
    ];

    /**
     * Rate Limits (requisições por minuto)
     */
    public array $rateLimits = [
        'search_console' => 200, // 200 req/dia
        'sheets' => 60,
        'drive' => 1000,
        'gmail' => 100, // 500/dia
        'analytics' => 10000,
        'maps' => 50000
    ];

    /**
     * Verifica se arquivo de credenciais existe
     */
    public function hasServiceAccountKey(): bool
    {
        return file_exists($this->serviceAccountKeyFile);
    }

    /**
     * Retorna credenciais da service account
     */
    public function getServiceAccountCredentials(): ?array
    {
        if (!$this->hasServiceAccountKey()) {
            return null;
        }

        return json_decode(file_get_contents($this->serviceAccountKeyFile), true);
    }
}

<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configurações do Sistema de Backup - DoarFazBem
 * Estilo UpdraftPlus Pro com integração Google Drive
 */
class Backup extends BaseConfig
{
    /**
     * Diretório temporário para backups locais
     */
    public string $tempPath = WRITEPATH . 'backups/';

    /**
     * Quantidade de backups locais a manter (rotação)
     */
    public int $keepLocalBackups = 3;

    /**
     * Quantidade de backups no Google Drive a manter
     */
    public int $keepRemoteBackups = 7;

    /**
     * Tamanho máximo de arquivo para upload em partes (5MB)
     */
    public int $chunkSize = 5 * 1024 * 1024;

    /**
     * Pastas a incluir no backup de arquivos
     */
    public array $includeFolders = [
        'app',
        'public/uploads',
        'public/assets',
        'writable/uploads',
    ];

    /**
     * Pastas/arquivos a excluir do backup
     */
    public array $excludePatterns = [
        'writable/cache/*',
        'writable/logs/*',
        'writable/session/*',
        'writable/debugbar/*',
        'writable/backups/*',
        'vendor/*',
        'node_modules/*',
        '.git/*',
        '*.log',
        '*.tmp',
    ];

    /**
     * Google Drive - ID da pasta de destino
     * Deixe vazio para usar a raiz do Drive
     */
    public string $googleDriveFolderId = '';

    /**
     * Google Drive - Caminho do arquivo de credenciais
     */
    public string $googleCredentialsPath = ROOTPATH . 'config/google-drive-credentials.json';

    /**
     * Google Drive - Caminho do token de acesso
     */
    public string $googleTokenPath = ROOTPATH . 'config/google-drive-token.json';

    /**
     * Email para notificações de backup
     */
    public string $notificationEmail = '';

    /**
     * Enviar notificação apenas em caso de erro?
     */
    public bool $notifyOnErrorOnly = false;

    /**
     * Prefixo para nomes dos arquivos de backup
     */
    public string $backupPrefix = 'doarfazbem';

    /**
     * Compressão do backup (zip, gzip)
     */
    public string $compression = 'zip';

    /**
     * Senha para criptografar backups (deixe vazio para não criptografar)
     */
    public string $encryptionPassword = '';

    /**
     * Tipos de backup disponíveis
     */
    public array $backupTypes = [
        'full'     => 'Backup completo (banco + arquivos)',
        'database' => 'Apenas banco de dados',
        'files'    => 'Apenas arquivos',
    ];

    /**
     * Agendamentos pré-definidos
     */
    public array $schedules = [
        'daily'   => 'Diário às 03:00',
        'weekly'  => 'Semanal (Domingo às 03:00)',
        'monthly' => 'Mensal (Dia 1 às 03:00)',
    ];
}

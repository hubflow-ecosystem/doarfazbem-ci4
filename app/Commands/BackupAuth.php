<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GoogleDriveService;

/**
 * Autorização do Google Drive para Backup
 *
 * Uso:
 *   php spark backup:auth           # Inicia processo de autorização
 *   php spark backup:auth <code>    # Completa autorização com código
 */
class BackupAuth extends BaseCommand
{
    protected $group = 'Backup';
    protected $name = 'backup:auth';
    protected $description = 'Autoriza integração com Google Drive';
    protected $usage = 'backup:auth [code]';
    protected $arguments = [
        'code' => 'Código de autorização do Google (opcional)',
    ];

    public function run(array $params)
    {
        CLI::write('');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::write('    AUTORIZAÇÃO GOOGLE DRIVE - BACKUP', 'green');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::newLine();

        $config = config('Backup');

        // Verificar se credenciais existem
        if (!file_exists($config->googleCredentialsPath)) {
            CLI::error('Arquivo de credenciais não encontrado!');
            CLI::newLine();
            CLI::write('Para configurar o Google Drive:', 'yellow');
            CLI::write('1. Acesse https://console.cloud.google.com/apis/credentials', 'white');
            CLI::write('2. Crie um OAuth 2.0 Client ID (tipo Desktop)', 'white');
            CLI::write('3. Baixe o JSON e salve em:', 'white');
            CLI::write('   ' . $config->googleCredentialsPath, 'cyan');
            CLI::newLine();
            return;
        }

        try {
            $googleDrive = new GoogleDriveService();

            // Verificar se já está autenticado
            if ($googleDrive->isAuthenticated()) {
                CLI::write('✅ Google Drive já está autenticado!', 'green');
                CLI::newLine();
                CLI::write('Para reautenticar, delete o arquivo:', 'yellow');
                CLI::write('   ' . $config->googleTokenPath, 'cyan');
                CLI::newLine();
                return;
            }

            // Se código foi fornecido, trocar por token
            $code = $params[0] ?? null;

            if ($code) {
                CLI::write('Trocando código por token...', 'yellow');

                $result = $googleDrive->exchangeCodeForToken($code);

                if ($result['success']) {
                    CLI::write('✅ Autorização concluída com sucesso!', 'green');
                    CLI::newLine();
                    CLI::write('Você já pode executar backups com:', 'cyan');
                    CLI::write('   php spark backup:run', 'white');
                } else {
                    CLI::error('Falha na autorização: ' . ($result['error'] ?? 'Erro desconhecido'));
                }

                CLI::newLine();
                return;
            }

            // Gerar URL de autorização
            $authUrl = $googleDrive->getAuthUrl();

            CLI::write('Para autorizar o acesso ao Google Drive:', 'yellow');
            CLI::newLine();
            CLI::write('1. Acesse a URL abaixo no navegador:', 'white');
            CLI::newLine();
            CLI::write($authUrl, 'cyan');
            CLI::newLine();
            CLI::write('2. Faça login e autorize o acesso', 'white');
            CLI::write('3. Copie o código de autorização', 'white');
            CLI::write('4. Execute novamente com o código:', 'white');
            CLI::newLine();
            CLI::write('   php spark backup:auth SEU_CODIGO_AQUI', 'green');
            CLI::newLine();

        } catch (\Exception $e) {
            CLI::error('Erro: ' . $e->getMessage());
        }

        CLI::newLine();
    }
}

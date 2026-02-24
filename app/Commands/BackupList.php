<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\BackupService;
use App\Services\GoogleDriveService;

/**
 * Lista backups disponíveis
 *
 * Uso:
 *   php spark backup:list
 *   php spark backup:list --remote   # Incluir backups do Google Drive
 */
class BackupList extends BaseCommand
{
    protected $group = 'Backup';
    protected $name = 'backup:list';
    protected $description = 'Lista backups disponíveis';
    protected $usage = 'backup:list [--remote]';
    protected $options = [
        '--remote' => 'Incluir backups do Google Drive',
    ];

    public function run(array $params)
    {
        CLI::write('');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::write('        BACKUPS DISPONÍVEIS', 'green');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::newLine();

        $backupService = new BackupService();

        // Backups locais
        CLI::write('📁 BACKUPS LOCAIS:', 'yellow');
        CLI::write('─────────────────────────────────────────', 'white');

        $localBackups = $backupService->listBackups();

        if (empty($localBackups)) {
            CLI::write('  Nenhum backup local encontrado', 'gray');
        } else {
            foreach ($localBackups as $backup) {
                $size = number_format($backup['size'] / 1024 / 1024, 2);
                $type = $backup['type'] ?? 'Desconhecido';
                CLI::write("  • {$backup['name']}", 'cyan');
                CLI::write("    Tipo: {$type} | Data: {$backup['created_at']} | Tamanho: {$size} MB", 'gray');
            }
        }

        CLI::newLine();

        // Backups remotos (se solicitado)
        $includeRemote = CLI::getOption('remote') !== null;

        if ($includeRemote) {
            CLI::write('☁️  BACKUPS NO GOOGLE DRIVE:', 'yellow');
            CLI::write('─────────────────────────────────────────', 'white');

            try {
                $googleDrive = new GoogleDriveService();

                if (!$googleDrive->isAuthenticated()) {
                    CLI::write('  Google Drive não autenticado', 'red');
                    CLI::write('  Execute: php spark backup:auth', 'gray');
                } else {
                    $config = config('Backup');
                    $remoteBackups = $googleDrive->listBackupFiles(
                        $config->backupPrefix,
                        $config->googleDriveFolderId
                    );

                    if (empty($remoteBackups)) {
                        CLI::write('  Nenhum backup remoto encontrado', 'gray');
                    } else {
                        foreach ($remoteBackups as $backup) {
                            $size = number_format(($backup['size'] ?? 0) / 1024 / 1024, 2);
                            $date = date('Y-m-d H:i:s', strtotime($backup['createdTime']));
                            CLI::write("  • {$backup['name']}", 'cyan');
                            CLI::write("    Data: {$date} | Tamanho: {$size} MB | ID: {$backup['id']}", 'gray');
                        }
                    }
                }
            } catch (\Exception $e) {
                CLI::write('  Erro ao acessar Google Drive: ' . $e->getMessage(), 'red');
            }

            CLI::newLine();
        } else {
            CLI::write('💡 Use --remote para ver backups do Google Drive', 'gray');
            CLI::newLine();
        }
    }
}

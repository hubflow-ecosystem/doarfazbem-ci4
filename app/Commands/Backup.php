<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\BackupService;
use App\Services\GoogleDriveService;

/**
 * Comando de Backup - DoarFazBem
 *
 * Uso:
 *   php spark backup:run              # Backup completo
 *   php spark backup:run --type=database   # Apenas banco
 *   php spark backup:run --type=files      # Apenas arquivos
 *   php spark backup:list             # Listar backups
 *   php spark backup:auth             # Autorizar Google Drive
 *   php spark backup:test             # Testar configuração
 *
 * Agendamento via Cron:
 *   0 3 * * * cd /path/to/project && php spark backup:run >> /var/log/backup.log 2>&1
 */
class Backup extends BaseCommand
{
    protected $group = 'Backup';
    protected $name = 'backup:run';
    protected $description = 'Executa backup do sistema DoarFazBem';
    protected $usage = 'backup:run [--type=<type>]';
    protected $arguments = [];
    protected $options = [
        '--type' => 'Tipo de backup: full, database, files (padrão: full)',
    ];

    public function run(array $params)
    {
        $type = $params['type'] ?? CLI::getOption('type') ?? 'full';

        CLI::write('');
        CLI::write('╔══════════════════════════════════════════╗', 'green');
        CLI::write('║     BACKUP DOARFAZBEM - ' . strtoupper($type) . str_repeat(' ', 16 - strlen($type)) . '║', 'green');
        CLI::write('╚══════════════════════════════════════════╝', 'green');
        CLI::write('');
        CLI::write('Iniciado em: ' . date('d/m/Y H:i:s'), 'cyan');
        CLI::newLine();

        $backupService = new BackupService();

        switch ($type) {
            case 'full':
                $result = $backupService->runFullBackup();
                break;

            case 'database':
                $result = $this->runDatabaseOnly($backupService);
                break;

            case 'files':
                $result = $this->runFilesOnly($backupService);
                break;

            default:
                CLI::error("Tipo de backup inválido: {$type}");
                CLI::write('Tipos válidos: full, database, files');
                return;
        }

        $this->displayResult($result);
    }

    /**
     * Backup apenas do banco de dados
     */
    protected function runDatabaseOnly(BackupService $backupService): array
    {
        $result = [
            'success' => false,
            'type' => 'database',
            'started_at' => date('Y-m-d H:i:s'),
        ];

        CLI::write('Executando backup do banco de dados...', 'yellow');

        $dbResult = $backupService->backupDatabase();
        $result['database'] = $dbResult;
        $result['success'] = $dbResult['success'];

        if ($dbResult['success'] && $backupService->isGoogleDriveConfigured()) {
            CLI::write('Enviando para Google Drive...', 'yellow');
            $result['upload'] = $backupService->uploadToGoogleDrive($dbResult['path']);
        }

        $result['finished_at'] = date('Y-m-d H:i:s');
        $result['log'] = $backupService->getLog();

        return $result;
    }

    /**
     * Backup apenas dos arquivos
     */
    protected function runFilesOnly(BackupService $backupService): array
    {
        $result = [
            'success' => false,
            'type' => 'files',
            'started_at' => date('Y-m-d H:i:s'),
        ];

        CLI::write('Executando backup dos arquivos...', 'yellow');

        $filesResult = $backupService->backupFiles();
        $result['files'] = $filesResult;
        $result['success'] = $filesResult['success'];

        if ($filesResult['success'] && $backupService->isGoogleDriveConfigured()) {
            CLI::write('Enviando para Google Drive...', 'yellow');
            $result['upload'] = $backupService->uploadToGoogleDrive($filesResult['path']);
        }

        $result['finished_at'] = date('Y-m-d H:i:s');
        $result['log'] = $backupService->getLog();

        return $result;
    }

    /**
     * Exibe resultado do backup
     */
    protected function displayResult(array $result): void
    {
        CLI::newLine();
        CLI::write('═══════════════════════════════════════════', 'white');

        if ($result['success']) {
            CLI::write('✅ BACKUP CONCLUÍDO COM SUCESSO!', 'green');
        } else {
            CLI::write('❌ BACKUP FALHOU!', 'red');
        }

        CLI::write('═══════════════════════════════════════════', 'white');
        CLI::newLine();

        // Detalhes do banco
        if (isset($result['database'])) {
            $db = $result['database'];
            $status = $db['success'] ? '✅' : '❌';
            $size = isset($db['size']) ? number_format($db['size'] / 1024 / 1024, 2) . ' MB' : 'N/A';
            CLI::write("{$status} Banco de dados: {$size} ({$db['tables']} tabelas)", $db['success'] ? 'green' : 'red');
        }

        // Detalhes dos arquivos
        if (isset($result['files'])) {
            $files = $result['files'];
            $status = $files['success'] ? '✅' : '❌';
            $size = isset($files['size']) ? number_format($files['size'] / 1024 / 1024, 2) . ' MB' : 'N/A';
            CLI::write("{$status} Arquivos: {$size} ({$files['files_count']} arquivos)", $files['success'] ? 'green' : 'red');
        }

        // Detalhes do upload
        if (isset($result['upload'])) {
            $upload = $result['upload'];
            $status = $upload['success'] ? '✅' : '⚠️';
            $msg = $upload['success'] ? 'Enviado para Google Drive' : ($upload['message'] ?? $upload['error'] ?? 'Não enviado');
            CLI::write("{$status} Google Drive: {$msg}", $upload['success'] ? 'green' : 'yellow');
        }

        // Pacote final
        if (isset($result['package_path'])) {
            $size = number_format($result['package_size'] / 1024 / 1024, 2);
            CLI::write("📦 Pacote: " . basename($result['package_path']) . " ({$size} MB)", 'cyan');
        }

        // Erros
        if (!empty($result['errors'])) {
            CLI::newLine();
            CLI::write('Erros encontrados:', 'red');
            foreach ($result['errors'] as $error) {
                CLI::write("  • {$error}", 'red');
            }
        }

        CLI::newLine();
        CLI::write('Finalizado em: ' . ($result['finished_at'] ?? date('Y-m-d H:i:s')), 'cyan');
        CLI::newLine();
    }
}

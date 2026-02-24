<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GoogleDriveService;

/**
 * Testa configuração do sistema de backup
 *
 * Uso:
 *   php spark backup:test
 */
class BackupTest extends BaseCommand
{
    protected $group = 'Backup';
    protected $name = 'backup:test';
    protected $description = 'Testa configuração do sistema de backup';
    protected $usage = 'backup:test';

    public function run(array $params)
    {
        CLI::write('');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::write('      TESTE DE CONFIGURAÇÃO - BACKUP', 'green');
        CLI::write('═══════════════════════════════════════════', 'green');
        CLI::newLine();

        $allOk = true;
        $config = config('Backup');

        // 1. Verificar diretório de backup
        CLI::write('1. Diretório de backup:', 'yellow');
        if (is_dir($config->tempPath) && is_writable($config->tempPath)) {
            CLI::write('   ✅ ' . $config->tempPath, 'green');
        } else {
            // Tentar criar
            if (!is_dir($config->tempPath)) {
                mkdir($config->tempPath, 0755, true);
            }

            if (is_writable($config->tempPath)) {
                CLI::write('   ✅ ' . $config->tempPath . ' (criado)', 'green');
            } else {
                CLI::write('   ❌ Diretório não gravável: ' . $config->tempPath, 'red');
                $allOk = false;
            }
        }

        // 2. Verificar conexão com banco
        CLI::write('2. Conexão com banco de dados:', 'yellow');
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();
            CLI::write('   ✅ Conectado (' . count($tables) . ' tabelas)', 'green');
        } catch (\Exception $e) {
            CLI::write('   ❌ Erro: ' . $e->getMessage(), 'red');
            $allOk = false;
        }

        // 3. Verificar ZipArchive ou PharData
        CLI::write('3. Compressão de arquivos:', 'yellow');
        if (class_exists('ZipArchive')) {
            CLI::write('   ✅ ZipArchive disponível', 'green');
        } elseif (class_exists('PharData')) {
            CLI::write('   ✅ PharData disponível (alternativa ao ZIP)', 'green');
        } else {
            CLI::write('   ⚠️ ZIP/Phar não disponível (backup apenas do banco)', 'yellow');
        }

        // 4. Verificar cURL
        CLI::write('4. Extensão cURL:', 'yellow');
        if (function_exists('curl_init')) {
            CLI::write('   ✅ Disponível', 'green');
        } else {
            CLI::write('   ❌ Não disponível (necessária para Google Drive)', 'red');
            $allOk = false;
        }

        // 5. Verificar credenciais Google Drive
        CLI::write('5. Credenciais Google Drive:', 'yellow');
        if (file_exists($config->googleCredentialsPath)) {
            CLI::write('   ✅ Arquivo encontrado', 'green');

            // Verificar token
            if (file_exists($config->googleTokenPath)) {
                try {
                    $googleDrive = new GoogleDriveService();
                    if ($googleDrive->isAuthenticated()) {
                        CLI::write('   ✅ Token válido', 'green');
                    } else {
                        CLI::write('   ⚠️ Token expirado ou inválido', 'yellow');
                        CLI::write('      Execute: php spark backup:auth', 'gray');
                    }
                } catch (\Exception $e) {
                    CLI::write('   ⚠️ ' . $e->getMessage(), 'yellow');
                }
            } else {
                CLI::write('   ⚠️ Token não encontrado', 'yellow');
                CLI::write('      Execute: php spark backup:auth', 'gray');
            }
        } else {
            CLI::write('   ⚠️ Não configurado (backup apenas local)', 'yellow');
            CLI::write('      Arquivo esperado: ' . $config->googleCredentialsPath, 'gray');
        }

        // 6. Verificar pastas a incluir no backup
        CLI::write('6. Pastas para backup:', 'yellow');
        foreach ($config->includeFolders as $folder) {
            $fullPath = ROOTPATH . $folder;
            if (is_dir($fullPath)) {
                CLI::write('   ✅ ' . $folder, 'green');
            } else {
                CLI::write('   ⚠️ ' . $folder . ' (não existe)', 'yellow');
            }
        }

        // 7. Verificar email de notificação
        CLI::write('7. Email de notificação:', 'yellow');
        if (!empty($config->notificationEmail)) {
            CLI::write('   ✅ ' . $config->notificationEmail, 'green');
        } else {
            CLI::write('   ⚠️ Não configurado', 'yellow');
            CLI::write('      Defina em Config/Backup.php', 'gray');
        }

        // 8. Verificar espaço em disco
        CLI::write('8. Espaço em disco:', 'yellow');
        $freeSpace = disk_free_space($config->tempPath);
        $freeSpaceGB = number_format($freeSpace / 1024 / 1024 / 1024, 2);
        if ($freeSpace > 1024 * 1024 * 1024) { // > 1GB
            CLI::write('   ✅ ' . $freeSpaceGB . ' GB disponível', 'green');
        } else {
            CLI::write('   ⚠️ Apenas ' . $freeSpaceGB . ' GB disponível', 'yellow');
        }

        // Resultado final
        CLI::newLine();
        CLI::write('═══════════════════════════════════════════', 'white');

        if ($allOk) {
            CLI::write('✅ Sistema de backup configurado corretamente!', 'green');
            CLI::newLine();
            CLI::write('Comandos disponíveis:', 'cyan');
            CLI::write('  php spark backup:run           # Backup completo', 'white');
            CLI::write('  php spark backup:run --type=database  # Apenas banco', 'white');
            CLI::write('  php spark backup:run --type=files     # Apenas arquivos', 'white');
            CLI::write('  php spark backup:list          # Listar backups', 'white');
            CLI::write('  php spark backup:auth          # Autorizar Google Drive', 'white');
        } else {
            CLI::write('❌ Alguns problemas precisam ser resolvidos', 'red');
        }

        CLI::newLine();
        CLI::write('Agendamento via Cron (diário às 3h):', 'cyan');
        CLI::write('  0 3 * * * cd ' . ROOTPATH . ' && php spark backup:run', 'white');
        CLI::newLine();
    }
}

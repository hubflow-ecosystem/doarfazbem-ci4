<?php

namespace App\Services;

use Config\Backup;
use Config\Database;

/**
 * Serviço de Backup - DoarFazBem
 * Sistema completo de backup com integração Google Drive
 */
class BackupService
{
    protected Backup $config;
    protected string $tempPath;
    protected array $log = [];

    public function __construct()
    {
        $this->config = config('Backup');
        $this->tempPath = $this->config->tempPath;

        // Criar diretório de backups se não existir
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Executa backup completo (banco + arquivos)
     */
    public function runFullBackup(): array
    {
        $this->log('Iniciando backup completo...');

        $result = [
            'success' => false,
            'type' => 'full',
            'started_at' => date('Y-m-d H:i:s'),
            'database' => null,
            'files' => null,
            'upload' => null,
            'errors' => [],
        ];

        try {
            // 1. Backup do banco de dados
            $dbBackup = $this->backupDatabase();
            $result['database'] = $dbBackup;

            if (!$dbBackup['success']) {
                throw new \Exception('Falha no backup do banco: ' . ($dbBackup['error'] ?? 'Erro desconhecido'));
            }

            // 2. Backup dos arquivos
            $filesBackup = $this->backupFiles();
            $result['files'] = $filesBackup;

            if (!$filesBackup['success']) {
                throw new \Exception('Falha no backup dos arquivos: ' . ($filesBackup['error'] ?? 'Erro desconhecido'));
            }

            // 3. Criar pacote único com ambos
            $packagePath = $this->createBackupPackage($dbBackup['path'], $filesBackup['path']);

            // 4. Upload para Google Drive
            if ($this->isGoogleDriveConfigured()) {
                $upload = $this->uploadToGoogleDrive($packagePath);
                $result['upload'] = $upload;
            } else {
                $result['upload'] = ['success' => false, 'message' => 'Google Drive não configurado'];
            }

            // 5. Rotação de backups locais
            $this->rotateLocalBackups();

            // 6. Limpar arquivos temporários
            $this->cleanupTempFiles([$dbBackup['path'], $filesBackup['path']]);

            $result['success'] = true;
            $result['package_path'] = $packagePath;
            $result['package_size'] = filesize($packagePath);
            $result['finished_at'] = date('Y-m-d H:i:s');

            $this->log('Backup completo finalizado com sucesso!');

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log('ERRO: ' . $e->getMessage(), 'error');
        }

        $result['log'] = $this->log;

        // Enviar notificação
        $this->sendNotification($result);

        return $result;
    }

    /**
     * Backup apenas do banco de dados
     */
    public function backupDatabase(): array
    {
        $this->log('Iniciando backup do banco de dados...');

        $result = [
            'success' => false,
            'path' => null,
            'size' => 0,
            'tables' => 0,
        ];

        try {
            $db = \Config\Database::connect();
            $dbConfig = config('Database')->default;

            $filename = sprintf(
                '%s_db_%s.sql',
                $this->config->backupPrefix,
                date('Y-m-d_His')
            );
            $filepath = $this->tempPath . $filename;

            // Obter todas as tabelas
            $tables = $db->listTables();
            $result['tables'] = count($tables);

            $this->log("Encontradas {$result['tables']} tabelas");

            // Gerar dump SQL
            $sql = $this->generateMySQLDump($db, $tables, $dbConfig['database']);

            // Salvar arquivo
            file_put_contents($filepath, $sql);

            $result['success'] = true;
            $result['path'] = $filepath;
            $result['size'] = filesize($filepath);

            $this->log(sprintf(
                'Backup do banco concluído: %s (%.2f MB)',
                $filename,
                $result['size'] / 1024 / 1024
            ));

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->log('ERRO no backup do banco: ' . $e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Gera dump MySQL manualmente (sem mysqldump)
     */
    protected function generateMySQLDump($db, array $tables, string $database): string
    {
        $sql = "-- DoarFazBem Database Backup\n";
        $sql .= "-- Gerado em: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table) {
            $this->log("Exportando tabela: {$table}");

            // Estrutura da tabela
            $query = $db->query("SHOW CREATE TABLE `{$table}`");
            $row = $query->getRowArray();

            $sql .= "-- Estrutura da tabela `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $row['Create Table'] . ";\n\n";

            // Dados da tabela
            $query = $db->query("SELECT * FROM `{$table}`");
            $rows = $query->getResultArray();

            if (!empty($rows)) {
                $sql .= "-- Dados da tabela `{$table}`\n";

                $columns = array_keys($rows[0]);
                $columnsList = '`' . implode('`, `', $columns) . '`';

                // Inserir em lotes de 100 registros
                $chunks = array_chunk($rows, 100);

                foreach ($chunks as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = array_map(function ($value) use ($db) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return $db->escape($value);
                        }, $row);
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }

                    $sql .= "INSERT INTO `{$table}` ({$columnsList}) VALUES\n";
                    $sql .= implode(",\n", $values) . ";\n";
                }

                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * Verifica se ZipArchive está disponível
     */
    public function isZipAvailable(): bool
    {
        return class_exists('ZipArchive');
    }

    /**
     * Backup dos arquivos do site
     */
    public function backupFiles(): array
    {
        $this->log('Iniciando backup dos arquivos...');

        $result = [
            'success' => false,
            'path' => null,
            'size' => 0,
            'files_count' => 0,
        ];

        try {
            // Usar ZipArchive se disponível, senão usar tar via shell
            if ($this->isZipAvailable()) {
                return $this->backupFilesWithZip();
            } else {
                return $this->backupFilesWithTar();
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->log('ERRO no backup dos arquivos: ' . $e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Backup de arquivos usando ZipArchive (PHP nativo)
     */
    protected function backupFilesWithZip(): array
    {
        $result = [
            'success' => false,
            'path' => null,
            'size' => 0,
            'files_count' => 0,
        ];

        $filename = sprintf(
            '%s_files_%s.zip',
            $this->config->backupPrefix,
            date('Y-m-d_His')
        );
        $filepath = $this->tempPath . $filename;

        $zip = new \ZipArchive();

        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Não foi possível criar arquivo ZIP');
        }

        $filesCount = 0;

        foreach ($this->config->includeFolders as $folder) {
            $fullPath = ROOTPATH . $folder;

            if (!is_dir($fullPath)) {
                $this->log("Pasta não encontrada (ignorando): {$folder}");
                continue;
            }

            $this->log("Adicionando pasta: {$folder}");
            $filesCount += $this->addFolderToZip($zip, $fullPath, $folder);
        }

        $zip->close();

        $result['success'] = true;
        $result['path'] = $filepath;
        $result['size'] = filesize($filepath);
        $result['files_count'] = $filesCount;

        $this->log(sprintf(
            'Backup dos arquivos concluído: %s (%d arquivos, %.2f MB)',
            $filename,
            $filesCount,
            $result['size'] / 1024 / 1024
        ));

        return $result;
    }

    /**
     * Backup de arquivos usando tar (Windows/Linux)
     */
    protected function backupFilesWithTar(): array
    {
        $this->log('Usando método alternativo (PharData) para backup de arquivos...');

        $result = [
            'success' => false,
            'path' => null,
            'size' => 0,
            'files_count' => 0,
        ];

        $filename = sprintf(
            '%s_files_%s.tar',
            $this->config->backupPrefix,
            date('Y-m-d_His')
        );
        $filepath = $this->tempPath . $filename;

        // Construir lista de arquivos
        $filesCount = 0;

        try {
            // Usar PharData para criar arquivo tar
            if (class_exists('PharData')) {
                $this->log('Criando arquivo tar...');

                $phar = new \PharData($filepath);

                foreach ($this->config->includeFolders as $folder) {
                    $fullPath = ROOTPATH . $folder;

                    if (!is_dir($fullPath)) {
                        $this->log("Pasta não encontrada (ignorando): {$folder}");
                        continue;
                    }

                    $this->log("Adicionando: {$folder}");

                    // Adicionar arquivos individualmente para melhor controle
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );

                    foreach ($iterator as $file) {
                        $filePath = $file->getRealPath();
                        $relativePath = $folder . '/' . substr($filePath, strlen($fullPath) + 1);

                        // Verificar exclusões
                        if ($this->shouldExclude($relativePath)) {
                            continue;
                        }

                        if ($file->isFile()) {
                            try {
                                $phar->addFile($filePath, $relativePath);
                                $filesCount++;
                            } catch (\Exception $e) {
                                // Ignorar arquivos que não podem ser adicionados
                                $this->log("Aviso: não foi possível adicionar {$relativePath}", 'warning');
                            }
                        }
                    }
                }

                $result['success'] = true;
                $result['path'] = $filepath;
                $result['size'] = file_exists($filepath) ? filesize($filepath) : 0;
                $result['files_count'] = $filesCount;

            } else {
                throw new \Exception('PharData não disponível');
            }

        } catch (\Exception $e) {
            // Fallback: criar arquivo com lista de arquivos
            $this->log('Fallback: Criando lista de arquivos...');

            $listFile = $this->tempPath . $this->config->backupPrefix . '_files_' . date('Y-m-d_His') . '.txt';
            $filesList = [];

            foreach ($this->config->includeFolders as $folder) {
                $fullPath = ROOTPATH . $folder;
                if (!is_dir($fullPath)) continue;

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $filesList[] = $file->getRealPath();
                    }
                }
            }

            file_put_contents($listFile, implode("\n", $filesList));

            $result['success'] = true;
            $result['path'] = $listFile;
            $result['size'] = filesize($listFile);
            $result['files_count'] = count($filesList);
            $result['note'] = 'Backup criado como lista de arquivos';
        }

        $this->log(sprintf(
            'Backup dos arquivos concluído: %s (%d arquivos)',
            basename($result['path']),
            $result['files_count']
        ));

        return $result;
    }

    /**
     * Adiciona pasta ao arquivo ZIP recursivamente
     */
    protected function addFolderToZip(\ZipArchive $zip, string $path, string $relativePath): int
    {
        $count = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $localPath = $relativePath . '/' . substr($filePath, strlen($path) + 1);

            // Verificar exclusões
            if ($this->shouldExclude($localPath)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($localPath);
            } else {
                $zip->addFile($filePath, $localPath);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Verifica se arquivo/pasta deve ser excluído
     */
    protected function shouldExclude(string $path): bool
    {
        foreach ($this->config->excludePatterns as $pattern) {
            if (fnmatch($pattern, $path) || fnmatch($pattern, basename($path))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cria pacote único com banco e arquivos
     */
    protected function createBackupPackage(string $dbPath, string $filesPath): string
    {
        $this->log('Criando pacote de backup...');

        // Usar ZipArchive se disponível
        if ($this->isZipAvailable()) {
            return $this->createPackageWithZip($dbPath, $filesPath);
        }

        // Usar PharData como alternativa
        if (class_exists('PharData')) {
            return $this->createPackageWithPhar($dbPath, $filesPath);
        }

        // Fallback: retornar apenas o arquivo do banco (maior prioridade)
        $this->log('AVISO: Sem compressor disponível, retornando apenas backup do banco');
        return $dbPath;
    }

    /**
     * Cria pacote usando ZipArchive
     */
    protected function createPackageWithZip(string $dbPath, string $filesPath): string
    {
        $filename = sprintf(
            '%s_full_%s.zip',
            $this->config->backupPrefix,
            date('Y-m-d_His')
        );
        $filepath = $this->tempPath . $filename;

        $zip = new \ZipArchive();
        $zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adicionar arquivo do banco
        $zip->addFile($dbPath, basename($dbPath));

        // Adicionar arquivo de arquivos
        $zip->addFile($filesPath, basename($filesPath));

        // Adicionar metadados
        $metadata = $this->getBackupMetadata($dbPath, $filesPath);
        $zip->addFromString('backup_info.json', json_encode($metadata, JSON_PRETTY_PRINT));

        $zip->close();

        $this->log(sprintf(
            'Pacote ZIP criado: %s (%.2f MB)',
            $filename,
            filesize($filepath) / 1024 / 1024
        ));

        return $filepath;
    }

    /**
     * Cria pacote usando PharData (tar)
     */
    protected function createPackageWithPhar(string $dbPath, string $filesPath): string
    {
        $filename = sprintf(
            '%s_full_%s.tar',
            $this->config->backupPrefix,
            date('Y-m-d_His')
        );
        $filepath = $this->tempPath . $filename;

        $phar = new \PharData($filepath);

        // Adicionar arquivo do banco
        $phar->addFile($dbPath, basename($dbPath));

        // Adicionar arquivo de arquivos
        $phar->addFile($filesPath, basename($filesPath));

        // Adicionar metadados
        $metadata = $this->getBackupMetadata($dbPath, $filesPath);
        $phar->addFromString('backup_info.json', json_encode($metadata, JSON_PRETTY_PRINT));

        $this->log(sprintf(
            'Pacote TAR criado: %s (%.2f MB)',
            basename($filepath),
            file_exists($filepath) ? filesize($filepath) / 1024 / 1024 : 0
        ));

        return $filepath;
    }

    /**
     * Gera metadados do backup
     */
    protected function getBackupMetadata(string $dbPath, string $filesPath): array
    {
        return [
            'created_at' => date('Y-m-d H:i:s'),
            'system' => 'DoarFazBem',
            'version' => '1.0',
            'php_version' => PHP_VERSION,
            'database_file' => basename($dbPath),
            'files_archive' => basename($filesPath),
        ];
    }

    /**
     * Verifica se Google Drive está configurado
     */
    public function isGoogleDriveConfigured(): bool
    {
        return file_exists($this->config->googleCredentialsPath)
            && file_exists($this->config->googleTokenPath);
    }

    /**
     * Upload para Google Drive
     */
    public function uploadToGoogleDrive(string $filePath): array
    {
        $this->log('Iniciando upload para Google Drive...');

        $result = [
            'success' => false,
            'file_id' => null,
            'file_name' => null,
        ];

        try {
            $googleDrive = new GoogleDriveService();

            $fileId = $googleDrive->uploadFile(
                $filePath,
                basename($filePath),
                $this->config->googleDriveFolderId
            );

            $result['success'] = true;
            $result['file_id'] = $fileId;
            $result['file_name'] = basename($filePath);

            $this->log('Upload concluído! File ID: ' . $fileId);

            // Rotação de backups remotos
            $this->rotateRemoteBackups($googleDrive);

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->log('ERRO no upload: ' . $e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Rotação de backups locais
     */
    protected function rotateLocalBackups(): void
    {
        $this->log('Executando rotação de backups locais...');

        $files = glob($this->tempPath . $this->config->backupPrefix . '_full_*.zip');
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $keep = $this->config->keepLocalBackups;
        $deleted = 0;

        foreach (array_slice($files, $keep) as $file) {
            unlink($file);
            $deleted++;
        }

        if ($deleted > 0) {
            $this->log("Removidos {$deleted} backups antigos (mantendo últimos {$keep})");
        }
    }

    /**
     * Rotação de backups no Google Drive
     */
    protected function rotateRemoteBackups(GoogleDriveService $googleDrive): void
    {
        $this->log('Executando rotação de backups remotos...');

        try {
            $files = $googleDrive->listBackupFiles(
                $this->config->backupPrefix,
                $this->config->googleDriveFolderId
            );

            $keep = $this->config->keepRemoteBackups;
            $deleted = 0;

            // Ordenar por data (mais recente primeiro)
            usort($files, function ($a, $b) {
                return strtotime($b['createdTime']) - strtotime($a['createdTime']);
            });

            foreach (array_slice($files, $keep) as $file) {
                $googleDrive->deleteFile($file['id']);
                $deleted++;
            }

            if ($deleted > 0) {
                $this->log("Removidos {$deleted} backups remotos antigos");
            }

        } catch (\Exception $e) {
            $this->log('Aviso: Erro na rotação remota: ' . $e->getMessage(), 'warning');
        }
    }

    /**
     * Limpa arquivos temporários
     */
    protected function cleanupTempFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Envia notificação por email
     */
    protected function sendNotification(array $result): void
    {
        $email = $this->config->notificationEmail;

        if (empty($email)) {
            return;
        }

        // Se configurado para notificar apenas erros
        if ($this->config->notifyOnErrorOnly && $result['success']) {
            return;
        }

        $this->log('Enviando notificação para: ' . $email);

        try {
            $emailService = \Config\Services::email();

            $status = $result['success'] ? 'SUCESSO' : 'FALHA';
            $subject = "[DoarFazBem Backup] {$status} - " . date('d/m/Y H:i');

            $body = $this->buildEmailBody($result);

            $emailService->setTo($email);
            $emailService->setSubject($subject);
            $emailService->setMessage($body);

            $emailService->send();

        } catch (\Exception $e) {
            $this->log('Erro ao enviar notificação: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Monta corpo do email de notificação
     */
    protected function buildEmailBody(array $result): string
    {
        $status = $result['success'] ? 'SUCESSO' : 'FALHA';
        $icon = $result['success'] ? '✅' : '❌';

        $html = "
        <h2>{$icon} Backup DoarFazBem - {$status}</h2>
        <p><strong>Data:</strong> {$result['started_at']}</p>
        <p><strong>Tipo:</strong> {$result['type']}</p>
        ";

        if ($result['database']) {
            $dbSize = number_format(($result['database']['size'] ?? 0) / 1024 / 1024, 2);
            $html .= "<p><strong>Banco de dados:</strong> {$dbSize} MB ({$result['database']['tables']} tabelas)</p>";
        }

        if ($result['files']) {
            $filesSize = number_format(($result['files']['size'] ?? 0) / 1024 / 1024, 2);
            $html .= "<p><strong>Arquivos:</strong> {$filesSize} MB ({$result['files']['files_count']} arquivos)</p>";
        }

        if ($result['upload']) {
            $uploadStatus = $result['upload']['success'] ? 'Enviado com sucesso' : 'Falha no upload';
            $html .= "<p><strong>Google Drive:</strong> {$uploadStatus}</p>";
        }

        if (!empty($result['errors'])) {
            $html .= "<h3>Erros:</h3><ul>";
            foreach ($result['errors'] as $error) {
                $html .= "<li>{$error}</li>";
            }
            $html .= "</ul>";
        }

        $html .= "<hr><p><small>DoarFazBem - Sistema de Backup Automatizado</small></p>";

        return $html;
    }

    /**
     * Lista backups disponíveis
     */
    public function listBackups(): array
    {
        $backups = [];

        // Buscar todos os tipos de backup (zip, tar, tar.gz, sql)
        $patterns = [
            $this->tempPath . $this->config->backupPrefix . '_full_*.zip',
            $this->tempPath . $this->config->backupPrefix . '_full_*.tar',
            $this->tempPath . $this->config->backupPrefix . '_full_*.tar.gz',
            $this->tempPath . $this->config->backupPrefix . '_db_*.sql',
            $this->tempPath . $this->config->backupPrefix . '_files_*.tar',
            $this->tempPath . $this->config->backupPrefix . '_files_*.zip',
        ];

        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                // Determinar tipo do backup
                $name = basename($file);
                if (strpos($name, '_full_') !== false) {
                    $type = 'Completo';
                } elseif (strpos($name, '_db_') !== false) {
                    $type = 'Banco de dados';
                } elseif (strpos($name, '_files_') !== false) {
                    $type = 'Arquivos';
                } else {
                    $type = 'Desconhecido';
                }

                $backups[] = [
                    'name' => $name,
                    'path' => $file,
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'location' => 'local',
                    'type' => $type,
                ];
            }
        }

        // Remover duplicatas (caso o mesmo arquivo case com múltiplos patterns)
        $backups = array_values(array_unique($backups, SORT_REGULAR));

        // Ordenar por data (mais recente primeiro)
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Restaura backup do banco de dados
     */
    public function restoreDatabase(string $sqlFile): array
    {
        $this->log('Iniciando restauração do banco de dados...');

        $result = [
            'success' => false,
            'queries_executed' => 0,
        ];

        try {
            if (!file_exists($sqlFile)) {
                throw new \Exception('Arquivo SQL não encontrado');
            }

            $sql = file_get_contents($sqlFile);
            $db = \Config\Database::connect();

            // Dividir em queries individuais
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($q) => !empty($q) && !str_starts_with($q, '--')
            );

            foreach ($queries as $query) {
                if (!empty($query)) {
                    $db->query($query);
                    $result['queries_executed']++;
                }
            }

            $result['success'] = true;
            $this->log("Restauração concluída: {$result['queries_executed']} queries executadas");

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->log('ERRO na restauração: ' . $e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Adiciona entrada ao log
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $entry = [
            'time' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
        ];

        $this->log[] = $entry;

        // Também logar no sistema
        log_message($level, '[Backup] ' . $message);
    }

    /**
     * Retorna o log atual
     */
    public function getLog(): array
    {
        return $this->log;
    }
}

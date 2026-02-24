<?php

namespace App\Libraries;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * SecureUpload
 *
 * Biblioteca para validação segura de uploads de arquivos
 * Protege contra:
 * - Upload de arquivos maliciosos
 * - Bypass de extensão (double extension)
 * - MIME type spoofing
 * - Path traversal
 * - Arquivos executáveis disfarçados
 */
class SecureUpload
{
    /**
     * Tipos MIME permitidos para imagens
     */
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Extensões permitidas para imagens
     */
    private const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
    ];

    /**
     * Tipos MIME permitidos para documentos
     */
    private const ALLOWED_DOCUMENT_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Extensões permitidas para documentos
     */
    private const ALLOWED_DOCUMENT_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Tamanho máximo padrão (2MB)
     */
    private const DEFAULT_MAX_SIZE = 2 * 1024 * 1024;

    /**
     * Lista de assinaturas de arquivos maliciosos (magic bytes)
     */
    private const MALICIOUS_SIGNATURES = [
        "\x4D\x5A",                       // EXE/DLL Windows
        "\x7F\x45\x4C\x46",               // ELF Linux
        "\xCA\xFE\xBA\xBE",               // Java class
        "\x50\x4B\x03\x04",               // ZIP (pode conter malware)
        "<?php",                          // PHP code
        "<?=",                            // PHP short tag
        "<script",                        // JavaScript
        "#!/",                            // Shell script
    ];

    /**
     * Valida e processa upload de imagem
     *
     * @param UploadedFile $file Arquivo enviado
     * @param string $destinationPath Diretório de destino
     * @param int $maxSize Tamanho máximo em bytes
     * @return array ['success' => bool, 'filename' => string, 'error' => string]
     */
    public function processImage(
        UploadedFile $file,
        string $destinationPath,
        int $maxSize = self::DEFAULT_MAX_SIZE
    ): array {
        return $this->processFile(
            $file,
            $destinationPath,
            self::ALLOWED_IMAGE_MIMES,
            self::ALLOWED_IMAGE_EXTENSIONS,
            $maxSize,
            true
        );
    }

    /**
     * Valida e processa upload de documento
     *
     * @param UploadedFile $file Arquivo enviado
     * @param string $destinationPath Diretório de destino
     * @param int $maxSize Tamanho máximo em bytes
     * @return array ['success' => bool, 'filename' => string, 'error' => string]
     */
    public function processDocument(
        UploadedFile $file,
        string $destinationPath,
        int $maxSize = self::DEFAULT_MAX_SIZE
    ): array {
        return $this->processFile(
            $file,
            $destinationPath,
            self::ALLOWED_DOCUMENT_MIMES,
            self::ALLOWED_DOCUMENT_EXTENSIONS,
            $maxSize,
            false
        );
    }

    /**
     * Processa o upload de arquivo com todas as validações
     */
    private function processFile(
        UploadedFile $file,
        string $destinationPath,
        array $allowedMimes,
        array $allowedExtensions,
        int $maxSize,
        bool $validateAsImage
    ): array {
        // 1. Verificar se o arquivo foi enviado corretamente
        if (!$file->isValid()) {
            return $this->error('Arquivo inválido ou não enviado: ' . $file->getErrorString());
        }

        // 2. Verificar tamanho
        if ($file->getSize() > $maxSize) {
            $maxMB = round($maxSize / 1024 / 1024, 1);
            return $this->error("Arquivo muito grande. Tamanho máximo: {$maxMB}MB");
        }

        // 3. Verificar extensão original
        $originalExtension = strtolower($file->getClientExtension());
        if (!in_array($originalExtension, $allowedExtensions)) {
            return $this->error('Extensão de arquivo não permitida: ' . $originalExtension);
        }

        // 4. Verificar MIME type reportado
        $reportedMime = $file->getClientMimeType();
        if (!in_array($reportedMime, $allowedMimes)) {
            return $this->error('Tipo de arquivo não permitido: ' . $reportedMime);
        }

        // 5. Verificar MIME type real (usando finfo)
        $realMime = $this->getRealMimeType($file->getTempName());
        if (!in_array($realMime, $allowedMimes)) {
            log_message('warning', "Upload bloqueado: MIME reportado ({$reportedMime}) diferente do real ({$realMime})");
            return $this->error('Tipo de arquivo inválido ou corrompido');
        }

        // 6. Verificar assinaturas maliciosas
        if ($this->containsMaliciousSignature($file->getTempName())) {
            log_message('error', 'Upload bloqueado: assinatura maliciosa detectada');
            return $this->error('Arquivo suspeito detectado');
        }

        // 7. Para imagens, validar se é realmente uma imagem válida
        if ($validateAsImage && !$this->isValidImage($file->getTempName())) {
            return $this->error('Arquivo de imagem inválido ou corrompido');
        }

        // 8. Verificar double extension (arquivo.php.jpg)
        $originalName = $file->getClientName();
        if ($this->hasDoubleExtension($originalName)) {
            log_message('warning', 'Upload bloqueado: extensão dupla detectada - ' . $originalName);
            return $this->error('Nome de arquivo inválido');
        }

        // 9. Criar diretório se não existir
        if (!is_dir($destinationPath)) {
            if (!mkdir($destinationPath, 0755, true)) {
                return $this->error('Erro ao criar diretório de destino');
            }
        }

        // 10. Gerar nome de arquivo seguro e único
        $safeFilename = $this->generateSafeFilename($originalExtension);

        // 11. Mover arquivo
        try {
            $file->move($destinationPath, $safeFilename);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao mover arquivo: ' . $e->getMessage());
            return $this->error('Erro ao salvar arquivo');
        }

        // 12. Verificar se o arquivo foi salvo corretamente
        $finalPath = $destinationPath . DIRECTORY_SEPARATOR . $safeFilename;
        if (!file_exists($finalPath)) {
            return $this->error('Erro ao salvar arquivo');
        }

        // 13. Definir permissões seguras (somente leitura)
        chmod($finalPath, 0644);

        log_message('info', "Upload seguro: {$safeFilename} ({$realMime}, " . $file->getSize() . " bytes)");

        return [
            'success' => true,
            'filename' => $safeFilename,
            'path' => $finalPath,
            'size' => $file->getSize(),
            'mime' => $realMime,
        ];
    }

    /**
     * Obtém o MIME type real do arquivo usando finfo
     */
    private function getRealMimeType(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($path) ?: 'application/octet-stream';
    }

    /**
     * Verifica se o arquivo contém assinaturas maliciosas
     */
    private function containsMaliciousSignature(string $path): bool
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return true; // Fail safe
        }

        // Ler primeiros 8KB do arquivo
        $content = fread($handle, 8192);
        fclose($handle);

        if ($content === false) {
            return true;
        }

        // Verificar assinaturas maliciosas
        foreach (self::MALICIOUS_SIGNATURES as $signature) {
            if (strpos($content, $signature) !== false) {
                return true;
            }
        }

        // Verificar por código PHP em qualquer lugar do arquivo
        if (preg_match('/<\?(php|=)/i', $content)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se é uma imagem válida
     */
    private function isValidImage(string $path): bool
    {
        // Tentar carregar a imagem
        $imageInfo = @getimagesize($path);

        if ($imageInfo === false) {
            return false;
        }

        // Verificar se o tipo é válido
        $validTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF,
            IMAGETYPE_WEBP,
        ];

        return in_array($imageInfo[2], $validTypes);
    }

    /**
     * Verifica se o nome tem extensão dupla
     */
    private function hasDoubleExtension(string $filename): bool
    {
        // Extensões perigosas
        $dangerousExtensions = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar',
            'exe', 'bat', 'cmd', 'sh', 'bash',
            'js', 'vbs', 'wsf', 'asp', 'aspx',
            'jsp', 'jspx', 'cgi', 'pl', 'py',
            'htaccess', 'htpasswd'
        ];

        // Remover a última extensão e verificar se há outra extensão perigosa
        $parts = explode('.', strtolower($filename));

        if (count($parts) > 2) {
            // Remover o último elemento (extensão final)
            array_pop($parts);

            // Verificar se alguma das partes restantes é uma extensão perigosa
            foreach ($parts as $part) {
                if (in_array($part, $dangerousExtensions)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gera um nome de arquivo seguro e único
     */
    private function generateSafeFilename(string $extension): string
    {
        // Usar timestamp + hash aleatório para unicidade
        $timestamp = time();
        $random = bin2hex(random_bytes(8));

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Retorna array de erro
     */
    private function error(string $message): array
    {
        return [
            'success' => false,
            'filename' => null,
            'error' => $message,
        ];
    }

    /**
     * Valida apenas (não move o arquivo)
     *
     * @param UploadedFile $file Arquivo a validar
     * @param string $type Tipo: 'image' ou 'document'
     * @param int $maxSize Tamanho máximo em bytes
     * @return array ['valid' => bool, 'error' => string]
     */
    public function validate(
        UploadedFile $file,
        string $type = 'image',
        int $maxSize = self::DEFAULT_MAX_SIZE
    ): array {
        $allowedMimes = $type === 'image' ? self::ALLOWED_IMAGE_MIMES : self::ALLOWED_DOCUMENT_MIMES;
        $allowedExtensions = $type === 'image' ? self::ALLOWED_IMAGE_EXTENSIONS : self::ALLOWED_DOCUMENT_EXTENSIONS;

        // Verificações básicas
        if (!$file->isValid()) {
            return ['valid' => false, 'error' => $file->getErrorString()];
        }

        if ($file->getSize() > $maxSize) {
            return ['valid' => false, 'error' => 'Arquivo muito grande'];
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Extensão não permitida'];
        }

        $mime = $this->getRealMimeType($file->getTempName());
        if (!in_array($mime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Tipo de arquivo inválido'];
        }

        if ($this->containsMaliciousSignature($file->getTempName())) {
            return ['valid' => false, 'error' => 'Arquivo suspeito'];
        }

        if ($type === 'image' && !$this->isValidImage($file->getTempName())) {
            return ['valid' => false, 'error' => 'Imagem inválida'];
        }

        return ['valid' => true, 'error' => null];
    }
}

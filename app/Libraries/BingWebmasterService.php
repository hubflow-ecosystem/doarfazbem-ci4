<?php

namespace App\Libraries;

/**
 * Serviço de integração com Bing — IndexNow Protocol
 *
 * IndexNow notifica o Bing (e outros buscadores) sobre URLs novas ou atualizadas.
 * É mais eficiente que esperar o crawl natural — o Bing indexa em minutos.
 *
 * Endpoints:
 *   Submissão: https://www.bing.com/indexnow
 *   Universal: https://api.indexnow.org/indexnow (todos os buscadores)
 *   Verificação: https://doarfazbem.com.br/{key}.txt
 *
 * Configuração no .env:
 *   BING_API_KEY = sua-indexnow-key
 *   BING_SITE_URL = https://doarfazbem.com.br
 */
class BingWebmasterService
{
  private string $indexNowKey;
  private string $siteUrl;
  private string $keyLocation;
  private string $bingEndpoint = 'https://www.bing.com/indexnow';
  private int $timeout = 15;

  public function __construct()
  {
    $this->indexNowKey = getenv('BING_API_KEY') ?: '';
    $this->siteUrl = rtrim(getenv('BING_SITE_URL') ?: 'https://doarfazbem.com.br', '/');
    $this->keyLocation = $this->siteUrl . '/' . $this->indexNowKey . '.txt';

    if (empty($this->indexNowKey)) {
      log_message('warning', 'BingWebmasterService: BING_API_KEY (IndexNow) não configurada no .env');
    }
  }

  // ================================================================
  // TESTE DE CONEXÃO
  // ================================================================

  /**
   * Testar se a configuração IndexNow está correta
   */
  public function testConnection(): array
  {
    if (empty($this->indexNowKey)) {
      return [
        'success' => false,
        'error' => 'BING_API_KEY não configurada no .env',
        'steps' => [
          '1. Gere uma chave em https://www.indexnow.org/getstarted',
          '2. Crie o arquivo public/{key}.txt com o conteúdo da chave',
          '3. Adicione BING_API_KEY=suachave no .env',
        ],
      ];
    }

    // Verificar arquivo de verificação
    $keyFileUrl = $this->keyLocation;
    $fileCheck = $this->checkKeyFile($keyFileUrl);

    if (!$fileCheck['valid']) {
      return [
        'success' => false,
        'error' => "Arquivo de verificação {$keyFileUrl} — " . $fileCheck['error'],
      ];
    }

    // Enviar submissão de teste (homepage)
    $result = $this->submitUrl($this->siteUrl . '/');

    return [
      'success' => $result['success'],
      'site_url' => $this->siteUrl,
      'key_file' => $keyFileUrl,
      'key_valid' => $fileCheck['valid'],
      'http_code' => $result['http_code'] ?? null,
      'error' => $result['error'] ?? null,
    ];
  }

  // ================================================================
  // SUBMISSÃO DE URLs (IndexNow)
  // ================================================================

  /**
   * Submeter uma URL única ao Bing via IndexNow
   */
  public function submitUrl(string $url): array
  {
    return $this->submitUrlBatch([$url]);
  }

  /**
   * Submeter múltiplas URLs ao Bing via IndexNow (até 10.000 por chamada)
   */
  public function submitUrlBatch(array $urls): array
  {
    if (empty($urls)) {
      return ['success' => true, 'submitted' => 0, 'http_code' => 0];
    }

    if (empty($this->indexNowKey)) {
      return ['success' => false, 'error' => 'BING_API_KEY não configurada', 'submitted' => 0];
    }

    $batches = array_chunk($urls, 10000);
    $totalSubmitted = 0;
    $errors = [];
    $lastHttpCode = 0;

    foreach ($batches as $batch) {
      $payload = json_encode([
        'host' => parse_url($this->siteUrl, PHP_URL_HOST),
        'key' => $this->indexNowKey,
        'keyLocation' => $this->keyLocation,
        'urlList' => array_values($batch),
      ]);

      $ch = curl_init($this->bingEndpoint);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => $this->timeout,
        CURLOPT_HTTPHEADER => [
          'Content-Type: application/json; charset=utf-8',
          'User-Agent: DoarFazBem-SEO/1.0',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
      ]);

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $curlError = curl_error($ch);
      $lastHttpCode = $httpCode;
      curl_close($ch);

      if ($curlError) {
        $errors[] = "cURL: {$curlError}";
        continue;
      }

      // IndexNow: 200/202 = Aceito, 400 = Inválido, 403 = Chave inválida, 429 = Rate limit
      if ($httpCode === 200 || $httpCode === 202) {
        $totalSubmitted += count($batch);
      } else {
        $errors[] = "HTTP {$httpCode}: {$response}";
      }

      usleep(200000); // 200ms entre batches
    }

    return [
      'success' => empty($errors),
      'submitted' => $totalSubmitted,
      'http_code' => $lastHttpCode,
      'errors' => $errors,
    ];
  }

  /**
   * Submeter sitemap ao Bing via ping
   */
  public function submitSitemap(string $sitemapUrl): array
  {
    $pingUrl = 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl);

    $ch = curl_init($pingUrl);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_USERAGENT => 'DoarFazBem-SEO/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
      return ['success' => false, 'error' => $curlError];
    }

    return [
      'success' => ($httpCode >= 200 && $httpCode < 300),
      'sitemap' => $sitemapUrl,
      'http_code' => $httpCode,
    ];
  }

  // ================================================================
  // MÉTODOS PRIVADOS
  // ================================================================

  private function checkKeyFile(string $url): array
  {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_USERAGENT => 'DoarFazBem-SEO/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
      return ['valid' => false, 'error' => "Erro cURL: {$curlError}"];
    }

    if ($httpCode !== 200) {
      return ['valid' => false, 'error' => "Arquivo retornou HTTP {$httpCode}"];
    }

    $content = trim($response);
    if ($content !== $this->indexNowKey) {
      return ['valid' => false, 'error' => "Conteúdo do arquivo não corresponde à chave"];
    }

    return ['valid' => true];
  }
}

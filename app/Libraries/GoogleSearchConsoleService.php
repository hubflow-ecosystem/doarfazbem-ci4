<?php

namespace App\Libraries;

/**
 * Serviço de integração com Google Search Console API (REST)
 *
 * Coleta dados de queries de busca (keywords, cliques, impressões, CTR, posição)
 * para alimentar o motor SEO autônomo do DoarFazBem.
 *
 * Autenticação: Service Account (JSON key file)
 * Escopo: webmasters.readonly + indexing
 *
 * Configuração no .env:
 *   GSC_CREDENTIALS_PATH = writable/credentials/gsc-service-account.json
 *   GSC_SITE_URL = sc-domain:doarfazbem.com.br
 */
class GoogleSearchConsoleService
{
  private $client;
  private string $siteUrl;
  private string $credentialsPath;
  private int $timeout = 60;

  public function __construct()
  {
    $this->siteUrl = getenv('GSC_SITE_URL') ?: 'sc-domain:doarfazbem.com.br';
    $this->credentialsPath = getenv('GSC_CREDENTIALS_PATH')
      ?: WRITEPATH . 'credentials/gsc-service-account.json';

    if (class_exists('\Google\Client')) {
      $this->initClient();
    }
  }

  /**
   * Inicializa o Google Client com credenciais de service account
   */
  private function initClient(): void
  {
    try {
      $this->client = new \Google\Client();
      $this->client->setApplicationName('DoarFazBem SEO Engine');
      $this->client->setAuthConfig($this->credentialsPath);
      $this->client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
      $this->client->addScope('https://www.googleapis.com/auth/indexing');
    } catch (\Exception $e) {
      log_message('error', 'GSC: Erro ao inicializar client: ' . $e->getMessage());
      $this->client = null;
    }
  }

  // ================================================================
  // MÉTODOS DE COLETA DE QUERIES
  // ================================================================

  /**
   * Busca TODAS as queries do período com paginação automática
   *
   * @param int $days Dias para trás
   * @return array ['success' => bool, 'queries' => [...], 'total' => int]
   */
  public function fetchAllQueries(int $days = 28): array
  {
    $startDate = date('Y-m-d', strtotime("-{$days} days"));
    $endDate = date('Y-m-d', strtotime('-1 day'));

    $allRows = [];
    $startRow = 0;
    $rowLimit = 25000;

    do {
      $body = [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'dimensions' => ['query', 'page'],
        'rowLimit' => $rowLimit,
        'startRow' => $startRow,
      ];

      $result = $this->querySearchAnalytics($body);

      if (!$result['success']) {
        return $result;
      }

      $rows = $result['rows'] ?? [];
      if (empty($rows)) break;

      foreach ($rows as $row) {
        $allRows[] = [
          'query' => $row['keys'][0] ?? '',
          'page_url' => $row['keys'][1] ?? '',
          'clicks' => (int) ($row['clicks'] ?? 0),
          'impressions' => (int) ($row['impressions'] ?? 0),
          'ctr' => round(($row['ctr'] ?? 0) * 100, 2),
          'position' => round($row['position'] ?? 0, 1),
          'date' => $endDate,
          'source' => 'google',
        ];
      }

      $startRow += $rowLimit;

    } while (count($rows) === $rowLimit);

    return [
      'success' => true,
      'queries' => $allRows,
      'total' => count($allRows),
      'period' => "{$startDate} → {$endDate}",
    ];
  }

  /**
   * Busca queries por dia individual com dimensão de device
   *
   * @param string $date Data no formato Y-m-d
   * @return array
   */
  public function fetchQueriesByDay(string $date): array
  {
    $body = [
      'startDate' => $date,
      'endDate' => $date,
      'dimensions' => ['query', 'page', 'device'],
      'rowLimit' => 25000,
    ];

    $result = $this->querySearchAnalytics($body);

    if (!$result['success']) {
      return $result;
    }

    $rows = [];
    foreach ($result['rows'] ?? [] as $row) {
      $rows[] = [
        'query' => $row['keys'][0] ?? '',
        'page_url' => $row['keys'][1] ?? '',
        'device' => strtolower($row['keys'][2] ?? 'desktop'),
        'clicks' => (int) ($row['clicks'] ?? 0),
        'impressions' => (int) ($row['impressions'] ?? 0),
        'ctr' => round(($row['ctr'] ?? 0) * 100, 2),
        'position' => round($row['position'] ?? 0, 1),
        'date' => $date,
        'source' => 'google',
      ];
    }

    return [
      'success' => true,
      'queries' => $rows,
      'total' => count($rows),
      'date' => $date,
    ];
  }

  /**
   * Histórico dia a dia dos últimos N dias
   */
  public function fetchDailyHistory(int $days = 28): array
  {
    $allRows = [];

    for ($i = $days; $i >= 1; $i--) {
      $date = date('Y-m-d', strtotime("-{$i} days"));
      $result = $this->fetchQueriesByDay($date);

      if ($result['success']) {
        $allRows = array_merge($allRows, $result['queries']);
      }

      usleep(200000); // 200ms entre requisições
    }

    return [
      'success' => true,
      'queries' => $allRows,
      'total' => count($allRows),
      'days' => $days,
    ];
  }

  /**
   * Páginas com muitas impressões mas CTR baixo
   */
  public function fetchLowCtrPages(int $days = 28, float $maxCtr = 0.03, int $minImpressions = 50): array
  {
    $result = $this->fetchAllQueries($days);
    if (!$result['success']) return $result;

    $lowCtr = array_filter($result['queries'], function ($row) use ($maxCtr, $minImpressions) {
      return $row['impressions'] >= $minImpressions && ($row['ctr'] / 100) < $maxCtr;
    });

    usort($lowCtr, fn($a, $b) => $b['impressions'] - $a['impressions']);

    return [
      'success' => true,
      'pages' => array_values($lowCtr),
      'total' => count($lowCtr),
    ];
  }

  /**
   * Content Gaps — queries sem posição relevante (posição > 20)
   */
  public function fetchContentGaps(int $days = 28, int $minImpressions = 20): array
  {
    $result = $this->fetchAllQueries($days);
    if (!$result['success']) return $result;

    $gaps = array_filter($result['queries'], function ($row) use ($minImpressions) {
      return $row['impressions'] >= $minImpressions && $row['position'] > 20;
    });

    usort($gaps, fn($a, $b) => $b['impressions'] - $a['impressions']);

    return [
      'success' => true,
      'gaps' => array_values($gaps),
      'total' => count($gaps),
    ];
  }

  /**
   * Queries na striking distance (posição 4-20)
   */
  public function fetchStrikingDistance(int $days = 28, int $minPos = 4, int $maxPos = 20, int $minImpressions = 10): array
  {
    $result = $this->fetchAllQueries($days);
    if (!$result['success']) return $result;

    $striking = array_filter($result['queries'], function ($row) use ($minPos, $maxPos, $minImpressions) {
      return $row['position'] >= $minPos
        && $row['position'] <= $maxPos
        && $row['impressions'] >= $minImpressions;
    });

    usort($striking, fn($a, $b) => $b['impressions'] - $a['impressions']);

    return [
      'success' => true,
      'queries' => array_values($striking),
      'total' => count($striking),
    ];
  }

  // ================================================================
  // INDEXING API
  // ================================================================

  /**
   * Solicitar indexação de URL via Indexing API
   */
  public function requestIndexing(string $url): array
  {
    if (!$this->client) {
      return ['success' => false, 'error' => 'Google Client não inicializado'];
    }

    try {
      $httpClient = $this->client->authorize();
      $response = $httpClient->post(
        'https://indexing.googleapis.com/v3/urlNotifications:publish',
        [
          'json' => [
            'url' => $url,
            'type' => 'URL_UPDATED',
          ],
        ]
      );

      $body = json_decode($response->getBody()->getContents(), true);

      return [
        'success' => true,
        'url' => $url,
        'notifyTime' => $body['urlNotificationMetadata']['latestUpdate']['notifyTime'] ?? null,
      ];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  /**
   * Solicitar remoção de URL via Indexing API
   */
  public function requestRemoval(string $url): array
  {
    if (!$this->client) {
      return ['success' => false, 'error' => 'Google Client não inicializado'];
    }

    try {
      $httpClient = $this->client->authorize();
      $response = $httpClient->post(
        'https://indexing.googleapis.com/v3/urlNotifications:publish',
        [
          'json' => [
            'url' => $url,
            'type' => 'URL_DELETED',
          ],
        ]
      );

      return ['success' => true, 'url' => $url];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  // ================================================================
  // URL INSPECTION API
  // ================================================================

  /**
   * Inspecionar URL — status de indexação, crawl, mobile
   */
  public function inspectUrl(string $url): array
  {
    if (!$this->client) {
      return ['success' => false, 'error' => 'Google Client não inicializado'];
    }

    try {
      $httpClient = $this->client->authorize();
      $response = $httpClient->post(
        'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect',
        [
          'json' => [
            'inspectionUrl' => $url,
            'siteUrl' => $this->siteUrl,
          ],
        ]
      );

      $body = json_decode($response->getBody()->getContents(), true);
      $result = $body['inspectionResult'] ?? [];

      return [
        'success' => true,
        'url' => $url,
        'indexing_state' => $result['indexStatusResult']['coverageState'] ?? 'unknown',
        'crawl_time' => $result['indexStatusResult']['lastCrawlTime'] ?? null,
        'mobile_usability' => $result['mobileUsabilityResult']['verdict'] ?? 'unknown',
        'rich_results' => $result['richResultsResult']['verdict'] ?? 'unknown',
      ];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  // ================================================================
  // PAGESPEED INSIGHTS
  // ================================================================

  /**
   * Obter dados do PageSpeed Insights (LCP, FID, CLS, FCP, TTFB)
   */
  public function getPageSpeedInsights(string $url): array
  {
    $apiKey = getenv('GOOGLE_PAGESPEED_API_KEY') ?: '';
    $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
    $params = http_build_query([
      'url' => $url,
      'key' => $apiKey,
      'strategy' => 'mobile',
      'category' => 'performance',
    ]);

    $ch = curl_init("{$apiUrl}?{$params}");
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
      return ['success' => false, 'error' => "HTTP {$httpCode}"];
    }

    $data = json_decode($response, true);
    $metrics = $data['lighthouseResult']['audits'] ?? [];

    return [
      'success' => true,
      'url' => $url,
      'score' => (int) (($data['lighthouseResult']['categories']['performance']['score'] ?? 0) * 100),
      'lcp' => $metrics['largest-contentful-paint']['numericValue'] ?? null,
      'fid' => $metrics['max-potential-fid']['numericValue'] ?? null,
      'cls' => $metrics['cumulative-layout-shift']['numericValue'] ?? null,
      'fcp' => $metrics['first-contentful-paint']['numericValue'] ?? null,
      'ttfb' => $metrics['server-response-time']['numericValue'] ?? null,
    ];
  }

  // ================================================================
  // DEAD URLs CHECKER
  // ================================================================

  /**
   * Verifica URLs mortas (404/410/500) usando curl_multi em paralelo
   */
  public function findDeadUrls(array $urls): array
  {
    $deadUrls = [];
    $multiHandle = curl_multi_init();
    $handles = [];

    foreach ($urls as $url) {
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'DoarFazBem-SEO/1.0',
      ]);
      curl_multi_add_handle($multiHandle, $ch);
      $handles[$url] = $ch;
    }

    do {
      $status = curl_multi_exec($multiHandle, $active);
      curl_multi_select($multiHandle);
    } while ($active && $status === CURLM_OK);

    foreach ($handles as $url => $ch) {
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if (in_array($httpCode, [404, 410, 500, 502, 503])) {
        $deadUrls[] = [
          'url' => $url,
          'http_code' => $httpCode,
        ];
      }
      curl_multi_remove_handle($multiHandle, $ch);
      curl_close($ch);
    }

    curl_multi_close($multiHandle);

    return $deadUrls;
  }

  // ================================================================
  // TESTE DE CONEXÃO
  // ================================================================

  /**
   * Testa conexão com a API do Google Search Console
   */
  public function testConnection(): array
  {
    if (!class_exists('\Google\Client')) {
      return [
        'success' => false,
        'error' => 'Biblioteca google/apiclient não instalada. Execute: composer require google/apiclient',
      ];
    }

    if (!$this->client) {
      return [
        'success' => false,
        'error' => 'Google Client não inicializado. Verifique credenciais em: ' . $this->credentialsPath,
      ];
    }

    if (!file_exists($this->credentialsPath)) {
      return [
        'success' => false,
        'error' => 'Arquivo de credenciais não encontrado: ' . $this->credentialsPath,
        'steps' => [
          '1. Acesse https://console.cloud.google.com/',
          '2. Crie um Service Account',
          '3. Baixe o JSON e salve em: ' . $this->credentialsPath,
          '4. No Search Console, adicione o email do SA como proprietário',
        ],
      ];
    }

    // Tenta uma consulta simples para validar a conexão
    $body = [
      'startDate' => date('Y-m-d', strtotime('-3 days')),
      'endDate' => date('Y-m-d', strtotime('-1 day')),
      'dimensions' => ['query'],
      'rowLimit' => 1,
    ];

    $result = $this->querySearchAnalytics($body);

    return [
      'success' => $result['success'],
      'site_url' => $this->siteUrl,
      'credentials' => basename($this->credentialsPath),
      'sample_rows' => count($result['rows'] ?? []),
      'error' => $result['error'] ?? null,
    ];
  }

  // ================================================================
  // MÉTODO BASE — CHAMADA À SEARCH ANALYTICS API
  // ================================================================

  /**
   * Chamada genérica para a Search Analytics API
   */
  private function querySearchAnalytics(array $body): array
  {
    if (!$this->client) {
      return ['success' => false, 'error' => 'Google Client não inicializado', 'rows' => []];
    }

    try {
      $httpClient = $this->client->authorize();

      $encodedSite = urlencode($this->siteUrl);
      $url = "https://searchconsole.googleapis.com/webmasters/v3/sites/{$encodedSite}/searchAnalytics/query";

      $response = $httpClient->post($url, [
        'json' => $body,
        'timeout' => $this->timeout,
      ]);

      $data = json_decode($response->getBody()->getContents(), true);

      return [
        'success' => true,
        'rows' => $data['rows'] ?? [],
        'responseAggregationType' => $data['responseAggregationType'] ?? null,
      ];
    } catch (\Exception $e) {
      log_message('error', 'GSC API error: ' . $e->getMessage());
      return ['success' => false, 'error' => $e->getMessage(), 'rows' => []];
    }
  }
}

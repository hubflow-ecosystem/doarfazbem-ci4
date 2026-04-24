<?php

namespace App\Libraries;

/**
 * Serviço de integração com Bing Webmaster Tools API (REST)
 *
 * DIFERENTE do BingWebmasterService.php (que usa IndexNow para submissão de URLs).
 * Este serviço usa a REST API oficial do Bing para coletar ANALYTICS:
 * - Queries de busca (keywords que trazem visitas)
 * - Cliques, impressões, CTR, posição média
 * - Dados de crawl e sitemaps
 *
 * Autenticação: API Key
 * Obtenção: Bing Webmaster Tools → Settings → API Access → Generate API Key
 *
 * Configuração no .env:
 *   BING_WMT_API_KEY = sua-api-key
 *   BING_SITE_URL = https://doarfazbem.com.br/
 */
class BingWebmasterApiService
{
  private string $baseUrl = 'https://ssl.bing.com/webmaster/api.svc/json';
  private string $apiKey;
  private string $defaultSite;
  private int $timeout = 30;

  public function __construct(?string $site = null)
  {
    $this->apiKey = getenv('BING_WMT_API_KEY') ?: '';
    $this->defaultSite = $site ?? (getenv('BING_SITE_URL') ?: 'https://doarfazbem.com.br/');
    $this->defaultSite = rtrim($this->defaultSite, '/') . '/';

    if (empty($this->apiKey)) {
      log_message('warning', 'BingWebmasterApiService: BING_WMT_API_KEY não configurada no .env');
    }
  }

  // ================================================================
  // MÉTODOS DE QUERIES/KEYWORDS (Analytics principal)
  // ================================================================

  /**
   * Retorna queries de busca para um site no período especificado
   */
  public function getQueryStats(string $siteUrl, string $startDate, string $endDate): array
  {
    $params = [
      'siteUrl' => $siteUrl,
      'startDate' => $startDate . 'T00:00:00',
      'endDate' => $endDate . 'T23:59:59',
    ];

    $result = $this->get('GetQueryStats', $params);

    if (!$result['success']) {
      return ['success' => false, 'error' => $result['error'], 'queries' => []];
    }

    $queries = $result['data']['d'] ?? [];

    return [
      'success' => true,
      'queries' => array_map(function ($q) {
        return [
          'query' => $q['Query'] ?? '',
          'impressions' => (int) ($q['Impressions'] ?? 0),
          'clicks' => (int) ($q['Clicks'] ?? 0),
          'avg_position' => (float) ($q['AvgClickPosition'] ?? 0),
          'avg_imp_position' => (float) ($q['AvgImpressionPosition'] ?? 0),
          'ctr' => $q['Clicks'] > 0 && $q['Impressions'] > 0
            ? round(($q['Clicks'] / $q['Impressions']) * 100, 2)
            : 0,
        ];
      }, $queries),
      'total' => count($queries),
      'site' => $siteUrl,
      'period' => "{$startDate} → {$endDate}",
    ];
  }

  /**
   * Stats de uma query específica por página (URL breakdown)
   */
  public function getQueryPageStats(string $siteUrl, string $query, string $startDate, string $endDate): array
  {
    $params = [
      'siteUrl' => $siteUrl,
      'query' => $query,
      'startDate' => $startDate . 'T00:00:00',
      'endDate' => $endDate . 'T23:59:59',
    ];

    $result = $this->get('GetQueryPageStats', $params);

    if (!$result['success']) {
      return ['success' => false, 'error' => $result['error'], 'pages' => []];
    }

    $pages = $result['data']['d'] ?? [];

    return [
      'success' => true,
      'query' => $query,
      'pages' => array_map(function ($p) {
        return [
          'url' => $p['Page'] ?? '',
          'impressions' => (int) ($p['Impressions'] ?? 0),
          'clicks' => (int) ($p['Clicks'] ?? 0),
          'avg_position' => (float) ($p['AvgClickPosition'] ?? 0),
        ];
      }, $pages),
    ];
  }

  // ================================================================
  // MÉTODOS DE PÁGINAS
  // ================================================================

  /**
   * Stats por página (quais URLs têm mais cliques/impressões)
   */
  public function getPageStats(string $siteUrl, string $startDate, string $endDate): array
  {
    $params = [
      'siteUrl' => $siteUrl,
      'startDate' => $startDate . 'T00:00:00',
      'endDate' => $endDate . 'T23:59:59',
    ];

    $result = $this->get('GetPageStats', $params);

    if (!$result['success']) {
      return ['success' => false, 'error' => $result['error'], 'pages' => []];
    }

    $pages = $result['data']['d'] ?? [];

    return [
      'success' => true,
      'pages' => array_map(function ($p) {
        return [
          'url' => $p['Page'] ?? '',
          'impressions' => (int) ($p['Impressions'] ?? 0),
          'clicks' => (int) ($p['Clicks'] ?? 0),
          'avg_position' => (float) ($p['AvgClickPosition'] ?? 0),
          'ctr' => $p['Clicks'] > 0 && $p['Impressions'] > 0
            ? round(($p['Clicks'] / $p['Impressions']) * 100, 2)
            : 0,
        ];
      }, $pages),
      'total' => count($pages),
    ];
  }

  // ================================================================
  // SITEMAPS
  // ================================================================

  /**
   * Lista sitemaps registrados no Bing
   */
  public function getSitemaps(string $siteUrl): array
  {
    $params = ['siteUrl' => $siteUrl];
    $result = $this->get('GetSitemaps', $params);

    if (!$result['success']) {
      return ['success' => false, 'error' => $result['error'], 'sitemaps' => []];
    }

    $sitemaps = $result['data']['d'] ?? [];

    return [
      'success' => true,
      'sitemaps' => array_map(function ($s) {
        return [
          'url' => $s['SiteMapUrl'] ?? '',
          'last_crawled' => $s['LastCrawled'] ?? '',
          'status' => $s['SiteMapStatus'] ?? '',
          'warnings' => (int) ($s['SiteMapWarnings'] ?? 0),
          'errors' => (int) ($s['SiteMapErrors'] ?? 0),
        ];
      }, $sitemaps),
    ];
  }

  // ================================================================
  // SITES CADASTRADOS
  // ================================================================

  /**
   * Lista todos os sites cadastrados na conta Bing Webmaster
   */
  public function getSites(): array
  {
    $result = $this->get('GetUserSites', []);

    if (!$result['success']) {
      return ['success' => false, 'error' => $result['error'], 'sites' => []];
    }

    $sites = $result['data']['d'] ?? [];

    return [
      'success' => true,
      'sites' => array_map(function ($s) {
        return [
          'url' => $s['Url'] ?? '',
          'authenticated' => (bool) ($s['Authenticated'] ?? false),
          'verified' => (bool) ($s['Verified'] ?? false),
        ];
      }, $sites),
    ];
  }

  // ================================================================
  // OPORTUNIDADES DE POSIÇÃO
  // ================================================================

  /**
   * Identifica queries na posição 2-20 — as mais fáceis de subir para top 3
   */
  public function getPositionOpportunities(
    string $siteUrl,
    string $startDate,
    string $endDate,
    int $minImpressions = 5
  ): array {
    $stats = $this->getQueryStats($siteUrl, $startDate, $endDate);

    if (!$stats['success']) {
      return $stats;
    }

    $opportunities = array_filter($stats['queries'], function ($q) use ($minImpressions) {
      return $q['avg_position'] >= 2
        && $q['avg_position'] <= 20
        && $q['impressions'] >= $minImpressions;
    });

    usort($opportunities, function ($a, $b) {
      $aPriority = ($a['avg_position'] <= 10) ? 0 : 1;
      $bPriority = ($b['avg_position'] <= 10) ? 0 : 1;
      if ($aPriority !== $bPriority) {
        return $aPriority - $bPriority;
      }
      return $b['impressions'] - $a['impressions'];
    });

    return [
      'success' => true,
      'opportunities' => array_values($opportunities),
      'total' => count($opportunities),
      'site' => $siteUrl,
      'period' => "{$startDate} → {$endDate}",
    ];
  }

  // ================================================================
  // TESTE DE CONEXÃO
  // ================================================================

  /**
   * Testa conexão com a API do Bing Webmaster
   */
  public function testConnection(): array
  {
    if (empty($this->apiKey)) {
      return [
        'success' => false,
        'error' => 'BING_WMT_API_KEY não configurada no .env',
        'steps' => [
          '1. Acesse https://www.bing.com/webmasters/',
          '2. Vá em Settings (engrenagem) → API Access',
          '3. Clique em "Generate API Key"',
          '4. Copie a key e adicione no .env como BING_WMT_API_KEY=suakey',
        ],
      ];
    }

    $result = $this->getSites();

    return [
      'success' => $result['success'],
      'api_key_prefix' => substr($this->apiKey, 0, 8) . '...',
      'sites_found' => count($result['sites'] ?? []),
      'sites' => $result['sites'] ?? [],
      'error' => $result['error'] ?? null,
    ];
  }

  // ================================================================
  // MÉTODO HTTP BASE
  // ================================================================

  private function get(string $endpoint, array $params = []): array
  {
    if (empty($this->apiKey)) {
      return ['success' => false, 'error' => 'BING_WMT_API_KEY não configurada'];
    }

    $params['apikey'] = $this->apiKey;
    $queryString = '?' . http_build_query($params);
    $url = "{$this->baseUrl}/{$endpoint}{$queryString}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: DoarFazBem-SEO/1.0',
      ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
      return ['success' => false, 'error' => "cURL: {$curlError}"];
    }

    if ($httpCode === 401) {
      return ['success' => false, 'error' => 'API Key inválida ou sem permissão (HTTP 401)'];
    }

    if ($httpCode === 403) {
      return ['success' => false, 'error' => 'Acesso negado — verifique se o site está cadastrado (HTTP 403)'];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
      return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      return ['success' => false, 'error' => 'Resposta inválida (JSON malformado)'];
    }

    return ['success' => true, 'data' => $data, 'http_code' => $httpCode];
  }
}

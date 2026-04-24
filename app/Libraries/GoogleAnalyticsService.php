<?php

namespace App\Libraries;

/**
 * Serviço de integração com Google Analytics 4 Data API
 *
 * Coleta métricas de tráfego, páginas mais acessadas, conversões
 * e fontes de tráfego para o dashboard SEO do DoarFazBem.
 *
 * Usa BetaAnalyticsDataClient da biblioteca oficial do Google.
 *
 * Configuração no .env:
 *   GA4_CREDENTIALS_PATH = writable/credentials/ga4-service-account.json
 *   GA4_PROPERTY_ID = 123456789
 */
class GoogleAnalyticsService
{
  private $client = null;
  private string $propertyId;
  private string $credentialsPath;
  private array $cache = [];
  private int $cacheTtl = 300; // 5 minutos

  public function __construct()
  {
    $this->propertyId = getenv('GA4_PROPERTY_ID') ?: '';
    $this->credentialsPath = getenv('GA4_CREDENTIALS_PATH')
      ?: WRITEPATH . 'credentials/ga4-service-account.json';

    if (class_exists('\Google\Analytics\Data\V1beta\BetaAnalyticsDataClient')) {
      $this->initClient();
    }
  }

  /**
   * Inicializa o BetaAnalyticsDataClient
   */
  private function initClient(): void
  {
    try {
      if (!file_exists($this->credentialsPath)) {
        log_message('warning', 'GA4: Arquivo de credenciais não encontrado: ' . $this->credentialsPath);
        return;
      }

      $this->client = new \Google\Analytics\Data\V1beta\BetaAnalyticsDataClient([
        'credentials' => $this->credentialsPath,
      ]);
    } catch (\Exception $e) {
      log_message('error', 'GA4: Erro ao inicializar client: ' . $e->getMessage());
    }
  }

  // ================================================================
  // MÉTRICAS EM TEMPO REAL
  // ================================================================

  /**
   * Usuários ativos em tempo real (cache 1 min)
   */
  public function getActiveUsers(): array
  {
    $cacheKey = 'ga4_active_users';
    if ($cached = $this->getCache($cacheKey, 60)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado', 'active_users' => 0];
    }

    try {
      $response = $this->client->runRealtimeReport([
        'property' => "properties/{$this->propertyId}",
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'activeUsers']),
        ],
      ]);

      $activeUsers = 0;
      foreach ($response->getRows() as $row) {
        $activeUsers = (int) $row->getMetricValues()[0]->getValue();
      }

      $result = [
        'success' => true,
        'active_users' => $activeUsers,
        'timestamp' => date('H:i:s'),
      ];

      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage(), 'active_users' => 0];
    }
  }

  // ================================================================
  // MÉTRICAS GERAIS
  // ================================================================

  /**
   * Métricas gerais do período (users, sessions, pageviews, bounce rate)
   */
  public function getMetrics(string $period = '7days'): array
  {
    $cacheKey = "ga4_metrics_{$period}";
    if ($cached = $this->getCache($cacheKey)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado'];
    }

    try {
      $dateRange = $this->getDateRange($period);

      $response = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'date']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'sessions']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'screenPageViews']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'bounceRate']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'engagementRate']),
        ],
        'orderBys' => [
          new \Google\Analytics\Data\V1beta\OrderBy([
            'dimension' => new \Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy(['dimension_name' => 'date']),
          ]),
        ],
      ]);

      $daily = [];
      $totals = ['users' => 0, 'sessions' => 0, 'pageviews' => 0, 'bounceRate' => 0, 'engagementRate' => 0];
      $count = 0;

      foreach ($response->getRows() as $row) {
        $date = $row->getDimensionValues()[0]->getValue();
        $values = $row->getMetricValues();

        $day = [
          'date' => substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2),
          'users' => (int) $values[0]->getValue(),
          'sessions' => (int) $values[1]->getValue(),
          'pageviews' => (int) $values[2]->getValue(),
          'bounceRate' => round((float) $values[3]->getValue() * 100, 1),
          'engagementRate' => round((float) $values[4]->getValue() * 100, 1),
        ];

        $daily[] = $day;
        $totals['users'] += $day['users'];
        $totals['sessions'] += $day['sessions'];
        $totals['pageviews'] += $day['pageviews'];
        $totals['bounceRate'] += $day['bounceRate'];
        $totals['engagementRate'] += $day['engagementRate'];
        $count++;
      }

      if ($count > 0) {
        $totals['bounceRate'] = round($totals['bounceRate'] / $count, 1);
        $totals['engagementRate'] = round($totals['engagementRate'] / $count, 1);
      }

      $result = [
        'success' => true,
        'period' => $period,
        'totals' => $totals,
        'daily' => $daily,
      ];

      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  // ================================================================
  // TOP PAGES
  // ================================================================

  /**
   * Páginas mais acessadas ordenadas por pageviews
   */
  public function getTopPages(int $limit = 10, string $period = '7days'): array
  {
    $cacheKey = "ga4_top_pages_{$period}_{$limit}";
    if ($cached = $this->getCache($cacheKey)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado', 'pages' => []];
    }

    try {
      $dateRange = $this->getDateRange($period);

      $response = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'pagePath']),
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'pageTitle']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'screenPageViews']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'engagementRate']),
        ],
        'orderBys' => [
          new \Google\Analytics\Data\V1beta\OrderBy([
            'metric' => new \Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy(['metric_name' => 'screenPageViews']),
            'desc' => true,
          ]),
        ],
        'limit' => $limit,
      ]);

      $pages = [];
      foreach ($response->getRows() as $row) {
        $values = $row->getMetricValues();
        $pages[] = [
          'path' => $row->getDimensionValues()[0]->getValue(),
          'title' => $row->getDimensionValues()[1]->getValue(),
          'pageviews' => (int) $values[0]->getValue(),
          'users' => (int) $values[1]->getValue(),
          'engagementRate' => round((float) $values[2]->getValue() * 100, 1),
        ];
      }

      $result = ['success' => true, 'pages' => $pages, 'period' => $period];
      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage(), 'pages' => []];
    }
  }

  // ================================================================
  // CONVERSÕES
  // ================================================================

  /**
   * Eventos de conversão com taxa
   */
  public function getConversions(string $period = '7days'): array
  {
    $cacheKey = "ga4_conversions_{$period}";
    if ($cached = $this->getCache($cacheKey)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado', 'events' => []];
    }

    try {
      $dateRange = $this->getDateRange($period);

      $response = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'eventName']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'eventCount']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
        ],
        'orderBys' => [
          new \Google\Analytics\Data\V1beta\OrderBy([
            'metric' => new \Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy(['metric_name' => 'eventCount']),
            'desc' => true,
          ]),
        ],
        'limit' => 20,
      ]);

      $events = [];
      foreach ($response->getRows() as $row) {
        $values = $row->getMetricValues();
        $events[] = [
          'event' => $row->getDimensionValues()[0]->getValue(),
          'count' => (int) $values[0]->getValue(),
          'users' => (int) $values[1]->getValue(),
        ];
      }

      $result = ['success' => true, 'events' => $events, 'period' => $period];
      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage(), 'events' => []];
    }
  }

  // ================================================================
  // FONTES DE TRÁFEGO
  // ================================================================

  /**
   * Origens de tráfego (source, medium, campaign)
   */
  public function getTrafficSources(string $period = '7days'): array
  {
    $cacheKey = "ga4_traffic_{$period}";
    if ($cached = $this->getCache($cacheKey)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado', 'sources' => []];
    }

    try {
      $dateRange = $this->getDateRange($period);

      $response = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'sessionSource']),
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'sessionMedium']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'sessions']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'engagementRate']),
        ],
        'orderBys' => [
          new \Google\Analytics\Data\V1beta\OrderBy([
            'metric' => new \Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy(['metric_name' => 'sessions']),
            'desc' => true,
          ]),
        ],
        'limit' => 15,
      ]);

      $sources = [];
      $totalSessions = 0;

      foreach ($response->getRows() as $row) {
        $values = $row->getMetricValues();
        $sessions = (int) $values[0]->getValue();
        $totalSessions += $sessions;
        $sources[] = [
          'source' => $row->getDimensionValues()[0]->getValue(),
          'medium' => $row->getDimensionValues()[1]->getValue(),
          'sessions' => $sessions,
          'users' => (int) $values[1]->getValue(),
          'engagementRate' => round((float) $values[2]->getValue() * 100, 1),
        ];
      }

      // Calcular percentuais
      foreach ($sources as &$s) {
        $s['percentage'] = $totalSessions > 0 ? round(($s['sessions'] / $totalSessions) * 100, 1) : 0;
      }

      $result = ['success' => true, 'sources' => $sources, 'total_sessions' => $totalSessions, 'period' => $period];
      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage(), 'sources' => []];
    }
  }

  // ================================================================
  // DEMOGRÁFICOS
  // ================================================================

  /**
   * Dados demográficos (países e dispositivos)
   */
  public function getDemographics(string $period = '7days'): array
  {
    $cacheKey = "ga4_demographics_{$period}";
    if ($cached = $this->getCache($cacheKey)) {
      return $cached;
    }

    if (!$this->client || !$this->propertyId) {
      return ['success' => false, 'error' => 'GA4 não configurado'];
    }

    try {
      $dateRange = $this->getDateRange($period);

      // Países
      $countryResponse = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'country']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
        ],
        'orderBys' => [
          new \Google\Analytics\Data\V1beta\OrderBy([
            'metric' => new \Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy(['metric_name' => 'totalUsers']),
            'desc' => true,
          ]),
        ],
        'limit' => 10,
      ]);

      $countries = [];
      foreach ($countryResponse->getRows() as $row) {
        $countries[] = [
          'country' => $row->getDimensionValues()[0]->getValue(),
          'users' => (int) $row->getMetricValues()[0]->getValue(),
        ];
      }

      // Dispositivos
      $deviceResponse = $this->client->runReport([
        'property' => "properties/{$this->propertyId}",
        'dateRanges' => [$dateRange],
        'dimensions' => [
          new \Google\Analytics\Data\V1beta\Dimension(['name' => 'deviceCategory']),
        ],
        'metrics' => [
          new \Google\Analytics\Data\V1beta\Metric(['name' => 'totalUsers']),
        ],
      ]);

      $devices = [];
      $totalDeviceUsers = 0;
      foreach ($deviceResponse->getRows() as $row) {
        $users = (int) $row->getMetricValues()[0]->getValue();
        $totalDeviceUsers += $users;
        $devices[] = [
          'device' => $row->getDimensionValues()[0]->getValue(),
          'users' => $users,
        ];
      }
      foreach ($devices as &$d) {
        $d['percentage'] = $totalDeviceUsers > 0 ? round(($d['users'] / $totalDeviceUsers) * 100, 1) : 0;
      }

      $result = [
        'success' => true,
        'countries' => $countries,
        'devices' => $devices,
        'period' => $period,
      ];

      $this->setCache($cacheKey, $result);
      return $result;
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  // ================================================================
  // CACHE + HELPERS
  // ================================================================

  /**
   * Limpa cache específico ou todos os caches GA4
   */
  public function clearCache(?string $specific = null): void
  {
    if ($specific) {
      unset($this->cache[$specific]);
    } else {
      $this->cache = [];
    }
  }

  private function getCache(string $key, ?int $ttl = null): ?array
  {
    $ttl = $ttl ?? $this->cacheTtl;
    if (isset($this->cache[$key]) && (time() - $this->cache[$key]['time']) < $ttl) {
      return $this->cache[$key]['data'];
    }
    return null;
  }

  private function setCache(string $key, array $data): void
  {
    $this->cache[$key] = ['data' => $data, 'time' => time()];
  }

  /**
   * Converte período em DateRange do GA4
   */
  private function getDateRange(string $period): \Google\Analytics\Data\V1beta\DateRange
  {
    $map = [
      'today' => ['startDate' => 'today', 'endDate' => 'today'],
      'yesterday' => ['startDate' => 'yesterday', 'endDate' => 'yesterday'],
      '7days' => ['startDate' => '7daysAgo', 'endDate' => 'today'],
      '30days' => ['startDate' => '30daysAgo', 'endDate' => 'today'],
      '90days' => ['startDate' => '90daysAgo', 'endDate' => 'today'],
    ];

    $range = $map[$period] ?? $map['7days'];

    return new \Google\Analytics\Data\V1beta\DateRange($range);
  }

  /**
   * Fecha conexão do client
   */
  public function __destruct()
  {
    if ($this->client) {
      try {
        $this->client->close();
      } catch (\Exception $e) {
        // Ignora erros ao fechar
      }
    }
  }
}

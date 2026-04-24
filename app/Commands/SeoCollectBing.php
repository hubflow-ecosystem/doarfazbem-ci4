<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\BingWebmasterService;
use App\Libraries\BingWebmasterApiService;
use App\Models\SeoBingAnalyticsModel;
use App\Models\SeoQueryModel;
use App\Models\SeoConfigModel;

/**
 * Integração com Bing: submissão IndexNow + coleta de analytics.
 *
 * Uso:
 *   php spark seo:collect-bing --test      # Testar IndexNow
 *   php spark seo:collect-bing --sitemap   # Enviar sitemap ao Bing
 *   php spark seo:collect-bing --all       # Submeter TODAS as URLs (bulk)
 *   php spark seo:collect-bing --analytics # Coletar analytics do Bing WMT API
 */
class SeoCollectBing extends BaseCommand
{
  protected $group = 'SEO';
  protected $name = 'seo:collect-bing';
  protected $description = 'Submete URLs ao Bing via IndexNow e coleta analytics';
  protected $usage = 'seo:collect-bing [--test] [--sitemap] [--all] [--analytics]';
  protected $options = [
    '--test' => 'Testar configuração do IndexNow',
    '--sitemap' => 'Enviar sitemap.xml ao Bing via ping',
    '--all' => 'Submeter TODAS as URLs (bulk — use na primeira vez)',
    '--analytics' => 'Coletar analytics do Bing WMT API',
  ];

  public function run(array $params)
  {
    CLI::write('============================================', 'cyan');
    CLI::write('  DoarFazBem SEO - Bing Webmaster Tools', 'cyan');
    CLI::write('============================================', 'cyan');
    CLI::newLine();

    if (getenv('BING_API_ENABLED') === 'false') {
      CLI::write('Integração Bing desativada (BING_API_ENABLED=false).', 'yellow');
      return;
    }

    $options = CLI::getOptions();

    // Modo analytics: coletar dados do Bing WMT API
    if (array_key_exists('analytics', $options)) {
      $this->coletarAnalytics();
      return;
    }

    // Modos IndexNow
    try {
      $bing = new BingWebmasterService();
    } catch (\Exception $e) {
      CLI::error('Erro ao inicializar Bing Service: ' . $e->getMessage());
      return;
    }

    if (array_key_exists('test', $options)) {
      $this->testarConexao($bing);
      return;
    }

    if (array_key_exists('sitemap', $options)) {
      $this->enviarSitemap($bing);
      return;
    }

    if (array_key_exists('all', $options)) {
      $this->submeterTodasUrls($bing);
      return;
    }

    // Padrão: mostrar instruções
    CLI::write('IndexNow ativo. Uso:', 'cyan');
    CLI::write('  --test      Testar configuração IndexNow', 'white');
    CLI::write('  --sitemap   Enviar sitemap.xml ao Bing', 'white');
    CLI::write('  --all       Submeter TODAS as URLs (bulk)', 'white');
    CLI::write('  --analytics Coletar analytics do Bing WMT', 'white');
    CLI::newLine();
  }

  private function testarConexao(BingWebmasterService $bing): void
  {
    CLI::write('Testando IndexNow (Bing)...', 'yellow');
    CLI::newLine();

    $resultado = $bing->testConnection();

    if ($resultado['success']) {
      $code = $resultado['http_code'] ?? 0;
      CLI::write('IndexNow configurado corretamente!', 'green');
      CLI::write('Site:          ' . ($resultado['site_url'] ?? 'N/A'), 'white');
      CLI::write('Arquivo chave: ' . ($resultado['key_file'] ?? 'N/A'), 'white');
      CLI::write("Resposta Bing: HTTP {$code}", 'green');
      CLI::newLine();
      CLI::write('Buscadores notificados via IndexNow:', 'cyan');
      CLI::write('  - Bing (Microsoft)', 'white');
      CLI::write('  - Yandex', 'white');
      CLI::write('  - Seznam.cz', 'white');
      CLI::write('  - Naver', 'white');
    } else {
      CLI::error('Falha no IndexNow: ' . ($resultado['error'] ?? 'Erro desconhecido'));
    }
  }

  private function enviarSitemap(BingWebmasterService $bing): void
  {
    $baseUrl = rtrim(getenv('app.baseURL') ?: 'https://doarfazbem.com.br', '/');
    $sitemapUrl = $baseUrl . '/sitemap.xml';

    CLI::write("Enviando sitemap ao Bing: {$sitemapUrl}", 'yellow');

    $result = $bing->submitSitemap($sitemapUrl);

    if ($result['success']) {
      CLI::write('Sitemap enviado com sucesso!', 'green');
    } else {
      CLI::error('Erro: ' . ($result['error'] ?? 'Desconhecido'));
    }
  }

  private function submeterTodasUrls(BingWebmasterService $bing): void
  {
    CLI::write('Coletando TODAS as URLs para submissão ao Bing...', 'yellow');
    CLI::newLine();

    $db = \Config\Database::connect();
    $baseUrl = rtrim(getenv('app.baseURL') ?: 'https://doarfazbem.com.br', '/');
    $urls = [];

    // Blog posts
    if ($db->tableExists('blog_posts')) {
      $posts = $db->table('blog_posts')->select('slug')->where('status', 'published')->get()->getResultArray();
      foreach ($posts as $p) {
        $urls[] = "{$baseUrl}/blog/{$p['slug']}";
      }
      CLI::write('  Blog: ' . count($posts) . ' URLs', 'white');
    }

    // Campanhas
    if ($db->tableExists('campaigns')) {
      $campaigns = $db->table('campaigns')
        ->select('slug')
        ->where('status', 'active')
        ->get()->getResultArray();
      foreach ($campaigns as $c) {
        $urls[] = "{$baseUrl}/campanhas/{$c['slug']}";
      }
      CLI::write('  Campanhas: ' . count($campaigns) . ' URLs', 'white');
    }

    // Rifas
    if ($db->tableExists('raffles')) {
      $raffles = $db->table('raffles')
        ->select('slug')
        ->where('status', 'active')
        ->get()->getResultArray();
      foreach ($raffles as $r) {
        $urls[] = "{$baseUrl}/rifas/{$r['slug']}";
      }
      CLI::write('  Rifas: ' . count($raffles) . ' URLs', 'white');
    }

    // Páginas estáticas
    $staticPages = ['/', '/sobre', '/como-funciona', '/faq', '/contato',
      '/categorias', '/transparencia', '/para-ongs', '/para-empresas'];
    foreach ($staticPages as $page) {
      $urls[] = $baseUrl . $page;
    }
    CLI::write('  Páginas estáticas: ' . count($staticPages) . ' URLs', 'white');

    CLI::newLine();
    $total = count($urls);
    CLI::write("Total de URLs: {$total}", 'cyan');

    if (empty($urls)) {
      CLI::write('Nenhuma URL encontrada.', 'yellow');
      return;
    }

    CLI::write('Submetendo ao Bing via IndexNow...', 'yellow');
    CLI::newLine();

    $result = $bing->submitUrlBatch($urls);

    if ($result['success']) {
      CLI::write('============================================', 'green');
      CLI::write('  Submissão Bulk Finalizada!', 'green');
      CLI::write('============================================', 'green');
      CLI::newLine();
      CLI::write("  URLs submetidas: {$result['submitted']}", 'green');
      CLI::write("  HTTP Code:       {$result['http_code']}", 'white');
    } else {
      CLI::error('Erros na submissão:');
      foreach ($result['errors'] as $err) {
        CLI::error("  - {$err}");
      }
    }

    $configModel = new SeoConfigModel();
    $configModel->setConfig('last_bing_bulk_submit', date('Y-m-d H:i:s'));
    $configModel->setConfig('last_bing_bulk_count', (string) $total);
  }

  /**
   * Coleta analytics do Bing Webmaster Tools API
   */
  private function coletarAnalytics(): void
  {
    CLI::write('Coletando analytics do Bing WMT API...', 'yellow');
    CLI::newLine();

    try {
      $bingApi = new BingWebmasterApiService();
    } catch (\Exception $e) {
      CLI::error('Erro ao inicializar Bing API: ' . $e->getMessage());
      return;
    }

    $siteUrl = getenv('BING_SITE_URL') ?: 'https://doarfazbem.com.br/';
    $startDate = date('Y-m-d', strtotime('-28 days'));
    $endDate = date('Y-m-d', strtotime('-2 days'));

    CLI::write("Site: {$siteUrl}", 'white');
    CLI::write("Período: {$startDate} até {$endDate}", 'white');
    CLI::newLine();

    $result = $bingApi->getQueryStats($siteUrl, $startDate, $endDate);

    if (!$result['success']) {
      CLI::error('Erro: ' . ($result['error'] ?? 'Desconhecido'));
      return;
    }

    $queries = $result['queries'] ?? [];
    CLI::write("Queries encontradas: " . count($queries), 'cyan');

    if (empty($queries)) {
      CLI::write('Nenhuma query retornada.', 'yellow');
      return;
    }

    // Salvar no seo_bing_analytics
    $bingModel = new SeoBingAnalyticsModel();
    $rows = [];
    foreach ($queries as $q) {
      // Classificar zona de oportunidade
      $zone = null;
      if ($q['avg_position'] >= 2 && $q['avg_position'] <= 10) {
        $zone = 'high_priority';
      } elseif ($q['avg_position'] > 10 && $q['avg_position'] <= 20) {
        $zone = 'medium_priority';
      } elseif ($q['avg_position'] > 20) {
        $zone = 'monitor';
      }

      $rows[] = [
        'query' => $q['query'],
        'impressions' => $q['impressions'],
        'clicks' => $q['clicks'],
        'avg_position' => $q['avg_position'],
        'ctr' => $q['ctr'],
        'opportunity_zone' => $zone,
        'period_start' => $startDate,
        'period_end' => $endDate,
      ];
    }

    $resultado = $bingModel->upsertBatch($rows);

    // Também salvar na tabela unificada seo_queries com source=bing
    $queryModel = new SeoQueryModel();
    $queryRows = [];
    foreach ($queries as $q) {
      $queryRows[] = [
        'query' => $q['query'],
        'page_url' => $siteUrl,
        'clicks' => $q['clicks'],
        'impressions' => $q['impressions'],
        'ctr' => $q['ctr'],
        'position' => $q['avg_position'],
        'device' => 'all',
        'source' => 'bing',
        'date' => $endDate,
      ];
    }
    $queryResult = $queryModel->upsertBatch($queryRows);

    CLI::newLine();
    CLI::write('============================================', 'green');
    CLI::write('  Coleta Bing Analytics Finalizada!', 'green');
    CLI::write('============================================', 'green');
    CLI::newLine();
    CLI::write("  seo_bing_analytics: {$resultado['inserted']} inseridos, {$resultado['updated']} atualizados", 'white');
    CLI::write("  seo_queries (bing): {$queryResult['inserted']} inseridos, {$queryResult['updated']} atualizados", 'white');
    CLI::newLine();

    $configModel = new SeoConfigModel();
    $configModel->setConfig('last_bing_collect_date', date('Y-m-d H:i:s'));
  }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\SeoAnalyzerService;
use App\Models\SeoQueryModel;
use App\Models\SeoConfigModel;

/**
 * Analisa dados coletados e identifica oportunidades SEO para o DoarFazBem.
 *
 * Uso:
 *   php spark seo:analyze           # Análise completa
 *   php spark seo:analyze --summary # Apenas resumo dos dados
 */
class SeoAnalyze extends BaseCommand
{
  protected $group = 'SEO';
  protected $name = 'seo:analyze';
  protected $description = 'Analisa dados do GSC e identifica oportunidades SEO';
  protected $usage = 'seo:analyze [--summary]';
  protected $options = [
    '--summary' => 'Mostra apenas resumo dos dados sem criar oportunidades',
  ];

  public function run(array $params)
  {
    CLI::write('============================================', 'cyan');
    CLI::write('  DoarFazBem SEO - Analisador de Oportunidades', 'cyan');
    CLI::write('============================================', 'cyan');
    CLI::newLine();

    $queryModel = new SeoQueryModel();
    $latestDate = $queryModel->latestDate();

    if (!$latestDate) {
      CLI::error('Nenhum dado encontrado. Execute seo:collect primeiro.');
      return;
    }

    CLI::write("Dados mais recentes: {$latestDate}", 'white');
    CLI::newLine();

    // Modo resumo
    if (array_key_exists('summary', CLI::getOptions())) {
      $this->mostrarResumo($queryModel);
      return;
    }

    CLI::write('Executando análise completa...', 'yellow');
    CLI::newLine();

    try {
      $analyzer = new SeoAnalyzerService();
      $resultado = $analyzer->analyzeAll();
    } catch (\Exception $e) {
      CLI::error('Erro na análise: ' . $e->getMessage());
      return;
    }

    if (!$resultado['success']) {
      CLI::error('Análise falhou.');
      return;
    }

    CLI::write('============================================', 'green');
    CLI::write('  Análise Finalizada!', 'green');
    CLI::write('============================================', 'green');
    CLI::newLine();

    CLI::write("Tempo de análise: {$resultado['analysis_time']}s", 'white');
    CLI::newLine();

    CLI::write('Oportunidades encontradas por tipo:', 'cyan');
    $breakdown = $resultado['breakdown'];
    CLI::write("  Content Gap:       {$breakdown['content_gaps']['count']}", 'white');
    CLI::write("  Low CTR:           {$breakdown['low_ctr']['count']}", 'white');
    CLI::write("  Top Position:      {$breakdown['top_position']['count']}", 'white');
    CLI::write("  Striking Distance: {$breakdown['striking_distance']['count']}", 'white');
    CLI::write("  Enrichment:        {$breakdown['enrichment']['count']}", 'white');
    CLI::newLine();

    CLI::write("Total encontradas:  {$resultado['total_found']}", 'white');
    CLI::write("Novas salvas:       {$resultado['new_saved']}", 'green');
    CLI::newLine();

    // Top oportunidades
    if ($resultado['total_found'] > 0) {
      CLI::write('Top 10 oportunidades:', 'cyan');
      CLI::newLine();

      $allItems = [];
      foreach ($breakdown as $data) {
        foreach ($data['items'] as $item) {
          $allItems[] = $item;
        }
      }

      usort($allItems, fn($a, $b) => ($b['priority_score'] ?? 0) - ($a['priority_score'] ?? 0));

      $top = array_slice($allItems, 0, 10);
      foreach ($top as $i => $item) {
        $num = $i + 1;
        $score = $item['priority_score'] ?? 0;
        $type = $item['type'] ?? '?';
        $kw = $item['keyword'] ?? '?';
        $imp = $item['impressions'] ?? 0;
        $pos = $item['current_position'] ?? 0;
        CLI::write("  {$num}. [{$type}] \"{$kw}\" - Score: {$score} | Imp: {$imp} | Pos: {$pos}", 'white');
      }
    }

    CLI::newLine();

    $configModel = new SeoConfigModel();
    $configModel->setConfig('last_analyze_date', date('Y-m-d H:i:s'));
  }

  private function mostrarResumo(SeoQueryModel $queryModel): void
  {
    CLI::write('Resumo dos dados coletados (28 dias):', 'cyan');
    CLI::newLine();

    $totalQueries = $queryModel->countUniqueQueries(28);
    $totalImpressions = $queryModel->totalImpressions(28);
    $totalClicks = $queryModel->totalClicks(28);
    $avgCtr = $queryModel->avgCtr(28);
    $avgPosition = $queryModel->avgPosition(28);

    CLI::write("  Queries únicas:     {$totalQueries}", 'white');
    CLI::write("  Total impressões:   " . number_format($totalImpressions), 'white');
    CLI::write("  Total cliques:      " . number_format($totalClicks), 'white');
    CLI::write("  CTR médio:          {$avgCtr}%", 'white');
    CLI::write("  Posição média:      {$avgPosition}", 'white');
    CLI::newLine();

    CLI::write('Top 10 queries por impressões:', 'cyan');
    $topQueries = $queryModel->topQueries(28, 10);
    foreach ($topQueries as $i => $q) {
      $num = $i + 1;
      $imp = number_format((float) ($q['total_impressions'] ?? 0));
      $clk = $q['total_clicks'] ?? 0;
      $pos = round((float) ($q['avg_position'] ?? 0), 1);
      CLI::write("  {$num}. \"{$q['query']}\" - Imp: {$imp} | Cliques: {$clk} | Pos: {$pos}", 'white');
    }

    CLI::newLine();

    CLI::write('Top 10 páginas por cliques:', 'cyan');
    $topPages = $queryModel->topPages(28, 10);
    foreach ($topPages as $i => $p) {
      $num = $i + 1;
      $clk = $p['total_clicks'] ?? 0;
      $imp = number_format((float) ($p['total_impressions'] ?? 0));
      $url = $p['page_url'] ?? '?';
      if (strlen($url) > 60) {
        $url = substr($url, 0, 57) . '...';
      }
      CLI::write("  {$num}. {$url} - Cliques: {$clk} | Imp: {$imp}", 'white');
    }

    CLI::newLine();
  }
}

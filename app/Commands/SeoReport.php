<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SeoQueryModel;
use App\Models\SeoOpportunityModel;
use App\Models\SeoActionLogModel;
use App\Models\SeoConfigModel;

/**
 * Gera relatório SEO completo no terminal para o DoarFazBem.
 *
 * Uso:
 *   php spark seo:report           # Relatório dos últimos 28 dias
 *   php spark seo:report --days 7  # Relatório dos últimos 7 dias
 */
class SeoReport extends BaseCommand
{
  protected $group = 'SEO';
  protected $name = 'seo:report';
  protected $description = 'Gera relatório resumido de SEO no terminal';
  protected $usage = 'seo:report [--days <dias>]';
  protected $options = [
    '--days' => 'Período do relatório em dias (padrão: 28)',
  ];

  public function run(array $params)
  {
    $dias = (int) (CLI::getOption('days') ?: 28);

    CLI::newLine();
    CLI::write('============================================', 'cyan');
    CLI::write('  DoarFazBem SEO - Relatório Completo', 'cyan');
    CLI::write('============================================', 'cyan');
    CLI::newLine();
    CLI::write("  Período: últimos {$dias} dias", 'white');
    CLI::newLine();

    $queryModel = new SeoQueryModel();
    $opportunityModel = new SeoOpportunityModel();
    $actionLogModel = new SeoActionLogModel();
    $configModel = new SeoConfigModel();

    $this->secaoMetricasGerais($queryModel, $dias);
    $this->secaoTopQueries($queryModel, $dias);
    $this->secaoTopPaginas($queryModel, $dias);
    $this->secaoOportunidades($opportunityModel);
    $this->secaoAcoesHoje($actionLogModel);
    $this->secaoCustoIA($actionLogModel);
    $this->secaoConfig($configModel);

    CLI::newLine();
    CLI::write('============================================', 'cyan');
    CLI::write('  Gerado em ' . date('d/m/Y H:i:s'), 'white');
    CLI::write('============================================', 'cyan');
    CLI::newLine();
  }

  private function secaoMetricasGerais(SeoQueryModel $model, int $dias): void
  {
    CLI::write('--- MÉTRICAS GERAIS ---', 'green');

    $totalClicks = $model->totalClicks($dias);
    $totalImpressions = $model->totalImpressions($dias);
    $avgCtr = $model->avgCtr($dias);
    $avgPosition = $model->avgPosition($dias);
    $uniqueQueries = $model->countUniqueQueries($dias);
    $latestDate = $model->latestDate();

    CLI::write("  Queries únicas:      " . number_format($uniqueQueries), 'white');
    CLI::write("  Total impressões:    " . number_format($totalImpressions), 'white');
    CLI::write("  Total cliques:       " . number_format($totalClicks), 'cyan');
    CLI::write("  CTR médio:           {$avgCtr}%", $avgCtr >= 5 ? 'green' : ($avgCtr >= 2 ? 'yellow' : 'red'));
    CLI::write("  Posição média:       {$avgPosition}", $avgPosition <= 10 ? 'green' : ($avgPosition <= 20 ? 'yellow' : 'red'));
    CLI::write("  Dados mais recentes: " . ($latestDate ?? 'Nenhum'), 'white');
    CLI::newLine();
  }

  private function secaoTopQueries(SeoQueryModel $model, int $dias): void
  {
    CLI::write('--- TOP 10 QUERIES POR IMPRESSÕES ---', 'green');

    $topQueries = $model->topQueries($dias, 10);

    if (empty($topQueries)) {
      CLI::write('  Nenhum dado disponível.', 'yellow');
      CLI::newLine();
      return;
    }

    foreach ($topQueries as $i => $q) {
      $num = $i + 1;
      $imp = number_format((float) ($q['total_impressions'] ?? 0));
      $clk = $q['total_clicks'] ?? 0;
      $pos = round((float) ($q['avg_position'] ?? 0), 1);
      $ctr = round((float) ($q['avg_ctr'] ?? 0), 2);
      CLI::write("  {$num}. \"{$q['query']}\" - Imp: {$imp} | Cliques: {$clk} | CTR: {$ctr}% | Pos: {$pos}", 'white');
    }
    CLI::newLine();
  }

  private function secaoTopPaginas(SeoQueryModel $model, int $dias): void
  {
    CLI::write('--- TOP 5 PÁGINAS POR CLIQUES ---', 'green');

    $topPages = $model->topPages($dias, 5);

    if (empty($topPages)) {
      CLI::write('  Nenhum dado disponível.', 'yellow');
      CLI::newLine();
      return;
    }

    foreach ($topPages as $i => $p) {
      $num = $i + 1;
      $clk = $p['total_clicks'] ?? 0;
      $imp = number_format((float) ($p['total_impressions'] ?? 0));
      $url = $p['page_url'] ?? '?';
      if (strlen($url) > 60) {
        $url = substr($url, 0, 57) . '...';
      }
      CLI::write("  {$num}. {$url}", 'cyan');
      CLI::write("     Cliques: {$clk} | Impressões: {$imp}", 'white');
    }
    CLI::newLine();
  }

  private function secaoOportunidades(SeoOpportunityModel $model): void
  {
    CLI::write('--- OPORTUNIDADES PENDENTES ---', 'green');

    $typeCounts = $model->countByType();

    if (empty($typeCounts)) {
      CLI::write('  Nenhuma oportunidade pendente.', 'yellow');
      CLI::write('  Execute seo:analyze para identificar oportunidades.', 'white');
      CLI::newLine();
      return;
    }

    $total = 0;
    foreach ($typeCounts as $tc) {
      $tipo = $tc['type'] ?? '?';
      $qtd = (int) ($tc['total'] ?? 0);
      $total += $qtd;
      CLI::write("  {$tipo}: {$qtd}", 'white');
    }
    CLI::write("  Total: {$total}", 'cyan');
    CLI::newLine();
  }

  private function secaoAcoesHoje(SeoActionLogModel $model): void
  {
    CLI::write('--- AÇÕES EXECUTADAS HOJE ---', 'green');

    $todayActions = $model->todayActions();

    if (empty($todayActions)) {
      CLI::write('  Nenhuma ação executada hoje.', 'yellow');
      CLI::newLine();
      return;
    }

    $sucesso = 0;
    $falha = 0;
    foreach ($todayActions as $a) {
      if (!empty($a['success'])) $sucesso++;
      else $falha++;
    }

    CLI::write("  Total: " . count($todayActions) . " | Sucesso: {$sucesso} | Falha: {$falha}", 'white');
    CLI::newLine();
  }

  private function secaoCustoIA(SeoActionLogModel $model): void
  {
    CLI::write('--- CUSTO IA ---', 'green');

    $custoMes = $model->totalAiCost(30);
    $custoTotal = $model->totalAiCost(365);

    CLI::write("  Custo este mês (USD): \${$custoMes}", 'white');
    CLI::write("  Custo total (USD):    \${$custoTotal}", 'white');
    CLI::newLine();
  }

  private function secaoConfig(SeoConfigModel $model): void
  {
    CLI::write('--- STATUS DO MOTOR ---', 'green');

    $lastCollect = $model->getConfig('last_collect_date', 'Nunca');
    $lastAnalyze = $model->getConfig('last_analyze_date', 'Nunca');
    $lastExecute = $model->getConfig('last_execute_date', 'Nunca');
    $enabled = $model->isEnabled() ? 'Ativo' : 'Inativo';

    CLI::write("  Motor SEO:        {$enabled}", $model->isEnabled() ? 'green' : 'red');
    CLI::write("  Última coleta:    {$lastCollect}", 'white');
    CLI::write("  Última análise:   {$lastAnalyze}", 'white');
    CLI::write("  Última execução:  {$lastExecute}", 'white');
    CLI::newLine();
  }
}

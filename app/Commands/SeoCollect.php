<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GoogleSearchConsoleService;
use App\Models\SeoQueryModel;
use App\Models\SeoConfigModel;

/**
 * Coleta dados do Google Search Console para o DoarFazBem.
 *
 * Uso:
 *   php spark seo:collect           # Coletar últimos 28 dias
 *   php spark seo:collect --days 7  # Coletar últimos 7 dias
 *   php spark seo:collect --test    # Testar conexão com GSC
 */
class SeoCollect extends BaseCommand
{
  protected $group = 'SEO';
  protected $name = 'seo:collect';
  protected $description = 'Coleta dados de queries do Google Search Console';
  protected $usage = 'seo:collect [--days <dias>] [--test]';
  protected $options = [
    '--days' => 'Número de dias para coletar (padrão: 28)',
    '--test' => 'Testar conexão com Google Search Console',
  ];

  public function run(array $params)
  {
    CLI::write('============================================', 'cyan');
    CLI::write('  DoarFazBem SEO - Coletor Search Console', 'cyan');
    CLI::write('============================================', 'cyan');
    CLI::newLine();

    try {
      $gsc = new GoogleSearchConsoleService();
    } catch (\Exception $e) {
      CLI::error('Erro ao inicializar Google Search Console Service:');
      CLI::error($e->getMessage());
      return;
    }

    // Modo teste
    if (array_key_exists('test', CLI::getOptions())) {
      $this->testarConexao($gsc);
      return;
    }

    $dias = (int) (CLI::getOption('days') ?: 28);

    if ($dias < 1 || $dias > 90) {
      CLI::error('O parâmetro --days deve estar entre 1 e 90.');
      return;
    }

    CLI::write("Coletando dados dos últimos {$dias} dias...", 'yellow');
    CLI::newLine();

    $queryModel = new SeoQueryModel();
    $configModel = new SeoConfigModel();

    $totalInseridos = 0;
    $totalAtualizados = 0;
    $totalErros = 0;

    // GSC tem delay de ~3 dias
    $dataFim = date('Y-m-d', strtotime('-3 days'));
    $dataInicio = date('Y-m-d', strtotime("-{$dias} days"));

    CLI::write("Período: {$dataInicio} até {$dataFim}", 'white');
    CLI::newLine();

    $dataAtual = $dataInicio;
    $diasProcessados = 0;
    $totalDias = (int) ((strtotime($dataFim) - strtotime($dataInicio)) / 86400) + 1;

    while (strtotime($dataAtual) <= strtotime($dataFim)) {
      $diasProcessados++;
      CLI::showProgress($diasProcessados, $totalDias);

      try {
        $result = $gsc->fetchQueriesByDay($dataAtual);

        if (!$result['success'] || empty($result['queries'])) {
          $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 day'));
          continue;
        }

        $resultado = $queryModel->upsertBatch($result['queries']);
        $totalInseridos += $resultado['inserted'] ?? 0;
        $totalAtualizados += $resultado['updated'] ?? 0;
      } catch (\Exception $e) {
        $totalErros++;
        CLI::newLine();
        CLI::write("  Erro no dia {$dataAtual}: {$e->getMessage()}", 'red');
      }

      $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 day'));
    }

    CLI::showProgress(false);
    CLI::newLine();
    CLI::newLine();

    $configModel->setConfig('last_collect_date', date('Y-m-d H:i:s'));

    CLI::write('============================================', 'green');
    CLI::write('  Coleta Finalizada!', 'green');
    CLI::write('============================================', 'green');
    CLI::newLine();
    CLI::write("  Dias processados:      {$diasProcessados}", 'white');
    CLI::write("  Registros inseridos:   {$totalInseridos}", 'green');
    CLI::write("  Registros atualizados: {$totalAtualizados}", 'cyan');

    if ($totalErros > 0) {
      CLI::write("  Erros encontrados:     {$totalErros}", 'red');
    }

    CLI::newLine();
  }

  private function testarConexao(GoogleSearchConsoleService $gsc): void
  {
    CLI::write('Testando conexão com Google Search Console...', 'yellow');
    CLI::newLine();

    try {
      $resultado = $gsc->testConnection();

      if (!empty($resultado['success'])) {
        CLI::write('Conexão bem-sucedida!', 'green');
        CLI::write('Site: ' . ($resultado['site_url'] ?? 'N/A'), 'white');
        CLI::write('Credenciais: ' . ($resultado['credentials'] ?? 'N/A'), 'white');
        CLI::write('Queries retornadas: ' . ($resultado['sample_rows'] ?? 0), 'white');
      } else {
        CLI::error('Falha na conexão:');
        CLI::error($resultado['error'] ?? 'Erro desconhecido');
      }
    } catch (\Exception $e) {
      CLI::error('Falha na conexão: ' . $e->getMessage());
      CLI::newLine();
      CLI::write('Verifique:', 'yellow');
      CLI::write('  1. Arquivo JSON de credenciais em writable/credentials/', 'white');
      CLI::write('  2. Service Account adicionada no Search Console como proprietária', 'white');
      CLI::write('  3. Site verificado no Google Search Console', 'white');
    }
  }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SeoOpportunityModel;
use App\Models\SeoActionLogModel;
use App\Models\SeoConfigModel;
use App\Libraries\GrokService;

/**
 * Executa ações SEO nas oportunidades identificadas para o DoarFazBem.
 *
 * Usa GrokService (Groq gratuito) para gerar meta tags otimizadas
 * no contexto de crowdfunding, doações e solidariedade.
 *
 * Uso:
 *   php spark seo:execute              # Executar pendentes
 *   php spark seo:execute --dry-run    # Preview sem executar
 *   php spark seo:execute --limit 5    # Processar apenas 5
 */
class SeoExecute extends BaseCommand
{
  protected $group = 'SEO';
  protected $name = 'seo:execute';
  protected $description = 'Executa ações SEO nas oportunidades identificadas';
  protected $usage = 'seo:execute [--dry-run] [--limit <n>]';
  protected $options = [
    '--dry-run' => 'Preview das ações sem executar',
    '--limit' => 'Número máximo de oportunidades a processar',
  ];

  private int $maxBlogPostsPerDay = 5;
  private int $maxEnrichmentsPerDay = 9999;
  private int $maxTopPositionsPerDay = 9999;
  private int $blogPostsHoje = 0;
  private int $enrichmentsHoje = 0;
  private int $topPositionsHoje = 0;
  private GrokService $grok;

  public function run(array $params)
  {
    CLI::write('============================================', 'cyan');
    CLI::write('  DoarFazBem SEO - Executor de Ações', 'cyan');
    CLI::write('============================================', 'cyan');
    CLI::newLine();

    $dryRun = array_key_exists('dry-run', CLI::getOptions());
    $limite = (int) (CLI::getOption('limit') ?: 50);

    if ($dryRun) {
      CLI::write('  MODO DRY-RUN: nenhuma ação será executada', 'yellow');
      CLI::newLine();
    }

    $this->grok = new GrokService();

    $opportunityModel = new SeoOpportunityModel();
    $actionLogModel = new SeoActionLogModel();
    $configModel = new SeoConfigModel();

    // Carregar limites diários
    $this->maxBlogPostsPerDay = (int) $configModel->getConfig('max_articles_per_day', 5);
    $this->maxEnrichmentsPerDay = (int) $configModel->getConfig('max_enrichments_per_day', 9999);
    $this->maxTopPositionsPerDay = (int) $configModel->getConfig('max_top_positions_per_day', 9999);

    // Contar ações já executadas hoje
    $todayActions = $actionLogModel->todayActions();
    foreach ($todayActions as $action) {
      if ($action['action_type'] === 'create_content') {
        $this->blogPostsHoje++;
      }
      if (in_array($action['action_type'], ['enrich_content', 'update_meta'])) {
        if (($action['target_type'] ?? '') === 'top_position') {
          $this->topPositionsHoje++;
        } else {
          $this->enrichmentsHoje++;
        }
      }
    }

    CLI::write('Limites diários:', 'yellow');
    CLI::write("  Blog posts:    {$this->blogPostsHoje}/{$this->maxBlogPostsPerDay}", 'white');
    CLI::write("  Top positions: {$this->topPositionsHoje}/{$this->maxTopPositionsPerDay}", 'white');
    CLI::write("  Enrichments:   {$this->enrichmentsHoje}/{$this->maxEnrichmentsPerDay}", 'white');
    CLI::newLine();

    $oportunidades = $opportunityModel->getPending($limite);

    if (empty($oportunidades)) {
      CLI::write('Nenhuma oportunidade pendente encontrada.', 'yellow');
      CLI::write('Execute seo:analyze primeiro.', 'white');
      return;
    }

    CLI::write('Oportunidades a processar: ' . count($oportunidades), 'white');
    CLI::newLine();

    $totalSucesso = 0;
    $totalErros = 0;
    $totalIgnoradas = 0;

    foreach ($oportunidades as $idx => $opp) {
      $num = $idx + 1;
      $tipo = $opp['type'] ?? '';
      $targetType = $opp['target_type'] ?? 'meta_only';
      $keyword = $opp['keyword'] ?? '';
      $pageUrl = $opp['current_page_url'] ?? '';
      $score = $opp['priority_score'] ?? 0;
      $oppId = (int) ($opp['id'] ?? 0);

      CLI::write("#{$num} [{$tipo}] \"{$keyword}\"", 'cyan');
      CLI::write("   URL: {$pageUrl} | Score: {$score} | Target: {$targetType}", 'white');

      if ($this->excedeuLimite($tipo)) {
        CLI::write('   Limite diário atingido. Pulando.', 'yellow');
        $totalIgnoradas++;
        continue;
      }

      if ($dryRun) {
        $acao = $this->definirAcao($tipo, $targetType);
        CLI::write("   Ação planejada: {$acao}", 'yellow');
        CLI::newLine();
        continue;
      }

      try {
        $resultado = $this->executarAcao($opp);

        if ($resultado['sucesso']) {
          $totalSucesso++;

          $opportunityModel->markExecuted(
            $oppId,
            $resultado['action_type'] ?? 'meta_update',
            $resultado['target_id'] ?? null,
            $resultado['target_url'] ?? null
          );

          $actionLogModel->logAction([
            'opportunity_id' => $oppId,
            'action_type' => $resultado['action_type'] ?? 'meta_update',
            'target_type' => $targetType,
            'keyword' => $keyword,
            'target_url' => $pageUrl,
            'after_state' => $resultado['detalhes'] ?? [],
            'ai_model' => 'groq-llama-3.3-70b',
            'ai_tokens_used' => 0,
            'ai_cost_usd' => 0,
            'success' => 1,
          ]);

          CLI::write('   Executado com sucesso!', 'green');
          $this->incrementarContador($tipo);
        } else {
          $totalErros++;
          CLI::write("   Falha: {$resultado['erro']}", 'red');

          $actionLogModel->logAction([
            'opportunity_id' => $oppId,
            'action_type' => $resultado['action_type'] ?? 'unknown',
            'target_type' => $targetType,
            'keyword' => $keyword,
            'target_url' => $pageUrl,
            'success' => 0,
            'error_message' => $resultado['erro'] ?? 'Erro desconhecido',
          ]);
        }
      } catch (\Exception $e) {
        $totalErros++;
        CLI::write("   Erro: {$e->getMessage()}", 'red');
      }

      CLI::newLine();
    }

    if (!$dryRun) {
      $configModel->setConfig('last_execute_date', date('Y-m-d H:i:s'));
    }

    CLI::newLine();
    CLI::write('============================================', 'green');
    CLI::write('  Execução Finalizada!', 'green');
    CLI::write('============================================', 'green');
    CLI::newLine();
    CLI::write("  Total processadas:  " . count($oportunidades), 'white');
    CLI::write("  Sucesso:            {$totalSucesso}", 'green');
    CLI::write("  Erros:              {$totalErros}", $totalErros > 0 ? 'red' : 'white');
    CLI::write("  Ignoradas (limite): {$totalIgnoradas}", $totalIgnoradas > 0 ? 'yellow' : 'white');
    CLI::newLine();
  }

  private function excedeuLimite(string $tipo): bool
  {
    return match ($tipo) {
      'content_gap' => $this->blogPostsHoje >= $this->maxBlogPostsPerDay,
      'top_position' => $this->topPositionsHoje >= $this->maxTopPositionsPerDay,
      'enrichment', 'striking_distance' => $this->enrichmentsHoje >= $this->maxEnrichmentsPerDay,
      default => false,
    };
  }

  private function incrementarContador(string $tipo): void
  {
    match ($tipo) {
      'content_gap' => $this->blogPostsHoje++,
      'top_position' => $this->topPositionsHoje++,
      'enrichment', 'striking_distance' => $this->enrichmentsHoje++,
      default => null,
    };
  }

  private function definirAcao(string $tipo, string $targetType): string
  {
    return match ($tipo) {
      'top_position' => "Otimizar CTR da página top 3 ({$targetType})",
      'low_ctr' => "Gerar meta title + description para melhorar CTR ({$targetType})",
      'enrichment', 'striking_distance' => "Gerar meta tags otimizadas ({$targetType})",
      'content_gap' => "Sugerir novo conteúdo ({$targetType})",
      default => "Ação não definida para '{$tipo}'",
    };
  }

  /**
   * Executa a ação SEO — gera meta tags otimizadas via IA
   * com contexto de crowdfunding/doações/solidariedade
   */
  private function executarAcao(array $opp): array
  {
    $keyword = $opp['keyword'] ?? '';
    $pageUrl = $opp['current_page_url'] ?? '';
    $targetType = $opp['target_type'] ?? 'meta_only';
    $tipo = $opp['type'] ?? 'striking_distance';
    $posicao = round((float) ($opp['current_position'] ?? 10), 1);
    $impressoes = (int) ($opp['impressions'] ?? 0);
    $ctr = round((float) ($opp['current_ctr'] ?? 0), 2);

    // Contexto adaptado para DoarFazBem (crowdfunding/doações)
    $contexto = match ($targetType) {
      'campaign' => 'uma página de campanha de doação no site DoarFazBem — plataforma de crowdfunding solidário',
      'raffle' => 'uma página de rifa solidária no site DoarFazBem — plataforma de crowdfunding e sorteios beneficentes',
      'blog' => 'um artigo do blog do DoarFazBem sobre solidariedade, doações e impacto social',
      'page' => 'uma página institucional do DoarFazBem — plataforma de doações e crowdfunding',
      default => 'uma página do site DoarFazBem — plataforma de crowdfunding solidário',
    };

    $instrucaoEspecifica = match ($tipo) {
      'top_position' => "ATENÇÃO: Esta página já está na POSIÇÃO {$posicao} do Google com {$impressoes} impressões e CTR de {$ctr}%. Maximize o CTR — o title deve ser irresistível, emocional e despertar empatia e urgência para clicar.",
      'striking_distance' => "Esta página está na POSIÇÃO {$posicao}. O objetivo é subir para o top 3 com um title que contenha a keyword exata e transmita confiança e impacto social.",
      'low_ctr' => "Esta página está na POSIÇÃO {$posicao} mas o CTR de {$ctr}% está abaixo do esperado. Crie um title que gere empatia e curiosidade para mais cliques.",
      default => "Página na posição {$posicao} do Google.",
    };

    $prompt = <<<PROMPT
Você é um especialista em SEO para plataformas de doação e crowdfunding solidário no Brasil.

{$instrucaoEspecifica}

Gere um meta title e meta description otimizados para {$contexto}.

Query de busca do Google: "{$keyword}"
URL da página: {$pageUrl}

Regras:
1. Meta title: máximo 60 caracteres, inclua a palavra-chave principal
2. Meta description: máximo 155 caracteres, inclua call-to-action emocional
3. Use linguagem que transmita solidariedade, confiança e impacto real
4. Inclua "DoarFazBem" quando possível — é uma marca de confiança
5. Gere empatia e incentive o clique com urgência social
6. Use português do Brasil

Responda em JSON:
{"title": "Meta title aqui", "description": "Meta description aqui"}
PROMPT;

    $system = 'Especialista SEO de crowdfunding solidário. Retorne APENAS JSON válido, sem texto adicional.';

    $metaTags = $this->grok->generateJson($prompt, $system, 400);

    if (empty($metaTags['title']) || empty($metaTags['description'])) {
      return [
        'sucesso' => false,
        'action_type' => 'meta_update',
        'erro' => 'Resposta inválida ou campos ausentes da IA',
      ];
    }

    // Salvar meta tags geradas na oportunidade
    $opportunityModel = new SeoOpportunityModel();
    $opportunityModel->update($opp['id'], [
      'ai_suggested_title' => $metaTags['title'],
      'ai_suggested_meta' => $metaTags['description'],
    ]);

    // Auto-aplicar em seo_page_meta se a tabela existir
    if (!empty($pageUrl)) {
      try {
        $db = db_connect();
        if ($db->tableExists('seo_page_meta')) {
          $canonicalUrl = rtrim($pageUrl, '/');
          $existing = $db->table('seo_page_meta')
            ->where('canonical_url', $canonicalUrl)
            ->get()->getRowArray();

          if ($existing) {
            $db->table('seo_page_meta')
              ->where('canonical_url', $canonicalUrl)
              ->update([
                'meta_title' => $metaTags['title'],
                'meta_description' => $metaTags['description'],
                'ai_generated' => 1,
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
              ]);
          } else {
            $db->table('seo_page_meta')->insert([
              'canonical_url' => $canonicalUrl,
              'page_type' => $targetType,
              'meta_title' => $metaTags['title'],
              'meta_description' => $metaTags['description'],
              'ai_generated' => 1,
              'is_active' => 1,
              'created_at' => date('Y-m-d H:i:s'),
              'updated_at' => date('Y-m-d H:i:s'),
            ]);
          }
        }
      } catch (\Throwable $e) {
        // Se a tabela não existir, só armazena na oportunidade
      }
    }

    return [
      'sucesso' => true,
      'action_type' => 'meta_update',
      'detalhes' => $metaTags,
    ];
  }
}

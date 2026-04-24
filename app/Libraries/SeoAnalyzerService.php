<?php

namespace App\Libraries;

use App\Models\SeoQueryModel;
use App\Models\SeoOpportunityModel;
use App\Models\SeoConfigModel;

/**
 * SeoAnalyzerService — Motor de Análise SEO do DoarFazBem
 *
 * Analisa dados do Search Console e identifica oportunidades SEO
 * automaticamente, classificando por prioridade e tipo de conteúdo.
 *
 * Tipos de oportunidade detectados:
 * - Content Gaps: queries com muitas impressões mas sem posição relevante
 * - Low CTR: páginas bem posicionadas mas com CTR abaixo do esperado
 * - Striking Distance: queries próximas do top 3 (posição 4-20)
 * - Top Position: páginas já no top 3 que podem melhorar CTR
 * - Enrichment: páginas existentes que podem ser melhoradas
 *
 * ADAPTAÇÃO CRÍTICA do MediLife:
 * - Target types: blog, campaign, raffle, page, meta_only
 *   (vs MediLife: cid, cid11, symptom, bulario, blog)
 * - classifyTargetType() reescrito para URLs e termos de crowdfunding/doações
 * - Scoring permanece igual (0-1000: 40% volume, 30% proximidade top3, 20% CTR gap, 10% bônus)
 */
class SeoAnalyzerService
{
  private SeoQueryModel $queryModel;
  private SeoOpportunityModel $opportunityModel;
  private SeoConfigModel $configModel;
  private array $thresholds = [];

  public function __construct()
  {
    $this->queryModel = new SeoQueryModel();
    $this->opportunityModel = new SeoOpportunityModel();
    $this->configModel = new SeoConfigModel();
    $this->loadThresholds();
  }

  /**
   * Carrega thresholds de configuração do banco
   */
  private function loadThresholds(): void
  {
    $this->thresholds = [
      'min_impressions_content_gap' => (int) $this->configModel->getConfig('min_impressions_content_gap', 20),
      'min_impressions_low_ctr' => (int) $this->configModel->getConfig('min_impressions_low_ctr', 50),
      'striking_distance_min' => (int) $this->configModel->getConfig('striking_distance_min', 4),
      'striking_distance_max' => (int) $this->configModel->getConfig('striking_distance_max', 20),
      'target_ctr' => (float) $this->configModel->getConfig('target_ctr', 5.0),
    ];
  }

  /**
   * Análise principal — executa todas as análises e retorna resumo
   */
  public function analyzeAll(): array
  {
    $startTime = microtime(true);

    $contentGaps = $this->findContentGaps();
    $lowCtr = $this->findLowCtrPages();
    $topPosition = $this->findTopPositionOpportunities();
    $strikingDistance = $this->findStrikingDistance();
    $enrichment = $this->findEnrichmentOpportunities();

    $elapsed = round(microtime(true) - $startTime, 2);

    $totalFound = count($contentGaps) + count($lowCtr) + count($topPosition)
      + count($strikingDistance) + count($enrichment);

    $savedCount = 0;
    $allOpportunities = array_merge($contentGaps, $lowCtr, $topPosition, $strikingDistance, $enrichment);
    foreach ($allOpportunities as $opp) {
      if (!empty($opp['saved'])) {
        $savedCount++;
      }
    }

    return [
      'success' => true,
      'analysis_time' => $elapsed,
      'total_found' => $totalFound,
      'new_saved' => $savedCount,
      'thresholds_used' => $this->thresholds,
      'breakdown' => [
        'content_gaps' => ['count' => count($contentGaps), 'items' => $contentGaps],
        'low_ctr' => ['count' => count($lowCtr), 'items' => $lowCtr],
        'top_position' => ['count' => count($topPosition), 'items' => $topPosition],
        'striking_distance' => ['count' => count($strikingDistance), 'items' => $strikingDistance],
        'enrichment' => ['count' => count($enrichment), 'items' => $enrichment],
      ],
      'analyzed_at' => date('Y-m-d H:i:s'),
    ];
  }

  /**
   * 1. Content Gaps — queries com muitas impressões mas posição > 20
   */
  private function findContentGaps(): array
  {
    $minImpressions = $this->thresholds['min_impressions_content_gap'];

    $queries = $this->queryModel
      ->where('impressions >=', $minImpressions)
      ->where('position >', 20)
      ->orderBy('impressions', 'DESC')
      ->findAll();

    $results = [];

    foreach ($queries as $query) {
      $targetType = $this->classifyTargetType($query['page_url'] ?? null, $query['query']);
      if ($targetType === 'unknown') {
        $targetType = 'blog';
      }

      $priorityScore = $this->calculatePriorityScore([
        'type' => 'content_gap',
        'impressions' => (int) $query['impressions'],
        'position' => (float) $query['position'],
        'ctr' => (float) $query['ctr'],
      ]);

      $potentialClicks = $this->estimatePotentialClicks(
        (int) $query['impressions'],
        (float) $query['position']
      );

      $opportunityData = [
        'keyword' => $query['query'],
        'current_page_url' => $query['page_url'] ?? null,
        'type' => 'content_gap',
        'target_type' => $targetType,
        'impressions' => (int) $query['impressions'],
        'clicks' => (int) $query['clicks'],
        'current_ctr' => (float) $query['ctr'],
        'current_position' => (float) $query['position'],
        'priority_score' => $priorityScore,
        'priority' => $this->determinePriority($priorityScore),
        'potential_clicks' => $potentialClicks,
        'ai_analysis' => json_encode(['suggestion' => "Criar conteúdo dedicado para \"{$query['query']}\" - {$query['impressions']} impressões com posição " . round($query['position'], 1)]),
      ];

      $opportunityData['saved'] = $this->saveOpportunity($opportunityData);
      $results[] = $opportunityData;
    }

    return $results;
  }

  /**
   * 2. Low CTR — queries com muitas impressões mas CTR abaixo do esperado
   */
  private function findLowCtrPages(): array
  {
    $minImpressions = $this->thresholds['min_impressions_low_ctr'];
    $targetCtr = $this->thresholds['target_ctr'];

    $queries = $this->queryModel
      ->where('impressions >=', $minImpressions)
      ->where('position <=', 20)
      ->where('ctr <', $targetCtr)
      ->orderBy('impressions', 'DESC')
      ->findAll();

    $results = [];

    foreach ($queries as $query) {
      $targetType = $this->classifyTargetType($query['page_url'] ?? null, $query['query']);
      if ($targetType === 'unknown') {
        $targetType = 'meta_only';
      }

      $priorityScore = $this->calculatePriorityScore([
        'type' => 'low_ctr',
        'impressions' => (int) $query['impressions'],
        'position' => (float) $query['position'],
        'ctr' => (float) $query['ctr'],
      ]);

      $potentialClicks = $this->estimatePotentialClicks(
        (int) $query['impressions'],
        (float) $query['position']
      );

      $ctrGap = round($targetCtr - (float) $query['ctr'], 2);

      $opportunityData = [
        'keyword' => $query['query'],
        'current_page_url' => $query['page_url'] ?? null,
        'type' => 'low_ctr',
        'target_type' => $targetType,
        'impressions' => (int) $query['impressions'],
        'clicks' => (int) $query['clicks'],
        'current_ctr' => (float) $query['ctr'],
        'current_position' => (float) $query['position'],
        'priority_score' => $priorityScore,
        'priority' => $this->determinePriority($priorityScore),
        'potential_clicks' => $potentialClicks,
        'ai_analysis' => json_encode(['suggestion' => "Melhorar title/description para \"{$query['query']}\" - CTR atual {$query['ctr']}% (gap de {$ctrGap}%)"]),
      ];

      $opportunityData['saved'] = $this->saveOpportunity($opportunityData);
      $results[] = $opportunityData;
    }

    return $results;
  }

  /**
   * 3. Striking Distance — queries na posição 4-20 (quick wins)
   */
  private function findStrikingDistance(): array
  {
    $minPosition = $this->thresholds['striking_distance_min'];
    $maxPosition = $this->thresholds['striking_distance_max'];

    $queries = $this->queryModel
      ->where('position >=', $minPosition)
      ->where('position <=', $maxPosition)
      ->where('impressions >', 0)
      ->orderBy('impressions', 'DESC')
      ->findAll();

    $results = [];

    foreach ($queries as $query) {
      $targetType = $this->classifyTargetType($query['page_url'] ?? null, $query['query']);
      if ($targetType === 'unknown') {
        $targetType = 'blog';
      }

      $priorityScore = $this->calculatePriorityScore([
        'type' => 'striking_distance',
        'impressions' => (int) $query['impressions'],
        'position' => (float) $query['position'],
        'ctr' => (float) $query['ctr'],
      ]);

      $potentialClicks = $this->estimatePotentialClicks(
        (int) $query['impressions'],
        (float) $query['position']
      );

      $positionsToGain = round((float) $query['position'] - 3, 1);

      $opportunityData = [
        'keyword' => $query['query'],
        'current_page_url' => $query['page_url'] ?? null,
        'type' => 'striking_distance',
        'target_type' => $targetType,
        'impressions' => (int) $query['impressions'],
        'clicks' => (int) $query['clicks'],
        'current_ctr' => (float) $query['ctr'],
        'current_position' => (float) $query['position'],
        'priority_score' => $priorityScore,
        'priority' => $this->determinePriority($priorityScore),
        'potential_clicks' => $potentialClicks,
        'ai_analysis' => json_encode(['suggestion' => "Otimizar conteúdo para \"{$query['query']}\" - faltam {$positionsToGain} posições para o top 3"]),
      ];

      $opportunityData['saved'] = $this->saveOpportunity($opportunityData);
      $results[] = $opportunityData;
    }

    return $results;
  }

  /**
   * 3b. Top Position — páginas já no top 3 (posição 1-3)
   */
  private function findTopPositionOpportunities(): array
  {
    $queries = $this->queryModel
      ->where('position >=', 1)
      ->where('position <', $this->thresholds['striking_distance_min'])
      ->where('impressions >', 0)
      ->orderBy('impressions', 'DESC')
      ->findAll();

    $results = [];

    foreach ($queries as $query) {
      $targetType = $this->classifyTargetType($query['page_url'] ?? null, $query['query']);
      if ($targetType === 'unknown') {
        $targetType = 'blog';
      }

      $position = (float) $query['position'];
      $impressions = (int) $query['impressions'];

      $priorityScore = $this->calculatePriorityScore([
        'type' => 'top_position',
        'impressions' => $impressions,
        'position' => $position,
        'ctr' => (float) $query['ctr'],
      ]);

      $positionsToTop = $position <= 1 ? 0 : round($position - 1, 1);
      $suggestion = $position <= 1
        ? "Melhorar CTR da posição #1 para \"{$query['query']}\" — otimizar title e rich snippets"
        : "Empurrar de posição " . round($position, 1) . " para #1 em \"{$query['query']}\" — {$positionsToTop} posições a ganhar";

      $opportunityData = [
        'keyword' => $query['query'],
        'current_page_url' => $query['page_url'] ?? null,
        'type' => 'top_position',
        'target_type' => $targetType,
        'impressions' => $impressions,
        'clicks' => (int) $query['clicks'],
        'current_ctr' => (float) $query['ctr'],
        'current_position' => $position,
        'priority_score' => $priorityScore,
        'priority' => $this->determinePriority($priorityScore),
        'potential_clicks' => $this->estimatePotentialClicks($impressions, $position),
        'ai_analysis' => json_encode(['suggestion' => $suggestion]),
      ];

      $opportunityData['saved'] = $this->saveOpportunity($opportunityData);
      $results[] = $opportunityData;
    }

    return $results;
  }

  /**
   * 4. Enrichment — páginas existentes que podem ser melhoradas
   */
  private function findEnrichmentOpportunities(): array
  {
    $targetCtr = $this->thresholds['target_ctr'];
    $minImpressions = $this->thresholds['min_impressions_content_gap'];

    $queries = $this->queryModel
      ->groupStart()
        ->groupStart()
          ->where('position >=', 1)
          ->where('position <=', 10)
          ->where('ctr <', $targetCtr)
          ->where('impressions >=', $minImpressions)
        ->groupEnd()
        ->orGroupStart()
          ->where('position >', 10)
          ->where('position <=', 20)
          ->where('impressions >=', $minImpressions * 2)
        ->groupEnd()
      ->groupEnd()
      ->where('page_url IS NOT NULL')
      ->where('page_url !=', '')
      ->orderBy('impressions', 'DESC')
      ->findAll();

    $results = [];

    foreach ($queries as $query) {
      $targetType = $this->classifyTargetType($query['page_url'] ?? null, $query['query']);
      if ($targetType === 'unknown') {
        $targetType = 'blog';
      }

      $priorityScore = $this->calculatePriorityScore([
        'type' => 'enrichment',
        'impressions' => (int) $query['impressions'],
        'position' => (float) $query['position'],
        'ctr' => (float) $query['ctr'],
      ]);

      $potentialClicks = $this->estimatePotentialClicks(
        (int) $query['impressions'],
        (float) $query['position']
      );

      $opportunityData = [
        'keyword' => $query['query'],
        'current_page_url' => $query['page_url'] ?? null,
        'type' => 'enrichment',
        'target_type' => $targetType,
        'impressions' => (int) $query['impressions'],
        'clicks' => (int) $query['clicks'],
        'current_ctr' => (float) $query['ctr'],
        'current_position' => (float) $query['position'],
        'priority_score' => $priorityScore,
        'priority' => $this->determinePriority($priorityScore),
        'potential_clicks' => $potentialClicks,
        'ai_analysis' => json_encode(['suggestion' => "Enriquecer conteúdo de \"{$query['page_url']}\" para \"{$query['query']}\" - adicionar FAQ, tabelas ou dados estruturados"]),
      ];

      $opportunityData['saved'] = $this->saveOpportunity($opportunityData);
      $results[] = $opportunityData;
    }

    return $results;
  }

  // ================================================================
  // CLASSIFICAÇÃO DE TIPO DE CONTEÚDO — ADAPTADO PARA DOARFAZBEM
  // ================================================================

  /**
   * Classificar tipo de conteúdo alvo pela URL e/ou query.
   *
   * Target types do DoarFazBem:
   * - campaign: campanhas de doação
   * - raffle: rifas solidárias / sorteios
   * - blog: artigos do blog
   * - page: páginas estáticas (sobre, como funciona, etc.)
   * - meta_only: só precisa melhorar meta tags (sem criar conteúdo)
   *
   * @param string|null $pageUrl URL da página no Search Console
   * @param string $query Termo de busca do usuário
   * @return string Tipo classificado
   */
  private function classifyTargetType(?string $pageUrl, string $query): string
  {
    // 1. Classificação por URL (prioridade máxima)
    if (!empty($pageUrl)) {
      $urlLower = strtolower($pageUrl);

      if (strpos($urlLower, '/campanhas/') !== false || strpos($urlLower, '/campanha/') !== false) {
        return 'campaign';
      }

      if (strpos($urlLower, '/rifas/') !== false || strpos($urlLower, '/rifa/') !== false
        || strpos($urlLower, '/sorteio/') !== false || strpos($urlLower, '/sorteios/') !== false) {
        return 'raffle';
      }

      if (strpos($urlLower, '/blog/') !== false || strpos($urlLower, '/artigo/') !== false
        || strpos($urlLower, '/artigos/') !== false) {
        return 'blog';
      }

      // Páginas estáticas conhecidas
      $staticPages = ['/sobre', '/como-funciona', '/faq', '/contato', '/termos',
        '/privacidade', '/transparencia', '/parceiros', '/para-ongs',
        '/para-empresas', '/impacto', '/categorias'];
      foreach ($staticPages as $page) {
        if (strpos($urlLower, $page) !== false) {
          return 'page';
        }
      }
    }

    // 2. Classificação por query (fallback)
    $queryLower = mb_strtolower(trim($query));

    // Termos de campanhas de doação
    $termosCampanha = [
      'campanha de doação', 'campanha doação', 'campanha solidária',
      'campanha solidaria', 'vaquinha', 'vaquinha online', 'crowdfunding',
      'financiamento coletivo', 'arrecadação', 'arrecadacao',
      'doação online', 'doacao online', 'doar para', 'como doar',
      'campanha médica', 'campanha medica', 'campanha de saúde',
      'campanha de saude', 'ajuda financeira', 'campanha emergencial',
      'pedir doação', 'pedir doacao', 'criar campanha',
      'campanha de transplante', 'campanha de tratamento',
    ];

    foreach ($termosCampanha as $termo) {
      if (strpos($queryLower, $termo) !== false) {
        return 'campaign';
      }
    }

    // Termos de rifas/sorteios
    $termosRifa = [
      'rifa solidária', 'rifa solidaria', 'rifa online', 'rifa beneficente',
      'sorteio solidário', 'sorteio solidario', 'sorteio beneficente',
      'rifa digital', 'comprar rifa', 'número da sorte', 'numero da sorte',
      'sorteio online', 'bilhete de rifa',
    ];

    foreach ($termosRifa as $termo) {
      if (strpos($queryLower, $termo) !== false) {
        return 'raffle';
      }
    }

    // Termos de doação genérica / solidariedade (mapeia para blog)
    $termosSolidariedade = [
      'doação', 'doacao', 'doar', 'solidariedade', 'solidário', 'solidario',
      'caridade', 'filantropia', 'ong', 'ação social', 'acao social',
      'voluntariado', 'voluntário', 'voluntario', 'impacto social',
      'causa social', 'ajudar', 'ajuda', 'beneficência', 'beneficencia',
      'nota fiscal paulista', 'dedução imposto de renda',
      'como ajudar', 'quero ajudar', 'faz o bem', 'fazer o bem',
      'doarfazbem', 'doar faz bem',
    ];

    foreach ($termosSolidariedade as $termo) {
      if (strpos($queryLower, $termo) !== false) {
        return 'blog';
      }
    }

    // Termos de páginas institucionais
    $termosPage = [
      'plataforma de doação', 'plataforma de doacao',
      'como funciona', 'criar vaquinha', 'é confiável', 'e confiavel',
      'site de doação', 'site de doacao', 'transparência', 'transparencia',
    ];

    foreach ($termosPage as $termo) {
      if (strpos($queryLower, $termo) !== false) {
        return 'page';
      }
    }

    return 'unknown';
  }

  // ================================================================
  // SCORING E PRIORIDADE
  // ================================================================

  /**
   * Calcular priority score (0-1000)
   *
   * Composição:
   * - Volume de impressões (peso 40%): min(impressions/10, 400)
   * - Proximidade do top 3 (peso 30%): escala por faixa de posição
   * - CTR gap (peso 20%): min((target_ctr - ctr) * 40, 200)
   * - Bônus por tipo (peso 10%): varia por tipo de oportunidade
   */
  private function calculatePriorityScore(array $data): int
  {
    $impressions = (int) ($data['impressions'] ?? 0);
    $position = (float) ($data['position'] ?? 100);
    $ctr = (float) ($data['ctr'] ?? 0);
    $type = $data['type'] ?? '';
    $targetCtr = $this->thresholds['target_ctr'];

    // 1. Volume de impressões (peso 40%, máx 400 pontos)
    $volumeScore = min($impressions / 10, 400);

    // 2. Proximidade do top 1 (peso 30%, máx 300 pontos)
    $positionScore = 0;
    if ($position <= 3) {
      $positionScore = 300 - (($position - 1) * 30);
    } elseif ($position <= 7) {
      $positionScore = 290 - (($position - 3) * 20);
    } elseif ($position <= 20) {
      $positionScore = (20 - $position) / 13 * 187;
    }

    // 3. CTR gap (peso 20%, máx 200 pontos)
    $ctrGap = max(0, $targetCtr - $ctr);
    $ctrScore = min($ctrGap * 40, 200);

    // 4. Bônus por tipo (peso 10%, máx 100 pontos)
    $typeBonus = match ($type) {
      'content_gap' => 100,
      'top_position' => 90,
      'striking_distance' => 80,
      'low_ctr' => 60,
      'enrichment' => 40,
      default => 0,
    };

    $totalScore = (int) round($volumeScore + $positionScore + $ctrScore + $typeBonus);

    return max(0, min(1000, $totalScore));
  }

  /**
   * Determinar prioridade textual
   */
  private function determinePriority(int $score): string
  {
    if ($score >= 800) return 'critical';
    if ($score >= 500) return 'high';
    if ($score >= 250) return 'medium';
    return 'low';
  }

  /**
   * Estimar cliques potenciais se chegar ao top 3
   */
  private function estimatePotentialClicks(int $impressions, float $currentPosition): int
  {
    $estimatedTopCtr = 18.7; // Média top 3

    $currentEstimatedCtr = match (true) {
      $currentPosition <= 1 => 31.0,
      $currentPosition <= 2 => 15.0,
      $currentPosition <= 3 => 10.0,
      $currentPosition <= 4 => 7.5,
      $currentPosition <= 5 => 5.0,
      $currentPosition <= 7 => 3.5,
      $currentPosition <= 10 => 2.0,
      $currentPosition <= 15 => 1.0,
      $currentPosition <= 20 => 0.5,
      $currentPosition <= 30 => 0.2,
      default => 0.1,
    };

    $potentialClicks = (int) round($impressions * ($estimatedTopCtr / 100));
    $currentClicks = (int) round($impressions * ($currentEstimatedCtr / 100));

    return max(0, $potentialClicks - $currentClicks);
  }

  /**
   * Salvar oportunidade no banco, evitando duplicatas
   */
  private function saveOpportunity(array $data): bool
  {
    try {
      $existing = $this->opportunityModel
        ->where('keyword', $data['keyword'])
        ->where('type', $data['type'])
        ->first();

      $saveData = [
        'type' => $data['type'],
        'target_type' => $data['target_type'],
        'keyword' => $data['keyword'],
        'current_page_url' => $data['current_page_url'],
        'impressions' => $data['impressions'],
        'clicks' => $data['clicks'],
        'current_ctr' => $data['current_ctr'],
        'current_position' => $data['current_position'],
        'priority_score' => $data['priority_score'],
        'priority' => $data['priority'],
        'potential_clicks' => $data['potential_clicks'],
        'ai_analysis' => $data['ai_analysis'] ?? null,
      ];

      if ($existing) {
        $existingId = $existing['id'] ?? null;
        if ($existingId) {
          $this->opportunityModel->update($existingId, $saveData);
          return true;
        }
        return false;
      }

      $saveData['status'] = 'pending';
      return (bool) $this->opportunityModel->insert($saveData);
    } catch (\Exception $e) {
      log_message('error', '[SeoAnalyzer] Erro ao salvar oportunidade: ' . $e->getMessage());
      return false;
    }
  }
}

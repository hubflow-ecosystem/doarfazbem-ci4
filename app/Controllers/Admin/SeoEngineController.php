<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SeoQueryModel;
use App\Models\SeoOpportunityModel;
use App\Models\SeoActionLogModel;
use App\Models\SeoBingAnalyticsModel;
use App\Models\SeoConfigModel;

/**
 * Controller do Motor SEO Autônomo — Dashboard administrativo
 *
 * Gerencia: KPIs, gráficos, oportunidades, histórico de ações,
 * configurações e testes de APIs (GSC, Bing, Groq).
 */
class SeoEngineController extends BaseController
{
  protected SeoQueryModel $queryModel;
  protected SeoOpportunityModel $opportunityModel;
  protected SeoActionLogModel $actionLogModel;
  protected SeoBingAnalyticsModel $bingModel;
  protected SeoConfigModel $configModel;

  public function __construct()
  {
    $this->queryModel = new SeoQueryModel();
    $this->opportunityModel = new SeoOpportunityModel();
    $this->actionLogModel = new SeoActionLogModel();
    $this->bingModel = new SeoBingAnalyticsModel();
    $this->configModel = new SeoConfigModel();
  }

  /**
   * Dashboard principal — KPIs + gráficos + top queries + oportunidades
   */
  public function index()
  {
    $days = 28;

    // Métricas gerais do Search Console
    $metrics = [
      'unique_queries' => $this->queryModel->countUniqueQueries($days),
      'total_impressions' => $this->queryModel->totalImpressions($days),
      'total_clicks' => $this->queryModel->totalClicks($days),
      'avg_ctr' => $this->queryModel->avgCtr($days),
      'avg_position' => $this->queryModel->avgPosition($days),
      'latest_date' => $this->queryModel->latestDate(),
    ];

    // Oportunidades
    $oppStats = $this->opportunityModel->dashboardStats();

    // Top queries e páginas
    $topQueries = $this->queryModel->topQueries($days, 10);
    $topPages = $this->queryModel->topPages($days, 10);

    // Ações hoje
    $todayActions = $this->actionLogModel->todayActions();
    $actionsByType = $this->actionLogModel->countByActionType(30);

    // Custo IA
    $aiCost = $this->actionLogModel->totalAiCost(30);

    // Status do motor
    $engineEnabled = $this->configModel->isEnabled();
    $lastCollect = $this->configModel->getConfig('last_collect_date', 'Nunca');
    $lastAnalyze = $this->configModel->getConfig('last_analyze_date', 'Nunca');
    $lastExecute = $this->configModel->getConfig('last_execute_date', 'Nunca');

    // GA4 real-time (se disponível)
    $activeUsers = 0;
    if (class_exists('\App\Libraries\GoogleAnalyticsService')) {
      try {
        $ga4 = new \App\Libraries\GoogleAnalyticsService();
        $ga4Result = $ga4->getActiveUsers();
        $activeUsers = $ga4Result['active_users'] ?? 0;
      } catch (\Throwable $e) {
        // Ignora se GA4 não está configurado
      }
    }

    return view('admin/seo/engine-dashboard', [
      'metrics' => $metrics,
      'oppStats' => $oppStats,
      'topQueries' => $topQueries,
      'topPages' => $topPages,
      'todayActions' => $todayActions,
      'actionsByType' => $actionsByType,
      'aiCost' => $aiCost,
      'engineEnabled' => $engineEnabled,
      'lastCollect' => $lastCollect,
      'lastAnalyze' => $lastAnalyze,
      'lastExecute' => $lastExecute,
      'activeUsers' => $activeUsers,
    ]);
  }

  /**
   * Lista de oportunidades com filtros
   */
  public function opportunities()
  {
    $status = $this->request->getGet('status') ?? 'pending';
    $type = $this->request->getGet('type') ?? '';

    $builder = $this->opportunityModel;

    if ($status) {
      $builder = $builder->where('status', $status);
    }

    if ($type) {
      $builder = $builder->where('type', $type);
    }

    $opportunities = $builder->orderBy('priority_score', 'DESC')->findAll(100);

    $countByType = $this->opportunityModel->countByType();
    $countByStatus = $this->opportunityModel->countByStatus();

    return view('admin/seo/engine-opportunities', [
      'opportunities' => $opportunities,
      'countByType' => $countByType,
      'countByStatus' => $countByStatus,
      'currentStatus' => $status,
      'currentType' => $type,
    ]);
  }

  /**
   * Histórico de ações executadas
   */
  public function actionLog()
  {
    $actions = $this->actionLogModel->recentActions(50);
    $actionsByType = $this->actionLogModel->countByActionType(30);
    $aiCost30 = $this->actionLogModel->totalAiCost(30);
    $aiCostTotal = $this->actionLogModel->totalAiCost(365);

    return view('admin/seo/engine-actions', [
      'actions' => $actions,
      'actionsByType' => $actionsByType,
      'aiCost30' => $aiCost30,
      'aiCostTotal' => $aiCostTotal,
    ]);
  }

  /**
   * Configurações do motor SEO
   */
  public function config()
  {
    $configs = $this->configModel->getAll();

    return view('admin/seo/engine-config', [
      'configs' => $configs,
    ]);
  }

  /**
   * Salvar configurações
   */
  public function saveConfig()
  {
    $keys = [
      'engine_enabled', 'auto_publish', 'target_ctr',
      'max_articles_per_day', 'max_enrichments_per_day', 'max_top_positions_per_day',
      'min_impressions_content_gap', 'min_impressions_low_ctr',
      'striking_distance_min', 'striking_distance_max',
    ];

    foreach ($keys as $key) {
      $value = $this->request->getPost($key);
      if ($value !== null) {
        $this->configModel->setConfig($key, $value);
      }
    }

    return redirect()->to('/admin/seo-engine/config')->with('success', 'Configurações salvas com sucesso!');
  }

  /**
   * Testar conexão com Google Search Console (AJAX)
   */
  public function testGsc()
  {
    try {
      $gsc = new \App\Libraries\GoogleSearchConsoleService();
      $result = $gsc->testConnection();
      return $this->response->setJSON($result);
    } catch (\Throwable $e) {
      return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  /**
   * Testar conexão com Bing (AJAX)
   */
  public function testBing()
  {
    try {
      $bing = new \App\Libraries\BingWebmasterApiService();
      $result = $bing->testConnection();
      return $this->response->setJSON($result);
    } catch (\Throwable $e) {
      return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  /**
   * Testar conexão com Groq/AI (AJAX)
   */
  public function testGrok()
  {
    try {
      $grok = new \App\Libraries\GrokService();
      $result = $grok->testConnection();
      return $this->response->setJSON($result);
    } catch (\Throwable $e) {
      return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  /**
   * Executar coleta manual (AJAX)
   */
  public function runCollect()
  {
    try {
      $gsc = new \App\Libraries\GoogleSearchConsoleService();
      $result = $gsc->fetchAllQueries(7);

      if ($result['success'] && !empty($result['queries'])) {
        $saved = $this->queryModel->upsertBatch($result['queries']);
        $this->configModel->setConfig('last_collect_date', date('Y-m-d H:i:s'));

        return $this->response->setJSON([
          'success' => true,
          'total' => $result['total'],
          'inserted' => $saved['inserted'],
          'updated' => $saved['updated'],
        ]);
      }

      return $this->response->setJSON($result);
    } catch (\Throwable $e) {
      return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  /**
   * Executar análise manual (AJAX)
   */
  public function runAnalyze()
  {
    try {
      $analyzer = new \App\Libraries\SeoAnalyzerService();
      $result = $analyzer->analyzeAll();
      $this->configModel->setConfig('last_analyze_date', date('Y-m-d H:i:s'));

      return $this->response->setJSON($result);
    } catch (\Throwable $e) {
      return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  /**
   * Dispensar oportunidade (AJAX)
   */
  public function dismiss()
  {
    $id = (int) $this->request->getPost('id');
    if (!$id) {
      return $this->response->setJSON(['success' => false, 'error' => 'ID inválido']);
    }

    $this->opportunityModel->update($id, ['status' => 'dismissed']);
    return $this->response->setJSON(['success' => true]);
  }

  /**
   * API: dados diários para gráfico Chart.js (AJAX)
   */
  public function dailyStats()
  {
    $days = (int) ($this->request->getGet('days') ?? 30);
    $stats = $this->queryModel->dailyStats($days);

    return $this->response->setJSON([
      'success' => true,
      'data' => $stats,
    ]);
  }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para oportunidades SEO detectadas pelo motor
 * Tabela: seo_opportunities
 */
class SeoOpportunityModel extends Model
{
  protected $table            = 'seo_opportunities';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $useTimestamps    = true;
  protected $createdField     = 'created_at';
  protected $updatedField     = 'updated_at';

  protected $allowedFields = [
    'type', 'target_type', 'keyword', 'related_keywords',
    'current_page_url', 'impressions', 'clicks',
    'current_ctr', 'current_position', 'potential_clicks',
    'priority', 'priority_score', 'ai_analysis',
    'ai_suggested_title', 'ai_suggested_meta', 'ai_suggested_content',
    'status', 'executed_at', 'result_action', 'result_target_id', 'result_target_url',
    'created_at', 'updated_at',
  ];

  /**
   * Oportunidades pendentes ordenadas por prioridade
   */
  public function getPending(int $limit = 50): array
  {
    return $this->where('status', 'pending')
      ->orderBy('priority_score', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Contagem por tipo de oportunidade
   */
  public function countByType(): array
  {
    return $this->select('type, COUNT(*) as total')
      ->where('status', 'pending')
      ->groupBy('type')
      ->findAll();
  }

  /**
   * Contagem por status
   */
  public function countByStatus(): array
  {
    return $this->select('status, COUNT(*) as total')
      ->groupBy('status')
      ->findAll();
  }

  /**
   * Top oportunidades para o dashboard
   */
  public function topOpportunities(int $limit = 10): array
  {
    return $this->where('status', 'pending')
      ->orderBy('priority_score', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Marcar como executada
   */
  public function markExecuted(int $id, string $action, ?int $targetId = null, ?string $targetUrl = null): bool
  {
    return $this->update($id, [
      'status'            => 'monitoring',
      'executed_at'       => date('Y-m-d H:i:s'),
      'result_action'     => $action,
      'result_target_id'  => $targetId,
      'result_target_url' => $targetUrl,
    ]);
  }

  /**
   * Marcar como falha
   */
  public function markFailed(int $id, string $reason): bool
  {
    return $this->update($id, [
      'status'        => 'failed',
      'result_action' => $reason,
    ]);
  }

  /**
   * Verificar se keyword já tem oportunidade pendente
   */
  public function hasPending(string $keyword): bool
  {
    return $this->where('keyword', $keyword)
      ->whereIn('status', ['pending', 'in_progress'])
      ->countAllResults() > 0;
  }

  /**
   * Estatísticas para dashboard
   */
  public function dashboardStats(): array
  {
    $pending   = $this->where('status', 'pending')->countAllResults();
    $executed  = $this->where('status', 'monitoring')->where('executed_at >=', date('Y-m-d'))->countAllResults();
    $todayBlog = $this->where('status', 'monitoring')
      ->where('executed_at >=', date('Y-m-d'))
      ->where('target_type', 'blog')
      ->countAllResults();
    $failed    = $this->where('status', 'failed')->where('created_at >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();

    return [
      'pending'    => $pending,
      'executed'   => $executed,
      'today_blog' => $todayBlog,
      'failed'     => $failed,
    ];
  }
}

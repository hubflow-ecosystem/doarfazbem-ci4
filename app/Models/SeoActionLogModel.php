<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para log de ações executadas pelo motor SEO
 * Tabela: seo_action_log
 */
class SeoActionLogModel extends Model
{
  protected $table            = 'seo_action_log';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $useTimestamps    = false;

  protected $allowedFields = [
    'opportunity_id', 'action_type', 'target_type',
    'target_id', 'target_url', 'keyword',
    'before_state', 'after_state',
    'ai_model', 'ai_tokens_used', 'ai_cost_usd',
    'success', 'error_message', 'executed_at',
  ];

  /**
   * Registrar ação executada
   */
  public function logAction(array $data): int
  {
    $data['executed_at'] = $data['executed_at'] ?? date('Y-m-d H:i:s');
    $data['success']     = $data['success'] ?? 1;

    if (isset($data['before_state']) && is_array($data['before_state'])) {
      $data['before_state'] = json_encode($data['before_state']);
    }
    if (isset($data['after_state']) && is_array($data['after_state'])) {
      $data['after_state'] = json_encode($data['after_state']);
    }

    return $this->insert($data);
  }

  /**
   * Últimas ações executadas
   */
  public function recentActions(int $limit = 20): array
  {
    return $this->orderBy('executed_at', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Ações de hoje
   */
  public function todayActions(): array
  {
    return $this->where('executed_at >=', date('Y-m-d 00:00:00'))
      ->orderBy('executed_at', 'DESC')
      ->findAll();
  }

  /**
   * Custo total de IA no período (Groq é gratuito, então será $0)
   */
  public function totalAiCost(int $days = 30): float
  {
    $result = $this->selectSum('ai_cost_usd')
      ->where('executed_at >=', date('Y-m-d', strtotime("-{$days} days")))
      ->first();
    return round((float) ($result['ai_cost_usd'] ?? 0), 4);
  }

  /**
   * Contagem por tipo de ação no período
   */
  public function countByActionType(int $days = 30): array
  {
    return $this->select('action_type, COUNT(*) as total, SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count')
      ->where('executed_at >=', date('Y-m-d', strtotime("-{$days} days")))
      ->groupBy('action_type')
      ->findAll();
  }
}

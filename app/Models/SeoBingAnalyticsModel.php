<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para analytics do Bing Webmaster Tools
 * Tabela: seo_bing_analytics
 */
class SeoBingAnalyticsModel extends Model
{
  protected $table            = 'seo_bing_analytics';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $useTimestamps    = false;
  protected $createdField     = 'created_at';

  protected $allowedFields = [
    'query', 'impressions', 'clicks', 'avg_position',
    'ctr', 'opportunity_zone', 'period_start', 'period_end', 'created_at',
  ];

  /**
   * Top queries do Bing por impressões
   */
  public function topQueries(int $limit = 20): array
  {
    return $this->orderBy('impressions', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Oportunidades na zona de prioridade (posição 2-20)
   */
  public function getOpportunities(int $limit = 50): array
  {
    return $this->where('opportunity_zone IS NOT NULL')
      ->where('opportunity_zone !=', '')
      ->orderBy('impressions', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Inserir ou atualizar em lote
   */
  public function upsertBatch(array $rows): array
  {
    $inserted = 0;
    $updated = 0;

    foreach ($rows as $row) {
      $existing = $this->where('query', $row['query'])
        ->where('period_start', $row['period_start'] ?? null)
        ->first();

      if ($existing) {
        $this->update($existing['id'], $row);
        $updated++;
      } else {
        $row['created_at'] = date('Y-m-d H:i:s');
        $this->insert($row);
        $inserted++;
      }
    }

    return ['inserted' => $inserted, 'updated' => $updated];
  }

  /**
   * Estatísticas gerais
   */
  public function getStats(): array
  {
    $total = $this->countAllResults(false);
    $opportunities = $this->where('opportunity_zone IS NOT NULL')->countAllResults(false);
    $totalImpressions = $this->selectSum('impressions')->first();
    $totalClicks = $this->selectSum('clicks')->first();

    return [
      'total_queries' => $total,
      'opportunities' => $opportunities,
      'total_impressions' => (int) ($totalImpressions['impressions'] ?? 0),
      'total_clicks' => (int) ($totalClicks['clicks'] ?? 0),
    ];
  }
}

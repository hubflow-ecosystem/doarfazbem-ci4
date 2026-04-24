<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para dados de queries do Google Search Console e Bing
 * Tabela: seo_queries
 */
class SeoQueryModel extends Model
{
  protected $table            = 'seo_queries';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $useTimestamps    = false;
  protected $createdField     = 'created_at';

  protected $allowedFields = [
    'query', 'page_url', 'clicks', 'impressions',
    'ctr', 'position', 'device', 'country', 'source', 'date', 'created_at',
  ];

  /**
   * Total de queries únicas no período
   */
  public function countUniqueQueries(int $days = 28): int
  {
    return $this->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->distinct()
      ->select('query')
      ->countAllResults();
  }

  /**
   * Total de impressões no período
   */
  public function totalImpressions(int $days = 28): int
  {
    $result = $this->selectSum('impressions')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->first();
    return (int) ($result['impressions'] ?? 0);
  }

  /**
   * Total de cliques no período
   */
  public function totalClicks(int $days = 28): int
  {
    $result = $this->selectSum('clicks')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->first();
    return (int) ($result['clicks'] ?? 0);
  }

  /**
   * CTR médio no período
   */
  public function avgCtr(int $days = 28): float
  {
    $result = $this->selectAvg('ctr')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->where('impressions >', 0)
      ->first();
    return round((float) ($result['ctr'] ?? 0), 2);
  }

  /**
   * Posição média no período
   */
  public function avgPosition(int $days = 28): float
  {
    $result = $this->selectAvg('position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->where('impressions >', 0)
      ->first();
    return round((float) ($result['position'] ?? 0), 1);
  }

  /**
   * Top queries por impressões
   */
  public function topQueries(int $days = 28, int $limit = 20): array
  {
    return $this->select('query, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->groupBy('query')
      ->orderBy('total_impressions', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Top páginas por cliques
   */
  public function topPages(int $days = 28, int $limit = 20): array
  {
    return $this->select('page_url, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->where('page_url IS NOT NULL')
      ->groupBy('page_url')
      ->orderBy('total_clicks', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Dados diários para gráfico (impressões e cliques por dia)
   */
  public function dailyStats(int $days = 30): array
  {
    return $this->select('date, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->groupBy('date')
      ->orderBy('date', 'ASC')
      ->findAll();
  }

  /**
   * Queries com CTR baixo mas muitas impressões (oportunidades de otimização)
   */
  public function lowCtrHighImpressions(int $days = 28, float $maxCtr = 3.0, int $minImpressions = 50): array
  {
    return $this->select('query, page_url, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->groupBy('query, page_url')
      ->having('total_impressions >=', $minImpressions)
      ->having('avg_ctr <', $maxCtr)
      ->orderBy('total_impressions', 'DESC')
      ->findAll();
  }

  /**
   * Queries na "striking distance" (posição 4-20 — fáceis de subir)
   */
  public function strikingDistance(int $days = 28, int $minPos = 4, int $maxPos = 20, int $minImpressions = 10): array
  {
    return $this->select('query, page_url, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->groupBy('query, page_url')
      ->having('avg_position >=', $minPos)
      ->having('avg_position <=', $maxPos)
      ->having('total_impressions >=', $minImpressions)
      ->orderBy('total_impressions', 'DESC')
      ->findAll();
  }

  /**
   * Distribuição por dispositivo
   */
  public function deviceDistribution(int $days = 28): array
  {
    return $this->select('device, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr')
      ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
      ->where('device IS NOT NULL')
      ->groupBy('device')
      ->findAll();
  }

  /**
   * Data mais recente no banco
   */
  public function latestDate(): ?string
  {
    $result = $this->selectMax('date')->first();
    return $result['date'] ?? null;
  }

  /**
   * Inserir ou atualizar em lote (upsert)
   */
  public function upsertBatch(array $rows): array
  {
    $inserted = 0;
    $updated = 0;

    foreach ($rows as $row) {
      $existing = $this->where([
        'query'    => $row['query'],
        'page_url' => $row['page_url'] ?? null,
        'date'     => $row['date'],
        'source'   => $row['source'] ?? 'google',
      ])->first();

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
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * FinancialDashboardController - Dashboard Financeiro SuperAdmin DoarFazBem
 *
 * Fornece: KPIs, receitas, doações por método, campanhas top, chargebacks, webhooks
 */
class FinancialDashboardController extends BaseController
{
  protected $db;

  public function __construct()
  {
    $this->db = \Config\Database::connect();
  }

  /**
   * Dashboard financeiro principal
   * GET /admin/financeiro
   */
  public function index()
  {
    $data = [
      'title' => 'Dashboard Financeiro — DoarFazBem',
      'kpis' => $this->getKPIs(),
      'revenue_trend' => $this->getRevenueTrend(12),
      'revenue_by_method' => $this->getRevenueByMethod(),
      'top_campaigns' => $this->getTopCampaigns(10),
      'recent_donations' => $this->getRecentDonations(20),
      'cash_flow' => $this->getCashFlow(),
    ];
    return view('admin/financial/index', $data);
  }

  /**
   * API: KPIs em JSON (refresh AJAX)
   */
  public function apiKPIs()
  {
    return $this->response->setJSON(['success' => true, 'data' => $this->getKPIs()]);
  }

  /**
   * Export CSV
   */
  public function export($type = 'csv')
  {
    $donations = $this->getRecentDonations(5000);
    $csv = "Data,Doador,Campanha,Valor,Status,Método\n";
    foreach ($donations as $d) {
      $csv .= '"' . ($d['created_at'] ?? '') . '","' . ($d['donor_name'] ?? 'Anônimo') . '","'
        . ($d['campaign_title'] ?? '') . '",' . ($d['amount'] ?? 0) . ','
        . ($d['status'] ?? '') . ',"' . ($d['payment_method'] ?? '') . "\"\n";
    }
    return $this->response
      ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
      ->setHeader('Content-Disposition', 'attachment; filename="financeiro_doarfazbem_' . date('Y-m-d') . '.csv"')
      ->setBody("\xEF\xBB\xBF" . $csv);
  }

  // =========================================================================
  // MÉTODOS PRIVADOS - DADOS
  // =========================================================================

  private function getKPIs(): array
  {
    $thisMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));

    // Total arrecadado (todas as doações confirmadas)
    $totalRaised = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status IN ('confirmed', 'paid')", 0
    );

    // Arrecadado este mês
    $thisMonthRaised = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status IN ('confirmed', 'paid') AND created_at >= '{$thisMonth}'", 0
    );

    // Arrecadado mês passado
    $lastMonthRaised = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status IN ('confirmed', 'paid') AND created_at >= '{$lastMonth}' AND created_at < '{$thisMonth}'", 0
    );

    // Variação percentual
    $revenueGrowth = $lastMonthRaised > 0
      ? round((($thisMonthRaised - $lastMonthRaised) / $lastMonthRaised) * 100, 1) : 0;

    // Total de doações
    $totalDonations = $this->safeCount('donations', ['status' => 'confirmed']);
    $totalDonationsThisMonth = $this->safeCount('donations', [
      'status' => 'confirmed', 'created_at >=' => $thisMonth,
    ]);

    // Ticket médio
    $avgDonation = $totalDonations > 0 ? round($totalRaised / $totalDonations, 2) : 0;

    // Taxa da plataforma arrecadada
    $platformFees = $this->safeScalar(
      "SELECT COALESCE(SUM(platform_fee), 0) FROM donations WHERE status IN ('confirmed', 'paid')", 0
    );

    // Campanhas ativas
    $activeCampaigns = $this->safeCount('campaigns', ['status' => 'active']);

    // Usuários totais
    $totalUsers = $this->safeCount('users', []);

    // Saques pendentes
    $pendingWithdrawals = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 'pending'", 0
    );

    // Rifas - arrecadação
    $raffleRevenue = $this->safeScalar(
      "SELECT COALESCE(SUM(total_amount), 0) FROM raffle_purchases WHERE payment_status = 'approved'", 0
    );

    return [
      'total_raised' => $totalRaised,
      'this_month_raised' => $thisMonthRaised,
      'last_month_raised' => $lastMonthRaised,
      'revenue_growth' => $revenueGrowth,
      'total_donations' => $totalDonations,
      'donations_this_month' => $totalDonationsThisMonth,
      'avg_donation' => $avgDonation,
      'platform_fees' => $platformFees,
      'active_campaigns' => $activeCampaigns,
      'total_users' => $totalUsers,
      'pending_withdrawals' => $pendingWithdrawals,
      'raffle_revenue' => $raffleRevenue,
    ];
  }

  private function getRevenueTrend(int $months = 12): array
  {
    $result = [];
    for ($i = $months - 1; $i >= 0; $i--) {
      $monthStart = date('Y-m-01', strtotime("-{$i} months"));
      $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
      $label = date('M/Y', strtotime($monthStart));

      $revenue = $this->safeScalar(
        "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status IN ('confirmed','paid') AND created_at >= '{$monthStart}' AND created_at <= '{$monthEnd} 23:59:59'", 0
      );

      $count = $this->safeScalar(
        "SELECT COUNT(*) FROM donations WHERE status IN ('confirmed','paid') AND created_at >= '{$monthStart}' AND created_at <= '{$monthEnd} 23:59:59'", 0
      );

      $result[] = ['label' => $label, 'revenue' => (float)$revenue, 'count' => (int)$count];
    }
    return $result;
  }

  private function getRevenueByMethod(): array
  {
    $methods = ['pix' => 0, 'boleto' => 0, 'credit_card' => 0, 'other' => 0];
    $rows = $this->safeQuery(
      "SELECT payment_method, COALESCE(SUM(amount), 0) as total FROM donations WHERE status IN ('confirmed','paid') GROUP BY payment_method", []
    );
    foreach ($rows as $row) {
      $method = strtolower($row['payment_method'] ?? 'other');
      if (isset($methods[$method])) {
        $methods[$method] = (float)$row['total'];
      } else {
        $methods['other'] += (float)$row['total'];
      }
    }
    return $methods;
  }

  private function getTopCampaigns(int $limit = 10): array
  {
    return $this->safeQuery(
      "SELECT c.id, c.title, c.slug, c.goal, c.status,
              COALESCE(SUM(d.amount), 0) as total_raised,
              COUNT(d.id) as donation_count
       FROM campaigns c
       LEFT JOIN donations d ON d.campaign_id = c.id AND d.status IN ('confirmed','paid')
       GROUP BY c.id
       ORDER BY total_raised DESC
       LIMIT {$limit}", []
    );
  }

  private function getRecentDonations(int $limit = 20): array
  {
    return $this->safeQuery(
      "SELECT d.*, c.title as campaign_title, u.name as donor_name
       FROM donations d
       LEFT JOIN campaigns c ON c.id = d.campaign_id
       LEFT JOIN users u ON u.id = d.user_id
       ORDER BY d.created_at DESC
       LIMIT {$limit}", []
    );
  }

  private function getCashFlow(): array
  {
    $thisMonth = date('Y-m-01');

    // Entradas (doações confirmadas este mês)
    $income = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status IN ('confirmed','paid') AND created_at >= '{$thisMonth}'", 0
    );

    // Saídas (saques aprovados este mês)
    $withdrawals = $this->safeScalar(
      "SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 'approved' AND updated_at >= '{$thisMonth}'", 0
    );

    return [
      'income' => (float)$income,
      'withdrawals' => (float)$withdrawals,
      'balance' => (float)$income - (float)$withdrawals,
      'period' => date('M/Y'),
    ];
  }

  // =========================================================================
  // SAFE QUERY HELPERS
  // =========================================================================

  private function safeQuery(string $sql, $default = [])
  {
    try {
      return $this->db->query($sql)->getResultArray();
    } catch (\Throwable $e) {
      log_message('error', 'FinancialDashboard query error: ' . $e->getMessage());
      return $default;
    }
  }

  private function safeScalar(string $sql, $default = 0)
  {
    try {
      $row = $this->db->query($sql)->getRowArray();
      return $row ? reset($row) : $default;
    } catch (\Throwable $e) {
      return $default;
    }
  }

  private function safeCount(string $table, array $where): int
  {
    try {
      $builder = $this->db->table($table);
      if (!empty($where)) {
        foreach ($where as $key => $val) {
          if (strpos($key, ' ') !== false) {
            $builder->where($key, $val);
          } else {
            $builder->where($key, $val);
          }
        }
      }
      return $builder->countAllResults();
    } catch (\Throwable $e) {
      return 0;
    }
  }
}

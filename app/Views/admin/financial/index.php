<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Cabeçalho -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-chart-line text-emerald-600 mr-2"></i>Dashboard Financeiro
      </h1>
      <p class="text-gray-500 mt-1">Visão geral das finanças da plataforma</p>
    </div>
    <div class="flex space-x-2">
      <button onclick="refreshKPIs()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
        <i class="fas fa-sync-alt mr-1"></i> Atualizar
      </button>
      <a href="<?= base_url('admin/financeiro/export') ?>" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
        <i class="fas fa-download mr-1"></i> Exportar CSV
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8" id="kpi-cards">
    <!-- Total Arrecadado -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Arrecadado</p>
          <p class="text-2xl font-bold text-gray-900">R$ <?= number_format($kpis['total_raised'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
          <i class="fas fa-hand-holding-dollar text-emerald-600 text-xl"></i>
        </div>
      </div>
    </div>

    <!-- Este Mês -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Este Mês</p>
          <p class="text-2xl font-bold text-gray-900">R$ <?= number_format($kpis['this_month_raised'] ?? 0, 2, ',', '.') ?></p>
          <p class="text-xs mt-1 <?= ($kpis['revenue_growth'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' ?>">
            <i class="fas fa-<?= ($kpis['revenue_growth'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
            <?= abs($kpis['revenue_growth'] ?? 0) ?>% vs mês anterior
          </p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
          <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
        </div>
      </div>
    </div>

    <!-- Doações -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total de Doações</p>
          <p class="text-2xl font-bold text-gray-900"><?= number_format($kpis['total_donations'] ?? 0) ?></p>
          <p class="text-xs text-gray-400 mt-1">Ticket médio: R$ <?= number_format($kpis['avg_donation'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
          <i class="fas fa-heart text-purple-600 text-xl"></i>
        </div>
      </div>
    </div>

    <!-- Taxas da Plataforma -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Receita Plataforma</p>
          <p class="text-2xl font-bold text-gray-900">R$ <?= number_format($kpis['platform_fees'] ?? 0, 2, ',', '.') ?></p>
          <p class="text-xs text-gray-400 mt-1">Rifas: R$ <?= number_format($kpis['raffle_revenue'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
          <i class="fas fa-coins text-yellow-600 text-xl"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs Secundários -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center">
      <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
        <i class="fas fa-bullhorn text-green-600"></i>
      </div>
      <div>
        <p class="text-sm text-gray-500">Campanhas Ativas</p>
        <p class="text-xl font-bold"><?= $kpis['active_campaigns'] ?? 0 ?></p>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center">
      <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
        <i class="fas fa-users text-indigo-600"></i>
      </div>
      <div>
        <p class="text-sm text-gray-500">Usuários Totais</p>
        <p class="text-xl font-bold"><?= $kpis['total_users'] ?? 0 ?></p>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center">
      <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
        <i class="fas fa-wallet text-red-600"></i>
      </div>
      <div>
        <p class="text-sm text-gray-500">Saques Pendentes</p>
        <p class="text-xl font-bold">R$ <?= number_format($kpis['pending_withdrawals'] ?? 0, 2, ',', '.') ?></p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Gráfico de Receita -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold mb-4"><i class="fas fa-chart-area text-emerald-600 mr-2"></i>Evolução da Arrecadação</h3>
      <canvas id="revenueChart" height="250"></canvas>
    </div>

    <!-- Receita por Método -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold mb-4"><i class="fas fa-chart-pie text-blue-600 mr-2"></i>Doações por Método</h3>
      <canvas id="methodChart" height="250"></canvas>
    </div>
  </div>

  <!-- Fluxo de Caixa -->
  <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4"><i class="fas fa-exchange-alt text-purple-600 mr-2"></i>Fluxo de Caixa — <?= $cash_flow['period'] ?? '' ?></h3>
    <div class="grid grid-cols-3 gap-4">
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <p class="text-sm text-green-600">Entradas</p>
        <p class="text-2xl font-bold text-green-700">R$ <?= number_format($cash_flow['income'] ?? 0, 2, ',', '.') ?></p>
      </div>
      <div class="text-center p-4 bg-red-50 rounded-lg">
        <p class="text-sm text-red-600">Saques</p>
        <p class="text-2xl font-bold text-red-700">R$ <?= number_format($cash_flow['withdrawals'] ?? 0, 2, ',', '.') ?></p>
      </div>
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <p class="text-sm text-blue-600">Saldo</p>
        <p class="text-2xl font-bold text-blue-700">R$ <?= number_format($cash_flow['balance'] ?? 0, 2, ',', '.') ?></p>
      </div>
    </div>
  </div>

  <!-- Top Campanhas -->
  <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4"><i class="fas fa-trophy text-yellow-500 mr-2"></i>Top 10 Campanhas por Arrecadação</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campanha</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Arrecadado</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Meta</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Doações</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($top_campaigns as $i => $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-bold text-gray-400"><?= $i + 1 ?></td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">
              <a href="<?= base_url('campaigns/' . ($c['slug'] ?? $c['id'])) ?>" class="hover:text-emerald-600" target="_blank">
                <?= esc(mb_substr($c['title'] ?? '', 0, 50)) ?>
              </a>
            </td>
            <td class="px-4 py-3 text-sm text-right font-bold text-emerald-600">R$ <?= number_format($c['total_raised'] ?? 0, 2, ',', '.') ?></td>
            <td class="px-4 py-3 text-sm text-right text-gray-500">R$ <?= number_format($c['goal'] ?? 0, 2, ',', '.') ?></td>
            <td class="px-4 py-3 text-sm text-right"><?= $c['donation_count'] ?? 0 ?></td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-1 text-xs rounded-full <?= ($c['status'] ?? '') === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                <?= ucfirst($c['status'] ?? '') ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Doações Recentes -->
  <div class="bg-white rounded-xl shadow-sm border p-6">
    <h3 class="text-lg font-semibold mb-4"><i class="fas fa-clock text-gray-500 mr-2"></i>Doações Recentes</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doador</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campanha</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach (array_slice($recent_donations, 0, 10) as $d): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($d['created_at'] ?? 'now')) ?></td>
            <td class="px-4 py-3 text-sm"><?= esc($d['donor_name'] ?? 'Anônimo') ?></td>
            <td class="px-4 py-3 text-sm text-gray-600"><?= esc(mb_substr($d['campaign_title'] ?? '', 0, 40)) ?></td>
            <td class="px-4 py-3 text-sm text-right font-medium">R$ <?= number_format($d['amount'] ?? 0, 2, ',', '.') ?></td>
            <td class="px-4 py-3 text-center">
              <?php
                $statusColors = ['confirmed' => 'bg-green-100 text-green-700', 'paid' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'failed' => 'bg-red-100 text-red-700'];
                $color = $statusColors[$d['status'] ?? ''] ?? 'bg-gray-100 text-gray-600';
              ?>
              <span class="px-2 py-1 text-xs rounded-full <?= $color ?>"><?= ucfirst($d['status'] ?? '') ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Gráfico de Receita
const revCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($revenue_trend ?? [], 'label')) ?>,
    datasets: [{
      label: 'Arrecadação (R$)',
      data: <?= json_encode(array_column($revenue_trend ?? [], 'revenue')) ?>,
      borderColor: '#10B981',
      backgroundColor: 'rgba(16,185,129,0.1)',
      fill: true,
      tension: 0.4,
    }]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Gráfico por Método
const methCtx = document.getElementById('methodChart').getContext('2d');
const methods = <?= json_encode($revenue_by_method ?? []) ?>;
new Chart(methCtx, {
  type: 'doughnut',
  data: {
    labels: ['PIX', 'Boleto', 'Cartão', 'Outros'],
    datasets: [{
      data: [methods.pix || 0, methods.boleto || 0, methods.credit_card || 0, methods.other || 0],
      backgroundColor: ['#10B981', '#3B82F6', '#8B5CF6', '#9CA3AF'],
    }]
  },
  options: { responsive: true }
});

function refreshKPIs() {
  fetch('<?= base_url('admin/financeiro/api/kpis') ?>')
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); });
}
</script>

<?= $this->endSection() ?>

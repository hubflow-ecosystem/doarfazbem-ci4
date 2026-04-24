<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-rocket text-purple-600 mr-2"></i>Motor SEO Autônomo
      </h1>
      <p class="text-gray-500 mt-1">Coleta dados, detecta oportunidades e otimiza conteúdo com IA</p>
    </div>
    <div class="flex items-center gap-3">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $engineEnabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <span class="w-2 h-2 rounded-full <?= $engineEnabled ? 'bg-green-500' : 'bg-red-500' ?> mr-2"></span>
        <?= $engineEnabled ? 'Motor Ativo' : 'Motor Inativo' ?>
      </span>
      <?php if ($activeUsers > 0): ?>
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
        <i class="fas fa-users mr-1"></i> <?= $activeUsers ?> online
      </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Impressões</p>
          <p class="text-2xl font-bold text-gray-900"><?= number_format($metrics['total_impressions']) ?></p>
          <p class="text-xs text-gray-400 mt-1">últimos 28 dias</p>
        </div>
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-eye text-blue-600"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Cliques</p>
          <p class="text-2xl font-bold text-gray-900"><?= number_format($metrics['total_clicks']) ?></p>
          <p class="text-xs text-gray-400 mt-1">últimos 28 dias</p>
        </div>
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-mouse-pointer text-green-600"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">CTR Médio</p>
          <p class="text-2xl font-bold <?= $metrics['avg_ctr'] >= 5 ? 'text-green-600' : ($metrics['avg_ctr'] >= 2 ? 'text-yellow-600' : 'text-red-600') ?>"><?= $metrics['avg_ctr'] ?>%</p>
          <p class="text-xs text-gray-400 mt-1">target: 5%</p>
        </div>
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-percentage text-purple-600"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Posição Média</p>
          <p class="text-2xl font-bold <?= $metrics['avg_position'] <= 10 ? 'text-green-600' : ($metrics['avg_position'] <= 20 ? 'text-yellow-600' : 'text-red-600') ?>"><?= $metrics['avg_position'] ?></p>
          <p class="text-xs text-gray-400 mt-1">Google</p>
        </div>
        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-chart-line text-orange-600"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Oportunidades</p>
          <p class="text-2xl font-bold text-purple-600"><?= $oppStats['pending'] ?? 0 ?></p>
          <p class="text-xs text-green-600 mt-1"><?= $oppStats['executed'] ?? 0 ?> executadas hoje</p>
        </div>
        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-lightbulb text-yellow-600"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfico 30 dias + Status do Motor -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Gráfico -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-chart-area text-blue-600 mr-2"></i>Impressões e Cliques (30 dias)
      </h3>
      <canvas id="dailyChart" height="200"></canvas>
    </div>

    <!-- Status do Motor -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-cog text-gray-600 mr-2"></i>Status do Motor
      </h3>
      <div class="space-y-4">
        <div>
          <p class="text-sm text-gray-500">Última Coleta (GSC)</p>
          <p class="text-sm font-medium text-gray-900"><?= esc($lastCollect) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Última Análise</p>
          <p class="text-sm font-medium text-gray-900"><?= esc($lastAnalyze) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Última Execução</p>
          <p class="text-sm font-medium text-gray-900"><?= esc($lastExecute) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Custo IA (30 dias)</p>
          <p class="text-sm font-bold text-green-600">$<?= $aiCost ?> USD</p>
        </div>
        <hr>
        <div class="space-y-2">
          <button onclick="runCollect()" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-download mr-1"></i> Coletar GSC (7 dias)
          </button>
          <button onclick="runAnalyze()" class="w-full px-3 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-brain mr-1"></i> Analisar Oportunidades
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Top Queries + Top Páginas -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Queries -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-search text-blue-600 mr-2"></i>Top Queries (28 dias)
      </h3>
      <?php if (empty($topQueries)): ?>
        <p class="text-gray-400 text-sm">Nenhum dado. Execute <code>php spark seo:collect</code></p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">Query</th>
                <th class="pb-2 text-right">Imp</th>
                <th class="pb-2 text-right">Cliques</th>
                <th class="pb-2 text-right">Pos</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topQueries as $q): ?>
              <tr class="border-b border-gray-50">
                <td class="py-2 font-medium text-gray-900 max-w-[200px] truncate"><?= esc($q['query']) ?></td>
                <td class="py-2 text-right text-gray-600"><?= number_format((float)($q['total_impressions'] ?? 0)) ?></td>
                <td class="py-2 text-right text-blue-600 font-medium"><?= $q['total_clicks'] ?? 0 ?></td>
                <td class="py-2 text-right">
                  <span class="px-2 py-0.5 rounded text-xs <?= ($q['avg_position'] ?? 99) <= 10 ? 'bg-green-100 text-green-700' : (($q['avg_position'] ?? 99) <= 20 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                    <?= round((float)($q['avg_position'] ?? 0), 1) ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Top Páginas -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-file-alt text-green-600 mr-2"></i>Top Páginas (28 dias)
      </h3>
      <?php if (empty($topPages)): ?>
        <p class="text-gray-400 text-sm">Nenhum dado disponível.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topPages, 0, 8) as $p): ?>
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate"><?= esc($p['page_url'] ?? '') ?></p>
            </div>
            <div class="ml-3 flex items-center gap-3 text-sm">
              <span class="text-blue-600 font-medium"><?= $p['total_clicks'] ?? 0 ?> cliques</span>
              <span class="text-gray-400"><?= number_format((float)($p['total_impressions'] ?? 0)) ?> imp</span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Links Rápidos -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <a href="/admin/seo-engine/opportunities" class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition group">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200">
          <i class="fas fa-lightbulb text-yellow-600"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-900">Oportunidades</p>
          <p class="text-xs text-gray-500"><?= $oppStats['pending'] ?? 0 ?> pendentes</p>
        </div>
      </div>
    </a>

    <a href="/admin/seo-engine/actions" class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition group">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
          <i class="fas fa-history text-blue-600"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-900">Histórico</p>
          <p class="text-xs text-gray-500"><?= count($todayActions) ?> ações hoje</p>
        </div>
      </div>
    </a>

    <a href="/admin/seo-engine/config" class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition group">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200">
          <i class="fas fa-sliders-h text-gray-600"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-900">Configurações</p>
          <p class="text-xs text-gray-500">Thresholds e APIs</p>
        </div>
      </div>
    </a>

    <a href="/admin/seo" class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition group">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200">
          <i class="fas fa-search text-green-600"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-900">SEO Dashboard</p>
          <p class="text-xs text-gray-500">Blog, 404, FAQs</p>
        </div>
      </div>
    </a>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// Gráfico diário
fetch('/admin/seo-engine/api/daily-stats?days=30')
  .then(r => r.json())
  .then(res => {
    if (!res.success || !res.data || res.data.length === 0) return;

    const labels = res.data.map(d => d.date ? d.date.substring(5) : '');
    const impressions = res.data.map(d => parseInt(d.total_impressions || 0));
    const clicks = res.data.map(d => parseInt(d.total_clicks || 0));

    new Chart(document.getElementById('dailyChart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Impressões',
            data: impressions,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.3,
          },
          {
            label: 'Cliques',
            data: clicks,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            fill: true,
            tension: 0.3,
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { position: 'top' }
        }
      }
    });
  });

// Ações AJAX
function runCollect() {
  const btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Coletando...';

  fetch('/admin/seo-engine/run-collect', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'} })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        alert('Coleta finalizada! ' + (res.inserted || 0) + ' inseridos, ' + (res.updated || 0) + ' atualizados.');
        location.reload();
      } else {
        alert('Erro: ' + (res.error || 'Desconhecido'));
      }
    })
    .catch(e => alert('Erro: ' + e.message))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-download mr-1"></i> Coletar GSC (7 dias)';
    });
}

function runAnalyze() {
  const btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Analisando...';

  fetch('/admin/seo-engine/run-analyze', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'} })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        alert('Análise finalizada! ' + (res.total_found || 0) + ' oportunidades, ' + (res.new_saved || 0) + ' novas.');
        location.reload();
      } else {
        alert('Erro: ' + (res.error || 'Desconhecido'));
      }
    })
    .catch(e => alert('Erro: ' + e.message))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-brain mr-1"></i> Analisar Oportunidades';
    });
}
</script>

<?= $this->endSection() ?>
